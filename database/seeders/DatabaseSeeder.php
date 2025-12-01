<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $categories = [
            ['name' => 'Đồng hồ Nam', 'description' => 'Đồng hồ dành cho nam giới', 'is_active' => true],
            ['name' => 'Đồng hồ Nữ', 'description' => 'Đồng hồ dành cho nữ giới', 'is_active' => true],
            ['name' => 'Đồng hồ Thông minh', 'description' => 'Smartwatch hiện đại', 'is_active' => true],
            ['name' => 'Đồng hồ Cơ', 'description' => 'Đồng hồ cơ học cao cấp', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Brands
        $brands = [
            ['name' => 'Rolex', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Casio', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Seiko', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Citizen', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Orient', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Tissot', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Apple', 'logo_url' => null, 'is_active' => true],
            ['name' => 'Samsung', 'logo_url' => null, 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->insert(array_merge($brand, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Products
        $products = [
            ['category_id' => 1, 'brand_id' => 1, 'name' => 'Rolex Submariner', 'description' => 'Đồng hồ lặn cao cấp', 'price' => 250000000, 'sale_price' => null, 'stock' => 5, 'is_active' => true, 'is_featured' => true],
            ['category_id' => 1, 'brand_id' => 2, 'name' => 'Casio G-Shock', 'description' => 'Đồng hồ thể thao bền bỉ', 'price' => 3500000, 'sale_price' => 2999000, 'stock' => 50, 'is_active' => true, 'is_featured' => true],
            ['category_id' => 1, 'brand_id' => 3, 'name' => 'Seiko Presage', 'description' => 'Đồng hồ cơ Nhật Bản', 'price' => 12000000, 'sale_price' => null, 'stock' => 15, 'is_active' => true, 'is_featured' => false],
            ['category_id' => 2, 'brand_id' => 4, 'name' => 'Citizen Eco-Drive', 'description' => 'Đồng hồ năng lượng ánh sáng', 'price' => 8500000, 'sale_price' => 7500000, 'stock' => 20, 'is_active' => true, 'is_featured' => false],
            ['category_id' => 2, 'brand_id' => 5, 'name' => 'Orient Bambino', 'description' => 'Đồng hồ cơ tự động', 'price' => 4500000, 'sale_price' => null, 'stock' => 30, 'is_active' => true, 'is_featured' => false],
            ['category_id' => 3, 'brand_id' => 7, 'name' => 'Apple Watch Series 9', 'description' => 'Smartwatch cao cấp từ Apple', 'price' => 12000000, 'sale_price' => 10999000, 'stock' => 40, 'is_active' => true, 'is_featured' => true],
            ['category_id' => 3, 'brand_id' => 8, 'name' => 'Samsung Galaxy Watch 6', 'description' => 'Smartwatch Android tốt nhất', 'price' => 8000000, 'sale_price' => null, 'stock' => 35, 'is_active' => true, 'is_featured' => false],
            ['category_id' => 4, 'brand_id' => 6, 'name' => 'Tissot PRX', 'description' => 'Đồng hồ thời trang Thụy Sỹ', 'price' => 15000000, 'sale_price' => null, 'stock' => 10, 'is_active' => true, 'is_featured' => false],
        ];

        foreach ($products as $product) {
            DB::table('products')->insert(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Admin user
        DB::table('users')->insert([
            'email' => 'admin@watchstore.com',
            'password' => bcrypt('password'),
            'provider' => 'LOCAL',
            'provider_id' => null,
            'name' => 'Admin',
            'phone' => '0901234567',
            'avatar_url' => null,
            'role' => 'ADMIN',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Test user
        DB::table('users')->insert([
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'provider' => 'LOCAL',
            'provider_id' => null,
            'name' => 'Test User',
            'phone' => '0907654321',
            'avatar_url' => null,
            'role' => 'USER',
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
