<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsulanMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class UsulanMediaController extends Controller
{
    public function __invoke(UsulanMedia $media): StreamedResponse
    {
        try {
            abort_unless(Storage::disk($media->storage_disk)->exists($media->file_path), 404);

            return Storage::disk($media->storage_disk)->response(
                $media->file_path,
                basename($media->file_path),
                ['Content-Type' => $media->mime_type],
                'inline',
            );
        } catch (Throwable) {
            abort(404, 'Berkas foto usulan tidak ditemukan di penyimpanan.');
        }
    }
}
