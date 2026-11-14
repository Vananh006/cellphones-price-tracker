@php
    $payload = $data ?? [];
    $alertObj = $alert ?? null;
    $storePriceObj = $storePrice ?? null;

    $formatVnd = function ($value) {
        if ($value === null || $value === '') {
            return 'Đang cập nhật';
        }
        return number_format((float) $value, 0, ',', '.') . 'đ';
    };

    $userName = data_get($payload, 'user_name')
        ?? data_get($alertObj, 'user.user_name')
        ?? data_get($alertObj, 'user.name')
        ?? 'bạn';

    $productName = data_get($payload, 'product_name')
        ?? data_get($storePriceObj, 'variant.product.product_name')
        ?? data_get($alertObj, 'variant.product.product_name')
        ?? 'Sản phẩm bạn theo dõi';

    $storage = data_get($payload, 'storage') ?? data_get($storePriceObj, 'variant.storage') ?? data_get($alertObj, 'variant.storage');
    $color = data_get($payload, 'color') ?? data_get($storePriceObj, 'variant.color') ?? data_get($alertObj, 'variant.color');
    $variantText = trim(($storage ? $storage : '') . ($color ? ' ' . $color : ''));
    $fullProductName = trim($productName . ($variantText ? ' - ' . $variantText : ''));

    $currentPrice = data_get($payload, 'current_price') ?? data_get($storePriceObj, 'current_price');
    $targetPrice = data_get($payload, 'target_price') ?? data_get($alertObj, 'target_price');
    $storeName = data_get($payload, 'store_name') ?? data_get($storePriceObj, 'store.store_name') ?? data_get($storePriceObj, 'store_name') ?? 'Nơi bán tốt nhất';

    $buyUrl = data_get($payload, 'buy_url')
        ?? data_get($payload, 'product_url')
        ?? data_get($payload, 'affiliate_url')
        ?? data_get($storePriceObj, 'affiliate_url')
        ?? data_get($storePriceObj, 'product_url')
        ?? data_get($alertObj, 'submitted_url')
        ?? url('/');

    $trackingUrl = data_get($payload, 'tracking_url')
        ?? (\Illuminate\Support\Facades\Route::has('tracking.index') ? route('tracking.index') : url('/theo-doi-san-pham'));

    $shopPrices = data_get($payload, 'shop_prices') ?? data_get($payload, 'prices') ?? ($shopPrices ?? []);
    if ($shopPrices instanceof \Illuminate\Support\Collection) {
        $shopPrices = $shopPrices->all();
    }

    $savedAmount = null;
    if ($currentPrice !== null && $targetPrice !== null) {
        $savedAmount = max(0, (float) $targetPrice - (float) $currentPrice);
    }
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm đã giảm giá</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:640px; max-width:94%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 12px 34px rgba(15,23,42,.10);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#ff3b30,#dc267f); padding:28px 24px; text-align:center; color:#ffffff;">
                            <div style="font-size:24px; font-weight:800; line-height:1.2;">Phone Price Tracker</div>
                            <div style="font-size:13px; font-weight:600; margin-top:8px; opacity:.95;">Crawler vừa phát hiện sản phẩm đã đạt giá mong muốn</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 28px 12px;">
                            <h1 style="margin:0 0 18px; font-size:22px; line-height:1.35; color:#111827;">Sản phẩm bạn theo dõi vừa có giá tốt!</h1>
                            <p style="margin:0; font-size:14px; line-height:1.8; color:#4b5563;">
                                Xin chào <strong style="color:#111827;">{{ $userName }}</strong>, hệ thống vừa cập nhật giá và phát hiện <strong style="color:#111827;">{{ $fullProductName }}</strong> đã thấp hơn hoặc bằng mức giá bạn mong muốn.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #fecaca; background:#fff7f4; border-radius:12px;">
                                <tr>
                                    <td style="padding:22px 18px; text-align:center;">
                                        <div style="font-size:13px; color:#6b7280; margin-bottom:10px;">Giá tốt nhất sau lần cập nhật</div>
                                        <div style="font-size:32px; font-weight:900; color:#f03b2f; line-height:1.2;">{{ $formatVnd($currentPrice) }}</div>
                                        <div style="font-size:14px; color:#374151; margin-top:12px;">Tại <strong>{{ $storeName }}</strong></div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 28px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fafafa; border-radius:10px; overflow:hidden;">
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#6b7280;">Giá bạn mong muốn</td>
                                    <td align="right" style="padding:12px 16px; font-size:13px; font-weight:800; color:#111827;">{{ $formatVnd($targetPrice) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#6b7280; border-top:1px solid #eeeeee;">Giá hiện tại</td>
                                    <td align="right" style="padding:12px 16px; font-size:13px; font-weight:800; color:#ef233c; border-top:1px solid #eeeeee;">{{ $formatVnd($currentPrice) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:13px; color:#6b7280; border-top:1px solid #eeeeee;">Thấp hơn giá mong muốn</td>
                                    <td align="right" style="padding:12px 16px; font-size:13px; font-weight:900; color:#008a3d; border-top:1px solid #eeeeee;">{{ $formatVnd($savedAmount) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    @if(!empty($shopPrices))
                    <tr>
                        <td style="padding:24px 28px 0;">
                            <h2 style="margin:0 0 12px; font-size:17px; color:#111827;">So sánh giá hiện tại</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; background:#ffffff; border:1px solid #eeeeee; border-radius:10px; overflow:hidden;">
                                <tr>
                                    <th align="left" style="padding:12px 14px; background:#f8f8f8; color:#111827; font-size:13px; border-bottom:1px solid #e5e7eb;">Cửa hàng</th>
                                    <th align="right" style="padding:12px 14px; background:#f8f8f8; color:#111827; font-size:13px; border-bottom:1px solid #e5e7eb;">Giá bán</th>
                                </tr>
                                @foreach($shopPrices as $row)
                                    @php
                                        $rowStore = data_get($row, 'store_name') ?? data_get($row, 'store.store_name') ?? 'Cửa hàng';
                                        $rowPrice = data_get($row, 'current_price') ?? data_get($row, 'price');
                                    @endphp
                                    <tr>
                                        <td style="padding:12px 14px; font-size:13px; color:#374151; border-bottom:1px solid #f0f0f0;">{{ $rowStore }}</td>
                                        <td align="right" style="padding:12px 14px; font-size:13px; color:#111827; font-weight:800; border-bottom:1px solid #f0f0f0;">{{ $formatVnd($rowPrice) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td align="center" style="padding:30px 28px 20px;">
                            <a href="{{ $buyUrl }}" target="_blank" style="display:inline-block; background:#ef3b2d; color:#ffffff; text-decoration:none; font-weight:800; font-size:14px; padding:15px 28px; border-radius:8px;">Mua tại nơi bán tốt nhất</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 26px;">
                            <div style="border-top:1px solid #e5e7eb; padding-top:18px; color:#6b7280; font-size:12px; line-height:1.8;">
                                <p style="margin:0 0 14px;">Bạn nhận được email này vì đã theo dõi giá cho sản phẩm trên.</p>
                                <p style="margin:0 0 14px;">•••</p>
                                <p style="margin:0;">Bạn có thể vào <a href="{{ $trackingUrl }}" target="_blank" style="color:#2563eb; font-weight:700;">Danh sách theo dõi</a> để quản lý hoặc hủy theo dõi.</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
