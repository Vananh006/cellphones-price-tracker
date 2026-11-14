<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StorePrice;
use App\Services\PriceCompareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProductTrackerApiController extends Controller
{
    public function check(Request $request, PriceCompareService $service): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'email' => ['required', 'email', 'max:150'],
            'target_price' => ['nullable', 'numeric', 'min:1000'],
        ]);

        try {
            $result = $service->checkAndSave(
                $validated['url'],
                $validated['email'],
                isset($validated['target_price']) ? (float) $validated['target_price'] : null,
            );

            $storePrice = $result['store_price'];

            return response()->json([
                'success' => true,
                'is_ok' => $result['is_ok'],
                'message' => $result['message'],
                'crawl_status' => $result['crawl_status'],
                'data' => [
                    'store_price_id' => $storePrice->id,
                    'product_name' => $storePrice->variant?->product?->product_name,
                    'store_name' => $storePrice->store?->store_name,
                    'current_price' => (float) $storePrice->current_price,
                    'target_price' => (float) $result['target_price'],
                    'alert_threshold' => (float) ($result['alert_threshold'] ?? 0),
                    'price_gap_percent' => $result['price_gap_percent'] ?? null,
                    'product_url' => $storePrice->product_url,
                    'affiliate_url' => $storePrice->affiliate_url,
                    'comparison' => $result['comparison'],
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function index(): JsonResponse
    {
        $items = StorePrice::query()
            ->with(['store', 'variant.product'])
            ->whereNotNull('current_price')
            ->where('current_price', '>', 1000000)
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(function (StorePrice $storePrice) {
                return [
                    'id' => $storePrice->id,
                    'store_name' => $storePrice->store?->store_name,
                    'product_name' => $storePrice->variant?->product?->product_name,
                    'variant_name' => $storePrice->variant?->display_name,
                    'current_price' => (float) $storePrice->current_price,
                    'display_price' => number_format((float) $storePrice->current_price, 0, ',', '.').'đ',
                    'original_price' => $storePrice->original_price ? (float) $storePrice->original_price : null,
                    'updated_at' => optional($storePrice->updated_at)->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }
}
