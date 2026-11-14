<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $table = 'price_history';

    public $timestamps = false;

    protected $fillable = [
        'variant_id',
        'store_id',
        'price',
        'recorded_at',
    ];

    protected $casts = [
        'price' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
