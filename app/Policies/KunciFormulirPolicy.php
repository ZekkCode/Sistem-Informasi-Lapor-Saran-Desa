<?php

namespace App\Policies;

use App\Enums\JenisFormulir;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Ability menerima jenis formulir agar saat peran dipecah menjadi Admin
 * Pengaduan dan Admin Usulan, cukup cocokkan $formulir dengan modul peran di
 * sini tanpa mengubah controller maupun route.
 */
class KunciFormulirPolicy
{
    public function ubahKunciAdmin(User $user, JenisFormulir $formulir): Response
    {
        return in_array($user->role, [UserRole::Admin, UserRole::SuperAdmin], true)
            ? Response::allow()
            : Response::deny('Akun ini tidak dapat mengubah kunci formulir.');
    }

    public function ubahKunciPengembang(User $user, JenisFormulir $formulir): Response
    {
        return $user->role === UserRole::SuperAdmin
            ? Response::allow()
            : Response::deny('Kunci pengembang hanya dapat diubah super admin.');
    }
}
