<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ tài khoản - Phone Price Tracker</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-red: #f12020;
            --primary-orange: #ff7a00;
            --soft-orange: #fff0e6;
            --soft-bg: #fff7f0;
            --card-border: rgba(255, 122, 0, .18);
            --text-muted: #6b7280;
            --dark: #1f1f1f;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: linear-gradient(180deg, #fff4ed 0%, #ffffff 42%, #fff7f0 100%);
            color: var(--dark);
        }

        a {
            text-decoration: none;
        }

        .fw-black {
            font-weight: 950;
        }

        .top-nav {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(255, 255, 255, .94);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--card-border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark);
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            box-shadow: 0 12px 28px rgba(241, 32, 32, .26);
        }

        .brand strong {
            display: block;
            line-height: 1.05;
            font-size: 18px;
        }

        .brand small {
            color: var(--text-muted);
            font-weight: 600;
        }

        .nav-pill {
            padding: 9px 16px;
            border-radius: 999px;
            color: #7c2d12;
            font-weight: 850;
            border: 0;
            background: transparent;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .nav-pill:hover {
            color: var(--primary-red);
            background: var(--soft-orange);
        }

        .page-shell {
            padding: 46px 0 70px;
        }

        .hero-card {
            border-radius: 34px;
            border: 1px solid var(--card-border);
            background:
                radial-gradient(circle at top left, rgba(255, 122, 0, .16), transparent 32%),
                linear-gradient(135deg, #fff, #fff7ed);
            box-shadow: 0 24px 64px rgba(127, 29, 29, .10);
            overflow: hidden;
        }

        .hero-title {
            letter-spacing: -1.2px;
            font-weight: 950;
        }

        .section-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 999px;
            color: #e04700;
            background: var(--soft-orange);
            font-weight: 900;
            font-size: 13px;
        }

        .profile-card {
            height: 100%;
            border-radius: 30px;
            border: 1px solid var(--card-border);
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 18px 45px rgba(127, 29, 29, .08);
            overflow: hidden;
        }

        .card-head {
            padding: 22px 24px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
        }

        .card-head.dark {
            background: linear-gradient(135deg, #1f2937, #111827);
        }

        .card-head h5 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 950;
        }

        .card-body-custom {
            padding: 26px 24px 24px;
        }

        .form-label {
            font-weight: 850;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control {
            min-height: 50px;
            border-radius: 18px;
            border: 1px solid #ffd4bd;
            padding: 12px 16px;
            font-weight: 650;
            color: #111827;
            background: #fff;
        }

        .form-control:focus {
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 .22rem rgba(255, 122, 0, .14);
        }

        .form-control[disabled],
        .form-control[readonly] {
            color: #6b7280;
            background: #f9fafb;
            border-color: #e5e7eb;
        }

        .help-text {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            margin-top: 7px;
        }

        .btn-main {
            width: 100%;
            min-height: 50px;
            border: 0;
            color: #fff;
            font-weight: 950;
            border-radius: 999px;
            padding: 12px 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
            box-shadow: 0 14px 30px rgba(241, 32, 32, .20);
        }

        .btn-main:hover {
            color: #fff;
            filter: brightness(.97);
        }

        .btn-dark-main {
            width: 100%;
            min-height: 50px;
            border: 0;
            color: #fff;
            font-weight: 950;
            border-radius: 999px;
            padding: 12px 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            background: linear-gradient(135deg, #111827, #374151);
            box-shadow: 0 14px 30px rgba(17, 24, 39, .18);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 16px;
            border-radius: 999px;
            color: #7c2d12;
            background: #fff7ed;
            border: 1px solid #ffd4bd;
            font-weight: 900;
        }

        .btn-back:hover {
            color: #fff;
            border-color: transparent;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
        }

        .alert-soft-success {
            border: 1px solid #bbf7d0;
            color: #166534;
            background: #f0fdf4;
            border-radius: 20px;
            font-weight: 750;
        }

        .alert-soft-danger {
            border: 1px solid #fecaca;
            color: #991b1b;
            background: #fef2f2;
            border-radius: 20px;
            font-weight: 750;
        }

        .mini-stat {
            border-radius: 24px;
            background: #fff7ed;
            border: 1px solid #ffd4bd;
            padding: 18px;
        }

        .mini-stat .icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            color: #fff;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-orange));
        }

        @media (max-width: 768px) {
            .brand small {
                display: none;
            }

            .page-shell {
                padding-top: 28px;
            }

            .hero-card {
                border-radius: 26px;
            }

            .profile-card {
                border-radius: 24px;
            }
        }
    </style>
</head>

<body>
    <nav class="top-nav">
        <div class="container py-3 d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <a href="{{ route('home') }}" class="brand">
                <span class="brand-icon">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </span>
                <span>
                    <strong>Phone Price Tracker</strong>
                    <small>Quản lý hồ sơ tài khoản</small>
                </span>
            </a>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('home') }}" class="nav-pill">
                    <i class="fa-solid fa-house"></i>
                    Trang chủ
                </a>

                @if(Route::has('tracking.index'))
                    <a href="{{ route('tracking.index') }}" class="nav-pill">
                        <i class="fa-solid fa-bell"></i>
                        Danh sách theo dõi
                    </a>
                @endif
            </div>
        </div>
    </nav>

    <main class="page-shell">
        <div class="container">
            <a href="{{ route('home') }}" class="btn-back mb-4">
                <i class="fa-solid fa-arrow-left"></i>
                Quay lại Trang chủ
            </a>

            @if(session('success'))
                <div class="alert alert-soft-success px-4 py-3 mb-4">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-soft-danger px-4 py-3 mb-4">
                    <div class="fw-black mb-1">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Có lỗi cần kiểm tra
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="hero-card p-4 p-lg-5 mb-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="section-badge mb-3">
                            <i class="fa-solid fa-user"></i>
                            Hồ sơ cá nhân
                        </span>

                        <h1 class="hero-title mb-2">
                            Xin chào, {{ $user->user_name ?? $user->email ?? 'người dùng' }}
                        </h1>

                        <p class="text-secondary fw-semibold mb-0">
                            Cập nhật tên hiển thị và đổi mật khẩu tài khoản của bạn tại một nơi.
                            Email đăng nhập được giữ cố định để đảm bảo an toàn tài khoản.
                        </p>
                    </div>

                    <div class="col-lg-4">
                        <div class="mini-stat">
                            <div class="d-flex align-items-center gap-3">
                                <span class="icon">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <div>
                                    <div class="text-secondary fw-bold small">Email đăng nhập</div>
                                    <div class="fw-black text-break">{{ $user->email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="row g-4">
                <div class="col-lg-6">
                    <section class="profile-card">
                        <div class="card-head">
                            <h5>
                                <i class="fa-solid fa-user-pen"></i>
                                Thông tin tài khoản
                            </h5>
                        </div>

                        <div class="card-body-custom">
                            <form method="POST" action="{{ route('profile.update') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Email đăng nhập</label>
                                    <input
                                        type="email"
                                        class="form-control"
                                        value="{{ $user->email }}"
                                        readonly
                                    >
                                    <div class="help-text">
                                        Email dùng để đăng nhập và nhận thông báo giá, hiện không cho thay đổi.
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="user_name">Tên hiển thị</label>
                                    <input
                                        type="text"
                                        id="user_name"
                                        name="user_name"
                                        class="form-control @error('user_name') is-invalid @enderror"
                                        value="{{ old('user_name', $user->user_name ?? '') }}"
                                        placeholder="Nhập tên hiển thị của bạn"
                                        required
                                    >
                                    @error('user_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn-main">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                    Lưu thay đổi
                                </button>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-lg-6">
                    <section class="profile-card">
                        <div class="card-head dark">
                            <h5>
                                <i class="fa-solid fa-key"></i>
                                Đổi mật khẩu
                            </h5>
                        </div>

                        <div class="card-body-custom">
                            <form method="POST" action="{{ route('profile.changePassword') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
                                    <input
                                        type="password"
                                        id="current_password"
                                        name="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror"
                                        placeholder="Nhập mật khẩu hiện tại"
                                        autocomplete="current-password"
                                        required
                                    >
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="new_password">Mật khẩu mới</label>
                                    <input
                                        type="password"
                                        id="new_password"
                                        name="new_password"
                                        class="form-control @error('new_password') is-invalid @enderror"
                                        placeholder="Nhập ít nhất 6 ký tự"
                                        autocomplete="new-password"
                                        required
                                    >
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="new_password_confirmation">Xác nhận mật khẩu mới</label>
                                    <input
                                        type="password"
                                        id="new_password_confirmation"
                                        name="new_password_confirmation"
                                        class="form-control"
                                        placeholder="Nhập lại mật khẩu mới"
                                        autocomplete="new-password"
                                        required
                                    >
                                    <div class="help-text">
                                        Mật khẩu mới nên có ít nhất 6 ký tự và không trùng mật khẩu cũ.
                                    </div>
                                </div>

                                <button type="submit" class="btn-dark-main">
                                    <i class="fa-solid fa-lock"></i>
                                    Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
