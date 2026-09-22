<?php

namespace App\Http\Controllers\Site;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicReportController extends Controller
{
    public function index(Request $request, SiteDataService $siteData): View
    {
        $references = $siteData->publicReferenceData();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'dusun' => ['nullable', 'integer', Rule::in($references['dusuns']->pluck('id')->all())],
            'category' => ['nullable', 'integer', Rule::in($references['categories']->pluck('id')->all())],
            'status' => ['nullable', Rule::enum(ReportStatus::class)],
        ]);

        return view('site.reports.daftar', [
            'reports' => $siteData->paginatePublicReports($filters, $request),
            'dusuns' => $references['dusuns'],
            'categories' => $references['categories'],
            'usingDemoData' => $references['usingDemoData'],
        ]);
    }

    public function show(string $reportCode, SiteDataService $siteData): View
    {
        $result = $siteData->findPublicReport(strtoupper($reportCode));
        abort_if($result['report'] === null, 404);

        return view('site.reports.detail', [
            'report' => $result['report'],
            'usingDemoData' => $result['usingDemoData'],
        ]);
    }
}
