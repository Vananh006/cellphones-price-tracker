<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracked_products', function (Blueprint $table) {
            $table->id();
            // Khóa ngoại liên kết với bảng users (khi user bị xóa, dữ liệu theo dõi này cũng xóa theo)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('product_id'); // Dùng để lưu Mã SP hoặc Link SP
            $table->decimal('desired_price', 15, 2); // Lưu giá mong muốn
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracked_products');
    }
};

?>