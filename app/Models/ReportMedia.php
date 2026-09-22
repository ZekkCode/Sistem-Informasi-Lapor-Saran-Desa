<?php

namespace App\Models;

use App\Enums\MediaBackupStatus;
use App\Enums\ReportMediaType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportMedia extends Model
{
    use HasFactory;

    protected $table = 'report_media';

    protected $fillable = [
        'report_id',
        'media_type',
        'storage_disk',
        'file_path',
        'mime_type',
        'file_size',
        'caption',
        'uploaded_by',
        'google_drive_status',
        'google_drive_file_id',
        'google_drive_attempts',
        'google_drive_synced_at',
        'google_drive_error',
    ];

    protected function casts(): array
    {
        return [
            'media_type' => ReportMediaType::class,
            'file_size' => 'integer',
            'google_drive_status' => MediaBackupStatus::class,
            'google_drive_attempts' => 'integer',
            'google_drive_synced_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
