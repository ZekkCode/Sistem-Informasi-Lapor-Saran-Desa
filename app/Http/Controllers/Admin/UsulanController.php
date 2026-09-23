<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaringUsulanRequest;
use App\Models\Dusun;
use App\Models\Usulan;
use App\Queries\PencarianUsulanAdmin;
use Illuminate\Contracts\View\View;

class UsulanController extends Controller
{
    public function daftar(SaringUsulanRequest $request, PencarianUsulanAdmin $pencarian): View
    {
        return view('admin.usulan.index', [
            'daftarUsulan' => $pencarian->bangun($request->validated())->paginate(20)->withQueryString(),
            'dusuns' => Dusun::query()->orderBy('name')->get(['id', 'name']),
            'jenisUsulan' => JenisUsulan::cases(),
            'statuses' => StatusUsulan::cases(),
        ]);
    }

    public function detail(Usulan $usulan): View
    {
        $usulan->load([
            'dusun',
            'penindak:id,name',
            'riwayat' => fn ($query) => $query->with('pengubah:id,name')->reorder()->oldest(),
        ]);

        return view('admin.usulan.show', [
            'usulan' => $usulan,
            'statuses' => StatusUsulan::cases(),
        ]);
    }
}
