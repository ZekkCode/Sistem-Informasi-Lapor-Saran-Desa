<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\Report;
use App\Models\Usulan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->canAccessReports()) {
            return redirect()->route('admin.usulan.index');
        }
        $total = Report::query()->count();
        $metrics = [
            'total' => $total,
            'pending' => Report::query()->where('verification_status', VerificationStatus::Pending->value)->count(),
            'not_started' => Report::query()->where('verification_status', VerificationStatus::Verified->value)->where('status', ReportStatus::NotStarted->value)->count(),
            'in_progress' => Report::query()->where('verification_status', VerificationStatus::Verified->value)->where('status', ReportStatus::InProgress->value)->count(),
            'completed' => Report::query()->where('verification_status', VerificationStatus::Verified->value)->where('status', ReportStatus::Completed->value)->count(),
        ];
        $metrics['completion_rate'] = $total > 0 ? round(($metrics['completed'] / $total) * 100, 1) : 0;

        $trendLabels = [];
        $trendReports = [];
        $trendUsulan = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $trendLabels[] = $day->translatedFormat('d M');
            $trendReports[] = Report::query()->whereDate('submitted_at', $day->toDateString())->count();
            $trendUsulan[] = Usulan::query()->whereDate('dikirim_pada', $day->toDateString())->count();
        }

        $trendChart = [
            'labels' => $trendLabels,
            'reports' => $trendReports,
            'usulan' => $trendUsulan,
        ];

        $statusChart = [
            'labels' => ['Menunggu Verifikasi', 'Belum Ditangani', 'Sedang Diproses', 'Selesai'],
            'values' => [
                $metrics['pending'],
                $metrics['not_started'],
                $metrics['in_progress'],
                $metrics['completed'],
            ],
        ];

        $dusunCounts = Dusun::query()
            ->active()
            ->withCount('reports')
            ->orderBy('name')
            ->get(['id', 'name']);

        $dusunChart = [
            'labels' => $dusunCounts->pluck('name')->all(),
            'values' => $dusunCounts->pluck('reports_count')->all(),
        ];

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
            'trendChart' => $trendChart,
            'statusChart' => $statusChart,
            'dusunChart' => $dusunChart,
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
