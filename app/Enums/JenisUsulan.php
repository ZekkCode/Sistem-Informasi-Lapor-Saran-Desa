<?php

namespace App\Enums;

enum JenisUsulan: string
{
    case Pembangunan = 'pembangunan';
    case Pelayanan = 'pelayanan';
    case Kegiatan = 'kegiatan';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Pembangunan => 'Pembangunan',
            self::Pelayanan => 'Pelayanan',
            self::Kegiatan => 'Kegiatan',
            self::Lainnya => 'Lainnya',
        };
    }
}
