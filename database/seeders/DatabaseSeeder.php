<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Call your custom seeder first
        $this->call([
            ProvinceSeeder::class,
            BeneficiarySetupSeeder::class,
            RepaymentSeeder::class,
            // ... other seeders
        ]);

        // Then your User factory (if needed)
        \App\Models\User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
