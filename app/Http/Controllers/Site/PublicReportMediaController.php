<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ReportMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class PublicReportMediaController extends Controller
{
    public function __invoke(ReportMedia $media): StreamedResponse
    {
        abort_unless($media->report !== null, 404);

        try {
            abort_unless(Storage::disk($media->storage_disk)->exists($media->file_path), 404);

            return Storage::disk($media->storage_disk)->response(
                $media->file_path,
                basename($media->file_path),
                [
                    'Content-Type' => $media->mime_type,
                    'Cache-Control' => 'public, max-age=86400',
                ],
                'inline',
            );
        } catch (Throwable) {
            abort(404, 'Berkas foto tidak ditemukan di penyimpanan.');
        }
    }
}
