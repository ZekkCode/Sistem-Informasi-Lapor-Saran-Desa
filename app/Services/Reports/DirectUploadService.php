<?php

namespace App\Services\Reports;

use App\Enums\ReportMediaType;
use App\Models\Report;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;
use RuntimeException;
use Throwable;

class DirectUploadService
{
    /**
     * @param  array<int, array{name: string, type: string, size: int}>  $files
     * @return array<int, array{name: string, url: string, headers: array<string, string>, token: string}>
     */
    public function authorizeUploads(array $files, ?string $purpose = null, ?int $reportId = null): array
    {
        $diskName = $this->diskName();
        $disk = Storage::disk($diskName);
        $expiration = now()->addMinutes((int) config('report-media.direct_upload.expires_after_minutes', 15));

        return collect($files)->map(function (array $file) use ($disk, $diskName, $expiration, $purpose, $reportId): array {
            $mimeType = (string) $file['type'];
            $path = 'temporary/report-media/'
                .now()->format('Y/m/d').'/'
                .Str::ulid().'.'.$this->extensionFor($mimeType);
            $signed = $disk->temporaryUploadUrl($path, $expiration, [
                'ContentType' => $mimeType,
            ]);

            return [
                'name' => (string) $file['name'],
                'url' => (string) $signed['url'],
                'headers' => ['Content-Type' => $mimeType],
                'token' => Crypt::encryptString(json_encode([
                    'version' => 1,
                    'disk' => $diskName,
                    'path' => $path,
                    'mime_type' => $mimeType,
                    'file_size' => (int) $file['size'],
                    'expires_at' => $expiration->getTimestamp(),
                    'purpose' => $purpose,
                    'report_id' => $reportId,
                ], JSON_THROW_ON_ERROR)),
            ];
        })->all();
    }

    /**
     * @param  array<int, string>  $tokens
     */
    public function attachUploadedEvidence(
        Report $report,
        array $tokens,
        ReportMediaType $mediaType = ReportMediaType::BeforePhoto,
        ?int $uploadedBy = null,
    ): void {
        $manifests = collect($tokens)->map(fn (string $token): array => $this->decodeToken($token));

        if ($manifests->pluck('path')->duplicates()->isNotEmpty()) {
            $this->invalidUpload('Foto yang sama tidak dapat digunakan dua kali.');
        }

        $diskName = $this->diskName();
        $disk = Storage::disk($diskName);
        $storedPaths = [];

        try {
            foreach ($manifests as $manifest) {
                if ($mediaType === ReportMediaType::AfterPhoto && (
                    ($manifest['purpose'] ?? null) !== 'after_photo'
                    || (int) ($manifest['report_id'] ?? 0) !== (int) $report->getKey()
                )) {
                    $this->invalidUpload('Izin unggah foto tidak sesuai dengan laporan ini. Pilih ulang foto.');
                }

                $this->verifyManifest($disk, $diskName, $manifest);

                $folder = $mediaType === ReportMediaType::AfterPhoto ? 'after' : 'before';
                $finalPath = "reports/{$report->report_code}/{$folder}/"
                    .Str::ulid().'.'.$this->extensionFor($manifest['mime_type']);

                if (! $disk->move($manifest['path'], $finalPath)) {
                    throw new RuntimeException('Foto bukti tidak dapat dipindahkan ke penyimpanan laporan.');
                }

                $storedPaths[] = $finalPath;

                $report->media()->create([
                    'media_type' => $mediaType,
                    'storage_disk' => $diskName,
                    'file_path' => $finalPath,
                    'mime_type' => $manifest['mime_type'],
                    'file_size' => $manifest['file_size'],
                    'uploaded_by' => $uploadedBy,
                ]);
            }
        } catch (Throwable $exception) {
            $disk->delete($storedPaths);

            throw $exception;
        }
    }

    /**
     * @return array{version: int, disk: string, path: string, mime_type: string, file_size: int, expires_at: int}
     */
    private function decodeToken(string $token): array
    {
        try {
            $manifest = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|JsonException) {
            $this->invalidUpload('Izin unggah foto tidak valid. Pilih ulang foto lalu kirim kembali.');
        }

        if (! is_array($manifest)) {
            $this->invalidUpload('Data unggahan foto tidak valid.');
        }

        return $manifest;
    }

    /**
     * @param  array<string, mixed>  $manifest
     */
    private function verifyManifest(Filesystem $disk, string $diskName, array $manifest): void
    {
        $allowedMimeTypes = config('report-media.direct_upload.allowed_mime_types', []);
        $maxSize = (int) config('report-media.direct_upload.max_file_size', 5 * 1024 * 1024);
        $path = (string) ($manifest['path'] ?? '');
        $mimeType = (string) ($manifest['mime_type'] ?? '');
        $expectedSize = (int) ($manifest['file_size'] ?? 0);

        if (
            ($manifest['version'] ?? null) !== 1
            || ($manifest['disk'] ?? null) !== $diskName
            || ! str_starts_with($path, 'temporary/report-media/')
            || ! in_array($mimeType, $allowedMimeTypes, true)
            || $expectedSize < 1
            || $expectedSize > $maxSize
            || (int) ($manifest['expires_at'] ?? 0) < now()->getTimestamp()
        ) {
            $this->invalidUpload('Izin unggah foto sudah tidak berlaku. Pilih ulang foto lalu kirim kembali.');
        }

        if (! $disk->exists($path)) {
            $this->invalidUpload('Salah satu foto belum selesai diunggah. Coba kirim kembali.');
        }

        $actualSize = $disk->size($path);

        if ($actualSize !== $expectedSize || $actualSize > $maxSize) {
            $this->invalidUpload('Ukuran foto yang diterima tidak sesuai. Pilih ulang foto.');
        }

        $stream = $disk->readStream($path);
        $signature = is_resource($stream) ? fread($stream, 32) : false;

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (! is_string($signature) || ! $this->matchesImageSignature($mimeType, $signature)) {
            $this->invalidUpload('File yang diunggah bukan foto JPG, PNG, atau WEBP yang valid.');
        }
    }

    private function matchesImageSignature(string $mimeType, string $signature): bool
    {
        return match ($mimeType) {
            'image/jpeg' => str_starts_with($signature, "\xFF\xD8\xFF"),
            'image/png' => str_starts_with($signature, "\x89PNG\r\n\x1A\n"),
            'image/webp' => str_starts_with($signature, 'RIFF') && substr($signature, 8, 4) === 'WEBP',
            default => false,
        };
    }

    private function extensionFor(string $mimeType): string
    {
        return match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('Format foto tidak didukung.'),
        };
    }

    private function diskName(): string
    {
        return (string) config('report-media.direct_upload.disk', 's3');
    }

    private function invalidUpload(string $message): never
    {
        throw ValidationException::withMessages(['photos' => $message]);
    }
}
