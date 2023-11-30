<?php

namespace App\Jobs;

use App\Models\AmazonAPICallHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\AmazonResults;
use Carbon\Carbon;
use DiDom\Document;

class ScrapeAmazonITJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data_id = array();

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        //
        $this->data_id = $data['data_id'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            // Determine if the batch has been cancelled...
            return;
        }
        //
        try {
 
            $access_token = $this->getAccessToken();
            $data_asin = array();
            $products = Product::whereIn('id', $this->data_id)->get();

            $szEmptyAsin = 0;
            foreach ($products as $key => $product) {
                if(!empty($product->asin)){
                    $item_asin = array(
                        'uri' => '/products/pricing/v0/items/' . $product->asin . '/offers',
                        'method' => 'GET',
                        'ItemCondition' => 'New',
                        'MarketplaceId' => env('AMAZON_MARKETPLACE_ID'),
                        'CustomerType' => 'Consumer'
                    );
                    array_push($data_asin, $item_asin);
                } else {
                    $szEmptyAsin++;
                }
            }

            // get last call time
            $last = AmazonAPICallHistory::orderBy('created_at', 'DESC')->first();
            if(!empty($last)) {
                $callTime = Carbon::parse($last->call_time);
                $now = Carbon::now();
                $diff = $now->diffInMicroseconds($callTime);

                // compare 10 second and wait
                $remain = 11000000 - $diff;
                if( $remain > 0 ) {
                    usleep($remain);
                }
            }

            $request_asin = array('requests' => $data_asin);
            $request_asin_str = json_encode($request_asin);
            $this->findOfferWithAsin($access_token, $request_asin_str, $szEmptyAsin);

        } catch (\Exception $e) {
            Log::info('Error: handle' . $e->getMessage());
        }
    }

    private function getAccessToken()
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.amazon.com/auth/o2/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'grant_type=refresh_token&refresh_token=' . env('AMAZON_SELLER_REFRESH_TOKEN') . '&client_id=' . env('AMAZON_SELLER_ID') . '&client_secret=' . env('AMAZON_SELLER_SECRET'),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $result = json_decode($response);
            if (isset($result->access_token)) {
                return $result->access_token;
            }
            return '';
        } catch (\Exception $e) {
            Log::info('Error: getAccessToken' . $e->getMessage());
        }
    }
    private function findOfferWithAsin($access_token, $request, $szEmptyAsin)
    {
        try {

            $history = new AmazonAPICallHistory();
            $history->call_group_id = 0;
            $history->call_sub_group_id = $this->data_id[0];
            $history->sz_empty_asin = $szEmptyAsin;
            $history->call_time = Carbon::now();
            $history->save();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://sellingpartnerapi-eu.amazon.com/batches/products/pricing/v0/itemOffers',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $request,
                CURLOPT_HTTPHEADER => array(
                    'x-amz-access-token:' . $access_token,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $result = json_decode($response);
            $this->processAsin($result, $history);
            return true;
        } catch (\Exception $e) {
            Log::info('Error: findOfferWithAsin' . $e->getMessage());
            $history->update([
                'sz_fail' => count($this->data_id),
            ]);
            return false;
        }
    }
    private function processAsin($result, $history)
    {
        try {
            if (isset($result->responses)) {
                $responses = $result->responses;

                $szSuccess = 0;
                $szFail = 0;
                for ($key = 0; $key < count($responses); $key++) {
                    $resp = $responses[$key];
                    $status = isset($resp->status) ? $resp->status : array();
                    $statusCode = isset($status->statusCode) ? $status->statusCode : 0;
                    if ($statusCode == 200) {
                        $body = $resp->body;
                        $asin = '';
                        if (isset($body->payload)) {
                            $payload = $body->payload;
                            $asin = isset($payload->ASIN) ? $payload->ASIN : '';
                            if (isset($payload->Offers)) {
                                $offers = $payload->Offers;
                                foreach ($offers as $key2 => $offer) {
                                    $listingPrice = isset($offer->ListingPrice) ? $offer->ListingPrice : array();
                                    $LPrice = isset($listingPrice->Amount) ? $listingPrice->Amount : 0;
                                    $ShippingPrice = isset($offer->Shipping) ? $offer->Shipping : array();
                                    $SPrice = isset($ShippingPrice->Amount) ? $ShippingPrice->Amount : 0;
                                    $SellerId = isset($offer->SellerId) ? $offer->SellerId : '';
                                    $data = [];
                                    $data['asin'] = $asin;
                                    $data['listing_price'] = $LPrice;
                                    $data['ship_price'] = $SPrice;
                                    $data['seller'] = $SellerId;
                                    $this->storeData($data);
                                    $szSuccess++;
                                }
                            } else {
                                $szFail++;
                            }
                        } else {
                            $szFail++;
                        }
                    } else {
                        $szFail++;
                    }
                }

                $history->update([
                    'sz_success' => $szSuccess,
                    'sz_fail' => $szFail,
                ]);

            } else {
                Log::info('process Asin: json_encode($result)');
                $history->update([
                    'sz_fail' => count($this->data_id),
                ]);
            }
            return true;
        } catch (\Exception $e) {
            Log::info('Error: processAsin' . $e->getMessage());
            $history->update([
                'sz_fail' => count($this->data_id),
            ]);
            return false;
        }
    }
    private function storeData($data)
    {
        $asin = $data['asin'] ? $data['asin'] : '';
        $listing_price = $data['listing_price'] ? $data['listing_price'] : 0;
        $ship_price = $data['ship_price'] ? $data['ship_price'] : 0;
        $seller = $data['seller'] ? $data['seller'] : '';
        $total_price = (float)$listing_price + (float)$ship_price;
        $sellerName = $this->getSellerName($seller);
        $product = Product::where('asin', $asin)->first();
        $product_id = $product->id;

        // store in database.
        AmazonResults::create([
            'product_id' => $product_id,
            'total_price' => $total_price,
            'seller' => $seller,
            'seller_name' => $sellerName,
            'item_price' => $listing_price,
            'offer_link' => 'https://amazon.it/dp/' . $asin,
        ]);
        return;
    }

    private function getSellerName($sellerID)
    {
        try {
            $sellerUrl = 'https://amazon.it/sp?seller=' . $sellerID;
            $url =
                "http://api.scraperapi.com?api_key=" . env('SCRAPER_API_KEY') . "&url=" . $sellerUrl;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            if (gettype($response) === 'string') {
                $document = new Document($response, false);
                $sellerName = count($document->find('#seller-name')) > 0 ? $document->find('#seller-name')[0]->text() : '';
                return trim($sellerName);
            }

            return '';
        } catch (\Exception $e) {
            Log::info('Error getSellerName:' . $e->getMessage());
            return '';
        }
    }
}
