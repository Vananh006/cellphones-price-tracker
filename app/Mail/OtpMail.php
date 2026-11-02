<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp; // Nhận mã OTP từ Controller truyền sang
    }

    public function build()
    {
        return $this->subject('Mã xác nhận đăng ký tài khoản')
            ->view('emails.otp')
            ->with([
                'otp' => $this->otp,
            ]);
    }
}
