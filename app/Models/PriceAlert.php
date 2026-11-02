<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceAlert extends Model
{
    protected $table = 'price_alerts';

    protected $fillable = [
        'user_id',
        'variant_id',
        'submitted_url',
        'target_price',
        'alert_status',
    ];

    protected $casts = [
        'target_price' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
