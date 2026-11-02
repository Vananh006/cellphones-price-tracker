@php
    $data = $data ?? [];
    $formatVnd = function ($price) {
        return $price !== null && $price !== '' ? number_format((int) $price, 0, ',', '.') . 'đ' : 'Đang cập nhật';
    };

    // Nhận link mua theo nhiều tên biến để tránh lỗi không khớp giữa Mail class và View.
    $buyUrl = $data['buy_url']
        ?? $data['product_url']
        ?? $buy_url
        ?? $product_url
        ?? null;

    $oldLowest = $data['old_lowest_price'] ?? null;
    $currentPrice = $data['current_price'] ?? null;
    $diff = ($oldLowest !== null && $currentPrice !== null)
        ? max((float) $oldLowest - (float) $currentPrice, 0)
        : null;
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sản phẩm vừa có giá tốt</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, sans-serif; color:#222;">
    <div style="max-width:620px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; border:1px solid #eeeeee;">
        <div style="background:linear-gradient(135deg, #1d976c, #93f9b9); padding:24px; text-align:center; color:#ffffff;">
            <h1 style="margin:0; font-size:24px;">Phone Price Tracker</h1>
            <p style="margin:8px 0 0; font-size:14px;">Phát hiện giá tốt mới cho sản phẩm bạn theo dõi</p>
        </div>

        <div style="padding:28px;">
            <h2 style="margin:0 0 12px; font-size:22px; color:#222;">Sản phẩm vừa có giá thấp nhất lịch sử!</h2>

            <p style="font-size:15px; line-height:1.6; color:#555; margin:0 0 20px;">
                Xin chào {{ $data['user_name'] ?? 'bạn' }}, hệ thống ghi nhận
                <strong>{{ $data['product_name'] ?? 'sản phẩm bạn theo dõi' }}</strong>
                đang có giá tốt hơn dữ liệu lịch sử trước đó.
            </p>

            <div style="background:#f0fff7; border:1px solid #b8f3d0; border-radius:14px; padding:20px; margin:22px 0; text-align:center;">
                <p style="margin:0; color:#777; font-size:14px;">Giá tốt nhất hiện tại</p>
                <h1 style="margin:10px 0; color:#0a8f3c; font-size:34px;">{{ $formatVnd($currentPrice) }}</h1>
                <p style="margin:0; color:#333; font-size:15px;">Tại <strong>{{ $data['best_shop'] ?? 'nơi bán tốt nhất' }}</strong></p>
            </div>

            <div style="background:#fafafa; border-radius:12px; padding:16px; margin-bottom:20px;">
                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:8px 0; color:#666;">Giá thấp nhất cũ</td>
                        <td style="padding:8px 0; text-align:right; font-weight:bold;">{{ $formatVnd($oldLowest) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#666;">Giá hiện tại</td>
                        <td style="padding:8px 0; text-align:right; font-weight:bold; color:#0a8f3c;">{{ $formatVnd($currentPrice) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:#666;">Giảm thêm</td>
                        <td style="padding:8px 0; text-align:right; font-weight:bold; color:#0a8f3c;">{{ $formatVnd($diff) }}</td>
                    </tr>
                </table>
            </div>

            @if(!empty($data['shop_prices']))
                <h3 style="font-size:17px; margin:24px 0 10px;">So sánh giá hiện tại</h3>
                <table style="width:100%; border-collapse:collapse; border:1px solid #eeeeee; border-radius:10px; overflow:hidden;">
                    <tr style="background:#fafafa;">
                        <th style="padding:12px; text-align:left; font-size:14px;">Cửa hàng</th>
                        <th style="padding:12px; text-align:right; font-size:14px;">Giá bán</th>
                    </tr>
                    @foreach($data['shop_prices'] as $shop)
                        <tr>
                            <td style="padding:12px; border-top:1px solid #eeeeee;">{{ $shop['store_name'] ?? 'Cửa hàng' }}</td>
                            <td style="padding:12px; border-top:1px solid #eeeeee; text-align:right; font-weight:bold;">{{ $formatVnd($shop['current_price'] ?? null) }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @if(!empty($buyUrl))
                <div style="text-align:center; margin:30px 0;">
                    <a href="{{ $buyUrl }}"
                       target="_blank"
                       rel="noopener"
                       style="background:#0a8f3c; color:#ffffff; text-decoration:none; padding:14px 28px; border-radius:10px; font-weight:bold; display:inline-block; font-size:16px;">
                        Mua tại nơi bán tốt nhất
                    </a>
                </div>
            @endif

            <p style="font-size:13px; line-height:1.5; color:#888; margin-top:20px; border-top:1px solid #eee; padding-top:15px;">
                Bạn nhận được email này vì đang theo dõi sản phẩm trên. Email này được gửi khi hệ thống phát hiện giá hiện tại thấp hơn mức thấp nhất trong dữ liệu lịch sử trước đó.<br>
                Quản lý theo dõi tại
                <a href="{{ $data['manage_url'] ?? url('/theo-doi-san-pham') }}" style="color:#0055ff; text-decoration:underline; font-weight:bold;">
                    Danh sách theo dõi
                </a>.
            </p>
        </div>
    </div>
</body>
</html>
