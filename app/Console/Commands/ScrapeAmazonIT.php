<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeAmazonITJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\AmazonResults;
use DiDom\Document;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

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
            $success = 0;
            $fail = 0;
            $failError = array();
            $batchJob = array();
            // Product::where('cron_flg_amazon', 1)->update(['cron_flg_amazon' => 0]);
            $product_list = Product::where('cron_flg_amazon', 0)->get();
            $count = count($product_list);
            if($count == 0) {
                return;
            }
            $size = 20;
            $szHeap = ceil($count / $size);
            for($i=0; $i < $szHeap; $i++) {
                $start = $i * $size;
                $end = min(($i + 1) * $size, $count);
                $ids = array();
                for($j=$start; $j < $end; $j++) {
                    $p = $product_list[$j];
                    array_push($ids, $p->id);
                }
                try{
                    if(count($ids) > 0){
                        $data = [
                            'data_id' => $ids,
                        ];
                        $job = new ScrapeAmazonITJob($data);
                        array_push($batchJob, $job);
                        $success ++;
                    }
                }catch(\Exception $e) {
                    $fail++;
                    array_push($failError, $e->getMessage());
                }
            }

            Product::where('cron_flg_amazon', 0)->update(['cron_flg_amazon' => 1]);

            $batch = Bus::batch($batchJob)->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
            })->finally(function (Batch $batch){
                // The batch has finished executing...
                $processedJobs = $batch->processedJobs();
                $failedJobs = $batch->failedJobs;
            })->allowFailures()->dispatch();
        } catch (\Exception $e) {
            Log::info('Error: handle' . $e->getMessage());
        }
    }
}
