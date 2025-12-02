<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@watchstore.com');
        $adminPassword = env('ADMIN_PASSWORD', 'Admin@123456');
        $adminName = env('ADMIN_NAME', 'Admin User');

        $admin = User::where('email', $adminEmail)->first();

        if (!$admin) {
            DB::table('users')->insert([
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'provider' => 'LOCAL',
                'provider_id' => null,
                'name' => $adminName,
                'phone' => '0000000000',
                'avatar_url' => null,
                'role' => 'ADMIN',
                'is_active' => true,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info('Admin account created successfully!');
        } else {
            $this->command->info('Admin account already exists.');

            // Update password if different
            if (!Hash::check($adminPassword, $admin->password)) {
                $admin->password = Hash::make($adminPassword);
                $admin->role = 'ADMIN';
                $admin->save();
                $this->command->info('Admin password updated!');
            }
        }
    }
}
