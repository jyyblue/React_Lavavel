<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use DateTime;
use Revolution\Google\Sheets\Facades\Sheets;
use App\Models\Product;
use App\Models\AmazonResults;
use DiDom\Document;

class ScrapeAmazonIT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-amazon-i-t';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $request = $this->markProcessedProduct();
            $count = $request['count'];
            if($count > 0) {
                $access_token = $this->getAccessToken();
                $this->findOfferWithAsin($access_token, $request['asin']);
            }

            // do {
            //     $access_token = $this->getAccessToken();
            //     $this->findOfferWithAsin($access_token, $request['asin']);
            //     $request = $this->markProcessedProduct();
            // } while (strlen($request['asin']) > 0);
        } catch (\Exception $e) {
            Log::info('Error: handle' . $e->getMessage());
        }
    }

    private function findOfferWithSku($access_token, $request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://sellingpartnerapi-eu.amazon.com/batches/products/pricing/v0/listingOffers',
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
    }
    private function processSku($result)
    {
        if (isset($result->responses)) {
            $responses = $result->responses;
            foreach ($responses as $key => $resp) {
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
                            }
                        }
                    }
                }
            }
        } else {
            Log::info(json_encode($result));
        }
    }

    private function findOfferWithAsin($access_token, $request)
    {
        try {
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
            $this->processAsin($result);
            return true;
        } catch (\Exception $e) {
            Log::info('Error: findOfferWithAsin' . $e->getMessage());

            return false;
        }
    }

    private function processAsin($result)
    {
        try {
            if (isset($result->responses)) {
                $responses = $result->responses;
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
                                    Log::info('processAsin:' . $asin);
                                    $this->storeData($data);
                                }
                            }
                        }
                    }
                }
            } else {
                Log::info(json_encode($result));
            }
            return true;
        } catch (\Exception $e) {
            Log::info('Error: processAsin' . $e->getMessage());

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
        $sellerName = 'xxx'; // $this->getSellerName($seller);
        $product = Product::where('asin', $asin)->first();
        $product_id = $product->id;
        $title = $product->title;
        // 'item_price', https://amazon.it/dp/B0719BRDS5
        // 'offer_link', https://amazon.it/s?me=AGMD7MBURS5HM&marketplaceID=APJ6JRA9NG5V4

        // store in database.
        AmazonResults::create([
            'product_id' => $product_id,
            'total_price' => $total_price,
            'seller' => $seller,
            'seller_name' => $sellerName,
            'item_price' => $listing_price,
            'offer_link' => $ship_price,
        ]);
        sleep(1);
        // store in google sheet
        if ($total_price > 0) {
            $append = [
                $product_id ? (int)$product_id : 0,
                $title ? $title : '',
                $total_price ? (float)$total_price : 0,
                $seller ? $seller  : '',
                $listing_price ? $listing_price  : 0,
                'https://amazon.it/s?me=' . $seller . '&marketplaceID=' . env('AMAZON_MARKETPLACE_ID'),
                now()->toDateTimeString(),
                $sellerName,
            ];
            Sheets::spreadsheet(config('sheets.amazon_spreadsheet_id'))
                ->sheet(config('sheets.amazon_sheet_id'))
                ->append([$append]);
        }
    }

    private function markProcessedProduct()
    {
        try {
            $pids = [];
            $product_list = Product::where('cron_flg_amazon', 0)->take(10)->get();

            foreach ($product_list as $key => $product) {
                array_push($pids, $product->id);
            }
            Product::whereIn('id', $pids)->update(['cron_flg_amazon' => 1]);
            $data_sku = array();
            $data_asin = array();
            foreach ($product_list as $key => $product) {
                $item_sku = array(
                    'uri' => '/products/pricing/v0/listings/' . $product->sku . '/offers',
                    'method' => 'GET',
                    'ItemCondition' => 'New',
                    'MarketplaceId' => env('AMAZON_MARKETPLACE_ID'),
                    'CustomerType' => 'Consumer'
                );
                array_push($data_sku, $item_sku);

                $item_asin = array(
                    'uri' => '/products/pricing/v0/items/' . $product->asin . '/offers',
                    'method' => 'GET',
                    'ItemCondition' => 'New',
                    'MarketplaceId' => env('AMAZON_MARKETPLACE_ID'),
                    'CustomerType' => 'Consumer'
                );
                array_push($data_asin, $item_asin);
            }
            $request_sku = array('requests' => $data_sku);
            $request_asin = array('requests' => $data_asin);
            $request_sku_str = json_encode($request_sku);
            $request_asin_str = json_encode($request_asin);
            return array(
                'sku' => $request_sku_str,
                'asin' => $request_asin_str,
                'count' => count($product_list),
            );
        } catch (\Exception $e) {
            Log::info('Error: getAccessToken' . $e->getMessage());
            return array(
                'sku' => '',
                'asin' => '',
                'count' => 0,
            );
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
                Log::info('seller Name: ' . $sellerName);
                return trim($sellerName);
            }

            return '';
        } catch (\Exception $e) {
            Log::info('Error getSellerName:' . $e->getMessage());
            return '';
        }
    }
}
