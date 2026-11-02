<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePrice extends Model
{
    protected $table = 'store_prices';

    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'variant_id',
        'store_id',
        'current_price',
        'original_price',
        'product_url',
        'affiliate_url',
        'in_stock',
        'availability_status',
        'last_crawled_at',
        'last_crawl_status',
        'crawl_error',
        'updated_at',
    ];

    protected $casts = [
        'current_price' => 'float',
        'original_price' => 'float',
        'in_stock' => 'boolean',
        'last_crawled_at' => 'datetime',
        'updated_at' => 'datetime',
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
