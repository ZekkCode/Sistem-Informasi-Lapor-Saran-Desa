<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin Desa',
            self::SuperAdmin => 'Super Admin',
        };
    }
}
