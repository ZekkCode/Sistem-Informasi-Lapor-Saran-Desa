<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\AuthorizeReportUploadsRequest;
use App\Models\Report;
use App\Services\Reports\DirectUploadService;
use Illuminate\Http\JsonResponse;

class ReportUploadController extends Controller
{
    public function __invoke(
        AuthorizeReportUploadsRequest $request,
        Report $report,
        DirectUploadService $uploads,
    ): JsonResponse {
        abort_unless(config('report-media.direct_upload.enabled'), 404);

        return response()->json([
            'uploads' => $uploads->authorizeUploads(
                $request->validated('files'),
                purpose: 'after_photo',
                reportId: $report->getKey(),
            ),
        ]);
    }
}
