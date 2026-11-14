<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = DB::table('products')->count();
        $totalUsers = DB::table('users')->count();
        $trackingLinks = DB::table('store_prices')->count();
        $sentAlerts = DB::table('price_alerts')->count();

        $recentProducts = DB::table('products')
            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('store_prices', 'product_variants.id', '=', 'store_prices.variant_id')
            ->leftJoin('stores', 'store_prices.store_id', '=', 'stores.id')
            ->select(
                'products.product_name',
                'products.brand',
                'product_variants.storage',
                'product_variants.color',
                'store_prices.current_price',
                'stores.store_name'
            )
            ->orderByDesc('products.id')
            ->limit(5)
            ->get();

        $recentActivities = [];

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalUsers',
            'trackingLinks',
            'sentAlerts',
            'recentProducts',
            'recentActivities'
        ));
    }
}