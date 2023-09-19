<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeGoogleITJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\GoogleResults;
use DateTime;
use DiDom\Document;
use DiDom\Query;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;


class ScrapeGoogleIT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-google-i-t';

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
        //
        try {
            $success = 0;
            $fail = 0;
            $failError = array();
            $batchJob = array();
    
            $product_list = Product::where('cron_flg', 0)->get();
    
            foreach ($product_list as $key => $item) {
                try{
                    $data = [
                        'data_id' => $item->id,
                    ];
                    $job = new ScrapeGoogleITJob($data);
                    array_push($batchJob, $job);
                    $success ++;
                }catch(\Exception $e) {
                    $fail++;
                    array_push($failError, $e->getMessage());
                }
            }
    
            Product::where('cron_flg', 0)->update(['cron_flg' => 1]);

            $batch = Bus::batch($batchJob)->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
            })->finally(function (Batch $batch){
                // The batch has finished executing...
                $processedJobs = $batch->processedJobs();
                $failedJobs = $batch->failedJobs;
            })->allowFailures()->dispatch();

            // $product_id = 0;
            // $product_list = Product::where('cron_flg', 0)->take(30)->get();
            // $pids = [];
            // foreach ($product_list as $key => $product) {
            //     array_push($pids, $product->id);
            // }
            // Product::whereIn('id', $pids)->update(['cron_flg'=> 1]);
            // foreach ($product_list as $key => $product) {
            //     $product_id = $product->id;
            //     $title = $product->title;
            //     $sku = $product->sku;
            //     $title = str_replace($sku, '', $title);
            //     $title = trim($title, " \t\n\r\0\x0B-");

            //     // scraperapi
            //     $google_id = $product->google_id;
            //     $google_url = $product->google_url;
            //     if (
            //         $google_id
            //         // test for free trial zenserp api. Commit below line in product
            //     ) {
            //         $url =
            //             "http://api.scraperapi.com?api_key=" . env('SCRAPER_API_KEY') . "&url=" . $google_url;
            //         $ch = curl_init();
            //         curl_setopt($ch, CURLOPT_URL, $url);
            //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //         curl_setopt($ch, CURLOPT_HEADER, FALSE);
            //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //         $response = curl_exec($ch);
            //         curl_close($ch);
            //         if (gettype($response) === 'string') {
            //             $document = new Document($response, false);
            //             // $document = new Document(storage_path() . '/logs/test.html', true);
            //             $baseNode = count($document->find('base')) > 0 ? $document->find('base')[0]->getAttribute('href') : '';
            //             $divContainer = count($document->find('.Z4PRXd')) > 0 ? $document->find('.Z4PRXd')[0] : null;
            //             $link = strlen($divContainer) > 0 ? $divContainer->nextSibling() : null;
            //             if (strlen($link) > 0) {
            //                 $link = $link->child(0)->getAttribute('href');
            //                 $offer_list_link =  $baseNode . $link;
            //                 // call offerlist page api.
            //                 $url =
            //                     "http://api.scraperapi.com?api_key=" . env('SCRAPER_API_KEY') . "&url=" . $offer_list_link;
            //                 $ch = curl_init();
            //                 curl_setopt($ch, CURLOPT_URL, $url);
            //                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //                 curl_setopt($ch, CURLOPT_HEADER, FALSE);
            //                 curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            //                 $response2 = curl_exec($ch);
            //                 curl_close($ch);
            //                 // $document2 = new Document(storage_path() . '/logs/offerlist.html', true);
            //                 if (gettype($response2) === 'string') {
            //                     $document2 = new Document($response2, false);
            //                     $rows = $document2->find('#sh-osd__online-sellers-cont .sh-osd__offer-row');
            //                     foreach ($rows as $key => $row) {
            //                         $nameNode = $row->child(0);
            //                         $tmp = count($nameNode->find('.kPMwsc a')) > 0 ? $nameNode->find('.kPMwsc a')[0]->text() : '';
            //                         $spanTxt = count($nameNode->find('.kPMwsc a span')) > 0 ? $nameNode->find('.kPMwsc a span')[0]->text() : '';
            //                         $offer_name = trim(str_replace($spanTxt, '', $tmp));

            //                         $itemPriceNode = $row->child(2);
            //                         $itemPrice = count($itemPriceNode->find('.g9WBQb')) > 0 ? $itemPriceNode->find('.g9WBQb')[0]->text() : '';
            //                         $itemPrice = str_replace(',', '.', $itemPrice);
            //                         $itemPrice = preg_replace('/[^0-9.]/', '', $itemPrice);
            //                         $itemPrice = $itemPrice ? (float)$itemPrice : 0;

            //                         $priceNode = $row->child(3);
            //                         $price = count($priceNode->find('.drzWO')) > 0 ? $priceNode->find('.drzWO')[0]->text() : '';
            //                         $price = str_replace(',', '.', $price);
            //                         $tmp = $price;
            //                         $price = preg_replace('/[^0-9.]/', '', $price);
            //                         $price = $price ? (float)$price : 0;

            //                         $linkNode = $row->child(4);
            //                         $offer_link = count($linkNode->find('.UAVKwf a')) > 0 ? $linkNode->find('.UAVKwf a')[0]->getAttribute('href') : '';
            //                         $offer_link = $baseNode . $offer_link;
            //                         $data = [];
            //                         $data['product_id'] = $product_id;
            //                         $data['title'] = $title;
            //                         $data['total_price'] = $price;
            //                         $data['seller'] = $offer_name;
            //                         $data['item_price'] = $itemPrice;
            //                         $data['offer_link'] = $offer_link;
            //                         $this->storeData($data);
            //                     }
            //                 }
            //             } else {
            //                 $offerNodes = strlen($divContainer) > 0 ? $divContainer->find('.SokQEb') : [];
            //                 foreach ($offerNodes as $key => $offer) {
            //                     $price = count($offer->find('.MVQv4e .zumdYc .DAkZw .aZK3gc')) > 0 ? $offer->find('.MVQv4e .zumdYc .DAkZw .aZK3gc')[0]->text() : '';
            //                     $price = str_replace(',', '.', $price);
            //                     $tmp = $price;
            //                     $price = preg_replace('/[^0-9.]/', '', $price);
            //                     $price = $price ? (float)$price : 0;

            //                     $price_deliver = count($offer->find('.MVQv4e .Lgcmkb>div')) > 0 ? $offer->find('.MVQv4e .Lgcmkb>div')[0]->text() : '';
            //                     $price_deliver = str_replace(',', '.', $price_deliver);
            //                     $price_deliver = preg_replace('/[^0-9.]/', '', $price_deliver);
            //                     $price_deliver = $price_deliver ? (float)$price_deliver : 0;

            //                     $offerLink = count($offer->find('.t7AZud span.VJGcUd a')) > 0 ? $offer->find('.t7AZud span.VJGcUd a')[0]->getAttribute('href') : '';

            //                     $offer_name = count($offer->find('.MVQv4e .DX0ugf a span')) > 0 ? $offer->find('.MVQv4e .DX0ugf a span')[0]->text() : '';
            //                     $offer_link = count($offer->find('.MVQv4e .DX0ugf a')) > 0 ? $offer->find('.MVQv4e .DX0ugf a')[0]->getAttribute('href') : '';
            //                     $data = [];
            //                     $data['product_id'] = $product_id;
            //                     $data['title'] = $title;
            //                     $data['total_price'] = $price + (float)$price_deliver;
            //                     $data['seller'] = $offer_name;
            //                     $data['item_price'] = $price;
            //                     $data['offer_link'] = $offerLink;
            //                     $this->storeData($data);
            //                 }
            //             }
            //         }
            //     }
            // }

            // set_time_limit(60);
        } catch (\Exception $e) {
            Log::info('Error:' . $e->getMessage());
            set_time_limit(60);
        }
    }

    private function storeData($data)
    {
        $product_id = $data['product_id'] ? $data['product_id'] : 0;
        $title = $data['title'] ? $data['title'] : '';
        $total_price = $data['total_price'] ? $data['total_price'] : 0;
        $seller = $data['seller'] ? $data['seller'] : '';
        $item_price = $data['item_price'] ? $data['item_price'] : 0;
        $offer_link = $data['offer_link'] ? $data['offer_link'] : '';

        // store in database.
        GoogleResults::create([
            'product_id' => $product_id,
            'title' => $title,
            'total_price' => $total_price,
            'seller' => $seller,
            'item_price' => $item_price,
            'offer_link' => $offer_link,
        ]);
        return;
    }

    function milliseconds() {
        $mt = explode(' ', microtime());
        return intval( $mt[1] * 1E3 ) + intval( round( $mt[0] * 1E3 ) );
    }
}
