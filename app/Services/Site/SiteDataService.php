<?php

namespace App\Services\Site;

use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\QrSource;
use App\Models\Report;
use App\Models\ReportStatusHistory;
use App\Models\Subcategory;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SiteDataService
{
    /** @var array{dusuns: Collection<int, Dusun>, categories: Collection<int, Category>, usingDemoData: bool}|null */
    private ?array $referenceData = null;

    private ?bool $publicReportsUseDemoData = null;

    private ?bool $trackingUsesDemoData = null;

    /**
     * @return array{dusuns: Collection<int, Dusun>, categories: Collection<int, Category>, usingDemoData: bool}
     */
    public function referenceData(): array
    {
        if ($this->referenceData !== null) {
            return $this->referenceData;
        }

        try {
            $dusuns = Dusun::query()->active()->orderBy('name')->get();
            $categories = Category::query()
                ->active()
                ->with(['subcategories' => fn ($query) => $query->active()])
                ->orderBy('sort_order')
                ->get()
                ->filter(fn (Category $category) => $category->subcategories->isNotEmpty())
                ->values();

            if ($dusuns->isNotEmpty() && $categories->isNotEmpty()) {
                return $this->referenceData = [
                    'dusuns' => $dusuns,
                    'categories' => $categories,
                    'usingDemoData' => false,
                ];
            }
        } catch (QueryException $exception) {
            $this->logFallback('reference-data', $exception);
        }

        return $this->referenceData = $this->demoReferenceData();
    }

    public function canAcceptReports(): bool
    {
        return ! $this->referenceData()['usingDemoData'];
    }

    public function bisaMenerimaUsulan(): bool
    {
        try {
            return Dusun::query()->active()->exists();
        } catch (QueryException $exception) {
            $this->logFallback('usulan-availability', $exception);

            return false;
        }
    }

    /**
     * @return Collection<int, Dusun>
     */
    public function dusunUntukUsulan(): Collection
    {
        try {
            $dusuns = Dusun::query()->active()->orderBy('name')->get();

            if ($dusuns->isNotEmpty()) {
                return $dusuns;
            }
        } catch (QueryException $exception) {
            $this->logFallback('usulan-dusuns', $exception);
        }

        return $this->demoReferenceData()['dusuns'];
    }

    public function findQrSource(string $code): ?QrSource
    {
        if ($code === '' || ! $this->canAcceptReports()) {
            return null;
        }

        try {
            return QrSource::query()->active()->where('code', $code)->first();
        } catch (QueryException $exception) {
            $this->logFallback('qr-source', $exception);

            return null;
        }
    }

    /**
     * @return array{dusuns: Collection<int, Dusun>, categories: Collection<int, Category>, usingDemoData: bool}
     */
    public function publicReferenceData(): array
    {
        $references = $this->referenceData();

        if ($references['usingDemoData'] || $this->publicReportsUseDemoData()) {
            return $this->demoReferenceData();
        }

        return $references;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginatePublicReports(array $filters, Request $request): LengthAwarePaginatorContract
    {
        if ($this->publicReportsUseDemoData()) {
            return $this->paginateDemoReports($filters, $request);
        }

        try {
            return Report::query()
                ->visibleToPublic()
                ->with(['dusun:id,name', 'subcategory:id,category_id,name', 'subcategory.category:id,name'])
                ->when($filters['q'] ?? null, function ($query, string $keyword) {
                    $query->where(function ($query) use ($keyword) {
                        $query->where('title', 'like', "%{$keyword}%")
                            ->orWhere('report_code', 'like', "%{$keyword}%");
                    });
                })
                ->when($filters['dusun'] ?? null, fn ($query, $dusun) => $query->where('dusun_id', $dusun))
                ->when($filters['category'] ?? null, function ($query, $category) {
                    $query->whereHas('subcategory', fn ($query) => $query->where('category_id', $category));
                })
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest('submitted_at')
                ->paginate(9)
                ->withQueryString();
        } catch (QueryException $exception) {
            $this->publicReportsUseDemoData = true;
            $this->logFallback('public-reports', $exception);

            return $this->paginateDemoReports($filters, $request);
        }
    }

    /**
     * @return array{report: Report|null, usingDemoData: bool}
     */
    public function findPublicReport(string $reportCode): array
    {
        if ($this->publicReportsUseDemoData()) {
            return [
                'report' => $this->demoReports()->firstWhere('report_code', $reportCode),
                'usingDemoData' => true,
            ];
        }

        try {
            $report = Report::query()
                ->visibleToPublic()
                ->where('report_code', $reportCode)
                ->with([
                    'dusun:id,name',
                    'subcategory:id,category_id,name',
                    'subcategory.category:id,name',
                    'histories' => fn ($query) => $query
                        ->select(['id', 'report_id', 'verification_status', 'status', 'public_note', 'created_at'])
                        ->oldest(),
                    'media',
                ])
                ->first();

            return ['report' => $report, 'usingDemoData' => false];
        } catch (QueryException $exception) {
            $this->publicReportsUseDemoData = true;
            $this->logFallback('public-report', $exception);

            return [
                'report' => $this->demoReports()->firstWhere('report_code', $reportCode),
                'usingDemoData' => true,
            ];
        }
    }

    /**
     * @return array{report: Report|null, usingDemoData: bool}
     */
    public function findTrackedReport(string $reportCode): array
    {
        if ($this->trackingUsesDemoData()) {
            return [
                'report' => $this->demoReports()->firstWhere('report_code', $reportCode),
                'usingDemoData' => true,
            ];
        }

        try {
            $report = Report::query()
                ->where('report_code', $reportCode)
                ->with([
                    'dusun:id,name',
                    'subcategory:id,category_id,name',
                    'subcategory.category:id,name',
                    'histories' => fn ($query) => $query
                        ->select(['id', 'report_id', 'verification_status', 'status', 'public_note', 'created_at'])
                        ->oldest(),
                ])
                ->first();

            return ['report' => $report, 'usingDemoData' => false];
        } catch (QueryException $exception) {
            $this->trackingUsesDemoData = true;
            $this->logFallback('report-tracking', $exception);

            return [
                'report' => $this->demoReports()->firstWhere('report_code', $reportCode),
                'usingDemoData' => true,
            ];
        }
    }

    private function publicReportsUseDemoData(): bool
    {
        if ($this->publicReportsUseDemoData !== null) {
            return $this->publicReportsUseDemoData;
        }

        if ($this->referenceData()['usingDemoData']) {
            return $this->publicReportsUseDemoData = true;
        }

        try {
            return $this->publicReportsUseDemoData = ! Report::query()->visibleToPublic()->exists();
        } catch (QueryException $exception) {
            $this->logFallback('public-report-availability', $exception);

            return $this->publicReportsUseDemoData = true;
        }
    }

    public function trackingUsesDemoData(): bool
    {
        if ($this->trackingUsesDemoData !== null) {
            return $this->trackingUsesDemoData;
        }

        if ($this->referenceData()['usingDemoData']) {
            return $this->trackingUsesDemoData = true;
        }

        try {
            return $this->trackingUsesDemoData = ! Report::query()->exists();
        } catch (QueryException $exception) {
            $this->logFallback('tracking-availability', $exception);

            return $this->trackingUsesDemoData = true;
        }
    }

    public function demoTrackingCode(): string
    {
        return 'PDL-'.now()->format('Y').'-DEMO02';
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function paginateDemoReports(array $filters, Request $request): LengthAwarePaginator
    {
        $reports = $this->demoReports()
            ->when($filters['q'] ?? null, function (Collection $reports, string $keyword) {
                $keyword = mb_strtolower($keyword);

                return $reports->filter(fn (Report $report) => str_contains(mb_strtolower($report->title), $keyword)
                    || str_contains(mb_strtolower($report->report_code), $keyword));
            })
            ->when($filters['dusun'] ?? null, fn (Collection $reports, $dusun) => $reports->where('dusun_id', (int) $dusun))
            ->when($filters['category'] ?? null, fn (Collection $reports, $category) => $reports->filter(
                fn (Report $report) => $report->subcategory->category_id === (int) $category,
            ))
            ->when($filters['status'] ?? null, fn (Collection $reports, $status) => $reports->filter(
                fn (Report $report) => $report->status->value === $status,
            ))
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 9;

        return new LengthAwarePaginator(
            $reports->forPage($page, $perPage)->values(),
            $reports->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    /**
     * @return array{dusuns: Collection<int, Dusun>, categories: Collection<int, Category>, usingDemoData: true}
     */
    private function demoReferenceData(): array
    {
        $dusuns = collect([
            [1001, 'BKL', 'Bangkal'],
            [1002, 'ASB', 'Asam Batur'],
            [1003, 'LTB', 'Laok Tambak'],
            [1004, 'MDG', 'Modung'],
            [1005, 'DTB', 'Dajah Tambak'],
            [1006, 'MRA', 'Muarah'],
        ])->map(function (array $data): Dusun {
            $dusun = new Dusun(['code' => $data[1], 'name' => $data[2], 'is_active' => true]);
            $dusun->forceFill(['id' => $data[0]]);

            return $dusun;
        });

        $definitions = [
            [2001, 'Fasilitas Desa', ['Lampu dan Penerangan Jalan', 'Fasilitas Umum', 'Lainnya']],
            [2002, 'Lingkungan', ['Sampah', 'Drainase', 'Pencemaran', 'Lainnya']],
            [2003, 'Infrastruktur', ['Jalan', 'Jembatan', 'Saluran Air', 'Lainnya']],
            [2004, 'Pelayanan Desa', ['Administrasi', 'Informasi Publik', 'Lainnya']],
            [2005, 'Aspirasi Masyarakat', ['Usulan Kegiatan', 'Saran Pembangunan', 'Lainnya']],
        ];

        $subcategoryId = 3000;
        $categories = collect($definitions)->map(function (array $definition, int $index) use (&$subcategoryId): Category {
            $category = new Category([
                'name' => $definition[1],
                'slug' => str($definition[1])->slug()->toString(),
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
            $category->forceFill(['id' => $definition[0]]);

            $subcategories = collect($definition[2])->map(function (string $name, int $index) use ($category, &$subcategoryId): Subcategory {
                $subcategory = new Subcategory([
                    'name' => $name,
                    'slug' => str($name)->slug()->toString(),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);
                $subcategory->forceFill([
                    'id' => ++$subcategoryId,
                    'category_id' => $category->id,
                ]);
                $subcategory->setRelation('category', $category);

                return $subcategory;
            });

            $category->setRelation('subcategories', $subcategories);

            return $category;
        });

        return [
            'dusuns' => $dusuns,
            'categories' => $categories,
            'usingDemoData' => true,
        ];
    }

    /** @return Collection<int, Report> */
    private function demoReports(): Collection
    {
        $references = $this->demoReferenceData();
        $year = now()->format('Y');
        $definitions = [
            [
                'code' => "PDL-{$year}-DEMO01",
                'title' => 'Perbaikan jalan berlubang dekat pasar desa',
                'description' => 'Permukaan jalan di jalur menuju pasar berlubang dan mengganggu kendaraan warga, terutama setelah hujan.',
                'note' => 'Perbaikan telah selesai dan lokasi sudah diperiksa kembali oleh petugas desa.',
                'status' => ReportStatus::Completed,
                'verification' => VerificationStatus::Verified,
                'dusun' => 'Dajah Tambak',
                'category' => 'Infrastruktur',
                'subcategory' => 'Jalan',
                'days_ago' => 12,
            ],
            [
                'code' => "PDL-{$year}-DEMO02",
                'title' => 'Lampu penerangan jalan tidak menyala',
                'description' => 'Lampu di pertigaan utama tidak menyala selama beberapa malam sehingga jalan gelap saat dilalui warga.',
                'note' => 'Perangkat pengganti sudah disiapkan dan pemasangan dijadwalkan bersama petugas lapangan.',
                'status' => ReportStatus::InProgress,
                'verification' => VerificationStatus::Verified,
                'dusun' => 'Bangkal',
                'category' => 'Fasilitas Desa',
                'subcategory' => 'Lampu dan Penerangan Jalan',
                'days_ago' => 7,
            ],
            [
                'code' => "PDL-{$year}-DEMO03",
                'title' => 'Drainase tersumbat setelah hujan',
                'description' => 'Saluran air di sisi jalan dipenuhi endapan dan sampah sehingga air meluap ketika hujan deras.',
                'note' => 'Laporan telah diverifikasi dan menunggu jadwal pemeriksaan lapangan.',
                'status' => ReportStatus::NotStarted,
                'verification' => VerificationStatus::Verified,
                'dusun' => 'Asam Batur',
                'category' => 'Lingkungan',
                'subcategory' => 'Drainase',
                'days_ago' => 3,
            ],
        ];

        return collect($definitions)->map(function (array $definition, int $index) use ($references): Report {
            $dusun = $references['dusuns']->firstWhere('name', $definition['dusun']);
            $category = $references['categories']->firstWhere('name', $definition['category']);
            $subcategory = $category->subcategories->firstWhere('name', $definition['subcategory']);
            $submittedAt = now()->subDays($definition['days_ago'])->startOfHour();

            $report = new Report([
                'report_code' => $definition['code'],
                'dusun_id' => $dusun->id,
                'subcategory_id' => $subcategory->id,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'location_text' => 'Lokasi contoh untuk pratinjau',
                'citizen_priority' => 'important',
                'verification_status' => $definition['verification']->value,
                'status' => $definition['status']->value,
                'current_public_note' => $definition['note'],
                'submitted_at' => $submittedAt,
            ]);
            $report->forceFill(['id' => 4001 + $index]);
            $report->setRelation('dusun', $dusun);
            $report->setRelation('subcategory', $subcategory);
            $report->setRelation('media', collect());
            $report->setRelation('histories', $this->demoHistories($report, $submittedAt));

            return $report;
        });
    }

    /** @return Collection<int, ReportStatusHistory> */
    private function demoHistories(Report $report, CarbonInterface $submittedAt): Collection
    {
        $steps = [
            [ReportStatus::NotStarted, 'Laporan diterima dan telah diverifikasi.', $submittedAt],
        ];

        if ($report->status === ReportStatus::InProgress || $report->status === ReportStatus::Completed) {
            $steps[] = [ReportStatus::InProgress, 'Petugas mulai menindaklanjuti lokasi laporan.', $submittedAt->copy()->addDays(2)];
        }

        if ($report->status === ReportStatus::Completed) {
            $steps[] = [ReportStatus::Completed, 'Penanganan selesai dan hasilnya telah diperiksa.', $submittedAt->copy()->addDays(6)];
        }

        return collect($steps)->map(function (array $step, int $index) use ($report): ReportStatusHistory {
            $history = new ReportStatusHistory([
                'report_id' => $report->id,
                'verification_status' => VerificationStatus::Verified->value,
                'status' => $step[0]->value,
                'public_note' => $step[1],
            ]);
            $history->forceFill([
                'id' => ($report->id * 10) + $index,
                'created_at' => $step[2],
                'updated_at' => $step[2],
            ]);

            return $history;
        });
    }

    private function logFallback(string $area, QueryException $exception): void
    {
        Log::notice('Data demo digunakan karena data utama belum dapat dibaca.', [
            'area' => $area,
            'exception' => $exception,
        ]);
    }
}
