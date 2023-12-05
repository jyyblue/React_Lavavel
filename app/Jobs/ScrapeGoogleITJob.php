<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;

use Illuminate\Support\Facades\Log;
use App\Models\Product;
use DateTime;
use DiDom\Document;
use DiDom\Query;
use App\Models\GoogleResults;
use App\Models\GoogleScrapeHistory;
use Carbon\Carbon;

class ScrapeGoogleITJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data_id;
    private $call_group_id;
    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        //
        $this->data_id = $data['data_id'];
        $this->call_group_id = $data['call_group_id'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
        $product = Product::find($this->data_id);
        $product_id = $product->id;
        $title = $product->title;
        $sku = $product->sku;
        $title = str_replace($sku, '', $title);
        $title = trim($title, " \t\n\r\0\x0B-");

        $success = 0;
        $fail = 0;

        // scraperapi
        $google_id = $product->google_id;
        $google_url = $product->google_url;
        if (
            $google_id
            // test for free trial zenserp api. Commit below line in product
        ) {
            $url =
                "http://api.scraperapi.com?api_key=" . env('SCRAPER_API_KEY') . "&url=" . $google_url;
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
                $baseNode = count($document->find('base')) > 0 ? $document->find('base')[0]->getAttribute('href') : '';
                $divContainer = count($document->find('.Z4PRXd')) > 0 ? $document->find('.Z4PRXd')[0] : null;
                $link = strlen($divContainer) > 0 ? $divContainer->nextSibling() : null;
                if (strlen($link) > 0) {
                    $link = $link->child(0)->getAttribute('href');
                    $offer_list_link =  $baseNode . $link;
                    // call offerlist page api.
                    $url =
                        "http://api.scraperapi.com?api_key=" . env('SCRAPER_API_KEY') . "&url=" . $offer_list_link;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $response2 = curl_exec($ch);
                    curl_close($ch);
                    // $document2 = new Document(storage_path() . '/logs/offerlist.html', true);
                    if (gettype($response2) === 'string') {
                        $document2 = new Document($response2, false);
                        $rows = $document2->find('#sh-osd__online-sellers-cont .sh-osd__offer-row');
                        if(count($rows) > 0) {
                            $success = 1;
                        }else{
                            $fail = 1;
                        }
                        foreach ($rows as $key => $row) {
                            $nameNode = $row->child(0);
                            $tmp = count($nameNode->find('.kPMwsc a')) > 0 ? $nameNode->find('.kPMwsc a')[0]->text() : '';
                            $spanTxt = count($nameNode->find('.kPMwsc a span')) > 0 ? $nameNode->find('.kPMwsc a span')[0]->text() : '';
                            $offer_name = trim(str_replace($spanTxt, '', $tmp));

                            $itemPriceNode = $row->child(2);
                            $itemPrice = count($itemPriceNode->find('.g9WBQb')) > 0 ? $itemPriceNode->find('.g9WBQb')[0]->text() : '';
                            $itemPrice = str_replace(',', '.', $itemPrice);
                            $itemPrice = preg_replace('/[^0-9.]/', '', $itemPrice);
                            $itemPrice = $itemPrice ? (float)$itemPrice : 0;

                            $priceNode = $row->child(3);
                            $price = count($priceNode->find('.drzWO')) > 0 ? $priceNode->find('.drzWO')[0]->text() : '';
                            $price = str_replace(',', '.', $price);
                            $tmp = $price;
                            $price = preg_replace('/[^0-9.]/', '', $price);
                            $price = $price ? (float)$price : 0;

                            $linkNode = $row->child(4);
                            $offer_link = count($linkNode->find('.UAVKwf a')) > 0 ? $linkNode->find('.UAVKwf a')[0]->getAttribute('href') : '';
                            $offer_link = $baseNode . $offer_link;
                            $data = [];
                            $data['product_id'] = $product_id;
                            $data['title'] = $title;
                            $data['total_price'] = $price;
                            $data['seller'] = $offer_name;
                            $data['item_price'] = $itemPrice;
                            $data['offer_link'] = $offer_link;
                            $this->storeData($data);
                        }
                    }else{
                        $fail = 1;
                    }
                } else {
                    $offerNodes = strlen($divContainer) > 0 ? $divContainer->find('.SokQEb') : [];
                    if(count($offerNodes) > 0) {
                        $success = 1;
                    }else{
                        $fail = 1;
                    }
                    foreach ($offerNodes as $key => $offer) {
                        $price = count($offer->find('.MVQv4e .zumdYc .DAkZw .aZK3gc')) > 0 ? $offer->find('.MVQv4e .zumdYc .DAkZw .aZK3gc')[0]->text() : '';
                        $price = str_replace(',', '.', $price);
                        $tmp = $price;
                        $price = preg_replace('/[^0-9.]/', '', $price);
                        $price = $price ? (float)$price : 0;

                        $price_deliver = count($offer->find('.MVQv4e .Lgcmkb>div')) > 0 ? $offer->find('.MVQv4e .Lgcmkb>div')[0]->text() : '';
                        $price_deliver = str_replace(',', '.', $price_deliver);
                        $price_deliver = preg_replace('/[^0-9.]/', '', $price_deliver);
                        $price_deliver = $price_deliver ? (float)$price_deliver : 0;

                        $offerLink = count($offer->find('.t7AZud span.VJGcUd a')) > 0 ? $offer->find('.t7AZud span.VJGcUd a')[0]->getAttribute('href') : '';

                        $offer_name = count($offer->find('.MVQv4e .DX0ugf a span')) > 0 ? $offer->find('.MVQv4e .DX0ugf a span')[0]->text() : '';
                        $offer_link = count($offer->find('.MVQv4e .DX0ugf a')) > 0 ? $offer->find('.MVQv4e .DX0ugf a')[0]->getAttribute('href') : '';
                        $data = [];
                        $data['product_id'] = $product_id;
                        $data['title'] = $title;
                        $data['total_price'] = $price + (float)$price_deliver;
                        $data['seller'] = $offer_name;
                        $data['item_price'] = $price;
                        $data['offer_link'] = $offerLink;
                        $this->storeData($data);
                    }
                }
            }else{
                $fail = 1;
            }
        } else{
            $fail = 1;
        }
        // store history
        $history = new GoogleScrapeHistory();
        $history->call_group_id = $this->call_group_id;
        $history->product_id = $product_id;
        $history->call_time = Carbon::now();
        $history->sz_success = $success;
        $history->sz_fail = $fail;
        $history->save();
    }

    private function storeData($data)
    {
        $product_id = $data['product_id'] ? $data['product_id'] : 0;
        $title = $data['title'] ? $data['title'] : '';
        $total_price = $data['total_price'] ? $data['total_price'] : 0;
        $seller = $data['seller'] ? $data['seller'] : '';
        $item_price = $data['item_price'] ? $data['item_price'] : 0;
        $offer_link = $data['offer_link'] ? $data['offer_link'] : '';

        Log::info('tp: '.$total_price);
        Log::info('pid: '.$product_id);
        Log::info('ip: '.$item_price);
        Log::info('---------------------------------');

        // if ((float)$total_price > 0 && (float)$item_price > 0) {
            // store in database.
            GoogleResults::create([
                'product_id' => $product_id,
                'title' => $title,
                'total_price' => $total_price,
                'seller' => $seller,
                'item_price' => $item_price,
                'offer_link' => $offer_link,
                'call_group_id' => $this->call_group_id,
            ]);
        // }
        return;
    }
}
