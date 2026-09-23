<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Usulan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LacakUsulanController extends Controller
{
    public function __invoke(Request $request): View
    {
        $usulan = null;
        $kode = strtoupper(trim($request->string('kode')->toString()));

        if ($kode !== '') {
            Validator::make(
                ['kode' => $kode],
                ['kode' => ['required', 'regex:/^USL-\d{4}-[A-Z0-9]{6}$/']],
                ['kode.regex' => 'Format nomor usulan belum sesuai.'],
            )->validate();

            $usulan = Usulan::query()
                ->where('kode', $kode)
                ->with(['dusun:id,name', 'riwayat' => fn ($query) => $query
                    ->select(['id', 'usulan_id', 'status', 'catatan_publik', 'created_at'])
                    ->reorder()->oldest()])
                ->first();
        }

        return view('site.usulan.lacak', compact('usulan', 'kode'));
    }
}
