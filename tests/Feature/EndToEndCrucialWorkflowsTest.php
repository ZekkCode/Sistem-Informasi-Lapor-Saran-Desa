<?php

namespace Tests\Feature;

use App\Enums\ReportStatus;
use App\Enums\StatusUsulan;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\User;
use App\Models\Usulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Concerns\WithAntiBotFields;
use Tests\TestCase;

class EndToEndCrucialWorkflowsTest extends TestCase
{
    use RefreshDatabase, WithAntiBotFields;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    public function test_complete_citizen_and_admin_workflow_for_laporan(): void
    {
        Storage::fake('public');
        config()->set('report-media.disk', 'public');
        config()->set('report-media.direct_upload.enabled', false);
        config()->set('report-media.direct_upload.required', false);

        // 1. CITIZEN: Buka form laporan & kirim laporan pengaduan
        $formRes = $this->get(route('reports.create'));
        $formRes->assertOk();

        $dusun = Dusun::query()->firstOrFail();
        $category = Category::query()->with('subcategories')->firstOrFail();
        $subcategory = $category->subcategories->firstOrFail();

        $submitRes = $this->post(route('reports.store'), $this->withAntiBotFields([
            'submission_token' => Str::uuid()->toString(),
            'reporter_name' => 'Budi Santoso',
            'reporter_phone' => '081234567890',
            'dusun_id' => $dusun->id,
            'location_text' => 'Dekat pos kamling dusun',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'title' => 'Lampu jalan padam',
            'description' => 'Lampu jalan penerangan umum padam sudah 4 hari dan membahayakan warga.',
            'citizen_priority' => 'important',
            'photos' => [UploadedFile::fake()->image('bukti.jpg', 800, 600)],
            'consent' => '1',
        ]));

        $report = Report::query()->first();
        $this->assertNotNull($report, 'Laporan warga harus berhasil dibuat');
        $submitRes->assertRedirect(route('reports.success', $report->report_code));

        // 2. CITIZEN: Cek status laporan
        $trackRes = $this->get(route('reports.track', ['code' => $report->report_code]));
        $trackRes->assertOk();
        $trackRes->assertSee($report->report_code);
        $trackRes->assertSee('Menunggu Verifikasi');
        // Identitas pelapor privat tidak boleh bocor ke publik
        $trackRes->assertDontSee('081234567890');

        // 3. ADMIN: Login petugas
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'username' => 'admindesa',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $loginRes = $this->post(route('admin.login.store'), [
            'username' => 'admindesa',
            'password' => 'password123',
        ]);
        $loginRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // 4. ADMIN: Buka Dasbor Ikhtisar dengan visual grafik
        $dashRes = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashRes->assertOk();
        $dashRes->assertSee('Ikhtisar laporan');
        $dashRes->assertSee('chart-trend');
        $dashRes->assertSee('chart-status');
        $dashRes->assertSee('chart-dusun');
        $dashRes->assertSee('Tren Aktivitas Warga (7 Hari Terakhir)');
        $dashRes->assertSee('Status Penanganan');
        $dashRes->assertSee('Sebaran Laporan per Dusun');

        // 5. ADMIN: Buka Kelola Laporan
        $listRes = $this->actingAs($admin)->get(route('admin.reports.index'));
        $listRes->assertOk();
        $listRes->assertSee($report->report_code);
        $listRes->assertSee('Tinjau');
        $listRes->assertSee('Unduh CSV');

        // 6. ADMIN: Tinjau & Verifikasi Laporan
        $showRes = $this->actingAs($admin)->get(route('admin.reports.show', $report));
        $showRes->assertOk();
        $showRes->assertSee($report->report_code);
        $showRes->assertSee('Budi Santoso');
        $showRes->assertSee('081234567890');

        // Verifikasi dan ubah status laporan ke Sedang Diproses
        $verifyRes = $this->actingAs($admin)->patch(route('admin.reports.workflow.update', $report), [
            'workflow_version' => $report->workflow_version,
            'verification_status' => 'verified',
            'status' => 'in_progress',
            'admin_priority' => 'high',
            'public_note' => 'Lokasi sudah diperiksa dan masuk jadwal penanganan.',
            'internal_note' => 'Koordinasi dengan tim lapangan.',
        ]);
        $verifyRes->assertRedirect(route('admin.reports.show', $report));
        $report->refresh();
        $this->assertSame('verified', $report->verification_status->value);
        $this->assertEquals(ReportStatus::InProgress, $report->status);

        // Selesaikan laporan
        $completeRes = $this->actingAs($admin)->patch(route('admin.reports.workflow.update', $report), [
            'workflow_version' => $report->workflow_version,
            'verification_status' => 'verified',
            'status' => 'completed',
            'admin_priority' => 'high',
            'public_note' => 'Lampu jalan sudah diganti dan berfungsi normal kembali.',
            'internal_note' => 'Pekerjaan selesai 100%.',
        ]);
        $completeRes->assertRedirect(route('admin.reports.show', $report));
        $report->refresh();
        $this->assertEquals(ReportStatus::Completed, $report->status);

        // 7. ADMIN: Ekspor CSV Laporan
        $csvRes = $this->actingAs($admin)->get(route('admin.reports.export.csv', ['periode' => 'minggu']));
        $csvRes->assertOk();
        $csvRes->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString($report->report_code, $csvRes->streamedContent());
    }

