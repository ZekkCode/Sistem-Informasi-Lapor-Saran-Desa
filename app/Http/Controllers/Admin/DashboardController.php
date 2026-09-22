<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $total = Report::query()->count();
        $metrics = [
            'total' => $total,
            'pending' => Report::query()->where('verification_status', VerificationStatus::Pending->value)->count(),
            'not_started' => Report::query()->where('verification_status', VerificationStatus::Verified->value)->where('status', ReportStatus::NotStarted->value)->count(),
            'in_progress' => Report::query()->where('verification_status', VerificationStatus::Verified->value)->where('status', ReportStatus::InProgress->value)->count(),
            'completed' => Report::query()->where('verification_status', VerificationStatus::Verified->value)->where('status', ReportStatus::Completed->value)->count(),
        ];
        $metrics['completion_rate'] = $total > 0 ? round(($metrics['completed'] / $total) * 100, 1) : 0;

        $byCategory = Report::query()
            ->join('subcategories', 'reports.subcategory_id', '=', 'subcategories.id')
            ->join('categories', 'subcategories.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('count(reports.id) as aggregate'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('aggregate')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'byCategory' => $byCategory,
            'recentReports' => Report::query()
                ->with(['dusun:id,name', 'subcategory.category:id,name'])
                ->latest('submitted_at')
                ->limit(6)
                ->get(),
            'priorityReports' => Report::query()
                ->with(['dusun:id,name'])
                ->whereIn('admin_priority', [AdminPriority::Urgent->value, AdminPriority::High->value])
                ->where('status', '!=', ReportStatus::Completed->value)
                ->latest('submitted_at')
                ->limit(5)
                ->get(),
        ]);
    }
}
