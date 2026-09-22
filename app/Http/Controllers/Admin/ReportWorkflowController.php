<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReportWorkflowRequest;
use App\Models\Report;
use App\Services\Reports\ReportWorkflowService;
use Illuminate\Http\RedirectResponse;

class ReportWorkflowController extends Controller
{
    public function update(
        UpdateReportWorkflowRequest $request,
        Report $report,
        ReportWorkflowService $workflow,
    ): RedirectResponse {
        $workflow->update(
            $report,
            $request->validated(),
            $request->file('after_photos', []),
            $request->validated('uploaded_media', []),
            $request->user(),
        );

        return back()->with('status', 'Perubahan laporan tersimpan dan riwayat telah diperbarui.');
    }
}
