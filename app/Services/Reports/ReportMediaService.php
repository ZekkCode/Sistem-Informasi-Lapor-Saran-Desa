<?php

namespace App\Services\Reports;

use App\Enums\MediaBackupStatus;
use App\Enums\ReportMediaType;
use App\Jobs\MirrorReportMediaToGoogleDrive;
use App\Models\Report;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ReportMediaService
{
    /**
     * @param  array<int, UploadedFile>  $photos
     */
    public function storeCitizenEvidence(Report $report, array $photos): void
    {
        $this->storeEvidence($report, $photos, ReportMediaType::BeforePhoto);
    }

    /** @param array<int, UploadedFile> $photos */
    public function storeAdminEvidence(Report $report, array $photos, int $uploadedBy): void
    {
        $this->storeEvidence($report, $photos, ReportMediaType::AfterPhoto, $uploadedBy);
    }

    /** @param array<int, UploadedFile> $photos */
    private function storeEvidence(
        Report $report,
        array $photos,
        ReportMediaType $mediaType,
        ?int $uploadedBy = null,
    ): void {
        $disk = (string) config('report-media.disk', 'public');
        $folder = $mediaType === ReportMediaType::AfterPhoto ? 'after' : 'before';
        $storedPaths = [];

        try {
            foreach ($photos as $photo) {
                $image = $this->createImage($photo);
                $image = $this->orientImage($image, $photo);
                $image = $this->resizeImage($image);

                ob_start();
                $encoded = imagewebp($image, null, 82);
                $contents = ob_get_clean();
                imagedestroy($image);

                if (! $encoded || ! is_string($contents)) {
                    throw new RuntimeException('Foto bukti tidak dapat dioptimalkan.');
                }

                $path = "reports/{$report->report_code}/{$folder}/".Str::ulid().'.webp';
                $stored = Storage::disk($disk)->put($path, $contents);

                if (! $stored) {
                    throw new RuntimeException('Foto bukti tidak dapat disimpan.');
                }

                $storedPaths[] = $path;
                $report->media()->create([
                    'media_type' => $mediaType,
                    'storage_disk' => $disk,
                    'file_path' => $path,
                    'mime_type' => 'image/webp',
                    'file_size' => strlen($contents),
                    'uploaded_by' => $uploadedBy,
                ]);
            }
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($storedPaths);

            throw $exception;
        }
    }

    public function queueGoogleDriveBackup(Report $report): void
    {
        if (! config('report-media.google_drive.enabled', false)) {
            return;
        }

        $report->loadMissing('media');

        foreach ($report->media->whereNull('google_drive_file_id') as $media) {
            $media->forceFill([
                'google_drive_status' => MediaBackupStatus::Pending,
                'google_drive_error' => null,
            ])->save();

            MirrorReportMediaToGoogleDrive::dispatchAfterResponse($media->getKey());
        }
    }

    private function createImage(UploadedFile $photo): \GdImage
    {
        $contents = file_get_contents($photo->getRealPath());
        $image = is_string($contents) ? imagecreatefromstring($contents) : false;

        if (! $image instanceof \GdImage) {
            throw new RuntimeException('Foto bukti tidak dapat dibaca.');
        }

        return $image;
    }

    private function orientImage(\GdImage $image, UploadedFile $photo): \GdImage
    {
        if ($photo->getMimeType() !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($photo->getRealPath());
        $angle = match ($exif['Orientation'] ?? null) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof \GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function resizeImage(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, 1920 / max($width, $height));

        if ($scale === 1) {
            return $image;
        }

        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($image);

        return $resized;
    }
}
