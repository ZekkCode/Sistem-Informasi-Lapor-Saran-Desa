<?php

namespace App\Models;

use App\Enums\JenisUsulan;
use App\Enums\StatusUsulan;
use Database\Factories\UsulanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usulan extends Model
{
    /** @use HasFactory<UsulanFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'usulan';

    protected $fillable = [
        'kode',
        'token_kirim',
        'versi_alur',
        'nama_pengusul',
        'telepon_pengusul',
        'dusun_id',
        'jenis',
        'judul',
        'isi',
        'status',
        'tampil_publik',
        'catatan_publik',
        'ditangani_oleh',
        'dipublikasikan_pada',
        'dikirim_pada',
    ];

    protected $hidden = ['nama_pengusul', 'telepon_pengusul', 'token_kirim'];

    protected function casts(): array
    {
        return [
            'jenis' => JenisUsulan::class,
            'status' => StatusUsulan::class,
            'versi_alur' => 'integer',
            'tampil_publik' => 'boolean',
            'dipublikasikan_pada' => 'datetime',
            'dikirim_pada' => 'datetime',
        ];
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function penindak(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatUsulan::class)->latest();
    }

    public function scopeTampilPublik(Builder $query): Builder
    {
        return $query->where('tampil_publik', true);
    }
}
