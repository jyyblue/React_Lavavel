<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmazonAPICallHistory extends Model
{
    use HasFactory;

    protected $table = 'amazon_api_call_history';
    
    protected $guarded = ['id'];
}
