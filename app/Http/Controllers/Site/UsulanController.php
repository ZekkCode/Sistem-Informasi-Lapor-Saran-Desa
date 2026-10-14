<?php

namespace App\Http\Controllers\Site;

use App\Enums\JenisFormulir;
use App\Enums\JenisUsulan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\KirimUsulanRequest;
use App\Models\Usulan;
use App\Services\Formulir\LayananKunciFormulir;
use App\Services\Site\SiteDataService;
use App\Services\Usulan\LayananKirimUsulan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class UsulanController extends Controller
{
    public function buat(SiteDataService $siteData, LayananKunciFormulir $kunciFormulir): View
    {
        $status = $kunciFormulir->status(JenisFormulir::Usulan);

        if (! $status->terbuka()) {
            return view('site.formulir.ditutup', ['status' => $status]);
        }

        $bisaTerima = $siteData->bisaMenerimaUsulan();

        return view('site.usulan.buat', [
            'dusuns' => $siteData->dusunUntukUsulan(),
            'jenisUsulan' => JenisUsulan::cases(),
            'tokenKirim' => old('token_kirim', Str::uuid()->toString()),
            'usingDemoData' => ! $bisaTerima,
            'directUpload' => [
                'enabled' => $bisaTerima && (bool) config('report-media.direct_upload.enabled'),
                'url' => route('reports.uploads.authorize'),
            ],
        ]);
    }

    public function simpan(KirimUsulanRequest $request, LayananKirimUsulan $layanan): RedirectResponse
    {
        $data = $request->validated();
        $usulan = $layanan->kirim(
            $data,
            $request->file('photos', []),
            $data['uploaded_media'] ?? [],
        );

        return redirect()->route('usulan.sukses', $usulan->kode);
    }

    public function sukses(string $kode): View
    {
        $usulan = Usulan::query()
            ->where('kode', strtoupper($kode))
            ->firstOrFail(['id', 'kode']);

        return view('site.usulan.sukses', compact('usulan'));
    }
}
