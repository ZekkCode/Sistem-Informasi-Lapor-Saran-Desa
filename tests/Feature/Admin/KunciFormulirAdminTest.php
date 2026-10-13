<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KunciFormulirAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => UserRole::SuperAdmin]);
    }

    private function nilaiSetting(string $key): ?string
    {
        return Setting::query()->where('key', $key)->value('value');
    }

    public function test_tamu_tidak_dapat_membuka_atau_mengubah_kunci_formulir(): void
    {
        $this->get(route('admin.formulir.index'))->assertRedirect(route('admin.login'));

        $this->patch(route('admin.formulir.kunci-admin.update', 'laporan'), ['terkunci' => '1'])
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseCount(Setting::class, 0);
    }

    public function test_admin_dan_super_admin_dapat_membuka_halaman_kunci_formulir(): void
    {
        foreach ([$this->admin(), $this->superAdmin()] as $user) {
            $this->actingAs($user)->get(route('admin.formulir.index'))
                ->assertOk()
                ->assertSee('Kunci formulir warga')
                ->assertSee('Formulir laporan')
                ->assertSee('Formulir usulan')
                ->assertSee('Kunci formulir')
                ->assertSee(route('admin.formulir.index'))
                ->assertSee('data-status-kunci="terbuka"', false)
                ->assertSee(route('admin.formulir.kunci-admin.update', 'laporan'));
        }
    }

    public function test_admin_hanya_melihat_kunci_pengembang_sebagai_teks(): void
    {
        $this->actingAs($this->admin())->get(route('admin.formulir.index'))
            ->assertOk()
            ->assertDontSee(route('admin.formulir.kunci-pengembang.update', 'laporan'))
            ->assertDontSee(route('admin.formulir.kunci-pengembang.update', 'usulan'))
            ->assertSee('Tidak aktif. Hanya super admin yang dapat mengubah kunci ini.');

        $this->actingAs($this->superAdmin())->get(route('admin.formulir.index'))
            ->assertOk()
            ->assertSee(route('admin.formulir.kunci-pengembang.update', 'laporan'))
            ->assertSee(route('admin.formulir.kunci-pengembang.update', 'usulan'))
            ->assertSee('Simpan kunci pengembang');
    }

    public function test_admin_dapat_menutup_dan_membuka_formulir_laporan(): void
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch(route('admin.formulir.kunci-admin.update', 'laporan'), [
                'formulir' => 'laporan',
                'terkunci' => '1',
                'catatan' => 'Dibuka lagi Senin pagi.',
            ])
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHas('status', 'Formulir laporan ditutup. Warga melihat pemberitahuan saat membukanya.');

        $this->assertSame('1', $this->nilaiSetting('kunci_admin_laporan'));
        $this->assertSame('Dibuka lagi Senin pagi.', $this->nilaiSetting('catatan_kunci_laporan'));
        $this->assertSame(1, AuditLog::query()
            ->where('action', 'formulir.kunci_admin_diubah')
            ->where('user_id', $admin->id)
            ->count());

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('Formulir laporan ditutup sementara.')
            ->assertSee('Dibuka lagi Senin pagi.')
            ->assertDontSee('name="submission_token"', false);

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('name="token_kirim"', false);

        $this->get(route('admin.formulir.index'))
            ->assertOk()
            ->assertSee('data-status-kunci="admin"', false)
            ->assertSee('Ditutup admin');

        $this->patch(route('admin.formulir.kunci-admin.update', 'laporan'), [
            'formulir' => 'laporan',
            'terkunci' => '0',
            'catatan' => 'Dibuka lagi Senin pagi.',
        ])
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHas('status', 'Formulir laporan dibuka kembali.');

        $this->assertSame('0', $this->nilaiSetting('kunci_admin_laporan'));
        $this->assertNull($this->nilaiSetting('catatan_kunci_laporan'));

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('name="submission_token"', false);
    }

    public function test_kiriman_ganda_tidak_menambah_audit(): void
    {
        $this->actingAs($this->admin());
        $data = ['formulir' => 'usulan', 'terkunci' => '1', 'catatan' => 'Ditutup sampai musyawarah selesai.'];

        $this->patch(route('admin.formulir.kunci-admin.update', 'usulan'), $data)
            ->assertSessionHas('status', 'Formulir usulan ditutup. Warga melihat pemberitahuan saat membukanya.');
        $this->patch(route('admin.formulir.kunci-admin.update', 'usulan'), $data)
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHas('status', 'Kunci formulir usulan tidak berubah.');

        $this->assertSame(1, AuditLog::query()->where('action', 'formulir.kunci_admin_diubah')->count());
    }

    public function test_admin_ditolak_saat_mengubah_kunci_pengembang(): void
    {
        $this->actingAs($this->admin());

        foreach (['laporan', 'usulan'] as $formulir) {
            $this->patch(route('admin.formulir.kunci-pengembang.update', $formulir), ['terkunci' => '1'])
                ->assertForbidden()
                ->assertSee('Kunci pengembang hanya dapat diubah super admin.');

            $this->assertDatabaseMissing(Setting::class, ['key' => 'kunci_pengembang_'.$formulir]);
        }

        $this->assertSame(0, AuditLog::query()->where('action', 'formulir.kunci_pengembang_diubah')->count());
    }

    public function test_kunci_pengembang_menimpa_buka_kunci_admin(): void
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
        $admin = $this->admin();
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->patch(route('admin.formulir.kunci-pengembang.update', 'laporan'), ['terkunci' => '1'])
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHas('status', 'Kunci pengembang aktif. Formulir laporan tertutup untuk pemeliharaan.');

        $this->assertSame('1', $this->nilaiSetting('kunci_pengembang_laporan'));
        $this->assertSame(1, AuditLog::query()
            ->where('action', 'formulir.kunci_pengembang_diubah')
            ->where('user_id', $superAdmin->id)
            ->count());

        $this->actingAs($admin)
            ->patch(route('admin.formulir.kunci-admin.update', 'laporan'), ['formulir' => 'laporan', 'terkunci' => '1'])
            ->assertSessionHas('status', 'Formulir laporan ditutup. Warga melihat pemberitahuan saat membukanya.');
        $this->patch(route('admin.formulir.kunci-admin.update', 'laporan'), ['formulir' => 'laporan', 'terkunci' => '0'])
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHas('status', 'Kunci admin dimatikan. Formulir laporan tetap tertutup karena kunci pengembang aktif.');

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('Formulir laporan sedang dalam pemeliharaan.')
            ->assertDontSee('name="submission_token"', false);

        $this->get(route('admin.formulir.index'))
            ->assertOk()
            ->assertSee('data-status-kunci="pengembang"', false)
            ->assertSee('Dikunci pengembang. Hubungi super admin untuk membukanya.')
            ->assertSee('Kunci pengembang aktif. Formulir tetap tertutup walau kunci admin dimatikan.');

        $this->actingAs($superAdmin)
            ->patch(route('admin.formulir.kunci-pengembang.update', 'laporan'), ['terkunci' => '0'])
            ->assertSessionHas('status', 'Kunci pengembang dimatikan. Formulir laporan dibuka kembali.');

        $this->get(route('reports.create'))
            ->assertOk()
            ->assertSee('name="submission_token"', false);
    }

    public function test_super_admin_dapat_mengubah_kedua_kunci(): void
    {
        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin);

        $this->patch(route('admin.formulir.kunci-admin.update', 'usulan'), ['formulir' => 'usulan', 'terkunci' => '1'])
            ->assertSessionHas('status', 'Formulir usulan ditutup. Warga melihat pemberitahuan saat membukanya.');
        $this->patch(route('admin.formulir.kunci-pengembang.update', 'usulan'), ['terkunci' => '1'])
            ->assertSessionHas('status', 'Kunci pengembang aktif. Formulir usulan tertutup untuk pemeliharaan.');
        $this->patch(route('admin.formulir.kunci-pengembang.update', 'usulan'), ['terkunci' => '0'])
            ->assertSessionHas('status', 'Kunci pengembang dimatikan. Formulir usulan masih ditutup admin.');

        $this->assertSame('1', $this->nilaiSetting('kunci_admin_usulan'));
        $this->assertSame('0', $this->nilaiSetting('kunci_pengembang_usulan'));
        $this->assertSame(3, AuditLog::query()->where('user_id', $superAdmin->id)->count());
    }

    public function test_catatan_lebih_dari_200_karakter_ditolak(): void
    {
        $this->actingAs($this->admin());

        $this->from(route('admin.formulir.index'))
            ->patch(route('admin.formulir.kunci-admin.update', 'laporan'), [
                'formulir' => 'laporan',
                'terkunci' => '1',
                'catatan' => str_repeat('a', 201),
            ])
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHasErrors('catatan');

        $this->assertDatabaseMissing(Setting::class, ['key' => 'kunci_admin_laporan']);
        $this->assertSame(0, AuditLog::query()->count());

        $this->get(route('admin.formulir.index'))
            ->assertOk()
            ->assertSee('Catatan untuk warga maksimal 200 karakter. Persingkat lalu simpan kembali.')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_tanpa_status_kunci_ditolak(): void
    {
        $this->actingAs($this->admin())
            ->from(route('admin.formulir.index'))
            ->patch(route('admin.formulir.kunci-admin.update', 'laporan'), ['catatan' => 'Tanpa status'])
            ->assertRedirect(route('admin.formulir.index'))
            ->assertSessionHasErrors(['terkunci' => 'Pilih status kunci sebelum menyimpan.']);

        $this->assertDatabaseCount(Setting::class, 0);
    }

    public function test_formulir_tidak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->superAdmin());

        $this->patch('/admin/formulir/lainnya/kunci-admin', ['terkunci' => '1'])->assertNotFound();
        $this->patch('/admin/formulir/lainnya/kunci-pengembang', ['terkunci' => '1'])->assertNotFound();
    }
}
