<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            if ($model->shouldSkipAuditing()) {
                return;
            }

            $module = $model->getAuditModule();
            $title = $model->getAuditIdentifier();
            $desc = "สร้าง{$model->getAuditEntityName()}: {$title}";

            $newValues = $model->filterAuditAttributes($model->getAttributes());

            AuditLog::record('create', $module, $desc, $model, null, $newValues);
        });

        static::updated(function (Model $model) {
            if ($model->shouldSkipAuditing()) {
                return;
            }

            $dirty = $model->getDirty();
            $dirty = $model->filterAuditAttributes($dirty);

            // If only updated_at changed or no monitored fields changed, skip
            if (empty($dirty)) {
                return;
            }

            $module = $model->getAuditModule();
            $title = $model->getAuditIdentifier();
            $fieldsCount = count($dirty);
            $desc = "แก้ไขข้อมูล{$model->getAuditEntityName()}: {$title} ({$fieldsCount} รายการ)";

            $oldValues = [];
            $newValues = [];
            foreach (array_keys($dirty) as $key) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $model->getAttribute($key);
            }

            AuditLog::record('update', $module, $desc, $model, $oldValues, $newValues);
        });

        static::deleted(function (Model $model) {
            if ($model->shouldSkipAuditing()) {
                return;
            }

            $module = $model->getAuditModule();
            $title = $model->getAuditIdentifier();
            $desc = "ลบ{$model->getAuditEntityName()}: {$title}";

            $oldValues = $model->filterAuditAttributes($model->getOriginal());

            AuditLog::record('delete', $module, $desc, $model, $oldValues, null);
        });
    }

    public function shouldSkipAuditing(): bool
    {
        return property_exists($this, 'disableAuditing') && $this->disableAuditing;
    }

    public function getAuditModule(): string
    {
        if (property_exists($this, 'auditModule') && !empty($this->auditModule)) {
            return $this->auditModule;
        }

        return match (class_basename($this)) {
            'Asset' => 'assets',
            'Repair' => 'repairs',
            'DataRequest' => 'data_requests',
            'SparePart' => 'spare_parts',
            'User' => 'users',
            'Department' => 'departments',
            'SystemSetting' => 'settings',
            'BackupLog' => 'backups',
            default => Str::snake(Str::plural(class_basename($this))),
        };
    }

    public function getAuditEntityName(): string
    {
        return match (class_basename($this)) {
            'Asset' => 'ครุภัณฑ์',
            'Repair' => 'ใบแจ้งซ่อม',
            'DataRequest' => 'คำขอข้อมูล',
            'SparePart' => 'พัสดุ/อะไหล่',
            'User' => 'ผู้ใช้งาน',
            'Department' => 'แผนก',
            'SystemSetting' => 'การตั้งค่าระบบ',
            'BackupLog' => 'ประวัติสำรองข้อมูล',
            default => class_basename($this),
        };
    }

    public function getAuditIdentifier(): string
    {
        if (isset($this->asset_code)) {
            return $this->asset_code . ($this->name ? " ({$this->name})" : '');
        }
        if (isset($this->ticket_number)) {
            return $this->ticket_number . ($this->title ? " ({$this->title})" : '');
        }
        if (isset($this->request_no)) {
            return $this->request_no . ($this->title ? " ({$this->title})" : '');
        }
        if (isset($this->request_code)) {
            return $this->request_code . ($this->subject ? " ({$this->subject})" : '');
        }
        if (isset($this->part_code)) {
            return $this->part_code . ($this->name ? " ({$this->name})" : '');
        }
        if (isset($this->code) && isset($this->name)) {
            return "{$this->code} ({$this->name})";
        }
        if (isset($this->username)) {
            return $this->name . " (@{$this->username})";
        }
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->key)) {
            return $this->key;
        }
        if (isset($this->filename)) {
            return $this->filename;
        }

        return '#' . $this->getKey();
    }

    public function filterAuditAttributes(array $attributes): array
    {
        $ignored = [
            'created_at',
            'updated_at',
            'password',
            'remember_token',
            'google_client_secret',
            'thaid_client_secret',
        ];

        foreach ($ignored as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }
}
