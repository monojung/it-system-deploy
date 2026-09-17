<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;
use Carbon\Carbon;

class AssetBorrow extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_asset_borrows';

    protected $fillable = [
        'borrow_no',
        'user_id',
        'borrower_name',
        'department_id',
        'borrower_department',
        'borrower_position',
        'contact_phone',
        'asset_id',
        'purpose',
        'location_used',
        'borrow_date',
        'expected_return_date',
        'actual_return_date',
        'accessories',
        'status',
        'notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'dispatched_by',
        'dispatched_at',
        'dispatch_condition',
        'received_by',
        'return_condition',
        'return_notes',
    ];

    protected $casts = [
        'borrow_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
        'approved_at' => 'datetime',
        'dispatched_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', 'borrowed');
    }

    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')
            ->whereDate('expected_return_date', '<', Carbon::today());
    }

    // Accessors
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'borrowed' && $this->expected_return_date) {
            return Carbon::parse($this->expected_return_date)->isPast();
        }
        return false;
    }

    public function getDurationDaysAttribute(): int
    {
        if ($this->borrow_date && $this->expected_return_date) {
            return max(1, $this->borrow_date->diffInDays($this->expected_return_date) + 1);
        }
        return 1;
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_overdue) {
            return 'เกินกำหนดส่งคืน';
        }

        return match ($this->status) {
            'pending' => 'รอเจ้าหน้าที่อนุมัติ',
            'approved' => 'อนุมัติแล้ว (รอรับอุปกรณ์)',
            'borrowed' => 'กำลังยืมใช้งาน',
            'returned' => 'ส่งคืนเรียบร้อยแล้ว',
            'rejected' => 'ไม่อนุมัติ / ปฏิเสธ',
            'cancelled' => 'ยกเลิกคำขอ',
            default => 'ไม่ระบุ',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_overdue) {
            return '<span class="badge badge-danger" style="background:#ef4444; color:#fff;"><i class="bi bi-exclamation-triangle-fill me-1"></i>เกินกำหนดส่งคืน</span>';
        }

        return match ($this->status) {
            'pending' => '<span class="badge badge-warning" style="background:#f59e0b; color:#fff;"><i class="bi bi-clock-history me-1"></i>รออนุมัติ</span>',
            'approved' => '<span class="badge badge-info" style="background:#0284c7; color:#fff;"><i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว</span>',
            'borrowed' => '<span class="badge badge-primary" style="background:#0d9488; color:#fff;"><i class="bi bi-box-arrow-right me-1"></i>กำลังยืมใช้งาน</span>',
            'returned' => '<span class="badge badge-success" style="background:#10b981; color:#fff;"><i class="bi bi-check2-circle me-1"></i>ส่งคืนแล้ว</span>',
            'rejected' => '<span class="badge badge-danger" style="background:#64748b; color:#fff;"><i class="bi bi-x-circle me-1"></i>ปฏิเสธ</span>',
            'cancelled' => '<span class="badge badge-secondary" style="background:#94a3b8; color:#fff;"><i class="bi bi-slash-circle me-1"></i>ยกเลิก</span>',
            default => '<span class="badge badge-light">ไม่ระบุ</span>',
        };
    }

    // Aliases & Compatibility Accessors
    public function getApproverIdAttribute()
    {
        return $this->approved_by;
    }

    public function setApproverIdAttribute($value): void
    {
        $this->attributes['approved_by'] = $value;
    }

    public function getDispatcherIdAttribute()
    {
        return $this->dispatched_by;
    }

    public function setDispatcherIdAttribute($value): void
    {
        $this->attributes['dispatched_by'] = $value;
    }

    public function getReceiverIdAttribute()
    {
        return $this->received_by;
    }

    public function setReceiverIdAttribute($value): void
    {
        $this->attributes['received_by'] = $value;
    }

    public function getBorrowerPhoneAttribute()
    {
        return $this->contact_phone;
    }

    public function setBorrowerPhoneAttribute($value): void
    {
        $this->attributes['contact_phone'] = $value;
    }

    public function getLocationOfUseAttribute()
    {
        return $this->location_used;
    }

    public function setLocationOfUseAttribute($value): void
    {
        $this->attributes['location_used'] = $value;
    }

    public function getBorrowerNoteAttribute()
    {
        return $this->notes;
    }

    public function setBorrowerNoteAttribute($value): void
    {
        $this->attributes['notes'] = $value;
    }

    public function getBorrowerDepartmentAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return $this->department?->name ?? null;
    }

    public function getAccessoriesAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    public function setAccessoriesAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['accessories'] = json_encode(array_values(array_filter($value)), JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['accessories'] = $value;
        }
    }

    public function getAccessoriesTextAttribute(): string
    {
        $acc = $this->accessories;
        if (is_array($acc)) {
            return implode(', ', $acc);
        }
        return (string) $acc;
    }

    public static function generateBorrowNo(): string
    {
        $prefix = 'BR-' . Carbon::now()->format('Ym') . '-';
        $latest = self::where('borrow_no', 'like', "{$prefix}%")
            ->orderBy('borrow_no', 'desc')
            ->first();

        if ($latest) {
            $lastSequence = (int) substr($latest->borrow_no, -4);
            $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }

        return $prefix . $newSequence;
    }
}
