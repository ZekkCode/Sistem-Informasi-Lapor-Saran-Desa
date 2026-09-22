<?php

namespace App\Http\Controllers\Site;

use App\Enums\CitizenPriority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreReportRequest;
use App\Models\QrSource;
use App\Models\Report;
use App\Services\Reports\ReportSubmissionService;
use App\Services\Site\SiteDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function create(Request $request, SiteDataService $siteData): View
    {
        $references = $siteData->referenceData();
        $qrSource = $siteData->findQrSource($request->string('qr')->upper()->toString());

        return view('site.reports.buat', [
            'dusuns' => $references['dusuns'],
            'categories' => $references['categories'],
            'priorities' => CitizenPriority::cases(),
            'qrSource' => $qrSource,
            'submissionToken' => old('submission_token', Str::uuid()->toString()),
            'usingDemoData' => $references['usingDemoData'],
            'directUpload' => [
                'enabled' => ! $references['usingDemoData'] && (bool) config('report-media.direct_upload.enabled'),
                'url' => route('reports.uploads.authorize'),
            ],
        ]);
    }

    public function store(StoreReportRequest $request, ReportSubmissionService $service): RedirectResponse
    {
        $data = $request->validated();

        if (filled($data['qr_code'] ?? null)) {
            $data['qr_source_id'] = QrSource::query()
                ->active()
                ->where('code', strtoupper($data['qr_code']))
                ->value('id');
        }

        $report = $service->submit(
            $data,
            $request->file('photos', []),
            $data['uploaded_media'] ?? [],
        );

        return redirect()->route('reports.success', $report->report_code);
    }

    public function success(string $reportCode): View
    {
        $report = Report::query()
            ->where('report_code', strtoupper($reportCode))
            ->firstOrFail(['id', 'report_code']);

        return view('site.reports.sukses', compact('report'));
    }
}
