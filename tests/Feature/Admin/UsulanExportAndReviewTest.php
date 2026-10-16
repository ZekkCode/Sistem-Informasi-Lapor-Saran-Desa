<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Models\Dusun;
use App\Models\User;
use App\Models\Usulan;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UsulanExportAndReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DusunSeeder::class, CategorySeeder::class]);
    }

    public function test_admin_usulan_can_view_usulan_table_with_photo_thumbnail_and_tinjau_action(): void
    {
        Storage::fake('public');
        $admin = User::factory()->adminUsulan()->create();
        $dusun = Dusun::query()->firstOrFail();

        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-FOT01',
            'token_kirim' => 'fake-token-1',
            'nama_pengusul' => 'Warga Padelegan',
            'telepon_pengusul' => '0881081772005',
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Pengaspalan Jalan Gang Nelayan',
            'isi' => 'Jalan berlubang dan becek saat musim hujan.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => false,
            'dikirim_pada' => now(),
        ]);

        $photoPath = 'usulan/USL-2026-FOT01/foto1.webp';
        Storage::disk('public')->put($photoPath, 'fake-image-bytes');
        $media = $usulan->media()->create([
            'storage_disk' => 'public',
            'file_path' => $photoPath,
            'mime_type' => 'image/webp',
            'file_size' => 1024,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.usulan.index'));

        $response->assertOk();
        $response->assertSee('USL-2026-FOT01');
        $response->assertSee('Pengaspalan Jalan Gang Nelayan');
        $response->assertSee('Tinjau');
        $response->assertSee(route('admin.usulan.show', $usulan));
        $response->assertSee(route('admin.usulan.media.show', $media));
        $response->assertSee('Unduh CSV');
        $response->assertSee('Ekspor ke Google Drive');
        $response->assertSee('Rentang waktu');
    }

    public function test_admin_usulan_can_filter_by_periode(): void
    {
        $admin = User::factory()->adminUsulan()->create();
        $dusun = Dusun::query()->firstOrFail();

        // Usulan baru (2 hari lalu)
        Usulan::query()->create([
            'kode' => 'USL-2026-RECENT',
            'token_kirim' => 'fake-token-recent',
            'nama_pengusul' => 'Warga Baru',
            'telepon_pengusul' => '0881081772005',
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Pelayanan,
            'judul' => 'Usulan Minggu Ini',
            'isi' => 'Mohon bantuan sarana posyandu.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => false,
            'dikirim_pada' => now()->subDays(2),
        ]);

        // Usulan lama (45 hari lalu)
        Usulan::query()->create([
            'kode' => 'USL-2026-OLD99',
            'token_kirim' => 'fake-token-old',
            'nama_pengusul' => 'Warga Lama',
            'telepon_pengusul' => '0881081772005',
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Kegiatan,
            'judul' => 'Usulan Bulan Lalu',
            'isi' => 'Pelatihan kerja pemuda desa.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => false,
            'dikirim_pada' => now()->subDays(45),
        ]);

        // Filter minggu: hanya recent yang tampil
        $responseMinggu = $this->actingAs($admin)->get(route('admin.usulan.index', ['periode' => 'minggu']));
        $responseMinggu->assertOk();
        $responseMinggu->assertSee('USL-2026-RECENT');
        $responseMinggu->assertDontSee('USL-2026-OLD99');

        // Filter semua: keduanya tampil
        $responseSemua = $this->actingAs($admin)->get(route('admin.usulan.index', ['periode' => 'semua']));
        $responseSemua->assertOk();
        $responseSemua->assertSee('USL-2026-RECENT');
        $responseSemua->assertSee('USL-2026-OLD99');
    }

    public function test_admin_usulan_can_download_csv(): void
    {
        $admin = User::factory()->adminUsulan()->create();
        $dusun = Dusun::query()->firstOrFail();

        Usulan::query()->create([
            'kode' => 'USL-2026-CSV01',
            'token_kirim' => 'fake-token-csv',
            'nama_pengusul' => 'Ahmad Dahlan',
            'telepon_pengusul' => '081234567890',
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Pengadaan Lampu Pesisir',
            'isi' => 'Penerangan perahu sandar malam hari.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => false,
            'dikirim_pada' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.usulan.export.csv', ['periode' => 'minggu']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('rekap-usulan-mingguan-7hari', $disposition);
        $this->assertStringContainsString('USL-2026-CSV01', $response->streamedContent());
    }

    public function test_admin_usulan_can_export_to_google_drive(): void
    {
        Cache::forget('google-drive:backup-access-token');
        config()->set('report-media.google_drive', [
            'enabled' => true,
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'refresh_token' => 'refresh-token',
            'folder_id' => 'folder-padelegan-123',
            'queue' => 'backups',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'api_url' => 'https://www.googleapis.com/drive/v3',
            'upload_url' => 'https://www.googleapis.com/upload/drive/v3',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'access-token-123',
                'expires_in' => 3600,
            ]),
            'https://www.googleapis.com/upload/drive/v3/files*' => Http::response([
                'id' => 'drive-csv-usulan-999',
                'name' => 'rekap-usulan-mingguan-7hari.csv',
                'webViewLink' => 'https://drive.google.com/file/d/drive-csv-usulan-999/view',
            ]),
        ]);

        $admin = User::factory()->adminUsulan()->create();
        $dusun = Dusun::query()->firstOrFail();

        Usulan::query()->create([
            'kode' => 'USL-2026-DRIVE1',
            'token_kirim' => 'fake-token-drive',
            'nama_pengusul' => 'Warga Pesisir',
            'telepon_pengusul' => '081234567890',
            'dusun_id' => $dusun->id,
            'jenis' => JenisUsulan::Kegiatan,
            'judul' => 'Kerja Bakti Pantai',
            'isi' => 'Pembersihan pantai berkala.',
            'status' => StatusUsulan::Ditinjau,
            'tampil_publik' => true,
            'dikirim_pada' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.usulan.export.drive', [
            'periode' => 'minggu',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('status');
        $this->assertStringContainsString('https://drive.google.com/file/d/drive-csv-usulan-999/view', session('status'));
    }

    public function test_admin_pengaduan_cannot_export_usulan_to_drive(): void
    {
        $adminPengaduan = User::factory()->adminPengaduan()->create();

        $response = $this->actingAs($adminPengaduan)->post(route('admin.usulan.export.drive'));

        $response->assertForbidden();
    }
}
