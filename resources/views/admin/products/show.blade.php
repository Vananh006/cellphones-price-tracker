@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Chi tiết sản phẩm</h2>
    <p class="text-muted mb-0">Thông tin sản phẩm trong hệ thống</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <div class="row g-4">
            <div class="col-md-4">
                @if($product->thumbnail_url)
                    <img src="{{ $product->thumbnail_url }}" alt="thumb" class="img-fluid rounded">
                @else
                    <div class="text-muted">Không có ảnh</div>
                @endif
            </div>

            <div class="col-md-8">
                <p><strong>ID:</strong> {{ $product->id }}</p>
                <p><strong>Tên sản phẩm:</strong> {{ $product->product_name }}</p>
                <p><strong>Thương hiệu:</strong> {{ $product->brand ?? '--' }}</p>
                <p><strong>Thumbnail URL:</strong> {{ $product->thumbnail_url ?? '--' }}</p>
                <p><strong>Tạo lúc:</strong> {{ $product->created_at }}</p>
                <p><strong>Cập nhật lúc:</strong> {{ $product->updated_at }}</p>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">Sửa</a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection