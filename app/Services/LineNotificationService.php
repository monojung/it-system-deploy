<?php

namespace App\Services;

use App\Models\Repair;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LineNotificationService
{
    /**
     * Send message via LINE Notify API
     *
     * @param string $token
     * @param string $message
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendLineMessage(string $token, string $message): array
    {
        $token = trim($token);
        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'ไม่พบ LINE Notify Token กรุณากำหนด Token ก่อนทดสอบส่งข้อความ',
            ];
        }

        try {
            $response = Http::withToken($token)
                ->asForm()
                ->timeout(10)
                ->post('https://notify-api.line.me/api/notify', [
                    'message' => $message,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'ส่งข้อความแจ้งเตือนเข้า LINE สำเร็จเรียบร้อยแล้ว',
                ];
            }

            $errorMsg = $response->json('message') ?? 'HTTP status ' . $response->status();
            Log::warning('LINE Notify API failed: ' . $errorMsg);

            return [
                'success' => false,
                'message' => 'ส่งข้อความไม่สำเร็จจาก LINE: ' . $errorMsg,
            ];
        } catch (Throwable $e) {
            Log::error('LINE Notify Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ LINE ได้: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send notification for a new repair ticket based on system settings
     *
     * @param Repair $repair
     * @return bool
     */
    public static function sendRepairTicketNotification(Repair $repair): bool
    {
        $enabled = (bool) setting('line_notify_enabled', false);
        if (!$enabled) {
            return false;
        }

        $token = setting('line_notify_token', '');
        if (empty($token)) {
            return false;
        }

        $criticalOnly = (bool) setting('notify_on_critical_only', false);
        if ($criticalOnly && !in_array($repair->urgency, ['high', 'critical'])) {
            return false;
        }

        $notifyOnNew = (bool) setting('notify_on_new_ticket', true);
        if (!$notifyOnNew) {
            return false;
        }

        $urgencyIcons = [
            'low' => '🟢 ปกติ (Low)',
            'normal' => '🔵 ปานกลาง (Normal)',
            'high' => '🟡 ด่วน (High)',
            'critical' => '🔴 ด่วนที่สุด (CRITICAL)',
        ];

        $urgencyText = $urgencyIcons[$repair->urgency] ?? $repair->urgency;
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $department = $repair->department ? $repair->department->name : '-';
        $slaHours = $repair->sla_hours;

        $assetInfo = $repair->asset
            ? "{$repair->asset->asset_code} ({$repair->asset->name})"
            : ($repair->other_device_info ?: 'ไม่ระบุครุภัณฑ์');

        $viewUrl = route('repairs.show', $repair);

        $msg = "\n🔔 มีงานแจ้งซ่อมบำรุงใหม่ ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🎫 รหัสตั๋ว: {$repair->ticket_number}\n";
        $msg .= "📌 หัวข้อ: {$repair->title}\n";
        $msg .= "🚨 ความเร่งด่วน: {$urgencyText}\n";
        $msg .= "⏱️ เป้าหมาย SLA: {$slaHours} ชม.\n";
        $msg .= "🏢 แผนก: {$department}\n";
        $msg .= "📍 สถานที่: " . ($repair->location_detail ?: '-') . "\n";
        $msg .= "💻 อุปกรณ์: {$assetInfo}\n";
        $msg .= "👤 ผู้แจ้ง: {$repair->requester_name} (โทร: {$repair->requester_phone})\n";
        $msg .= "🔗 รายละเอียด: {$viewUrl}";

        $result = self::sendLineMessage($token, $msg);
        return $result['success'];
    }

    /**
     * Send a quick test notification
     *
     * @param string $token
     * @param string $senderName
     * @return array
     */
    public static function sendTestMessage(string $token, string $senderName = 'Admin'): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $departmentName = setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล');
        $now = now()->addYears(543)->format('d/m/Y H:i:s');

        $msg = "\n🧪 ทดสอบการเชื่อมต่อระบบแจ้งเตือน ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "✅ การเชื่อมต่อ LINE Notify สำเร็จสมบูรณ์!\n";
        $msg .= "🏢 หน่วยงาน: {$departmentName}\n";
        $msg .= "👤 ผู้ทดสอบ: {$senderName}\n";
        $msg .= "🕒 เวลาทดสอบ: {$now} น.\n";
        $msg .= "💡 ระบบพร้อมส่งการแจ้งเตือนงานซ่อมและกิจกรรมสำคัญแล้วครับ";

        return self::sendLineMessage($token, $msg);
    }
}
