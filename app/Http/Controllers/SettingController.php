<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Services\LineNotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            // Hospital Info
            'hospital_name_th' => setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง'),
            'hospital_name_en' => setting('hospital_name_en', 'Thung Hua Chang Hospital'),
            'hospital_code' => setting('hospital_code', '11143'),
            'department_name' => setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล'),
            'it_head_name' => setting('it_head_name', 'หัวหน้ากลุ่มงานสุขภาพดิจิทัล'),
            'director_name' => setting('director_name', 'ผู้อำนวยการโรงพยาบาลทุ่งหัวช้าง'),
            'hospital_phone' => setting('hospital_phone', '053-595055'),
            'hospital_address' => setting('hospital_address', 'ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160'),

            // Helpdesk & SLA
            'sla_low' => setting('sla_low', 48),
            'sla_normal' => setting('sla_normal', 24),
            'sla_high' => setting('sla_high', 4),
            'sla_critical' => setting('sla_critical', 1),
            'ticket_prefix' => setting('ticket_prefix', 'REP'),
            'default_min_stock' => setting('default_min_stock', 3),

            // Assets & Inventory
            'fiscal_year_current' => setting('fiscal_year_current', 2568),
            'asset_code_prefix' => setting('asset_code_prefix', 'THC-COM'),
            'enforce_ict_standard' => setting('enforce_ict_standard', true),

            // Notification
            'line_notify_token' => setting('line_notify_token', ''),
            'line_notify_enabled' => setting('line_notify_enabled', false),
            'notify_on_new_ticket' => setting('notify_on_new_ticket', true),
            'notify_on_critical_only' => setting('notify_on_critical_only', false),
            'notify_email_admin' => setting('notify_email_admin', ''),

            // SMTP & Email
            'mail_mailer' => setting('mail_mailer', config('mail.default', 'smtp')),
            'mail_host' => setting('mail_host', config('mail.mailers.smtp.host', 'smtp.gmail.com')),
            'mail_port' => setting('mail_port', config('mail.mailers.smtp.port', 587)),
            'mail_username' => setting('mail_username', config('mail.mailers.smtp.username', '')),
            'mail_password' => setting('mail_password', config('mail.mailers.smtp.password', '')),
            'mail_encryption' => setting('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'mail_from_address' => setting('mail_from_address', config('mail.from.address', 'noreply@thchospital.go.th')),
            'mail_from_name' => setting('mail_from_name', setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง')),

            // Security & Auth
            'allow_thai_id_login' => setting('allow_thai_id_login', true),
            'enforce_mfa_all' => setting('enforce_mfa_all', false),
            'backup_retention_days' => setting('backup_retention_days', 30),
            'audit_log_retention_days' => setting('audit_log_retention_days', 90),
            'google_client_id' => setting('google_client_id', ''),
            'google_client_secret' => setting('google_client_secret', ''),
            'google_redirect_uri' => setting('google_redirect_uri', ''),
            'thaid_client_id' => setting('thaid_client_id', ''),
            'thaid_client_secret' => setting('thaid_client_secret', ''),

            // System Version
            'app_version' => setting('app_version', config('version.version', '2.2.1')),
        ];

        $systemStats = [
            'repairs' => \App\Models\Repair::count(),
            'data_requests' => \App\Models\DataRequest::count(),
            'audit_logs' => \App\Models\AuditLog::count(),
            'assets' => \App\Models\Asset::count(),
            'spare_parts' => \App\Models\SparePart::count(),
            'users' => \App\Models\User::count(),
            'backups' => \App\Models\BackupLog::count(),
        ];

        $versionInfo = app_version_info();

        return view('settings.index', compact('settings', 'systemStats', 'versionInfo'));
    }

    public function update(Request $request)
    {
        $group = $request->input('setting_group', 'general');

        if ($group === 'hospital') {
            $data = $request->validate([
                'hospital_name_th' => 'required|string|max:255',
                'hospital_name_en' => 'nullable|string|max:255',
                'hospital_code' => 'required|string|max:20',
                'department_name' => 'required|string|max:255',
                'it_head_name' => 'nullable|string|max:255',
                'director_name' => 'nullable|string|max:255',
                'hospital_phone' => 'nullable|string|max:50',
                'hospital_address' => 'nullable|string|max:500',
            ]);

            foreach ($data as $k => $v) {
                SystemSetting::set($k, $v, 'hospital', 'text');
            }
        } elseif ($group === 'helpdesk') {
            $data = $request->validate([
                'sla_low' => 'required|integer|min:1',
                'sla_normal' => 'required|integer|min:1',
                'sla_high' => 'required|integer|min:1',
                'sla_critical' => 'required|integer|min:1',
                'default_min_stock' => 'required|integer|min:1',
                'ticket_prefix' => 'nullable|string|max:20',
            ]);

            foreach ($data as $k => $v) {
                $type = in_array($k, ['sla_low', 'sla_normal', 'sla_high', 'sla_critical', 'default_min_stock']) ? 'integer' : 'text';
                SystemSetting::set($k, $v, 'helpdesk', $type);
            }
        } elseif ($group === 'assets') {
            $data = $request->validate([
                'fiscal_year_current' => 'required|integer|min:2500|max:2600',
                'asset_code_prefix' => 'required|string|max:30',
                'default_min_stock' => 'required|integer|min:1',
            ]);

            SystemSetting::set('fiscal_year_current', (int)$data['fiscal_year_current'], 'assets', 'integer');
            SystemSetting::set('asset_code_prefix', $data['asset_code_prefix'], 'assets', 'text');
            SystemSetting::set('default_min_stock', (int)$data['default_min_stock'], 'assets', 'integer');
            SystemSetting::set('enforce_ict_standard', $request->boolean('enforce_ict_standard'), 'assets', 'boolean');
        } elseif ($group === 'notification') {
            $lineToken = $request->input('line_notify_token', '');
            $lineEnabled = $request->boolean('line_notify_enabled');
            $notifyNew = $request->boolean('notify_on_new_ticket');
            $notifyCritical = $request->boolean('notify_on_critical_only');
            $notifyEmail = $request->input('notify_email_admin', '');

            SystemSetting::set('line_notify_token', $lineToken, 'notification', 'text');
            SystemSetting::set('line_notify_enabled', $lineEnabled, 'notification', 'boolean');
            SystemSetting::set('notify_on_new_ticket', $notifyNew, 'notification', 'boolean');
            SystemSetting::set('notify_on_critical_only', $notifyCritical, 'notification', 'boolean');
            SystemSetting::set('notify_email_admin', $notifyEmail, 'notification', 'text');
        } elseif ($group === 'mail') {
            $mailData = [
                'mail_mailer' => $request->input('mail_mailer', 'smtp'),
                'mail_host' => $request->input('mail_host', 'smtp.gmail.com'),
                'mail_port' => (int) $request->input('mail_port', 587),
                'mail_username' => $request->input('mail_username', ''),
                'mail_password' => $request->input('mail_password', ''),
                'mail_encryption' => $request->input('mail_encryption', 'tls'),
                'mail_from_address' => $request->input('mail_from_address', 'noreply@thchospital.go.th'),
                'mail_from_name' => $request->input('mail_from_name', setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง')),
            ];

            foreach ($mailData as $k => $v) {
                $type = is_int($v) ? 'integer' : 'text';
                SystemSetting::set($k, $v, 'mail', $type);
            }
        } elseif ($group === 'security') {
            $allowThaiId = $request->boolean('allow_thai_id_login');
            $enforceMfa = $request->boolean('enforce_mfa_all');
            $backupRetention = (int) $request->input('backup_retention_days', 30);
            $auditRetention = (int) $request->input('audit_log_retention_days', 90);

            SystemSetting::set('allow_thai_id_login', $allowThaiId, 'security', 'boolean');
            SystemSetting::set('enforce_mfa_all', $enforceMfa, 'security', 'boolean');
            SystemSetting::set('backup_retention_days', $backupRetention, 'security', 'integer');
            SystemSetting::set('audit_log_retention_days', $auditRetention, 'security', 'integer');

            SystemSetting::set('google_client_id', $request->input('google_client_id', ''), 'security', 'text');
            SystemSetting::set('google_client_secret', $request->input('google_client_secret', ''), 'security', 'text');
            SystemSetting::set('google_redirect_uri', $request->input('google_redirect_uri', ''), 'security', 'text');
            SystemSetting::set('thaid_client_id', $request->input('thaid_client_id', ''), 'security', 'text');
            SystemSetting::set('thaid_client_secret', $request->input('thaid_client_secret', ''), 'security', 'text');
        } elseif ($group === 'version') {
            $data = $request->validate([
                'app_version' => 'required|string|max:50',
            ]);

            SystemSetting::set('app_version', $data['app_version'], 'version', 'text', 'เวอร์ชันระบบสารสนเทศ');
        }

        return back()->with('success', 'บันทึกการตั้งค่าระบบเรียบร้อยแล้ว');
    }

    /**
     * Test LINE Notify Token
     */
    public function testLineNotify(Request $request)
    {
        $token = $request->input('token') ?: setting('line_notify_token');
        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบ LINE Notify Token กรุณากรอก Token ก่อนทดสอบส่งข้อความ',
            ], 422);
        }

        $user = auth()->user();
        $senderName = $user ? $user->name : 'Admin';

        $result = LineNotificationService::sendTestMessage($token, $senderName);

        return response()->json($result);
    }

    /**
     * Test SMTP Email configuration
     */
    public function testMail(Request $request)
    {
        $targetEmail = $request->input('email');
        if (empty($targetEmail)) {
            $targetEmail = auth()->user()?->email;
        }

        if (empty($targetEmail) || !filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุที่อยู่อีเมลผู้รับที่ถูกต้องสำหรับการทดสอบ',
            ], 422);
        }

        try {
            $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
            $deptName = setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล');

            // Apply runtime mail config from settings
            $mailer = setting('mail_mailer') ?: config('mail.default', 'smtp');
            $host = setting('mail_host') ?: config('mail.mailers.smtp.host');
            $port = setting('mail_port') ?: config('mail.mailers.smtp.port', 587);
            $username = setting('mail_username') ?: config('mail.mailers.smtp.username');
            $password = setting('mail_password') ?: config('mail.mailers.smtp.password');
            $encryption = setting('mail_encryption') ?: config('mail.mailers.smtp.encryption', 'tls');
            $fromAddress = setting('mail_from_address') ?: config('mail.from.address', 'noreply@thchospital.go.th');
            $fromName = setting('mail_from_name') ?: setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');

            config([
                'mail.default' => $mailer,
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            Mail::raw("นี่คืออีเมลทดสอบการเชื่อมต่อระบบ SMTP จาก {$hospitalName} ({$deptName})\nส่งเมื่อ: " . now()->toDateTimeString() . "\nหากท่านได้รับอีเมลนี้ แสดงว่าการตั้งค่าอีเมลทำงานได้ถูกต้องสมบูรณ์", function ($msg) use ($targetEmail, $fromAddress, $fromName, $hospitalName) {
                $msg->to($targetEmail)
                    ->subject("ทดสอบระบบอีเมลแจ้งเตือน - {$hospitalName}")
                    ->from($fromAddress, $fromName);
            });

            return response()->json([
                'success' => true,
                'message' => "ส่งอีเมลทดสอบไปยัง {$targetEmail} สำเร็จเรียบร้อยแล้ว",
            ]);
        } catch (Throwable $e) {
            Log::error('Mail test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถส่งอีเมลได้: ' . $e->getMessage(),
            ], 500);
        }
    }
}
