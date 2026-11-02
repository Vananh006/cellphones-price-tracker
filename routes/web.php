<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackedProductController;
use App\Services\AffiliateLinkService;
use App\Services\ProductImageService;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\PriceAlertController;
use App\Http\Controllers\Admin\AffiliateController;
use App\Http\Controllers\Admin\FavoriteController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ExportController;



/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::middleware(['guest'])->group(function () {
    Route::view('/login', 'auth.login')->name('login.form');
    Route::view('/register', 'auth.register')->name('register.form');

    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::get('/xac-nhan-otp', [AuthController::class, 'showOtpForm'])->name('otp.form');
    Route::post('/xac-nhan-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::get('/huy-otp', [AuthController::class, 'cancelOtp'])->name('otp.cancel');

    Route::get('/quen-mat-khau', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/quen-mat-khau/gui-otp', [AuthController::class, 'sendResetOtp'])->name('password.email');
    Route::post('/quen-mat-khau/dat-lai', [AuthController::class, 'resetPasswordWithOtp'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| TRANG CHỦ
|--------------------------------------------------------------------------
*/
Route::get('/', function (Request $request) {
    $keyword = trim((string) $request->query('q', ''));
    $sort = (string) $request->query('sort', 'diff_desc');

    $query = DB::table('products')
        ->select(
            'products.id',
            'products.product_name',
            'products.brand',
            'products.thumbnail_url',
            'products.view_count',
            'products.created_at',
            'products.updated_at'
        );

    if ($keyword !== '') {
        $query->where(function ($q) use ($keyword) {
            $q->where('products.product_name', 'like', '%' . $keyword . '%')
                ->orWhere('products.brand', 'like', '%' . $keyword . '%')
                ->orWhereExists(function ($sub) use ($keyword) {
                    $sub->select(DB::raw(1))
                        ->from('product_variants')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->where(function ($variantQuery) use ($keyword) {
                            $variantQuery->where('product_variants.storage', 'like', '%' . $keyword . '%')
                                ->orWhere('product_variants.color', 'like', '%' . $keyword . '%');
                        });
                });
        });
    }

    $products = $query->get();

    foreach ($products as $product) {
        $prices = DB::table('store_prices as sp')
            ->join('product_variants as pv', 'pv.id', '=', 'sp.variant_id')
            ->join('stores as s', 's.id', '=', 'sp.store_id')
            ->where('pv.product_id', $product->id)
            ->where('sp.current_price', '>', 0)
            ->where('sp.in_stock', 1)
            ->select(
                'sp.id as store_price_id',
                'sp.variant_id',
                'sp.store_id',
                'sp.current_price',
                'sp.original_price',
                'sp.product_url',
                'sp.affiliate_url',
                'sp.in_stock',
                'sp.updated_at as price_updated_at',
                'pv.storage',
                'pv.color',
                's.store_name'
            )
            ->get();

        $bestPrice = $prices->sortBy('current_price')->first();
        $highestPrice = $prices->sortByDesc('current_price')->first();

        $product->current_price = $bestPrice->current_price ?? null;
        $product->original_price = $bestPrice->original_price ?? null;
        $product->store_name = $bestPrice->store_name ?? '';
        $product->product_url = $bestPrice->product_url ?? '#';
        $product->affiliate_url = $bestPrice->affiliate_url ?? null;
        $product->buy_url = $bestPrice
            ? (($bestPrice->affiliate_url ?? null) ?: ($bestPrice->product_url ?? '#'))
            : '#';

        $product->variant_id = $bestPrice->variant_id ?? null;
        $product->storage = $bestPrice->storage ?? null;
        $product->color = $bestPrice->color ?? null;
        $product->variant_storage = $product->storage;
        $product->variant_color = $product->color;

        $product->lowest_price = $bestPrice->current_price ?? null;
        $product->highest_price = $highestPrice->current_price ?? null;

        if ($bestPrice && $highestPrice) {
            $product->price_gap = max(0, (float) $highestPrice->current_price - (float) $bestPrice->current_price);
        } else {
            $product->price_gap = 0;
        }

        $product->last_price_updated_at = $bestPrice->price_updated_at ?? $product->updated_at ?? $product->created_at;

        $product->final_image = ProductImageService::firstImage(
            $product->product_name ?? '',
            $product->storage ?? '',
            $product->color ?? ''
        );

        $product->thumbnail_url = $product->final_image;
    }

    $products = match ($sort) {
        'price_asc' => $products->sortBy(fn ($item) => $item->current_price ?? PHP_INT_MAX),
        'price_desc' => $products->sortByDesc(fn ($item) => $item->current_price ?? 0),
        'updated_desc' => $products->sortByDesc(fn ($item) => strtotime((string) ($item->last_price_updated_at ?? '1970-01-01'))),
        'name_asc' => $products->sortBy(fn ($item) => mb_strtolower($item->product_name ?? '')),
        default => $products->sortByDesc(fn ($item) => $item->price_gap ?? 0),
    };

    $products = $products->values();

    return view('home', [
        'products' => $products,
        'keyword' => $keyword,
        'sort' => $sort,
    ]);
})->name('home');

// Trang danh sách sản phẩm phụ, nếu view cũ còn gọi route products.index.
Route::get('/san-pham', fn() => redirect()->route('home'))->name('products.index');

/*
|--------------------------------------------------------------------------
| API: LỊCH SỬ GIÁ + CẢNH BÁO GIÁ
|--------------------------------------------------------------------------
*/
Route::get('/api/price-history/{product}', [PriceController::class, 'getHistory'])
    ->name('api.price-history');

Route::post('/api/price-alerts', [PriceAlertController::class, 'store'])
    ->middleware('auth')
    ->name('api.price-alerts.store');

/*
|--------------------------------------------------------------------------
| CHI TIẾT SẢN PHẨM
|--------------------------------------------------------------------------
*/
$productDetailHandler = function ($id, AffiliateLinkService $affiliateService) {
    $product = DB::table('products')->where('id', $id)->first();

    if (!$product) {
        abort(404, 'Không tìm thấy sản phẩm.');
    }

    $product->name = $product->product_name;

    $variants = DB::table('product_variants')
        ->where('product_id', $id)
        ->orderBy('storage')
        ->orderBy('color')
        ->get();

    $variantData = [];
    $allPrices = collect();

    foreach ($variants as $variant) {
        $prices = DB::table('store_prices as sp')
            ->join('stores as s', 'sp.store_id', '=', 's.id')
            ->where('sp.variant_id', $variant->id)
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
                'sp.in_stock',
                's.store_name'
            )
            ->orderBy('sp.current_price')
            ->get();

        foreach ($prices as $price) {
            if (empty($price->affiliate_url)) {
                $price->affiliate_url = $affiliateService->make($price->product_url, $price->store_name);
            }

            $price->final_url = $price->affiliate_url ?: $price->product_url;
            $allPrices->push($price);
        }

        $galleryImages = ProductImageService::gallery($product->product_name, $variant->storage, $variant->color);

        if (empty($galleryImages)) {
            $galleryImages[] = ProductImageService::firstImage($product->product_name);
        }

        $variantData[] = [
            'id' => $variant->id,
            'product_id' => $variant->product_id,
            'storage' => $variant->storage,
            'color' => $variant->color,
            'sku' => $variant->sku ?? null,
            'image_url' => $galleryImages[0] ?? null,
            'gallery_images' => $galleryImages,
            'prices' => $prices->values(),
            'lowest_price' => $prices->first()?->current_price,
            'lowest_store' => $prices->first()?->store_name,
        ];
    }

    $lowestPrice = $allPrices->sortBy('current_price')->first();
    $product->current_price = $lowestPrice?->current_price;
    $product->lowest_price = $lowestPrice?->current_price;
    $product->original_price = $lowestPrice?->original_price;
    $product->store_name = $lowestPrice?->store_name;
    $product->product_url = $lowestPrice?->product_url;
    $product->affiliate_url = $lowestPrice?->affiliate_url;
    $product->buy_url = $lowestPrice?->final_url;

    $productGalleryImages = ProductImageService::gallery($product->product_name);
    if (empty($productGalleryImages)) {
        $productGalleryImages[] = ProductImageService::firstImage($product->product_name);
    }
    $product->thumbnail_url = $productGalleryImages[0] ?? ProductImageService::firstImage($product->product_name);

    return view('products.show', [
        'product' => $product,
        'variants' => $variants,
        'variantData' => $variantData,
        'allPrices' => $allPrices->values(),
        'lowestPrice' => $lowestPrice,
        'productGalleryImages' => $productGalleryImages,
    ]);
};

Route::get('/san-pham/{id}', $productDetailHandler)->name('products.show');

// Alias để các file cũ còn gọi route('product.detail', $id) vẫn không bị lỗi.
Route::get('/chi-tiet-san-pham/{id}', fn($id) => redirect()->route('products.show', $id))->name('product.detail');

/*
|--------------------------------------------------------------------------
| USER: HỒ SƠ + DANH SÁCH THEO DÕI
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.changePassword');

    Route::get('/theo-doi-san-pham', [TrackedProductController::class, 'index'])->name('tracking.index');
    Route::get('/them-gia-mong-muon', [TrackedProductController::class, 'create'])->name('tracking.create');
    Route::post('/luu-gia-mong-muon', [TrackedProductController::class, 'store'])->name('tracking.store');
    Route::delete('/huy-theo-doi/{id}', [TrackedProductController::class, 'destroy'])->name('tracking.destroy');
});

// Hai link này được bấm trực tiếp từ email, không nên đặt trong middleware auth.
Route::get('/theo-doi/tiep-tuc-email/{alert}', [TrackedProductController::class, 'continueFromEmail'])->name('tracking.continueFromEmail');
Route::get('/theo-doi/huy-email/{alert}', [TrackedProductController::class, 'cancelFromEmail'])->name('tracking.cancelFromEmail');

/*
|--------------------------------------------------------------------------
| ADMIN + TOOL TẠO LINK AFFILIATE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    if (class_exists(\App\Http\Controllers\Admin\DashboardController::class)) {
        Route::get('/admin/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');
    } else {
        Route::get('/admin/dashboard', fn() => 'Admin dashboard')->name('admin.dashboard');
    }

    if (class_exists(\App\Http\Controllers\Admin\ProductController::class)) {
        Route::resource('admin/products', \App\Http\Controllers\Admin\ProductController::class)->names('admin.products');
    }
    if (class_exists(\App\Http\Controllers\Admin\ProductLinkController::class)) {
        Route::resource('admin/product-links', \App\Http\Controllers\Admin\ProductLinkController::class)->names('admin.product-links');
    }
    if (class_exists(\App\Http\Controllers\Admin\UserController::class)) {
        Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users');
    }
    if (class_exists(\App\Http\Controllers\Admin\PriceAlertController::class)) {
        Route::resource('admin/price-alerts', \App\Http\Controllers\Admin\PriceAlertController::class)->names('admin.price-alerts');
    }
    if (class_exists(\App\Http\Controllers\Admin\FavoriteController::class)) {
        Route::resource('admin/favorites', \App\Http\Controllers\Admin\FavoriteController::class)->names('admin.favorites');
    }
    Route::prefix('admin')->group(function () {
        Route::get('/affiliate-links', [AffiliateController::class, 'index'])
            ->name('admin.affiliate.index');
    });
    Route::prefix('admin')->group(function () {
        Route::get('/report', [ReportController::class, 'index'])
            ->name('admin.report');
    });
    Route::get('/admin/export', [ExportController::class, 'export'])->name('admin.export');

    Route::get('/tool/tao-link-affiliate', function (AffiliateLinkService $affiliateService) {
        $rows = DB::table('store_prices as sp')
            ->join('stores as s', 'sp.store_id', '=', 's.id')
            ->whereNotNull('sp.product_url')
            ->select('sp.id', 'sp.product_url', 'sp.affiliate_url', 's.store_name')
            ->get();

        $updated = 0;
        $skipped = 0;
        $samples = [];

        foreach ($rows as $row) {
            if (!empty($row->affiliate_url)) {
                $skipped++;
                continue;
            }

            $affiliateUrl = $affiliateService->make($row->product_url, $row->store_name);
            if (!$affiliateUrl) {
                $skipped++;
                continue;
            }

            DB::table('store_prices')->where('id', $row->id)->update(['affiliate_url' => $affiliateUrl]);
            $updated++;

            if (count($samples) < 5) {
                $samples[] = [
                    'id' => $row->id,
                    'product_url' => $row->product_url,
                    'affiliate_url' => $affiliateUrl,
                ];
            }
        }

        return response()->json([
            'message' => 'Tạo link affiliate hoàn tất.',
            'updated' => $updated,
            'skipped' => $skipped,
            'samples' => $samples,
        ]);
    })->name('affiliate.generate');

    Route::get('/tracker', function () {
        $stats = [
            'products' => DB::table('products')->count(),
            'variants' => DB::table('product_variants')->count(),
            'store_prices' => DB::table('store_prices')->count(),
            'pending_links' => DB::table('store_prices')->whereNull('current_price')->orWhere('current_price', '<=', 0)->count(),
            'last_updated_at' => DB::table('store_prices')->max('updated_at'),
        ];

        $recentPrices = DB::table('price_history as ph')
            ->join('store_prices as sp', 'ph.store_price_id', '=', 'sp.id')
            ->join('product_variants as pv', 'sp.variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->join('stores as s', 'sp.store_id', '=', 's.id')
            ->select('p.product_name', 'pv.storage', 'pv.color', 's.store_name', 'ph.current_price', 'ph.original_price', 'ph.crawled_at')
            ->orderByDesc('ph.crawled_at')
            ->limit(20)
            ->get();

        return view('tracker.index', compact('stats', 'recentPrices'));
    })->name('tracker.index');

    Route::post('/tracker/check', function () {
        return redirect()->route('tracker.index')->with('success', 'Trang kiểm thử thủ công đã được giữ lại. Crawl tự động chính chạy bằng lệnh php artisan prices:run-cycle hoặc scheduler.');
    })->name('tracker.check');
});
