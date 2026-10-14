<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanMedia extends Model
{
    use HasFactory;

    protected $table = 'usulan_media';

    protected $fillable = [
        'usulan_id',
        'storage_disk',
        'file_path',
        'mime_type',
        'file_size',
        'caption',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(Usulan::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
