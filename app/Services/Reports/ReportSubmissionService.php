<?php

namespace App\Services\Reports;

use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Report;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ReportSubmissionService
{
    public function __construct(
        private readonly ReportCodeGenerator $codeGenerator,
        private readonly ReportMediaService $mediaService,
        private readonly DirectUploadService $directUploadService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     */
    public function submit(array $data, array $photos, array $uploadedMedia = []): Report
    {
        $submissionToken = (string) $data['submission_token'];
        $existingReport = Report::query()->where('submission_token', $submissionToken)->first();

        if ($existingReport) {
            return $existingReport;
        }

        $reportCode = $this->codeGenerator->generate();
        $disk = $uploadedMedia !== []
            ? (string) config('report-media.direct_upload.disk', 's3')
            : (string) config('report-media.disk', 'public');

        try {
            $report = DB::transaction(function () use ($data, $photos, $uploadedMedia, $reportCode) {
                $report = Report::query()->create([
                    ...Arr::only($data, [
                        'reporter_name',
                        'reporter_phone',
                        'submission_token',
                        'dusun_id',
                        'subcategory_id',
                        'qr_source_id',
                        'title',
                        'description',
                        'location_text',
                        'citizen_priority',
                    ]),
                    'report_code' => $reportCode,
                    'verification_status' => VerificationStatus::Pending,
                    'status' => ReportStatus::NotStarted,
                    'current_public_note' => 'Laporan diterima dan menunggu verifikasi Pemerintah Desa.',
                    'submitted_at' => now(),
                ]);

                if ($uploadedMedia !== []) {
                    $this->directUploadService->attachUploadedEvidence($report, $uploadedMedia);
                } else {
                    $this->mediaService->storeCitizenEvidence($report, $photos);
                }

                $report->histories()->create([
                    'verification_status' => VerificationStatus::Pending,
                    'status' => ReportStatus::NotStarted,
                    'public_note' => 'Laporan berhasil dikirim dan sedang menunggu verifikasi.',
                ]);

                return $report;
            });

        } catch (Throwable $exception) {
            Storage::disk($disk)->deleteDirectory("reports/{$reportCode}");

            try {
                $existingReport = Report::query()->where('submission_token', $submissionToken)->first();
            } catch (Throwable) {
                $existingReport = null;
            }

            if ($existingReport) {
                return $existingReport;
            }

            throw $exception;
        }

        try {
            $this->mediaService->queueGoogleDriveBackup($report);
        } catch (Throwable $exception) {
            Log::warning('Laporan tersimpan, tetapi antrean cadangan Google Drive gagal dibuat.', [
                'report_id' => $report->getKey(),
                'exception' => $exception,
            ]);
        }

        return $report;
    }
}
