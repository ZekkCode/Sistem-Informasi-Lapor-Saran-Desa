<?php

namespace App\Services\Formulir;

use App\Enums\JenisFormulir;
use App\Models\Setting;
use App\Models\User;
use App\Services\Admin\AuditService;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LayananKunciFormulir
{
    /** @var Collection<string, string|null>|null */
    private ?Collection $nilai = null;

    public function __construct(
        private readonly AuditService $audit,
    ) {}

    public function status(JenisFormulir $formulir): StatusKunciFormulir
    {
        $nilai = $this->nilai();

        return new StatusKunciFormulir(
            formulir: $formulir,
            kunciAdmin: $this->terkunci($nilai->get($formulir->namaSettingKunciAdmin())),
            kunciPengembang: $this->terkunci($nilai->get($formulir->namaSettingKunciPengembang())),
            catatan: $this->rapikanCatatan($nilai->get($formulir->namaSettingCatatan())),
        );
    }

    /**
     * Menyimpan kunci admin beserta catatan publik. Catatan dikosongkan saat
     * formulir dibuka agar tidak muncul lagi pada penutupan berikutnya.
     */
    public function aturKunciAdmin(JenisFormulir $formulir, bool $terkunci, ?string $catatan, User $petugas): bool
    {
        $kunci = $formulir->namaSettingKunciAdmin();
        $kunciCatatan = $formulir->namaSettingCatatan();
        $catatan = $terkunci ? $this->rapikanCatatan($catatan) : null;

        return $this->simpan(
            aksi: 'formulir.kunci_admin_diubah',
            formulir: $formulir,
            petugas: $petugas,
            baru: [$kunci => $terkunci ? '1' : '0', $kunciCatatan => $catatan],
            ringkas: fn (Closure $ambil): array => [
                'terkunci' => $this->terkunci($ambil($kunci)),
                'catatan' => $this->rapikanCatatan($ambil($kunciCatatan)),
            ],
        );
    }

    public function aturKunciPengembang(JenisFormulir $formulir, bool $terkunci, User $petugas): bool
    {
        $kunci = $formulir->namaSettingKunciPengembang();

        return $this->simpan(
            aksi: 'formulir.kunci_pengembang_diubah',
            formulir: $formulir,
            petugas: $petugas,
            baru: [$kunci => $terkunci ? '1' : '0'],
            ringkas: fn (Closure $ambil): array => [
                'terkunci' => $this->terkunci($ambil($kunci)),
            ],
        );
    }

    /**
     * Set nilai eksplisit: kiriman ulang dengan keadaan yang sama tidak menulis
     * apa pun dan tidak menambah audit.
     *
     * @param  array<string, string|null>  $baru
     * @param  Closure(Closure(string): (string|null)): array<string, mixed>  $ringkas
     */
    private function simpan(string $aksi, JenisFormulir $formulir, User $petugas, array $baru, Closure $ringkas): bool
    {
        $berubah = DB::transaction(function () use ($aksi, $formulir, $petugas, $baru, $ringkas): bool {
            $lama = Setting::query()
                ->whereIn('key', array_keys($baru))
                ->lockForUpdate()
                ->pluck('value', 'key');

            $sebelum = $ringkas(fn (string $key): ?string => $lama->get($key));
            $sesudah = $ringkas(fn (string $key): ?string => $baru[$key]);

            if ($sebelum === $sesudah) {
                return false;
            }

            Setting::query()->upsert(
                collect($baru)->map(fn (?string $value, string $key): array => ['key' => $key, 'value' => $value])->values()->all(),
                ['key'],
                ['value'],
            );

            $this->audit->record($aksi, null, [
                'formulir' => $formulir->value,
                'peran' => $petugas->role->value,
                'sebelum' => $sebelum,
                'sesudah' => $sesudah,
            ]);

            return true;
        });

        $this->nilai = null;

        return $berubah;
    }

    /** @return Collection<string, string|null> */
    private function nilai(): Collection
    {
        if ($this->nilai !== null) {
            return $this->nilai;
        }

        $keys = collect(JenisFormulir::cases())
            ->flatMap(fn (JenisFormulir $formulir): array => [
                $formulir->namaSettingKunciAdmin(),
                $formulir->namaSettingKunciPengembang(),
                $formulir->namaSettingCatatan(),
            ])
            ->all();

        try {
            return $this->nilai = Setting::query()->whereIn('key', $keys)->pluck('value', 'key');
        } catch (QueryException $exception) {
            Log::notice('Kunci formulir tidak dapat dibaca. Formulir dianggap terbuka.', [
                'exception' => $exception,
            ]);

            return $this->nilai = collect();
        }
    }

    private function terkunci(?string $nilai): bool
    {
        return $nilai === '1';
    }

    private function rapikanCatatan(?string $catatan): ?string
    {
        $catatan = trim((string) $catatan);

        return $catatan === '' ? null : $catatan;
    }
}
