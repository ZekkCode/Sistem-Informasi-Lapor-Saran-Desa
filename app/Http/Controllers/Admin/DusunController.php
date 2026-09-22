<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Services\Admin\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DusunController extends Controller
{
    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $dusun = Dusun::query()->create($this->validated($request));
        $audit->record('master.dusun_created', $dusun, $dusun->only(['code', 'name', 'is_active']));

        return back()->with('status', 'Dusun berhasil ditambahkan.');
    }

    public function update(Request $request, Dusun $dusun, AuditService $audit): RedirectResponse
    {
        $dusun->update($this->validated($request, $dusun));
        $audit->record('master.dusun_updated', $dusun, $dusun->only(['code', 'name', 'is_active']));

        return back()->with('status', 'Dusun berhasil diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Dusun $dusun = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('dusuns', 'code')->ignore($dusun)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
