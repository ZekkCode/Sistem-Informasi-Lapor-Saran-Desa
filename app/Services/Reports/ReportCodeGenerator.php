<?php

namespace App\Services\Reports;

use App\Models\Report;
use RuntimeException;

class ReportCodeGenerator
{
    private const ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function generate(): string
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $suffix = '';

            for ($index = 0; $index < 6; $index++) {
                $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }

            $code = sprintf('PDL-%s-%s', now()->format('Y'), $suffix);

            if (! Report::query()->where('report_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Tidak dapat membuat nomor laporan unik.');
    }
}
