<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;

class ReportController
{
    public function index()
    {
        $totalProducts = DB::table('products')->count();
        $totalUsers = DB::table('users')->count();

        $totalLinks = DB::table('store_prices')
            ->whereNotNull('affiliate_url')
            ->count();

        $missingLinks = DB::table('store_prices')
            ->whereNull('affiliate_url')
            ->count();

        return view('admin.report.index', compact(
            'totalProducts',
            'totalUsers',
            'totalLinks',
            'missingLinks'
        ));
    }
}