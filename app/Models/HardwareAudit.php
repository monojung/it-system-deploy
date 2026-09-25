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
        'hardware_id',
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
        'storage_second',
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

    public function getIsOnlineAttribute(): bool
    {
        if ($this->hardware_id && \Illuminate\Support\Facades\Cache::has('agent_online:hwid:' . strtoupper(trim($this->hardware_id)))) {
            return true;
        }
        if ($this->hostname && \Illuminate\Support\Facades\Cache::has('agent_online:host:' . strtoupper(trim($this->hostname)))) {
            return true;
        }
        if ($this->ip_address && \Illuminate\Support\Facades\Cache::has('agent_online:ip:' . trim($this->ip_address))) {
            return true;
        }
        return false;
    }

    public function getAgentTelemetryBadgeAttribute(): string
    {
        $isOnline = $this->is_online;
        $onlineBadge = $isOnline
            ? '<span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;" title="Agent เชื่อมต่อและออนไลน์อยู่ พร้อมดึงข้อมูลได้ทันที"><span style="width:6px;height:6px;border-radius:50%;background:#22c55e;display:inline-block;"></span> ออนไลน์</span>'
            : '<span class="badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;font-size:10px;font-weight:500;padding:2px 6px;border-radius:4px;display:inline-flex;align-items:center;gap:3px;" title="Agent ออฟไลน์หรือยังไม่ได้เปิดโปรแกรม"><span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;display:inline-block;"></span> ออฟไลน์</span>';

        $timeStr = $this->updated_at ? $this->updated_at->format('d/m/Y H:i น.') : '-';
        $ipStr = $this->ip_address ? "IP: {$this->ip_address}" : '';
        $verStr = $this->client_agent_version ? "v{$this->client_agent_version}" : '';

        if ($this->client_agent_version || $this->hardware_id) {
            return '<div class="d-inline-flex flex-column align-items-start gap-1">' .
                '<div class="d-flex align-items-center gap-1">' .
                    $onlineBadge .
                    '<span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="ได้รับข้อมูลจริงจาก Agent เมื่อ ' . e($timeStr) . ' ' . e($ipStr) . ' ' . e($verStr) . '">' .
                        '<i class="bi bi-shield-check text-success"></i> ได้รับข้อมูลแล้ว' .
                    '</span>' .
                '</div>' .
                '<div style="font-size: 10px; color: #059669; font-weight: 600;">' .
                    '<i class="bi bi-clock me-1"></i>' . ($this->updated_at ? $this->updated_at->diffForHumans() : '-') .
                '</div>' .
            '</div>';
        }

        return '<div class="d-inline-flex align-items-center gap-1">' .
            $onlineBadge .
            '<span class="badge" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 10.5px; font-weight: 500; padding: 3px 7px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="Agent ประจำเครื่องสแตนด์บายรอรับคำสั่ง ไม่ส่งข้อมูลจนกว่าแอดมินจะกดดึง">' .
                '<i class="bi bi-pause-circle"></i> สแตนด์บาย (รอแอดมินกดดึง)' .
            '</span>' .
        '</div>';
    }
}
