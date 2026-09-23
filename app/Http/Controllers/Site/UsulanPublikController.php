<?php

namespace App\Http\Controllers\Site;

use App\Enums\JenisUsulan;
use App\Http\Controllers\Controller;
use App\Services\Site\DataUsulanService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsulanPublikController extends Controller
{
    public function daftar(Request $request, DataUsulanService $dataUsulan): View
    {
        $dusuns = $dataUsulan->dusunUntukSaringan();
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'dusun' => ['nullable', 'integer', Rule::in($dusuns->pluck('id')->all())],
            'jenis' => ['nullable', Rule::enum(JenisUsulan::class)],
        ]);

        return view('site.usulan.daftar', [
            'daftarUsulan' => $dataUsulan->paginasiUsulanPublik($filters, $request),
            'dusuns' => $dusuns,
            'jenisUsulan' => JenisUsulan::cases(),
            'usingDemoData' => $dataUsulan->memakaiDemo(),
        ]);
    }

    public function detail(string $kode, DataUsulanService $dataUsulan): View
    {
        $hasil = $dataUsulan->cariUsulanPublik(strtoupper($kode));
        abort_if($hasil['usulan'] === null, 404);

        return view('site.usulan.detail', [
            'usulan' => $hasil['usulan'],
            'usingDemoData' => $hasil['memakaiDemo'],
        ]);
    }
}
