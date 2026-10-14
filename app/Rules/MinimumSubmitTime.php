<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Menolak pengiriman yang terlalu cepat (di bawah batas waktu minimum).
 *
 * Form mengisi field `_form_rendered_at` secara otomatis saat halaman dimuat.
 * Jika selisih waktu antara render dan submit kurang dari threshold,
 * kemungkinan besar pengiriman berasal dari bot.
 */
class MinimumSubmitTime implements ValidationRule
{
    public function __construct(
        private readonly int $minimumSeconds = 5,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail('Pengiriman tidak dapat diproses. Muat ulang halaman lalu coba kembali.');

            return;
        }

        $renderedAt = (int) $value;
        $elapsedSeconds = time() - $renderedAt;

        if ($elapsedSeconds < $this->minimumSeconds) {
            $fail('Formulir dikirim terlalu cepat. Pastikan data sudah diisi dengan benar, lalu kirim ulang.');
        }
    }
}
