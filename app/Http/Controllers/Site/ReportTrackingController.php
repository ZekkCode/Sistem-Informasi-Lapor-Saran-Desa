<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\Site\SiteDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportTrackingController extends Controller
{
    public function __invoke(Request $request, SiteDataService $siteData): View
    {
        $report = null;
        $usingDemoData = $siteData->trackingUsesDemoData();
        $demoReportCode = $usingDemoData ? $siteData->demoTrackingCode() : null;
        $code = strtoupper(trim($request->string('code')->toString()));

        if ($code !== '') {
            Validator::make(
                ['code' => $code],
                ['code' => ['required', 'regex:/^PDL-\d{4}-[A-Z0-9]{6}$/']],
                ['code.regex' => 'Format nomor laporan belum sesuai.'],
            )->validate();

            $result = $siteData->findTrackedReport($code);
            $report = $result['report'];
            $usingDemoData = $result['usingDemoData'];
        }

        return view('site.reports.lacak', compact('report', 'code', 'usingDemoData', 'demoReportCode'));
    }
}
