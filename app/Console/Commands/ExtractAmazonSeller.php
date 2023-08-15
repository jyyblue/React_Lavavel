<?php

namespace App\Console\Commands;

use App\Models\AmazonResults;
use App\Models\AmazonSeller;
use Illuminate\Console\Command;

class ExtractAmazonSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extract-amazon-seller';

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
        $sellers = AmazonResults::select('seller')
        ->where('seller', '<>', '')
        ->whereNotNull('seller')
        ->groupBy('seller')
        ->get();
    foreach ($sellers as $key => $item) {
        $sellerid = $item->seller;
        $sellername = $item->seller_name;
        if($sellername == '') {
            $sellername = $sellerid;
        } else {
            $sellername = $sellername.'-'.$sellerid.'-';
        }
        AmazonSeller::updateOrCreate(
            [
                'name' => $sellername,
            ],
            [
                'name' => $sellername,
            ]
        );
    }
    }
}
