<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'product_name',
        'brand',
        'thumbnail_url',
        'view_count',
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }
}
