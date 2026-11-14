@extends('layouts.admin')

@section('title', 'Quản lý user')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Quản lý user</h2>
        <p class="text-muted mb-0">Danh sách tài khoản trong hệ thống</p>
    </div>

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        + Thêm user
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 mb-4">
            <div class="col-md-10">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Tìm theo email, tên hoặc role..."
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
                        <th>Tên user</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tạo lúc</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->user_name ?? '--' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="status-badge primary">Admin</span>
                                @else
                                    <span class="status-badge success">User</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at ?? '--' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-info">Xem</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">Sửa</a>

                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa user này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Chưa có dữ liệu user
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection