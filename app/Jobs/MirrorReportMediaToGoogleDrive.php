<?php

namespace App\Jobs;

use App\Enums\MediaBackupStatus;
use App\Models\ReportMedia;
use App\Services\Storage\GoogleDriveBackup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class MirrorReportMediaToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 120;

    public function __construct(public readonly int $reportMediaId)
    {
        $this->onQueue((string) config('report-media.google_drive.queue', 'backups'));
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("google-drive-media-{$this->reportMediaId}"))
                ->releaseAfter(30)
                ->expireAfter(150),
        ];
    }

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(GoogleDriveBackup $drive): void
    {
        $media = ReportMedia::query()->find($this->reportMediaId);

        if (! $media || $media->google_drive_file_id || ! $drive->enabled()) {
            return;
        }

        $media->forceFill([
            'google_drive_status' => MediaBackupStatus::Syncing,
            'google_drive_attempts' => $media->google_drive_attempts + 1,
            'google_drive_error' => null,
        ])->save();

        try {
            $file = $drive->upload($media);

            $media->forceFill([
                'google_drive_status' => MediaBackupStatus::Synced,
                'google_drive_file_id' => $file['id'],
                'google_drive_synced_at' => now(),
                'google_drive_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $this->recordFailure($media, $exception);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $media = ReportMedia::query()->find($this->reportMediaId);

        if ($media && ! $media->google_drive_file_id) {
            $this->recordFailure($media, $exception);
        }
    }

    private function recordFailure(ReportMedia $media, ?Throwable $exception): void
    {
        $media->forceFill([
            'google_drive_status' => MediaBackupStatus::Failed,
            'google_drive_error' => Str::limit($exception?->getMessage() ?? 'Pencadangan gagal tanpa detail.', 1000),
        ])->save();
    }
}
