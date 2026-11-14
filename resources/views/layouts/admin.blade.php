<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('admin-dashboard/css/style.css') }}">
</head>

<body>
    <div class="admin-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-title">
                    <strong>Phone price tracker</strong>
                    <span>Admin Panel</span>
                </div>
            </div>

            <nav class="menu mt-4">


                <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.products.index') }}" class="menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>CRUD sản phẩm</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Quản lý user</span>
                </a>

                <a href="{{ route('admin.product-links.index') }}" class="menu-link {{ request()->routeIs('admin.product-links.*') ? 'active' : '' }}">
                    <i class="bi bi-link-45deg"></i>
                    <span>Link sản phẩm</span>
                </a>

                <a href="{{ route('admin.price-alerts.index') }}" class="menu-link {{ request()->routeIs('admin.price-alerts.*') ? 'active' : '' }}">
                    <i class="bi bi-bookmark-heart"></i>
                    <span>Giá mong muốn</span>
                </a>

                <a href="{{ route('admin.affiliate.index') }}" class="menu-link">
                    <i class="bi bi-megaphone"></i>
                    <span>Affiliate link</span>
                </a>

                <a href="{{ route('admin.favorites.index') }}" class="menu-link {{ request()->routeIs('admin.favorites.*') ? 'active' : '' }}">
                    <i class="bi bi-list-check"></i>
                    <span>Danh sách theo dõi</span>
                </a>

                <a href="{{ url('/admin/report') }}" class="menu-link">
                    <i class="bi bi-bar-chart"></i>
                    <span>Report</span>
                </a>

                <a href="{{ route('admin.export') }}" class="menu-link">
                    <i class="bi bi-download"></i>
                    <span>Export file</span>
                </a>
            </nav>
        </aside>

        <main class="main-panel">
            <header class="topbar">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" class="search-input" placeholder="Search..." disabled>
                </div>

                <div class="topbar-actions">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary topbar-home-btn">
                        <i class="bi bi-house-door me-1"></i>
                        Về trang chủ
                    </a>

                    <button type="button" class="topbar-icon">
                        <i class="bi bi-bell"></i>
                    </button>

                    <div class="admin-user">
                        <div class="avatar">A</div>
                        <div class="admin-user-info">
                            <strong>Admin</strong>
                            <span>Quản trị viên</span>
                        </div>
                    </div>
                </div>
            </header>

            <section class="content-area">
                @yield('content')
            </section>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @yield('scripts')
</body>

</html>