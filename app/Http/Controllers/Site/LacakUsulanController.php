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

            $with = ['dusun:id,name', 'media'];
            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_usulan')) {
                $with['riwayat'] = fn ($query) => $query
                    ->select(['id', 'usulan_id', 'status', 'catatan_publik', 'created_at'])
                    ->reorder()->oldest();
            }

            $usulan = Usulan::query()
                ->where('kode', $kode)
                ->with($with)
                ->first();

            if ($usulan && ! $usulan->relationLoaded('riwayat')) {
                $usulan->setRelation('riwayat', collect());
            }
        }

        return view('site.usulan.lacak', compact('usulan', 'kode'));
    }
}
