<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('store_name', 100)->unique();
            $table->string('base_url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('product_name', 150);
            $table->string('brand', 50)->default('Apple');
            $table->string('thumbnail_url', 255)->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('storage', 20);
            $table->string('color', 50);
            $table->string('sku', 100)->nullable();

            $table->foreign('product_id', 'fk_variant_product')
                ->references('id')->on('products')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });

        Schema::create('store_prices', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('variant_id');
            $table->unsignedInteger('store_id');
            $table->decimal('current_price', 15, 2);
            $table->decimal('original_price', 15, 2)->nullable();
            $table->string('product_url', 500);
            $table->string('affiliate_url', 1000)->nullable();
            $table->boolean('in_stock')->default(true);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('variant_id', 'fk_price_variant')
                ->references('id')->on('product_variants')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('store_id', 'fk_price_store')
                ->references('id')->on('stores')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['store_id', 'variant_id'], 'unique_store_variant');
        });

        Schema::create('price_alerts', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('variant_id');
            $table->string('submitted_url', 500);
            $table->decimal('target_price', 15, 2);
            $table->enum('alert_status', ['pending', 'triggered', 'canceled'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id', 'fk_alert_user')
                ->references('id')->on('users')
                ->cascadeOnDelete();
            $table->foreign('variant_id', 'fk_alert_variant')
                ->references('id')->on('product_variants')
                ->cascadeOnDelete();
        });

        Schema::create('price_history', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('variant_id');
            $table->unsignedInteger('store_id');
            $table->decimal('price', 15, 2);
            $table->timestamp('recorded_at')->useCurrent();

            $table->foreign('variant_id', 'fk_history_variant')
                ->references('id')->on('product_variants')
                ->cascadeOnDelete();
            $table->foreign('store_id', 'fk_history_store')
                ->references('id')->on('stores')
                ->cascadeOnDelete();
        });

        Schema::create('favorites', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('product_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id', 'fk_favorite_user')
                ->references('id')->on('users')
                ->cascadeOnDelete();
            $table->foreign('product_id', 'fk_favorite_product')
                ->references('id')->on('products')
                ->cascadeOnDelete();
            $table->unique(['user_id', 'product_id'], 'unique_user_product');
        });

        Schema::create('affiliate_click_logs', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('store_price_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('ip_address', 45);
            $table->timestamp('clicked_at')->useCurrent();

            $table->foreign('store_price_id', 'fk_log_price')
                ->references('id')->on('store_prices')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'fk_log_user')
                ->references('id')->on('users')
                ->nullOnDelete();
        });

        DB::table('stores')->insertOrIgnore([
            ['store_name' => 'CellphoneS', 'base_url' => 'https://cellphones.com.vn'],
            ['store_name' => 'FPT Shop', 'base_url' => 'https://fptshop.com.vn'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_click_logs');
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('price_history');
        Schema::dropIfExists('price_alerts');
        Schema::dropIfExists('store_prices');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('stores');
    }
};
