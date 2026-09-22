<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReportsRequest;
use App\Queries\AdminReportQuery;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function csv(FilterReportsRequest $request, AdminReportQuery $reports): StreamedResponse
    {
        $query = $reports->build($request->validated())->with(['dusun', 'subcategory.category']);

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Nomor laporan', 'Tanggal', 'Pelapor', 'WhatsApp', 'Dusun', 'Kategori', 'Jenis masalah',
                'Judul', 'Urgensi warga', 'Prioritas admin', 'Verifikasi', 'Status', 'Catatan publik',
            ], ';', '"', '');

            foreach ($query->cursor() as $report) {
                fputcsv($output, array_map($this->csvCell(...), [
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

            fclose($output);
        }, 'rekap-laporan-'.now()->format('Y-m-d-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

    private function csvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
