<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    protected $table = 'it_users';

    protected $fillable = [
        'name',
        'username',
        'cid',
        'google_id',
        'google_email',
        'mfa_enabled',
        'mfa_enforced',
        'mfa_secret',
        'mfa_enrolled_at',
        'email',
        'email_verified_at',
        'password_setup_token',
        'password_setup_expires_at',
        'password',
        'role',
        'department_id',
        'position',
        'phone',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'password_setup_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'mfa_enabled' => 'boolean',
        'mfa_enforced' => 'boolean',
        'mfa_enrolled_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password_setup_expires_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function repairsRequested(): HasMany
    {
        return $this->hasMany(Repair::class, 'requester_id');
    }

    public function repairsAssigned(): HasMany
    {
        return $this->hasMany(Repair::class, 'technician_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTechnician(): bool
    {
        return in_array($this->role, ['admin', 'technician']);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'ผู้ดูแลระบบ (Admin)',
            'technician' => 'เจ้าหน้าที่ IT / ช่างซ่อม',
            'user' => 'ผู้ใช้ทั่วไป (บุคลากร รพ.)',
            default => 'ผู้ใช้งาน',
        };
    }

    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'badge-danger',
            'technician' => 'badge-primary',
            'user' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    public function getFormattedCidAttribute(): ?string
    {
        return $this->cid ? format_thai_id($this->cid) : null;
    }

    public function setCidAttribute(?string $value): void
    {
        $this->attributes['cid'] = $value ? preg_replace('/[^0-9]/', '', $value) : null;
    }

    public function isMfaActive(): bool
    {
        return (bool) ($this->mfa_enabled || $this->mfa_enforced);
    }

    public function hasGoogleLinked(): bool
    {
        return !empty($this->google_id) || !empty($this->google_email);
    }

    public function hasThaidLinked(): bool
    {
        return !empty($this->cid);
    }

    public function getMfaStatusLabelAttribute(): string
    {
        if ($this->mfa_enforced) {
            return 'บังคับใช้ MFA (Admin Enforced)';
        }
        return $this->mfa_enabled ? 'เปิดใช้งาน (Active)' : 'ปิดใช้งาน (Disabled)';
    }

    public function getMfaStatusBadgeAttribute(): string
    {
        if ($this->mfa_enforced) {
            return 'badge-warning';
        }
        return $this->mfa_enabled ? 'badge-success' : 'badge-light';
    }

    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function hasPasswordSetupPending(): bool
    {
        return !empty($this->password_setup_token);
    }
}
