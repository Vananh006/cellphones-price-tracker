@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Sửa sản phẩm</h2>
    <p class="text-muted mb-0">Cập nhật thông tin sản phẩm</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.products.update', $product) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $product->product_name) }}">
                    @error('product_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Thương hiệu</label>
                    <input type="text" name="brand" class="form-control" value="{{ old('brand', $product->brand) }}">
                    @error('brand')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Thumbnail URL</label>
                    <input type="text" name="thumbnail_url" class="form-control" value="{{ old('thumbnail_url', $product->thumbnail_url) }}">
                    @error('thumbnail_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection