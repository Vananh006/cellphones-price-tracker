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

    $alertId = data_get($payload, 'alert_id') ?? data_get($alertObj, 'id');

    $continueUrl = data_get($payload, 'continue_url')
        ?? ($alertId && \Illuminate\Support\Facades\Route::has('tracking.continueFromEmail') ? route('tracking.continueFromEmail', $alertId) : url('/theo-doi-san-pham'));

    $cancelUrl = data_get($payload, 'cancel_url')
        ?? ($alertId && \Illuminate\Support\Facades\Route::has('tracking.cancelFromEmail') ? route('tracking.cancelFromEmail', $alertId) : url('/theo-doi-san-pham'));

    $trackingUrl = data_get($payload, 'tracking_url')
        ?? (\Illuminate\Support\Facades\Route::has('tracking.index') ? route('tracking.index') : url('/theo-doi-san-pham'));

    $gap = null;
    if ($currentPrice !== null && $targetPrice !== null) {
        $gap = max(0, (float) $currentPrice - (float) $targetPrice);
    }
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bạn có muốn tiếp tục theo dõi?</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="width:640px; max-width:94%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 12px 34px rgba(15,23,42,.10);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#ff3b30,#dc267f); padding:28px 24px; text-align:center; color:#ffffff;">
                            <div style="font-size:24px; font-weight:800; line-height:1.2;">Phone Price Tracker</div>
                            <div style="font-size:13px; font-weight:600; margin-top:8px; opacity:.95;">Sản phẩm chưa đạt giá sau 3 ngày theo dõi</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 28px 12px;">
                            <h1 style="margin:0 0 18px; font-size:22px; line-height:1.35; color:#111827;">Bạn có muốn tiếp tục theo dõi không?</h1>
                            <p style="margin:0; font-size:14px; line-height:1.8; color:#4b5563;">
                                Xin chào <strong style="color:#111827;">{{ $userName }}</strong>, sản phẩm <strong style="color:#111827;">{{ $fullProductName }}</strong> đã được theo dõi trong 3 ngày nhưng hiện vẫn chưa thấp hơn mức giá bạn mong muốn.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px 0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #fed7aa; background:#fff7ed; border-radius:12px;">
                                <tr>
                                    <td style="padding:22px 18px; text-align:center;">
                                        <div style="font-size:13px; color:#6b7280; margin-bottom:10px;">Giá tốt nhất hiện tại</div>
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
                                    <td style="padding:12px 16px; font-size:13px; color:#6b7280; border-top:1px solid #eeeeee;">Còn cao hơn giá mong muốn</td>
                                    <td align="right" style="padding:12px 16px; font-size:13px; font-weight:900; color:#dc2626; border-top:1px solid #eeeeee;">{{ $formatVnd($gap) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding:30px 28px 20px;">
                            <a href="{{ $continueUrl }}" target="_blank" style="display:inline-block; background:#ef3b2d; color:#ffffff; text-decoration:none; font-weight:800; font-size:14px; padding:15px 24px; border-radius:8px; margin:0 6px 10px;">Tiếp tục theo dõi</a>
                            <a href="{{ $cancelUrl }}" target="_blank" style="display:inline-block; background:#f3f4f6; color:#374151; text-decoration:none; font-weight:800; font-size:14px; padding:15px 24px; border-radius:8px; margin:0 6px 10px; border:1px solid #e5e7eb;">Hủy theo dõi</a>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 28px 26px;">
                            <div style="border-top:1px solid #e5e7eb; padding-top:18px; color:#6b7280; font-size:12px; line-height:1.8;">
                                <p style="margin:0 0 14px;">Nếu bạn chọn tiếp tục, hệ thống sẽ giữ sản phẩm trong danh sách theo dõi và kiểm tra lại ở các lần cập nhật sau.</p>
                                <p style="margin:0;">Bạn cũng có thể vào <a href="{{ $trackingUrl }}" target="_blank" style="color:#2563eb; font-weight:700;">Danh sách theo dõi</a> để quản lý thủ công.</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
