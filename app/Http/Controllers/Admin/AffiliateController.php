<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    public function index()
{
    $links = DB::table('store_prices')
        ->join('product_variants', 'store_prices.variant_id', '=', 'product_variants.id')
        ->join('products', 'product_variants.product_id', '=', 'products.id')
        ->join('stores', 'store_prices.store_id', '=', 'stores.id')
        ->select(
            'store_prices.id',
            'products.product_name as product_name',
            'stores.store_name',
            'store_prices.affiliate_url'
        )
        ->get();

    return view('admin.affiliate.index', compact('links'));
}
}