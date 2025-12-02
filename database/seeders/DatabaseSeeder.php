<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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

        // Call other seeders
        $this->call([
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
