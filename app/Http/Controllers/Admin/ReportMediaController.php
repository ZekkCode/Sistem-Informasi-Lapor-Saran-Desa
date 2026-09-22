<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportMediaController extends Controller
{
    public function __invoke(ReportMedia $media): StreamedResponse
    {
        abort_unless(Storage::disk($media->storage_disk)->exists($media->file_path), 404);

        return Storage::disk($media->storage_disk)->response(
            $media->file_path,
            basename($media->file_path),
            ['Content-Type' => $media->mime_type],
            'inline',
        );
    }
}
