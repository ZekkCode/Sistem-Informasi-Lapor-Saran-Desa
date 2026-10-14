<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminPriority;
use App\Enums\CitizenPriority;
use App\Enums\JenisUsulan;
use App\Enums\ReportStatus;
use App\Enums\StatusUsulan;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\QrSource;
use App\Models\Report;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Usulan;
use App\Services\Usulan\LayananKirimUsulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAndSuperAdminFullLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    public function test_citizen_report_lifecycle_admin_and_superadmin_view_update_delete_and_audit(): void
    {
        $dusun = Dusun::query()->firstOrFail();
        $subcategory = Subcategory::query()->firstOrFail();

        // 1. Buat Laporan (Simulasi laporan masuk dari warga)
        $report = Report::query()->create([
            'ticket_id' => 'PDL-TEST-0001',
            'report_code' => 'PDL-TEST-0001',
            'submission_token' => (string) Str::uuid(),
            'dusun_id' => $dusun->id,
            'subcategory_id' => $subcategory->id,
            'title' => 'Laporan uji coba jalan amblas',
            'description' => 'Ada jalan amblas yang membahayakan pengendara motor di pertigaan.',
            'location_text' => 'Dekat jembatan dusun',
            'citizen_priority' => CitizenPriority::Important,
            'verification_status' => VerificationStatus::Pending,
            'status' => ReportStatus::NotStarted,
            'reporter_name' => 'Ahmad Warga',
            'reporter_phone' => '081298765432',
            'reporter_address' => 'Dusun Barat',
            'workflow_version' => 0,
            'submitted_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        // 2. Admin melihat index laporan (UI & Controller test)
        $indexResponse = $this->actingAs($admin)->get(route('admin.reports.index'));
        $indexResponse->assertOk()
            ->assertSee('PDL-TEST-0001')
            ->assertSee('Laporan uji coba jalan amblas')
            ->assertSee($dusun->name);

        // 3. Admin & Super Admin melihat halaman detail laporan
        $showResponse = $this->actingAs($admin)->get(route('admin.reports.show', $report));
        $showResponse->assertOk()
            ->assertSee('PDL-TEST-0001')
            ->assertSee('Ahmad Warga')
            ->assertSee('Hapus laporan')
            ->assertSee(route('admin.reports.destroy', $report));

        // 4. Admin memperbarui alur/status laporan (Workflow update)
        $updateResponse = $this->actingAs($admin)->patch(route('admin.reports.workflow.update', $report), [
            'workflow_version' => 0,
            'verification_status' => VerificationStatus::Verified->value,
            'status' => ReportStatus::InProgress->value,
            'admin_priority' => AdminPriority::High->value,
            'public_note' => 'Petugas telah meninjau lokasi dan material mulai diturunkan.',
            'internal_note' => 'Koordinasi dengan perangkat desa selesai.',
        ]);
        $updateResponse->assertRedirect();

        $report->refresh();
        $this->assertSame(VerificationStatus::Verified, $report->verification_status);
        $this->assertSame(ReportStatus::InProgress, $report->status);
        $this->assertSame(1, $report->workflow_version);

        // 5. Super Admin menghapus laporan uji coba (Destroy & Soft Delete)
        $deleteResponse = $this->actingAs($superAdmin)->delete(route('admin.reports.destroy', $report));
        $deleteResponse->assertRedirect(route('admin.reports.index'))
            ->assertSessionHas('status', "Laporan {$report->report_code} berhasil dihapus.");

        // Verifikasi database: Soft deleted
        $this->assertSoftDeleted('reports', ['id' => $report->id]);
        $this->assertNull(Report::find($report->id));
        $this->assertNotNull(Report::withTrashed()->find($report->id));

        // Verifikasi audit log pencatatan hapus laporan
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'report.deleted',
            'entity_id' => $report->id,
        ]);

        // Verifikasi di rute publik: Laporan yang dihapus tidak ditemukan (404)
        $this->get(route('public-reports.show', $report->report_code))->assertNotFound();
    }

    public function test_citizen_usulan_lifecycle_admin_and_superadmin_view_update_delete_and_audit(): void
    {
        $dusun = Dusun::query()->firstOrFail();

        // 1. Warga mengirim usulan
        $layanan = app(LayananKirimUsulan::class);
        $usulan = $layanan->kirim([
            'token_kirim' => (string) Str::uuid(),
            'nama_pengusul' => 'Siti Pengusul',
            'telepon_pengusul' => '085712345678',
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Pembangunan->value,
            'judul' => 'Perbaikan Saluran Air Pesisir',
            'isi' => 'Mohon perbaikan drainase pinggir pantai agar tidak meluap saat pasang.',
        ]);

        $this->assertNotNull($usulan->kode);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        // 2. Admin melihat index usulan (UI & Controller test)
        $indexResponse = $this->actingAs($admin)->get(route('admin.usulan.index'));
        $indexResponse->assertOk()
            ->assertSee($usulan->kode)
            ->assertSee('Perbaikan Saluran Air Pesisir')
            ->assertSee($dusun->name);

        // 3. Admin melihat detail usulan
        $showResponse = $this->actingAs($admin)->get(route('admin.usulan.show', $usulan));
        $showResponse->assertOk()
            ->assertSee($usulan->kode)
            ->assertSee('Siti Pengusul')
            ->assertSee('Hapus usulan')
            ->assertSee(route('admin.usulan.destroy', $usulan));

        // 4. Admin menindaklanjuti & mempublikasikan usulan
        $this->actingAs($admin)->patch(route('admin.usulan.alur.update', $usulan), [
            'versi_alur' => 0,
            'status' => StatusUsulan::Ditindaklanjuti->value,
            'tampil_publik' => '1',
            'catatan_publik' => 'Usulan diagendakan dalam RKPDesa tahun depan.',
            'catatan_internal' => 'Didiskusikan dengan bendahara desa.',
        ])->assertRedirect();

        $usulan->refresh();
        $this->assertSame(StatusUsulan::Ditindaklanjuti, $usulan->status);
        $this->assertTrue($usulan->tampil_publik);

        // Verifikasi tampil di halaman usulan warga publik
        $this->get(route('public-usulan.index'))
            ->assertOk()
            ->assertSee($usulan->kode)
            ->assertSee('Perbaikan Saluran Air Pesisir');

        // 5. Admin menghapus usulan uji coba (Destroy & Soft Delete)
        $deleteResponse = $this->actingAs($admin)->delete(route('admin.usulan.destroy', $usulan));
        $deleteResponse->assertRedirect(route('admin.usulan.index'))
            ->assertSessionHas('status', "Usulan {$usulan->kode} berhasil dihapus.");

        // Verifikasi database: Soft deleted
        $this->assertSoftDeleted('usulan', ['id' => $usulan->id]);
        $this->assertNull(Usulan::find($usulan->id));
        $this->assertNotNull(Usulan::withTrashed()->find($usulan->id));

        // Verifikasi audit log pencatatan hapus usulan
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'usulan.deleted',
            'entity_id' => $usulan->id,
        ]);

        // Verifikasi di halaman usulan publik: usulan yang terhapus tidak muncul lagi
        $this->get(route('public-usulan.index'))
            ->assertOk()
            ->assertDontSee($usulan->kode);
    }

    public function test_role_based_access_control_and_module_isolation(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $adminPengaduan = User::factory()->create(['role' => UserRole::AdminPengaduan]);
        $adminUsulan = User::factory()->create(['role' => UserRole::AdminUsulan]);

        // Super Admin memiliki akses ke semua modul
        $this->actingAs($superAdmin)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.usulan.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.formulir.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.master.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();

        // Admin Desa memiliki akses ke laporan, usulan, dan formulir, tetapi diblokir (403) dari data-master dan user management
        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.usulan.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.formulir.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.master.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertForbidden();

        // Admin Pengaduan hanya bisa akses modul laporan
        $this->actingAs($adminPengaduan)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($adminPengaduan)->get(route('admin.usulan.index'))->assertForbidden();
        $this->actingAs($adminPengaduan)->get(route('admin.master.index'))->assertForbidden();
        $this->actingAs($adminPengaduan)->get(route('admin.users.index'))->assertForbidden();

        // Admin Usulan hanya bisa akses modul usulan
        $this->actingAs($adminUsulan)->get(route('admin.usulan.index'))->assertOk();
        $this->actingAs($adminUsulan)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($adminUsulan)->get(route('admin.master.index'))->assertForbidden();
        $this->actingAs($adminUsulan)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_full_crud_on_master_data(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        // 1. Dusun CRUD
        $this->actingAs($superAdmin)->post(route('admin.master.dusuns.store'), [
            'code' => 'DSN-BARU',
            'name' => 'Dusun Baru Padelegan',
            'is_active' => '1',
        ])->assertRedirect();
        $dusun = Dusun::query()->where('code', 'DSN-BARU')->firstOrFail();

        $this->actingAs($superAdmin)->patch(route('admin.master.dusuns.update', $dusun), [
            'code' => 'DSN-BARU',
            'name' => 'Dusun Baru Padelegan Permai',
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame('Dusun Baru Padelegan Permai', $dusun->fresh()->name);

        // 2. Kategori CRUD
        $this->actingAs($superAdmin)->post(route('admin.master.categories.store'), [
            'name' => 'Kesehatan Desa',
            'sort_order' => 10,
            'is_active' => '1',
        ])->assertRedirect();
        $category = Category::query()->where('name', 'Kesehatan Desa')->firstOrFail();

        $this->actingAs($superAdmin)->patch(route('admin.master.categories.update', $category), [
            'name' => 'Kesehatan Masyarakat Desa',
            'sort_order' => 11,
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame('Kesehatan Masyarakat Desa', $category->fresh()->name);

        // 3. Jenis Masalah (Subcategory) CRUD
        $this->actingAs($superAdmin)->post(route('admin.master.subcategories.store'), [
            'category_id' => $category->id,
            'name' => 'Posyandu Kurang Tenaga Medis',
            'sort_order' => 1,
            'is_active' => '1',
        ])->assertRedirect();
        $sub = Subcategory::query()->where('name', 'Posyandu Kurang Tenaga Medis')->firstOrFail();

        $this->actingAs($superAdmin)->patch(route('admin.master.subcategories.update', $sub), [
            'category_id' => $category->id,
            'name' => 'Posyandu Kurang Tenaga Kesehatan',
            'sort_order' => 2,
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame('Posyandu Kurang Tenaga Kesehatan', $sub->fresh()->name);

        // 4. Sumber QR CRUD
        $this->actingAs($superAdmin)->post(route('admin.master.qr-sources.store'), [
            'code' => 'QR-BALAI-DESA',
            'name' => 'Papan Informasi Balai Desa',
            'dusun_id' => $dusun->id,
            'placement' => 'Dipasang di dekat pintu masuk kantor desa',
            'is_active' => '1',
        ])->assertRedirect();
        $qr = QrSource::query()->where('code', 'QR-BALAI-DESA')->firstOrFail();

        $this->actingAs($superAdmin)->patch(route('admin.master.qr-sources.update', $qr), [
            'code' => 'QR-BALAI-DESA',
            'name' => 'Papan Pengumuman Balai Desa',
            'dusun_id' => $dusun->id,
            'placement' => 'Pintu masuk lobi',
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertSame('Papan Pengumuman Balai Desa', $qr->fresh()->name);

        // Non-superadmin mencoba mengubah master data harus ditolak 403
        $this->actingAs($admin)->post(route('admin.master.dusuns.store'), [
            'code' => 'DSN-ILEGAL',
            'name' => 'Ilegal',
            'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_super_admin_full_crud_on_petugas(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        // 1. Super Admin melihat daftar petugas
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();

        // 2. Super Admin membuat akun petugas baru
        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Budi Santoso',
            'username' => 'budi.santoso',
            'email' => 'budi@padelegan.desa.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::AdminPengaduan->value,
            'is_active' => '1',
        ])->assertRedirect();

        $petugas = User::query()->where('username', 'budi.santoso')->firstOrFail();
        $this->assertSame(UserRole::AdminPengaduan, $petugas->role);
        $this->assertTrue($petugas->is_active);

        // 3. Super Admin mengubah data dan peran petugas
        $this->actingAs($superAdmin)->patch(route('admin.users.update', $petugas), [
            'name' => 'Budi Santoso Updated',
            'username' => 'budi.santoso',
            'email' => 'budi.updated@padelegan.desa.id',
            'role' => UserRole::AdminUsulan->value,
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect();

        $petugas->refresh();
        $this->assertSame('Budi Santoso Updated', $petugas->name);
        $this->assertSame(UserRole::AdminUsulan, $petugas->role);

        // Non-superadmin dilarang membuat atau mengubah petugas
        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Hack Admin',
            'username' => 'hack.admin',
            'email' => 'hack@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::SuperAdmin->value,
            'is_active' => '1',
        ])->assertForbidden();
    }

    public function test_ui_frontend_navigation_and_elements_render_per_role(): void
    {
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'name' => 'Super Petugas']);
        $adminPengaduan = User::factory()->create(['role' => UserRole::AdminPengaduan, 'name' => 'Petugas Laporan']);
        $adminUsulan = User::factory()->create(['role' => UserRole::AdminUsulan, 'name' => 'Petugas Usulan']);

        // Super Admin melihat semua link navigasi
        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));
        $response->assertOk()
            ->assertSee('Kelola laporan')
            ->assertSee('Usulan &amp; saran', false)
            ->assertSee('Kunci formulir')
            ->assertSee('Data master')
            ->assertSee('Akun petugas')
            ->assertSee('Super Petugas')
            ->assertSee('Super Admin');

        // Admin Pengaduan tidak melihat navigasi Usulan & saran atau Pengaturan Sistem
        $responsePengaduan = $this->actingAs($adminPengaduan)->get(route('admin.reports.index'));
        $responsePengaduan->assertOk()
            ->assertSee('Kelola laporan')
            ->assertDontSee('Usulan &amp; saran', false)
            ->assertDontSee('Data master')
            ->assertDontSee('Akun petugas');

        // Admin Usulan tidak melihat navigasi Kelola laporan atau Pengaturan Sistem
        $responseUsulan = $this->actingAs($adminUsulan)->get(route('admin.usulan.index'));
        $responseUsulan->assertOk()
            ->assertSee('Usulan &amp; saran', false)
            ->assertDontSee('Kelola laporan')
            ->assertDontSee('Data master')
            ->assertDontSee('Akun petugas');
    }
}
