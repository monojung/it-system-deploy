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
        'first_name',
        'last_name',
        'username',
        'cid',
        'line_user_id',
        'notify_line_enabled',
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
        'approval_status',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'onboarding_completed',
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
        'notify_line_enabled' => 'boolean',
        'mfa_enabled' => 'boolean',
        'mfa_enforced' => 'boolean',
        'onboarding_completed' => 'boolean',
        'approved_at' => 'datetime',
        'mfa_enrolled_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password_setup_expires_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function repairsRequested(): HasMany
    {
        return $this->hasMany(Repair::class, 'requester_id');
    }

    public function repairsAssigned(): HasMany
    {
        return $this->hasMany(Repair::class, 'technician_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isTechnician(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'technician']);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function getRoleNameAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'แอดมินระบบ (Super Admin)',
            'admin' => 'แอดมิน (Admin)',
            'technician' => 'แอดมิน (Admin)',
            'user' => 'ผู้ใช้งาน (User)',
            default => 'ผู้ใช้งาน',
        };
    }

    public function getRoleBadgeAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'badge-danger',
            'admin' => 'badge-primary',
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

    public function hasLineLinked(): bool
    {
        return !empty($this->line_user_id);
    }

    public function isLineNotifyActive(): bool
    {
        return (bool) $this->notify_line_enabled && (!empty($this->line_user_id) || !empty($this->cid));
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

    public function isApproved(): bool
    {
        return $this->isAdmin() || $this->approval_status === 'approved';
    }

    public function isPendingApproval(): bool
    {
        return !$this->isAdmin() && $this->approval_status === 'pending';
    }

    public function isRejected(): bool
    {
        return !$this->isAdmin() && $this->approval_status === 'rejected';
    }

    public function hasCompletedOnboarding(): bool
    {
        return (bool) $this->onboarding_completed && !empty($this->department_id);
    }

    public function getApprovalBadgeAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'badge-success',
            'pending' => 'badge-warning',
            'rejected' => 'badge-danger',
            default => 'badge-light',
        };
    }

    public function getApprovalLabelAttribute(): string
    {
        return match ($this->approval_status) {
            'approved' => 'อนุมัติแล้ว (เป็นเจ้าหน้าที่ รพ. จริง)',
            'pending' => 'รอตรวจสอบยืนยันตัวตน',
            'rejected' => 'ปฏิเสธ (ไม่ใช่เจ้าหน้าที่)',
            default => 'ไม่ระบุ',
        };
    }

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }
}
