<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'storage',
        'color',
        'sku',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function storePrices()
    {
        return $this->hasMany(StorePrice::class, 'variant_id');
    }

    public function priceAlerts()
    {
        return $this->hasMany(PriceAlert::class, 'variant_id');
    }
}
