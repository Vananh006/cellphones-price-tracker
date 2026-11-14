<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Chi tiết sản phẩm - Price Tracker</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/chart-logic.js') }}" defer></script>
    
    <style>
        /* --- CSS GIAO DIỆN CHUNG (MỚI) --- */
        body { background-color: #f4f6f8; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .product-card { background: #fff; border-radius: 12px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .price-highlight { color: #e53935; font-size: 32px; font-weight: 700; margin: 10px 0; }
        .shop-logo { height: 40px; object-fit: contain; }
        .btn-buy { background: #ff3945; color: white; font-weight: bold; border-radius: 8px; padding: 10px 20px; transition: 0.3s; }
        .btn-buy:hover { background: #d32f2f; color: white; transform: translateY(-2px); }
        .table-compare th, .table-compare td { vertical-align: middle; }
        .badge-lowest { background-color: #4caf50; font-size: 12px; }

        /* --- CSS CHO MODAL CẢNH BÁO GIÁ (GIỮ NGUYÊN TỪ BẠN) --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; }
        /* Thêm margin để modal nằm giữa màn hình nếu dùng display:block */
        .modal-content { background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 420px; position: relative; margin: 10% auto; text-align: left; box-shadow: 0 10px 25px rgba(0,0,0,0.2);}
        .close-modal { position: absolute; top: 12px; right: 18px; font-size: 28px; cursor: pointer; color: #888; transition: 0.2s;}
        .close-modal:hover { color: #333; }
        .preset-buttons { display: flex; gap: 10px; margin-bottom: 20px; margin-top: 15px;}
        .btn-preset { flex: 1; padding: 10px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 8px; cursor: pointer; font-weight: 500; transition: 0.2s;}
        .btn-preset:hover { background: #e0e0e0; border-color: #ccc; }
        .input-wrapper { display: flex; border: 1px solid #ccc; border-radius: 8px; margin-top: 8px; overflow: hidden; }
        #targetPriceInput { flex: 1; padding: 12px; border: none; outline: none; font-size: 16px; }
        .currency-suffix { background: #f8f8f8; padding: 12px 15px; border-left: 1px solid #ccc; color: #555; font-weight: 600;}
        .btn-submit { width: 100%; padding: 14px; margin-top: 20px; background: #e50027; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.2s;}
        .btn-submit:hover { background: #cc0022; }
        .error-msg { color: #e50027; font-size: 13px; display: block; margin-top: 5px; min-height: 18px; }
    </style>
</head>
<body>

<div class="container mt-4 mb-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="#">Điện thoại</a></li>
            <li class="breadcrumb-item active" aria-current="page">iPhone 15 Pro Max 256GB</li>
        </ol>
    </nav>

    <div class="product-card">
        <div class="row">
            <div class="col-md-5 text-center">
                <img src="https://cdn2.cellphones.com.vn/insecure/rs:fill:358:358/q:90/plain/https://cellphones.com.vn/media/catalog/product/i/p/iphone-15-pro-max_3.png" alt="iPhone 15 Pro Max" class="img-fluid rounded" style="max-height: 400px;">
            </div>
            
            <div class="col-md-7">
                <h1 class="h2 fw-bold">Apple iPhone 15 Pro Max 256GB | Chính hãng VN/A</h1>
                <div class="mb-3">
                    <span class="text-warning"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></span>
                    <span class="text-muted ms-2">(45 đánh giá)</span>
                </div>
                
                <p class="text-muted mb-1">Giá tốt nhất hiện nay tại <strong>CellphoneS</strong>:</p>
                <div class="price-highlight">27.500.000đ <span class="badge badge-lowest text-white ms-2">Rẻ nhất</span></div>
                
                <ul class="list-unstyled mt-4 mb-4">
                    <li><i class="fas fa-microchip text-secondary w-20"></i> Apple A17 Pro 6 nhân</li>
                    <li><i class="fas fa-mobile-alt text-secondary w-20"></i> 6.7 inch, Super Retina XDR, 120Hz</li>
                    <li><i class="fas fa-memory text-secondary w-20"></i> RAM 8GB | ROM 256GB</li>
                </ul>

                <button class="btn btn-buy btn-lg w-50"><i class="fas fa-shopping-cart"></i> Đến nơi bán ngay</button>
                
                <button id="btnOpenAlert" class="btn btn-outline-danger btn-lg ms-2"><i class="fas fa-bell"></i> Theo dõi giá</button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            
            <div class="product-card">
                <h3 class="h5 fw-bold mb-4"><i class="fas fa-store text-primary"></i> So sánh giá các đại lý</h3>
                <div class="table-responsive">
                    <table class="table table-hover table-compare">
                        <thead class="table-light">
                            <tr>
                                <th>Đại lý</th>
                                <th>Tình trạng</th>
                                <th>Giá bán</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><img src="https://cdn.haitrieu.com/wp-content/uploads/2021/11/Logo-CellphoneS.png" class="shop-logo" alt="CellphoneS"></td>
                                <td><span class="text-success"><i class="fas fa-check-circle"></i> Còn hàng</span></td>
                                <td class="fw-bold text-danger fs-5">27.500.000đ</td>
                                <td class="text-end"><button class="btn btn-buy btn-sm">Tới cửa hàng</button></td>
                            </tr>
                            <tr>
                                <td><img src="https://cdn.haitrieu.com/wp-content/uploads/2021/11/Logo-FPT-Shop-Black.png" class="shop-logo" alt="FPT Shop"></td>
                                <td><span class="text-success"><i class="fas fa-check-circle"></i> Còn hàng</span></td>
                                <td class="fw-bold fs-5">28.490.000đ</td>
                                <td class="text-end"><button class="btn btn-outline-primary btn-sm">Tới cửa hàng</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="product-card">
                <h3 class="h5 fw-bold mb-4"><i class="fas fa-chart-line text-success"></i> Biểu đồ biến động giá</h3>
                <canvas id="priceChart" data-product-id="123" width="400" height="150"></canvas>
            </div>

            <div class="product-card" id="reviews-section">
                <h3 class="h5 fw-bold mb-4"><i class="fas fa-comments text-warning"></i> Đánh giá & Bình luận</h3>
                <p class="text-muted text-center py-4">Chúng ta sẽ code Form bình luận và Danh sách Review vào khu vực này.</p>
            </div>

        </div>

        <div class="col-md-4">
            <div class="product-card">
                <h3 class="h5 fw-bold mb-4">Thông số kỹ thuật</h3>
                <table class="table table-striped table-sm text-muted">
                    <tbody>
                        <tr><th>Màn hình</th><td>6.7 inch, 120Hz</td></tr>
                        <tr><th>Camera sau</th><td>Chính 48MP & Phụ 12MP</td></tr>
                        <tr><th>Camera trước</th><td>12 MP</td></tr>
                        <tr><th>Chipset</th><td>Apple A17 Pro 6 nhân</td></tr>
                        <tr><th>RAM</th><td>8 GB</td></tr>
                        <tr><th>Bộ nhớ trong</th><td>256 GB</td></tr>
                        <tr><th>Pin</th><td>4422 mAh, 20W</td></tr>
                        <tr><th>Hệ điều hành</th><td>iOS 17</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="priceAlertModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3 style="margin-top: 0; color: #333;">Cài đặt cảnh báo giá</h3>
        
        <p class="current-price-text" style="margin-bottom: 5px; font-size: 15px;">
            Giá tốt nhất hiện tại: 
            <strong id="currentPriceRef" data-price="28000000" style="color: #e50027; font-size: 18px;">28.000.000đ</strong> tại <strong>CellphoneS</strong>
        </p>
        <p class="shop-prices" style="color: #777; font-size: 13px; margin-bottom: 15px; margin-top: 0;">
            CellphoneS: 28.000.000đ | FPT Shop: 28.500.000đ
        </p>

        <div class="preset-buttons">
            <button class="btn-preset" data-discount="0.05">Giảm 5%</button>
            <button class="btn-preset" data-discount="0.10">Giảm 10%</button>
            <button class="btn-preset" data-discount="0.15">Giảm 15%</button>
        </div>

        <div class="input-group d-block">
            <label style="font-weight: 600; font-size: 14px; color: #444;">Hoặc nhập mức giá mong muốn:</label>
            <div class="input-wrapper">
                <input type="text" id="targetPriceInput" placeholder="Ví dụ: 27.500.000" autocomplete="off">
                <span class="currency-suffix">VNĐ</span>
            </div>
            <span id="priceError" class="error-msg"></span>
            <span id="priceSuccess" style="color: #28a745; font-size: 14px; display: block; margin-top: 5px; text-align: center; font-weight: 500;"></span>
        </div>

        <button id="btnSubmitAlert" class="btn-submit">Xác nhận theo dõi</button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>