<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaringUsulanRequest;
use App\Models\Dusun;
use App\Models\Usulan;
use App\Queries\PencarianUsulanAdmin;
use App\Services\Admin\AuditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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
            'media',
            'penindak:id,name',
            'riwayat' => fn ($query) => $query->with('pengubah:id,name')->reorder()->oldest(),
        ]);

        return view('admin.usulan.show', [
            'usulan' => $usulan,
            'statuses' => StatusUsulan::cases(),
        ]);
    }

    public function hapus(Usulan $usulan, AuditService $audit): RedirectResponse
    {
        $kode = $usulan->kode;
        $audit->record('usulan.deleted', $usulan, ['kode' => $kode]);
        $usulan->delete();

        return redirect()->route('admin.usulan.index')
            ->with('status', "Usulan {$kode} berhasil dihapus.");
    }
}
