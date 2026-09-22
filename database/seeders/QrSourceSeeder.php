<?php

namespace Database\Seeders;

use App\Models\Dusun;
use App\Models\QrSource;
use Illuminate\Database\Seeder;

class QrSourceSeeder extends Seeder
{
    public function run(): void
    {
        Dusun::query()->orderBy('id')->get()->each(function (Dusun $dusun, int $index) {
            QrSource::query()->updateOrCreate(
                ['code' => 'DSN'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)],
                [
                    'name' => 'QR Dusun '.$dusun->name,
                    'dusun_id' => $dusun->id,
                    'placement' => 'Titik informasi Dusun '.$dusun->name,
                    'is_active' => true,
                ],
            );
        });
    }
}
