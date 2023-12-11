<?php

namespace App\Console\Commands;

use App\Jobs\ScrapeAmazonITJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\AmazonResults;
use App\Models\AmazonSeller;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use App\Models\Setting;
use App\Jobs\AmazonMailJob;
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

            $product_list = Product::where('cron_flg_amazon', 0)->get();
            $count = count($product_list);
            if ($count == 0) {
                return;
            }
            $call_result = AmazonResults::orderBy('call_group_id', 'DESC')->first();
            $call_group_id = 0;
            if (!empty($call_result)) {
                $call_group_id = $call_result->call_group_id;
            }
            $call_group_id++;

            $size = 20;
            $szHeap = ceil($count / $size);
            for ($i = 0; $i < $szHeap; $i++) {
                $start = $i * $size;
                $end = min(($i + 1) * $size, $count);
                $ids = array();
                for ($j = $start; $j < $end; $j++) {
                    $p = $product_list[$j];
                    array_push($ids, $p->id);
                }
                try {
                    if (count($ids) > 0) {
                        $data = [
                            'data_id' => $ids,
                            'call_group_id' => $call_group_id
                        ];
                        $job = new ScrapeAmazonITJob($data);
                        array_push($batchJob, $job);
                        $success++;
                    }
                } catch (\Exception $e) {
                    $fail++;
                    array_push($failError, $e->getMessage());
                }
            }

            Product::where('cron_flg_amazon', 0)->update(['cron_flg_amazon' => 1]);

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
            Log::info('Error: handle' . $e->getMessage());
        }
    }

    private function MailQueue()
    {
        $discount = Setting::where('category', 'discount')->where('name', 'amazon')->first();
        $batchJob1 = array();
        $batchJob2 = array();
        $sellers = AmazonSeller::whereNotNull('email')->where('email_flg', '1')->get();

        foreach ($sellers as $key => $seller) {
            $data = [
                'seller' => $seller,
                'discount' => 1,
                'email' => $seller->email,
            ];
            $job = new AmazonMailJob($data);
            array_push($batchJob1, $job);
        }

        $batch1 = Bus::batch($batchJob1)->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->allowFailures()->dispatch();

        $sellers = AmazonSeller::whereNotNull('sales_agent_email')->where('agent_flg', '1')->get();

        foreach ($sellers as $key => $seller) {
            $data = [
                'seller' => $seller,
                'discount' => 1,
                'email' => $seller->sales_agent_email,
            ];
            $job = new AmazonMailJob($data);
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
