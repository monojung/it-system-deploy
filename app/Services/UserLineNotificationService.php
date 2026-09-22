<?php

namespace App\Services;

use App\Models\Repair;
use App\Models\DataRequest;
use App\Models\AssetBorrow;
use App\Models\AssetTransfer;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserLineNotificationService
{
    /**
     * Send direct Push Message to user via LINE Official Account (Messaging API)
     *
     * @param string $lineUserId
     * @param array $messages
     * @param string|null $channelAccessToken
     * @return array ['success' => bool, 'message' => string, 'status' => int]
     */
    public static function sendLineOaPush(string $lineUserId, array $messages, ?string $channelAccessToken = null): array
    {
        $lineUserId = trim($lineUserId);
        $token = trim($channelAccessToken ?: setting('line_oa_channel_access_token', ''));

        if (empty($lineUserId)) {
            return ['success' => false, 'message' => 'ไม่พบ LINE User ID ของผู้รับ', 'status' => 400];
        }

        if (empty($token)) {
            return ['success' => false, 'message' => 'ไม่พบ Channel Access Token ของ LINE OA โรงพยาบาล', 'status' => 400];
        }

        $payload = [
            'to' => $lineUserId,
            'messages' => $messages,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->timeout(12)
                ->post('https://api.line.me/v2/bot/message/push', $payload);

            if ($response->successful()) {
                Log::info("LINE OA push message sent successfully to user {$lineUserId}");
                return ['success' => true, 'message' => 'ส่งการแจ้งเตือนเข้า LINE OA ของผู้ใช้งานสำเร็จ', 'status' => 200];
            }

            $statusCode = $response->status();
            $body = $response->json() ?? $response->body();
            Log::warning("LINE OA push API failed (Status {$statusCode}): " . json_encode($body));

            // Fallback to text if Flex was rejected
            if (isset($messages[0]['type']) && $messages[0]['type'] === 'flex') {
                $altText = $messages[0]['altText'] ?? '🔔 มีการแจ้งเตือนใหม่จากระบบสารสนเทศ รพ.ทุ่งหัวช้าง';
                $fallbackPayload = [
                    'to' => $lineUserId,
                    'messages' => [['type' => 'text', 'text' => $altText]]
                ];
                $fallbackRes = Http::withoutVerifying()
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])
                    ->timeout(10)
                    ->post('https://api.line.me/v2/bot/message/push', $fallbackPayload);

                if ($fallbackRes->successful()) {
                    return ['success' => true, 'message' => 'ส่งข้อความตัวอักษรสำรองเข้า LINE OA ผู้ใช้สำเร็จ', 'status' => 200];
                }
            }

            return [
                'success' => false,
                'message' => "LINE OA API ตอบกลับข้อผิดพลาด ({$statusCode}): " . ($response->json('message') ?: 'ส่งไม่สำเร็จ'),
                'status' => $statusCode,
            ];
        } catch (Throwable $e) {
            Log::error("LINE OA Push Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ LINE OA: ' . $e->getMessage(), 'status' => 500];
        }
    }

    /**
     * Send direct message to user's MorPromt LINE OA via MOPH Notify using Citizen ID (CID)
     *
     * @param string $cid 13-digit citizen ID
     * @param array $messages
     * @return array
     */
    public static function sendMophCidPush(string $cid, array $messages): array
    {
        $cid = preg_replace('/[^0-9]/', '', $cid);
        if (strlen($cid) !== 13) {
            return ['success' => false, 'message' => 'เลขประจำตัวประชาชนไม่ถูกต้อง (ต้องมี 13 หลัก)', 'status' => 400];
        }

        $payload = [
            'cid' => $cid,
            'messages' => $messages,
        ];

        return MophNotifyService::sendMophMessage($payload);
    }

    /**
     * Dispatch notification to user using available channels (LINE OA User ID, or MOPH CID)
     *
     * @param User|null $user
     * @param array $flexMessage
     * @param string $plainText
     * @return bool
     */
    public static function dispatchToUser(?User $user, array $flexMessage, string $plainText): bool
    {
        $enabled = (bool) setting('user_notify_enabled', true);
        if (!$enabled) {
            return false;
        }

        if (!$user) {
            return false;
        }

        if (!$user->notify_line_enabled) {
            return false;
        }

        $format = setting('moph_notify_message_type', 'flex');
        $messages = ($format === 'flex') 
            ? [$flexMessage] 
            : [['type' => 'text', 'text' => $plainText]];

        $sent = false;

        // Channel 1: LINE OA Push via line_user_id
        if (!empty($user->line_user_id)) {
            $oaToken = setting('line_oa_channel_access_token', '');
            if (!empty($oaToken)) {
                $res = self::sendLineOaPush($user->line_user_id, $messages, $oaToken);
                if ($res['success']) {
                    $sent = true;
                }
            }
        }

        // Channel 2: MOPH Notify via CID (หมอพร้อม LINE OA)
        if (!$sent && !empty($user->cid)) {
            $mophUserEnabled = (bool) setting('user_notify_via_moph_cid', true);
            if ($mophUserEnabled && (bool) setting('moph_notify_enabled', true)) {
                $res = self::sendMophCidPush($user->cid, $messages);
                if ($res['success']) {
                    $sent = true;
                }
            }
        }

        return $sent;
    }

    // =========================================================================
    // 1. งานแจ้งซ่อมบำรุง (Repair Ticket User Notifications)
    // =========================================================================

    public static function notifyRepairUser(Repair $repair, string $stage = 'created', ?string $comment = null): bool
    {
        $notifyOnRepair = (bool) setting('notify_user_on_repair_status', true);
        if (!$notifyOnRepair) {
            return false;
        }

        $user = $repair->requester ?: ($repair->requester_id ? User::find($repair->requester_id) : null);
        if (!$user && $repair->requester_phone) {
            $user = User::where('phone', $repair->requester_phone)->first();
        }

        if (!$user) {
            return false;
        }

        $flex = self::buildRepairUserFlex($repair, $stage, $comment);
        $text = self::buildRepairUserText($repair, $stage, $comment);

        return self::dispatchToUser($user, $flex, $text);
    }

    protected static function buildRepairUserFlex(Repair $repair, string $stage, ?string $comment): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $viewUrl = MophNotifyService::getSafePublicUrl('/repairs/' . $repair->id);

        switch ($stage) {
            case 'completed':
                $title = '🎉 งานซ่อมของคุณเสร็จเรียบร้อยแล้ว';
                $headerColor = '#059669';
                $statusText = 'ซ่อมเสร็จสิ้น พร้อมใช้งาน';
                $instruction = 'ท่านสามารถทดลองใช้งาน หรือติดต่อรับอุปกรณ์คืนได้ที่ห้อง IT';
                $btnLabel = '⭐ ดูรายละเอียดและให้คะแนนบริการ';
                break;
            case 'in_progress':
                $title = '🛠️ ช่างกำลังดำเนินการซ่อมอุปกรณ์';
                $headerColor = '#0284c7';
                $statusText = 'กำลังดำเนินการซ่อมแซม';
                $instruction = 'ช่างได้รับเรื่องแล้ว และกำลังเข้าตรวจเช็ค/แก้ไขปัญหา';
                $btnLabel = '🔎 ติดตามสถานะงานซ่อม';
                break;
            case 'assigned':
                $title = '👨‍🔧 มอบหมายช่างผู้ดูแลเรียบร้อยแล้ว';
                $headerColor = '#4f46e5';
                $statusText = 'มอบหมายช่างแล้ว';
                $instruction = 'ช่าง ' . ($repair->technician?->name ?? 'ฝ่าย IT') . ' ได้รับมอบหมายงานแล้ว';
                $btnLabel = '🔎 ดูรายละเอียดช่างผู้ซ่อม';
                break;
            case 'cancelled':
                $title = '❌ ยกเลิกใบแจ้งซ่อมบำรุง';
                $headerColor = '#dc2626';
                $statusText = 'ยกเลิกใบงาน';
                $instruction = $comment ?: 'ใบแจ้งซ่อมถูกยกเลิก หากมีข้อสงสัยกรุณาติดต่อกลุ่มงาน IT';
                $btnLabel = '🔎 ดูรายละเอียดการยกเลิก';
                break;
            case 'created':
            default:
                $title = '✅ ได้รับการแจ้งซ่อมของท่านแล้ว';
                $headerColor = '#0d9488';
                $statusText = 'รับเรื่องเข้าระบบเรียบร้อย';
                $instruction = 'ระบบกำลังจัดคิวช่างเพื่อเข้าตรวจสอบตามเป้าหมาย SLA';
                $btnLabel = '🔎 ตรวจสอบสถานะใบงานของฉัน';
                break;
        }

        $nowThai = now()->addYears(543)->format('d/m/Y H:i');
        $assetName = $repair->asset ? "{$repair->asset->asset_code} ({$repair->asset->name})" : ($repair->other_device_info ?: 'ไม่ระบุอุปกรณ์');

        $bodyContents = [
            [
                'type' => 'box',
                'layout' => 'vertical',
                'backgroundColor' => '#f0fdf4',
                'paddingAll' => 'md',
                'cornerRadius' => 'md',
                'contents' => [
                    ['type' => 'text', 'text' => "เรียนคุณ {$repair->requester_name}", 'size' => 'xs', 'color' => '#166534', 'weight' => 'bold'],
                    ['type' => 'text', 'text' => $instruction, 'size' => 'xs', 'color' => '#14532d', 'margin' => 'xs', 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'margin' => 'md',
                'contents' => [
                    ['type' => 'text', 'text' => '🎫 รหัสใบแจ้งซ่อม:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 5],
                    ['type' => 'text', 'text' => $repair->ticket_number, 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 7],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '📌 อาการ/หัวข้อ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 5],
                    ['type' => 'text', 'text' => $repair->title, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 7, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '💻 อุปกรณ์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 5],
                    ['type' => 'text', 'text' => $assetName, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 7, 'wrap' => true],
                ]
            ],
            [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '🚦 สถานะปัจจุบัน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 5],
                    ['type' => 'text', 'text' => $statusText, 'size' => 'xs', 'color' => $headerColor, 'weight' => 'bold', 'flex' => 7],
                ]
            ],
        ];

        if ($repair->technician) {
            $bodyContents[] = [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '👨‍🔧 ช่างผู้ดูแล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 5],
                    ['type' => 'text', 'text' => "{$repair->technician->name} (โทร: " . ($repair->technician->phone ?: 'เบอร์ภายใน IT') . ")", 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 7, 'wrap' => true],
                ]
            ];
        }

        if ($stage === 'completed' && $repair->solution) {
            $bodyContents[] = [
                'type' => 'box',
                'layout' => 'horizontal',
                'contents' => [
                    ['type' => 'text', 'text' => '💡 การแก้ไขปัญหา:', 'size' => 'xs', 'color' => '#059669', 'flex' => 5],
                    ['type' => 'text', 'text' => $repair->solution, 'size' => 'xs', 'color' => '#065f46', 'flex' => 7, 'wrap' => true],
                ]
            ];
        }

        return [
            'type' => 'flex',
            'altText' => "{$title} [{$repair->ticket_number}]",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $headerColor,
                    'paddingAll' => 'lg',
                    'contents' => [
                        ['type' => 'text', 'text' => $title, 'weight' => 'bold', 'size' => 'sm', 'color' => '#ffffff', 'wrap' => true],
                        ['type' => 'text', 'text' => "{$hospitalName} • {$nowThai} น.", 'size' => 'xxs', 'color' => '#ffffffd9', 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => $bodyContents
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'uri', 'label' => $btnLabel, 'uri' => $viewUrl],
                            'style' => 'primary',
                            'color' => $headerColor,
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    protected static function buildRepairUserText(Repair $repair, string $stage, ?string $comment): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $viewUrl = url('/repairs/' . $repair->id);

        $msg = "\n🔔 แจ้งเตือนสถานะงานซ่อม ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "เรียนคุณ {$repair->requester_name}\n";
        $msg .= "🎫 รหัสตั๋ว: {$repair->ticket_number}\n";
        $msg .= "📌 หัวข้อ: {$repair->title}\n";
        $msg .= "🚦 สถานะ: " . $repair->status_label . "\n";
        if ($repair->technician) {
            $msg .= "👨‍🔧 ช่างผู้ดูแล: {$repair->technician->name}\n";
        }
        if ($stage === 'completed' && $repair->solution) {
            $msg .= "💡 ผลการซ่อม: {$repair->solution}\n";
        }
        $msg .= "🔗 รายละเอียด: {$viewUrl}";
        return $msg;
    }

    // =========================================================================
    // 2. ระบบขอข้อมูลสารสนเทศและสถิติ (Data Request User Notifications)
    // =========================================================================

    public static function notifyDataRequestUser(DataRequest $dataRequest, string $stage = 'created', ?string $comment = null): bool
    {
        $notifyOnData = (bool) setting('notify_user_on_data_status', true);
        if (!$notifyOnData) {
            return false;
        }

        $user = $dataRequest->user;
        if (!$user && $dataRequest->contact_phone) {
            $user = User::where('phone', $dataRequest->contact_phone)->first();
        }

        if (!$user) {
            return false;
        }

        $flex = self::buildDataRequestUserFlex($dataRequest, $stage, $comment);
        $text = self::buildDataRequestUserText($dataRequest, $stage, $comment);

        return self::dispatchToUser($user, $flex, $text);
    }

    protected static function buildDataRequestUserFlex(DataRequest $dataRequest, string $stage, ?string $comment): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $viewUrl = MophNotifyService::getSafePublicUrl('/data-requests/' . $dataRequest->id);

        switch ($stage) {
            case 'completed':
                $title = '🎉 สกัดข้อมูลสารสนเทศเสร็จเรียบร้อยแล้ว';
                $headerColor = '#059669';
                $statusText = 'สกัดข้อมูลสำเร็จ พร้อมดาวน์โหลด';
                $instruction = 'ข้อมูลและไฟล์ที่ท่านขอได้รับการประมวลผลเสร็จแล้ว สามารถกดดาวน์โหลดไฟล์ได้ทันที';
                $btnLabel = '📥 ดาวน์โหลดไฟล์ข้อมูล';
                break;
            case 'approved':
                $title = '✅ คำขอข้อมูลของคุณได้รับการอนุมัติ';
                $headerColor = '#0284c7';
                $statusText = 'อนุมัติแล้ว อยู่ระหว่างดำเนินการ';
                $instruction = 'เจ้าหน้าที่ IT กำลังดำเนินการดึงและตรวจสอบความถูกต้องของข้อมูลตาม พ.ร.บ. ข้อมูลข่าวสาร/PDPA';
                $btnLabel = '🔎 ติดตามสถานะคำขอ';
                break;
            case 'rejected':
                $title = '❌ คำขอข้อมูลไม่ผ่านการพิจารณา';
                $headerColor = '#dc2626';
                $statusText = 'ปฏิเสธคำขอ';
                $instruction = $comment ?: ($dataRequest->admin_notes ?: 'ไม่ผ่านเกณฑ์การพิจารณา กรุณาติดต่อกลุ่มงาน IT');
                $btnLabel = '🔎 ดูเหตุผลการไม่อนุมัติ';
                break;
            case 'created':
            default:
                $title = '📄 ได้รับคำขอข้อมูลสารสนเทศแล้ว';
                $headerColor = '#4f46e5';
                $statusText = 'รอเจ้าหน้าที่พิจารณา';
                $instruction = 'คำขอข้อมูลของคุณถูกส่งเข้าระบบเรียบร้อยแล้ว เจ้าหน้าที่จะตรวจสอบวัตถุประสงค์และการคุ้มครองข้อมูล';
                $btnLabel = '🔎 ตรวจสอบคำขอของฉัน';
                break;
        }

        $nowThai = now()->addYears(543)->format('d/m/Y H:i');
        $requesterName = $dataRequest->user ? $dataRequest->user->name : 'ผู้รับบริการ';
        $formatName = strtoupper($dataRequest->file_format ?: 'EXCEL');

        return [
            'type' => 'flex',
            'altText' => "{$title} [{$dataRequest->request_no}]",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $headerColor,
                    'paddingAll' => 'lg',
                    'contents' => [
                        ['type' => 'text', 'text' => $title, 'weight' => 'bold', 'size' => 'sm', 'color' => '#ffffff', 'wrap' => true],
                        ['type' => 'text', 'text' => "{$hospitalName} • {$nowThai} น.", 'size' => 'xxs', 'color' => '#ffffffd9', 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#f0f9ff',
                            'paddingAll' => 'md',
                            'cornerRadius' => 'md',
                            'contents' => [
                                ['type' => 'text', 'text' => "เรียนคุณ {$requesterName}", 'size' => 'xs', 'color' => '#0369a1', 'weight' => 'bold'],
                                ['type' => 'text', 'text' => $instruction, 'size' => 'xs', 'color' => '#0c4a6e', 'margin' => 'xs', 'wrap' => true],
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'md',
                            'contents' => [
                                ['type' => 'text', 'text' => '🔖 เลขที่คำขอ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $dataRequest->request_no, 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8],
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => '📌 หัวข้อ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $dataRequest->title, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 8, 'wrap' => true],
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
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => '🚦 สถานะ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $statusText, 'size' => 'xs', 'color' => $headerColor, 'weight' => 'bold', 'flex' => 8],
                            ]
                        ],
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'uri', 'label' => $btnLabel, 'uri' => $viewUrl],
                            'style' => 'primary',
                            'color' => $headerColor,
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    protected static function buildDataRequestUserText(DataRequest $dataRequest, string $stage, ?string $comment): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $viewUrl = url('/data-requests/' . $dataRequest->id);

        $msg = "\n📄 แจ้งเตือนคำขอข้อมูลสารสนเทศ ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔖 รหัสคำขอ: {$dataRequest->request_no}\n";
        $msg .= "📌 หัวข้อ: {$dataRequest->title}\n";
        $msg .= "🚦 สถานะ: " . $dataRequest->status_label . "\n";
        $msg .= "🔗 รายละเอียด/ดาวน์โหลด: {$viewUrl}";
        return $msg;
    }

    // =========================================================================
    // 3. ระบบขอยืม-คืนอุปกรณ์และครุภัณฑ์ IT (Asset Borrow User Notifications)
    // =========================================================================

    public static function notifyBorrowUser(AssetBorrow $borrow, string $stage = 'created', ?string $comment = null): bool
    {
        $notifyOnBorrow = (bool) setting('notify_user_on_borrow_status', true);
        if (!$notifyOnBorrow) {
            return false;
        }

        $user = $borrow->user;
        if (!$user && $borrow->contact_phone) {
            $user = User::where('phone', $borrow->contact_phone)->first();
        }

        if (!$user) {
            return false;
        }

        $flex = self::buildBorrowUserFlex($borrow, $stage, $comment);
        $text = self::buildBorrowUserText($borrow, $stage, $comment);

        return self::dispatchToUser($user, $flex, $text);
    }

    protected static function buildBorrowUserFlex(AssetBorrow $borrow, string $stage, ?string $comment): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $viewUrl = MophNotifyService::getSafePublicUrl('/asset-borrows/' . $borrow->id);

        switch ($stage) {
            case 'approved':
                $title = '✅ คำขอยืมอุปกรณ์ได้รับการอนุมัติแล้ว';
                $headerColor = '#059669';
                $statusText = 'อนุมัติแล้ว (พร้อมรับอุปกรณ์)';
                $instruction = 'กรุณาติดต่อรับเครื่องและอุปกรณ์ต่อพ่วง ณ งานเทคโนโลยีสารสนเทศตามวันเวลาที่ระบุ';
                $btnLabel = '📦 ดูรายละเอียดและเวลารับของ';
                break;
            case 'dispatched':
                $title = '🚀 ส่งมอบอุปกรณ์แล้ว (เริ่มการยืม)';
                $headerColor = '#7c3aed';
                $statusText = 'กำลังยืมใช้งาน';
                $instruction = 'ท่านได้รับอุปกรณ์แล้ว กรุณาส่งคืนภายในวันที่: ' . ($borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : '-');
                $btnLabel = '🔎 ดูใบยืมและกำหนดวันส่งคืน';
                break;
            case 'returned':
                $title = '📥 ส่งคืนอุปกรณ์เรียบร้อยแล้ว';
                $headerColor = '#0d9488';
                $statusText = 'ส่งคืนสมบูรณ์';
                $instruction = 'เจ้าหน้าที่ตรวจสอบอุปกรณ์และรับคืนเข้าคลังเรียบร้อยแล้ว ขอบคุณที่ดูแลทรัพย์สินส่วนรวม';
                $btnLabel = '🔎 ดูประวัติการคืนอุปกรณ์';
                break;
            case 'rejected':
                $title = '❌ คำขอยืมอุปกรณ์ไม่ได้รับการอนุมัติ';
                $headerColor = '#dc2626';
                $statusText = 'ไม่อนุมัติคำขอ';
                $instruction = $comment ?: ($borrow->rejection_reason ?: 'เนื่องจากอุปกรณ์ไม่ว่าง หรือมีคิวใช้งานชนกัน');
                $btnLabel = '🔎 ดูเหตุผลที่ไม่อนุมัติ';
                break;
            case 'created':
            default:
                $title = '📦 ยื่นคำขอยืมอุปกรณ์เรียบร้อยแล้ว';
                $headerColor = '#0284c7';
                $statusText = 'รอเจ้าหน้าที่อนุมัติ';
                $instruction = 'คำขอยืมของคุณส่งถึงฝ่าย IT แล้ว เจ้าหน้าที่จะตรวจเช็คสภาพความพร้อมของอุปกรณ์และแจ้งผลให้ทราบ';
                $btnLabel = '🔎 ตรวจสอบคำขอยืมของฉัน';
                break;
        }

        $nowThai = now()->addYears(543)->format('d/m/Y H:i');
        $asset = $borrow->asset;
        $assetName = $asset ? "{$asset->asset_code} ({$asset->name})" : 'ไม่ระบุอุปกรณ์';

        return [
            'type' => 'flex',
            'altText' => "{$title} [{$borrow->borrow_no}]",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $headerColor,
                    'paddingAll' => 'lg',
                    'contents' => [
                        ['type' => 'text', 'text' => $title, 'weight' => 'bold', 'size' => 'sm', 'color' => '#ffffff', 'wrap' => true],
                        ['type' => 'text', 'text' => "{$hospitalName} • {$nowThai} น.", 'size' => 'xxs', 'color' => '#ffffffd9', 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#f8fafc',
                            'paddingAll' => 'md',
                            'cornerRadius' => 'md',
                            'contents' => [
                                ['type' => 'text', 'text' => "เรียนคุณ {$borrow->borrower_name}", 'size' => 'xs', 'color' => '#475569', 'weight' => 'bold'],
                                ['type' => 'text', 'text' => $instruction, 'size' => 'xs', 'color' => '#334155', 'margin' => 'xs', 'wrap' => true],
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'md',
                            'contents' => [
                                ['type' => 'text', 'text' => '🔖 รหัสคำขอยืม:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $borrow->borrow_no, 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 8],
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
                                ['type' => 'text', 'text' => '📅 กำหนดส่งคืน:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : '-', 'size' => 'xs', 'color' => '#ea580c', 'weight' => 'bold', 'flex' => 8],
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => '🚦 สถานะ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $statusText, 'size' => 'xs', 'color' => $headerColor, 'weight' => 'bold', 'flex' => 8],
                            ]
                        ],
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'uri', 'label' => $btnLabel, 'uri' => $viewUrl],
                            'style' => 'primary',
                            'color' => $headerColor,
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    protected static function buildBorrowUserText(AssetBorrow $borrow, string $stage, ?string $comment): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $viewUrl = url('/asset-borrows/' . $borrow->id);

        $msg = "\n📦 แจ้งเตือนการยืม-คืนอุปกรณ์ ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "เรียนคุณ {$borrow->borrower_name}\n";
        $msg .= "🔖 เลขที่ใบยืม: {$borrow->borrow_no}\n";
        $msg .= "💻 ครุภัณฑ์: " . ($borrow->asset?->name ?? '-') . "\n";
        $msg .= "📅 กำหนดคืน: " . ($borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : '-') . "\n";
        $msg .= "🚦 สถานะ: " . $borrow->status_label . "\n";
        $msg .= "🔗 รายละเอียด: {$viewUrl}";
        return $msg;
    }

    // =========================================================================
    // 4. ระบบย้ายจุดติดตั้งครุภัณฑ์ (Asset Transfer User Notifications)
    // =========================================================================

    public static function notifyTransferUser(AssetTransfer $transfer, string $stage = 'created', ?string $comment = null): bool
    {
        $notifyOnTransfer = (bool) setting('notify_user_on_transfer_status', true);
        if (!$notifyOnTransfer) {
            return false;
        }

        $user = $transfer->user;
        if (!$user && $transfer->to_custodian_name) {
            $user = User::where('name', $transfer->to_custodian_name)->first();
        }

        if (!$user) {
            return false;
        }

        $flex = self::buildTransferUserFlex($transfer, $stage, $comment);
        $text = self::buildTransferUserText($transfer, $stage, $comment);

        return self::dispatchToUser($user, $flex, $text);
    }

    protected static function buildTransferUserFlex(AssetTransfer $transfer, string $stage, ?string $comment): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $viewUrl = MophNotifyService::getSafePublicUrl('/asset-transfers/' . $transfer->id);

        switch ($stage) {
            case 'completed':
                $title = '✅ ย้ายและติดตั้งครุภัณฑ์เรียบร้อยแล้ว';
                $headerColor = '#059669';
                $statusText = 'ติดตั้งและทดสอบสำเร็จ';
                $instruction = 'อุปกรณ์ได้รับการติดตั้ง ณ จุดใหม่ และอัปเดตข้อมูลในระบบสารสนเทศเรียบร้อยแล้ว';
                $btnLabel = '🔎 ดูรายละเอียดและใบส่งมอบ';
                break;
            case 'in_progress':
                $title = '🛠️ ช่างกำลังดำเนินการย้ายจุดติดตั้ง';
                $headerColor = '#0284c7';
                $statusText = 'กำลังดำเนินการย้าย';
                $instruction = 'ช่าง IT ได้รับเรื่องและกำลังเข้าดำเนินการถอดประกอบ/เดินสาย/ติดตั้ง';
                $btnLabel = '🔎 ติดตามสถานะการย้าย';
                break;
            case 'cancelled':
                $title = '❌ ยกเลิกคำขอย้ายจุดติดตั้งครุภัณฑ์';
                $headerColor = '#dc2626';
                $statusText = 'ยกเลิกคำขอ';
                $instruction = $comment ?: 'คำขอย้ายจุดติดตั้งถูกยกเลิก กรุณาติดต่อช่าง IT';
                $btnLabel = '🔎 ดูรายละเอียด';
                break;
            case 'created':
            default:
                $title = '🚚 รับเรื่องขอย้ายจุดติดตั้งครุภัณฑ์แล้ว';
                $headerColor = '#7c3aed';
                $statusText = 'รอดำเนินการ';
                $instruction = 'คำขอย้ายจุดติดตั้งของคุณส่งถึงทีมช่างแล้ว';
                $btnLabel = '🔎 ดูรายละเอียดคำขอ';
                break;
        }

        $nowThai = now()->addYears(543)->format('d/m/Y H:i');
        $asset = $transfer->asset;
        $assetName = $asset ? "{$asset->asset_code} ({$asset->name})" : 'ไม่ระบุอุปกรณ์';
        $toDept = $transfer->toDepartment ? $transfer->toDepartment->name : ($transfer->to_department_name ?: '-');
        $toLoc = $transfer->to_location_detail ?: '-';

        return [
            'type' => 'flex',
            'altText' => "{$title} [{$transfer->transfer_no}]",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $headerColor,
                    'paddingAll' => 'lg',
                    'contents' => [
                        ['type' => 'text', 'text' => $title, 'weight' => 'bold', 'size' => 'sm', 'color' => '#ffffff', 'wrap' => true],
                        ['type' => 'text', 'text' => "{$hospitalName} • {$nowThai} น.", 'size' => 'xxs', 'color' => '#ffffffd9', 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#f8fafc',
                            'paddingAll' => 'md',
                            'cornerRadius' => 'md',
                            'contents' => [
                                ['type' => 'text', 'text' => "แจ้งเตือนการย้ายจุดติดตั้ง", 'size' => 'xs', 'color' => '#475569', 'weight' => 'bold'],
                                ['type' => 'text', 'text' => $instruction, 'size' => 'xs', 'color' => '#334155', 'margin' => 'xs', 'wrap' => true],
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'md',
                            'contents' => [
                                ['type' => 'text', 'text' => '🔖 เลขที่เอกสาร:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => $transfer->transfer_no, 'size' => 'xs', 'color' => '#7c3aed', 'weight' => 'bold', 'flex' => 8],
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
                                ['type' => 'text', 'text' => '🎯 จุดติดตั้งใหม่:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 4],
                                ['type' => 'text', 'text' => "{$toDept} ({$toLoc})", 'size' => 'xs', 'color' => '#166534', 'weight' => 'bold', 'flex' => 8, 'wrap' => true],
                            ]
                        ],
                    ]
                ],
                'footer' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'uri', 'label' => $btnLabel, 'uri' => $viewUrl],
                            'style' => 'primary',
                            'color' => $headerColor,
                            'height' => 'sm',
                        ]
                    ]
                ]
            ]
        ];
    }

    protected static function buildTransferUserText(AssetTransfer $transfer, string $stage, ?string $comment): string
    {
        $hospitalName = setting('hospital_name_th', 'รพ.ทุ่งหัวช้าง');
        $viewUrl = url('/asset-transfers/' . $transfer->id);

        $msg = "\n🚚 แจ้งเตือนการย้ายจุดติดตั้งครุภัณฑ์ ({$hospitalName})\n";
        $msg .= "━━━━━━━━━━━━━━━━━━\n";
        $msg .= "🔖 เลขที่ย้าย: {$transfer->transfer_no}\n";
        $msg .= "💻 ครุภัณฑ์: " . ($transfer->asset?->name ?? '-') . "\n";
        $msg .= "🎯 จุดติดตั้งใหม่: " . ($transfer->toDepartment?->name ?? '-') . " (" . ($transfer->to_location_detail ?: '-') . ")\n";
        $msg .= "🚦 สถานะ: " . $transfer->status_label . "\n";
        $msg .= "🔗 รายละเอียด: {$viewUrl}";
        return $msg;
    }

    /**
     * Send test notification to a specific user
     */
    public static function sendTestToUser(User $user, ?string $customToken = null): array
    {
        $hospitalName = setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง');
        $now = now()->addYears(543)->format('d/m/Y H:i:s');

        $flex = [
            'type' => 'flex',
            'altText' => "🔔 ทดสอบการแจ้งเตือนส่วนตัวผ่าน LINE OA [{$hospitalName}]",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#059669',
                    'paddingAll' => 'lg',
                    'contents' => [
                        ['type' => 'text', 'text' => '✅ ทดสอบการเชื่อมต่อ LINE OA สำเร็จ', 'weight' => 'bold', 'size' => 'sm', 'color' => '#ffffff'],
                        ['type' => 'text', 'text' => "{$hospitalName} • {$now} น.", 'size' => 'xxs', 'color' => '#ffffffd9', 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => [
                        ['type' => 'text', 'text' => "ยินดีต้อนรับคุณ {$user->name}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a'],
                        ['type' => 'text', 'text' => 'ระบบแจ้งเตือนส่วนตัวสำหรับผู้ใช้งานพร้อมทำงานแล้ว ท่านจะได้รับข้อความอัปเดตเมื่อมีงานแจ้งซ่อม ขอข้อมูล ขอยืมคืนอุปกรณ์ และย้ายจุดติดตั้ง', 'size' => 'xs', 'color' => '#475569', 'wrap' => true],
                    ]
                ]
            ]
        ];

        $text = "✅ ทดสอบการเชื่อมต่อ LINE OA สำเร็จ!\nยินดีต้อนรับคุณ {$user->name}\nระบบพร้อมส่งการแจ้งเตือนงานซ่อม ข้อมูล และยืมคืนให้ท่านแล้ว";

        if (!empty($user->line_user_id)) {
            $token = $customToken ?: setting('line_oa_channel_access_token', '');
            if (!empty($token)) {
                $res = self::sendLineOaPush($user->line_user_id, [$flex], $token);
                if ($res['success']) {
                    return ['success' => true, 'message' => "ส่งข้อความทดสอบไปยัง LINE OA ของคุณ {$user->name} สำเร็จเรียบร้อยแล้ว", 'channel' => 'line_oa'];
                }
                return $res;
            }
        }

        if (!empty($user->cid)) {
            $res = self::sendMophCidPush($user->cid, [$flex]);
            if ($res['success']) {
                return ['success' => true, 'message' => "ส่งข้อความทดสอบไปยัง LINE หมอพร้อมของคุณ {$user->name} สำเร็จเรียบร้อยแล้ว", 'channel' => 'moph_cid'];
            }
            return $res;
        }

        return [
            'success' => false,
            'message' => "ไม่สามารถส่งข้อความได้ กรุณาตรวจสอบว่าคุณ {$user->name} ได้ระบุ LINE User ID หรือเลขบัตร ปชช. (CID) และเปิดรับการแจ้งเตือนแล้วหรือไม่",
        ];
    }
}
