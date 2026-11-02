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

    $products = $products ?? collect();
@endphp

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>So sánh và theo dõi giá điện thoại</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #f12020;
            --primary-orange: #ff7a00;
            --soft-bg: #fff4ed;
            --soft-orange: #fff0e6;
            --text-main: #1f1f1f;
            --text-muted: #6b7280;
            --card-border: rgba(255, 122, 0, .16);
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text-main);
            background: linear-gradient(180deg, #fff4ed 0%, #ffffff 42%, #fff7f0 100%);
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
            color: var(--text-main);
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
            padding: 12px 18px;
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
            padding: 11px 16px;
        }

        .btn-outline-main:hover {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
        }

        .hero-wrap { padding: 28px 0 0; }

        .banner-img {
            width: 100%;
            height: 390px;
            object-fit: cover;
            border-radius: 30px;
            box-shadow: 0 24px 56px rgba(127, 29, 29, .14);
            background: linear-gradient(135deg, #ffefe4, #fff);
        }

        .soft-card,
        .stat-card,
        .product-card,
        .filter-card {
            border: 1px solid var(--card-border);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 18px 45px rgba(127, 29, 29, .08);
        }

        .soft-card { border-radius: 28px; }
        .stat-card { border-radius: 24px; }
        .filter-card { border-radius: 999px; }

        .stat-icon {
            width: 58px;
            height: 58px;
            border-radius: 20px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 22px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
        }

        .stat-card strong { font-size: 28px; font-weight: 950; }

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

        .section-title { font-weight: 950; letter-spacing: -1px; }

        .model-chip {
            border: 1px solid #ffd4bd;
            color: #9a3412;
            background: #fff;
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 900;
        }

        .model-chip:hover,
        .model-chip.active {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
        }

        .product-card {
            position: relative;
            height: 100%;
            border-radius: 28px;
            transition: .22s ease;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 26px 54px rgba(127, 29, 29, .13);
        }

        .product-img-wrap {
            height: 235px;
            display: grid;
            place-items: center;
            border-radius: 24px;
            background: linear-gradient(135deg, #fff, #fff1e6);
            overflow: hidden;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 18px;
            transition: .22s ease;
        }

        .product-card:hover .product-img { transform: scale(1.04); }

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

        .deal-ribbon {
            position: absolute;
            top: 16px;
            left: 16px;
            z-index: 2;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            color: #fff;
            border-radius: 999px;
            padding: 8px 13px;
            font-size: 12px;
            font-weight: 950;
            box-shadow: 0 12px 22px rgba(241, 32, 32, .25);
        }

        .store-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
            font-weight: 900;
        }

        .variant-line {
            min-height: 22px;
            color: #7c2d12;
            font-weight: 800;
            font-size: 13px;
        }

        .footer {
            border-top: 1px solid var(--card-border);
            background: rgba(255, 255, 255, .78);
        }

        @media (max-width: 768px) {
            .banner-img { height: 260px; }
            .top-nav .container { gap: 14px; }
            .nav-area { width: 100%; justify-content: center; flex-wrap: wrap; }
        }
    </style>
</head>

<body>
<nav class="top-nav py-3">
    <div class="container d-flex align-items-center justify-content-between flex-wrap">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
            <span>
                <strong>Phone Price Tracker</strong>
                <small>So sánh giá CellphoneS & FPT Shop</small>
            </span>
        </a>

        <div class="nav-area d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('home') }}" class="nav-pill"><i class="fa-solid fa-house me-1"></i> Trang chủ</a>

            @guest
                <a href="{{ route('login.form') }}" class="nav-pill"><i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập</a>
                <a href="{{ route('register.form') }}" class="btn-main py-2 px-3"><i class="fa-solid fa-user-plus me-1"></i> Đăng ký</a>
            @else
                <a href="{{ route('tracking.index') }}" class="nav-pill"><i class="fa-solid fa-bell me-1"></i> Theo dõi giá</a>
                <a href="{{ route('profile.index') }}" class="nav-pill"><i class="fa-solid fa-user me-1"></i> Hồ sơ</a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="nav-pill"><i class="fa-solid fa-screwdriver-wrench me-1"></i> Admin</a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="nav-pill"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</button>
                </form>
            @endguest
        </div>
    </div>
</nav>

@if(session('success'))
    <div class="container mt-3">
        <div class="alert alert-success rounded-4 border-0 shadow-sm mb-0">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    </div>
@endif

@if($errors->any())
    <div class="container mt-3">
        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-0">
            @foreach($errors->all() as $error)
                <div><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<main>
    <section class="hero-wrap">
        <div class="container">
            <div id="homeBannerCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img class="banner-img" src="{{ asset('images/banner/banner1.jpg') }}" alt="So sánh giá điện thoại">
                    </div>
                    <div class="carousel-item">
                        <img class="banner-img" src="{{ asset('images/banner/banner2.jpg') }}" alt="Theo dõi giá iPhone">
                    </div>
                    <div class="carousel-item">
                        <img class="banner-img" src="{{ asset('images/banner/banner3.jpg') }}" alt="Cảnh báo giá tốt">
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="stat-card p-4 h-100 d-flex gap-3 align-items-center">
                        <div class="stat-icon"><i class="fa-solid fa-mobile-screen"></i></div>
                        <div>
                            <strong>{{ count($products) }}</strong>
                            <div class="mini-label">Sản phẩm đang có</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-4 h-100 d-flex gap-3 align-items-center">
                        <div class="stat-icon"><i class="fa-solid fa-shop"></i></div>
                        <div>
                            <strong>2</strong>
                            <div class="mini-label">Nền tảng so sánh</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card p-4 h-100 d-flex gap-3 align-items-center">
                        <div class="stat-icon"><i class="fa-solid fa-envelope-circle-check"></i></div>
                        <div>
                            <strong>24/7</strong>
                            <div class="mini-label">Theo dõi biến động giá</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="pb-5">
        <div class="container">
            <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-4">
                <div>
                    <div class="section-badge mb-3"><i class="fa-solid fa-fire"></i> Giá tốt đang cập nhật từ database</div>
                    <h1 class="section-title mb-2">Danh sách sản phẩm</h1>
                    <p class="text-muted fw-semibold mb-0">
                        Bấm <b>Xem chi tiết</b> để chọn màu, dung lượng, so sánh chênh lệch và tới nơi bán bằng link affiliate.
                    </p>
                </div>

                <div class="filter-card p-2 d-flex align-items-center gap-2">
                    <input type="text" id="searchInput" class="form-control" placeholder="Tìm iPhone...">
                    <button class="btn-main" type="button" onclick="filterProducts()">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap mb-4">
                <button type="button" class="model-chip active" onclick="setKeyword('')"><i class="fa-solid fa-layer-group"></i> Tất cả</button>
                <button type="button" class="model-chip" onclick="setKeyword('iphone 13')">iPhone 13</button>
                <button type="button" class="model-chip" onclick="setKeyword('iphone 14')">iPhone 14</button>
                <button type="button" class="model-chip" onclick="setKeyword('iphone 15')">iPhone 15</button>
                <button type="button" class="model-chip" onclick="setKeyword('iphone 16')">iPhone 16</button>
                <button type="button" class="model-chip" onclick="setKeyword('iphone 17')">iPhone 17</button>
            </div>

            @if($products->isEmpty())
                <div class="soft-card p-5 text-center">
                    <i class="fa-solid fa-circle-exclamation fa-3x text-warning mb-3"></i>
                    <h4 class="fw-black">Chưa có sản phẩm để hiển thị</h4>
                    <p class="text-muted mb-0">Bạn kiểm tra lại database <b>phone_price_tracker</b> và bảng <b>products</b>.</p>
                </div>
            @else
                <div class="row g-4" id="productGrid">
                    @foreach($products as $item)
                        @php
                            $productName = $item->product_name ?? 'Sản phẩm chưa có tên';
                            $thumbnailUrl = $imageUrl($item->thumbnail_url ?? null);
                            $currentPrice = $item->current_price ?? null;
                            $storeName = $item->store_name ?? 'Đang cập nhật';
                            $productUrl = $item->product_url ?? '#';
                            $bestVariantId = $item->best_variant_id ?? null;
                        @endphp

                        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 product-item" data-name="{{ mb_strtolower($productName) }}">
                            <div class="product-card p-3">
                                @if($currentPrice)
                                    <div class="deal-ribbon"><i class="fa-solid fa-bolt me-1"></i> Giá tốt</div>
                                @endif

                                <a href="{{ route('product.detail', $item->id) }}" class="d-block product-img-wrap mb-3">
                                    <img src="{{ $thumbnailUrl }}" class="product-img" alt="{{ $productName }}">
                                </a>

                                <h5 class="fw-black mb-2" style="font-size: 17px; line-height: 1.35; min-height: 46px;">
                                    <a href="{{ route('product.detail', $item->id) }}" class="text-dark">{{ $productName }}</a>
                                </h5>

                                <div class="variant-line mb-2">
                                    @if($item->storage || $item->color)
                                        {{ $item->storage }} @if($item->color) - {{ $item->color }} @endif
                                    @else
                                        Phiên bản giá tốt nhất
                                    @endif
                                </div>

                                <div class="store-badge mb-3"><i class="fa-solid fa-store"></i> {{ $storeName }}</div>

                                <div class="mb-3">
                                    <div class="mini-label">Giá hiện tại tốt nhất</div>
                                    <div class="price">{{ $formatVnd($currentPrice) }}</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <a href="{{ route('product.detail', $item->id) }}" class="btn-main">
                                        <i class="fa-solid fa-circle-info"></i> Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</main>

<footer class="footer py-4 mt-4">
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <strong>Phone Price Tracker</strong>
            <div class="text-muted small">Hệ thống theo dõi và so sánh giá điện thoại từ CellphoneS và FPT Shop.</div>
        </div>
        <div class="text-muted small">Laravel • MySQL • Price Tracking</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterProducts() {
        const keyword = document.getElementById('searchInput').value.trim().toLowerCase();
        const items = document.querySelectorAll('.product-item');

        items.forEach(item => {
            const name = item.dataset.name || '';
            item.style.display = name.includes(keyword) ? '' : 'none';
        });
    }

    function setKeyword(keyword) {
        document.getElementById('searchInput').value = keyword;
        filterProducts();
    }

    document.getElementById('searchInput')?.addEventListener('keyup', filterProducts);
</script>
</body>
</html>
