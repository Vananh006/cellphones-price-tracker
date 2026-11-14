@extends('layouts.admin')

@section('title', 'Quản lý link sản phẩm')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Quản lý link sản phẩm</h2>
        <p class="text-muted mb-0">Danh sách link theo từng biến thể và cửa hàng</p>
    </div>

    <a href="{{ route('admin.product-links.create') }}" class="btn btn-primary">
        + Thêm link sản phẩm
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.product-links.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-6">
                <input type="text" name="q" class="form-control" placeholder="Tìm theo sản phẩm, màu, dung lượng..." value="{{ $q ?? '' }}">
            </div>
            <div class="col-md-4">
                <select name="store_id" class="form-select">
                    <option value="">Tất cả cửa hàng</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ (string)($storeId ?? '') === (string)$store->id ? 'selected' : '' }}>
                            {{ $store->store_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100">Lọc</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table custom-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Store</th>
                        <th>Giá hiện tại</th>
                        <th>Giá gốc</th>
                        <th>Còn hàng</th>
                        <th>Cập nhật</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($links as $link)
                        <tr>
                            <td>{{ $link->id }}</td>
                            <td>
                                <strong>{{ $link->product_name }}</strong><br>
                                <small class="text-muted">
                                    {{ $link->storage ?? '--' }}
                                    {{ $link->color ? ' - ' . $link->color : '' }}
                                    {{ $link->sku ? ' - ' . $link->sku : '' }}
                                </small>
                            </td>
                            <td>{{ $link->store_name ?? '--' }}</td>
                            <td>{{ $link->current_price ? number_format($link->current_price, 0, ',', '.') . 'đ' : '--' }}</td>
                            <td>{{ $link->original_price ? number_format($link->original_price, 0, ',', '.') . 'đ' : '--' }}</td>
                            <td>
                                @if($link->in_stock)
                                    <span class="status-badge success">Còn hàng</span>
                                @else
                                    <span class="status-badge warning">Hết hàng</span>
                                @endif
                            </td>
                            <td>{{ $link->updated_at ?? '--' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.product-links.show', $link->id) }}" class="btn btn-sm btn-outline-info">Xem</a>
                                    <a href="{{ route('admin.product-links.edit', $link->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                    <form action="{{ route('admin.product-links.destroy', $link->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa link này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Chưa có dữ liệu link sản phẩm</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection