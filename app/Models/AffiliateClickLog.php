<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateClickLog extends Model
{
    protected $table = 'affiliate_click_logs';

    public $timestamps = false;

    protected $fillable = [
        'store_price_id',
        'user_id',
        'ip_address',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function storePrice()
    {
        return $this->belongsTo(StorePrice::class, 'store_price_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
