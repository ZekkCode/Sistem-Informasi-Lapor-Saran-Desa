<?php

namespace Tests\Feature\Admin;

use App\Enums\AdminPriority;
use App\Enums\CitizenPriority;
use App\Enums\JenisUsulan;
use App\Enums\ReportStatus;
use App\Enums\StatusUsulan;
use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\ReportMedia;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Usulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createDusunAndSubcategory(): array
    {
        $this->seed([DusunSeeder::class, CategorySeeder::class]);

        $dusun = Dusun::query()->first();
        $sub = Subcategory::query()->first();

        return [$dusun, $sub];
    }

    private function createReport(Dusun $dusun, Subcategory $sub): Report
    {
        return Report::query()->create([
            'ticket_id' => 'PL-TEST-001',
            'report_code' => 'PL-TEST-001',
            'submission_token' => (string) \Illuminate\Support\Str::uuid(),
            'dusun_id' => $dusun->id,
            'subcategory_id' => $sub->id,
            'title' => 'Laporan uji coba jalan berlubang',
            'description' => 'Deskripsi laporan uji coba',
            'location_text' => 'Dekat balai dusun',
            'citizen_priority' => CitizenPriority::Normal,
            'verification_status' => VerificationStatus::Verified,
            'status' => ReportStatus::InProgress,
            'admin_priority' => AdminPriority::Medium,
            'reporter_name' => 'Warga Test',
            'reporter_phone' => '08123456789',
            'reporter_address' => 'Jl. Mawar',
            'latitude' => null,
            'longitude' => null,
            'workflow_version' => 1,
            'submitted_at' => now(),
        ]);
    }

    private function createUsulan(Dusun $dusun): Usulan
    {
        return Usulan::factory()->create([
            'dusun_id' => $dusun->id,
            'status' => StatusUsulan::Ditinjau,
            'tampil_publik' => true,
        ]);
    }

    public function test_admin_pengaduan_can_access_reports_and_is_blocked_from_usulan(): void
    {
        [$dusun, $sub] = $this->createDusunAndSubcategory();
        $report = $this->createReport($dusun, $sub);
        $usulan = $this->createUsulan($dusun);

        $adminPengaduan = User::factory()->adminPengaduan()->create();

        // Akses laporan diizinkan
        $this->actingAs($adminPengaduan)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($adminPengaduan)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($adminPengaduan)->get(route('admin.reports.show', $report))->assertOk();
        $this->actingAs($adminPengaduan)->get(route('admin.reports.export.csv'))->assertOk();
        $this->actingAs($adminPengaduan)->get(route('admin.reports.print'))->assertOk();

        // Akses usulan diblokir (403)
        $this->actingAs($adminPengaduan)->get(route('admin.usulan.index'))->assertForbidden();
        $this->actingAs($adminPengaduan)->get(route('admin.usulan.show', $usulan))->assertForbidden();
        $this->actingAs($adminPengaduan)->patch(route('admin.usulan.alur.update', $usulan), [
            'versi_alur' => 1,
            'status' => StatusUsulan::Ditindaklanjuti->value,
        ])->assertForbidden();
    }

    public function test_admin_usulan_can_access_usulan_and_is_blocked_from_reports(): void
    {
        [$dusun, $sub] = $this->createDusunAndSubcategory();
        $report = $this->createReport($dusun, $sub);
        $usulan = $this->createUsulan($dusun);

        $adminUsulan = User::factory()->adminUsulan()->create();

        // Mengunjungi /admin diarahkan langsung ke daftar usulan
        $this->actingAs($adminUsulan)->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.usulan.index'));

        // Akses usulan diizinkan
        $this->actingAs($adminUsulan)->get(route('admin.usulan.index'))->assertOk();
        $this->actingAs($adminUsulan)->get(route('admin.usulan.show', $usulan))->assertOk();

        // Akses laporan diblokir (403)
        $this->actingAs($adminUsulan)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($adminUsulan)->get(route('admin.reports.show', $report))->assertForbidden();
        $this->actingAs($adminUsulan)->get(route('admin.reports.export.csv'))->assertForbidden();
        $this->actingAs($adminUsulan)->get(route('admin.reports.print'))->assertForbidden();
        $this->actingAs($adminUsulan)->patch(route('admin.reports.workflow.update', $report), [
            'workflow_version' => 1,
            'verification_status' => VerificationStatus::Verified->value,
            'status' => ReportStatus::Completed->value,
            'admin_priority' => AdminPriority::High->value,
        ])->assertForbidden();

        // Akses lampiran media laporan diblokir (403)
        Storage::fake('local');
        Storage::disk('local')->put('reports/test.jpg', 'fake-image');
        $media = ReportMedia::query()->create([
            'report_id' => $report->id,
            'media_type' => \App\Enums\ReportMediaType::BeforePhoto,
            'storage_disk' => 'local',
            'file_path' => 'reports/test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 100,
        ]);
        $this->actingAs($adminUsulan)->get(route('admin.media.show', $media))->assertForbidden();
    }

    public function test_kunci_formulir_authorization_per_role(): void
    {
        $adminPengaduan = User::factory()->adminPengaduan()->create();
        $adminUsulan = User::factory()->adminUsulan()->create();

        // Admin pengaduan hanya dapat mengubah kunci formulir laporan
        $this->actingAs($adminPengaduan)
            ->patch(route('admin.formulir.kunci-admin.update', 'laporan'), [
                'formulir' => 'laporan',
                'terkunci' => '1',
            ])->assertRedirect(route('admin.formulir.index'));

        $this->actingAs($adminPengaduan)
            ->patch(route('admin.formulir.kunci-admin.update', 'usulan'), [
                'formulir' => 'usulan',
                'terkunci' => '1',
            ])->assertForbidden();

        // Admin usulan hanya dapat mengubah kunci formulir usulan
        $this->actingAs($adminUsulan)
            ->patch(route('admin.formulir.kunci-admin.update', 'usulan'), [
                'formulir' => 'usulan',
                'terkunci' => '1',
            ])->assertRedirect(route('admin.formulir.index'));

        $this->actingAs($adminUsulan)
            ->patch(route('admin.formulir.kunci-admin.update', 'laporan'), [
                'formulir' => 'laporan',
                'terkunci' => '1',
            ])->assertForbidden();

        // Keduanya dilarang mengubah kunci pengembang
        $this->actingAs($adminPengaduan)
            ->patch(route('admin.formulir.kunci-pengembang.update', 'laporan'), ['terkunci' => '1'])
            ->assertForbidden();

        $this->actingAs($adminUsulan)
            ->patch(route('admin.formulir.kunci-pengembang.update', 'usulan'), ['terkunci' => '1'])
            ->assertForbidden();
    }

    public function test_admin_pengaduan_and_admin_usulan_cannot_manage_users_or_master_data(): void
    {
        $adminPengaduan = User::factory()->adminPengaduan()->create();
        $adminUsulan = User::factory()->adminUsulan()->create();

        foreach ([$adminPengaduan, $adminUsulan] as $user) {
            $this->actingAs($user)->get(route('admin.master.index'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        }
    }

    public function test_super_admin_can_access_all_modules_and_create_specific_roles(): void
    {
        [$dusun, $sub] = $this->createDusunAndSubcategory();
        $report = $this->createReport($dusun, $sub);
        $usulan = $this->createUsulan($dusun);

        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.reports.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.reports.show', $report))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.usulan.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.usulan.show', $usulan))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.master.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();

        // Super Admin dapat membuat Admin Pengaduan dan Admin Usulan baru
        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Petugas Pengaduan Baru',
            'username' => 'petugas.pengaduan',
            'email' => 'pengaduan@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => UserRole::AdminPengaduan->value,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'petugas.pengaduan',
            'role' => UserRole::AdminPengaduan->value,
        ]);

        $this->actingAs($superAdmin)->post(route('admin.users.store'), [
            'name' => 'Petugas Usulan Baru',
            'username' => 'petugas.usulan',
            'email' => 'usulan@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => UserRole::AdminUsulan->value,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'username' => 'petugas.usulan',
            'role' => UserRole::AdminUsulan->value,
        ]);
    }
}
