<?php

namespace Tests\Feature\Site;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Enums\UserRole;
use App\Models\Dusun;
use App\Models\User;
use App\Models\Usulan;
use App\Models\UsulanMedia;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicUsulanMediaTest extends TestCase
{
    use RefreshDatabase;

    private Dusun $dusun;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('report-media.disk', 'public');
        $this->seed(DusunSeeder::class);
        $this->dusun = Dusun::query()->firstOrFail();
    }

    public function test_public_user_can_view_usulan_media_stream(): void
    {
        Storage::fake('public');
        $filePath = 'usulan/USL-2026-MED001/photos/test.webp';
        Storage::disk('public')->put($filePath, 'fake-usulan-image');

        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-MED001',
            'token_kirim' => 'token-test-1',
            'nama_pengusul' => 'Warga Padelegan',
            'telepon_pengusul' => '081234567890',
            'dusun_id' => $this->dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Pengaspalan Jalan Dusun',
            'isi' => 'Usulan pengaspalan jalan dusun yang berlubang agar aman dilalui.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => true,
            'catatan_publik' => 'Usulan diterima desa.',
            'dikirim_pada' => now(),
        ]);

        $media = UsulanMedia::query()->create([
            'usulan_id' => $usulan->id,
            'storage_disk' => 'public',
            'file_path' => $filePath,
            'mime_type' => 'image/webp',
            'file_size' => 1024,
            'caption' => 'Foto jalan berlubang',
            'uploaded_by' => null,
        ]);

        $response = $this->get(route('public-usulan.media', $media));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/webp');
    }

    public function test_public_usulan_detail_displays_warga_photo_and_desa_realization(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('usulan/USL-2026-MED002/photos/awal.webp', 'image-warga');
        Storage::disk('public')->put('usulan/USL-2026-MED002/bukti/hasil.webp', 'image-desa');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-MED002',
            'token_kirim' => 'token-test-2',
            'nama_pengusul' => 'Warga Pengusul',
            'dusun_id' => $this->dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Pemasangan Lampu Jalan',
            'isi' => 'Usulan lampu penerangan di perempatan jalan dusun.',
            'status' => StatusUsulan::Selesai,
            'tampil_publik' => true,
            'catatan_publik' => 'Pemasangan lampu telah selesai dikerjakan.',
            'dikirim_pada' => now(),
            'dipublikasikan_pada' => now(),
        ]);

        $wargaMedia = UsulanMedia::query()->create([
            'usulan_id' => $usulan->id,
            'storage_disk' => 'public',
            'file_path' => 'usulan/USL-2026-MED002/photos/awal.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
            'caption' => 'Kondisi gelap malam hari',
            'uploaded_by' => null,
        ]);

        $desaMedia = UsulanMedia::query()->create([
            'usulan_id' => $usulan->id,
            'storage_disk' => 'public',
            'file_path' => 'usulan/USL-2026-MED002/bukti/hasil.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
            'caption' => 'Bukti pemasangan lampu jalan',
            'uploaded_by' => $admin->id,
        ]);

        $response = $this->get(route('public-usulan.show', $usulan->kode));

        $response->assertOk();
        $response->assertSee('Dokumentasi dan Bukti Realisasi');
        $response->assertSee('Foto Usulan');
        $response->assertSee('Hasil Tindak Lanjut');
        $response->assertSee('Sudah Dilampirkan Bukti Realisasi');
        $response->assertSee(route('public-usulan.media', $wargaMedia));
        $response->assertSee(route('public-usulan.media', $desaMedia));
    }

    public function test_usulan_tracking_page_displays_evidence(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('usulan/USL-2026-MED003/photos/warga.webp', 'image-warga');

        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-MED003',
            'token_kirim' => 'token-test-3',
            'nama_pengusul' => 'Warga Lacak',
            'dusun_id' => $this->dusun->id,
            'jenis' => JenisUsulan::Kegiatan,
            'judul' => 'Pengadaan Tenda Pertemuan',
            'isi' => 'Usulan pengadaan tenda kegiatan warga dusun.',
            'status' => StatusUsulan::Ditindaklanjuti,
            'tampil_publik' => true,
            'catatan_publik' => 'Tenda sedang dalam pengadaan.',
            'dikirim_pada' => now(),
        ]);

        $wargaMedia = UsulanMedia::query()->create([
            'usulan_id' => $usulan->id,
            'storage_disk' => 'public',
            'file_path' => 'usulan/USL-2026-MED003/photos/warga.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
            'caption' => 'Foto lokasi penyimpanan',
            'uploaded_by' => null,
        ]);

        $response = $this->get(route('usulan.lacak', ['kode' => $usulan->kode]));

        $response->assertOk();
        $response->assertSee('Dokumentasi dan Bukti Realisasi');
        $response->assertSee('Foto Usulan');
        $response->assertSee(route('public-usulan.media', $wargaMedia));
    }

    public function test_petugas_can_upload_bukti_photos_on_alur_update(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-MED004',
            'token_kirim' => 'token-test-4',
            'dusun_id' => $this->dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Drainase RT 01',
            'isi' => 'Usulan pembersihan dan perbaikan drainase RT 01.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => false,
            'dikirim_pada' => now(),
        ]);

        $fileBukti = UploadedFile::fake()->image('bukti-drainase.jpg', 600, 400);

        $response = $this->actingAs($admin)->patch(route('admin.usulan.alur.update', $usulan), [
            'versi_alur' => 0,
            'status' => 'selesai',
            'tampil_publik' => '1',
            'catatan_publik' => 'Pembersihan drainase telah selesai.',
            'bukti_photos' => [$fileBukti],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('usulan_media', [
            'usulan_id' => $usulan->id,
            'uploaded_by' => $admin->id,
            'caption' => 'Bukti tindak lanjut oleh petugas desa',
        ]);

        $media = UsulanMedia::query()->where('usulan_id', $usulan->id)->firstOrFail();
        Storage::disk('public')->assertExists($media->file_path);
    }

    public function test_missing_usulan_media_file_returns_404(): void
    {
        Storage::fake('public');

        $usulan = Usulan::query()->create([
            'kode' => 'USL-2026-MED005',
            'token_kirim' => 'token-test-5',
            'dusun_id' => $this->dusun->id,
            'jenis' => JenisUsulan::Pembangunan,
            'judul' => 'Kasus File Hilang',
            'isi' => 'Kasus uji ketika file foto terhapus di storage.',
            'status' => StatusUsulan::Baru,
            'tampil_publik' => true,
            'dikirim_pada' => now(),
        ]);

        $media = UsulanMedia::query()->create([
            'usulan_id' => $usulan->id,
            'storage_disk' => 'public',
            'file_path' => 'usulan/USL-2026-MED005/photos/tidak-ada.webp',
            'mime_type' => 'image/webp',
            'file_size' => 1024,
            'uploaded_by' => null,
        ]);

        $response = $this->get(route('public-usulan.media', $media));

        $response->assertNotFound();
    }
}
