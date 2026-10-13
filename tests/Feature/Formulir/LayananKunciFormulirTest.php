<?php

namespace Tests\Feature\Formulir;

use App\Enums\JenisFormulir;
use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\Formulir\LayananKunciFormulir;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LayananKunciFormulirTest extends TestCase
{
    use RefreshDatabase;

    /** @param  array<string, string|null>  $nilai */
    private function simpanSettings(array $nilai): void
    {
        foreach ($nilai as $key => $value) {
            Setting::query()->create(['key' => $key, 'value' => $value]);
        }
    }

    private function layanan(): LayananKunciFormulir
    {
        return app(LayananKunciFormulir::class);
    }

    public function test_tanpa_baris_settings_kedua_formulir_terbuka(): void
    {
        foreach (JenisFormulir::cases() as $formulir) {
            $status = $this->layanan()->status($formulir);

            $this->assertTrue($status->terbuka());
            $this->assertSame('terbuka', $status->kode());
            $this->assertSame('Dibuka', $status->label());
        }
    }

    public function test_kunci_admin_hanya_menutup_formulirnya_dan_menampilkan_catatan(): void
    {
        $this->simpanSettings([
            'kunci_admin_laporan' => '1',
            'catatan_kunci_laporan' => '  Dibuka lagi Senin pagi.  ',
        ]);

        $laporan = $this->layanan()->status(JenisFormulir::Laporan);

        $this->assertFalse($laporan->terbuka());
        $this->assertSame('admin', $laporan->kode());
        $this->assertSame('Ditutup admin', $laporan->label());
        $this->assertSame('Dibuka lagi Senin pagi.', $laporan->catatanPublik());
        $this->assertStringContainsString('Laporan Anda belum terkirim.', $laporan->pesanPublik());
        $this->assertStringContainsString('Catatan petugas: Dibuka lagi Senin pagi.', $laporan->pesanPublik());

        $this->assertTrue($this->layanan()->status(JenisFormulir::Usulan)->terbuka());
    }

    public function test_kunci_pengembang_menimpa_kunci_admin_dan_menyembunyikan_catatan(): void
    {
        $this->simpanSettings([
            'kunci_admin_usulan' => '1',
            'catatan_kunci_usulan' => 'Catatan admin',
            'kunci_pengembang_usulan' => '1',
        ]);

        $status = $this->layanan()->status(JenisFormulir::Usulan);

        $this->assertSame('pengembang', $status->kode());
        $this->assertSame('Dikunci pengembang', $status->label());
        $this->assertNull($status->catatanPublik());
        $this->assertStringContainsString('Formulir usulan sedang dalam pemeliharaan sistem. Usulan Anda belum terkirim.', $status->pesanPublik());
        $this->assertStringNotContainsString('Catatan admin', $status->pesanPublik());
    }

    public function test_nilai_selain_satu_dan_report_form_open_tidak_mengunci(): void
    {
        $this->simpanSettings([
            'kunci_admin_laporan' => '0',
            'kunci_pengembang_laporan' => 'true',
            'kunci_admin_usulan' => '',
            'kunci_pengembang_usulan' => null,
            'report_form_open' => '0',
        ]);

        $this->assertTrue($this->layanan()->status(JenisFormulir::Laporan)->terbuka());
        $this->assertTrue($this->layanan()->status(JenisFormulir::Usulan)->terbuka());
    }

    public function test_tabel_settings_tidak_terjangkau_dianggap_terbuka(): void
    {
        Log::spy();
        Schema::drop('settings');

        $this->assertTrue($this->layanan()->status(JenisFormulir::Laporan)->terbuka());
        $this->assertTrue($this->layanan()->status(JenisFormulir::Usulan)->terbuka());

        // Hasil gagal ikut di-memoize sehingga query tidak diulang tiap pemanggilan.
        Log::shouldHaveReceived('notice')->once();
    }

    public function test_atur_kunci_admin_menulis_nilai_dan_audit_sekali(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->assertTrue($this->layanan()->status(JenisFormulir::Laporan)->terbuka());

        $berubah = $this->layanan()->aturKunciAdmin(JenisFormulir::Laporan, true, 'Dibuka Senin', $admin);

        $this->assertTrue($berubah);
        $this->assertSame('1', Setting::query()->where('key', 'kunci_admin_laporan')->value('value'));
        $this->assertSame('Dibuka Senin', Setting::query()->where('key', 'catatan_kunci_laporan')->value('value'));
        $this->assertNotNull(Setting::query()->where('key', 'kunci_admin_laporan')->value('created_at'));

        // Memo dikosongkan setelah tulis, jadi status membaca nilai baru.
        $this->assertSame('admin', $this->layanan()->status(JenisFormulir::Laporan)->kode());

        $audit = AuditLog::query()->sole();
        $this->assertSame('formulir.kunci_admin_diubah', $audit->action);
        $this->assertSame($admin->id, $audit->user_id);
        $this->assertSame([
            'formulir' => 'laporan',
            'peran' => 'admin',
            'sebelum' => ['terkunci' => false, 'catatan' => null],
            'sesudah' => ['terkunci' => true, 'catatan' => 'Dibuka Senin'],
        ], $audit->meta);

        $this->assertFalse($this->layanan()->aturKunciAdmin(JenisFormulir::Laporan, true, 'Dibuka Senin', $admin));
        $this->assertSame(1, AuditLog::query()->count());
    }

    public function test_membuka_kunci_admin_mengosongkan_catatan(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $this->layanan()->aturKunciAdmin(JenisFormulir::Usulan, true, 'Ditutup sementara', $admin);
        $this->assertTrue($this->layanan()->aturKunciAdmin(JenisFormulir::Usulan, false, 'sisa', $admin));

        $this->assertSame('0', Setting::query()->where('key', 'kunci_admin_usulan')->value('value'));
        $this->assertNull(Setting::query()->where('key', 'catatan_kunci_usulan')->value('value'));
        $this->assertTrue($this->layanan()->status(JenisFormulir::Usulan)->terbuka());
    }

    public function test_atur_kunci_pengembang_menulis_nilai_dan_audit(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($superAdmin);

        $this->assertTrue($this->layanan()->aturKunciPengembang(JenisFormulir::Laporan, true, $superAdmin));

        $this->assertSame('1', Setting::query()->where('key', 'kunci_pengembang_laporan')->value('value'));
        $this->assertSame('pengembang', $this->layanan()->status(JenisFormulir::Laporan)->kode());

        $audit = AuditLog::query()->sole();
        $this->assertSame('formulir.kunci_pengembang_diubah', $audit->action);
        $this->assertSame($superAdmin->id, $audit->user_id);
        $this->assertSame([
            'formulir' => 'laporan',
            'peran' => 'super_admin',
            'sebelum' => ['terkunci' => false],
            'sesudah' => ['terkunci' => true],
        ], $audit->meta);
    }
}
