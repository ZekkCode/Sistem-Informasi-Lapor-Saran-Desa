<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PerbaruiAlurUsulanRequest;
use App\Models\Usulan;
use App\Services\Usulan\LayananAlurUsulan;
use Illuminate\Http\RedirectResponse;

class AlurUsulanController extends Controller
{
    public function perbarui(
        PerbaruiAlurUsulanRequest $request,
        Usulan $usulan,
        LayananAlurUsulan $layanan,
    ): RedirectResponse {
        $layanan->perbarui(
            $usulan,
            $request->validated(),
            $request->user(),
            $request->file('bukti_photos', []),
        );

        return back()->with('status', 'Tindak lanjut usulan berhasil disimpan.');
    }
}
