<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReportsRequest;
use App\Queries\AdminReportQuery;
use App\Services\Admin\AuditService;
use App\Services\Storage\GoogleDriveBackup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ReportExportController extends Controller
{
    public function csv(FilterReportsRequest $request, AdminReportQuery $reports): StreamedResponse
    {
        $query = $reports->build($request->validated())->with(['dusun', 'subcategory.category']);
        $fileName = $this->buildFileName($request->validated('periode'));

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            $this->writeCsvToStream($output, $query);
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportDrive(
        FilterReportsRequest $request,
        AdminReportQuery $reports,
        GoogleDriveBackup $drive,
        AuditService $audit,
    ): RedirectResponse {
        if (! $drive->enabled()) {
            return redirect()->back()
                ->with('error', 'Ekspor ke Google Drive belum diaktifkan di konfigurasi sistem.');
        }

        $query = $reports->build($request->validated())->with(['dusun', 'subcategory.category']);
        $fileName = $this->buildFileName($request->validated('periode'));

        $stream = fopen('php://temp', 'r+');
        $this->writeCsvToStream($stream, $query);
        rewind($stream);
        $contents = (string) stream_get_contents($stream);
        fclose($stream);

        try {
            $result = $drive->uploadFile($fileName, $contents, 'text/csv');
            $audit->record('report.exported_drive', null, [
                'file_name' => $fileName,
                'drive_id' => $result['id'],
                'filters' => $request->validated(),
            ]);

            $message = "Rekap laporan ({$fileName}) berhasil disimpan ke Google Drive.";
            if (! empty($result['webViewLink'])) {
                $message .= ' <a href="' . e($result['webViewLink']) . '" target="_blank" rel="noopener" class="underline font-semibold ml-1">Buka di Drive</a>';
            }

            return redirect()->back()->with('status', $message);
        } catch (Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Gagal mengekspor rekap ke Google Drive: ' . $e->getMessage());
        }
    }

    public function print(FilterReportsRequest $request, AdminReportQuery $reports): View
    {
        $items = $reports->build($request->validated())
            ->with(['dusun', 'subcategory.category'])
            ->get();

        return view('admin.reports.print', [
            'reports' => $items,
            'filters' => $request->validated(),
            'generatedAt' => now(),
        ]);
    }

    /**
     * @param resource $stream
     */
    private function writeCsvToStream($stream, Builder $query): void
    {
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'Nomor laporan', 'Tanggal', 'Pelapor', 'WhatsApp', 'Dusun', 'Kategori', 'Jenis masalah',
            'Judul', 'Urgensi warga', 'Prioritas admin', 'Verifikasi', 'Status', 'Catatan publik',
        ], ';', '"', '');

        foreach ($query->cursor() as $report) {
            fputcsv($stream, array_map($this->csvCell(...), [
                $report->report_code,
                $report->submitted_at->format('Y-m-d H:i:s'),
                $report->reporter_name,
                $report->reporter_phone,
                $report->dusun->name,
                $report->subcategory->category->name,
                $report->subcategory->name,
                $report->title,
                $report->citizen_priority->label(),
                $report->admin_priority?->label(),
                $report->verification_status->label(),
                $report->status->label(),
                $report->current_public_note,
            ]), ';', '"', '');
        }
    }

    private function buildFileName(?string $periode): string
    {
        $periodeLabel = match ($periode) {
            'minggu' => 'mingguan-7hari',
            'bulan' => 'bulanan-30hari',
            'tahun' => 'tahunan-' . now()->format('Y'),
            default => 'semua',
        };

        return 'rekap-laporan-' . $periodeLabel . '-' . now()->format('Y-m-d-His') . '.csv';
    }

    private function csvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
