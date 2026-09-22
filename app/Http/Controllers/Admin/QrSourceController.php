<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrSource;
use App\Services\Admin\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QrSourceController extends Controller
{
    public function store(Request $request, AuditService $audit): RedirectResponse
    {
        $source = QrSource::query()->create($this->validated($request));
        $audit->record('master.qr_source_created', $source, $source->only(['code', 'name', 'dusun_id', 'is_active']));

        return back()->with('status', 'Sumber QR berhasil ditambahkan.');
    }

    public function update(Request $request, QrSource $qrSource, AuditService $audit): RedirectResponse
    {
        $qrSource->update($this->validated($request, $qrSource));
        $audit->record('master.qr_source_updated', $qrSource, $qrSource->only(['code', 'name', 'dusun_id', 'is_active']));

        return back()->with('status', 'Sumber QR berhasil diperbarui.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?QrSource $source = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('qr_sources', 'code')->ignore($source)],
            'name' => ['required', 'string', 'max:120'],
            'dusun_id' => ['nullable', 'integer', Rule::exists('dusuns', 'id')],
            'placement' => ['nullable', 'string', 'max:200'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
