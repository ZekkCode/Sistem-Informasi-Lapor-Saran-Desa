<?php

use App\Enums\UserRole;

return [
    'support_email' => env('SITE_SUPPORT_EMAIL'),

    /*
    |---------------------------------------------------------------------------
    | Akun petugas awal
    |---------------------------------------------------------------------------
    | Dipakai AdminSeeder untuk membuat akun bootstrap. Akun hanya dibuat saat
    | password terisi, sehingga seeder aman dijalankan ulang di production tanpa
    | menyimpan kredensial. Kosongkan kembali password setelah akun tersedia,
    | lalu kelola petugas lain melalui Panel Petugas.
    */
    'initial_accounts' => [
        [
            'key' => 'INITIAL_ADMIN',
            'role' => UserRole::SuperAdmin,
            'name' => env('INITIAL_ADMIN_NAME', 'Administrator Desa'),
            'username' => env('INITIAL_ADMIN_USERNAME', 'admin'),
            'password' => env('INITIAL_ADMIN_PASSWORD'),
        ],
        [
            'key' => 'INITIAL_ADMIN_DESA',
            'role' => UserRole::Admin,
            'name' => env('INITIAL_ADMIN_DESA_NAME', 'Admin Desa'),
            'username' => env('INITIAL_ADMIN_DESA_USERNAME', 'admindesa'),
            'password' => env('INITIAL_ADMIN_DESA_PASSWORD'),
        ],
    ],
];
