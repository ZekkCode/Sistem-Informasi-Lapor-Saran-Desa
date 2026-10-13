<?php

namespace App\Enums;

enum JenisFormulir: string
{
    case Laporan = 'laporan';
    case Usulan = 'usulan';

    public function kataBenda(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Laporan => 'Formulir laporan',
            self::Usulan => 'Formulir usulan',
        };
    }

    public function namaSettingKunciAdmin(): string
    {
        return 'kunci_admin_'.$this->value;
    }

    public function namaSettingKunciPengembang(): string
    {
        return 'kunci_pengembang_'.$this->value;
    }

    public function namaSettingCatatan(): string
    {
        return 'catatan_kunci_'.$this->value;
    }

    public function routeBuat(): string
    {
        return match ($this) {
            self::Laporan => 'reports.create',
            self::Usulan => 'usulan.create',
        };
    }
}
