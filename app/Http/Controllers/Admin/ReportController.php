<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterReportsRequest;
use App\Models\Category;
use App\Models\Dusun;
use App\Models\Report;
use App\Queries\AdminReportQuery;
use App\Services\Admin\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function index(FilterReportsRequest $request, AdminReportQuery $reports): View
    {
        return view('admin.reports.index', [
            'reports' => $reports->build($request->validated())->paginate(20)->withQueryString(),
            'dusuns' => Dusun::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('sort_order')->get(['id', 'name']),
            'verificationStatuses' => VerificationStatus::cases(),
            'statuses' => ReportStatus::cases(),
            'priorities' => AdminPriority::cases(),
        ]);
    }

    public function show(Report $report): View
    {
        $report->load([
            'dusun',
            'subcategory.category',
            'qrSource',
            'verifier:id,name',
            'media.uploader:id,name',
            'histories' => fn ($query) => $query->with('changedBy:id,name')->reorder()->oldest(),
        ]);

        return view('admin.reports.show', [
            'report' => $report,
            'verificationStatuses' => VerificationStatus::cases(),
            'statuses' => ReportStatus::cases(),
            'priorities' => AdminPriority::cases(),
            'directUpload' => [
                'enabled' => (bool) config('report-media.direct_upload.enabled'),
                'url' => route('admin.reports.uploads.authorize', $report),
            ],
        ]);
    }

    public function destroy(Report $report, AuditService $audit): RedirectResponse
    {
        $code = $report->report_code;
        $audit->record('report.deleted', $report, ['report_code' => $code]);
        $report->delete();

        return redirect()->route('admin.reports.index')
            ->with('status', "Laporan {$code} berhasil dihapus.");
    }
}
