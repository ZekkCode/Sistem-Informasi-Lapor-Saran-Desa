<?php

namespace App\Queries;

use App\Models\Usulan;
use Illuminate\Database\Eloquent\Builder;

class PencarianUsulanAdmin
{
    /** @param array<string, mixed> $filters */
    public function bangun(array $filters): Builder
    {
        return Usulan::query()
            ->with(['dusun:id,name'])
            ->when($filters['q'] ?? null, function (Builder $query, string $kata) {
                $query->where(function (Builder $query) use ($kata) {
                    $query->where('kode', 'like', "%{$kata}%")
                        ->orWhere('judul', 'like', "%{$kata}%")
                        ->orWhere('nama_pengusul', 'like', "%{$kata}%")
                        ->orWhere('telepon_pengusul', 'like', "%{$kata}%");
                });
            })
            ->when($filters['dusun_id'] ?? null, fn (Builder $query, $nilai) => $query->where('dusun_id', $nilai))
            ->when($filters['jenis'] ?? null, fn (Builder $query, $nilai) => $query->where('jenis', $nilai))
            ->when($filters['status'] ?? null, fn (Builder $query, $nilai) => $query->where('status', $nilai))
            ->when(
                array_key_exists('tampil_publik', $filters) && $filters['tampil_publik'] !== null && $filters['tampil_publik'] !== '',
                fn (Builder $query) => $query->where('tampil_publik', (bool) $filters['tampil_publik']),
            )
            ->when($filters['date_from'] ?? null, fn (Builder $query, $nilai) => $query->whereDate('dikirim_pada', '>=', $nilai))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $nilai) => $query->whereDate('dikirim_pada', '<=', $nilai))
            ->latest('dikirim_pada');
    }
}
