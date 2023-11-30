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
            if(count($product_list) > 0) {
                return ;
            }
            $call_result = GoogleResults::orderBy('call_group_id', 'DESC')->first();
            $call_group_id = 0;
            if(!empty($call_result)) {
                $call_group_id = $call_result->call_group_id;
            }
            $call_group_id ++;
            foreach ($product_list as $key => $item) {
                try{
                    $data = [
                        'data_id' => $item->id,
                        'call_group_id' => $call_group_id,
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

        } catch (\Exception $e) {
            Log::info('Error:' . $e->getMessage());
            set_time_limit(60);
        }
    }
}
