<?php

namespace App\Services\Usulan;

use App\Enums\StatusUsulan;
use App\Models\Usulan;
use App\Services\Reports\DirectUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LayananKirimUsulan
{
    public function __construct(
        private readonly PembuatKodeUsulan $pembuatKode,
        private readonly DirectUploadService $directUploadService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     * @param  array<int, string>  $uploadedMedia
     */
    public function kirim(array $data, array $photos = [], array $uploadedMedia = []): Usulan
    {
        $tokenKirim = (string) $data['token_kirim'];
        $usulanLama = Usulan::query()->where('token_kirim', $tokenKirim)->first();

        if ($usulanLama) {
            return $usulanLama;
        }

        $kode = $this->pembuatKode->buat();

        return DB::transaction(function () use ($data, $photos, $uploadedMedia, $kode): Usulan {
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

            if ($uploadedMedia !== []) {
                $this->directUploadService->attachUploadedUsulanEvidence($usulan, $uploadedMedia);
            } elseif ($photos !== []) {
                $disk = (string) config('report-media.disk', 'public');
                foreach ($photos as $photo) {
                    $ext = $photo->guessExtension() ?: 'jpg';
                    $filename = Str::ulid().'.'.$ext;
                    $path = "usulan/{$usulan->kode}/photos/{$filename}";
                    Storage::disk($disk)->putFileAs("usulan/{$usulan->kode}/photos", $photo, $filename);
                    $usulan->media()->create([
                        'storage_disk' => $disk,
                        'file_path' => $path,
                        'mime_type' => $photo->getMimeType() ?: 'image/jpeg',
                        'file_size' => $photo->getSize() ?: 0,
                    ]);
                }
            }

            $usulan->riwayat()->create([
                'status' => StatusUsulan::Baru,
                'catatan_publik' => 'Usulan berhasil dikirim dan menunggu tinjauan.',
            ]);

            return $usulan;
        });
    }
}
