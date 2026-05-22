<?php

namespace Database\Seeders;

use App\Models\CreditPackage;
use Illuminate\Database\Seeder;

class CreditPackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'        => 'Starter',
                'description' => 'Ideal para experimentar a plataforma',
                'credits'     => 10,
                'price'       => 9.90,
                'is_active'   => true,
            ],
            [
                'name'        => 'Popular',
                'description' => 'O mais escolhido pelos nossos usuários',
                'credits'     => 50,
                'price'       => 39.90,
                'is_active'   => true,
            ],
            [
                'name'        => 'Pro',
                'description' => 'Para uso intensivo e profissional',
                'credits'     => 100,
                'price'       => 69.90,
                'is_active'   => true,
            ],
        ];

        foreach ($packages as $package) {
            CreditPackage::firstOrCreate(['name' => $package['name']], $package);
        }
    }
}
