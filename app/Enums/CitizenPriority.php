<?php

namespace App\Enums;

enum CitizenPriority: string
{
    case Normal = 'normal';
    case Important = 'important';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Biasa',
            self::Important => 'Penting',
            self::Emergency => 'Darurat',
        };
    }
}
