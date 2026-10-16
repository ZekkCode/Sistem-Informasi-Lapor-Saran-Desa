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
        $this->ensureUsulanTables();

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('usulan_media')) {
                $usulan->load('media');
            } else {
                $usulan->setRelation('media', collect());
            }
        } catch (\Throwable) {
            $usulan->setRelation('media', collect());
        }

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_usulan')) {
                $usulan->load(['riwayat' => fn ($query) => $query->with('pengubah:id,name')->reorder()->oldest()]);
            } else {
                $usulan->setRelation('riwayat', collect());
            }
        } catch (\Throwable) {
            $usulan->setRelation('riwayat', collect());
        }

        $usulan->load([
            'dusun',
            'penindak:id,name',
        ]);

        return view('admin.usulan.show', [
            'usulan' => $usulan,
            'statuses' => StatusUsulan::cases(),
        ]);
    }

    private function ensureUsulanTables(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('usulan_media')) {
                \Illuminate\Support\Facades\Schema::create('usulan_media', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->foreignId('usulan_id')->constrained('usulan')->cascadeOnDelete();
                    $table->string('storage_disk', 32)->default('public');
                    $table->string('file_path');
                    $table->string('mime_type', 100);
                    $table->unsignedBigInteger('file_size');
                    $table->string('caption')->nullable();
                    $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                    $table->timestamps();
                });
            }
        } catch (\Throwable) {
            // Abaikan kegagalan DDL jika pengguna database tidak memiliki izin
        }
    }

    public function hapus(Usulan $usulan, AuditService $audit): RedirectResponse
    {
        $kode = $usulan->kode;

        try {
            $audit->record('usulan.deleted', $usulan, ['kode' => $kode]);
        } catch (\Throwable) {
            // Jangan hentikan penghapusan jika audit gagal
        }

        $usulan->delete();

        return redirect()->route('admin.usulan.index')
            ->with('status', "Usulan {$kode} berhasil dihapus.");
    }
}
