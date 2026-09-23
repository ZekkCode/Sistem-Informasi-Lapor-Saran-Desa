<?php

namespace Tests\Feature\Usulan;

use App\Enums\StatusUsulan;
use App\Models\Usulan;
use App\Services\Usulan\LayananKirimUsulan;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KirimUsulanTest extends TestCase
{
    use RefreshDatabase;

    public function test_layanan_membuat_usulan_dengan_kode_unik_dan_riwayat_awal(): void
    {
        $this->seed(DusunSeeder::class);
        $dusunId = \App\Models\Dusun::query()->value('id');

        $usulan = app(LayananKirimUsulan::class)->kirim([
            'token_kirim' => Str::uuid()->toString(),
            'nama_pengusul' => 'Warga Padelegan',
            'telepon_pengusul' => '081234567890',
            'dusun_id' => $dusunId,
            'jenis' => 'pembangunan',
            'judul' => 'Perbaikan lampu balai dusun',
            'isi' => 'Mohon penambahan lampu di halaman balai dusun agar aman saat malam.',
        ]);

        $this->assertMatchesRegularExpression('/^USL-\d{4}-[A-Z0-9]{6}$/', $usulan->kode);
        $this->assertSame(StatusUsulan::Baru, $usulan->status);
        $this->assertFalse($usulan->tampil_publik);
        $this->assertDatabaseCount('usulan', 1);
        $this->assertDatabaseCount('riwayat_usulan', 1);
    }

    public function test_pengiriman_ulang_dengan_token_sama_tidak_menggandakan(): void
    {
        $this->seed(DusunSeeder::class);
        $dusunId = \App\Models\Dusun::query()->value('id');
        $token = Str::uuid()->toString();

        $payload = [
            'token_kirim' => $token,
            'nama_pengusul' => 'Warga Padelegan',
            'telepon_pengusul' => '081234567890',
            'dusun_id' => $dusunId,
            'jenis' => 'pelayanan',
            'judul' => 'Tambah jam layanan surat',
            'isi' => 'Usulan agar layanan surat dibuka lebih pagi untuk warga yang bekerja.',
        ];

        $pertama = app(LayananKirimUsulan::class)->kirim($payload);
        $kedua = app(LayananKirimUsulan::class)->kirim($payload);

        $this->assertSame($pertama->id, $kedua->id);
        $this->assertDatabaseCount('usulan', 1);
        $this->assertDatabaseCount('riwayat_usulan', 1);
    }

    public function test_usulan_dapat_dikirim_tanpa_identitas(): void
    {
        $this->seed(DusunSeeder::class);
        $dusunId = \App\Models\Dusun::query()->value('id');

        $usulan = app(LayananKirimUsulan::class)->kirim([
            'token_kirim' => Str::uuid()->toString(),
            'nama_pengusul' => null,
            'telepon_pengusul' => null,
            'dusun_id' => $dusunId,
            'jenis' => 'kegiatan',
            'judul' => 'Kerja bakti rutin',
            'isi' => 'Usulan mengadakan kerja bakti setiap awal bulan di tiap dusun.',
        ]);

        $this->assertNull($usulan->nama_pengusul);
        $this->assertNull($usulan->telepon_pengusul);
        $this->assertDatabaseHas('usulan', ['id' => $usulan->id, 'nama_pengusul' => null]);
    }
}
