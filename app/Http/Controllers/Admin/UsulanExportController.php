<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaringUsulanRequest;
use App\Queries\PencarianUsulanAdmin;
use App\Services\Admin\AuditService;
use App\Services\Storage\GoogleDriveBackup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UsulanExportController extends Controller
{
    public function csv(SaringUsulanRequest $request, PencarianUsulanAdmin $usulanQuery): StreamedResponse
    {
        $query = $usulanQuery->bangun($request->validated())->with(['dusun']);
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
        SaringUsulanRequest $request,
        PencarianUsulanAdmin $usulanQuery,
        GoogleDriveBackup $drive,
        AuditService $audit,
    ): RedirectResponse {
        if (! $drive->enabled()) {
            return redirect()->back()
                ->with('error', 'Ekspor ke Google Drive belum diaktifkan di konfigurasi sistem.');
        }

        $query = $usulanQuery->bangun($request->validated())->with(['dusun']);
        $fileName = $this->buildFileName($request->validated('periode'));

        $stream = fopen('php://temp', 'r+');
        $this->writeCsvToStream($stream, $query);
        rewind($stream);
        $contents = (string) stream_get_contents($stream);
        fclose($stream);

        try {
            $result = $drive->uploadFile($fileName, $contents, 'text/csv');
            $audit->record('usulan.exported_drive', null, [
                'file_name' => $fileName,
                'drive_id' => $result['id'],
                'filters' => $request->validated(),
            ]);

            $message = "Rekap usulan ({$fileName}) berhasil disimpan ke Google Drive.";
            if (! empty($result['webViewLink'])) {
                $message .= ' <a href="' . e($result['webViewLink']) . '" target="_blank" rel="noopener" class="underline font-semibold ml-1">Buka di Drive</a>';
            }

            return redirect()->back()->with('status', $message);
        } catch (Throwable $e) {
            report($e);

            return redirect()->back()->with('error', 'Gagal mengekspor rekap usulan ke Google Drive: ' . $e->getMessage());
        }
    }

    public function print(SaringUsulanRequest $request, PencarianUsulanAdmin $usulanQuery): View
    {
        $items = $usulanQuery->bangun($request->validated())
            ->with(['dusun'])
            ->get();

        return view('admin.usulan.print', [
            'daftarUsulan' => $items,
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
            'Nomor usulan', 'Tanggal', 'Pengusul', 'WhatsApp', 'Dusun', 'Jenis usulan',
            'Judul', 'Isi usulan', 'Status', 'Tampil publik', 'Catatan publik',
        ], ';', '"', '');

        foreach ($query->cursor() as $usulan) {
            fputcsv($stream, array_map($this->csvCell(...), [
                $usulan->kode,
                $usulan->dikirim_pada->format('Y-m-d H:i:s'),
                $usulan->nama_pengusul ?: 'Anonim',
                $usulan->telepon_pengusul ?: '-',
                $usulan->dusun->name,
                $usulan->jenis->label(),
                $usulan->judul,
                $usulan->isi,
                $usulan->status->label(),
                $usulan->tampil_publik ? 'Ditampilkan' : 'Tersembunyi',
                $usulan->catatan_publik,
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

        return 'rekap-usulan-' . $periodeLabel . '-' . now()->format('Y-m-d-His') . '.csv';
    }

    private function csvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
