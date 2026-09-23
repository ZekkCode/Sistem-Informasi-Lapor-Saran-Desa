<?php

namespace App\Enums;

enum StatusUsulan: string
{
    case Baru = 'baru';
    case Ditinjau = 'ditinjau';
    case Ditindaklanjuti = 'ditindaklanjuti';
    case Selesai = 'selesai';

    public function label(): string
    {
        return match ($this) {
            self::Baru => 'Baru',
            self::Ditinjau => 'Sedang Ditinjau',
            self::Ditindaklanjuti => 'Ditindaklanjuti',
            self::Selesai => 'Selesai',
        };
    }
}
