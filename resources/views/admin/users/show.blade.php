@extends('layouts.admin')

@section('title', 'Chi tiết user')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Chi tiết user</h2>
    <p class="text-muted mb-0">Thông tin tài khoản trong hệ thống</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <p><strong>ID:</strong> {{ $user->id }}</p>
        <p><strong>Tên user:</strong> {{ $user->user_name ?? '--' }}</p>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Role:</strong> {{ $user->role }}</p>
        <p><strong>Tạo lúc:</strong> {{ $user->created_at ?? '--' }}</p>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">Sửa</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection