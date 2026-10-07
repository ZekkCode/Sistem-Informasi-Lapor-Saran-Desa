<?php

return [
    'support_email' => env('SITE_SUPPORT_EMAIL'),

    /*
    | Kontak bantuan situs untuk warga. Nomor dipakai untuk tautan WhatsApp
    | (hanya angka) dan tautan telepon. Ubah lewat environment bila berganti.
    */
    'support_contact_name' => env('SITE_SUPPORT_CONTACT_NAME', 'Pak Wakil Sekdes'),
    'support_whatsapp' => env('SITE_SUPPORT_WHATSAPP', '6282301842301'),
    'support_whatsapp_label' => env('SITE_SUPPORT_WHATSAPP_LABEL', '+62 823-0184-2301'),

    /*
    | Daftar email Google yang boleh masuk lewat SSO, dipisahkan koma. Bawaannya
    | akun resmi desa. Email di luar daftar selalu ditolak. Bila email di daftar
    | belum punya akun petugas, sistem membuatnya dengan peran di bawah ini.
    | Akun yang sudah ada tetap memakai perannya sendiri dan bisa dinonaktifkan
    | Super Admin untuk menutup akses.
    */
    'google_sso' => [
        'allowed_emails' => array_values(array_filter(array_map(
            static fn (string $email): string => strtolower(trim($email)),
            explode(',', (string) env('GOOGLE_SSO_ALLOWED_EMAILS', 'laporpadelegan@gmail.com')),
        ))),
        'role' => env('GOOGLE_SSO_ROLE', 'admin'),
    ],

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
            'role' => 'super_admin',
            'name' => env('INITIAL_ADMIN_NAME', 'Administrator Desa'),
            'username' => env('INITIAL_ADMIN_USERNAME', 'admin'),
            'email' => env('INITIAL_ADMIN_EMAIL'),
            'password' => env('INITIAL_ADMIN_PASSWORD'),
        ],
        [
            'key' => 'INITIAL_ADMIN_DESA',
            'role' => 'admin',
            'name' => env('INITIAL_ADMIN_DESA_NAME', 'Admin Desa'),
            'username' => env('INITIAL_ADMIN_DESA_USERNAME', 'admindesa'),
            'email' => env('INITIAL_ADMIN_DESA_EMAIL'),
            'password' => env('INITIAL_ADMIN_DESA_PASSWORD'),
        ],
    ],
];
