@php
    $formatVnd = function ($price) {
        if ($price === null || $price === '' || (int) $price <= 0) {
            return 'Đang cập nhật';
        }

        return number_format((int) $price, 0, ',', '.') . 'đ';
    };

    $imageUrl = function ($url) {
        if (empty($url)) {
            return 'https://via.placeholder.com/420x420?text=Chưa+có+ảnh';
        }

        return str_starts_with($url, 'http') ? $url : asset($url);
    };

    $tracked_items = $tracked_items ?? collect();
@endphp

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sản phẩm đang theo dõi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #f12020;
            --primary-orange: #ff7a00;
            --soft-orange: #fff0e6;
            --card-border: rgba(255, 122, 0, .16);
            --text-muted: #6b7280;
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #fff4ed 0%, #ffffff 42%, #fff7f0 100%);
            color: #1f1f1f;
        }

        a { text-decoration: none; }
        .fw-black { font-weight: 950; }

        .top-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1f1f1f;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            box-shadow: 0 12px 28px rgba(241, 32, 32, .26);
        }

        .brand strong { display: block; line-height: 1.05; font-size: 18px; }
        .brand small { color: var(--text-muted); font-weight: 600; }

        .nav-pill {
            padding: 9px 16px;
            border-radius: 999px;
            color: #7c2d12;
            font-weight: 800;
            border: 0;
            background: transparent;
        }

        .nav-pill:hover {
            color: var(--primary-red);
            background: var(--soft-orange);
        }

        .btn-main {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            color: #fff;
            font-weight: 900;
            border-radius: 999px;
            padding: 11px 17px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            box-shadow: 0 14px 30px rgba(241, 32, 32, .22);
        }

        .btn-main:hover { color: #fff; filter: brightness(.96); }

        .btn-outline-main {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid #ffb088;
            color: #c2410c;
            background: #fff7ed;
            font-weight: 900;
            border-radius: 999px;
            padding: 10px 15px;
        }

        .soft-card,
        .tracked-card,
        .summary-card {
            border: 1px solid var(--card-border);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 18px 45px rgba(127, 29, 29, .08);
        }

        .soft-card { border-radius: 30px; }
        .tracked-card { border-radius: 28px; }
        .summary-card { border-radius: 24px; }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            color: #e04700;
            background: var(--soft-orange);
            font-weight: 900;
            font-size: 13px;
        }

        .product-thumb {
            width: 125px;
            height: 125px;
            object-fit: contain;
            border-radius: 24px;
            background: linear-gradient(135deg, #fff, #fff1e6);
            padding: 12px;
        }

        .price {
            color: #e11d2e;
            font-size: 24px;
            font-weight: 950;
        }

        .mini-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 800;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
        }

        .status-pending { color: #9a3412; background: #fff7ed; }
        .status-triggered { color: #166534; background: #dcfce7; }
        .status-canceled { color: #334155; background: #e2e8f0; }

        @media (max-width: 768px) {
            .product-thumb { width: 100%; height: 220px; }
            .brand small { display: none; }
        }
    </style>
</head>
<body>
<nav class="top-nav py-3">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
            <span>
                <strong>Phone Price Tracker</strong>
                <small>Danh sách sản phẩm đang theo dõi</small>
            </span>
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a href="{{ route('home') }}" class="nav-pill"><i class="fa-solid fa-house me-1"></i> Trang chủ</a>
            <a href="{{ route('profile.index') }}" class="nav-pill"><i class="fa-solid fa-user me-1"></i> Hồ sơ</a>

            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}" class="nav-pill"><i class="fa-solid fa-screwdriver-wrench me-1"></i> Admin</a>
            @endif

            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="nav-pill"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4 py-lg-5">
    @if(session('success'))
        <div class="alert alert-success rounded-4 border-0 shadow-sm">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-4 border-0 shadow-sm">
            @foreach($errors->all() as $error)
                <div><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="soft-card p-4 p-lg-5 mb-4">
        <div class="d-flex align-items-end justify-content-between flex-wrap gap-3">
            <div>
                <span class="section-badge"><i class="fa-solid fa-bell"></i> Theo dõi giá</span>
                <h1 class="fw-black mt-3 mb-2">Sản phẩm bạn đang theo dõi</h1>
                <p class="text-secondary fw-semibold mb-0">
                    Các sản phẩm được thêm từ nút <b>Theo dõi giá</b> ở trang chủ hoặc trang chi tiết sản phẩm.
                </p>
            </div>
            <a href="{{ route('home') }}" class="btn-main"><i class="fa-solid fa-plus"></i> Thêm sản phẩm</a>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="summary-card p-4 h-100">
                <div class="mini-label">Tổng sản phẩm theo dõi</div>
                <div class="fw-black fs-2">{{ $tracked_items->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card p-4 h-100">
                <div class="mini-label">Đã đạt giá mong muốn</div>
                <div class="fw-black fs-2 text-success">
                    {{ $tracked_items->filter(fn($item) => $item->current_price !== null && $item->target_price !== null && $item->current_price <= $item->target_price)->count() }}
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card p-4 h-100">
                <div class="mini-label">Đang chờ giảm giá</div>
                <div class="fw-black fs-2 text-danger">
                    {{ $tracked_items->filter(fn($item) => $item->current_price === null || $item->target_price === null || $item->current_price > $item->target_price)->count() }}
                </div>
            </div>
        </div>
    </div>

    @if($tracked_items->isEmpty())
        <div class="soft-card p-5 text-center">
            <i class="fa-regular fa-bell-slash fa-3x text-warning mb-3"></i>
            <h4 class="fw-black">Bạn chưa theo dõi sản phẩm nào</h4>
            <p class="text-secondary">Quay lại trang chủ hoặc trang chi tiết sản phẩm để thêm sản phẩm vào danh sách theo dõi.</p>
            <a href="{{ route('home') }}" class="btn-main"><i class="fa-solid fa-mobile-screen"></i> Xem sản phẩm</a>
        </div>
    @else
        <div class="row g-4">
            @foreach($tracked_items as $item)
                @php
                    $targetPrice = $item->target_price ?? $item->desired_price ?? null;
                    $priceGap = $item->price_gap ?? (($item->current_price !== null && $targetPrice !== null) ? ((float) $item->current_price - (float) $targetPrice) : null);
                    $detailUrl = $item->detail_url ?? (isset($item->product_id) ? route('products.show', $item->product_id) : route('home'));
                    $buyUrl = $item->buy_url ?? $item->affiliate_url ?? $item->product_url ?? $item->submitted_url ?? '#';
                    $reached = $item->current_price !== null && $targetPrice !== null && (float) $item->current_price <= (float) $targetPrice;
                    $statusClass = $reached ? 'status-triggered' : 'status-pending';
                    $statusText = $reached ? 'Đã đạt giá mong muốn' : 'Đang theo dõi';
                @endphp
                <div class="col-12">
                    <div class="tracked-card p-3 p-lg-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-auto">
                                <img src="{{ $imageUrl($item->thumbnail_url) }}" class="product-thumb" alt="{{ $item->product_name }}">
                            </div>

                            <div class="col-md">
                                <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap mb-2">
                                    <div>
                                        <h3 class="h5 fw-black mb-1">{{ $item->product_name }}</h3>
                                        <div class="text-secondary fw-semibold">
                                            {{ $item->storage ?? 'Dung lượng chưa rõ' }}
                                            @if(!empty($item->color))
                                                - {{ $item->color }}
                                            @endif
                                        </div>
                                    </div>

                                    <span class="status-pill {{ $statusClass }}">
                                        <i class="fa-solid {{ $reached ? 'fa-circle-check' : 'fa-clock' }}"></i>
                                        {{ $statusText }}
                                    </span>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-sm-4">
                                        <div class="mini-label">Giá hiện tại tốt nhất</div>
                                        <div class="price">{{ $formatVnd($item->current_price) }}</div>
                                        <div class="small text-secondary">{{ $item->store_name }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mini-label">Giá mong muốn</div>
                                        <div class="fw-black fs-5">{{ $formatVnd($targetPrice) }}</div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="mini-label">Chênh lệch</div>
                                        @if($priceGap === null)
                                            <div class="fw-black fs-5 text-secondary">Đang cập nhật</div>
                                        @elseif($priceGap <= 0)
                                            <div class="fw-black fs-5 text-success">Đã thấp hơn {{ $formatVnd(abs($priceGap)) }}</div>
                                        @else
                                            <div class="fw-black fs-5 text-danger">Còn cao hơn {{ $formatVnd($priceGap) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-auto">
                                <div class="d-grid gap-2">
                                    <a href="{{ $detailUrl }}" class="btn-outline-main">
                                        <i class="fa-solid fa-circle-info"></i> Chi tiết
                                    </a>
                                    @if(!empty($buyUrl) && $buyUrl !== '#')
                                        <a href="{{ $buyUrl }}" target="_blank" rel="noopener" class="btn-main">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Tới nơi bán
                                        </a>
                                    @endif
                                    <form action="{{ route('tracking.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn muốn hủy theo dõi sản phẩm này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border rounded-pill fw-bold w-100 text-danger">
                                            <i class="fa-solid fa-trash-can me-1"></i> Hủy theo dõi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
