<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công cụ theo dõi và crawl giá</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff7f1;
            color: #1f1f1f;
        }

        a {
            color: inherit;
        }

        .wrap {
            width: min(1220px, calc(100% - 40px));
            margin: 0 auto;
            padding: 28px 0 60px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .brand {
            font-weight: 900;
            font-size: 22px;
            color: #ff5a1f;
            text-decoration: none;
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .nav a,
        .nav button {
            border: 1px solid #ffd3bd;
            background: #fff;
            color: #5b2b18;
            border-radius: 999px;
            padding: 10px 14px;
            text-decoration: none;
            font-weight: 800;
            cursor: pointer;
        }

        .hero {
            background: linear-gradient(135deg, #fff, #fff2e9, #ffe7d6);
            border: 1px solid #ffd6c2;
            border-radius: 28px;
            padding: 36px 40px;
            box-shadow: 0 18px 45px rgba(255, 104, 32, .10);
        }

        .hero h1 {
            font-size: clamp(32px, 4vw, 56px);
            line-height: 1.05;
            margin: 0 0 14px;
            letter-spacing: -1px;
        }

        .hero p {
            margin: 0;
            max-width: 940px;
            font-size: 18px;
            color: #696969;
            line-height: 1.6;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin: 22px 0;
        }

        .stat {
            background: #fff;
            border: 1px solid #ffe1d1;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 12px 30px rgba(255, 104, 32, .07);
        }

        .stat span {
            color: #777;
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .stat b {
            display: block;
            margin-top: 8px;
            font-size: 24px;
        }

        .panel {
            background: #fff;
            border: 1px solid #ffe1d1;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 14px 36px rgba(255, 104, 32, .08);
            margin-top: 20px;
        }

        form.price-form {
            display: grid;
            grid-template-columns: 2fr 1.2fr 1fr auto;
            gap: 16px;
            align-items: end;
        }

        label {
            display: block;
            font-weight: 900;
            margin-bottom: 8px;
            color: #5b2b18;
        }

        input {
            width: 100%;
            border: 1px solid #ffd3bd;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: 16px;
            outline: none;
            background: #fffaf7;
        }

        input:focus {
            border-color: #ff5a1f;
            box-shadow: 0 0 0 4px rgba(255, 90, 31, .13);
        }

        button,
        .btn {
            border: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 14px 18px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        button,
        .btn-main {
            background: linear-gradient(135deg, #ff4d00, #ff8a00);
            color: #fff;
        }

        .btn-light {
            background: #fff7f1;
            color: #6b2d13;
            border: 1px solid #ffd3bd;
        }

        .btn-dark {
            background: #1f1f1f;
            color: #fff;
        }

        .notice {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 14px;
            font-weight: 800;
        }

        .ok {
            background: #dcfce7;
            color: #166534;
        }

        .bad {
            background: #fee2e2;
            color: #991b1b;
        }

        .warn {
            background: #fff7ed;
            color: #9a3412;
        }

        .result {
            border-left: 8px solid #ff7a1a;
        }

        .result h2 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 14px;
            margin: 18px 0;
        }

        .mini {
            background: #fffaf7;
            border: 1px solid #ffe1d1;
            border-radius: 16px;
            padding: 16px;
        }

        .mini span {
            display: block;
            color: #777;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .mini b {
            display: block;
            margin-top: 8px;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 16px;
            background: #fff;
        }

        th {
            background: #1f1f1f;
            color: #fff;
            text-align: left;
            padding: 14px;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #f1e2d9;
            vertical-align: top;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 7px 10px;
            font-size: 13px;
            font-weight: 900;
        }

        .badge-ok {
            background: #dcfce7;
            color: #166534;
        }

        .badge-wait {
            background: #fff3e9;
            color: #9a3412;
        }

        .section-title {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 16px;
            margin: 30px 0 14px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 28px;
        }

        .muted {
            color: #777;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .card {
            background: #fff;
            border: 1px solid #ffe1d1;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(255, 104, 32, .07);
        }

        .imgbox {
            height: 190px;
            background: #fffaf7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-weight: 900;
        }

        .imgbox img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .card-body {
            padding: 18px;
        }

        .card h3 {
            margin: 10px 0;
            font-size: 20px;
            line-height: 1.25;
        }

        .price {
            color: #e11d48;
            font-size: 28px;
            font-weight: 900;
            margin: 10px 0;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 14px;
        }

        .actions>* {
            flex: 1;
        }

        @media (max-width: 1000px) {

            form.price-form,
            .stats,
            .mini-grid,
            .cards {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 680px) {
            .wrap {
                width: calc(100% - 22px);
            }

            form.price-form,
            .stats,
            .mini-grid,
            .cards {
                grid-template-columns: 1fr;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .hero {
                padding: 26px 22px;
            }
        }
    </style>
</head>

<body>
    <div class="wrap">
        <header class="topbar">
            <a class="brand" href="{{ route('home') }}">Phone Price Tracker</a>
            <nav class="nav">
                <a href="{{ route('home') }}">Trang chủ</a>
                <a href="{{ route('tracking.index') }}">Danh sách theo dõi</a>
                @auth
                <a href="{{ route('profile.index') }}">Hồ sơ</a>
                @if((auth()->user()->role ?? '') === 'admin')
                <a href="{{ route('admin.dashboard') }}">Admin</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit">Đăng xuất</button>
                </form>
                @else
                <a href="{{ route('login.form') }}">Đăng nhập</a>
                <a href="{{ route('register.form') }}">Đăng ký</a>
                @endauth
            </nav>
        </header>

        <section class="hero">
            <h1>Công cụ crawl và kiểm tra cảnh báo giá</h1>
            <p>Trang này là công cụ phụ để team kiểm tra link CellphoneS/FPT Shop, cập nhật bảng <b>store_prices</b>, lưu lịch sử giá và gửi email khi giá hiện tại thấp hơn hoặc bằng giá mong muốn của người dùng.</p>
        </section>

        <section class="stats">
            <div class="stat"><span>Tổng link trong DB</span><b>{{ number_format($stats['total_links'] ?? 0) }}</b></div>
            <div class="stat"><span>Đã có giá crawl</span><b>{{ number_format($stats['crawled_links'] ?? 0) }}</b></div>
            <div class="stat"><span>Chờ crawl</span><b>{{ number_format($stats['pending_links'] ?? 0) }}</b></div>
            <div class="stat"><span>Cập nhật gần nhất</span><b>{{ $stats['last_updated_at'] ?? 'Chưa có' }}</b></div>
        </section>

        <section class="panel">
            <form class="price-form" method="POST" action="{{ route('tracker.check') }}">
                @csrf
                <div>
                    <label>Link sản phẩm</label>
                    <input type="url" name="url" value="{{ old('url') }}" placeholder="https://cellphones.com.vn/... hoặc https://fptshop.com.vn/..." required>
                </div>
                <div>
                    <label>Email nhận cảnh báo</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="you@example.com" required>
                </div>
                <div>
                    <label>Giá mong muốn</label>
                    <input type="number" name="target_price" value="{{ old('target_price') }}" placeholder="VD: 23000000" min="1000" step="1000">
                </div>
                <button type="submit">Kiểm tra</button>
            </form>
            <p class="muted" style="margin:14px 0 0;">Nếu giá tốt nhất hiện tại ≤ giá mong muốn, hệ thống gửi email ngay. Nếu chưa đạt, hệ thống lưu alert pending để gửi sau khi crawler cập nhật giá thấp hơn.</p>

            @if ($errors->any())
            <div class="notice bad">{{ $errors->first() }}</div>
            @endif
            @if (session('error'))
            <div class="notice bad">{{ session('error') }}</div>
            @endif
            @if (session('success'))
            <div class="notice ok">{{ session('success') }}</div>
            @endif
        </section>

        @if (session('result'))
        @php
        $result = session('result');
        $comparison = $result['comparison'] ?? [];
        $bestCurrent = $comparison['best_current'] ?? null;
        $targetPrice = $comparison['target_price'] ?? ($result['target_price'] ?? null);
        @endphp
        <section class="panel result">
            <h2>{{ $result['is_ok'] ? 'Đã đạt giá mong muốn' : 'Đã lưu theo dõi, chờ giảm giá' }}</h2>
            <p>{{ $result['message'] }}</p>

            <div class="mini-grid">
                <div class="mini"><span>Sản phẩm</span><b>{{ $result['product_name'] ?? 'Không rõ' }}</b></div>
                <div class="mini"><span>Nơi bán vừa kiểm tra</span><b>{{ $result['store_name'] ?? 'Không rõ' }}</b></div>
                <div class="mini"><span>Giá tốt nhất hiện tại</span><b>{{ $bestCurrent['display_price'] ?? 'Chưa có' }}</b></div>
                <div class="mini"><span>Giá mong muốn</span><b>{{ $targetPrice ? number_format((float) $targetPrice, 0, ',', '.') . 'đ' : 'Chưa nhập' }}</b></div>
            </div>

            <h3>So sánh giá theo sàn</h3>
            <table>
                <thead>
                    <tr>
                        <th>Sàn</th>
                        <th>Biến thể</th>
                        <th>Giá hiện tại</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật</th>
                        <th>Link mua</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($comparison['current_prices'] ?? []) as $row)
                    <tr>
                        <td>{{ $row['store_name'] }}</td>
                        <td>{{ $row['variant_name'] ?? '' }}</td>
                        <td><b>{{ $row['display_price'] }}</b></td>
                        <td>
                            @if (($row['is_reached_target'] ?? false))
                            <span class="badge badge-ok">Đạt giá mong muốn</span>
                            @else
                            <span class="badge badge-wait">Chưa đạt</span>
                            @endif
                        </td>
                        <td>{{ $row['updated_at'] ?? 'Chưa rõ' }}</td>
                        <td><a class="btn btn-light" href="{{ route('affiliate.redirect', $row['store_price_id']) }}" target="_blank">Mua ngay</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="muted">Chưa có dữ liệu so sánh.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        @endif

        <div class="section-title">
            <div>
                <h2>Link vừa cập nhật giá</h2>
                <p class="muted">Dữ liệu lấy từ bảng store_prices. Giao diện này không thay trang chủ sản phẩm hiện tại của bạn.</p>
            </div>
            <span class="muted">Tự làm mới giá mỗi 20 giây</span>
        </div>

        <section class="cards">
            @forelse ($storePrices as $storePrice)
            <article class="card" data-price-card data-price-id="{{ $storePrice->id }}">
                <div class="imgbox">
                    @if ($storePrice->variant?->product?->thumbnail_url)
                    <img src="{{ $storePrice->variant->product->thumbnail_url }}" alt="{{ $storePrice->variant?->product?->product_name }}">
                    @else
                    Không có ảnh
                    @endif
                </div>
                <div class="card-body">
                    <span class="badge badge-wait">{{ $storePrice->store?->store_name }}</span>
                    <h3>{{ $storePrice->variant?->product?->product_name ?? 'Sản phẩm' }}</h3>
                    <p class="muted">{{ $storePrice->variant?->display_name }}</p>
                    <div class="price" data-price-text>{{ number_format((float) $storePrice->current_price, 0, ',', '.') }}đ</div>
                    @if ($storePrice->original_price)
                    <div class="muted">Giá gốc: {{ number_format((float) $storePrice->original_price, 0, ',', '.') }}đ</div>
                    @endif
                    <div class="muted">Cập nhật: <span data-updated-at>{{ optional($storePrice->updated_at)->format('d/m/Y H:i') }}</span></div>
                    <div class="actions">
                        <a class="btn btn-light" href="{{ route('affiliate.redirect', $storePrice) }}" target="_blank">Mua ngay</a>
                        <form method="POST" action="{{ route('tracker.refresh', $storePrice) }}" style="margin:0;">
                            @csrf
                            <button class="btn-dark" type="submit" style="width:100%;">Crawl lại</button>
                        </form>
                    </div>
                </div>
            </article>
            @empty
            <div class="panel" style="grid-column:1/-1;">Chưa có link nào đã crawl được giá.</div>
            @endforelse
        </section>

        <div style="margin-top:24px;">{{ $storePrices->links() }}</div>
    </div>

    <script>
        async function refreshVisibleCards() {
            const cards = document.querySelectorAll('[data-price-card]');
            if (!cards.length) return;

            try {
                const response = await fetch("{{ route('api.tracker.items') }}", {
                    headers: {
                        "Accept": "application/json"
                    }
                });
                const payload = await response.json();
                if (!payload.success) return;

                const data = new Map(payload.data.map(item => [String(item.id), item]));
                cards.forEach(card => {
                    const item = data.get(card.dataset.priceId);
                    if (!item) return;
                    const price = card.querySelector('[data-price-text]');
                    const updated = card.querySelector('[data-updated-at]');
                    if (price) price.textContent = item.display_price;
                    if (updated) updated.textContent = item.updated_at;
                });
            } catch (error) {
                console.warn('Không tự cập nhật được giá trên giao diện:', error);
            }
        }

        setInterval(refreshVisibleCards, 20000);
    </script>
</body>

</html>