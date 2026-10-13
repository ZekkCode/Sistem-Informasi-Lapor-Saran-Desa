<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisFormulir;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AturKunciAdminRequest;
use App\Http\Requests\Admin\AturKunciPengembangRequest;
use App\Services\Formulir\LayananKunciFormulir;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class KunciFormulirController extends Controller
{
    public function daftar(LayananKunciFormulir $layanan): View
    {
        return view('admin.formulir.index', [
            'statuses' => collect(JenisFormulir::cases())
                ->map(fn (JenisFormulir $formulir) => $layanan->status($formulir)),
        ]);
    }

    public function aturKunciAdmin(
        AturKunciAdminRequest $request,
        JenisFormulir $formulir,
        LayananKunciFormulir $layanan,
    ): RedirectResponse {
        $berubah = $layanan->aturKunciAdmin(
            $formulir,
            $request->boolean('terkunci'),
            $request->validated('catatan'),
            $request->user(),
        );

        $kata = $formulir->kataBenda();
        $status = $layanan->status($formulir);

        $pesan = match (true) {
            ! $berubah => "Kunci formulir {$kata} tidak berubah.",
            $status->kunciAdmin => "Formulir {$kata} ditutup. Warga melihat pemberitahuan saat membukanya.",
            $status->kunciPengembang => "Kunci admin dimatikan. Formulir {$kata} tetap tertutup karena kunci pengembang aktif.",
            default => "Formulir {$kata} dibuka kembali.",
        };

        return redirect()->route('admin.formulir.index')->with('status', $pesan);
    }

    public function aturKunciPengembang(
        AturKunciPengembangRequest $request,
        JenisFormulir $formulir,
        LayananKunciFormulir $layanan,
    ): RedirectResponse {
        $berubah = $layanan->aturKunciPengembang(
            $formulir,
            $request->boolean('terkunci'),
            $request->user(),
        );

        $kata = $formulir->kataBenda();
        $status = $layanan->status($formulir);

        $pesan = match (true) {
            ! $berubah => "Kunci formulir {$kata} tidak berubah.",
            $status->kunciPengembang => "Kunci pengembang aktif. Formulir {$kata} tertutup untuk pemeliharaan.",
            $status->kunciAdmin => "Kunci pengembang dimatikan. Formulir {$kata} masih ditutup admin.",
            default => "Kunci pengembang dimatikan. Formulir {$kata} dibuka kembali.",
        };

        return redirect()->route('admin.formulir.index')->with('status', $pesan);
    }
}
