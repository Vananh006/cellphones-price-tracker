<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Tracker Pro</title>
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
            min-height: 560px;
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

        .brand-title { font-weight: 800; letter-spacing: -0.5px; }
        .brand-text { color: #c8cdd2; line-height: 1.7; }

        .brand-list {
            list-style: none;
            padding: 0;
            margin: 22px 0 0;
        }

        .brand-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #d7dce1;
            margin-bottom: 12px;
        }

        .brand-list i { color: var(--main-red); }

        .form-panel { padding: 46px 42px; }

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

        .link-main {
            color: var(--main-red);
            text-decoration: none;
            font-weight: 700;
        }

        .link-main:hover { color: #a80012; }
        .small-muted { color: var(--text-muted); }

        .otp-input {
            font-size: 28px;
            text-align: center;
            letter-spacing: 12px;
            font-weight: 700;
            padding: 16px;
        }

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
                        Tạo tài khoản để lưu danh sách theo dõi, nhận email khi giá đạt mức mong muốn và truy cập hồ sơ cá nhân.
                    </p>
                    <ul class="brand-list">
                        <li><i class="fa-solid fa-check-circle"></i> Theo dõi sản phẩm theo màu và dung lượng</li>
                        <li><i class="fa-solid fa-check-circle"></i> So sánh giá giữa CellphoneS và FPT Shop</li>
                        <li><i class="fa-solid fa-check-circle"></i> Nhận thông báo giá qua email</li>
                    </ul>
                </div>

                <div>
                    <a href="{{ route('home') }}" class="btn btn-outline-light rounded-pill px-4">
                        <i class="fa-solid fa-house me-2"></i> Về trang chủ
                    </a>
                </div>
            </section>

            <section class="col-md-7 form-panel">
                @if(session('verify_email'))
                    <div class="mb-4">
                        <span class="badge rounded-pill text-bg-light border px-3 py-2 mb-3">
                            <i class="fa-solid fa-key text-danger me-2"></i> Xác nhận OTP
                        </span>
                        <h2 class="fw-bold mb-2">Kiểm tra email của bạn</h2>
                        <p class="small-muted mb-0">
                            Mã OTP 5 số đã được gửi đến <strong>{{ session('verify_email') }}</strong>.
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success rounded-4 border-0">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-4 border-0">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('otp.verify') }}" method="POST">
                        @csrf
                        <label for="otp" class="form-label fw-semibold">Mã OTP</label>
                        <input type="text" id="otp" name="otp" class="form-control otp-input mb-3" maxlength="5" placeholder="-----" required>
                        <button type="submit" class="btn btn-main w-100 mb-3">
                            <i class="fa-solid fa-circle-check me-2"></i> Xác nhận & Đăng nhập
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="{{ route('otp.cancel') }}" class="link-main">
                            <i class="fa-solid fa-arrow-rotate-left me-1"></i> Nhập lại từ đầu
                        </a>
                    </div>
                @else
                    <div class="mb-4">
                        <span class="badge rounded-pill text-bg-light border px-3 py-2 mb-3">
                            <i class="fa-solid fa-user-plus text-danger me-2"></i> Đăng ký tài khoản
                        </span>
                        <h2 class="fw-bold mb-2">Tạo tài khoản mới</h2>
                        <p class="small-muted mb-0">Điền thông tin bên dưới để bắt đầu theo dõi giá sản phẩm.</p>
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

                    <form method="POST" action="{{ route('register') }}" id="registerForm">
                        @csrf
                        <div class="mb-3">
                            <label for="user_name" class="form-label fw-semibold">Họ và tên</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="fa-solid fa-user text-danger"></i></span>
                                <input type="text" id="user_name" name="user_name" value="{{ old('user_name', old('name')) }}" class="form-control border-start-0 rounded-end-4" placeholder="Nhập họ tên" required>
                            </div>
                            <input type="hidden" id="name" name="name" value="{{ old('name', old('user_name')) }}">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="fa-solid fa-envelope text-danger"></i></span>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control border-start-0 rounded-end-4" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="fa-solid fa-lock text-danger"></i></span>
                                <input type="password" id="password" name="password" class="form-control border-start-0 rounded-end-4" placeholder="Tối thiểu 6 ký tự" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-4"><i class="fa-solid fa-lock text-danger"></i></span>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control border-start-0 rounded-end-4" placeholder="Nhập lại mật khẩu" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-main w-100 mb-3">
                            <i class="fa-solid fa-user-plus me-2"></i> Đăng ký
                        </button>

                        <div class="text-center small-muted">
                            Đã có tài khoản?
                            <a href="{{ route('login.form') }}" class="link-main">Đăng nhập</a>
                        </div>
                    </form>
                @endif
            </section>
        </div>
    </main>

    <script>
        const userNameInput = document.getElementById('user_name');
        const nameInput = document.getElementById('name');
        if (userNameInput && nameInput) {
            const syncName = () => { nameInput.value = userNameInput.value; };
            userNameInput.addEventListener('input', syncName);
            syncName();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
