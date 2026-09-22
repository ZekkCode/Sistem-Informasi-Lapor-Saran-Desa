<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $created = 0;

        foreach (config('padelegan.initial_accounts', []) as $account) {
            if (blank($account['password'])) {
                $this->command?->warn(sprintf(
                    'Akun %s dilewati. Isi %s_PASSWORD lalu jalankan seeder kembali.',
                    $account['role']->label(),
                    $account['key'],
                ));

                continue;
            }

            User::query()->updateOrCreate(
                ['username' => $account['username']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'role' => $account['role'],
                    'is_active' => true,
                ],
            );

            $created++;

            $this->command?->info(sprintf(
                'Akun %s siap dipakai dengan username %s.',
                $account['role']->label(),
                $account['username'],
            ));
        }

        if ($created === 0) {
            $this->command?->warn('Belum ada akun petugas dibuat. Periksa nilai password pada berkas .env.');
        }
    }
}
