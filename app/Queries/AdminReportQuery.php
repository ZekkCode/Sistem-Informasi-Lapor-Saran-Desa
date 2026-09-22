<?php

namespace App\Queries;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

class AdminReportQuery
{
    /** @param array<string, mixed> $filters */
    public function build(array $filters): Builder
    {
        return Report::query()
            ->with(['dusun:id,name', 'subcategory:id,category_id,name', 'subcategory.category:id,name'])
            ->when($filters['q'] ?? null, function (Builder $query, string $keyword) {
                $query->where(function (Builder $query) use ($keyword) {
                    $query->where('report_code', 'like', "%{$keyword}%")
                        ->orWhere('title', 'like', "%{$keyword}%")
                        ->orWhere('reporter_name', 'like', "%{$keyword}%")
                        ->orWhere('reporter_phone', 'like', "%{$keyword}%");
                });
            })
            ->when($filters['dusun_id'] ?? null, fn (Builder $query, $value) => $query->where('dusun_id', $value))
            ->when($filters['category_id'] ?? null, fn (Builder $query, $value) => $query->whereHas(
                'subcategory',
                fn (Builder $query) => $query->where('category_id', $value),
            ))
            ->when($filters['verification_status'] ?? null, fn (Builder $query, $value) => $query->where('verification_status', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, $value) => $query->where('status', $value))
            ->when($filters['admin_priority'] ?? null, fn (Builder $query, $value) => $query->where('admin_priority', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, $value) => $query->whereDate('submitted_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, $value) => $query->whereDate('submitted_at', '<=', $value))
            ->latest('submitted_at');
    }
}
