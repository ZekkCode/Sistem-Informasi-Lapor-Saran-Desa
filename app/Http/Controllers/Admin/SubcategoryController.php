<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use App\Services\Admin\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubcategoryController extends Controller
{
    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $this->validated($request);
        $subcategory = Subcategory::query()->create($data);
        $audit->record('master.subcategory_created', $subcategory, $subcategory->only(['category_id', 'name', 'sort_order', 'is_active']));

        return back()->with('status', 'Jenis masalah berhasil ditambahkan.');
    }

    public function update(Request $request, Subcategory $subcategory, AuditService $audit): RedirectResponse
    {
        $data = $this->validated($request, $subcategory);
        $subcategory->update($data);
        $audit->record('master.subcategory_updated', $subcategory, $subcategory->only(['category_id', 'name', 'sort_order', 'is_active']));

        return back()->with('status', 'Jenis masalah berhasil diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Subcategory $subcategory = null): array
    {
        $request->merge(['slug' => Str::slug((string) $request->input('name'))]);

        return $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:120'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'slug' => [
                'required',
                'string',
                'max:140',
                Rule::unique('subcategories', 'slug')
                    ->where(fn ($query) => $query->where('category_id', $request->integer('category_id')))
                    ->ignore($subcategory),
            ],
        ], [], ['category_id' => 'kategori']);
    }
}
