<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // Check if admin already exists
        $admin = User::where('email', $adminEmail)->first();

        if (!$admin) {
            // Create admin account
            User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'role' => 'ADMIN',
                'phone' => null,
                'address' => null,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin account created successfully!');
            $this->command->info('Email: ' . $adminEmail);
            $this->command->info('Password: ' . $adminPassword);
        } else {
            $this->command->info('Admin account already exists.');

            // Update password if different
            if (!Hash::check($adminPassword, $admin->password)) {
                $admin->password = Hash::make($adminPassword);
                $admin->role = 'ADMIN'; // Ensure role is admin
                $admin->save();
                $this->command->info('Admin password updated!');
            }
        }
    }
}
