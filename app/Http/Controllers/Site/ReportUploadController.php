<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\AuthorizeReportUploadsRequest;
use App\Services\Reports\DirectUploadService;
use App\Services\Site\SiteDataService;
use Illuminate\Http\JsonResponse;

class ReportUploadController extends Controller
{
    public function __invoke(
        AuthorizeReportUploadsRequest $request,
        DirectUploadService $uploads,
        SiteDataService $siteData,
    ): JsonResponse {
        abort_unless(config('report-media.direct_upload.enabled'), 404);

        if (! $siteData->canAcceptReports()) {
            return response()->json([
                'message' => 'Pengiriman belum aktif karena database belum siap.',
            ], 503);
        }

        return response()->json([
            'uploads' => $uploads->authorizeUploads($request->validated('files')),
        ]);
    }
}
