<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Auditable;

class Asset extends Model
{
    use HasFactory, Auditable;

    protected $table = 'it_assets';

    protected $fillable = [
        'asset_code',
        'serial_number',
        'name',
        'device_type_id',
        'brand',
        'model',
        'specs',
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
        'ip_address',
        'mac_address',
        'department_id',
        'location_detail',
        'custodian_name',
        'status',
        'purchase_date',
        'price',
        'warranty_expire_date',
        'budget_year',
        'image',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expire_date' => 'date',
            'price' => 'decimal:2',
            'ram_capacity' => 'integer',
        ];
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(DeviceType::class, 'device_type_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class, 'asset_id')->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'ใช้งานปกติ',
            'spare' => 'เครื่องสำรอง',
            'repairing' => 'กำลังส่งซ่อม',
            'broken' => 'ชำรุดรอซ่อม',
            'disposed' => 'รอจำหน่าย/แทงจำหน่าย',
            default => 'ไม่ระบุ',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'spare' => 'badge-info',
            'repairing' => 'badge-warning',
            'broken' => 'badge-danger',
            'disposed' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    public function getFormattedSpecsAttribute(): string
    {
        $parts = [];
        if ($this->cpu_model) {
            $parts[] = $this->cpu_model . ($this->cpu_speed ? " ({$this->cpu_speed})" : '');
        }
        if ($this->ram_capacity) {
            $ramStr = "RAM {$this->ram_capacity} GB";
            if ($this->ram_type) $ramStr .= " {$this->ram_type}";
            $parts[] = $ramStr;
        }
        if ($this->storage_capacity || $this->storage_type) {
            $storageStr = trim(($this->storage_type ?? '') . ' ' . ($this->storage_capacity ?? ''));
            if ($storageStr) $parts[] = $storageStr;
        }
        if ($this->os_name) {
            $parts[] = $this->os_name;
        }

        if (!empty($parts)) {
            return implode(' • ', $parts);
        }

        return $this->specs ?? '-';
    }

    public function getRamDisplayAttribute(): ?string
    {
        if (!$this->ram_capacity) return null;
        $str = "{$this->ram_capacity} GB";
        if ($this->ram_type) $str .= " {$this->ram_type}";
        if ($this->ram_bus) $str .= " ({$this->ram_bus})";
        return $str;
    }

    public function getStorageDisplayAttribute(): ?string
    {
        if (!$this->storage_capacity && !$this->storage_type) return null;
        $str = trim(($this->storage_type ?? '') . ' ' . ($this->storage_capacity ?? ''));
        if ($this->storage_second) {
            $str .= " + {$this->storage_second}";
        }
        return $str;
    }

    public function getIsComputerAttribute(): bool
    {
        $code = strtoupper($this->deviceType?->code ?? '');
        if (in_array($code, ['PC', 'NB', 'AIO', 'SRV'])) {
            return true;
        }

        $name = mb_strtolower($this->deviceType?->name ?? $this->name);
        return str_contains($name, 'คอมพิวเตอร์') || str_contains($name, 'โน้ตบุ๊ก') || str_contains($name, 'server') || str_contains($name, 'pc');
    }
}

