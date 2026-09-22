<?php

namespace App\Models;

use App\Enums\AdminPriority;
use App\Enums\CitizenPriority;
use App\Enums\ReportStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_code',
        'submission_token',
        'workflow_version',
        'reporter_name',
        'reporter_phone',
        'dusun_id',
        'subcategory_id',
        'qr_source_id',
        'title',
        'description',
        'location_text',
        'citizen_priority',
        'admin_priority',
        'verification_status',
        'status',
        'current_public_note',
        'verified_by',
        'verified_at',
        'completed_at',
        'submitted_at',
    ];

    protected $hidden = ['reporter_name', 'reporter_phone', 'submission_token'];

    protected function casts(): array
    {
        return [
            'citizen_priority' => CitizenPriority::class,
            'workflow_version' => 'integer',
            'admin_priority' => AdminPriority::class,
            'verification_status' => VerificationStatus::class,
            'status' => ReportStatus::class,
            'verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function qrSource(): BelongsTo
    {
        return $this->belongsTo(QrSource::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ReportMedia::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class)->latest();
    }

    public function scopeVisibleToPublic(Builder $query): Builder
    {
        return $query->where('verification_status', VerificationStatus::Verified->value);
    }
}
