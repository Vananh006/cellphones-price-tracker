@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="hero-card">
            <div class="hero-text">
                <h2>Dashboard Admin 🎉</h2>
                <p>
                    Khu vực quản trị hệ thống Cellphones Price Tracker.
                    Dữ liệu bên dưới sẽ được cập nhật từ hệ thống thực tế.
                </p>
                <button class="btn btn-purple">Xem chi tiết</button>
            </div>

            <div class="hero-illustration">
                <div class="hero-circle"></div>
                <i class="bi bi-graph-up-arrow hero-icon"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6">
        <div class="mini-card">
            <div class="mini-icon green"><i class="bi bi-box-seam"></i></div>
            <div class="mini-title">Sản phẩm</div>
            <div class="mini-value">{{ $totalProducts ?? '--' }}</div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6">
        <div class="mini-card">
            <div class="mini-icon blue"><i class="bi bi-people"></i></div>
            <div class="mini-title">Users</div>
            <div class="mini-value">{{ $totalUsers ?? '--' }}</div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-head">
                <div>
                    <h4>Tổng quan theo dõi giá</h4>
                    <p>Dữ liệu hệ thống thực tế</p>
                </div>
            </div>

            <div id="overviewChart"></div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel-card h-100">
            <div class="panel-head">
                <div>
                    <h4>Tăng trưởng</h4>
                    <p>Hiệu suất hệ thống</p>
                </div>
            </div>
            <div id="growthChart"></div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="panel-card">
            <div class="panel-head">
                <div>
                    <h4>Sản phẩm gần đây</h4>
                    <p>Dữ liệu lấy từ hệ thống</p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table custom-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Giá hiện tại</th>
                            <th>Nguồn</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentProducts as $product)
                        <tr>
                            <td>
                                {{ $product->product_name ?? '--' }}
                                @if($product->storage || $product->color)
                                <br>
                                <small class="text-muted">
                                    {{ $product->storage ?? '' }}
                                    {{ $product->color ? ' - ' . $product->color : '' }}
                                </small>
                                @endif
                            </td>
                            <td>
                                {{ isset($product->current_price) ? number_format($product->current_price, 0, ',', '.') . 'đ' : '--' }}
                            </td>
                            <td>{{ $product->store_name ?? '--' }}</td>
                            <td><span class="status-badge primary">Đã đồng bộ</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Chưa có dữ liệu sản phẩm
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel-card">
            <div class="panel-head">
                <h3>Hoạt động gần đây</h3>
                <p>Nhật ký thao tác</p>
            </div>

            <div class="panel-body-custom">
                @if(!empty($recentActivities) && count($recentActivities) > 0)
                @foreach($recentActivities as $activity)
                <div class="activity-item">
                    {{ $activity }}
                </div>
                @endforeach
                @else
                <div class="empty-state">
                    Chưa có dữ liệu hoạt động
                </div>
                @endif
            </div>
        </div>


    </div>
    @endsection

    @section('scripts')
    <script>
        const overviewOptions = {
            chart: {
                type: 'bar',
                height: 320,
                toolbar: {
                    show: false
                }
            },
            series: [],
            xaxis: {
                categories: []
            },
            noData: {
                text: 'Chưa có dữ liệu biểu đồ'
            }
        };

        new ApexCharts(document.querySelector("#overviewChart"), overviewOptions).render();

        const growthOptions = {
            chart: {
                type: 'radialBar',
                height: 280
            },
            series: [0],
            labels: ['Growth'],
            noData: {
                text: 'Chưa có dữ liệu'
            }
        };

        new ApexCharts(document.querySelector("#growthChart"), growthOptions).render();
    </script>
    @endsection