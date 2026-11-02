<div class="product-card p-3">
    @if(!empty($featured))
        <span class="deal-ribbon">Nổi bật</span>
    @endif

    <a href="{{ route('products.show', $card['slug']) }}" class="text-dark">
        <div class="product-img-wrap mb-3">
            <img src="{{ asset($card['image']) }}" class="product-img" alt="{{ $card['model'] }}">
        </div>
    </a>

    <div class="px-1 pb-1">
        <a href="{{ route('products.show', $card['slug']) }}" class="text-dark">
            <h3 class="h5 fw-black mb-2">{{ $card['model'] }}</h3>
        </a>

        @if(!empty($featured))
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge rounded-pill text-bg-light border">{{ $card['variant_count'] }} phiên bản</span>
                <span class="badge rounded-pill text-bg-light border">{{ $card['storage_count'] }} dung lượng</span>
                <span class="badge rounded-pill text-bg-light border">{{ $card['color_count'] }} màu</span>
            </div>

            <div class="mini-label">Giá thấp nhất đang ghi nhận</div>
            <div class="price mb-1">{{ $formatVnd($card['min_price']) }}</div>

            <div class="small text-secondary mb-3">
                @if($card['cheaper_shop'])
                    Rẻ nhất tại <strong>{{ $card['cheaper_shop'] }}</strong>
                @else
                    Đang cập nhật nơi bán
                @endif
                <br>
                Chênh lệch cao nhất: <strong>{{ $formatVnd($card['price_gap']) }}</strong>
            </div>
        @else
            <p class="text-secondary small mb-3">Bấm xem chi tiết để chọn màu sắc, dung lượng và so sánh giá giữa CellphoneS với FPT Shop.</p>
        @endif

        <a href="{{ route('products.show', $card['slug']) }}" class="btn btn-main w-100">Xem chi tiết</a>
    </div>
</div>
