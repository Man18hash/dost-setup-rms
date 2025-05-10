<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run()
    {
        $list = ['Batanes','Cagayan','Isabela','Nueva Vizcaya','Quirino'];
        foreach ($list as $name) {
            DB::table('provinces')->insert([
                'name'       => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
