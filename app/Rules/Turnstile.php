<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Validasi token Cloudflare Turnstile.
 *
 * Mengirim token `cf-turnstile-response` ke API Cloudflare untuk memastikan
 * pengiriman berasal dari warga, bukan program bot.
 */
class Turnstile implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secretKey = (string) config('services.turnstile.secret_key');

        if ($secretKey === '') {
            return;
        }

        if (! is_string($value) || trim($value) === '') {
            $fail('Selesaikan pemeriksaan keamanan sebelum mengirim formulir.');

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->successful() || ! (bool) $response->json('success')) {
                $fail('Pemeriksaan keamanan gagal diverifikasi. Centang kembali kotak pemeriksaan lalu coba lagi.');
            }
        } catch (Throwable $e) {
            Log::warning('Gagal menghubungi layanan verifikasi Cloudflare Turnstile.', [
                'error' => $e->getMessage(),
            ]);

            $fail('Pemeriksaan keamanan tidak dapat tersambung. Periksa koneksi internet lalu coba lagi.');
        }
    }
}
