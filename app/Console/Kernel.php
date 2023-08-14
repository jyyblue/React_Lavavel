<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Product;
use App\Console\Commands\ScrapeAmazonIT;
use App\Console\Commands\ScrapeGoogleIT;
use App\Console\Commands\ExtractGoogleSeller;

class Kernel extends ConsoleKernel
{
        /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        ScrapeAmazonIT::class,
        ScrapeGoogleIT::class,
        ExtractGoogleSeller::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('app:scrape-amazon-i-t')->everyMinute();
        $schedule->command('app:extract-google-seller')->daily();
        // $schedule->command('app:scrape-google-i-t')->everyMinute();
        
        $schedule->call(function () {
            //get all disputed orders
            Product::where('cron_flg', 1)->update(['cron_flg' => 0]);
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
