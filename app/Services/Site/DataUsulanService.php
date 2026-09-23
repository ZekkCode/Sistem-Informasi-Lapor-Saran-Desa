<?php

namespace App\Services\Site;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Models\Dusun;
use App\Models\RiwayatUsulan;
use App\Models\Usulan;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DataUsulanService
{
    private ?bool $memakaiDemo = null;

    public function memakaiDemo(): bool
    {
        if ($this->memakaiDemo !== null) {
            return $this->memakaiDemo;
        }

        try {
            return $this->memakaiDemo = ! Usulan::query()->tampilPublik()->exists();
        } catch (QueryException $exception) {
            $this->catatFallback('usulan-publik-tersedia', $exception);

            return $this->memakaiDemo = true;
        }
    }

    /**
     * @return Collection<int, Dusun>
     */
    public function dusunUntukSaringan(): Collection
    {
        try {
            $dusuns = Dusun::query()->active()->orderBy('name')->get(['id', 'name']);

            if ($dusuns->isNotEmpty()) {
                return $dusuns;
            }
        } catch (QueryException $exception) {
            $this->catatFallback('usulan-publik-dusun', $exception);
        }

        return $this->usulanDemo()->map(fn (Usulan $usulan) => $usulan->dusun)->unique('id')->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginasiUsulanPublik(array $filters, Request $request): LengthAwarePaginatorContract
    {
        if ($this->memakaiDemo()) {
            return $this->paginasiDemo($filters, $request);
        }

        try {
            return Usulan::query()
                ->tampilPublik()
                ->with(['dusun:id,name'])
                ->when($filters['q'] ?? null, function ($query, string $kata) {
                    $query->where(function ($query) use ($kata) {
                        $query->where('judul', 'like', "%{$kata}%")
                            ->orWhere('kode', 'like', "%{$kata}%");
                    });
                })
                ->when($filters['dusun'] ?? null, fn ($query, $dusun) => $query->where('dusun_id', $dusun))
                ->when($filters['jenis'] ?? null, fn ($query, $jenis) => $query->where('jenis', $jenis))
                ->latest('dipublikasikan_pada')
                ->paginate(9)
                ->withQueryString();
        } catch (QueryException $exception) {
            $this->memakaiDemo = true;
            $this->catatFallback('usulan-publik-daftar', $exception);

            return $this->paginasiDemo($filters, $request);
        }
    }

    /**
     * @return array{usulan: Usulan|null, memakaiDemo: bool}
     */
    public function cariUsulanPublik(string $kode): array
    {
        if ($this->memakaiDemo()) {
            return [
                'usulan' => $this->usulanDemo()->firstWhere('kode', $kode),
                'memakaiDemo' => true,
            ];
        }

        try {
            $usulan = Usulan::query()
                ->tampilPublik()
                ->where('kode', $kode)
                ->with([
                    'dusun:id,name',
                    'riwayat' => fn ($query) => $query
                        ->select(['id', 'usulan_id', 'status', 'catatan_publik', 'created_at'])
                        ->reorder()->oldest(),
                ])
                ->first();

            return ['usulan' => $usulan, 'memakaiDemo' => false];
        } catch (QueryException $exception) {
            $this->memakaiDemo = true;
            $this->catatFallback('usulan-publik-detail', $exception);

            return [
                'usulan' => $this->usulanDemo()->firstWhere('kode', $kode),
                'memakaiDemo' => true,
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginasiDemo(array $filters, Request $request): LengthAwarePaginator
    {
        $usulan = $this->usulanDemo()
            ->when($filters['q'] ?? null, function (Collection $items, string $kata) {
                $kata = mb_strtolower($kata);

                return $items->filter(fn (Usulan $usulan) => str_contains(mb_strtolower($usulan->judul), $kata)
                    || str_contains(mb_strtolower($usulan->kode), $kata));
            })
            ->when($filters['dusun'] ?? null, fn (Collection $items, $dusun) => $items->where('dusun_id', (int) $dusun))
            ->when($filters['jenis'] ?? null, fn (Collection $items, $jenis) => $items->filter(
                fn (Usulan $usulan) => $usulan->jenis->value === $jenis,
            ))
            ->values();

        $halaman = LengthAwarePaginator::resolveCurrentPage();
        $perHalaman = 9;

        return new LengthAwarePaginator(
            $usulan->forPage($halaman, $perHalaman)->values(),
            $usulan->count(),
            $perHalaman,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }

    /**
     * @return Collection<int, Usulan>
     */
    private function usulanDemo(): Collection
    {
        $tahun = now()->format('Y');
        $dusunContoh = [
            [1001, 'BKL', 'Bangkal'],
            [1002, 'ASB', 'Asam Batur'],
        ];

        $definisi = [
            [
                'kode' => "USL-{$tahun}-DEMO01",
                'judul' => 'Penambahan lampu di jalan dusun',
                'isi' => 'Warga mengusulkan penambahan lampu penerangan di jalur dusun agar aman saat malam.',
                'catatan' => 'Usulan masuk pembahasan musyawarah dusun dan dianggarkan tahun berjalan.',
                'status' => StatusUsulan::Ditindaklanjuti,
                'jenis' => JenisUsulan::Pembangunan,
                'dusun' => 1001,
                'hari_lalu' => 9,
            ],
            [
                'kode' => "USL-{$tahun}-DEMO02",
                'judul' => 'Jam layanan surat dibuka lebih pagi',
                'isi' => 'Usulan agar layanan surat di balai desa dibuka lebih pagi untuk warga yang bekerja.',
                'catatan' => 'Jam layanan disesuaikan mulai pukul 07.30 sesuai usulan warga.',
                'status' => StatusUsulan::Selesai,
                'jenis' => JenisUsulan::Pelayanan,
                'dusun' => 1002,
                'hari_lalu' => 4,
            ],
        ];

        $dusuns = collect($dusunContoh)->mapWithKeys(function (array $data): array {
            $dusun = new Dusun(['code' => $data[1], 'name' => $data[2], 'is_active' => true]);
            $dusun->forceFill(['id' => $data[0]]);

            return [$data[0] => $dusun];
        });

        return collect($definisi)->map(function (array $data, int $index) use ($dusuns): Usulan {
            $dikirim = now()->subDays($data['hari_lalu'])->startOfHour();
            $usulan = new Usulan([
                'kode' => $data['kode'],
                'dusun_id' => $data['dusun'],
                'jenis' => $data['jenis']->value,
                'judul' => $data['judul'],
                'isi' => $data['isi'],
                'status' => $data['status']->value,
                'tampil_publik' => true,
                'catatan_publik' => $data['catatan'],
                'dipublikasikan_pada' => $dikirim->copy()->addDays(2),
                'dikirim_pada' => $dikirim,
            ]);
            $usulan->forceFill(['id' => 5001 + $index]);
            $usulan->setRelation('dusun', $dusuns->get($data['dusun']));
            $usulan->setRelation('riwayat', $this->riwayatDemo($usulan, $dikirim));

            return $usulan;
        });
    }

    /**
     * @return Collection<int, RiwayatUsulan>
     */
    private function riwayatDemo(Usulan $usulan, CarbonInterface $dikirim): Collection
    {
        $langkah = [
            [StatusUsulan::Baru, 'Usulan diterima dan menunggu tinjauan.', $dikirim],
            [StatusUsulan::Ditinjau, 'Petugas meninjau usulan bersama kepala dusun.', $dikirim->copy()->addDay()],
        ];

        if ($usulan->status === StatusUsulan::Ditindaklanjuti || $usulan->status === StatusUsulan::Selesai) {
            $langkah[] = [StatusUsulan::Ditindaklanjuti, $usulan->catatan_publik, $dikirim->copy()->addDays(2)];
        }

        if ($usulan->status === StatusUsulan::Selesai) {
            $langkah[] = [StatusUsulan::Selesai, 'Usulan selesai ditindaklanjuti.', $dikirim->copy()->addDays(3)];
        }

        return collect($langkah)->map(function (array $langkah, int $index) use ($usulan): RiwayatUsulan {
            $riwayat = new RiwayatUsulan([
                'usulan_id' => $usulan->id,
                'status' => $langkah[0]->value,
                'catatan_publik' => $langkah[1],
            ]);
            $riwayat->forceFill([
                'id' => ($usulan->id * 10) + $index,
                'created_at' => $langkah[2],
                'updated_at' => $langkah[2],
            ]);

            return $riwayat;
        });
    }

    private function catatFallback(string $area, QueryException $exception): void
    {
        Log::notice('Data demo usulan digunakan karena data utama belum dapat dibaca.', [
            'area' => $area,
            'exception' => $exception,
        ]);
    }
}
