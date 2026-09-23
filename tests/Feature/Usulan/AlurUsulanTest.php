<?php

namespace Tests\Feature\Usulan;

use App\Enums\StatusUsulan;
use App\Enums\UserRole;
use App\Models\User;
use App\Models\Usulan;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurUsulanTest extends TestCase
{
    use RefreshDatabase;

    public function test_petugas_dapat_memperbarui_status_dan_menampilkan_ke_publik(): void
    {
        $this->seed(DusunSeeder::class);
        $petugas = User::factory()->create();
        $usulan = Usulan::factory()->create();

        $this->actingAs($petugas)->patch(route('admin.usulan.alur.update', $usulan), [
            'versi_alur' => 0,
            'status' => 'ditindaklanjuti',
            'tampil_publik' => '1',
            'catatan_publik' => 'Usulan masuk pembahasan musyawarah dusun.',
            'catatan_internal' => 'Koordinasi dengan kepala dusun.',
        ])->assertRedirect();

        $usulan->refresh();
        $this->assertSame(StatusUsulan::Ditindaklanjuti, $usulan->status);
        $this->assertTrue($usulan->tampil_publik);
        $this->assertSame(1, $usulan->versi_alur);
        $this->assertSame($petugas->id, $usulan->ditangani_oleh);
        $this->assertNotNull($usulan->dipublikasikan_pada);
        $this->assertDatabaseHas('riwayat_usulan', ['usulan_id' => $usulan->id, 'diubah_oleh' => $petugas->id]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $petugas->id, 'action' => 'usulan.alur_diperbarui']);
    }

    public function test_versi_basi_tidak_menimpa_perubahan_petugas_lain(): void
    {
        $this->seed(DusunSeeder::class);
        $petugasA = User::factory()->create();
        $petugasB = User::factory()->create();
        $usulan = Usulan::factory()->create();

        $payload = [
            'versi_alur' => 0,
            'status' => 'ditinjau',
            'catatan_publik' => 'Sedang ditinjau.',
        ];

        $this->actingAs($petugasA)->patch(route('admin.usulan.alur.update', $usulan), $payload)->assertRedirect();
        $this->actingAs($petugasB)->from(route('admin.usulan.show', $usulan))
            ->patch(route('admin.usulan.alur.update', $usulan), [...$payload, 'status' => 'selesai'])
            ->assertRedirect(route('admin.usulan.show', $usulan))
            ->assertSessionHasErrors('versi_alur');

        $this->assertSame(StatusUsulan::Ditinjau, $usulan->fresh()->status);
        $this->assertDatabaseCount('riwayat_usulan', 1);
    }

    public function test_menampilkan_ke_publik_wajib_disertai_catatan(): void
    {
        $this->seed(DusunSeeder::class);
        $petugas = User::factory()->create();
        $usulan = Usulan::factory()->create();

        $this->actingAs($petugas)->from(route('admin.usulan.show', $usulan))
            ->patch(route('admin.usulan.alur.update', $usulan), [
                'versi_alur' => 0,
                'status' => 'ditindaklanjuti',
                'tampil_publik' => '1',
                'catatan_publik' => '',
            ])
            ->assertRedirect(route('admin.usulan.show', $usulan))
            ->assertSessionHasErrors('catatan_publik');

        $this->assertFalse($usulan->fresh()->tampil_publik);
    }

    public function test_admin_desa_dan_super_admin_bisa_membuka_daftar_usulan(): void
    {
        $this->seed(DusunSeeder::class);
        $adminDesa = User::factory()->create(['role' => UserRole::Admin]);
        $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($adminDesa)->get(route('admin.usulan.index'))->assertOk();
        $this->actingAs($superAdmin)->get(route('admin.usulan.index'))->assertOk();
    }
}
