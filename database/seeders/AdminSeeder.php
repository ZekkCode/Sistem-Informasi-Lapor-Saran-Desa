<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('padelegan.initial_admin.password');

        if (blank($password)) {
            $this->command?->warn('Admin awal dilewati. Isi INITIAL_ADMIN_PASSWORD lalu jalankan seeder kembali.');

            return;
        }

        User::query()->updateOrCreate(
            ['username' => config('padelegan.initial_admin.username')],
            [
                'name' => config('padelegan.initial_admin.name'),
                'password' => $password,
                'role' => UserRole::SuperAdmin,
                'is_active' => true,
            ],
        );
    }
}
