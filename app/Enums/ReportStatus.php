<?php

namespace App\Enums;

enum ReportStatus: string
{
    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::NotStarted => 'Belum Terlaksana',
            self::InProgress => 'Proses Pelaksanaan',
            self::Completed => 'Sudah Terlaksana',
        };
    }
}
