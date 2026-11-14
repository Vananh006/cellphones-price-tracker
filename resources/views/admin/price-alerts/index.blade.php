@extends('layouts.admin')

@section('title', 'Quản lý giá mong muốn')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Quản lý giá mong muốn</h2>
        <p class="text-muted mb-0">Danh sách yêu cầu giá mong muốn của người dùng</p>
    </div>

    <a href="{{ route('admin.price-alerts.create') }}" class="btn btn-primary">
        + Thêm giá mong muốn
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.price-alerts.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-6">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Tìm theo user, email, sản phẩm, trạng thái..."
                    value="{{ $q ?? '' }}"
                >
            </div>

            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statuses as $item)
                        <option value="{{ $item }}" {{ (string)($status ?? '') === (string)$item ? 'selected' : '' }}>
                            {{ $item }}
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
                        <th>User</th>
                        <th>Sản phẩm</th>
                        <th>Giá mong muốn</th>
                        <th>Trạng thái</th>
                        <th>Tạo lúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alerts as $alert)
                        <tr>
                            <td>{{ $alert->id }}</td>
                            <td>
                                <strong>{{ $alert->user_name ?? '--' }}</strong><br>
                                <small class="text-muted">{{ $alert->email ?? '--' }}</small>
                            </td>
                            <td>
                                <strong>{{ $alert->product_name ?? '--' }}</strong><br>
                                <small class="text-muted">
                                    {{ $alert->storage ?? '--' }}
                                    {{ $alert->color ? ' - ' . $alert->color : '' }}
                                </small>
                            </td>
                            <td>{{ number_format($alert->target_price, 0, ',', '.') }}đ</td>
                            <td>
                                @if($alert->alert_status === 'pending')
                                    <span class="status-badge warning">pending</span>
                                @elseif($alert->alert_status === 'triggered')
                                    <span class="status-badge success">triggered</span>
                                @elseif($alert->alert_status === 'canceled')
                                    <span class="status-badge primary">canceled</span>
                                @else
                                    <span class="status-badge">{{ $alert->alert_status ?? '--' }}</span>
                                @endif
                            </td>
                            <td>{{ $alert->created_at ?? '--' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.price-alerts.show', $alert->id) }}" class="btn btn-sm btn-outline-info">Xem</a>
                                    <a href="{{ route('admin.price-alerts.edit', $alert->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                    <form action="{{ route('admin.price-alerts.destroy', $alert->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa giá mong muốn này?')">
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
                                Chưa có dữ liệu giá mong muốn
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection