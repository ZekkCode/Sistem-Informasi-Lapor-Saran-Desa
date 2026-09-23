<?php

namespace App\Services\Usulan;

use App\Models\Usulan;
use RuntimeException;

class PembuatKodeUsulan
{
    private const ALFABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function buat(): string
    {
        for ($percobaan = 0; $percobaan < 20; $percobaan++) {
            $akhiran = '';

            for ($indeks = 0; $indeks < 6; $indeks++) {
                $akhiran .= self::ALFABET[random_int(0, strlen(self::ALFABET) - 1)];
            }

            $kode = sprintf('USL-%s-%s', now()->format('Y'), $akhiran);

            if (! Usulan::query()->where('kode', $kode)->exists()) {
                return $kode;
            }
        }

        throw new RuntimeException('Tidak dapat membuat nomor usulan unik.');
    }
}
