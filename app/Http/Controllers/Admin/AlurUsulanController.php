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
        $layanan->perbarui($usulan, $request->validated(), $request->user());

        return back()->with('status', 'Perubahan usulan tersimpan dan riwayat telah diperbarui.');
    }
}
