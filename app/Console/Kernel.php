<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Product;
use App\Console\Commands\ScrapeAmazonIT;
use App\Console\Commands\ScrapeGoogleIT;
use App\Console\Commands\ExtractGoogleSeller;
use App\Console\Commands\ExtractAmazonSeller;

class Kernel extends ConsoleKernel
{
        /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        ScrapeAmazonIT::class,
        ScrapeGoogleIT::class,
        ExtractGoogleSeller::class,
        ExtractAmazonSeller::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // scrape google and amazon data.
        $schedule->command('app:scrape-amazon-i-t')->everyMinute();
        $schedule->command('app:scrape-google-i-t')->everyMinute();

        // // extract seller.
        $schedule->command('app:extract-google-seller')->daily();
        $schedule->command('app:extract-amazon-seller')->daily();
        
        $schedule->call(function () {
            // mark all product to be refreshed.
            Product::where('cron_flg', 1)->update(['cron_flg' => 0]);
            Product::where('cron_flg_amazon', 1)->update(['cron_flg_amazon' => 0]);
        })->weekly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
