<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as AkunGoogle;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Tests\TestCase;

class MasukGoogleTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL_ADMIN = 'laporpadelegan@gmail.com';

    private function aktifkanSso(array $daftarEmail = [self::EMAIL_ADMIN]): void
    {
        config([
            'services.google.client_id' => 'id-uji',
            'services.google.client_secret' => 'rahasia-uji',
            'services.google.redirect' => null,
            'padelegan.google_sso.allowed_emails' => $daftarEmail,
        ]);
    }

    private function balasanGoogle(string $email, bool $terverifikasi = true): void
    {
        $akun = (new AkunGoogle)
            ->setRaw(['email' => $email, 'email_verified' => $terverifikasi])
            ->map(['id' => 'google-1', 'email' => $email, 'name' => 'Akun Uji']);

        Socialite::shouldReceive('driver->redirectUrl->user')->andReturn($akun);
    }

    public function test_tombol_google_tersembunyi_saat_belum_dikonfigurasi(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertDontSee('Masuk dengan Google');
    }

    public function test_tombol_google_tampil_saat_dikonfigurasi(): void
    {
        $this->aktifkanSso();

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Masuk dengan Google')
            ->assertSee(route('admin.login.google'));
    }

    public function test_daftar_email_kosong_menutup_jalur_google(): void
    {
        $this->aktifkanSso([]);

        $this->get(route('admin.login.google'))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('sso_error');
        $this->assertSame('Masuk dengan Google belum aktif', session('sso_error')['judul']);
    }

    public function test_tombol_mengarahkan_ke_google_saat_aktif(): void
    {
        $this->aktifkanSso();
        Socialite::shouldReceive('driver->redirectUrl->with->redirect')
            ->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/auth'));

        $this->get(route('admin.login.google'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_email_admin_desa_berhasil_masuk(): void
    {
        $this->aktifkanSso();
        // Email di akun petugas boleh berbeda huruf besar-kecil.
        $petugas = User::factory()->create(['email' => 'LaporPadelegan@gmail.com']);
        $this->balasanGoogle(self::EMAIL_ADMIN);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji', 'state' => 'state-uji']))
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($petugas);
        $this->assertNotNull($petugas->fresh()->last_login_at);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $petugas->id, 'action' => 'auth.login']);
    }

    public function test_email_di_luar_daftar_ditolak_sebagai_bukan_admin(): void
    {
        $this->aktifkanSso();
        // Akun petugas ada, tetapi emailnya tidak masuk daftar izin SSO.
        User::factory()->create(['email' => 'pribadi@gmail.com']);
        $this->balasanGoogle('pribadi@gmail.com');

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))
            ->assertRedirect(route('admin.login'))
            ->assertSessionHas('sso_error');

        $this->assertGuest();
        $this->assertSame('Akun ini bukan akun admin desa', session('sso_error')['judul']);
        $this->assertStringContainsString('pribadi@gmail.com', session('sso_error')['pesan']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.google_ditolak']);
    }

    public function test_email_resmi_tanpa_akun_dibuat_otomatis_sebagai_admin(): void
    {
        $this->aktifkanSso();
        $this->balasanGoogle(self::EMAIL_ADMIN);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))
            ->assertRedirect(route('admin.dashboard'));

        $petugas = User::query()->where('email', self::EMAIL_ADMIN)->sole();
        $this->assertAuthenticatedAs($petugas);
        $this->assertSame(UserRole::Admin, $petugas->role);
        $this->assertTrue($petugas->is_active);
        $this->assertSame('laporpadelegan', $petugas->username);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.akun_google_dibuat', 'entity_id' => $petugas->id]);
    }

    public function test_masuk_berulang_tidak_membuat_akun_ganda(): void
    {
        $this->aktifkanSso();
        $this->balasanGoogle(self::EMAIL_ADMIN);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))->assertRedirect(route('admin.dashboard'));
        $this->post(route('admin.logout'));
        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))->assertRedirect(route('admin.dashboard'));

        $this->assertSame(1, User::query()->where('email', self::EMAIL_ADMIN)->count());
    }

    public function test_username_bentrok_diberi_akhiran(): void
    {
        $this->aktifkanSso();
        User::factory()->create(['username' => 'laporpadelegan', 'email' => 'lain@contoh.id']);
        $this->balasanGoogle(self::EMAIL_ADMIN);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))->assertRedirect(route('admin.dashboard'));

        $this->assertSame('laporpadelegan.2', User::query()->where('email', self::EMAIL_ADMIN)->value('username'));
    }

    public function test_akun_yang_sudah_ada_mempertahankan_perannya(): void
    {
        $this->aktifkanSso();
        $superAdmin = User::factory()->create(['email' => self::EMAIL_ADMIN, 'role' => UserRole::SuperAdmin]);
        $this->balasanGoogle(self::EMAIL_ADMIN);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($superAdmin);
        $this->assertSame(UserRole::SuperAdmin, $superAdmin->fresh()->role);
    }

    public function test_email_resmi_desa_terdaftar_secara_bawaan(): void
    {
        $this->assertContains(self::EMAIL_ADMIN, config('padelegan.google_sso.allowed_emails'));
        $this->assertSame('admin', config('padelegan.google_sso.role'));
    }

    public function test_akun_petugas_nonaktif_ditolak(): void
    {
        $this->aktifkanSso();
        User::factory()->create(['email' => self::EMAIL_ADMIN, 'is_active' => false]);
        $this->balasanGoogle(self::EMAIL_ADMIN);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->assertSame('Akun petugas nonaktif', session('sso_error')['judul']);
    }

    public function test_email_google_belum_terverifikasi_ditolak(): void
    {
        $this->aktifkanSso();
        User::factory()->create(['email' => self::EMAIL_ADMIN]);
        $this->balasanGoogle(self::EMAIL_ADMIN, terverifikasi: false);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->assertSame('Email Google belum terverifikasi', session('sso_error')['judul']);
    }

    public function test_login_dibatalkan_di_google_ditolak(): void
    {
        $this->aktifkanSso();

        $this->get(route('admin.login.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->assertSame('Masuk dengan Google tidak selesai', session('sso_error')['judul']);
    }

    public function test_state_tidak_valid_ditolak(): void
    {
        $this->aktifkanSso();
        Socialite::shouldReceive('driver->redirectUrl->user')->andThrow(new InvalidStateException);

        $this->get(route('admin.login.google.callback', ['code' => 'kode-uji']))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
        $this->assertSame('Masuk dengan Google tidak selesai', session('sso_error')['judul']);
    }

    public function test_halaman_login_menampilkan_popup_penolakan(): void
    {
        $this->withSession(['sso_error' => [
            'judul' => 'Akun ini bukan akun admin desa',
            'pesan' => 'Email pribadi@gmail.com tidak terdaftar sebagai admin desa.',
        ]])->get(route('admin.login'))
            ->assertOk()
            ->assertSee('data-dialog-otomatis', false)
            ->assertSee('Akun ini bukan akun admin desa')
            ->assertSee('pribadi@gmail.com');
    }

    public function test_login_password_tetap_berjalan(): void
    {
        $this->aktifkanSso();
        $petugas = User::factory()->create(['username' => 'petugas.uji', 'password' => 'rahasia123']);

        $this->post(route('admin.login.store'), ['username' => 'petugas.uji', 'password' => 'rahasia123'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($petugas);
    }
}
