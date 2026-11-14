<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PriceAlertController extends Controller
{
    /**
     * API lưu cảnh báo giá từ JS/modal nếu trang chi tiết cũ còn dùng /api/price-alerts.
     * Luồng chính hiện tại của bạn vẫn là TrackedProductController qua form theo dõi.
     */
    public function store(Request $request)
    {
        $request->merge([
            'target_price' => $this->normalizeMoney($request->input('target_price', $request->input('desired_price'))),
        ]);

        $validated = $request->validate([
            'target_price' => ['required', 'numeric', 'min:1'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_id' => ['nullable', 'integer'],
            'submitted_url' => ['nullable', 'url'],
        ], [
            'target_price.required' => 'Vui lòng nhập giá mong muốn.',
            'target_price.numeric' => 'Giá mong muốn phải là số.',
        ]);

        $storePrice = null;

        if (!empty($validated['variant_id'])) {
            $storePrice = DB::table('store_prices')
                ->where('variant_id', $validated['variant_id'])
                ->where('current_price', '>', 0)
                ->orderBy('current_price', 'asc')
                ->first();
        }

        if (!$storePrice && !empty($validated['product_id'])) {
            $storePrice = DB::table('store_prices')
                ->join('product_variants', 'store_prices.variant_id', '=', 'product_variants.id')
                ->where('product_variants.product_id', $validated['product_id'])
                ->where('store_prices.current_price', '>', 0)
                ->select('store_prices.*')
                ->orderBy('store_prices.current_price', 'asc')
                ->first();
        }

        if (!$storePrice && !empty($validated['submitted_url'])) {
            $storePrice = DB::table('store_prices')
                ->where('product_url', $validated['submitted_url'])
                ->orWhere('affiliate_url', $validated['submitted_url'])
                ->first();
        }

        if (!$storePrice) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy sản phẩm/phiên bản giá trong hệ thống.',
            ], 422);
        }

        $submittedUrl = $validated['submitted_url']
            ?? ($storePrice->affiliate_url ?: $storePrice->product_url);

        $existing = DB::table('price_alerts')
            ->where('user_id', Auth::id())
            ->where('variant_id', $storePrice->variant_id)
            ->where('alert_status', '!=', 'canceled')
            ->first();

        if ($existing) {
            DB::table('price_alerts')
                ->where('id', $existing->id)
                ->update([
                    'submitted_url' => $submittedUrl,
                    'target_price' => (float) $validated['target_price'],
                    'alert_status' => 'pending',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã cập nhật lại giá mong muốn cho sản phẩm đang theo dõi.',
            ]);
        }

        DB::table('price_alerts')->insert([
            'user_id' => Auth::id(),
            'variant_id' => $storePrice->variant_id,
            'submitted_url' => $submittedUrl,
            'target_price' => (float) $validated['target_price'],
            'alert_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã bật cảnh báo giá. Hệ thống sẽ gửi email khi có giá phù hợp.',
        ]);
    }

    private function normalizeMoney(string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', (string) $value);
    }
}
