@extends('layouts.admin')

@section('title', 'Danh sách theo dõi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Danh sách theo dõi</h2>
        <p class="text-muted mb-0">Các sản phẩm được người dùng thêm vào theo dõi</p>
    </div>

    <a href="{{ route('admin.favorites.create') }}" class="btn btn-primary">
        + Thêm theo dõi
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.favorites.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-10">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Tìm theo user, email, sản phẩm, thương hiệu..."
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
                        <th>User</th>
                        <th>Sản phẩm</th>
                        <th>Thương hiệu</th>
                        <th>Lượt xem</th>
                        <th>Thêm vào theo dõi lúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($favorites as $favorite)
                        <tr>
                            <td>{{ $favorite->id }}</td>
                            <td>
                                <strong>{{ $favorite->user_name ?? '--' }}</strong><br>
                                <small class="text-muted">{{ $favorite->email ?? '--' }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($favorite->thumbnail_url)
                                        <img
                                            src="{{ $favorite->thumbnail_url }}"
                                            alt="{{ $favorite->product_name }}"
                                            style="width: 56px; height: 56px; object-fit: cover; border-radius: 10px;"
                                        >
                                    @endif

                                    <div>
                                        <strong>{{ $favorite->product_name ?? '--' }}</strong>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $favorite->brand ?? '--' }}</td>
                            <td>{{ $favorite->view_count ?? 0 }}</td>
                            <td>{{ $favorite->created_at ?? '--' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.favorites.show', $favorite->id) }}" class="btn btn-sm btn-outline-info">Xem</a>
                                    <a href="{{ route('admin.favorites.edit', $favorite->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                    <form action="{{ route('admin.favorites.destroy', $favorite->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa theo dõi này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Chưa có dữ liệu theo dõi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection