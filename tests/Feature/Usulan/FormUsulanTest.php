<?php

namespace Tests\Feature\Usulan;

use App\Models\Dusun;
use App\Models\Usulan;
use Database\Seeders\DusunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FormUsulanTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_usulan_menampilkan_field_wajib_dan_jenis(): void
    {
        $this->seed(DusunSeeder::class);

        $this->get(route('usulan.create'))
            ->assertOk()
            ->assertSee('Sampaikan usulan dan saran untuk desa.')
            ->assertSee('Jenis usulan')
            ->assertSee('Isi usulan')
            ->assertSee('name="token_kirim"', false);
    }

    public function test_warga_dapat_mengirim_usulan_dan_diarahkan_ke_halaman_sukses(): void
    {
        $this->seed(DusunSeeder::class);
        $dusunId = Dusun::query()->value('id');

        $response = $this->post(route('usulan.store'), [
            'token_kirim' => Str::uuid()->toString(),
            'nama_pengusul' => 'Warga Padelegan',
            'telepon_pengusul' => '081234567890',
            'dusun_id' => $dusunId,
            'jenis' => 'pembangunan',
            'judul' => 'Tambah lampu jalan dusun',
            'isi' => 'Usul menambah lampu penerangan di jalur dusun agar warga aman saat malam.',
            'consent' => '1',
        ]);

        $usulan = Usulan::query()->firstOrFail();
        $response->assertRedirect(route('usulan.sukses', $usulan->kode));
        $this->assertMatchesRegularExpression('/^USL-\d{4}-[A-Z0-9]{6}$/', $usulan->kode);
    }

    public function test_isi_terlalu_pendek_ditolak(): void
    {
        $this->seed(DusunSeeder::class);
        $dusunId = Dusun::query()->value('id');

        $this->from(route('usulan.create'))->post(route('usulan.store'), [
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => $dusunId,
            'jenis' => 'pelayanan',
            'judul' => 'Saran singkat',
            'isi' => 'Terlalu pendek',
            'consent' => '1',
        ])->assertRedirect(route('usulan.create'))->assertSessionHasErrors('isi');

        $this->assertDatabaseCount('usulan', 0);
    }

    public function test_nomor_whatsapp_tidak_valid_ditolak_saat_diisi(): void
    {
        $this->seed(DusunSeeder::class);
        $dusunId = Dusun::query()->value('id');

        $this->from(route('usulan.create'))->post(route('usulan.store'), [
            'token_kirim' => Str::uuid()->toString(),
            'telepon_pengusul' => '12345',
            'dusun_id' => $dusunId,
            'jenis' => 'kegiatan',
            'judul' => 'Usulan kegiatan warga',
            'isi' => 'Mengadakan kegiatan gotong royong rutin setiap awal bulan di tiap dusun.',
            'consent' => '1',
        ])->assertRedirect(route('usulan.create'))->assertSessionHasErrors('telepon_pengusul');

        $this->assertDatabaseCount('usulan', 0);
    }

    public function test_mode_pratinjau_memblokir_pengiriman(): void
    {
        // Tanpa seeding dusun/kategori, situs berada dalam mode pratinjau.
        $this->from(route('usulan.create'))->post(route('usulan.store'), [
            'token_kirim' => Str::uuid()->toString(),
            'dusun_id' => 1,
            'jenis' => 'pembangunan',
            'judul' => 'Usulan saat pratinjau',
            'isi' => 'Usulan ini seharusnya ditolak karena database belum tersambung.',
            'consent' => '1',
        ])->assertRedirect(route('usulan.create'))->assertSessionHasErrors('submission');

        $this->assertDatabaseCount('usulan', 0);
    }
}
