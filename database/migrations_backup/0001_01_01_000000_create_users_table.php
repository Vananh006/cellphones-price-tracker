<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration (Tạo bảng).
     */
    public function up(): void
    {
        // 1. Tạo bảng Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Dùng cho form sửa Profile
            $table->string('email')->unique(); // Dùng cho Đăng nhập
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Dùng cho Đăng nhập và Đổi mật khẩu
            $table->string('otp_code')->nullable(); // Lưu mã xác thực OTP
            $table->string('role')->default('user'); // Phân quyền Admin/User
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tạo bảng Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // 3. Tạo bảng Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Thu hồi migration (Xóa bảng).
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};