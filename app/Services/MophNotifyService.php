<?php

namespace App\Services;

use App\Models\Repair;
use App\Models\AssetBorrow;
use App\Models\DataRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MophNotifyService
{
    const DEFAULT_PROD_ENDPOINT = 'https://morpromt2c.moph.go.th/api/notify/send';
    const DEFAULT_UAT_ENDPOINT = 'https://morpromt2f.moph.go.th/api/notify/send';

    /**
     * Send payload to MOPH Notify API
     *
     * @param array $payload
     * @param string|null $endpoint
     * @param string|null $clientKey
     * @param string|null $secretKey
     * @return array ['success' => bool, 'message' => string, 'status' => int]
     */
    public static function sendMophMessage(
        array $payload,
        ?string $endpoint = null,
        ?string $clientKey = null,
        ?string $secretKey = null
    ): array {
        $endpoint = trim($endpoint ?: setting('moph_notify_endpoint', self::DEFAULT_PROD_ENDPOINT));
        $clientKey = trim($clientKey ?: setting('moph_notify_client_key', ''));
        $secretKey = trim($secretKey ?: setting('moph_notify_secret_key', ''));

        if (empty($endpoint)) {
            return [
                'success' => false,
                'message' => 'ไม่พบ API Endpoint ของ MOPH Notify กรุณากำหนด URL ก่อนส่ง',
                'status' => 400,
            ];
        }

        if (empty($clientKey) || empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'ไม่พบ Client Key หรือ Secret Key ของ MOPH Notify กรุณาระบุให้ครบถ้วน',
                'status' => 400,
            ];
        }

        $result = self::executeHttpSend($payload, $endpoint, $clientKey, $secretKey);

        // If sending flex message failed (e.g. status 400 Bad Request or LINE flex rejected by endpoint),
        // automatically fallback to Plain Text so notification is NEVER lost!
        if (!$result['success'] && isset($payload['messages'][0]['type']) && $payload['messages'][0]['type'] === 'flex') {
            Log::warning("MOPH Notify Flex Message failed (Status: {$result['status']}), attempting automatic Plain Text fallback...");

            $altText = $payload['messages'][0]['altText'] ?? '🔔 แจ้งเตือนจากระบบสารสนเทศ รพ.ทุ่งหัวช้าง';
            $fallbackPayload = [
                'messages' => [
                    ['type' => 'text', 'text' => $altText]
                ]
            ];
            $fallbackResult = self::executeHttpSend($fallbackPayload, $endpoint, $clientKey, $secretKey);
            if ($fallbackResult['success']) {
                $fallbackResult['message'] .= ' (ส่งผ่านโหมดข้อความธรรมดาสำรองอัตโนมัติ เนื่องจากปลายทางไม่รองรับ Flex)';
                return $fallbackResult;
            }
        }

        return $result;
    }

    /**
     * Execute HTTP POST to MOPH Notify API with timeout and SSL flexibility
     */
    protected static function executeHttpSend(
        array $payload,
        string $endpoint,
        string $clientKey,
        string $secretKey
    ): array {
        try {
            $version = config('version.version', '2.4.4');
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key' => $clientKey,
                    'secret-key' => $secretKey,
                    'User-Agent' => "MOPH-IT-System/{$version} (ThungHuaChang Hospital)",
                ])
                ->timeout(15)
                ->post($endpoint, $payload);

            if ($response->successful()) {
                Log::info("MOPH Notify message sent successfully to {$endpoint}");
                return [
                    'success' => true,
                    'message' => 'ส่งการแจ้งเตือนผ่าน MOPH Notify (หมอพร้อม LINE OA) สำเร็จเรียบร้อยแล้ว',
                    'status' => $response->status(),
                    'response' => $response->json(),
                ];
            }

            $errorBody = $response->json('message') ?? $response->body();
            $statusCode = $response->status();
            Log::warning("MOPH Notify API returned status {$statusCode}: {$errorBody}");

            return [
                'success' => false,
                'message' => "MOPH Notify API ตอบกลับข้อผิดพลาด (HTTP {$statusCode}): " . ($errorBody ?: 'ไม่ทราบสาเหตุ'),
                'status' => $statusCode,
            ];
        } catch (Throwable $e) {
            Log::error('MOPH Notify Connection Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ MOPH Notify ได้: ' . $e->getMessage(),
                'status' => 500,
            ];
        }
    }

    /**
     * Generate a public, LINE-compliant URL for actions
     * (Avoids localhost/127.0.0.1 which causes LINE API validation rejection)
     */
    public static function getSafePublicUrl(string $path): string
    {
        $base = url('/');
        if (preg_match('/localhost|127\.0\.0\.1|::1/i', $base)) {
            $fallbackDomain = setting('hospital_public_url') ?: 'https://thchospital.moph.go.th/it-system';
            return rtrim($fallbackDomain, '/') . '/' . ltrim($path, '/');
        }
        return url($path);
    }

    /**
     * Send notification for a new repair ticket
     *
     * @param Repair $repair
     * @return bool
     */
    public static function sendRepairTicketNotification(Repair $repair): bool
    {
        $enabled = (bool) setting('moph_notify_enabled', true);
        if (!$enabled) {
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

        $format = setting('moph_notify_message_type', 'flex');

        if ($format === 'flex') {
            $flex = self::buildRepairTicketFlexMessage($repair);
            $payload = [
                'messages' => [$flex]
            ];
        } else {
            $text = self::buildRepairTicketText($repair);
            $payload = [
                'messages' => [
                    ['type' => 'text', 'text' => $text]
                ]
            ];
        }

        $res = self::sendMophMessage($payload);
        return $res['success'];
    }

    /**
     * Send notification for repair ticket status update
     *
     * @param Repair $repair
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $comment
     * @return bool
     */
    public static function sendRepairStatusNotification(
        Repair $repair,
        string $oldStatus,
        string $newStatus,
        ?string $comment = null
    ): bool {
        $enabled = (bool) setting('moph_notify_enabled', true);
        if (!$enabled) {
            return false;
        }

        $notifyOnStatus = (bool) setting('notify_on_status_change', true);
        if (!$notifyOnStatus) {
            return false;
        }

        $format = setting('moph_notify_message_type', 'flex');

        if ($format === 'flex') {
            $flex = self::buildStatusChangeFlexMessage($repair, $oldStatus, $newStatus, $comment);
            $payload = [
                'messages' => [$flex]
            ];
        } else {
            $text = self::buildStatusChangeText($repair, $oldStatus, $newStatus, $comment);
            $payload = [
                'messages' => [
                    ['type' => 'text', 'text' => $text]
                ]
            ];
        }

        $res = self::sendMophMessage($payload);
        return $res['success'];
    }

    /**
     * Send notification for asset borrow events
     *
     * @param AssetBorrow $borrow
     * @param string $action 'created', 'approved', 'rejected', 'dispatched', 'returned'
     * @return bool
     */
    public static function sendAssetBorrowNotification(AssetBorrow $borrow, string $action = 'created'): bool
    {
        $enabled = (bool) setting('moph_notify_enabled', true);
        if (!$enabled) {
            return false;
        }

        $notifyOnBorrow = (bool) setting('notify_on_borrow_request', true);
        if (!$notifyOnBorrow) {
            return false;
        }

        $format = setting('moph_notify_message_type', 'flex');

        if ($format === 'flex') {
            $flex = self::buildAssetBorrowFlexMessage($borrow, $action);
            $payload = [
                'messages' => [$flex]
            ];
        } else {
            $text = self::buildAssetBorrowText($borrow, $action);
            $payload = [
                'messages' => [
                    ['type' => 'text', 'text' => $text]
                ]
            ];
        }

        $res = self::sendMophMessage($payload);
        return $res['success'];
    }

    /**
     * Send notification for data request events
     *
     * @param DataRequest $dataRequest
     * @param string $action 'created', 'approved', 'in_progress', 'rejected', 'completed'
     * @param string|null $comment
     * @return bool
     */
    public static function sendDataRequestNotification(DataRequest $dataRequest, string $action = 'created', ?string $comment = null): bool
    {
        $enabled = (bool) setting('moph_notify_enabled', true);
        if (!$enabled) {
            return false;
        }

        $notifyOnData = (bool) setting('notify_on_data_request', true);
        if (!$notifyOnData) {
            return false;
        }

        $criticalOnly = (bool) setting('notify_on_critical_only', false);
        if ($criticalOnly && !in_array($dataRequest->urgency, ['urgent', 'very_urgent'])) {
            return false;
        }

        $format = setting('moph_notify_message_type', 'flex');

        if ($format === 'flex') {
            $flex = self::buildDataRequestFlexMessage($dataRequest, $action, $comment);
            $payload = [
                'messages' => [$flex]
            ];
        } else {
            $text = self::buildDataRequestText($dataRequest, $action, $comment);
            $payload = [
                'messages' => [
                    ['type' => 'text', 'text' => $text]
                ]
            ];
        }

        $res = self::sendMophMessage($payload);
        return $res['success'];
    }

    /**
     * Send test notification (Supports both Flex and Plain Text mode)
     *
     * @param string $senderName
     * @param string|null $endpoint
     * @param string|null $clientKey
     * @param string|null $secretKey
     * @param string|null $messageType
     * @return array
     */
    public static function sendTestMessage(
        string $senderName = 'Admin',
        ?string $endpoint = null,
        ?string $clientKey = null,
        ?string $secretKey = null,
        ?string $messageType = null
    ): array {
        $messageType = $messageType ?: setting('moph_notify_message_type', 'flex');

        if ($messageType === 'text') {
            $text = self::buildTestText($senderName);
            $payload = [
                'messages' => [
                    ['type' => 'text', 'text' => $text]
                ]
            ];
        } else {
            $flex = self::buildTestFlexMessage($senderName);
            $payload = [
                'messages' => [$flex]
            ];
        }

        return self::sendMophMessage($payload, $endpoint, $clientKey, $secretKey);
    }

    /**
     * Build Plain Text for 1-Click Test
     *
     * @param string $senderName
     * @return string
     */
    public static function buildTestText(string $senderName = 'Admin'): string
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $departmentName = setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล');
        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $systemUrl = self::getSafePublicUrl('/dashboard');

        return "🧪 [ทดสอบระบบ MOPH Notify]\n"
            . "🏥 {$hospitalName}\n"
            . "--------------------------------\n"
            . "✅ การเชื่อมต่อระบบสำเร็จสมบูรณ์!\n"
            . "ระบบสารสนเทศพร้อมส่งการแจ้งเตือนผ่านช่องทาง MOPH Notify (หมอพร้อม LINE OA)\n"
            . "🏢 หน่วยงาน: {$departmentName}\n"
            . "👤 ผู้ทดสอบ: {$senderName}\n"
            . "🕒 เวลา: {$nowThai} น.\n"
            . "🌐 ระบบ: {$systemUrl}";
    }

    /**
     * Build LINE Flex Message for New Repair Ticket
     *
     * @param Repair $repair
     * @return array
     */
    public static function buildRepairTicketFlexMessage(Repair $repair): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $departmentName = $repair->department ? $repair->department->name : '-';
        $slaHours = $repair->sla_hours ?: 24;

        $urgencyConfig = [
            'low' => ['label' => '🟢 ปกติ (Low)', 'color' => '#16a34a', 'bg' => '#f0fdf4'],
            'normal' => ['label' => '🔵 ปานกลาง (Normal)', 'color' => '#0284c7', 'bg' => '#f0f9ff'],
            'high' => ['label' => '🟡 ด่วน (High)', 'color' => '#ea580c', 'bg' => '#fff7ed'],
            'critical' => ['label' => '🔴 ด่วนที่สุด (CRITICAL)', 'color' => '#dc2626', 'bg' => '#fef2f2'],
        ];
        $urgency = $urgencyConfig[$repair->urgency] ?? ['label' => $repair->urgency, 'color' => '#0d9488', 'bg' => '#f0fdfa'];

        $assetInfo = $repair->asset
            ? "{$repair->asset->asset_code} ({$repair->asset->name})"
            : ($repair->other_device_info ?: 'ไม่ระบุครุภัณฑ์');

        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $viewUrl = self::getSafePublicUrl('/repairs/' . $repair->id);

        return [
            'type' => 'flex',
            'altText' => "🔔 แจ้งซ่อมใหม่ #{$repair->ticket_number}: {$repair->title} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $urgency['color'],
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'alignItems' => 'center',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '🔔 แจ้งซ่อมบำรุงไอทีใหม่',
                                    'color' => '#ffffff',
                                    'weight' => 'bold',
                                    'size' => 'md',
                                    'flex' => 1,
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $urgency['label'],
                                    'color' => '#ffffff',
                                    'weight' => 'bold',
                                    'size' => 'xs',
                                    'align' => 'end',
                                ]
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => "{$hospitalName} • ฝ่ายสุขภาพดิจิทัล",
                            'color' => '#ffffffd9',
                            'size' => 'xxs',
                            'margin' => 'xs',
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => "#{$repair->ticket_number}",
                            'size' => 'xs',
                            'color' => '#0d9488',
                            'weight' => 'bold',
                        ],
                        [
                            'type' => 'text',
                            'text' => $repair->title,
                            'size' => 'md',
                            'weight' => 'bold',
                            'color' => '#0f172a',
                            'wrap' => true,
                            'margin' => 'xs',
                        ],
                        [
                            'type' => 'separator',
                            'margin' => 'md',
                            'color' => '#e2e8f0',
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'md',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '🏢 แผนก/หน่วย:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => $departmentName, 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8, 'wrap' => true],
                                    ]
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '📍 สถานที่:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => $repair->location_detail ?: '-', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                                    ]
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '💻 อุปกรณ์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => $assetInfo, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                                    ]
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '⏱️ เป้าหมาย SLA:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => "ภายใน {$slaHours} ชั่วโมง", 'size' => 'xs', 'color' => '#0369a1', 'weight' => 'bold', 'flex' => 8],
                                    ]
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '👤 ผู้แจ้ง:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => "{$repair->requester_name} (โทร: {$repair->requester_phone})", 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                                    ]
                                ],
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'md',
                            'paddingAll' => '10px',
                            'backgroundColor' => '#f8fafc',
                            'cornerRadius' => '8px',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '📝 รายละเอียดอาการเสีย:',
                                    'size' => 'xxs',
                                    'color' => '#64748b',
                                    'weight' => 'bold',
                                ],
                                [
                                    'type' => 'text',
                                    'text' => mb_strimwidth($repair->description ?: 'ไม่มีรายละเอียดเพิ่มเติม', 0, 160, '...'),
                                    'size' => 'xs',
                                    'color' => '#334155',
                                    'wrap' => true,
                                    'margin' => 'xs',
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'md',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => "🕒 {$nowThai} น.",
                                    'size' => 'xxs',
                                    'color' => '#94a3b8',
                                ]
                            ]
                        ]
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => [
                                'type' => 'uri',
                                'label' => '🔎 ดูรายละเอียดและรับงานซ่อม',
                                'uri' => $viewUrl,
                            ],
                            'style' => 'primary',
                            'color' => '#0d9488',
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build Flex Message for Repair Status Change
     *
     * @param Repair $repair
     * @param string $oldStatus
     * @param string $newStatus
     * @param string|null $comment
     * @return array
     */
    public static function buildStatusChangeFlexMessage(
        Repair $repair,
        string $oldStatus,
        string $newStatus,
        ?string $comment = null
    ): array {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $statusLabels = [
            'pending' => ['label' => 'รอดำเนินการ', 'color' => '#eab308'],
            'in_progress' => ['label' => 'กำลังดำเนินการซ่อม', 'color' => '#3b82f6'],
            'waiting_parts' => ['label' => 'รอเบิก/สั่งอะไหล่', 'color' => '#f97316'],
            'completed' => ['label' => 'ซ่อมบำรุงเสร็จสิ้น', 'color' => '#10b981'],
            'external' => ['label' => 'ส่งซ่อมหน่วยงานภายนอก', 'color' => '#8b5cf6'],
            'cancelled' => ['label' => 'ยกเลิกรายการ', 'color' => '#64748b'],
        ];

        $target = $statusLabels[$newStatus] ?? ['label' => $newStatus, 'color' => '#0d9488'];
        $oldLabel = $statusLabels[$oldStatus]['label'] ?? $oldStatus;
        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $viewUrl = self::getSafePublicUrl('/repairs/' . $repair->id);

        return [
            'type' => 'flex',
            'altText' => "🔄 อัปเดตสถานะ #{$repair->ticket_number} -> {$target['label']} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $target['color'],
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => "🔄 อัปเดตสถานะงานซ่อม #{$repair->ticket_number}",
                            'color' => '#ffffff',
                            'weight' => 'bold',
                            'size' => 'md',
                        ],
                        [
                            'type' => 'text',
                            'text' => "สถานะปัจจุบัน: {$target['label']}",
                            'color' => '#ffffffe6',
                            'size' => 'xs',
                            'margin' => 'xs',
                            'weight' => 'bold',
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => $repair->title,
                            'weight' => 'bold',
                            'size' => 'sm',
                            'color' => '#0f172a',
                            'wrap' => true,
                        ],
                        [
                            'type' => 'text',
                            'text' => "เปลี่ยนจาก: '{$oldLabel}' ➔ '{$target['label']}'",
                            'size' => 'xs',
                            'color' => '#64748b',
                            'margin' => 'xs',
                        ],
                        [
                            'type' => 'separator',
                            'margin' => 'md',
                            'color' => '#e2e8f0',
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'md',
                            'paddingAll' => '10px',
                            'backgroundColor' => '#f8fafc',
                            'cornerRadius' => '8px',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '💬 บันทึกความคืบหน้า / วิธีแก้ไข:',
                                    'size' => 'xxs',
                                    'color' => '#64748b',
                                    'weight' => 'bold',
                                ],
                                [
                                    'type' => 'text',
                                    'text' => $comment ?: ($repair->solution ?: 'ช่างได้อัปเดตสถานะงานซ่อมเรียบร้อยแล้ว'),
                                    'size' => 'xs',
                                    'color' => '#334155',
                                    'wrap' => true,
                                    'margin' => 'xs',
                                ]
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => "🕒 เวลา: {$nowThai} น.",
                            'size' => 'xxs',
                            'color' => '#94a3b8',
                            'margin' => 'md',
                        ]
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => [
                                'type' => 'uri',
                                'label' => '🔎 ตรวจสอบรายละเอียดใบงาน',
                                'uri' => $viewUrl,
                            ],
                            'style' => 'primary',
                            'color' => '#0d9488',
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build Flex Message for Asset Borrowing (All Lifecycle Stages)
     *
     * @param AssetBorrow $borrow
     * @param string $action 'created', 'approved', 'rejected', 'dispatched', 'returned'
     * @return array
     */
    public static function buildAssetBorrowFlexMessage(AssetBorrow $borrow, string $action = 'created'): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');

        switch ($action) {
            case 'approved':
                $title = '✅ อนุมัติคำขอยืมอุปกรณ์แล้ว';
                $headerColor = '#059669';
                $btnLabel = '📦 ดูรายละเอียดและส่งมอบอุปกรณ์';
                break;
            case 'rejected':
                $title = '❌ ปฏิเสธคำขอยืมอุปกรณ์ไอที';
                $headerColor = '#dc2626';
                $btnLabel = '🔎 ดูรายละเอียดคำขอยืม';
                break;
            case 'dispatched':
                $title = '🚀 ส่งมอบอุปกรณ์แล้ว (กำลังยืมใช้งาน)';
                $headerColor = '#7c3aed';
                $btnLabel = '🔎 ดูประวัติและกำหนดคืน';
                break;
            case 'returned':
                $title = '📥 ส่งคืนอุปกรณ์เรียบร้อยแล้ว';
                $headerColor = '#0d9488';
                $btnLabel = '🔎 ดูรายละเอียดการส่งคืน';
                break;
            case 'created':
            default:
                $title = '📦 มีคำขอยืมอุปกรณ์ไอทีใหม่';
                $headerColor = '#0284c7';
                $btnLabel = '🔎 ตรวจสอบและจัดการคำขอยืม';
                break;
        }

        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $asset = $borrow->asset;
        $assetName = $asset ? "{$asset->asset_code} ({$asset->name})" : 'ไม่ระบุอุปกรณ์';
        $viewUrl = self::getSafePublicUrl('/asset-borrows/' . $borrow->id);

        $bodyContents = [
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '👤 ผู้ขอยืม:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => "{$borrow->borrower_name} (โทร: " . ($borrow->contact_phone ?: '-') . ")", 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '🏢 แผนก/หน่วย:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $borrow->department ? $borrow->department->name : '-', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '💻 ครุภัณฑ์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $assetName, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '🎯 วัตถุประสงค์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $borrow->purpose ?: '-', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '📅 กำหนดคืน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : 'ไม่ระบุ', 'size' => 'xs', 'color' => '#ea580c', 'weight' => 'bold', 'flex' => 8],
                ]
            ],
        ];

        if ($action === 'rejected' && $borrow->rejection_reason) {
            $bodyContents[] = [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '❗ เหตุผลที่ปฏิเสธ:', 'size' => 'xs', 'color' => '#dc2626', 'flex' => 4],
                    ['type' => 'text', 'text' => $borrow->rejection_reason, 'size' => 'xs', 'color' => '#dc2626', 'weight' => 'bold', 'flex' => 8, 'wrap' => true],
                ]
            ];
        }

        if ($action === 'dispatched' && $borrow->dispatch_condition) {
            $bodyContents[] = [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '📦 สภาพส่งมอบ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $borrow->dispatch_condition, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                ]
            ];
        }

        if ($action === 'returned') {
            if ($borrow->return_condition) {
                $bodyContents[] = [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => '📥 สภาพรับคืน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                        ['type' => 'text', 'text' => $borrow->return_condition, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                    ]
                ];
            }
            if ($borrow->actual_return_date) {
                $bodyContents[] = [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => '🕒 วันที่ส่งคืน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                        ['type' => 'text', 'text' => $borrow->actual_return_date->format('d/m/Y'), 'size' => 'xs', 'color' => '#0d9488', 'weight' => 'bold', 'flex' => 8],
                    ]
                ];
            }
        }

        return [
            'type' => 'flex',
            'altText' => "{$title} #{$borrow->borrow_no} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $headerColor,
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => $title,
                            'color' => '#ffffff',
                            'weight' => 'bold',
                            'size' => 'md',
                        ],
                        [
                            'type' => 'text',
                            'text' => "รหัสใบยืม: #{$borrow->borrow_no}",
                            'color' => '#ffffffe6',
                            'size' => 'xs',
                            'margin' => 'xs',
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'spacing' => 'sm',
                            'contents' => $bodyContents,
                        ],
                        [
                            'type' => 'text',
                            'text' => "🕒 เวลา: {$nowThai} น.",
                            'size' => 'xxs',
                            'color' => '#94a3b8',
                            'margin' => 'md',
                        ]
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => [
                                'type' => 'uri',
                                'label' => $btnLabel,
                                'uri' => $viewUrl,
                            ],
                            'style' => 'primary',
                            'color' => $headerColor,
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build Flex Message for Data Request (All Lifecycle Stages)
     *
     * @param DataRequest $dataRequest
     * @param string $action 'created', 'approved', 'in_progress', 'rejected', 'completed'
     * @param string|null $comment
     * @return array
     */
    public static function buildDataRequestFlexMessage(DataRequest $dataRequest, string $action = 'created', ?string $comment = null): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');

        switch ($action) {
            case 'approved':
                $title = '✅ อนุมัติ/รับเรื่องคำขอข้อมูลสารสนเทศ';
                $headerColor = '#059669';
                $btnLabel = '🔎 ตรวจสอบและดำเนินการสกัดข้อมูล';
                break;
            case 'in_progress':
                $title = '⚙️ กำลังสกัดข้อมูลสารสนเทศ (In Progress)';
                $headerColor = '#d97706';
                $btnLabel = '🔎 ตรวจสอบความคืบหน้า';
                break;
            case 'rejected':
                $title = '❌ ไม่อนุมัติคำขอข้อมูลสารสนเทศ';
                $headerColor = '#dc2626';
                $btnLabel = '🔎 ดูรายละเอียดคำขอข้อมูล';
                break;
            case 'completed':
                $title = '🎉 สกัดข้อมูลเสร็จสิ้นแล้ว (พร้อมดาวน์โหลด)';
                $headerColor = '#0d9488';
                $btnLabel = '📥 เปิดดูและดาวน์โหลดไฟล์ข้อมูล';
                break;
            case 'created':
            default:
                $title = '📄 มีคำขอข้อมูลสารสนเทศใหม่ (Data Request)';
                $headerColor = '#0284c7';
                $btnLabel = '🔎 ตรวจสอบคำขอข้อมูลสารสนเทศ';
                break;
        }

        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $viewUrl = self::getSafePublicUrl('/data-requests/' . $dataRequest->id);
        $requesterName = $dataRequest->user ? $dataRequest->user->name : 'ไม่ระบุผู้ขอ';
        $requesterPhone = $dataRequest->user ? ($dataRequest->user->phone ?: '-') : '-';
        $departmentName = $dataRequest->department ? $dataRequest->department->name : '-';
        $formatName = strtoupper($dataRequest->file_format ?: 'EXCEL');

        $urgencyBadges = [
            'normal' => '🟢 ปกติ',
            'urgent' => '🟡 ด่วน',
            'very_urgent' => '🔴 ด่วนที่สุด',
        ];
        $urgencyText = $urgencyBadges[$dataRequest->urgency] ?? $dataRequest->urgency_label ?? $dataRequest->urgency;

        $bodyContents = [
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '👤 ผู้ยื่นคำขอ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => "{$requesterName} (โทร: {$requesterPhone})", 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '🏢 แผนก/หน่วย:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $departmentName, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '📌 หัวข้อข้อมูล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $dataRequest->title, 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '🎯 วัตถุประสงค์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $dataRequest->objective_label ?: $dataRequest->objective_type, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '🚨 ความเร่งด่วน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $urgencyText, 'size' => 'xs', 'color' => '#ea580c', 'weight' => 'bold', 'flex' => 8],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '💾 รูปแบบไฟล์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $formatName, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'flex' => 8],
                ]
            ],
        ];

        $note = $comment ?: $dataRequest->admin_notes;
        if ($note) {
            $bodyContents[] = [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '💬 บันทึก/ผล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                    ['type' => 'text', 'text' => $note, 'size' => 'xs', 'color' => '#334155', 'flex' => 8, 'wrap' => true],
                ]
            ];
        }

        return [
            'type' => 'flex',
            'altText' => "{$title} #{$dataRequest->request_no} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $headerColor,
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => $title,
                            'color' => '#ffffff',
                            'weight' => 'bold',
                            'size' => 'md',
                        ],
                        [
                            'type' => 'text',
                            'text' => "เลขที่คำขอ: #{$dataRequest->request_no}",
                            'color' => '#ffffffe6',
                            'size' => 'xs',
                            'margin' => 'xs',
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'spacing' => 'sm',
                            'contents' => $bodyContents,
                        ],
                        [
                            'type' => 'text',
                            'text' => "🕒 เวลา: {$nowThai} น.",
                            'size' => 'xxs',
                            'color' => '#94a3b8',
                            'margin' => 'md',
                        ]
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => [
                                'type' => 'uri',
                                'label' => $btnLabel,
                                'uri' => $viewUrl,
                            ],
                            'style' => 'primary',
                            'color' => $headerColor,
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Build Flex Message for 1-Click Test
     *
     * @param string $senderName
     * @return array
     */
    public static function buildTestFlexMessage(string $senderName = 'Admin'): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $departmentName = setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล');
        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $systemUrl = self::getSafePublicUrl('/dashboard');

        return [
            'type' => 'flex',
            'altText' => "🧪 ทดสอบระบบ MOPH Notify ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#0d9488',
                    'paddingAll' => '18px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => '🏥 MOPH Notify Service',
                                    'color' => '#ffffff',
                                    'weight' => 'bold',
                                    'size' => 'md',
                                    'flex' => 1,
                                ],
                                [
                                    'type' => 'text',
                                    'text' => '🟢 ONLINE',
                                    'color' => '#a7f3d0',
                                    'weight' => 'bold',
                                    'size' => 'xs',
                                    'align' => 'end',
                                ]
                            ]
                        ],
                        [
                            'type' => 'text',
                            'text' => "สำนักสุขภาพดิจิทัล • กระทรวงสาธารณสุข",
                            'color' => '#ccfbf1',
                            'size' => 'xxs',
                            'margin' => 'xs',
                        ]
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '16px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '✅ การเชื่อมต่อระบบสำเร็จสมบูรณ์!',
                            'size' => 'md',
                            'weight' => 'bold',
                            'color' => '#0f172a',
                        ],
                        [
                            'type' => 'text',
                            'text' => "ระบบสารสนเทศ {$hospitalName} สามารถส่งข้อความแจ้งเตือนผ่านช่องทาง MOPH Notify (หมอพร้อม LINE OA) ได้อย่างถูกต้อง",
                            'size' => 'xs',
                            'color' => '#475569',
                            'wrap' => true,
                            'margin' => 'xs',
                        ],
                        [
                            'type' => 'separator',
                            'margin' => 'md',
                            'color' => '#e2e8f0',
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'md',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '🏢 หน่วยงาน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => $departmentName, 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8],
                                    ]
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '👤 ผู้ทดสอบ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => $senderName, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8],
                                    ]
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => '🕒 เวลาทดสอบ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                        ['type' => 'text', 'text' => "{$nowThai} น.", 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8],
                                    ]
                                ],
                            ]
                        ]
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => [
                                'type' => 'uri',
                                'label' => '🌐 เข้าสู่ระบบสารสนเทศ รพ.',
                                'uri' => $systemUrl,
                            ],
                            'style' => 'primary',
                            'color' => '#0d9488',
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Fallback Plain Text for New Repair
     */
    public static function buildRepairTicketText(Repair $repair): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $department = $repair->department ? $repair->department->name : '-';
        $urgencyIcons = [
            'low' => '🟢 ปกติ',
            'normal' => '🔵 ปานกลาง',
            'high' => '🟡 ด่วน',
            'critical' => '🔴 ด่วนที่สุด',
        ];
        $urgencyText = $urgencyIcons[$repair->urgency] ?? $repair->urgency;
        $assetInfo = $repair->asset ? "{$repair->asset->asset_code} ({$repair->asset->name})" : ($repair->other_device_info ?: 'ไม่ระบุ');
        $viewUrl = url('/repairs/' . $repair->id);

        $msg = "\n🔔 มีงานแจ้งซ่อมบำรุงใหม่ ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🎫 รหัสตั๋ว: {$repair->ticket_number}\n";
        $msg .= "📌 หัวข้อ: {$repair->title}\n";
        $msg .= "🚨 ความเร่งด่วน: {$urgencyText}\n";
        $msg .= "⏱️ เป้าหมาย SLA: {$repair->sla_hours} ชม.\n";
        $msg .= "🏢 แผนก: {$department}\n";
        $msg .= "📍 สถานที่: " . ($repair->location_detail ?: '-') . "\n";
        $msg .= "💻 ครุภัณฑ์: {$assetInfo}\n";
        $msg .= "👤 ผู้แจ้ง: {$repair->requester_name} (โทร: {$repair->requester_phone})\n";
        $msg .= "🔗 รายละเอียด: {$viewUrl}";

        return $msg;
    }

    /**
     * Fallback Plain Text for Status Change
     */
    public static function buildStatusChangeText(Repair $repair, string $oldStatus, string $newStatus, ?string $comment = null): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $viewUrl = url('/repairs/' . $repair->id);

        $msg = "\n🔄 อัปเดตสถานะงานซ่อม ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🎫 รหัสตั๋ว: {$repair->ticket_number}\n";
        $msg .= "📌 หัวข้อ: {$repair->title}\n";
        $msg .= "📊 สถานะ: {$newStatus}\n";
        if ($comment) {
            $msg .= "💬 บันทึก: {$comment}\n";
        }
        $msg .= "🔗 รายละเอียด: {$viewUrl}";

        return $msg;
    }

    /**
     * Fallback Plain Text for Asset Borrow (All Lifecycle Stages)
     */
    public static function buildAssetBorrowText(AssetBorrow $borrow, string $action = 'created'): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $titles = [
            'created' => 'มีคำขอยืมอุปกรณ์ไอทีใหม่',
            'approved' => 'อนุมัติการยืมอุปกรณ์ไอทีแล้ว',
            'rejected' => 'ปฏิเสธคำขอยืมอุปกรณ์ไอที',
            'dispatched' => 'ส่งมอบอุปกรณ์แล้ว (กำลังใช้งาน)',
            'returned' => 'ส่งคืนอุปกรณ์เรียบร้อยแล้ว',
        ];
        $title = $titles[$action] ?? 'รายการยืมอุปกรณ์ไอที';
        $viewUrl = url('/asset-borrows/' . $borrow->id);

        $msg = "\n📦 {$title} ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔖 เลขที่ใบยืม: {$borrow->borrow_no}\n";
        $msg .= "👤 ผู้ขอยืม: {$borrow->borrower_name}\n";
        $msg .= "🏢 แผนก: " . ($borrow->department ? $borrow->department->name : '-') . "\n";
        $msg .= "💻 ครุภัณฑ์: " . ($borrow->asset ? $borrow->asset->asset_code . ' ' . $borrow->asset->name : '-') . "\n";
        $msg .= "🎯 วัตถุประสงค์: {$borrow->purpose}\n";
        if ($borrow->expected_return_date) {
            $msg .= "📅 กำหนดคืน: " . $borrow->expected_return_date->format('d/m/Y') . "\n";
        }
        if ($action === 'rejected' && $borrow->rejection_reason) {
            $msg .= "❌ เหตุผลที่ปฏิเสธ: {$borrow->rejection_reason}\n";
        }
        if ($action === 'dispatched' && $borrow->dispatch_condition) {
            $msg .= "📦 สภาพส่งมอบ: {$borrow->dispatch_condition}\n";
        }
        if ($action === 'returned') {
            if ($borrow->return_condition) {
                $msg .= "📥 สภาพส่งคืน: {$borrow->return_condition}\n";
            }
            if ($borrow->actual_return_date) {
                $msg .= "🕒 วันที่ส่งคืนจริง: " . $borrow->actual_return_date->format('d/m/Y') . "\n";
            }
        }
        $msg .= "🔗 จัดการรายการ: {$viewUrl}";

        return $msg;
    }

    /**
     * Fallback Plain Text for Data Request (All Lifecycle Stages)
     */
    public static function buildDataRequestText(DataRequest $dataRequest, string $action = 'created', ?string $comment = null): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $titles = [
            'created' => 'มีคำขอข้อมูลสารสนเทศใหม่',
            'approved' => 'อนุมัติ/รับเรื่องคำขอข้อมูลสารสนเทศ',
            'in_progress' => 'กำลังดำเนินการสกัดข้อมูล',
            'rejected' => 'ไม่อนุมัติคำขอข้อมูลสารสนเทศ',
            'completed' => 'สกัดข้อมูลสารสนเทศเสร็จสิ้นแล้ว',
        ];
        $title = $titles[$action] ?? 'คำขอข้อมูลสารสนเทศ';
        $viewUrl = url('/data-requests/' . $dataRequest->id);
        $requesterName = $dataRequest->user ? $dataRequest->user->name : '-';
        $departmentName = $dataRequest->department ? $dataRequest->department->name : '-';
        $formatName = strtoupper($dataRequest->file_format ?: 'EXCEL');

        $msg = "\n📄 {$title} ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔖 เลขที่คำขอ: {$dataRequest->request_no}\n";
        $msg .= "📌 หัวข้อ: {$dataRequest->title}\n";
        $msg .= "👤 ผู้ยื่นคำขอ: {$requesterName}\n";
        $msg .= "🏢 แผนก: {$departmentName}\n";
        $msg .= "🎯 วัตถุประสงค์: " . ($dataRequest->objective_label ?: $dataRequest->objective_type) . "\n";
        $msg .= "🚨 ความเร่งด่วน: " . ($dataRequest->urgency_label ?: $dataRequest->urgency) . "\n";
        $msg .= "💾 รูปแบบไฟล์: {$formatName}\n";
        $note = $comment ?: $dataRequest->admin_notes;
        if ($note) {
            $msg .= "💬 บันทึก/ผล: {$note}\n";
        }
        $msg .= "🔗 รายละเอียด: {$viewUrl}";

        return $msg;
    }
}
