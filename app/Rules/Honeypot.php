<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Honeypot: field yang harus kosong.
 *
 * Bot pengisi-otomatis cenderung mengisi semua field, termasuk field
 * tersembunyi. Jika field ini terisi, pengiriman ditolak secara diam-diam
 * tanpa pesan error detail agar bot tidak belajar.
 */
class Honeypot implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filled($value)) {
            $fail('Pengiriman tidak dapat diproses.');
        }
    }
}
