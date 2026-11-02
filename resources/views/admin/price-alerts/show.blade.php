@extends('layouts.admin')

@section('title', 'Chi tiết giá mong muốn')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Chi tiết giá mong muốn</h2>
    <p class="text-muted mb-0">Thông tin yêu cầu giá mong muốn của người dùng</p>
</div>

<div class="panel-card">
    <div class="p-4">
        <div class="row g-4">
            <div class="col-md-4">
                @if($alert->thumbnail_url)
                    <img
                        src="{{ $alert->thumbnail_url }}"
                        alt="{{ $alert->product_name }}"
                        class="img-fluid rounded"
                        style="max-height: 320px; object-fit: contain;"
                    >
                @else
                    <div class="text-muted">Không có ảnh</div>
                @endif
            </div>

            <div class="col-md-8">
                <p><strong>ID alert:</strong> {{ $alert->id }}</p>
                <p><strong>User:</strong> {{ $alert->user_name ?? '--' }}</p>
                <p><strong>Email:</strong> {{ $alert->email ?? '--' }}</p>

                <hr>

                <p><strong>Sản phẩm:</strong> {{ $alert->product_name ?? '--' }}</p>
                <p><strong>Thương hiệu:</strong> {{ $alert->brand ?? '--' }}</p>
                <p>
                    <strong>Biến thể:</strong>
                    {{ $alert->storage ?? '--' }}
                    {{ $alert->color ? ' - ' . $alert->color : '' }}
                </p>
                <p><strong>SKU:</strong> {{ $alert->sku ?? '--' }}</p>

                <hr>

                <p>
                    <strong>Giá mong muốn:</strong>
                    {{ number_format($alert->target_price, 0, ',', '.') }}đ
                </p>

                <p><strong>Trạng thái:</strong> {{ $alert->alert_status ?? '--' }}</p>

                <p>
                    <strong>Link người dùng gửi:</strong><br>
                    @if($alert->submitted_url)
                        <a href="{{ $alert->submitted_url }}" target="_blank" rel="noopener noreferrer">
                            {{ $alert->submitted_url }}
                        </a>
                    @else
                        --
                    @endif
                </p>

                <hr>

                <p><strong>Tạo lúc:</strong> {{ $alert->created_at ?? '--' }}</p>
                <p><strong>Cập nhật lúc:</strong> {{ $alert->updated_at ?? '--' }}</p>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('admin.price-alerts.edit', $alert->id) }}" class="btn btn-primary">Sửa</a>
            <a href="{{ route('admin.price-alerts.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection