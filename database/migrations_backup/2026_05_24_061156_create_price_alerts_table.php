<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('price_alerts', function (Blueprint $table) {
        $table->id();
        // Liên kết với bảng users (Tiêu chí 3: Dùng user_id)
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
        $table->string('product_id'); // ID của sản phẩm đang theo dõi
        $table->integer('target_price'); // Giá mong muốn
        $table->string('status')->default('active'); // active, paused
        $table->boolean('is_notified')->default(false); // Đã thông báo chưa
        $table->timestamp('last_notified_at')->nullable();
        $table->timestamps();
    });
}
};
