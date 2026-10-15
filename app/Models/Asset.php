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
        'it_code',
        'serial_number',
        'hardware_id',
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
        'budget_source_id',
        'acquisition_method_id',
        'ownership_type',
        'rental_contract_no',
        'rental_vendor',
        'rental_start_date',
        'rental_end_date',
        'rental_monthly_fee',
        'rental_contact_phone',
        'rental_conditions',
        'image',
        'notes',
        'last_audited_at',
        'last_audited_fiscal_year',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expire_date' => 'date',
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'price' => 'decimal:2',
        'rental_monthly_fee' => 'decimal:2',
        'ram_capacity' => 'integer',
        'last_audited_at' => 'datetime',
        'last_audited_fiscal_year' => 'integer',
    ];

    public function budgetSource(): BelongsTo
    {
        return $this->belongsTo(BudgetSource::class, 'budget_source_id');
    }

    public function acquisitionMethod(): BelongsTo
    {
        return $this->belongsTo(AcquisitionMethod::class, 'acquisition_method_id');
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

    public function hardwareAudits(): HasMany
    {
        return $this->hasMany(HardwareAudit::class, 'asset_id')->latest();
    }

    public function borrows(): HasMany
    {
        return $this->hasMany(AssetBorrow::class, 'asset_id')->latest();
    }

    public function currentBorrow()
    {
        return $this->hasOne(AssetBorrow::class, 'asset_id')
            ->whereIn('status', ['approved', 'borrowed'])
            ->latestOfMany();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id')->latest();
    }

    public function latestTransfer()
    {
        return $this->hasOne(AssetTransfer::class, 'asset_id')->latestOfMany();
    }

    public function scopeAuditedInFiscalYear($query, int $fiscalYear)
    {
        return $query->where('last_audited_fiscal_year', $fiscalYear);
    }

    public function scopePendingAuditInFiscalYear($query, int $fiscalYear)
    {
        return $query->where(function ($q) use ($fiscalYear) {
            $q->whereNull('last_audited_fiscal_year')
              ->orWhere('last_audited_fiscal_year', '<', $fiscalYear);
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'ใช้งานปกติ',
            'spare' => 'เครื่องสำรอง',
            'borrowed' => 'กำลังถูกยืมใช้งาน',
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
            'borrowed' => 'badge-primary',
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

    public function getOwnershipLabelAttribute(): string
    {
        return match ($this->ownership_type) {
            'rented' => 'เช่าใช้ (เครื่องเช่า)',
            'donated' => 'ได้รับบริจาค',
            'borrowed' => 'ยืมใช้งานภายนอก',
            default => 'เป็นของ รพ. (ซื้อขาด)',
        };
    }

    public function getOwnershipBadgeAttribute(): string
    {
        return match ($this->ownership_type) {
            'rented' => 'badge-warning',
            'donated' => 'badge-info',
            'borrowed' => 'badge-primary',
            default => 'badge-success',
        };
    }

    public function getIsRentedAttribute(): bool
    {
        return $this->ownership_type === 'rented';
    }

    public function getRentalDaysRemainingAttribute(): ?int
    {
        if (!$this->rental_end_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->rental_end_date->startOfDay(), false);
    }

    public function getRentalStatusLabelAttribute(): string
    {
        if (!$this->is_rented) return '-';
        if (!$this->rental_end_date) return 'มีสัญญาเช่า';

        $days = $this->rental_days_remaining;
        if ($days < 0) {
            return 'หมดสัญญาเช่าแล้ว (เกินกำหนด ' . abs($days) . ' วัน)';
        } elseif ($days === 0) {
            return 'หมดสัญญาเช่าวันนี้';
        } elseif ($days <= 30) {
            return 'ใกล้หมดสัญญาเช่า (เหลือ ' . $days . ' วัน)';
        }
        return 'อยู่ในสัญญาเช่า (เหลือ ' . $days . ' วัน)';
    }

    public function getRentalStatusBadgeAttribute(): string
    {
        if (!$this->is_rented || !$this->rental_end_date) return 'badge-secondary';
        $days = $this->rental_days_remaining;
        if ($days < 0) return 'badge-danger';
        if ($days <= 30) return 'badge-warning';
        return 'badge-success';
    }

    public function scopeRented($query)
    {
        return $query->where('ownership_type', 'rented');
    }

    public function scopeOwned($query)
    {
        return $query->where('ownership_type', 'owned');
    }

    public function getIsDonatedAttribute(): bool
    {
        if ($this->ownership_type === 'donated') {
            return true;
        }
        if ($this->acquisitionMethod && str_contains(mb_strtolower($this->acquisitionMethod->name), 'บริจาค')) {
            return true;
        }
        return false;
    }

    public function scopeDonated($query)
    {
        return $query->where('ownership_type', 'donated')
            ->orWhereHas('acquisitionMethod', fn($q) => $q->where('name', 'like', '%บริจาค%'));
    }

    protected static function booted()
    {
        static::saving(function ($asset) {
            if (empty($asset->attributes['it_code'])) {
                $asset->attributes['it_code'] = \App\Services\ItAssetCodeGeneratorService::generateAutoBoundCode($asset);
            }
        });
    }

    /**
     * รหัสคุมครุภัณฑ์ไอที (IT Asset Tag / it_code)
     * โครงสร้างใหม่: [รหัสย่อประเภท]-[สุ่ม 6 หลัก อักษรพิมพ์ใหญ่+ตัวเลข] (เช่น PC-K9M2X4)
     */
    public function getItCodeAttribute($value = null): string
    {
        // 1. ถ้ามีค่าจริงในคอลัมน์ it_code
        if (!empty($value)) {
            return strtoupper(trim($value));
        }

        if (!empty($this->attributes['it_code'])) {
            return strtoupper(trim($this->attributes['it_code']));
        }

        // 2. ถ้ามีการบันทึกแท็กปรับแต่งพิเศษไว้ใน notes เช่น [IT-TAG: PC-K9M2X4]
        if (!empty($this->notes) && preg_match('/\[IT-TAG:\s*([^\]]+)\]/', $this->notes, $matches)) {
            return strtoupper(trim($matches[1]));
        }

        // 3. ผูกรหัสสุ่มอิสระตามประเภทอุปกรณ์
        $code = \App\Services\ItAssetCodeGeneratorService::generateAutoBoundCode($this);
        $this->attributes['it_code'] = $code;
        return $code;
    }

    public function setItCodeAttribute($value): void
    {
        $this->attributes['it_code'] = !empty($value) ? strtoupper(trim($value)) : null;
    }

    /**
     * ดึง Barcode SVG ของรหัสคุมครุภัณฑ์ไอที
     */
    public function getBarcodeSvgAttribute(): string
    {
        $code = $this->it_code ?: ($this->asset_code ?: 'ASSET');
        return \App\Services\BarcodeService::generateSvg($code, 55, 2, true);
    }

    /**
     * ดึง Barcode Data URI สำหรับแสดงผลใน <img src="...">
     */
    public function getBarcodeDataUriAttribute(): string
    {
        $code = $this->it_code ?: ($this->asset_code ?: 'ASSET');
        return \App\Services\BarcodeService::generateDataUri($code, 55, 2, true);
    }

    /**
     * Alias สำหรับ it_asset_code เพื่อความเข้ากันได้ 100% กับระบบเดิมและ Blade Views
     */
    public function getItAssetCodeAttribute(): string
    {
        return $this->it_code;
    }

    public function setItAssetCodeAttribute($value): void
    {
        $this->it_code = $value;
    }

    /**
     * ตรวจสอบว่ามีรหัสครุภัณฑ์พัสดุราชการที่แท้จริงหรือไม่
     */
    public function getHasOfficialAssetCodeAttribute(): bool
    {
        if ($this->is_rented || $this->is_donated) {
            return false;
        }
        if (empty($this->asset_code)) {
            return false;
        }
        $code = strtoupper(trim($this->asset_code));
        return !str_starts_with($code, 'PENDING') 
            && !str_starts_with($code, 'TEMP') 
            && !str_starts_with($code, 'NO-ASSET') 
            && !str_starts_with($code, 'NOASSET') 
            && !str_starts_with($code, 'RENT') 
            && !str_starts_with($code, 'DON');
    }

    /**
     * ข้อความแสดงรหัสครุภัณฑ์พัสดุสำหรับแสดงผล
     */
    public function getOfficialAssetCodeDisplayAttribute(): string
    {
        if ($this->is_rented || str_starts_with((string)$this->it_code, 'RENT')) {
            return 'เครื่องเช่า (ไม่มีเลขพัสดุ)';
        }
        if ($this->is_donated || str_starts_with((string)$this->it_code, 'DON')) {
            return 'รับบริจาค (ไม่มีเลขพัสดุ)';
        }
        if ($this->has_official_asset_code) {
            return $this->asset_code;
        }
        return 'รอเลขพัสดุ';
    }
}

