<?php

namespace Database\Seeders;

use App\Models\Dusun;
use Illuminate\Database\Seeder;

class DusunSeeder extends Seeder
{
    public function run(): void
    {
        $dusuns = [
            ['code' => 'BKL', 'name' => 'Bangkal'],
            ['code' => 'ASB', 'name' => 'Asam Batur'],
            ['code' => 'LTB', 'name' => 'Laok Tambak'],
            ['code' => 'MDG', 'name' => 'Modung'],
            ['code' => 'DTB', 'name' => 'Dajah Tambak'],
            ['code' => 'MRA', 'name' => 'Muarah'],
        ];

        foreach ($dusuns as $dusun) {
            Dusun::query()->updateOrCreate(
                ['code' => $dusun['code']],
                [...$dusun, 'is_active' => true],
            );
        }
    }
}
