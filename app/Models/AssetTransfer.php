<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Carbon\Carbon;

class AssetTransfer extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_asset_transfers';

    protected $fillable = [
        'transfer_no',
        'asset_id',
        'user_id',
        'transfer_type',
        'from_department_id',
        'from_department_name',
        'from_location_detail',
        'from_custodian_name',
        'from_ip_address',
        'to_department_id',
        'to_department_name',
        'to_location_detail',
        'to_custodian_name',
        'to_ip_address',
        'transfer_date',
        'reason',
        'status',
        'technician_id',
        'completed_at',
        'test_result',
        'receiver_name',
        'notes',
        'attachment',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Accessors
    public function getTransferTypeLabelAttribute(): string
    {
        return match ($this->transfer_type) {
            'relocation' => 'ย้ายจุดติดตั้ง/ห้อง',
            'department_transfer' => 'โอนย้ายหน่วยงาน/ผู้ครอบครอง',
            'temporary_move' => 'ย้ายใช้งานชั่วคราว',
            default => 'ย้ายจุดติดตั้ง',
        };
    }

    public function getTransferTypeBadgeAttribute(): string
    {
        return match ($this->transfer_type) {
            'relocation' => '<span class="badge" style="background:#e0e7ff; color:#4338ca; border:1px solid #c7d2fe;"><i class="bi bi-geo-alt me-1"></i>ย้ายจุดติดตั้ง</span>',
            'department_transfer' => '<span class="badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a;"><i class="bi bi-buildings me-1"></i>โอนย้ายหน่วยงาน</span>',
            'temporary_move' => '<span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;"><i class="bi bi-clock me-1"></i>ย้ายชั่วคราว</span>',
            default => '<span class="badge badge-light">ย้ายจุดติดตั้ง</span>',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'รอเจ้าหน้าที่ดำเนินการ',
            'in_progress' => 'กำลังดำเนินการย้าย',
            'completed' => 'ย้ายเสร็จสมบูรณ์',
            'cancelled' => 'ยกเลิกคำขอ',
            default => 'ไม่ระบุ',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => '<span class="badge" style="background:#f59e0b; color:#fff;"><i class="bi bi-clock-history me-1"></i>รอดำเนินการ</span>',
            'in_progress' => '<span class="badge" style="background:#0284c7; color:#fff;"><i class="bi bi-tools me-1"></i>กำลังย้าย/ติดตั้ง</span>',
            'completed' => '<span class="badge" style="background:#10b981; color:#fff;"><i class="bi bi-check2-circle me-1"></i>ย้ายเรียบร้อย</span>',
            'cancelled' => '<span class="badge" style="background:#94a3b8; color:#fff;"><i class="bi bi-slash-circle me-1"></i>ยกเลิก</span>',
            default => '<span class="badge badge-light">ไม่ระบุ</span>',
        };
    }

    public static function generateTransferNo(): string
    {
        $prefix = 'TR-' . Carbon::now()->format('Ym') . '-';
        $latest = self::where('transfer_no', 'like', "{$prefix}%")
            ->orderBy('transfer_no', 'desc')
            ->first();

        if ($latest) {
            $lastSequence = (int) substr($latest->transfer_no, -4);
            $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }

        return $prefix . $newSequence;
    }
}
