<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HardwareAudit extends Model
{
    use HasFactory;

    protected $table = 'it_hardware_audits';

    protected $fillable = [
        'fiscal_year',
        'asset_id',
        'hostname',
        'serial_number',
        'mac_address',
        'ip_address',
        'brand',
        'model',
        'device_type_code',
        'cpu_model',
        'cpu_speed',
        'ram_capacity',
        'ram_type',
        'ram_bus',
        'ram_slots',
        'storage_type',
        'storage_capacity',
        'os_name',
        'os_license',
        'gpu_model',
        'monitor_size',
        'raw_payload',
        'specs_diff',
        'client_agent_version',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'ram_capacity' => 'integer',
        'raw_payload' => 'array',
        'specs_diff' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFiscalYear($query, $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function hasDiff(): bool
    {
        return !empty($this->specs_diff);
    }

    public function getDiffCountAttribute(): int
    {
        return is_array($this->specs_diff) ? count($this->specs_diff) : 0;
    }

    public function isUnderMdesStandard(): bool
    {
        if ($this->ram_capacity && $this->ram_capacity < 8) return true;
        if ($this->storage_type && stripos($this->storage_type, 'HDD') !== false) return true;
        if ($this->os_name && (stripos($this->os_name, 'Windows 7') !== false || stripos($this->os_name, 'Windows 8') !== false)) return true;
        return false;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'อนุมัติอัปเดตแล้ว',
            'rejected' => 'ปฏิเสธ/ยกเลิก',
            default => 'รอแอดมินตรวจสอบ',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว</span>',
            'rejected' => '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-x-circle me-1"></i>ปฏิเสธ</span>',
            default => '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="bi bi-clock-history me-1"></i>รอตรวจสอบ</span>',
        };
    }
}
