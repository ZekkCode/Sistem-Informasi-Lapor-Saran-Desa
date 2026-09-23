<?php

namespace App\Models;

use App\Enums\StatusUsulan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatUsulan extends Model
{
    use HasFactory;

    protected $table = 'riwayat_usulan';

    protected $fillable = [
        'usulan_id',
        'status',
        'catatan_publik',
        'catatan_internal',
        'diubah_oleh',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusUsulan::class,
        ];
    }

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(Usulan::class);
    }

    public function pengubah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh');
    }
}
