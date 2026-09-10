<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'it_audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'module',
        'action',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Record an audit log entry safely without interrupting business flow
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
        ?User $user = null
    ): ?self {
        try {
            $user = $user ?? Auth::user();
            $request = $request ?? (app()->bound('request') ? request() : null);

            // Filter out sensitive fields
            $hidden = ['password', 'remember_token', 'google_client_secret', 'thaid_client_secret', 'client_secret'];
            if ($oldValues) {
                foreach ($hidden as $key) {
                    unset($oldValues[$key]);
                }
            }
            if ($newValues) {
                foreach ($hidden as $key) {
                    unset($newValues[$key]);
                }
            }

            return self::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'ระบบ / ผู้เยี่ยมชม',
                'user_role' => $user?->role ?? 'guest',
                'module' => $module,
                'action' => $action,
                'description' => mb_substr($description, 0, 500, 'UTF-8'),
                'auditable_type' => $auditable ? get_class($auditable) : null,
                'auditable_id' => $auditable?->getKey(),
                'old_values' => !empty($oldValues) ? $oldValues : null,
                'new_values' => !empty($newValues) ? $newValues : null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to write audit log: ' . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
            ]);
            return null;
        }
    }

    public function getTargetUrlAttribute(): ?string
    {
        if (!$this->auditable_type || !$this->auditable_id) {
            return null;
        }

        // Deletions do not have an active target view
        if ($this->action === 'delete') {
            return null;
        }

        try {
            $class = class_basename($this->auditable_type);
            return match ($class) {
                'Repair' => route('repairs.show', $this->auditable_id),
                'Asset' => route('assets.show', $this->auditable_id),
                'DataRequest' => route('data-requests.show', $this->auditable_id),
                'User' => route('users.edit', $this->auditable_id),
                'SparePart' => route('spare-parts.edit', $this->auditable_id),
                'Department' => route('departments.index'),
                'SystemSetting' => route('settings.index'),
                'BackupLog', 'Backup' => route('backups.index'),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }
    }

    public function getTargetLabelAttribute(): ?string
    {
        if (!$this->auditable_type) {
            return null;
        }

        $class = class_basename($this->auditable_type);
        $name = match ($class) {
            'Repair' => 'ใบแจ้งซ่อม',
            'Asset' => 'ครุภัณฑ์คอมพิวเตอร์',
            'DataRequest' => 'คำขอข้อมูลสารสนเทศ',
            'User' => 'ผู้ใช้งานระบบ',
            'SparePart' => 'พัสดุ/อะไหล่ IT',
            'Department' => 'แผนก/หน่วยงาน',
            'SystemSetting' => 'การตั้งค่าระบบ',
            'BackupLog', 'Backup' => 'การสำรองฐานข้อมูล',
            default => $class,
        };

        return $name . ($this->auditable_id ? " #{$this->auditable_id}" : '');
    }

    public function getTargetIconAttribute(): string
    {
        if (!$this->auditable_type) {
            return 'bi-link-45deg';
        }

        $class = class_basename($this->auditable_type);
        return match ($class) {
            'Repair' => 'bi-tools',
            'Asset' => 'bi-pc-display',
            'DataRequest' => 'bi-file-earmark-bar-graph',
            'User' => 'bi-person-badge',
            'SparePart' => 'bi-box-seam',
            'Department' => 'bi-diagram-3',
            'SystemSetting' => 'bi-sliders',
            'BackupLog', 'Backup' => 'bi-database-gear',
            default => 'bi-link-45deg',
        };
    }

    public function getActionBadgeAttribute(): string
    {
        return match ($this->action) {
            'create' => 'badge-success',
            'update' => 'badge-info',
            'delete' => 'badge-danger',
            'login' => 'badge-purple',
            'logout' => 'badge-secondary',
            'status_change' => 'badge-warning',
            'download', 'download_sample' => 'badge-primary',
            'restore' => 'badge-danger',
            'backup' => 'badge-primary',
            'execute_query', 'generate_file' => 'badge-orange',
            'stock_adjust' => 'badge-warning',
            'import', 'import_staff' => 'badge-primary',
            'export' => 'badge-success',
            'toggle_active', 'toggle_mfa', 'reset_mfa', 'toggle_enforce_mfa' => 'badge-warning',
            'unlink_google', 'unlink_thaid' => 'badge-danger',
            'link_google', 'link_cid' => 'badge-info',
            'clear_old', 'clear_data' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getActionNameAttribute(): string
    {
        return match ($this->action) {
            'create' => 'สร้างข้อมูลใหม่',
            'update' => 'แก้ไขข้อมูล',
            'delete' => 'ลบข้อมูล',
            'login' => 'เข้าสู่ระบบ',
            'logout' => 'ออกจากระบบ',
            'status_change' => 'เปลี่ยนสถานะ',
            'download' => 'ดาวน์โหลดผลลัพธ์',
            'download_sample' => 'ดาวน์โหลดไฟล์ตัวอย่าง',
            'restore' => 'กู้คืนฐานข้อมูล',
            'backup' => 'สำรองฐานข้อมูล',
            'execute_query' => 'รันคำสั่ง SQL',
            'generate_file' => 'สร้างไฟล์ข้อมูล',
            'stock_adjust' => 'ปรับปรุงยอดสต็อก',
            'export' => 'ส่งออกรายงาน',
            'import', 'import_staff' => 'นำเข้าข้อมูล',
            'toggle_active' => 'ปรับสถานะการใช้งาน',
            'toggle_mfa', 'mfa_enable', 'mfa_disable', 'cli_enable_mfa', 'cli_disable_mfa' => 'ตั้งค่า 2FA/MFA',
            'reset_mfa', 'mfa_reset_secret', 'cli_reset_mfa' => 'รีเซ็ตคีย์ MFA',
            'toggle_enforce_mfa' => 'นโยบายบังคับ MFA',
            'link_google' => 'เชื่อมโยง Google',
            'unlink_google' => 'ยกเลิกเชื่อม Google',
            'link_cid' => 'เชื่อมโยง Thai ID',
            'unlink_thaid' => 'ยกเลิก Thai ID',
            'register' => 'ลงทะเบียนผู้ใช้',
            'password_setup' => 'ตั้งรหัสผ่านสำเร็จ',
            'forgot_password' => 'ขอรีเซ็ตรหัสผ่าน',
            'send_setup_link', 'resend_setup_link' => 'ส่งลิงก์รหัสผ่าน',
            'clear_data', 'clear_old' => 'ล้างประวัติ/ข้อมูล',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function getModuleNameAttribute(): string
    {
        return match ($this->module) {
            'auth' => 'ความปลอดภัย & ยืนยันตัวตน',
            'repairs' => 'ระบบแจ้งซ่อมบำรุง',
            'data_requests' => 'บริการข้อมูล & สารสนเทศ',
            'assets' => 'คลังครุภัณฑ์ & ฮาร์ดแวร์',
            'spare_parts' => 'คลังพัสดุ & อะไหล่ IT',
            'backups' => 'สำรอง & กู้คืนฐานข้อมูล',
            'users' => 'จัดการผู้ใช้งาน & สิทธิ์',
            'departments' => 'จัดการแผนก / หน่วยงาน',
            'settings' => 'ตั้งค่าระบบกลาง',
            'reports' => 'รายงานและสถิติ',
            'security' => 'ความปลอดภัยระบบ',
            default => ucfirst(str_replace('_', ' ', $this->module)),
        };
    }

    public function getModuleIconAttribute(): string
    {
        return match ($this->module) {
            'auth', 'security' => 'bi-shield-lock',
            'repairs' => 'bi-tools',
            'data_requests' => 'bi-file-earmark-bar-graph',
            'assets' => 'bi-pc-display',
            'spare_parts' => 'bi-box-seam',
            'backups' => 'bi-database-gear',
            'users' => 'bi-people',
            'departments' => 'bi-diagram-3',
            'settings' => 'bi-sliders',
            'reports' => 'bi-pie-chart',
            default => 'bi-journal-text',
        };
    }

    public function getBrowserInfoAttribute(): string
    {
        $ua = $this->user_agent;
        if (empty($ua)) {
            return 'ไม่ระบุ';
        }

        $platform = 'ไม่ทราบระบบ';
        if (str_contains($ua, 'Windows NT 10.0')) {
            $platform = 'Windows 10/11';
        } elseif (str_contains($ua, 'Windows')) {
            $platform = 'Windows';
        } elseif (str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS X')) {
            $platform = 'macOS';
        } elseif (str_contains($ua, 'Android')) {
            $platform = 'Android';
        } elseif (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) {
            $platform = 'iOS';
        } elseif (str_contains($ua, 'Linux')) {
            $platform = 'Linux';
        }

        $browser = 'เบราว์เซอร์';
        if (str_contains($ua, 'Edg/')) {
            $browser = 'Edge';
        } elseif (str_contains($ua, 'Chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($ua, 'Firefox/')) {
            $browser = 'Firefox';
        }

        return "{$browser} ({$platform})";
    }
}
