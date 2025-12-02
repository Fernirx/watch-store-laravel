<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Rolex',
                'description' => 'Swiss luxury watchmaker known for precision and prestige',
                'country' => 'Switzerland',
                'website' => 'https://www.rolex.com',
                'is_active' => true,
            ],
            [
                'name' => 'Omega',
                'description' => 'Swiss luxury watchmaker with a rich heritage in precision timekeeping',
                'country' => 'Switzerland',
                'website' => 'https://www.omegawatches.com',
                'is_active' => true,
            ],
            [
                'name' => 'Seiko',
                'description' => 'Japanese watchmaker renowned for innovation and quality',
                'country' => 'Japan',
                'website' => 'https://www.seiko.com',
                'is_active' => true,
            ],
            [
                'name' => 'Casio',
                'description' => 'Japanese electronics company famous for G-Shock watches',
                'country' => 'Japan',
                'website' => 'https://www.casio.com',
                'is_active' => true,
            ],
            [
                'name' => 'Apple',
                'description' => 'Technology company producing the Apple Watch smartwatch',
                'country' => 'United States',
                'website' => 'https://www.apple.com',
                'is_active' => true,
            ],
            [
                'name' => 'Tag Heuer',
                'description' => 'Swiss luxury watchmaker specializing in sports watches',
                'country' => 'Switzerland',
                'website' => 'https://www.tagheuer.com',
                'is_active' => true,
            ],
            [
                'name' => 'Citizen',
                'description' => 'Japanese watchmaker known for Eco-Drive technology',
                'country' => 'Japan',
                'website' => 'https://www.citizenwatch.com',
                'is_active' => true,
            ],
            [
                'name' => 'Tissot',
                'description' => 'Swiss watchmaker offering quality timepieces at accessible prices',
                'country' => 'Switzerland',
                'website' => 'https://www.tissotwatches.com',
                'is_active' => true,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
