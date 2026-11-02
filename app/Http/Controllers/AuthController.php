<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'user_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'user_name.required' => 'Vui lòng nhập tên hiển thị.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ]);

        $otp = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        try {
            Mail::to($request->email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            return back()->withErrors([
                'email' => 'Lỗi gửi mail: Kiểm tra lại mạng hoặc cấu hình .env!'
            ])->withInput();
        }

        session([
            'verify_email' => $request->email,
            'temp_user_data' => [
                'user_name' => $request->user_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user',
                'otp_code' => $otp,
            ]
        ]);

        return redirect()->route('register.form')
            ->with('success', 'Mã OTP đã được gửi đến email của bạn!');
    }

    public function showOtpForm()
    {
        if (! session('verify_email')) {
            return redirect()->route('register.form');
        }

        return view('auth.register');
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:5'],
        ], [
            'otp.required' => 'Vui lòng nhập mã OTP.',
            'otp.digits' => 'Mã OTP phải gồm 5 số.',
        ]);

        $tempUser = session('temp_user_data');

        if (! $tempUser) {
            return redirect()->route('register.form')
                ->withErrors(['otp' => 'Phiên xác minh đã hết hạn. Vui lòng đăng ký lại.']);
        }

        if ($tempUser['otp_code'] !== $validated['otp']) {
            return back()->withErrors(['otp' => 'Mã OTP không chính xác.']);
        }

        if (User::where('email', $tempUser['email'])->exists()) {
            session()->forget(['verify_email', 'temp_user_data']);

            return redirect()->route('login.form')
                ->withErrors(['email' => 'Email này đã tồn tại. Vui lòng đăng nhập.']);
        }

        $user = User::create([
            'user_name' => $tempUser['user_name'],
            'email' => $tempUser['email'],
            'password' => $tempUser['password'],
            'role' => $tempUser['role'] ?? 'user',
        ]);

        Auth::login($user);

        session()->forget(['verify_email', 'temp_user_data']);

        return redirect()->route('home')
            ->with('success', 'Đăng ký và xác minh tài khoản thành công.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Không dùng remember_token vì bảng users trong SQL hiện tại không có cột remember_token.
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công.');
        }

        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Email hoặc mật khẩu không chính xác.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Đăng xuất thành công.');
    }

public function showForgotPasswordForm()
{
    return view('auth.forgot-password');
}

public function sendResetOtp(Request $request)
{
    $validated = $request->validate([
        'email' => ['required', 'email', 'exists:users,email'],
    ], [
        'email.required' => 'Vui lòng nhập email.',
        'email.email' => 'Email không hợp lệ.',
        'email.exists' => 'Email này chưa được đăng ký trong hệ thống.',
    ]);

    $otp = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

    try {
        Mail::to($validated['email'])->send(new OtpMail($otp));
    } catch (\Throwable $e) {
        return back()
            ->withInput()
            ->withErrors(['email' => 'Không gửi được OTP. Hãy kiểm tra cấu hình MAIL trong file .env.']);
    }

    session([
        'reset_email' => $validated['email'],
        'reset_otp' => $otp,
        'reset_otp_verified' => false,
    ]);

    return redirect()
        ->route('password.request')
        ->with('success', 'Mã OTP đã được gửi đến email của bạn.');
}

public function verifyResetOtp(Request $request)
{
    $validated = $request->validate([
        'otp' => ['required', 'digits:5'],
    ], [
        'otp.required' => 'Vui lòng nhập mã OTP.',
        'otp.digits' => 'Mã OTP phải gồm đúng 5 số.',
    ]);

    $email = session('reset_email');
    $otp = session('reset_otp');

    if (!$email || !$otp) {
        return redirect()
            ->route('password.request')
            ->withErrors(['otp' => 'Phiên đặt lại mật khẩu đã hết hạn. Vui lòng gửi lại OTP.']);
    }

    if ($otp !== $validated['otp']) {
        return back()->withErrors(['otp' => 'Mã OTP không chính xác.']);
    }

    session(['reset_otp_verified' => true]);

    return redirect()
        ->route('password.request')
        ->with('success', 'Xác nhận OTP thành công. Vui lòng nhập mật khẩu mới.');
}

public function resetPasswordWithOtp(Request $request)
{
    $validated = $request->validate([
        'password' => ['required', 'string', 'min:6', 'confirmed'],
    ], [
        'password.required' => 'Vui lòng nhập mật khẩu mới.',
        'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
    ]);

    $email = session('reset_email');
    $otpVerified = session('reset_otp_verified') === true;

    if (!$email || !$otpVerified) {
        return redirect()
            ->route('password.request')
            ->withErrors(['otp' => 'Bạn cần xác nhận OTP trước khi đặt lại mật khẩu.']);
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        session()->forget(['reset_email', 'reset_otp', 'reset_otp_verified']);

        return redirect()
            ->route('password.request')
            ->withErrors(['email' => 'Không tìm thấy tài khoản cần đặt lại mật khẩu.']);
    }

    $user->update([
        'password' => Hash::make($validated['password']),
    ]);

    session()->forget(['reset_email', 'reset_otp', 'reset_otp_verified']);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()
        ->route('home')
        ->with('success', 'Đặt lại mật khẩu thành công.');
}

public function cancelOtp()
{
    session()->forget([
        'verify_email',
        'temp_user_data',
        'reset_email',
        'reset_otp',
        'reset_otp_verified',
    ]);

    return redirect()->route('login.form');
}

}