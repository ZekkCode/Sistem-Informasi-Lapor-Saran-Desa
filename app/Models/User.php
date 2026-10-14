<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function verifiedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'verified_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isAdminDesa(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isAdminPengaduan(): bool
    {
        return $this->role === UserRole::AdminPengaduan;
    }

    public function isAdminUsulan(): bool
    {
        return $this->role === UserRole::AdminUsulan;
    }

    public function canAccessReports(): bool
    {
        return in_array($this->role, [
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::AdminPengaduan,
        ], true);
    }

    public function canAccessUsulan(): bool
    {
        return in_array($this->role, [
            UserRole::SuperAdmin,
            UserRole::Admin,
            UserRole::AdminUsulan,
        ], true);
    }
}
