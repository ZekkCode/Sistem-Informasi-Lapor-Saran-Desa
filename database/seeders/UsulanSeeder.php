<?php

namespace Database\Seeders;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Models\Dusun;
use App\Models\Usulan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsulanSeeder extends Seeder
{
    public function run(): void
    {
        $dusuns = Dusun::query()->pluck('id', 'name');

        if ($dusuns->isEmpty()) {
            $this->command?->warn('Usulan contoh dilewati. Jalankan DusunSeeder lebih dahulu.');

            return;
        }

        $contoh = [
            [
                'judul' => 'Penambahan lampu penerangan jalan dusun',
                'isi' => 'Warga mengusulkan penambahan lampu penerangan di jalur utama dusun agar aman saat malam, terutama di dekat pertigaan.',
                'jenis' => JenisUsulan::Pembangunan,
                'status' => StatusUsulan::Ditindaklanjuti,
                'dusun' => 'Bangkal',
                'nama' => 'Warga Bangkal',
                'telepon' => '081234567801',
                'tampil_publik' => true,
                'catatan' => 'Usulan masuk pembahasan musyawarah dusun dan dianggarkan tahun berjalan.',
                'hari_lalu' => 12,
                'riwayat' => [
                    [StatusUsulan::Baru, 'Usulan diterima dan menunggu tinjauan.', 12],
                    [StatusUsulan::Ditinjau, 'Petugas meninjau usulan bersama kepala dusun.', 10],
                    [StatusUsulan::Ditindaklanjuti, 'Usulan masuk pembahasan musyawarah dusun dan dianggarkan tahun berjalan.', 7],
                ],
            ],
            [
                'judul' => 'Jam layanan surat dibuka lebih pagi',
                'isi' => 'Usulan agar layanan surat di balai desa dibuka mulai pukul 07.30 untuk warga yang harus bekerja pagi.',
                'jenis' => JenisUsulan::Pelayanan,
                'status' => StatusUsulan::Selesai,
                'dusun' => 'Asam Batur',
                'nama' => null,
                'telepon' => null,
                'tampil_publik' => true,
                'catatan' => 'Jam layanan disesuaikan mulai pukul 07.30 sesuai usulan warga.',
                'hari_lalu' => 20,
                'riwayat' => [
                    [StatusUsulan::Baru, 'Usulan diterima dan menunggu tinjauan.', 20],
                    [StatusUsulan::Ditinjau, 'Petugas membahas penyesuaian jadwal layanan.', 18],
                    [StatusUsulan::Selesai, 'Jam layanan disesuaikan mulai pukul 07.30 sesuai usulan warga.', 14],
                ],
            ],
            [
                'judul' => 'Kerja bakti rutin tiap awal bulan',
                'isi' => 'Mengusulkan kegiatan kerja bakti bersama setiap awal bulan untuk menjaga kebersihan lingkungan dusun.',
                'jenis' => JenisUsulan::Kegiatan,
                'status' => StatusUsulan::Baru,
                'dusun' => 'Modung',
                'nama' => 'Warga Modung',
                'telepon' => '081234567803',
                'tampil_publik' => false,
                'catatan' => 'Usulan diterima dan menunggu tinjauan Pemerintah Desa.',
                'hari_lalu' => 2,
                'riwayat' => [
                    [StatusUsulan::Baru, 'Usulan berhasil dikirim dan menunggu tinjauan.', 2],
                ],
            ],
        ];

        foreach ($contoh as $data) {
            $dusunId = $dusuns->get($data['dusun']);

            if (! $dusunId) {
                continue;
            }

            $dikirim = now()->subDays($data['hari_lalu'])->startOfHour();

            $usulan = Usulan::query()->updateOrCreate(
                ['kode' => $this->kodeContoh($data['judul'])],
                [
                    'token_kirim' => (string) Str::uuid(),
                    'versi_alur' => count($data['riwayat']) - 1,
                    'nama_pengusul' => $data['nama'],
                    'telepon_pengusul' => $data['telepon'],
                    'dusun_id' => $dusunId,
                    'jenis' => $data['jenis'],
                    'judul' => $data['judul'],
                    'isi' => $data['isi'],
                    'status' => $data['status'],
                    'tampil_publik' => $data['tampil_publik'],
                    'catatan_publik' => $data['catatan'],
                    'dipublikasikan_pada' => $data['tampil_publik'] ? $dikirim->copy()->addDays(2) : null,
                    'dikirim_pada' => $dikirim,
                ],
            );

            $usulan->riwayat()->delete();

            foreach ($data['riwayat'] as $langkah) {
                $waktu = now()->subDays($langkah[2])->startOfHour();
                $riwayat = $usulan->riwayat()->create([
                    'status' => $langkah[0],
                    'catatan_publik' => $langkah[1],
                ]);
                $riwayat->forceFill(['created_at' => $waktu, 'updated_at' => $waktu])->save();
            }
        }
    }

    private function kodeContoh(string $judul): string
    {
        return 'USL-'.now()->format('Y').'-'.Str::upper(Str::substr(Str::slug($judul, ''), 0, 6));
    }
}
