<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleMail extends Model
{
    use HasFactory;
        /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'google_mail';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'seller_id',
        'status',
    ];

    public function seller() {
        return $this->hasOne(GoogleSeller::class, 'id', 'seller_id');
    }
}
