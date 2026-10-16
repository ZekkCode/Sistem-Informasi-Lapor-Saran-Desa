<?php

namespace App\Queries;

use App\Models\Usulan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PencarianUsulanAdmin
{
    private static bool $hasEnsuredMediaTable = false;

    /** @param array<string, mixed> $filters */
    public function bangun(array $filters): Builder
    {
        $hasMediaTable = $this->ensureMediaTable();

        return Usulan::query()
            ->with([
                'dusun:id,name',
                ...($hasMediaTable ? ['media:id,usulan_id,file_path,storage_disk,mime_type'] : []),
            ])
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
            ->when($filters['periode'] ?? null, function (Builder $query, string $periode) {
                match ($periode) {
                    'minggu' => $query->where('dikirim_pada', '>=', now()->subDays(7)),
                    'bulan' => $query->where('dikirim_pada', '>=', now()->subDays(30)),
                    'tahun' => $query->where('dikirim_pada', '>=', now()->startOfYear()),
                    default => null,
                };
            })
            ->when($filters['date_from'] ?? null, fn (Builder $query, $nilai) => $query->whereDate('dikirim_pada', '>=', $nilai))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $nilai) => $query->whereDate('dikirim_pada', '<=', $nilai))
            ->latest('dikirim_pada');
    }

    private function ensureMediaTable(): bool
    {
        if (self::$hasEnsuredMediaTable) {
            return true;
        }

        try {
            if (! Schema::hasTable('usulan_media')) {
                Schema::create('usulan_media', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('usulan_id')->constrained('usulan')->cascadeOnDelete();
                    $table->string('storage_disk', 32)->default('public');
                    $table->string('file_path');
                    $table->string('mime_type', 100);
                    $table->unsignedBigInteger('file_size');
                    $table->string('caption')->nullable();
                    $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }

            self::$hasEnsuredMediaTable = true;

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
