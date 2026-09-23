<?php

namespace App\Services\Usulan;

use App\Enums\StatusUsulan;
use App\Models\Usulan;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LayananKirimUsulan
{
    public function __construct(
        private readonly PembuatKodeUsulan $pembuatKode,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function kirim(array $data): Usulan
    {
        $tokenKirim = (string) $data['token_kirim'];
        $usulanLama = Usulan::query()->where('token_kirim', $tokenKirim)->first();

        if ($usulanLama) {
            return $usulanLama;
        }

        $kode = $this->pembuatKode->buat();

        return DB::transaction(function () use ($data, $kode): Usulan {
            $usulan = Usulan::query()->create([
                ...Arr::only($data, [
                    'token_kirim',
                    'nama_pengusul',
                    'telepon_pengusul',
                    'dusun_id',
                    'jenis',
                    'judul',
                    'isi',
                ]),
                'kode' => $kode,
                'status' => StatusUsulan::Baru,
                'tampil_publik' => false,
                'catatan_publik' => 'Usulan diterima dan menunggu tinjauan Pemerintah Desa.',
                'dikirim_pada' => now(),
            ]);

            $usulan->riwayat()->create([
                'status' => StatusUsulan::Baru,
                'catatan_publik' => 'Usulan berhasil dikirim dan menunggu tinjauan.',
            ]);

            return $usulan;
        });
    }
}
