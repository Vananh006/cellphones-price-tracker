<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Tracker Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --main-red: #d70018;
            --dark: #212529;
            --soft-bg: #f4f6f8;
            --text-muted: #6c757d;
        }

        body {
            min-height: 100vh;
            background: var(--soft-bg);
            font-family: Arial, sans-serif;
        }

        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .auth-card {
            width: min(980px, 100%);
            border: none;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.12);
            background: #fff;
        }

        .brand-panel {
            background: linear-gradient(145deg, #212529 0%, #111418 70%);
            color: #fff;
            padding: 46px 38px;
            min-height: 520px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: rgba(215, 0, 24, 0.14);
            color: var(--main-red);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-text {
            color: #c8cdd2;
            line-height: 1.7;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            font-size: 14px;
            margin-top: 12px;
        }

        .form-panel {
            padding: 46px 42px;
        }

        .form-control {
            border-radius: 14px;
            padding: 13px 15px;
            border-color: #e0e3e7;
        }

        .form-control:focus {
            border-color: var(--main-red);
            box-shadow: 0 0 0 0.2rem rgba(215, 0, 24, 0.12);
        }

        .btn-main {
            background: var(--main-red);
            border-color: var(--main-red);
            color: #fff;
            border-radius: 14px;
            padding: 13px 18px;
            font-weight: 700;
        }

        .btn-main:hover {
            background: #b80014;
            border-color: #b80014;
            color: #fff;
        }

        .btn-outline-main {
            border-color: var(--main-red);
            color: var(--main-red);
            border-radius: 14px;
            padding: 12px 18px;
            font-weight: 700;
        }

        .btn-outline-main:hover {
            background: var(--main-red);
            color: #fff;
        }

        .link-main {
            color: var(--main-red);
            text-decoration: none;
            font-weight: 700;
        }

        .link-main:hover { color: #a80012; }

        .small-muted { color: var(--text-muted); }

        @media (max-width: 767.98px) {
            .brand-panel { min-height: auto; padding: 30px 26px; }
            .form-panel { padding: 32px 24px; }
        }
    </style>
</head>
<body>
    <main class="auth-wrapper">
        <div class="auth-card row g-0">
            <section class="col-md-5 brand-panel">
                <div>
                    <div class="brand-logo"><i class="fa-solid fa-chart-line"></i></div>
                    <h1 class="h3 brand-title mb-3">Phone Price Tracker</h1>
                    <p class="brand-text mb-0">
                        Đăng nhập để quản lý hồ sơ, theo dõi sản phẩm yêu thích và nhận thông báo khi giá điện thoại đạt mức mong muốn.
                    </p>
                    <div class="brand-pill">
                        <i class="fa-solid fa-shield-halved text-danger"></i>
                        Theo dõi giá CellphoneS & FPT Shop
                    </div>
                </div>

                <div>
                    <a href="{{ route('home') }}" class="btn btn-outline-light rounded-pill px-4">
                        <i class="fa-solid fa-house me-2"></i> Về trang chủ
                    </a>
                </div>
            </section>

            <section class="col-md-7 form-panel">
                <div class="mb-4">
                    <span class="badge rounded-pill text-bg-light border px-3 py-2 mb-3">
                        <i class="fa-solid fa-right-to-bracket text-danger me-2"></i> Đăng nhập tài khoản
                    </span>
                    <h2 class="fw-bold mb-2">Chào mừng bạn quay lại</h2>
                    <p class="small-muted mb-0">Nhập email và mật khẩu để tiếp tục sử dụng hệ thống.</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success rounded-4 border-0">
                        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger rounded-4 border-0">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-triangle-exclamation me-2"></i>Có lỗi xảy ra</div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="fa-solid fa-envelope text-danger"></i></span>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control border-start-0 rounded-end-4" placeholder="name@example.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="fa-solid fa-lock text-danger"></i></span>
                            <input type="password" id="password" name="password" class="form-control border-start-0 rounded-end-4" placeholder="Nhập mật khẩu" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label small-muted" for="remember">Ghi nhớ đăng nhập</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="link-main">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn btn-main w-100 mb-3">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Đăng nhập
                    </button>

                    <div class="text-center small-muted">
                        Chưa có tài khoản?
                        <a href="{{ route('register.form') }}" class="link-main">Đăng ký ngay</a>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
