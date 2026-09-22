<?php

namespace App\Services;

use App\Models\Repair;
use App\Models\AssetBorrow;
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

        try {
            $version = config('version.version', '2.4.4');
            $response = Http::withHeaders([
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
     * @param string $action 'created' or 'approved'
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
     * Send test notification
     *
     * @param string $senderName
     * @param string|null $endpoint
     * @param string|null $clientKey
     * @param string|null $secretKey
     * @return array
     */
    public static function sendTestMessage(
        string $senderName = 'Admin',
        ?string $endpoint = null,
        ?string $clientKey = null,
        ?string $secretKey = null
    ): array {
        $flex = self::buildTestFlexMessage($senderName);
        $payload = [
            'messages' => [$flex]
        ];

        return self::sendMophMessage($payload, $endpoint, $clientKey, $secretKey);
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
        $viewUrl = url('/repairs/' . $repair->id);

        return [
            'type' => 'flex',
            'altText' => "🔔 แจ้งซ่อมใหม่ #{$repair->ticket_number}: {$repair->title} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'giga',
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
                            'color' => 'rgba(255,255,255,0.85)',
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
        $viewUrl = url('/repairs/' . $repair->id);

        return [
            'type' => 'flex',
            'altText' => "🔄 อัปเดตสถานะ #{$repair->ticket_number} -> {$target['label']} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'giga',
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
                            'color' => 'rgba(255,255,255,0.9)',
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
     * Build Flex Message for Asset Borrowing
     *
     * @param AssetBorrow $borrow
     * @param string $action 'created' or 'approved'
     * @return array
     */
    public static function buildAssetBorrowFlexMessage(AssetBorrow $borrow, string $action = 'created'): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $isApproved = ($action === 'approved');
        $headerColor = $isApproved ? '#059669' : '#0284c7';
        $title = $isApproved ? '✅ อนุมัติการยืมอุปกรณ์ไอทีแล้ว' : '📦 มีคำขอยืมอุปกรณ์ไอทีใหม่';
        $nowThai = now()->addYears(543)->format('d/m/Y H:i:s');
        $asset = $borrow->asset;
        $assetName = $asset ? "{$asset->asset_code} ({$asset->name})" : 'ไม่ระบุอุปกรณ์';
        $viewUrl = url('/asset-borrows/' . $borrow->id);

        return [
            'type' => 'flex',
            'altText' => "{$title} #{$borrow->borrow_no} ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'giga',
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
                            'color' => 'rgba(255,255,255,0.9)',
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
                            'contents' => [
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
                                'label' => '🔎 ตรวจสอบและจัดการคำขอยืม',
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
        $systemUrl = url('/dashboard');

        return [
            'type' => 'flex',
            'altText' => "🧪 ทดสอบระบบ MOPH Notify ({$hospitalName})",
            'contents' => [
                'type' => 'bubble',
                'size' => 'giga',
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
     * Fallback Plain Text for Asset Borrow
     */
    public static function buildAssetBorrowText(AssetBorrow $borrow, string $action = 'created'): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $title = ($action === 'approved') ? 'อนุมัติการยืมอุปกรณ์ไอทีแล้ว' : 'มีคำขอยืมอุปกรณ์ไอทีใหม่';
        $viewUrl = url('/asset-borrows/' . $borrow->id);

        $msg = "\n📦 {$title} ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔖 เลขที่ใบยืม: {$borrow->borrow_no}\n";
        $msg .= "👤 ผู้ขอยืม: {$borrow->borrower_name}\n";
        $msg .= "🏢 แผนก: " . ($borrow->department ? $borrow->department->name : '-') . "\n";
        $msg .= "💻 ครุภัณฑ์: " . ($borrow->asset ? $borrow->asset->asset_code . ' ' . $borrow->asset->name : '-') . "\n";
        $msg .= "🎯 วัตถุประสงค์: {$borrow->purpose}\n";
        $msg .= "🔗 จัดการรายการ: {$viewUrl}";

        return $msg;
    }
}
