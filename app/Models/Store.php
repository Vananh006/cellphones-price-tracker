<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'stores';

    public $timestamps = false;

    protected $fillable = [
        'store_name',
        'base_url',
    ];

    public function storePrices()
    {
        return $this->hasMany(StorePrice::class, 'store_id');
    }
}