    public function test_complete_citizen_and_admin_workflow_for_usulan(): void
    {
        Storage::fake('public');

        // 1. CITIZEN: Kirim Usulan Warga
        $formRes = $this->get(route('usulan.create'));
        $formRes->assertOk();

        $dusun = Dusun::query()->firstOrFail();

        $submitRes = $this->post(route('usulan.store'), $this->withAntiBotFields([
            'token_kirim' => Str::uuid()->toString(),
            'nama_pengusul' => 'Siti Rahma',
            'telepon_pengusul' => '089876543210',
            'dusun_id' => $dusun->id,
            'jenis' => 'pembangunan',
            'judul' => 'Pengadaan Tempat Sampah Terpilah Dusun',
            'isi' => 'Mohon disediakan tempat sampah organik dan anorganik di sekitar jalan utama dusun agar lingkungan bersih.',
            'photos' => [UploadedFile::fake()->image('lokasi.jpg', 600, 400)],
            'consent' => '1',
        ]));

        $usulan = Usulan::query()->first();
        $this->assertNotNull($usulan, 'Usulan warga harus tersimpan di database');
        $submitRes->assertRedirect(route('usulan.sukses', $usulan->kode));

        // 2. CITIZEN: Lacak status usulan
        $trackRes = $this->get(route('usulan.lacak', ['kode' => $usulan->kode]));
        $trackRes->assertOk();
        $trackRes->assertSee($usulan->kode);
        $trackRes->assertSee('Baru');
        // Nomor telepon tidak boleh bocor di pelacak
        $trackRes->assertDontSee('089876543210');

        // 3. ADMIN: Login petugas
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        // 4. ADMIN: Buka tabel kelola Usulan & Saran
        $listRes = $this->actingAs($admin)->get(route('admin.usulan.index'));
        $listRes->assertOk();
        $listRes->assertSee($usulan->kode);
        $listRes->assertSee('Foto');
        $listRes->assertSee('Aksi');
        $listRes->assertSee('Tinjau');
        $listRes->assertSee('Unduh CSV');

        // 5. ADMIN: Tinjau usulan & tindaklanjuti
        $showRes = $this->actingAs($admin)->get(route('admin.usulan.show', $usulan));
        $showRes->assertOk();
        $showRes->assertSee($usulan->kode);
        $showRes->assertSee('Siti Rahma');
        $showRes->assertSee('089876543210');

        // Admin mengubah status menjadi Ditindaklanjuti dan menampilkan ke publik
        $updateRes = $this->actingAs($admin)->patch(route('admin.usulan.alur.update', $usulan), [
            'status' => StatusUsulan::Ditindaklanjuti->value,
            'catatan_publik' => 'Usulan telah dimasukkan ke dalam RKPDes tahun depan.',
            'catatan_internal' => 'Disetujui bersama kepala dusun.',
            'tampil_publik' => '1',
            'versi_alur' => $usulan->versi_alur,
        ]);
        $updateRes->assertRedirect(route('admin.usulan.show', $usulan));
        $usulan->refresh();
        $this->assertEquals(StatusUsulan::Ditindaklanjuti, $usulan->status);
        $this->assertTrue($usulan->tampil_publik);
        $this->assertEquals('Usulan telah dimasukkan ke dalam RKPDes tahun depan.', $usulan->catatan_publik);

        // 6. CITIZEN: Usulan publik sekarang tampil di tabel usulan warga tanpa identitas
        $publicRes = $this->get(route('public-usulan.index'));
        $publicRes->assertOk();
        $publicRes->assertSee($usulan->kode);
        $publicRes->assertSee('Pengadaan Tempat Sampah Terpilah Dusun');
        $publicRes->assertDontSee('Siti Rahma');
        $publicRes->assertDontSee('089876543210');

        // Detail publik usulan
        $publicDetailRes = $this->get(route('public-usulan.show', $usulan->kode));
        $publicDetailRes->assertOk();
        $publicDetailRes->assertSee('Pengadaan Tempat Sampah Terpilah Dusun');
        $publicDetailRes->assertSee('Usulan telah dimasukkan ke dalam RKPDes tahun depan.');
        $publicDetailRes->assertDontSee('Siti Rahma');

        // 7. ADMIN: Ekspor CSV Usulan
        $csvRes = $this->actingAs($admin)->get(route('admin.usulan.export.csv', ['periode' => 'bulan']));
        $csvRes->assertOk();
        $csvRes->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString($usulan->kode, $csvRes->streamedContent());

        // Cetak rekap usulan
        $printRes = $this->actingAs($admin)->get(route('admin.usulan.print'));
        $printRes->assertOk();
        $printRes->assertSee($usulan->kode);
    }
}
