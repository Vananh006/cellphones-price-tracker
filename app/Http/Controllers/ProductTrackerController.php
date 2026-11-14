<?php

namespace App\Http\Controllers;

use App\Models\AffiliateClickLog;
use App\Models\PriceAlert;
use App\Models\StorePrice;
use App\Services\PriceCompareService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ProductTrackerController extends Controller
{
    public function index(Request $request): View
    {
        // Trang chủ chỉ hiện các sản phẩm đã có giá thật sau khi crawl, tránh màn hình bị đầy bởi link chờ crawl.
        $storePrices = StorePrice::query()
            ->with(['store', 'variant.product'])
            ->whereNotNull('current_price')
            ->where('current_price', '>', 1000000)
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $alerts = PriceAlert::query()
            ->with(['user', 'variant.product'])
            ->latest()
            ->limit(8)
            ->get();

        $stats = [
            'total_links' => StorePrice::count(),
            'crawled_links' => StorePrice::whereNotNull('current_price')->where('current_price', '>', 1000000)->count(),
            'pending_links' => StorePrice::where(function ($query) {
                $query->whereNull('current_price')->orWhere('current_price', '<=', 1000000);
            })->count(),
            'last_updated_at' => StorePrice::whereNotNull('current_price')->max('updated_at'),
        ];

        return view('tracker.index', compact('storePrices', 'alerts', 'stats'));
    }

    public function store(Request $request, PriceCompareService $service): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'email' => ['required', 'email', 'max:150'],
            'target_price' => ['nullable', 'numeric', 'min:1000'],
        ], [
            'url.required' => 'Bạn cần nhập link sản phẩm.',
            'url.url' => 'Link sản phẩm chưa đúng định dạng URL.',
            'email.required' => 'Bạn cần nhập email để lưu yêu cầu theo dõi giá.',
            'target_price.numeric' => 'Giá mong muốn phải là số.',
        ]);

        try {
            $result = $service->checkAndSave(
                $validated['url'],
                $validated['email'],
                isset($validated['target_price']) ? (float) $validated['target_price'] : null,
            );

            return back()->with('result', [
                'is_ok' => $result['is_ok'],
                'message' => $result['message'],
                'crawl_status' => $result['crawl_status'],
                'crawl_errors' => $result['crawl_errors'] ?? [],
                'product_name' => $result['store_price']->variant?->product?->product_name,
                'store_name' => $result['store_price']->store?->store_name,
                'current_price' => (float) $result['store_price']->current_price,
                'target_price' => (float) $result['target_price'],
                'alert_threshold' => (float) ($result['alert_threshold'] ?? 0),
                'price_gap_percent' => $result['price_gap_percent'] ?? null,
                'affiliate_url' => route('affiliate.redirect', $result['store_price']),
                'comparison' => $result['comparison'],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Chưa xử lý được link này: '.$exception->getMessage());
        }
    }

    public function refresh(StorePrice $storePrice, PriceCompareService $service): RedirectResponse
    {
        try {
            $service->refreshStorePrice($storePrice);
            $service->refreshStorePricesForVariant((int) $storePrice->variant_id, (int) $storePrice->id);

            return back()->with('success', 'Đã crawl lại giá mới cho sản phẩm và các link cùng biến thể nếu có. Giá đã được lưu vào store_prices và price_history.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Crawl lại thất bại: '.$exception->getMessage());
        }
    }

    public function redirectAffiliate(Request $request, StorePrice $storePrice): RedirectResponse
    {
        AffiliateClickLog::create([
            'store_price_id' => $storePrice->id,
            'user_id' => null,
            'ip_address' => $request->ip() ?: '0.0.0.0',
            'clicked_at' => now(),
        ]);

        return redirect()->away($storePrice->affiliate_url ?: $storePrice->product_url);
    }
}
