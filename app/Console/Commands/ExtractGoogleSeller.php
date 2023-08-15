<?php

namespace App\Console\Commands;

use App\Models\GoogleResults;
use Illuminate\Console\Command;
use App\Models\GoogleSeller;

class ExtractGoogleSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extract-google-seller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extract seller from google result table and insert into google_seller table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $sellers = GoogleResults::select('seller')
            ->where('seller', '<>', '')
            ->whereNotNull('seller')
            ->groupBy('seller')
            ->get();
        foreach ($sellers as $key => $item) {
            GoogleSeller::updateOrCreate(
                [
                    'name' => $item->seller,
                ],
                [
                    'name' => $item->seller,
                ]
            );
        }
    }
}
