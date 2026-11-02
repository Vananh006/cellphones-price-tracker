<?php

namespace App\Http\Controllers;

use App\Mail\PriceDropAlertMail;
use App\Models\PriceAlert;
use App\Models\StorePrice;
use App\Services\PriceCompareService;
use App\Services\ProductImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

class TrackedProductController extends Controller
{
    public function index()
    {
        $tracked_items = DB::table('price_alerts as pa')
            ->join('product_variants as pv', 'pa.variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->where('pa.user_id', Auth::id())
            ->where('pa.alert_status', '!=', 'canceled')
            ->select(
                'pa.id',
                'pa.user_id',
                'pa.variant_id',
                'pa.submitted_url',
                'pa.target_price',
                'pa.alert_status',
                'pa.created_at',
                'pa.updated_at',
                'pv.product_id',
                'pv.storage',
                'pv.color',
                'p.product_name',
                'p.thumbnail_url'
            )
            ->orderByDesc('pa.created_at')
            ->get();

        foreach ($tracked_items as $item) {
            $best = $this->bestStorePriceQuery((int) $item->variant_id)->first();

            $item->current_price = $best?->current_price !== null ? (float) $best->current_price : null;
            $item->store_name = $best->store_name ?? null;
            $item->product_url = $best->product_url ?? $item->submitted_url;
            $item->affiliate_url = $best->affiliate_url ?? null;
            $item->buy_url = $best?->affiliate_url ?: ($best?->product_url ?: $item->submitted_url);
            $item->product_link = $item->buy_url;
            $item->target_price = $item->target_price !== null ? (float) $item->target_price : null;
            $item->desired_price = $item->target_price;
            $item->price_gap = ($item->current_price !== null && $item->target_price !== null)
                ? ((float) $item->current_price - (float) $item->target_price)
                : null;

            $item->detail_url = Route::has('products.show')
                ? route('products.show', $item->product_id)
                : (Route::has('product.detail') ? route('product.detail', $item->product_id) : url('/'));

            // Không lấy ảnh từ DB nữa. Ảnh lấy từ public/product_image theo tên SP + màu + GB.
            $item->thumbnail_url = ProductImageService::firstImage(
                $item->product_name,
                $item->storage,
                $item->color
            );
        }

        // Giữ thêm biến cũ để các view cũ không bị lỗi nếu còn dùng $trackedProducts.
        $trackedProducts = $tracked_items;

        return view('profile.tracking.index', compact('tracked_items', 'trackedProducts'));
    }

    public function create(Request $request)
    {
        $prefill = [
            'product_id' => $request->query('product_id'),
            'variant_id' => $request->query('variant_id'),
            'submitted_url' => $request->query('submitted_url') ?: $request->query('product_url'),
            'product_name' => $request->query('product_name'),
            'storage' => $request->query('storage'),
            'color' => $request->query('color'),
            'current_price' => $request->query('current_price'),
            'store_name' => $request->query('store_name'),
        ];

        if (!empty($prefill['variant_id'])) {
            $variant = DB::table('product_variants as pv')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->where('pv.id', $prefill['variant_id'])
                ->select('pv.*', 'p.product_name')
                ->first();

            $best = $this->bestStorePriceQuery((int) $prefill['variant_id'])->first();

            $prefill['product_id'] = $prefill['product_id'] ?: ($variant->product_id ?? null);
            $prefill['product_name'] = $prefill['product_name'] ?: ($variant->product_name ?? null);
            $prefill['storage'] = $prefill['storage'] ?: ($variant->storage ?? null);
            $prefill['color'] = $prefill['color'] ?: ($variant->color ?? null);
            $prefill['current_price'] = $prefill['current_price'] ?: ($best->current_price ?? null);
            $prefill['store_name'] = $prefill['store_name'] ?: ($best->store_name ?? null);
            $prefill['submitted_url'] = $prefill['submitted_url'] ?: (($best->affiliate_url ?? null) ?: ($best->product_url ?? null));
        }

        return view('profile.tracking.create', compact('prefill'));
    }

    public function store(Request $request, PriceCompareService $priceCompareService)
    {
        $request->validate([
            'submitted_url' => ['required', 'string', 'max:1000'],
            'desired_price' => ['nullable', 'numeric', 'min:1'],
            'target_price' => ['nullable', 'numeric', 'min:1'],
            'variant_id' => ['nullable', 'integer'],
        ], [
            'submitted_url.required' => 'Vui lòng chọn hoặc nhập link sản phẩm.',
            'desired_price.numeric' => 'Giá mong muốn phải là số.',
            'target_price.numeric' => 'Giá mong muốn phải là số.',
        ]);

        $targetPrice = (float) ($request->input('target_price') ?: $request->input('desired_price'));
        if ($targetPrice <= 0) {
            return back()->withInput()->withErrors(['desired_price' => 'Vui lòng nhập giá mong muốn hợp lệ.']);
        }

        $variantId = $request->filled('variant_id') ? (int) $request->input('variant_id') : null;
        $submittedUrl = trim((string) $request->input('submitted_url'));

        // Nếu form được mở từ trang chi tiết, variant_id là nguồn chính xác nhất.
        if (!$variantId) {
            $matched = StorePrice::where('product_url', $submittedUrl)
                ->orWhere('affiliate_url', $submittedUrl)
                ->first();
            $variantId = $matched?->variant_id;
        }

        // Fallback cho trường hợp nhập link gốc thủ công.
        if (!$variantId) {
            try {
                $result = $priceCompareService->checkAndSave($submittedUrl, Auth::user()->email, $targetPrice);
                return redirect()->route('tracking.index')->with('success', $result['mail_sent']
                    ? 'Sản phẩm đã đạt giá mong muốn, hệ thống đã gửi email thông báo.'
                    : 'Đã lưu sản phẩm vào danh sách theo dõi. Hệ thống sẽ tự kiểm tra giá định kỳ.');
            } catch (\Throwable $e) {
                return back()->withInput()->withErrors(['submitted_url' => 'Không xác định được phiên bản sản phẩm từ link này. Bạn nên chọn màu và GB ở trang chi tiết sản phẩm rồi bấm Theo dõi phiên bản này.']);
            }
        }

        $bestStorePrice = StorePrice::with(['store', 'variant.product'])
            ->where('variant_id', $variantId)
            ->whereNotNull('current_price')
            ->where('current_price', '>', 0)
            ->orderBy('current_price')
            ->first();

        $isReached = $bestStorePrice && (float) $bestStorePrice->current_price <= $targetPrice;

        $alert = PriceAlert::create([
            'user_id' => Auth::id(),
            'variant_id' => $variantId,
            'submitted_url' => $submittedUrl,
            'target_price' => $targetPrice,
            'alert_status' => $isReached ? 'triggered' : 'pending',
        ]);

        if ($isReached) {
            Mail::to(Auth::user()->email)->send(new PriceDropAlertMail($alert, $bestStorePrice));
            return redirect()->route('tracking.index')->with('success', 'Giá hiện tại đã thấp hơn hoặc bằng giá mong muốn. Hệ thống đã gửi email thông báo cho bạn.');
        }

        return redirect()->route('tracking.index')->with('success', 'Đã lưu sản phẩm vào danh sách theo dõi. Khi giá thấp hơn hoặc bằng giá mong muốn, hệ thống sẽ gửi email cho bạn.');
    }

    public function destroy($id)
    {
        PriceAlert::where('id', $id)
            ->where('user_id', Auth::id())
            ->update(['alert_status' => 'canceled']);

        return redirect()->route('tracking.index')->with('success', 'Đã hủy theo dõi sản phẩm.');
    }

    public function continueFromEmail(PriceAlert $alert)
    {
        if ($alert->alert_status === 'canceled') {
            return redirect()->route('home')->with('error', 'Theo dõi này đã bị hủy trước đó.');
        }

        // Giữ nguyên target_price, chỉ reset mốc 3 ngày để hệ thống tiếp tục theo dõi.
        $alert->forceFill([
            'alert_status' => 'pending',
            'updated_at' => now(),
        ])->save();

        return redirect()->route('tracking.index')->with('success', 'Đã tiếp tục theo dõi sản phẩm. Giá mong muốn của bạn được giữ nguyên.');
    }

    public function cancelFromEmail(PriceAlert $alert)
    {
        $alert->update(['alert_status' => 'canceled']);

        return redirect()->route('home')->with('success', 'Đã hủy theo dõi sản phẩm từ email.');
    }

    private function bestStorePriceQuery(int $variantId)
    {
        return DB::table('store_prices as sp')
            ->join('stores as s', 'sp.store_id', '=', 's.id')
            ->where('sp.variant_id', $variantId)
            ->whereNotNull('sp.current_price')
            ->where('sp.current_price', '>', 0)
            ->select(
                'sp.id',
                'sp.variant_id',
                'sp.store_id',
                'sp.current_price',
                'sp.original_price',
                'sp.product_url',
                'sp.affiliate_url',
                'sp.updated_at',
                's.store_name'
            )
            ->orderBy('sp.current_price', 'asc');
    }
}
