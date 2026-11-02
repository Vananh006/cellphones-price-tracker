@extends('layouts.admin')

@section('title', 'Sửa user')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Sửa user</h2>
    <p class="text-muted mb-0">Cập nhật thông tin tài khoản</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Tên user</label>
                    <input type="text" name="user_name" class="form-control" value="{{ old('user_name', $user->user_name) }}">
                    @error('user_name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control" placeholder="Để trống nếu không đổi">
                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection