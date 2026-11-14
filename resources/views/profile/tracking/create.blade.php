@php
    $prefill = $prefill ?? [];

    // Tương thích với controller cũ nếu còn truyền $prefillUrl, $variantId.
    if (empty($prefill['submitted_url']) && !empty($prefillUrl)) {
        $prefill['submitted_url'] = $prefillUrl;
    }

    if (empty($prefill['variant_id']) && !empty($variantId)) {
        $prefill['variant_id'] = $variantId;
    }

    $formatVnd = function ($price) {
        if ($price === null || $price === '' || (float) $price <= 0) {
            return 'Đang cập nhật';
        }

        return number_format((float) $price, 0, ',', '.') . 'đ';
    };
@endphp

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thêm sản phẩm theo dõi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #f12020;
            --primary-orange: #ff7a00;
            --soft-orange: #fff0e6;
            --card-border: rgba(255, 122, 0, .16);
        }

        body {
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #fff4ed 0%, #ffffff 55%, #fff7f0 100%);
        }

        a { text-decoration: none; }

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
        .brand small { color: #6b7280; font-weight: 600; }

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

        .box {
            max-width: 760px;
            margin: 44px auto;
            border-radius: 30px;
            border: 1px solid var(--card-border);
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 18px 45px rgba(127, 29, 29, .08);
            overflow: hidden;
        }

        .box-head {
            color: #fff;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            padding: 26px 30px;
        }

        .summary-box {
            border: 1px solid #ffd4bd;
            background: #fff7ed;
            border-radius: 24px;
            padding: 18px;
        }

        .btn-main {
            border: 0;
            color: #fff;
            font-weight: 900;
            border-radius: 999px;
            padding: 12px 18px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            box-shadow: 0 14px 30px rgba(241, 32, 32, .22);
        }

        .btn-main:hover { color: #fff; filter: brightness(.96); }
        .form-control { border-radius: 999px; border-color: #ffd4bd; padding: 12px 16px; }
        .form-control:focus { border-color: var(--primary-orange); box-shadow: 0 0 0 .2rem rgba(255, 122, 0, .16); }
    </style>
</head>
<body>
<nav class="top-nav py-3">
    <div class="container d-flex align-items-center justify-content-between flex-wrap gap-3">
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
            <span>
                <strong>Phone Price Tracker</strong>
                <small>Thêm sản phẩm theo dõi</small>
            </span>
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            <a href="{{ route('home') }}" class="nav-pill"><i class="fa-solid fa-house me-1"></i> Trang chủ</a>
            <a href="{{ route('tracking.index') }}" class="nav-pill"><i class="fa-solid fa-bell me-1"></i> Danh sách theo dõi</a>
            <a href="{{ route('profile.index') }}" class="nav-pill"><i class="fa-solid fa-user me-1"></i> Hồ sơ</a>
        </div>
    </div>
</nav>

<main class="container">
    <div class="box">
        <div class="box-head">
            <h1 class="h3 fw-bold mb-1"><i class="fa-solid fa-bell me-2"></i>Thêm sản phẩm theo dõi</h1>
            <div>Form này được tạo từ phiên bản màu và GB bạn vừa chọn ở trang chi tiết sản phẩm.</div>
        </div>

        <div class="p-4 p-lg-5">
            @if(session('success'))
                <div class="alert alert-success rounded-4">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-4">
                    @foreach($errors->all() as $error)
                        <div><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(!empty($prefill['product_name']) || !empty($prefill['submitted_url']))
                <div class="summary-box mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-secondary small fw-bold">Sản phẩm</div>
                            <div class="fw-bold fs-5">{{ $prefill['product_name'] ?? 'Sản phẩm vừa chọn' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small fw-bold">Phiên bản</div>
                            <div class="fw-bold">{{ !empty($prefill['storage']) ? $prefill['storage'] : 'GB chưa rõ' }}</div>
                            <div class="small text-secondary">{{ !empty($prefill['color']) ? $prefill['color'] : 'Màu chưa rõ' }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-secondary small fw-bold">Giá thấp nhất hiện tại</div>
                            <div class="fw-bold text-danger">{{ $formatVnd($prefill['current_price'] ?? null) }}</div>
                            <div class="small text-secondary">{{ $prefill['store_name'] ?? '' }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning rounded-4">
                    Bạn nên vào trang chi tiết sản phẩm, chọn màu và GB trước rồi bấm <b>Theo dõi phiên bản này</b> để form tự điền đúng link.
                </div>
            @endif

            <form action="{{ route('tracking.store') }}" method="POST">
                @csrf

                <input type="hidden" name="product_id" value="{{ old('product_id', $prefill['product_id'] ?? '') }}">
                <input type="hidden" name="variant_id" value="{{ old('variant_id', $prefill['variant_id'] ?? '') }}">

                <div class="mb-3">
                    <label class="form-label fw-bold">Link sản phẩm vừa chọn</label>
                    <input
                        type="url"
                        name="submitted_url"
                        class="form-control"
                        value="{{ old('submitted_url', $prefill['submitted_url'] ?? '') }}"
                        placeholder="Link affiliate_url hoặc product_url"
                        required
                        @if(!empty($prefill['submitted_url'])) readonly @endif>
                    <div class="form-text">Link này ưu tiên lấy từ cột <b>affiliate_url</b>. Nếu affiliate_url chưa có thì dùng <b>product_url</b>.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Giá mong muốn của bạn</label>
                    <input
                        type="number"
                        name="desired_price"
                        class="form-control"
                        value="{{ old('desired_price') }}"
                        min="1"
                        placeholder="Ví dụ: 25000000"
                        required>
                    <div class="form-text">Khi giá thực tế thấp hơn hoặc bằng mức này, hệ thống sẽ gửi cảnh báo cho bạn.</div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn-main"><i class="fa-solid fa-plus me-1"></i> Lưu vào danh sách theo dõi</button>
                    <a href="{{ route('tracking.index') }}" class="btn btn-light border rounded-pill fw-bold">Về danh sách theo dõi</a>
                    <a href="{{ route('home') }}" class="btn btn-link text-danger fw-bold">Quay lại trang chủ</a>
                </div>
            </form>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
