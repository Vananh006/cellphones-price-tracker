<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder tối giản để không lỗi với schema users của team.
     * Dữ liệu sản phẩm/giá đầy đủ nằm trong file database.sql.
     */
    public function run(): void
    {
        DB::table('stores')->insertOrIgnore([
            ['store_name' => 'CellphoneS', 'base_url' => 'https://cellphones.com.vn'],
            ['store_name' => 'FPT Shop', 'base_url' => 'https://fptshop.com.vn'],
        ]);

        DB::table('users')->insertOrIgnore([
            [
                'email' => 'demo@example.com',
                'password' => Hash::make('password'),
                'user_name' => 'Demo User',
                'role' => 'user',
                'created_at' => now(),
            ],
        ]);
    }
}
