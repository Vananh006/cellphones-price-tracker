<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Phone Price Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --main-red: #d70018;
            --dark: #111827;
            --muted: #6b7280;
            --bg: #f4f6f8;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(215, 0, 24, .10), transparent 32%),
                radial-gradient(circle at bottom right, rgba(255, 122, 0, .10), transparent 30%),
                var(--bg);
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-wrap {
            width: min(980px, 100%);
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .16);
        }

        .auth-side {
            background: linear-gradient(145deg, #111827, #262626);
            color: #fff;
            padding: 42px 34px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 30px;
        }

        .brand-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--main-red);
            color: #fff;
            font-size: 28px;
            box-shadow: 0 15px 34px rgba(215, 0, 24, .35);
        }

        .auth-side h1 {
            font-weight: 900;
            font-size: 32px;
            line-height: 1.15;
            margin-top: 22px;
        }

        .auth-side p {
            color: #d1d5db;
            margin: 0;
        }

        .step-box {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: 20px;
            padding: 18px;
        }

        .step-item {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 14px;
        }

        .step-item:last-child { margin-bottom: 0; }

        .step-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--main-red);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            flex: 0 0 auto;
        }

        .auth-main {
            padding: 42px;
        }

        .mini-title {
            color: var(--main-red);
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
            font-size: 13px;
        }

        .form-control {
            border-radius: 15px;
            padding: 13px 15px;
            border-color: #e5e7eb;
        }

        .form-control:focus {
            border-color: var(--main-red);
            box-shadow: 0 0 0 .2rem rgba(215, 0, 24, .12);
        }

        .input-group-text {
            border-color: #e5e7eb;
            color: var(--main-red);
        }

        .btn-main {
            background: var(--main-red);
            border-color: var(--main-red);
            color: #fff;
            border-radius: 15px;
            padding: 13px 18px;
            font-weight: 800;
        }

        .btn-main:hover {
            background: #b80014;
            border-color: #b80014;
            color: #fff;
        }

        .btn-outline-main {
            color: var(--main-red);
            border-color: rgba(215, 0, 24, .35);
            border-radius: 15px;
            padding: 12px 18px;
            font-weight: 800;
        }

        .btn-outline-main:hover {
            background: rgba(215, 0, 24, .08);
            color: var(--main-red);
            border-color: var(--main-red);
        }

        .link-main {
            color: var(--main-red);
            text-decoration: none;
            font-weight: 800;
        }

        .otp-input {
            text-align: center;
            font-size: 24px;
            letter-spacing: 8px;
            font-weight: 800;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 9px 14px;
            font-weight: 800;
            font-size: 13px;
            background: #fff1f2;
            color: var(--main-red);
        }

        @media (max-width: 820px) {
            .auth-wrap { grid-template-columns: 1fr; }
            .auth-side { display: none; }
            .auth-main { padding: 30px 22px; }
        }
    </style>
</head>
<body>
    @php
        $resetEmail = session('reset_email');
        $otpVerified = session('reset_otp_verified') === true;
    @endphp

    <div class="auth-wrap">
        <aside class="auth-side">
            <div>
                <div class="brand-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                <h1>Khôi phục mật khẩu an toàn</h1>
                <p>Hệ thống sẽ xác minh email bằng OTP trước khi cho phép bạn đặt mật khẩu mới.</p>
            </div>

            <div class="step-box">
                <div class="step-item">
                    <span class="step-number">1</span>
                    <div>
                        <strong>Nhập email</strong>
                        <p>Gửi mã OTP đến email tài khoản.</p>
                    </div>
                </div>
                <div class="step-item">
                    <span class="step-number">2</span>
                    <div>
                        <strong>Xác nhận OTP</strong>
                        <p>OTP đúng thì mới mở form đổi mật khẩu.</p>
                    </div>
                </div>
                <div class="step-item">
                    <span class="step-number">3</span>
                    <div>
                        <strong>Đặt mật khẩu mới</strong>
                        <p>Đổi mật khẩu và đăng nhập lại vào hệ thống.</p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="auth-main">
            <div class="mini-title mb-2">Phone Price Tracker</div>
            <h2 class="fw-bold mb-2">Quên mật khẩu</h2>
            <p class="text-muted mb-4">
                @if(!$resetEmail)
                    Nhập email đã đăng ký để nhận mã OTP.
                @elseif(!$otpVerified)
                    Mã OTP đã được gửi đến <strong>{{ $resetEmail }}</strong>. Vui lòng xác nhận OTP để tiếp tục.
                @else
                    OTP đã được xác nhận. Hãy nhập mật khẩu mới cho tài khoản <strong>{{ $resetEmail }}</strong>.
                @endif
            </p>

            @if(session('success'))
                <div class="alert alert-success rounded-4 border-0">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger rounded-4 border-0">
                    @foreach($errors->all() as $error)
                        <div><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(!$resetEmail)
                {{-- BƯỚC 1: NHẬP EMAIL GỬI OTP --}}
                <form action="{{ route('password.email') }}" method="POST">
                    @csrf
                    <label class="form-label fw-semibold" for="email">Email tài khoản</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white border-end-0 rounded-start-4">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control border-start-0 rounded-end-4"
                            placeholder="name@example.com"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-main w-100">
                        <i class="fa-solid fa-paper-plane me-2"></i>Gửi mã OTP
                    </button>
                </form>
            @elseif(!$otpVerified)
                {{-- BƯỚC 2: XÁC NHẬN OTP --}}
                <div class="mb-3">
                    <span class="status-chip">
                        <i class="fa-solid fa-shield-halved"></i>
                        Đang chờ xác nhận OTP
                    </span>
                </div>

                <form action="{{ route('password.otp.verify') }}" method="POST">
                    @csrf
                    <label class="form-label fw-semibold" for="otp">Mã OTP</label>
                    <input
                        type="text"
                        id="otp"
                        name="otp"
                        class="form-control otp-input mb-3"
                        maxlength="5"
                        placeholder="-----"
                        inputmode="numeric"
                        required
                    >

                    <button type="submit" class="btn btn-main w-100">
                        <i class="fa-solid fa-check me-2"></i>Xác nhận OTP
                    </button>
                </form>

                <form action="{{ route('password.email') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="email" value="{{ $resetEmail }}">
                    <button type="submit" class="btn btn-outline-main w-100">
                        <i class="fa-solid fa-rotate-right me-2"></i>Gửi lại OTP
                    </button>
                </form>
            @else
                {{-- BƯỚC 3: NHẬP MẬT KHẨU MỚI --}}
                <div class="mb-3">
                    <span class="status-chip" style="background:#ecfdf5;color:#047857;">
                        <i class="fa-solid fa-circle-check"></i>
                        OTP đã xác nhận
                    </span>
                </div>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">Mật khẩu mới</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-4">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control border-start-0 rounded-end-4"
                                placeholder="Tối thiểu 6 ký tự"
                                required
                            >
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="password_confirmation">Xác nhận mật khẩu mới</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-4">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control border-start-0 rounded-end-4"
                                placeholder="Nhập lại mật khẩu mới"
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn btn-main w-100">
                        <i class="fa-solid fa-key me-2"></i>Đặt lại mật khẩu
                    </button>
                </form>
            @endif

            <div class="text-center mt-4">
                <a href="{{ route('login.form') }}" class="link-main">
                    <i class="fa-solid fa-arrow-left me-1"></i>Quay lại đăng nhập
                </a>
            </div>
        </main>
    </div>
</body>
</html>
