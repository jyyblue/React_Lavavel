<?php

namespace Database\Seeders;

use App\Models\Setting;
use Hamcrest\Core\Set;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $items = [
            [
                'category' => 'cron',
                'name' => 'amazon_week1',
                'value' => '0',
            ],
            [
                'category' => 'cron',
                'name' => 'amazon_week2',
                'value' => '-1',
            ],
            [
                'category' => 'cron',
                'name' => 'google_week1',
                'value' => '0',
            ],
            [
                'category' => 'cron',
                'name' => 'google_week2',
                'value' => '-1',
            ],
            [
                'category' => 'mail',
                'name' => 'amazon_main',
                'value' => '0',
            ],
            [
                'category' => 'mail',
                'name' => 'amazon_agent',
                'value' => '0',
            ],
            [
                'category' => 'mail',
                'name' => 'google_main',
                'value' => '0',
            ],
            [
                'category' => 'mail',
                'name' => 'google_agent',
                'value' => '0',
            ],
            [
                'category' => 'discount',
                'name' => 'amazon',
                'value' => '0',
            ],
            [
                'category' => 'discount',
                'name' => 'google',
                'value' => '0',
            ],
        ];

        for($i =0 ; $i < count($items); $i++) {
            $item = Setting::where('category', $items[$i]['category'])
            ->where('name', $items[$i]['name'])->first();
            if($item === null ) {
                $item = Setting::create([
                    'category' => $items[$i]['category'],
                    'name' => $items[$i]['name'],
                    'value' => $items[$i]['value'],
                ]);
            }
        }
    }
}
