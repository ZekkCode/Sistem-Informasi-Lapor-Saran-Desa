<?php

namespace App\Services\Usulan;

use App\Enums\StatusUsulan;
use App\Models\User;
use App\Models\Usulan;
use App\Services\Admin\AuditService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LayananAlurUsulan
{
    public function __construct(
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function perbarui(Usulan $usulan, array $data, User $user): Usulan
    {
        $sebelum = Arr::only($usulan->getAttributes(), [
            'status',
            'tampil_publik',
            'catatan_publik',
        ]);

        DB::transaction(function () use ($usulan, $data, $user): void {
            $terkunci = Usulan::query()->lockForUpdate()->findOrFail($usulan->getKey());

            if ($terkunci->versi_alur !== (int) $data['versi_alur']) {
                throw ValidationException::withMessages([
                    'versi_alur' => 'Petugas lain sudah memperbarui usulan ini. Muat ulang halaman, periksa perubahan terbaru, lalu simpan kembali.',
                ]);
            }

            $status = StatusUsulan::from($data['status']);
            $tampilPublik = (bool) ($data['tampil_publik'] ?? false);
            $catatanPublik = $data['catatan_publik'] ?? null;
            $catatanInternal = $data['catatan_internal'] ?? null;

            $terkunci->forceFill([
                'status' => $status,
                'tampil_publik' => $tampilPublik,
                'catatan_publik' => $catatanPublik ?: null,
                'ditangani_oleh' => $user->getKey(),
                'dipublikasikan_pada' => $tampilPublik ? ($terkunci->dipublikasikan_pada ?? now()) : null,
                'versi_alur' => $terkunci->versi_alur + 1,
            ])->save();

            $terkunci->riwayat()->create([
                'status' => $status,
                'catatan_publik' => $catatanPublik ?: null,
                'catatan_internal' => $catatanInternal ?: null,
                'diubah_oleh' => $user->getKey(),
            ]);

            $usulan->setRawAttributes($terkunci->getAttributes());
        });

        $usulan->refresh();

        $this->audit->record('usulan.alur_diperbarui', $usulan, [
            'sebelum' => $sebelum,
            'sesudah' => Arr::only($usulan->getAttributes(), [
                'status',
                'tampil_publik',
                'catatan_publik',
            ]),
        ]);

        return $usulan;
    }
}
