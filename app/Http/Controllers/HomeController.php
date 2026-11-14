<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $keyword = trim((string) $request->query('q', ''));

        $productsQuery = DB::table('products')
            ->select('id', 'product_name', 'brand', 'thumbnail_url', 'view_count', 'created_at')
            ->orderBy('product_name');

        if ($keyword !== '') {
            $productsQuery->where('product_name', 'like', '%' . $keyword . '%');
        }

        $products = $productsQuery->get();

        foreach ($products as $product) {
            $prices = DB::table('store_prices')
                ->join('product_variants', 'store_prices.variant_id', '=', 'product_variants.id')
                ->join('stores', 'store_prices.store_id', '=', 'stores.id')
                ->where('product_variants.product_id', $product->id)
                ->where('store_prices.current_price', '>', 0)
                ->select(
                    'store_prices.id as store_price_id',
                    'store_prices.variant_id',
                    'store_prices.current_price',
                    'store_prices.original_price',
                    'store_prices.product_url',
                    'store_prices.affiliate_url',
                    'store_prices.updated_at',
                    'stores.store_name',
                    'product_variants.storage',
                    'product_variants.color'
                )
                ->orderBy('store_prices.current_price')
                ->get();

            $bestPrice = $prices->first();
            $cellphonePrice = $prices->firstWhere('store_name', 'CellphoneS');
            $fptPrice = $prices->firstWhere('store_name', 'FPT Shop');

            $product->best_price = $bestPrice;
            $product->cellphones_price = $cellphonePrice;
            $product->fpt_price = $fptPrice;
            $product->lowest_price = $bestPrice?->current_price;
            $product->store_name = $bestPrice?->store_name ?? 'Đang cập nhật';
            $product->product_url = $bestPrice?->affiliate_url ?: $bestPrice?->product_url;
            $product->store_price_id = $bestPrice?->store_price_id;
            $product->variant_label = $bestPrice ? trim($bestPrice->storage . ' • ' . $bestPrice->color) : '';
            $product->final_image = $product->thumbnail_url ?: 'https://images.unsplash.com/photo-1591337676887-a217a6970a8a?q=80&w=600&auto=format&fit=crop';
        }

        return view('home', compact('products', 'keyword'));
    }
}
