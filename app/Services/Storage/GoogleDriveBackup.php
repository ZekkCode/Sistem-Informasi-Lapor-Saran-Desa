<?php

namespace App\Services\Storage;

use App\Models\ReportMedia;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleDriveBackup
{
    private const TOKEN_CACHE_KEY = 'google-drive:backup-access-token';

    /**
     * @return array{id: string, name: string|null, webViewLink: string|null}
     */
    public function upload(ReportMedia $media): array
    {
        $this->ensureConfigured();
        $media->loadMissing('report:id,report_code');

        if ($existing = $this->findExistingFile($media)) {
            return $existing;
        }

        $disk = $media->storage_disk ?: config('report-media.disk', 'public');
        $filesystem = Storage::disk($disk);

        if (! $filesystem->exists($media->file_path)) {
            throw new RuntimeException('Berkas utama tidak ditemukan untuk dicadangkan.');
        }

        $contents = $filesystem->get($media->file_path);
        $boundary = 'padelegan-'.Str::lower((string) Str::ulid());
        $metadata = json_encode([
            'name' => $this->fileName($media),
            'parents' => [(string) config('report-media.google_drive.folder_id')],
            'appProperties' => [
                'padelegan_media_id' => (string) $media->getKey(),
                'padelegan_report_code' => (string) $media->report->report_code,
            ],
        ], JSON_THROW_ON_ERROR);

        $body = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            .$metadata."\r\n"
            ."--{$boundary}\r\n"
            ."Content-Type: {$media->mime_type}\r\n\r\n"
            .$contents."\r\n"
            ."--{$boundary}--";

        $response = $this->sendWithFreshToken(fn (PendingRequest $request) => $request
            ->withQueryParameters([
                'uploadType' => 'multipart',
                'supportsAllDrives' => 'true',
                'fields' => 'id,name,webViewLink',
            ])
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post($this->uploadUrl('/files')));

        $response->throw();

        return $this->normaliseFile($response->json());
    }

    public function enabled(): bool
    {
        return (bool) config('report-media.google_drive.enabled', false);
    }

    /**
     * @return array{id: string, name: string|null, webViewLink: string|null}|null
     */
    private function findExistingFile(ReportMedia $media): ?array
    {
        $mediaId = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $media->getKey());
        $query = "trashed = false and appProperties has { key = 'padelegan_media_id' and value = '{$mediaId}' }";

        $response = $this->sendWithFreshToken(fn (PendingRequest $request) => $request
            ->get($this->apiUrl('/files'), [
                'q' => $query,
                'spaces' => 'drive',
                'pageSize' => 1,
                'includeItemsFromAllDrives' => 'true',
                'supportsAllDrives' => 'true',
                'fields' => 'files(id,name,webViewLink)',
            ]));

        $response->throw();
        $file = $response->json('files.0');

        return is_array($file) ? $this->normaliseFile($file) : null;
    }

    private function sendWithFreshToken(callable $callback): Response
    {
        $response = $callback($this->request($this->accessToken()));

        if ($response->unauthorized()) {
            Cache::forget(self::TOKEN_CACHE_KEY);
            $response = $callback($this->request($this->accessToken()));
        }

        return $response;
    }

    private function request(string $accessToken): PendingRequest
    {
        return Http::withToken($accessToken)
            ->acceptJson()
            ->timeout(45)
            ->connectTimeout(10);
    }

    private function accessToken(): string
    {
        if ($cached = Cache::get(self::TOKEN_CACHE_KEY)) {
            return (string) $cached;
        }

        $payload = array_filter([
            'client_id' => config('report-media.google_drive.client_id'),
            'client_secret' => config('report-media.google_drive.client_secret'),
            'refresh_token' => config('report-media.google_drive.refresh_token'),
            'grant_type' => 'refresh_token',
        ], fn ($value) => filled($value));

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->post((string) config('report-media.google_drive.token_url'), $payload);

        $response->throw();
        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Google Drive tidak mengembalikan access token.');
        }

        $seconds = max(60, (int) $response->json('expires_in', 3600) - 120);
        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addSeconds($seconds));

        return $token;
    }

    private function ensureConfigured(): void
    {
        if (! $this->enabled()) {
            throw new RuntimeException('Pencadangan Google Drive belum diaktifkan.');
        }

        $required = ['client_id', 'refresh_token', 'folder_id'];
        $missing = array_filter($required, fn (string $key) => blank(config("report-media.google_drive.{$key}")));

        if ($missing !== []) {
            throw new RuntimeException('Konfigurasi Google Drive belum lengkap: '.implode(', ', $missing).'.');
        }
    }

    private function fileName(ReportMedia $media): string
    {
        $extension = pathinfo($media->file_path, PATHINFO_EXTENSION) ?: 'bin';

        return Str::slug((string) $media->report->report_code).'-media-'.$media->getKey().'.'.$extension;
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('report-media.google_drive.api_url'), '/').$path;
    }

    private function uploadUrl(string $path): string
    {
        return rtrim((string) config('report-media.google_drive.upload_url'), '/').$path;
    }

    /**
     * @param  array<string, mixed>  $file
     * @return array{id: string, name: string|null, webViewLink: string|null}
     */
    private function normaliseFile(array $file): array
    {
        $id = $file['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new RuntimeException('Google Drive tidak mengembalikan ID berkas.');
        }

        return [
            'id' => $id,
            'name' => is_string($file['name'] ?? null) ? $file['name'] : null,
            'webViewLink' => is_string($file['webViewLink'] ?? null) ? $file['webViewLink'] : null,
        ];
    }
}
