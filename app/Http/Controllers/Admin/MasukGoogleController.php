<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AuditService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class MasukGoogleController extends Controller
{
    /**
     * Judul dan pesan popup untuk setiap alasan penolakan. Placeholder :email
     * diganti dengan email Google yang dipakai agar petugas tahu akun mana yang salah.
     *
     * @var array<string, array{judul: string, pesan: string}>
     */
    private const PESAN = [
        'tidak_dikonfigurasi' => [
            'judul' => 'Masuk dengan Google belum aktif',
            'pesan' => 'Pengelola situs belum menyambungkan akun Google admin desa. Masuk memakai username dan password.',
        ],
        'dibatalkan' => [
            'judul' => 'Masuk dengan Google tidak selesai',
            'pesan' => 'Proses masuk dibatalkan atau sesinya berakhir. Tekan Masuk dengan Google untuk mencoba lagi.',
        ],
        'belum_terverifikasi' => [
            'judul' => 'Email Google belum terverifikasi',
            'pesan' => 'Verifikasi email pada akun Google Anda, lalu coba masuk lagi.',
        ],
        'bukan_admin' => [
            'judul' => 'Akun ini bukan akun admin desa',
            'pesan' => 'Email :email tidak terdaftar sebagai admin desa. Pilih akun Google resmi desa, atau masuk memakai username dan password.',
        ],
        'nonaktif' => [
            'judul' => 'Akun petugas nonaktif',
            'pesan' => 'Akun untuk :email sedang dinonaktifkan. Hubungi Super Admin untuk mengaktifkannya kembali.',
        ],
        'database' => [
            'judul' => 'Login petugas belum aktif',
            'pesan' => 'Database belum tersambung sehingga akun belum bisa diperiksa. Hubungi pengelola situs.',
        ],
    ];

    public function arahkan(): SymfonyRedirectResponse
    {
        if (! self::aktif()) {
            return $this->tolak('tidak_dikonfigurasi');
        }

        // Paksa pemilih akun agar petugas bisa memilih akun resmi desa,
        // bukan otomatis memakai akun pribadi yang sedang aktif di browser.
        return Socialite::driver('google')
            ->redirectUrl($this->alamatKembali())
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function kembali(Request $request, AuditService $audit): RedirectResponse
    {
        if (! self::aktif()) {
            return $this->tolak('tidak_dikonfigurasi');
        }

        if ($request->filled('error')) {
            return $this->tolak('dibatalkan');
        }

        try {
            $akunGoogle = Socialite::driver('google')->redirectUrl($this->alamatKembali())->user();
        } catch (Throwable $exception) {
            Log::notice('Masuk dengan Google gagal diproses.', ['exception' => $exception]);

            return $this->tolak('dibatalkan');
        }

        $email = strtolower(trim((string) $akunGoogle->getEmail()));
        $terverifikasi = filter_var(data_get($akunGoogle->getRaw(), 'email_verified', false), FILTER_VALIDATE_BOOL);

        if ($email === '' || ! $terverifikasi) {
            return $this->tolak('belum_terverifikasi', $email, $audit);
        }

        if (! in_array($email, config('padelegan.google_sso.allowed_emails', []), true)) {
            return $this->tolak('bukan_admin', $email, $audit);
        }

        try {
            $petugas = User::query()->whereRaw('lower(email) = ?', [$email])->first()
                ?? $this->buatAkunPetugas($email, $akunGoogle->getName(), $audit);
        } catch (QueryException) {
            return $this->tolak('database', $email);
        }

        // Akun yang dinonaktifkan Super Admin tetap tertutup walau emailnya ada di daftar.
        if (! $petugas->is_active) {
            return $this->tolak('nonaktif', $email, $audit);
        }

        Auth::login($petugas);
        $request->session()->regenerate();
        $petugas->forceFill(['last_login_at' => now()])->save();
        $audit->record('auth.login', $petugas, ['metode' => 'google'], $request);

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * SSO hanya aktif bila kredensial OAuth lengkap dan daftar email admin terisi.
     * Daftar kosong menutup jalur Google sepenuhnya.
     */
    public static function aktif(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && config('padelegan.google_sso.allowed_emails', []) !== [];
    }

    /**
     * Buat akun petugas untuk email resmi yang pertama kali masuk lewat Google.
     * Peran mengikuti konfigurasi (bawaan Admin Desa). Password diisi acak agar
     * akun ini hanya bisa dibuka lewat Google sampai Super Admin mengaturnya.
     */
    private function buatAkunPetugas(string $email, ?string $nama, AuditService $audit): User
    {
        $peran = UserRole::tryFrom((string) config('padelegan.google_sso.role')) ?? UserRole::Admin;

        $petugas = User::query()->createOrFirst(
            ['email' => $email],
            [
                'name' => filled($nama) ? Str::limit($nama, 120, '') : 'Admin Desa',
                'username' => $this->usernameUnik($email),
                'password' => Str::random(48),
                'role' => $peran,
                'is_active' => true,
            ],
        );

        if ($petugas->wasRecentlyCreated) {
            $audit->record('auth.akun_google_dibuat', $petugas, ['peran' => $peran->value]);
        }

        return $petugas;
    }

    private function usernameUnik(string $email): string
    {
        $dasar = Str::of(Str::before($email, '@'))
            ->lower()
            ->replaceMatches('/[^a-z0-9._-]/', '')
            ->limit(60, '')
            ->toString() ?: 'admin';

        $username = $dasar;
        $urutan = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $dasar.'.'.(++$urutan);
        }

        return $username;
    }

    private function alamatKembali(): string
    {
        return config('services.google.redirect') ?: route('admin.login.google.callback');
    }

    private function tolak(string $alasan, ?string $email = null, ?AuditService $audit = null): RedirectResponse
    {
        if ($audit) {
            try {
                $audit->record('auth.google_ditolak', null, ['alasan' => $alasan, 'email' => $email]);
            } catch (QueryException) {
                // Audit gagal dicatat saat database bermasalah. Penolakan tetap berlaku.
            }
        }

        $pesan = self::PESAN[$alasan];

        return redirect()->route('admin.login')->with('sso_error', [
            'judul' => $pesan['judul'],
            'pesan' => str_replace(':email', $email ?: 'ini', $pesan['pesan']),
        ]);
    }
}
