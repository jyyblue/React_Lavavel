<?php

namespace App\Console\Commands;

use App\Jobs\GoogleMailJob;
use App\Jobs\ScrapeGoogleITJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\GoogleResults;
use App\Models\GoogleSeller;
use App\Models\Setting;
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
            if (count($product_list) == 0) {
                return;
            }
            $call_result = GoogleResults::orderBy('call_group_id', 'DESC')->first();
            $call_group_id = 0;
            if (!empty($call_result)) {
                $call_group_id = $call_result->call_group_id;
            }
            $call_group_id++;
            foreach ($product_list as $key => $item) {
                try {
                    $data = [
                        'data_id' => $item->id,
                        'call_group_id' => $call_group_id,
                    ];
                    $job = new ScrapeGoogleITJob($data);
                    array_push($batchJob, $job);
                    $success++;
                } catch (\Exception $e) {
                    $fail++;
                    array_push($failError, $e->getMessage());
                }
            }

            Product::where('cron_flg', 0)->update(['cron_flg' => 1]);

            $batch = Bus::batch($batchJob)->then(function (Batch $batch) {
                // All jobs completed successfully...
            })->catch(function (Batch $batch, Throwable $e) {
                // First batch job failure detected...
            })->finally(function (Batch $batch) {
                // The batch has finished executing...
                $processedJobs = $batch->processedJobs();
                $failedJobs = $batch->failedJobs;
            })->allowFailures()->dispatch();

            $this->MailQueue();
        } catch (\Exception $e) {
            Log::info('Error:' . $e->getMessage());
            set_time_limit(60);
        }
    }

    private function MailQueue()
    {
        $discount = Setting::where('category', 'discount')->where('name', 'google')->first();
        $batchJob1 = array();
        $batchJob2 = array();
        $sellers = GoogleSeller::whereNotNull('email')->where('email_flg', '1')->get();

        foreach ($sellers as $key => $seller) {
            $data = [
                'seller' => $seller,
                'discount' => $discount->value,
                'email' => $seller->email,
            ];
            $job = new GoogleMailJob($data);
            array_push($batchJob1, $job);
        }

        $batch1 = Bus::batch($batchJob1)->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->allowFailures()->dispatch();


        $sellers = GoogleSeller::whereNotNull('sales_agent_email')->where('agent_flg', '1')->get();

        foreach ($sellers as $key => $seller) {
            $data = [
                'seller' => $seller,
                'discount' => $discount,
                'email' => $seller->sales_agent_email,
            ];
            $job = new GoogleMailJob($data);
            array_push($batchJob2, $job);
        }

        $batch1 = Bus::batch($batchJob2)->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->allowFailures()->dispatch();
    }
}
