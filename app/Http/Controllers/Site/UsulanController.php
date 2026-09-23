<?php

namespace App\Http\Controllers\Site;

use App\Enums\JenisUsulan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Site\KirimUsulanRequest;
use App\Models\Usulan;
use App\Services\Site\SiteDataService;
use App\Services\Usulan\LayananKirimUsulan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class UsulanController extends Controller
{
    public function buat(SiteDataService $siteData): View
    {
        return view('site.usulan.buat', [
            'dusuns' => $siteData->dusunUntukUsulan(),
            'jenisUsulan' => JenisUsulan::cases(),
            'tokenKirim' => old('token_kirim', Str::uuid()->toString()),
            'usingDemoData' => ! $siteData->bisaMenerimaUsulan(),
        ]);
    }

    public function simpan(KirimUsulanRequest $request, LayananKirimUsulan $layanan): RedirectResponse
    {
        $usulan = $layanan->kirim($request->validated());

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
