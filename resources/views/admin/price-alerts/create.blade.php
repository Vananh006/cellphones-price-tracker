@extends('layouts.admin')

@section('title', 'Thêm giá mong muốn')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Thêm giá mong muốn</h2>
    <p class="text-muted mb-0">Tạo mới yêu cầu giá mong muốn</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <form action="{{ route('admin.price-alerts.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">Chọn user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->email }}{{ $user->user_name ? ' - ' . $user->user_name : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Biến thể sản phẩm</label>
                    <select name="variant_id" class="form-select">
                        <option value="">Chọn biến thể</option>
                        @foreach($variants as $variant)
                            <option value="{{ $variant->id }}" {{ old('variant_id') == $variant->id ? 'selected' : '' }}>
                                {{ $variant->product_name }} - {{ $variant->storage ?? '--' }}{{ $variant->color ? ' - ' . $variant->color : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('variant_id')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label">Link người dùng gửi</label>
                    <input type="text" name="submitted_url" class="form-control" value="{{ old('submitted_url') }}">
                    @error('submitted_url')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Giá mong muốn</label>
                    <input type="number" name="target_price" class="form-control" value="{{ old('target_price') }}">
                    @error('target_price')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
<div class="col-md-6">
    <label class="form-label">Trạng thái</label>
    <select name="alert_status" class="form-select">
        <option value="pending" {{ old('alert_status', 'pending') === 'pending' ? 'selected' : '' }}>pending</option>
        <option value="triggered" {{ old('alert_status') === 'triggered' ? 'selected' : '' }}>triggered</option>
        <option value="canceled" {{ old('alert_status') === 'canceled' ? 'selected' : '' }}>canceled</option>
    </select>
    @error('alert_status')
        <small class="text-danger">{{ $message }}</small>
    @enderror
</div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lưu giá mong muốn</button>
                <a href="{{ route('admin.price-alerts.index') }}" class="btn btn-outline-secondary">Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection
