<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case AdminPengaduan = 'admin_pengaduan';
    case AdminUsulan = 'admin_usulan';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin Desa',
            self::SuperAdmin => 'Super Admin',
            self::AdminPengaduan => 'Admin Pengaduan',
            self::AdminUsulan => 'Admin Usulan',
        };
    }
}
