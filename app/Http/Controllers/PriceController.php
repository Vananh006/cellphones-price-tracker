<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceController extends Controller
{
    public function getHistory(Request $request, string $product)
    {
        $variantId = $request->query('variant_id');
        $days = (int) $request->query('days', 30);

        if (!in_array($days, [0, 7, 30, 90], true)) {
            $days = 30;
        }

        if (!$variantId) {
            $bestVariant = DB::table('store_prices')
                ->join('product_variants', 'store_prices.variant_id', '=', 'product_variants.id')
                ->where('product_variants.product_id', $product)
                ->whereNotNull('store_prices.current_price')
                ->where('store_prices.current_price', '>', 0)
                ->where('store_prices.in_stock', 1)
                ->select('store_prices.variant_id')
                ->orderBy('store_prices.current_price', 'asc')
                ->first();

            $variantId = $bestVariant->variant_id ?? null;
        }

        if (!$variantId) {
            return response()->json([
                'dates' => [],
                'fptPrices' => [],
                'cpsPrices' => [],
                'summary' => null,
                'message' => 'Sản phẩm này chưa có phiên bản có giá.',
            ]);
        }

        $historyQuery = DB::table('price_history as ph')
            ->join('stores as s', 'ph.store_id', '=', 's.id')
            ->where('ph.variant_id', $variantId)
            ->where('ph.price', '>', 0);

        if ($days > 0) {
            $historyQuery->where('ph.recorded_at', '>=', now()->subDays($days));
        }

        $rows = $historyQuery
            ->selectRaw('
                DATE(ph.recorded_at) as price_date,
                s.store_name,
                MIN(ph.price) as price
            ')
            ->groupByRaw('DATE(ph.recorded_at), s.store_name')
            ->orderByRaw('DATE(ph.recorded_at) ASC')
            ->get();

        if ($rows->isEmpty()) {
            $rows = DB::table('store_prices as sp')
                ->join('stores as s', 'sp.store_id', '=', 's.id')
                ->where('sp.variant_id', $variantId)
                ->whereNotNull('sp.current_price')
                ->where('sp.current_price', '>', 0)
                ->selectRaw('
                    DATE(sp.updated_at) as price_date,
                    s.store_name,
                    sp.current_price as price
                ')
                ->orderBy('sp.updated_at')
                ->get();
        }

        $dates = $rows
            ->pluck('price_date')
            ->unique()
            ->sort()
            ->values();

        $priceForStore = function (string $keyword) use ($rows, $dates) {
            return $dates->map(function ($date) use ($rows, $keyword) {
                $matched = $rows->first(function ($row) use ($date, $keyword) {
                    return $row->price_date === $date
                        && str_contains(
                            mb_strtolower($row->store_name, 'UTF-8'),
                            mb_strtolower($keyword, 'UTF-8')
                        );
                });

                return $matched ? (int) round($matched->price) : null;
            })->values();
        };

        $latestDate = $dates->last();

        $latestRows = $latestDate
            ? $rows->where('price_date', $latestDate)->values()
            : collect();

        $bestNow = $latestRows->sortBy('price')->first();
        $lowestHistory = $rows->sortBy('price')->first();

        return response()->json([
            'variantId' => (int) $variantId,
            'dates' => $dates->map(fn ($date) => date('d/m', strtotime($date)))->values(),
            'fptPrices' => $priceForStore('fpt'),
            'cpsPrices' => $priceForStore('cellphone'),
            'summary' => [
                'best_now' => $bestNow ? [
                    'store' => $bestNow->store_name,
                    'price' => (int) round($bestNow->price),
                ] : null,
                'lowest_history' => $lowestHistory ? [
                    'store' => $lowestHistory->store_name,
                    'price' => (int) round($lowestHistory->price),
                    'date' => date('d/m/Y', strtotime($lowestHistory->price_date)),
                ] : null,
                'last_updated' => $latestDate ? date('d/m/Y', strtotime($latestDate)) : null,
            ],
        ]);
    }
}