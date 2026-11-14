@extends('layouts.admin')

@section('title', 'CRUD sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Quản lý sản phẩm</h2>
        <p class="text-muted mb-0">Danh sách dòng sản phẩm chung</p>
    </div>

    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        + Thêm sản phẩm
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="panel-head">
        <div>
            <h4>Danh sách sản phẩm</h4>
            <p>Tìm kiếm theo tên hoặc thương hiệu</p>
        </div>
    </div>

    <div class="p-4">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-10">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Tìm sản phẩm..."
                    value="{{ $q ?? '' }}"
                >
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Tìm</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                @if($product->thumbnail_url)
                                    <img src="{{ $product->thumbnail_url }}" alt="thumb" style="width: 60px; height: 60px; object-fit: cover; border-radius: 10px;">
                                @else
                                    <span class="text-muted">Không có ảnh</span>
                                @endif
                            </td>
                            <td>{{ $product->product_name }}</td>
                            <td>{{ $product->brand ?? '--' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-outline-info">Xem</a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                Chưa có dữ liệu sản phẩm
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection