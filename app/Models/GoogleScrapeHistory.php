<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleScrapeHistory extends Model
{
    use HasFactory;

    protected $table = 'google_scrape_history';
    
    protected $guarded = ['id'];
}
