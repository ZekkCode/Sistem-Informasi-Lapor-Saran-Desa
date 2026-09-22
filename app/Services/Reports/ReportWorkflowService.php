<?php

namespace App\Services\Reports;

use App\Enums\ReportMediaType;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Models\Report;
use App\Models\User;
use App\Services\Admin\AuditService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReportWorkflowService
{
    public function __construct(
        private readonly ReportMediaService $mediaService,
        private readonly DirectUploadService $directUploadService,
        private readonly AuditService $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $photos
     * @param  array<int, string>  $uploadedMedia
     */
    public function update(Report $report, array $data, array $photos, array $uploadedMedia, User $user): Report
    {
        $before = Arr::only($report->getAttributes(), [
            'verification_status',
            'status',
            'admin_priority',
            'current_public_note',
        ]);

        DB::transaction(function () use ($report, $data, $photos, $uploadedMedia, $user): void {
            $report = Report::query()->lockForUpdate()->findOrFail($report->getKey());

            if ($report->workflow_version !== (int) $data['workflow_version']) {
                throw ValidationException::withMessages([
                    'workflow' => 'Petugas lain sudah memperbarui laporan ini. Muat ulang halaman, periksa perubahan terbaru, lalu simpan kembali.',
                ]);
            }

            $verification = VerificationStatus::from($data['verification_status']);
            $status = ReportStatus::from($data['status']);
            $publicNote = $data['public_note'] ?? null;
            $internalNote = $data['internal_note'] ?? null;

            $report->forceFill([
                'verification_status' => $verification,
                'status' => $status,
                'admin_priority' => $data['admin_priority'],
                'current_public_note' => $publicNote ?: null,
                'verified_by' => $verification === VerificationStatus::Pending ? null : $user->getKey(),
                'verified_at' => $verification === VerificationStatus::Pending ? null : ($report->verified_at ?? now()),
                'completed_at' => $status === ReportStatus::Completed ? ($report->completed_at ?? now()) : null,
                'workflow_version' => $report->workflow_version + 1,
            ])->save();

            $report->histories()->create([
                'verification_status' => $verification,
                'status' => $status,
                'public_note' => $publicNote ?: null,
                'internal_note' => $internalNote ?: null,
                'changed_by' => $user->getKey(),
            ]);

            if ($uploadedMedia !== []) {
                $this->directUploadService->attachUploadedEvidence(
                    $report,
                    $uploadedMedia,
                    ReportMediaType::AfterPhoto,
                    $user->getKey(),
                );
            } elseif ($photos !== []) {
                $this->mediaService->storeAdminEvidence($report, $photos, $user->getKey());
            }
        });

        $report->refresh();

        $this->audit->record('report.workflow_updated', $report, [
            'before' => $before,
            'after' => Arr::only($report->getAttributes(), [
                'verification_status',
                'status',
                'admin_priority',
                'current_public_note',
            ]),
        ]);

        try {
            $this->mediaService->queueGoogleDriveBackup($report->load('media'));
        } catch (Throwable $exception) {
            Log::warning('Perubahan laporan tersimpan, tetapi antrean cadangan Google Drive gagal dibuat.', [
                'report_id' => $report->getKey(),
                'exception' => $exception,
            ]);
        }

        return $report;
    }
}
