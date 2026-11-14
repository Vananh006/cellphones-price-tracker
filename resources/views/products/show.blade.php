@php
    $formatVnd = function ($price) {
        if ($price === null || $price === '' || (int) $price <= 0) {
            return 'Đang cập nhật';
        }

        return number_format((int) $price, 0, ',', '.') . 'đ';
    };

    $imageUrl = function ($url) {
        if (empty($url)) {
            return 'https://via.placeholder.com/520x520?text=Chưa+có+ảnh';
        }

        return str_starts_with($url, 'http') ? $url : asset($url);
    };

    $selectedVariantId = $bestVariant['id'] ?? ($variantData[0]['id'] ?? null);

    $galleryImages = collect($productGalleryImages ?? [])
        ->filter()
        ->unique()
        ->values();

    if ($galleryImages->isEmpty() && !empty($product->thumbnail_url)) {
        $galleryImages = collect([$imageUrl($product->thumbnail_url)]);
    }
@endphp

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->product_name }} - Chi tiết sản phẩm</title>
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
        .btn-main.disabled { pointer-events: none; opacity: .58; box-shadow: none; }

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

        .soft-card,
        .shop-card,
        .compare-card {
            border: 1px solid var(--card-border);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 18px 45px rgba(127, 29, 29, .08);
        }

        .soft-card { border-radius: 30px; }
        .shop-card { border-radius: 24px; }
        .compare-card {
            border-radius: 26px;
            background: linear-gradient(135deg, #fff, #fff7f0);
        }

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

        .main-img {
            width: 100%;
            height: 430px;
            object-fit: contain;
            padding: 28px;
            border-radius: 26px;
            background: linear-gradient(135deg, #fff, #fff1e6);
        }

        .gallery-thumbs {
            display: flex;
            gap: 12px;
            overflow-x: auto;
            padding: 14px 4px 2px;
            scrollbar-width: thin;
        }

        .gallery-thumb {
            width: 74px;
            height: 74px;
            flex: 0 0 74px;
            border: 2px solid #ffe0cc;
            border-radius: 18px;
            background: #fff;
            padding: 6px;
            cursor: pointer;
            transition: .18s ease;
        }

        .gallery-thumb:hover,
        .gallery-thumb.active {
            border-color: var(--primary-orange);
            box-shadow: 0 10px 22px rgba(241, 32, 32, .14);
            transform: translateY(-2px);
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .price {
            color: #e11d2e;
            font-size: 30px;
            font-weight: 950;
        }

        .mini-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 800;
        }

        .variant-btn {
            border: 1px solid #ffd4bd;
            background: #fff;
            color: #7c2d12;
            border-radius: 18px;
            padding: 10px 14px;
            font-weight: 900;
            text-align: left;
            transition: .18s ease;
        }

        .variant-btn:hover,
        .variant-btn.active {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            box-shadow: 0 12px 24px rgba(241, 32, 32, .2);
        }

        .lower-card {
            border-radius: 24px;
            background: linear-gradient(135deg, #fff7ed, #fff);
            border: 1px solid #ffd4bd;
        }

        .diff-box {
            border-radius: 22px;
            border: 1px dashed #ffb088;
            background: #fff7ed;
            padding: 18px;
        }

        .store-logo-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 999px;
            background: #fff7ed;
            color: #9a3412;
            font-size: 12px;
            font-weight: 900;
        }

        .table thead th {
            color: #7c2d12;
            background: #fff7ed;
            border-bottom: 0;
        }
.price-history-card {
    border-radius: 26px;
    overflow: hidden;
}

.price-chart-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

.chart-range-btn {
    border: 1px solid #ffd4bd;
    background: #fff;
    color: #9a3412;
    border-radius: 999px;
    padding: 8px 13px;
    font-size: 13px;
    font-weight: 900;
}

.chart-range-btn:hover,
.chart-range-btn.active {
    color: #fff;
    border-color: transparent;
    background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
    box-shadow: 0 10px 22px rgba(241, 32, 32, .14);
}

.price-chart-wrapper {
    position: relative;
    width: 100%;
    height: 340px;
    padding: 12px;
    border: 1px solid #ffe0cc;
    border-radius: 24px;
    background: linear-gradient(135deg, #ffffff, #fff7ed);
}

.chart-summary-box {
    height: 100%;
    padding: 15px 16px;
    border: 1px solid #ffe0cc;
    border-radius: 18px;
    background: #fffaf7;
}
        @media (max-width: 768px) {
            .brand small { display: none; }
            .main-img { height: 300px; }
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
                <small>So sánh giá CellphoneS & FPT Shop</small>
            </span>
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
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

<main class="container py-4 py-lg-5" id="productPage"
      data-selected-variant-id="{{ $selectedVariantId }}"
      data-tracking-url="{{ route('tracking.create') }}"
      data-product-id="{{ $product->id }}"
      data-product-name="{{ $product->product_name }}">
    <div class="mb-3 small fw-bold">
        <a href="{{ route('home') }}" class="text-danger">Trang chủ</a>
        <span class="text-secondary">/ {{ $product->product_name }}</span>
    </div>

    <section class="row g-4 align-items-stretch">
        <div class="col-lg-5">
            <div class="soft-card p-3 h-100">
                <img id="mainProductImage" src="{{ $imageUrl($product->thumbnail_url ?? null) }}" class="main-img" alt="{{ $product->product_name }}">

                <div class="gallery-thumbs" id="galleryThumbs" aria-label="Ảnh sản phẩm">
                    @foreach($galleryImages as $index => $galleryImage)
                        <button type="button"
                            class="gallery-thumb {{ $index === 0 ? 'active' : '' }}"
                            data-image-url="{{ $galleryImage }}"
                            aria-label="Xem ảnh {{ $index + 1 }}">
                            <img src="{{ $galleryImage }}" alt="Ảnh {{ $index + 1 }} - {{ $product->product_name }}">
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="soft-card p-4 h-100">
                <span class="section-badge"><i class="fa-brands fa-apple"></i> {{ $product->brand ?? 'Apple' }}</span>
                <h1 class="fw-black mt-3 mb-2">{{ $product->product_name }}</h1>
                <p class="text-secondary fw-semibold">
                    Chọn dung lượng và màu sắc để xem shop đang có giá thấp hơn. Bạn có thể mua ngay bằng link affiliate ở bảng so sánh hoặc bấm theo dõi để nhập giá mong muốn.
                </p>

                <div class="mb-4">
                    <div class="mini-label mb-2">Chọn màu và GB</div>
                    @if(count($variantData) > 0)
                        <div class="d-flex flex-wrap gap-2" id="variantButtons">
                            @foreach($variantData as $variant)
                                <button type="button"
                                    class="variant-btn {{ $variant['id'] == $selectedVariantId ? 'active' : '' }}"
                                    data-variant-id="{{ $variant['id'] }}">
                                    <i class="fa-solid fa-mobile-screen me-1"></i>
                                    {{ $variant['storage'] ?: 'Dung lượng chưa rõ' }}
                                    <br>
                                    <small>{{ $variant['color'] ?: 'Màu chưa rõ' }}</small>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning rounded-4 mb-0">Sản phẩm này chưa có biến thể màu/dung lượng trong database.</div>
                    @endif
                </div>

                <div class="lower-card p-3 p-lg-4 mb-4">
                    <div class="mini-label">Shop có giá thấp hơn cho phiên bản đang chọn</div>
                    <h3 class="fw-black mb-1" id="lowestStore">Đang cập nhật</h3>
                    <div class="price" id="lowestPrice">Đang cập nhật</div>
                    <div class="text-secondary small" id="lowestVariantText">Chọn phiên bản để xem giá.</div>
                </div>

                <div class="row g-3 align-items-center">
                    <div class="col-md-7">
                        <div class="alert alert-light border rounded-4 mb-0">
                            <i class="fa-solid fa-circle-info text-danger me-2"></i>
                            Chọn đúng <b>màu</b> và <b>GB</b>, sau đó bấm <b>Theo dõi phiên bản này</b>. Hệ thống sẽ chuyển sang form theo dõi với link sản phẩm đã chọn sẵn.
                        </div>
                    </div>
                    <div class="col-md-5 d-grid gap-2">
                        @auth
                            <a href="#" id="trackSelectedLink" class="btn-main w-100 disabled" aria-disabled="true">
                                <i class="fa-solid fa-bell"></i> Chọn màu/GB để theo dõi
                            </a>
                        @else
                            <a href="{{ route('login.form') }}" class="btn-main w-100">
                                <i class="fa-solid fa-right-to-bracket"></i> Đăng nhập để theo dõi
                            </a>
                        @endauth
                        <a href="#compareSection" class="btn-outline-main w-100">
                            <i class="fa-solid fa-scale-balanced"></i> Xem so sánh
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
      <section class="soft-card price-history-card p-3 p-lg-4 mt-4" id="priceHistorySection">
        <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <span class="section-badge">
                    <i class="fa-solid fa-chart-line"></i> Lịch sử giá
                </span>
                <h2 class="fw-black mt-3 mb-1">Biểu đồ biến động giá</h2>
                <p class="text-secondary fw-semibold mb-0">
                    Theo dõi xu hướng giá của phiên bản đang chọn giữa CellphoneS và FPT Shop.
                </p>
            </div>

            <div class="price-chart-toolbar" id="priceChartToolbar">
                <button type="button" class="chart-range-btn" data-days="7">7 ngày</button>
                <button type="button" class="chart-range-btn active" data-days="30">30 ngày</button>
                <button type="button" class="chart-range-btn" data-days="90">90 ngày</button>
                <button type="button" class="chart-range-btn" data-days="0">Tất cả</button>
            </div>
        </div>

        <div id="priceHistoryState" class="text-secondary small fw-bold mb-2">
            Đang tải lịch sử giá...
        </div>

        <div class="price-chart-wrapper">
            <canvas id="priceHistoryChart"></canvas>
        </div>

        <div class="row g-3 mt-3" id="priceHistorySummary" style="display: none;">
            <div class="col-md-4">
                <div class="chart-summary-box">
                    <div class="mini-label">Giá tốt nhất hiện tại</div>
                    <div class="fw-black text-danger" id="summaryBestNow">Đang cập nhật</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-summary-box">
                    <div class="mini-label">Thấp nhất lịch sử</div>
                    <div class="fw-black text-success" id="summaryLowest">Đang cập nhật</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="chart-summary-box">
                    <div class="mini-label">Cập nhật gần nhất</div>
                    <div class="fw-black" id="summaryUpdated">Đang cập nhật</div>
                </div>
            </div>
        </div>
    </section>
    <section class="soft-card p-3 p-lg-4 mt-4" id="compareSection">
        <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <span class="section-badge"><i class="fa-solid fa-scale-balanced"></i> So sánh chênh lệch</span>
                <h2 class="fw-black mt-3 mb-1">CellphoneS và FPT Shop</h2>
                <p class="text-secondary fw-semibold mb-0">Bảng bên dưới tự đổi theo màu và dung lượng bạn chọn ở phần thông tin sản phẩm.</p>
            </div>
            <div class="diff-box">
                <div class="mini-label">Chênh lệch giữa 2 shop</div>
                <div class="fw-black fs-4 text-danger" id="differenceText">Đang cập nhật</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nơi bán</th>
                        <th>Phiên bản</th>
                        <th>Giá hiện tại</th>
                        <th>Giá gốc</th>
                        <th>Tình trạng</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="priceTableBody">
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">Đang tải dữ liệu giá...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script type="application/json" id="variantDataJson">{!! json_encode($variantData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script>
    const productPage = document.getElementById('productPage');
    const variantDataElement = document.getElementById('variantDataJson');
    const variantData = JSON.parse(variantDataElement ? variantDataElement.textContent : '[]');
    const selectedVariantId = Number(productPage ? productPage.dataset.selectedVariantId : 0);

    let currentGalleryImages = [];
    let currentGalleryIndex = 0;
    let galleryAutoSlideTimer = null;

    function setMainImage(imageUrl) {
        const mainProductImage = document.getElementById('mainProductImage');
        if (!mainProductImage || !imageUrl) return;

        mainProductImage.src = imageUrl;
        currentGalleryIndex = Math.max(0, currentGalleryImages.indexOf(imageUrl));

        document.querySelectorAll('.gallery-thumb').forEach(button => {
            button.classList.toggle('active', button.dataset.imageUrl === imageUrl);
        });
    }

    function stopGalleryAutoSlide() {
        if (galleryAutoSlideTimer) {
            clearInterval(galleryAutoSlideTimer);
            galleryAutoSlideTimer = null;
        }
    }

    function startGalleryAutoSlide() {
        stopGalleryAutoSlide();

        if (currentGalleryImages.length <= 1) {
            return;
        }

        galleryAutoSlideTimer = setInterval(() => {
            currentGalleryIndex = (currentGalleryIndex + 1) % currentGalleryImages.length;
            setMainImage(currentGalleryImages[currentGalleryIndex]);
        }, 3000);
    }

    function renderGallery(images) {
        const galleryThumbs = document.getElementById('galleryThumbs');
        const validImages = [...new Set((images || []).filter(Boolean))];

        if (!galleryThumbs || validImages.length === 0) {
            return;
        }

        currentGalleryImages = validImages;
        currentGalleryIndex = 0;

        galleryThumbs.innerHTML = validImages.map((imageUrl, index) => `
            <button type="button"
                class="gallery-thumb ${index === 0 ? 'active' : ''}"
                data-image-url="${escapeHtml(imageUrl)}"
                aria-label="Xem ảnh ${index + 1}">
                <img src="${escapeHtml(imageUrl)}" alt="Ảnh sản phẩm ${index + 1}">
            </button>
        `).join('');

        setMainImage(validImages[0]);
        startGalleryAutoSlide();
    }

    document.getElementById('galleryThumbs')?.addEventListener('click', function (event) {
        const button = event.target.closest('.gallery-thumb');
        if (!button) return;

        setMainImage(button.dataset.imageUrl);
        startGalleryAutoSlide();
    });

    function formatVnd(price) {
        if (!price || Number(price) <= 0) return 'Đang cập nhật';
        return new Intl.NumberFormat('vi-VN').format(Number(price)) + 'đ';
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function getVariant(variantId) {
        return variantData.find(item => Number(item.id) === Number(variantId)) || variantData[0];
    }

    const trackingCreateBaseUrl = productPage ? productPage.dataset.trackingUrl : '';
    const productId = productPage ? productPage.dataset.productId : '';
    const productName = productPage ? productPage.dataset.productName : '';

    let priceHistoryChart = null;
    let currentHistoryDays = 30;

    function hasPriceHistoryData(fptPrices, cpsPrices) {
        return [...(fptPrices || []), ...(cpsPrices || [])]
            .some(price => price !== null && price !== undefined);
    }

    function setPriceHistoryState(message, type = 'muted') {
        const state = document.getElementById('priceHistoryState');

        if (!state) return;

        state.classList.remove('text-secondary', 'text-danger', 'text-success');

        if (type === 'danger') {
            state.classList.add('text-danger');
        } else if (type === 'success') {
            state.classList.add('text-success');
        } else {
            state.classList.add('text-secondary');
        }

        state.textContent = message;
    }

    function updatePriceHistorySummary(summary) {
        const summaryWrapper = document.getElementById('priceHistorySummary');
        const summaryBestNow = document.getElementById('summaryBestNow');
        const summaryLowest = document.getElementById('summaryLowest');
        const summaryUpdated = document.getElementById('summaryUpdated');

        if (!summaryWrapper || !summary) {
            if (summaryWrapper) summaryWrapper.style.display = 'none';
            return;
        }

        summaryBestNow.textContent = summary.best_now
            ? `${formatVnd(summary.best_now.price)} - ${summary.best_now.store}`
            : 'Chưa có dữ liệu';

        summaryLowest.textContent = summary.lowest_history
            ? `${formatVnd(summary.lowest_history.price)} - ${summary.lowest_history.store} (${summary.lowest_history.date})`
            : 'Chưa có dữ liệu';

        summaryUpdated.textContent = summary.last_updated || 'Chưa có dữ liệu';
        summaryWrapper.style.display = 'flex';
    }

    function renderPriceHistoryChart(labels = [], fptPrices = [], cpsPrices = []) {
        const chartCanvas = document.getElementById('priceHistoryChart');

        if (!chartCanvas || typeof Chart === 'undefined') {
            return;
        }

        const ctx = chartCanvas.getContext('2d');

        if (priceHistoryChart) {
            priceHistoryChart.destroy();
        }

        priceHistoryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'FPT Shop',
                        data: fptPrices,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.08)',
                        borderWidth: 3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        spanGaps: true
                    },
                    {
                        label: 'CellphoneS',
                        data: cpsPrices,
                        borderColor: '#e11d48',
                        backgroundColor: 'rgba(225, 29, 72, 0.08)',
                        borderWidth: 3,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        spanGaps: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 18
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatVnd(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 8
                        }
                    },
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function (value) {
                                return Math.round(Number(value) / 1000000) + 'tr';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Giá bán'
                        }
                    }
                }
            }
        });
    }

    function loadPriceHistory(variantId, days = currentHistoryDays) {
        if (!productId || !variantId) {
            setPriceHistoryState('Chưa có phiên bản sản phẩm để tải lịch sử giá.', 'danger');
            return;
        }

        const url = `/api/price-history/${productId}?variant_id=${encodeURIComponent(variantId)}&days=${days}`;

        setPriceHistoryState('Đang tải lịch sử giá...');

        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Không thể tải lịch sử giá.');
                }

                return response.json();
            })
            .then(data => {
                renderPriceHistoryChart(data.dates || [], data.fptPrices || [], data.cpsPrices || []);
                updatePriceHistorySummary(data.summary || null);

                if (!hasPriceHistoryData(data.fptPrices, data.cpsPrices)) {
                    setPriceHistoryState('Chưa có đủ dữ liệu lịch sử giá cho phiên bản này. Hãy thử chọn “Tất cả” hoặc chạy crawler thêm vài lần.');
                    return;
                }

                setPriceHistoryState('Di chuột vào biểu đồ để xem giá từng ngày.', 'success');
            })
            .catch(() => {
                setPriceHistoryState('Không thể tải lịch sử giá. Kiểm tra route /api/price-history hoặc dữ liệu bảng price_history.', 'danger');
            });
    }

    document.querySelectorAll('.chart-range-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.querySelectorAll('.chart-range-btn').forEach(item => {
                item.classList.remove('active');
            });

            this.classList.add('active');

            currentHistoryDays = Number(this.dataset.days);

            const activeVariantButton = document.querySelector('.variant-btn.active');
            const activeVariantId = activeVariantButton
                ? activeVariantButton.dataset.variantId
                : selectedVariantId;

            loadPriceHistory(activeVariantId, currentHistoryDays);
        });
    });


    function buildTrackingUrl(variant, lowest) {
        const params = new URLSearchParams();
        params.set('product_id', productId);
        params.set('variant_id', variant.id);
        params.set('product_name', productName);
        params.set('storage', variant.storage || '');
        params.set('color', variant.color || '');

        if (lowest) {
            params.set('submitted_url', lowest.final_url || lowest.affiliate_url || lowest.product_url || '');
            params.set('current_price', Math.round(Number(lowest.current_price || 0)));
            params.set('store_name', lowest.store_name || '');
        }

        return trackingCreateBaseUrl + '?' + params.toString();
    }

    function updateTrackLink(variant, lowest, enable = false) {
        const trackSelectedLink = document.getElementById('trackSelectedLink');
        if (!trackSelectedLink || !variant || !lowest) return;

        trackSelectedLink.href = buildTrackingUrl(variant, lowest);

        if (enable) {
            trackSelectedLink.classList.remove('disabled');
            trackSelectedLink.removeAttribute('aria-disabled');
            trackSelectedLink.innerHTML = '<i class="fa-solid fa-bell"></i> Theo dõi phiên bản này';
        }
    }

    function renderVariant(variantId, userSelected = false) {
        const variant = getVariant(variantId);
        const tbody = document.getElementById('priceTableBody');
        const lowestStore = document.getElementById('lowestStore');
        const lowestPrice = document.getElementById('lowestPrice');
        const lowestVariantText = document.getElementById('lowestVariantText');
        const differenceText = document.getElementById('differenceText');
        const trackSelectedLink = document.getElementById('trackSelectedLink');

        if (!variant) {
            lowestStore.textContent = 'Chưa có dữ liệu biến thể';
            lowestPrice.textContent = 'Đang cập nhật';
            lowestVariantText.textContent = 'Sản phẩm này chưa có biến thể trong database.';
            differenceText.textContent = 'Chưa đủ dữ liệu';
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Sản phẩm này chưa có biến thể.</td></tr>';
            return;
        }

        document.querySelectorAll('.variant-btn').forEach(button => {
            button.classList.toggle('active', Number(button.dataset.variantId) === Number(variant.id));
        });

        loadPriceHistory(variant.id);

        renderGallery(
            variant.gallery_images && variant.gallery_images.length > 0
                ? variant.gallery_images
                : (variant.image_url ? [variant.image_url] : [])
        );

        if (!variant.prices || variant.prices.length === 0) {
            lowestStore.textContent = 'Chưa có dữ liệu giá';
            lowestPrice.textContent = 'Đang cập nhật';
            lowestVariantText.textContent = 'Phiên bản này chưa có dữ liệu giá từ CellphoneS hoặc FPT Shop.';
            differenceText.textContent = 'Chưa đủ dữ liệu';
            if (trackSelectedLink) {
                trackSelectedLink.classList.add('disabled');
                trackSelectedLink.setAttribute('aria-disabled', 'true');
                trackSelectedLink.href = '#';
                trackSelectedLink.innerHTML = '<i class="fa-solid fa-bell"></i> Chưa có giá để theo dõi';
            }
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-4">Phiên bản này chưa có giá.</td></tr>';
            return;
        }

        const validPrices = variant.prices.filter(item => Number(item.current_price) > 0);
        const lowest = variant.lowest || validPrices[0];
        const second = variant.second || validPrices[1];

        if (lowest) {
            lowestStore.textContent = lowest.store_name;
            lowestPrice.textContent = formatVnd(lowest.current_price);
            lowestVariantText.textContent = `${variant.storage || 'Dung lượng chưa rõ'} - ${variant.color || 'Màu chưa rõ'}`;
            updateTrackLink(variant, lowest, userSelected);
        } else {
            lowestStore.textContent = 'Chưa có dữ liệu giá';
            lowestPrice.textContent = 'Đang cập nhật';
            lowestVariantText.textContent = `${variant.storage || 'Dung lượng chưa rõ'} - ${variant.color || 'Màu chưa rõ'}`;
            if (trackSelectedLink) {
                trackSelectedLink.classList.add('disabled');
                trackSelectedLink.setAttribute('aria-disabled', 'true');
                trackSelectedLink.href = '#';
                trackSelectedLink.innerHTML = '<i class="fa-solid fa-bell"></i> Chưa có giá để theo dõi';
            }
        }

        if (lowest && second) {
            const diff = Math.abs(Number(second.current_price) - Number(lowest.current_price));
            differenceText.textContent = formatVnd(diff);
        } else {
            differenceText.textContent = 'Chưa đủ 2 shop để so sánh';
        }

        tbody.innerHTML = variant.prices.map(item => {
            const isLowest = lowest && Number(item.id) === Number(lowest.id);
            const finalUrl = item.final_url || item.affiliate_url || item.product_url || '#';
            const stockText = Number(item.in_stock) === 1 ? 'Còn hàng' : 'Tạm hết';
            const stockClass = Number(item.in_stock) === 1 ? 'bg-success' : 'bg-secondary';

            return `
                <tr class="${isLowest ? 'table-warning' : ''}">
                    <td>
                        <span class="store-logo-pill"><i class="fa-solid fa-store"></i>${escapeHtml(item.store_name)}</span>
                        ${isLowest ? '<div class="small text-danger fw-bold mt-1">Giá thấp hơn</div>' : ''}
                    </td>
                    <td>${escapeHtml(variant.storage || 'Dung lượng chưa rõ')}<br><small class="text-secondary">${escapeHtml(variant.color || 'Màu chưa rõ')}</small></td>
                    <td class="fw-black text-danger">${formatVnd(item.current_price)}</td>
                    <td>${item.original_price ? '<del class="text-secondary">' + formatVnd(item.original_price) + '</del>' : '<span class="text-secondary">Đang cập nhật</span>'}</td>
                    <td><span class="badge rounded-pill ${stockClass}">${stockText}</span></td>
                    <td class="text-end">
                        ${finalUrl && finalUrl !== '#'
                            ? `<a href="${escapeHtml(finalUrl)}" target="_blank" rel="noopener" class="btn btn-sm btn-danger rounded-pill fw-bold px-3">Tới nơi bán</a>`
                            : '<span class="text-secondary small">Chưa có link</span>'}
                    </td>
                </tr>
            `;
        }).join('');
    }

    document.querySelectorAll('.variant-btn').forEach(button => {
        button.addEventListener('click', function () {
            renderVariant(this.dataset.variantId, true);
        });
    });

    renderVariant(selectedVariantId, false);
</script>
</body>
</html>
