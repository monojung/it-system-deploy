<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Repair extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_repairs';

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'asset_id',
        'other_device_info',
        'department_id',
        'location_detail',
        'urgency',
        'status',
        'requester_id',
        'requester_name',
        'requester_phone',
        'technician_id',
        'received_at',
        'completed_at',
        'cause',
        'solution',
        'external_repair_detail',
        'total_cost',
        'satisfaction_score',
        'satisfaction_comment',
        'attachment_image',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_cost' => 'decimal:2',
        'satisfaction_score' => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RepairLog::class, 'repair_id')->orderBy('created_at', 'desc');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(RepairPart::class, 'repair_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'รอรับเรื่อง',
            'in_progress' => 'กำลังดำเนินการซ่อม',
            'waiting_parts' => 'รออะไหล่',
            'completed' => 'ซ่อมเสร็จสิ้น',
            'external' => 'ส่งซ่อมภายนอก',
            'cancelled' => 'ยกเลิกรายการ',
            default => 'ไม่ทราบสถานะ',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'badge-warning',
            'in_progress' => 'badge-primary',
            'waiting_parts' => 'badge-orange',
            'completed' => 'badge-success',
            'external' => 'badge-purple',
            'cancelled' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    public function getUrgencyLabelAttribute(): string
    {
        return match ($this->urgency) {
            'low' => 'ปกติ (ทั่วไป)',
            'normal' => 'ปานกลาง',
            'high' => 'ด่วน',
            'critical' => 'ด่วนที่สุด (กระทบงานบริการผู้ป่วย)',
            default => 'ปกติ',
        };
    }

    public function getUrgencyBadgeAttribute(): string
    {
        return match ($this->urgency) {
            'low' => 'badge-outline-secondary',
            'normal' => 'badge-outline-info',
            'high' => 'badge-outline-warning',
            'critical' => 'badge-outline-danger animate-pulse',
            default => 'badge-outline-secondary',
        };
    }

    /**
     * Get SLA target hours dynamically from system settings
     */
    public function getSlaHoursAttribute(): int
    {
        $settingKey = 'sla_' . ($this->urgency ?: 'normal');
        $defaultHours = match ($this->urgency) {
            'low' => 48,
            'normal' => 24,
            'high' => 4,
            'critical' => 1,
            default => 24,
        };

        return (int) setting($settingKey, $defaultHours);
    }

    /**
     * Target completion due date based on creation time and SLA hours
     */
    public function getDueDateAttribute()
    {
        $start = $this->received_at ?? $this->created_at;
        return $start ? $start->copy()->addHours($this->sla_hours) : null;
    }

    /**
     * Check if this ticket exceeded the SLA target
     */
    public function getIsOverdueAttribute(): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            if ($this->completed_at && $this->due_date) {
                return $this->completed_at->gt($this->due_date);
            }
            return false;
        }

        return $this->due_date ? now()->gt($this->due_date) : false;
    }

    /**
     * Remaining hours until SLA deadline (or negative if overdue)
     */
    public function getRemainingHoursAttribute(): ?float
    {
        if (!$this->due_date) {
            return null;
        }

        $compareTo = in_array($this->status, ['completed', 'cancelled']) && $this->completed_at
            ? $this->completed_at
            : now();

        return round($compareTo->diffInMinutes($this->due_date, false) / 60, 1);
    }
}
