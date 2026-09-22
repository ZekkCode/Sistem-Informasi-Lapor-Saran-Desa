<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Admin\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $this->validated($request);
        $category = Category::query()->create($data);
        $audit->record('master.category_created', $category, $category->only(['name', 'sort_order', 'is_active']));

        return back()->with('status', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category, AuditService $audit): RedirectResponse
    {
        $data = $this->validated($request, $category);
        $category->update($data);
        $audit->record('master.category_updated', $category, $category->only(['name', 'sort_order', 'is_active']));

        return back()->with('status', 'Kategori berhasil diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Category $category = null): array
    {
        $request->merge(['slug' => Str::slug((string) $request->input('name'))]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', Rule::unique('categories', 'slug')->ignore($category)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
