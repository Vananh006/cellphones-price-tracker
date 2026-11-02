@extends('layouts.admin')

@section('title', 'Thêm link sản phẩm')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Thêm link sản phẩm</h2>
    <p class="text-muted mb-0">Tạo liên kết sản phẩm theo cửa hàng</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.product-links.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Biến thể sản phẩm</label>
                    <select name="variant_id" class="form-select">
                        <option value="">Chọn biến thể</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}">
                                {{ $variant->product_name }} - {{ $variant->storage ?? '--' }}{{ $variant->color ? ' - ' . $variant->color : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Cửa hàng</label>
                    <select name="store_id" class="form-select">
                        <option value="">Chọn cửa hàng</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->store_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Giá hiện tại</label>
                    <input type="number" name="current_price" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Giá gốc</label>
                    <input type="number" name="original_price" class="form-control">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Product URL</label>
                    <input type="text" name="product_url" class="form-control">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Affiliate URL</label>
                    <input type="text" name="affiliate_url" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Tình trạng hàng</label>
                    <select name="in_stock" class="form-select">
                        <option value="1">Còn hàng</option>
                        <option value="0">Hết hàng</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lưu link sản phẩm</button>
                <a href="{{ route('admin.product-links.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection