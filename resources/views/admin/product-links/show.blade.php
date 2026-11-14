@extends('layouts.admin')

@section('title', 'Chi tiết link sản phẩm')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Chi tiết link sản phẩm</h2>
    <p class="text-muted mb-0">Thông tin chi tiết của một link sản phẩm theo từng cửa hàng</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <div class="row g-4">
            <div class="col-md-4">
                @if($link->thumbnail_url)
                    <img
                        src="{{ $link->thumbnail_url }}"
                        alt="{{ $link->product_name }}"
                        class="img-fluid rounded"
                        style="max-height: 320px; object-fit: contain;"
                    >
                @else
                    <div class="text-muted">Không có ảnh</div>
                @endif
            </div>

            <div class="col-md-8">
                <h4 class="mb-3">1. Thông tin nhận diện</h4>
                <p><strong>ID link:</strong> {{ $link->link_id }}</p>
                <p><strong>ID sản phẩm:</strong> {{ $link->product_id_real }}</p>
                <p><strong>ID biến thể:</strong> {{ $link->variant_id_real }}</p>

                <hr>

                <h4 class="mb-3">2. Thông tin sản phẩm</h4>
                <p><strong>Tên sản phẩm:</strong> {{ $link->product_name ?? '--' }}</p>
                <p><strong>Thương hiệu:</strong> {{ $link->brand ?? '--' }}</p>
                <p><strong>Ảnh đại diện:</strong> {{ $link->thumbnail_url ?? '--' }}</p>

                <hr>

                <h4 class="mb-3">3. Thông tin biến thể</h4>
                <p><strong>Dung lượng:</strong> {{ $link->storage ?? '--' }}</p>
                <p><strong>Màu sắc:</strong> {{ $link->color ?? '--' }}</p>
                <p><strong>SKU:</strong> {{ $link->sku ?? '--' }}</p>

                <hr>

                <h4 class="mb-3">4. Thông tin cửa hàng và giá</h4>
                <p><strong>Cửa hàng:</strong> {{ $link->store_name ?? '--' }}</p>
                <p><strong>Base URL:</strong> {{ $link->base_url ?? '--' }}</p>
                <p>
                    <strong>Giá hiện tại:</strong>
                    {{ $link->current_price ? number_format($link->current_price, 0, ',', '.') . 'đ' : '--' }}
                </p>
                <p>
                    <strong>Giá gốc:</strong>
                    {{ $link->original_price ? number_format($link->original_price, 0, ',', '.') . 'đ' : '--' }}
                </p>
                <p><strong>Tình trạng hàng:</strong> {{ $link->in_stock ? 'Còn hàng' : 'Hết hàng' }}</p>

                <hr>

                <h4 class="mb-3">5. Liên kết</h4>
                <p>
                    <strong>Link sản phẩm:</strong><br>
                    @if($link->product_url)
                        <a href="{{ $link->product_url }}" target="_blank" rel="noopener noreferrer">
                            {{ $link->product_url }}
                        </a>
                    @else
                        --
                    @endif
                </p>

                <p>
                    <strong>Affiliate link:</strong><br>
                    @if($link->affiliate_url)
                        <a href="{{ $link->affiliate_url }}" target="_blank" rel="noopener noreferrer">
                            {{ $link->affiliate_url }}
                        </a>
                    @else
                        --
                    @endif
                </p>

                <hr>

                <h4 class="mb-3">6. Thời gian</h4>
                <p><strong>Mốc dữ liệu link:</strong> {{ $link->updated_at ?? '--' }}</p>
                <p><strong>Tạo bản ghi sản phẩm:</strong> {{ $link->product_created_at ?? '--' }}</p>
                <p><strong>Cập nhật bản ghi sản phẩm:</strong> {{ $link->product_updated_at ?? '--' }}</p>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.product-links.edit', $link->link_id) }}" class="btn btn-primary">Sửa</a>
            <a href="{{ route('admin.product-links.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection