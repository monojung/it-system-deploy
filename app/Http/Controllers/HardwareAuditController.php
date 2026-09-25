<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HardwareAudit;
use App\Models\Asset;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\AuditLog;
use App\Models\AgentCommand;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HardwareAuditController extends Controller
{
    /**
     * Helper to get current Thai fiscal year (e.g. 2568, 2569)
     */
    protected function getCurrentFiscalYear(): int
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $defaultFiscalYear = ($currentMonth >= 10) ? ($currentYear + 1 + 543) : ($currentYear + 543);
        return (int) setting('fiscal_year_current', $defaultFiscalYear);
    }

    /**
     * Ensure text is clean, valid UTF-8 and automatically heal ISO-8859-1 mojibake
     * Commonly caused by PowerShell 5.1 irm (e.g. à¹‚à¸£à¸‡... -> โรง...)
     */
    protected function sanitizeUtf8(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        // 1. Detect and recover ISO-8859-1 mojibake for Thai UTF-8 characters
        if (preg_match('/[àáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ]/', $text)) {
            $attempt = @mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
            if ($attempt !== false && preg_match('/[\x{0E00}-\x{0E7F}]/u', $attempt)) {
                $text = $attempt;
            }
        }

        // 2. Ensure valid UTF-8 encoding
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = @mb_convert_encoding($text, 'UTF-8', 'Windows-874, TIS-620, ISO-8859-1, ASCII');
        }

        return trim($text);
    }

    /**
     * API Endpoint: Receive hardware specs submission from client-side PowerShell Agent (POST)
     * Or provide service health/status information when accessed via browser or health checks (GET)
     * Publicly accessible by client agent (CSRF-exempt)
     */
    public function submit(Request $request)
    {
        // Handle GET Request (Health check, API status, and browser direct access)
        if ($request->isMethod('get')) {
            $fiscalYear = $this->getCurrentFiscalYear();
            $auditedCount = HardwareAudit::where('fiscal_year', $fiscalYear)->count();

            $statusData = [
                'status' => 'online',
                'service' => 'THC Client Hardware Audit Telemetry API',
                'hospital' => 'โรงพยาบาลทุ่งหัวช้าง (Thung Hua Chang Hospital)',
                'endpoint' => url('/api/hardware-audit/submit'),
                'version' => '2.1.0',
                'current_fiscal_year' => $fiscalYear,
                'supported_methods' => ['POST', 'GET'],
                'payload_format' => 'application/json',
                'stats' => [
                    'current_fiscal_year_audits' => $auditedCount,
                ],
                'message' => 'ระบบ API ตรวจนับและติดตามสเปคคอมพิวเตอร์พร้อมใช้งาน (กรุณาส่งข้อมูลฮาร์ดแวร์ผ่าน HTTP POST ด้วยรูปแบบ application/json)',
                'timestamp' => now()->toIso8601String(),
            ];

            // If requested via JSON, API client, cURL, or explicitly ?format=json
            if ($request->wantsJson() || $request->input('format') === 'json' || !$request->acceptsHtml()) {
                return response()->json($statusData, 200, [
                    'Content-Type' => 'application/json; charset=utf-8',
                ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            // If accessed directly in a web browser, render the status interface
            return response()->view('hardware_audits.api_status', [
                'statusData' => $statusData,
                'fiscalYear' => $fiscalYear,
                'auditedCount' => $auditedCount,
            ], 200, [
                'Content-Type' => 'text/html; charset=utf-8',
            ]);
        }

        $validated = $request->validate([
            'hostname' => 'nullable|string|max:100',
            'hardware_id' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'mac_address' => 'nullable|string|max:50',
            'ip_address' => 'nullable|string|max:45',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'device_type_code' => 'nullable|string|max:20',
            'cpu_model' => 'nullable|string|max:255',
            'cpu_speed' => 'nullable|string|max:50',
            'ram_capacity' => 'nullable|integer',
            'ram_type' => 'nullable|string|max:30',
            'ram_bus' => 'nullable|string|max:30',
            'ram_slots' => 'nullable|string|max:255',
            'storage_type' => 'nullable|string|max:50',
            'storage_capacity' => 'nullable|string|max:50',
            'storage_second' => 'nullable|string|max:255',
            'os_name' => 'nullable|string|max:100',
            'os_license' => 'nullable|string|max:255',
            'gpu_model' => 'nullable|string|max:255',
            'monitor_size' => 'nullable|string|max:255',
            'client_agent_version' => 'nullable|string|max:30',
        ]);

        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());
        $ip = $request->ip() ?: $request->input('ip_address');

        // Clean Brand, Model, and CPU Model for optimal readability
        $brand = trim((string)$request->input('brand', ''));
        if (preg_match('/To be filled by O\.E\.M\.|System manufacturer|Default string/i', $brand) || $brand === '') {
            $brand = 'เครื่องประกอบ (Custom PC)';
        }

        $model = trim((string)$request->input('model', ''));
        if (preg_match('/To be filled by O\.E\.M\.|System Product Name|Default string/i', $model) || $model === '') {
            $model = 'เครื่องประกอบ (DIY/Clone)';
        }

        $cpuModel = trim((string)$request->input('cpu_model', ''));
        if (!empty($cpuModel)) {
            $cpuModel = preg_replace('/\s*@\s*[\d\.]+\s*GHz/i', '', $cpuModel);
            $cpuModel = str_ireplace(['(R)', '(TM)'], '', $cpuModel);
            $cpuModel = trim(preg_replace('/\s+/', ' ', $cpuModel));
        }

        // Clean HardwareID and Serial Number
        $hardwareId = trim((string)$request->input('hardware_id', ''));
        if (preg_match('/^[0F-]{36}$/i', $hardwareId) || preg_match('/Default string|To be filled by O\.E\.M\.|None/i', $hardwareId)) {
            $hardwareId = '';
        }

        $serial = trim((string)$request->input('serial_number', ''));
        if (preg_match('/Default string|To be filled by O\.E\.M\.|None/i', $serial)) {
            $serial = '';
        }

        // Try Auto-matching with existing Asset in it_assets table
        $matchedAsset = null;
        $matchedBy = null;

        // 0. Explicit Match by Asset Code or Asset ID if passed by client
        if ($request->filled('asset_code')) {
            $matchedAsset = Asset::where('asset_code', trim((string)$request->input('asset_code')))->first();
            if ($matchedAsset) {
                $matchedBy = 'asset_code';
            }
        }
        if (!$matchedAsset && $request->filled('asset_id')) {
            $matchedAsset = Asset::find($request->input('asset_id'));
            if ($matchedAsset) {
                $matchedBy = 'asset_id';
            }
        }

        // 1. Highest Priority: Match by HardwareID (Physical SMBIOS / Motherboard UUID)
        if (!$matchedAsset && !empty($hardwareId)) {
            $matchedAsset = Asset::where('hardware_id', $hardwareId)->first();
            if ($matchedAsset) {
                $matchedBy = 'hardware_id';
            }
        }

        // 2. Secondary: Match by Serial Number
        if (!$matchedAsset && !empty($serial)) {
            $matchedAsset = Asset::where('serial_number', $serial)->first();
            if ($matchedAsset) {
                $matchedBy = 'serial_number';
            }
        }

        // 3. Fallback: Match by MAC Address
        if (!$matchedAsset && !empty($request->input('mac_address'))) {
            $mac = trim($request->input('mac_address'));
            $matchedAsset = Asset::where('mac_address', $mac)->first();
            if ($matchedAsset) {
                $matchedBy = 'mac_address';
            }
        }

        // 4. Fallback: Match by Hostname / Asset Code
        if (!$matchedAsset && !empty($request->input('hostname'))) {
            $hostname = trim($request->input('hostname'));
            $matchedAsset = Asset::where('name', 'like', "%{$hostname}%")
                ->orWhere('asset_code', 'like', "%{$hostname}%")
                ->first();
            if ($matchedAsset) {
                $matchedBy = 'hostname';
            }
        }

        // Calculate Specs Diff if matched asset exists
        $diff = [];
        if ($matchedAsset) {
            $checks = [
                'cpu_model' => 'รุ่น CPU',
                'ram_capacity' => 'ความจุ RAM',
                'ram_type' => 'ชนิด RAM',
                'storage_type' => 'ชนิดพื้นที่จัดเก็บ',
                'storage_capacity' => 'ขนาดพื้นที่จัดเก็บ',
                'storage_second' => 'พื้นที่จัดเก็บเสริม (Secondary Drive)',
                'os_name' => 'ระบบปฏิบัติการ',
                'gpu_model' => 'การ์ดจอ GPU',
            ];

            foreach ($checks as $field => $label) {
                $oldVal = (string)($matchedAsset->$field ?? '');
                $newVal = (string)($request->input($field) ?? '');
                if (!empty($newVal) && !empty($oldVal) && $oldVal !== $newVal) {
                    $diff[$field] = [
                        'label' => $label,
                        'old' => $oldVal,
                        'new' => $newVal,
                    ];
                }
            }
        }

        // Locate existing audit record for this machine in the current fiscal year
        $auditQuery = HardwareAudit::where('fiscal_year', $fiscalYear);
        if (!empty($hardwareId)) {
            $auditQuery->where('hardware_id', $hardwareId);
        } elseif (!empty($serial)) {
            $auditQuery->where('serial_number', $serial);
        } else {
            $auditQuery->where('hostname', $request->input('hostname'));
        }

        $audit = $auditQuery->first();
        if (!$audit) {
            $audit = new HardwareAudit();
            $audit->fiscal_year = $fiscalYear;
        }

        // Sanitize UTF-8 on all textual incoming fields to guarantee 100% Thai fidelity
        $hostname = mb_substr((string)$this->sanitizeUtf8($request->input('hostname')), 0, 100);
        $cleanBrand = mb_substr((string)$this->sanitizeUtf8($brand ?: $request->input('brand')), 0, 250);
        $cleanModel = mb_substr((string)$this->sanitizeUtf8($model ?: $request->input('model')), 0, 250);
        $deviceTypeCode = mb_substr((string)$this->sanitizeUtf8($request->input('device_type_code', 'PC')), 0, 20);
        $cleanCpuModel = mb_substr((string)$this->sanitizeUtf8($cpuModel ?: $request->input('cpu_model')), 0, 250);
        $cpuSpeed = mb_substr((string)$this->sanitizeUtf8($request->input('cpu_speed')), 0, 50);
        $ramType = mb_substr((string)$this->sanitizeUtf8($request->input('ram_type')), 0, 30);
        $ramBus = mb_substr((string)$this->sanitizeUtf8($request->input('ram_bus')), 0, 30);
        $ramSlots = mb_substr((string)$this->sanitizeUtf8($request->input('ram_slots')), 0, 250);
        $storageType = mb_substr((string)$this->sanitizeUtf8($request->input('storage_type')), 0, 50);
        $storageCap = mb_substr((string)$this->sanitizeUtf8($request->input('storage_capacity')), 0, 50);
        $storageSecond = mb_substr((string)$this->sanitizeUtf8($request->input('storage_second')), 0, 250);
        $osName = mb_substr((string)$this->sanitizeUtf8($request->input('os_name')), 0, 100);
        $osLicense = mb_substr((string)$this->sanitizeUtf8($request->input('os_license')), 0, 250);
        $gpuModel = mb_substr((string)$this->sanitizeUtf8($request->input('gpu_model')), 0, 250);
        $monitorSize = mb_substr((string)$this->sanitizeUtf8($request->input('monitor_size')), 0, 250);
        $macAddress = mb_substr((string)$this->sanitizeUtf8($request->input('mac_address')), 0, 50);
        $clientVersion = mb_substr((string)$this->sanitizeUtf8($request->input('client_agent_version', '2.1.0')), 0, 30);

        $audit->fill([
            'asset_id' => $matchedAsset ? $matchedAsset->id : null,
            'hostname' => $hostname ?: null,
            'hardware_id' => $hardwareId ?: null,
            'serial_number' => $serial ?: null,
            'mac_address' => $macAddress ?: null,
            'ip_address' => $ip,
            'brand' => $cleanBrand ?: null,
            'model' => $cleanModel ?: null,
            'device_type_code' => $deviceTypeCode ?: 'PC',
            'cpu_model' => $cleanCpuModel ?: null,
            'cpu_speed' => $cpuSpeed ?: null,
            'ram_capacity' => $request->input('ram_capacity'),
            'ram_type' => $ramType ?: null,
            'ram_bus' => $ramBus ?: null,
            'ram_slots' => $ramSlots ?: null,
            'storage_type' => $storageType ?: null,
            'storage_capacity' => $storageCap ?: null,
            'storage_second' => $storageSecond ?: null,
            'os_name' => $osName ?: null,
            'os_license' => $osLicense ?: null,
            'gpu_model' => $gpuModel ?: null,
            'monitor_size' => $monitorSize ?: null,
            'raw_payload' => $request->all(),
            'specs_diff' => !empty($diff) ? $diff : null,
            'client_agent_version' => $clientVersion ?: '2.1.0',
            'status' => 'pending', // Awaiting Admin Approval
        ]);
        $audit->save();

        // Acknowledge and complete any pending AgentCommand for this machine
        $commandId = $request->input('command_id');
        $activeBatchId = null;
        if (!empty($commandId)) {
            $cmd = AgentCommand::find($commandId);
            if ($cmd) {
                $activeBatchId = $cmd->batch_id;
                $cmd->target_audit_id = $audit->id;
                if ($cmd->target_type === 'single') {
                    $cmd->markAsCompleted("ได้รับข้อมูลจริงจาก {$hostname} (IP: {$ip}, Audit #{$audit->id})", $ip);
                }
            }
        }

        // Auto-complete any active pending/processing commands for this machine across all recent batches
        $pendingCmds = AgentCommand::whereIn('status', ['pending', 'processing'])
            ->where('target_type', 'single')
            ->where(function ($q) use ($hardwareId, $hostname) {
                if (!empty($hardwareId)) {
                    $q->where('target_hardware_id', $hardwareId);
                }
                if (!empty($hostname)) {
                    if (!empty($hardwareId)) {
                        $q->orWhere('target_hostname', $hostname);
                    } else {
                        $q->where('target_hostname', $hostname);
                    }
                }
            })
            ->get();

        foreach ($pendingCmds as $pCmd) {
            $pCmd->target_audit_id = $audit->id;
            $pCmd->markAsCompleted("ได้รับข้อมูลจริงจาก {$hostname} (IP: {$ip}, Audit #{$audit->id})", $ip);
        }

        // Check if there is an active broadcast batch from the last 2 hours without a single record for this machine
        $recentBroadcast = AgentCommand::where('target_type', 'all')
            ->where('created_at', '>=', now()->subHours(2))
            ->latest()
            ->first();

        if ($recentBroadcast) {
            $existingInBatch = AgentCommand::where('batch_id', $recentBroadcast->batch_id)
                ->where('target_type', 'single')
                ->where(function ($q) use ($hardwareId, $hostname) {
                    if (!empty($hardwareId)) {
                        $q->where('target_hardware_id', $hardwareId);
                    }
                    if (!empty($hostname)) {
                        $q->orWhere('target_hostname', $hostname);
                    }
                })
                ->first();

            if (!$existingInBatch) {
                AgentCommand::create([
                    'command' => 'scan',
                    'batch_id' => $recentBroadcast->batch_id,
                    'target_type' => 'single',
                    'target_hardware_id' => $hardwareId ?: null,
                    'target_hostname' => $hostname ?: null,
                    'target_audit_id' => $audit->id,
                    'target_asset_id' => $audit->asset_id,
                    'status' => 'completed',
                    'requested_by' => $recentBroadcast->requested_by,
                    'dispatched_at' => now(),
                    'executed_at' => now(),
                    'ip_address' => $ip,
                    'result_summary' => "สแกนสำเร็จจาก {$hostname} (Audit #{$audit->id})",
                    'parameters' => $recentBroadcast->parameters,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'ส่งรายงานสเปคคอมพิวเตอร์เข้าสู่ระบบเรียบร้อยแล้ว (สถานะ: รอแอดมินตรวจสอบและอนุมัติ)',
            'audit_id' => $audit->id,
            'fiscal_year' => $fiscalYear,
            'hardware_id' => $hardwareId ?: null,
            'is_new_device' => !$matchedAsset,
            'matched_by' => $matchedBy,
            'matched_asset' => $matchedAsset ? [
                'id' => $matchedAsset->id,
                'asset_code' => $matchedAsset->asset_code,
                'name' => $matchedAsset->name,
                'hardware_id' => $matchedAsset->hardware_id,
            ] : null,
            'has_specs_diff' => !empty($diff),
            'diff' => $diff,
            'status' => 'pending',
        ], 200, [
            'Content-Type' => 'application/json; charset=utf-8',
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Agent Polling Endpoint: Check if there are any pending commands for this client machine (GET/POST)
     * CSRF-exempt, accessible by THC-IT-Agent PowerShell script
     */
    public function pollCommand(Request $request)
    {
        $hardwareId = trim((string)$request->input('hardware_id', ''));
        $hostname = trim((string)$request->input('hostname', ''));
        $mac = trim((string)$request->input('mac_address', ''));
        $clientVersion = trim((string)$request->input('client_version', ''));
        $ip = $request->ip() ?: $request->input('ip_address');

        if (preg_match('/^[0F-]{36}$/i', $hardwareId) || preg_match('/Default string|To be filled by O\.E\.M\.|None/i', $hardwareId)) {
            $hardwareId = '';
        }

        // Live Heartbeat Registration (Active Standby Tracking)
        $nowIso = now()->toIso8601String();
        $onlineTtl = now()->addSeconds(30);
        if (!empty($hardwareId)) {
            Cache::put('agent_online:hwid:' . strtoupper(trim($hardwareId)), $nowIso, $onlineTtl);
        }
        if (!empty($hostname)) {
            Cache::put('agent_online:host:' . strtoupper(trim($hostname)), $nowIso, $onlineTtl);
        }
        if (!empty($ip)) {
            Cache::put('agent_online:ip:' . trim($ip), $nowIso, $onlineTtl);
        }

        // Track active online agents list
        $agentKey = strtoupper(trim($hardwareId ?: $hostname ?: $ip));
        if (!empty($agentKey)) {
            try {
                $registry = Cache::get('agent_online_registry', []);
                if (!is_array($registry)) $registry = [];
                $registry[$agentKey] = [
                    'hwid' => $hardwareId,
                    'hostname' => $hostname,
                    'ip' => $ip,
                    'mode' => $request->input('mode', 'tray_agent'),
                    'version' => $request->input('agent_version') ?: $request->input('client_version', '2.5.5'),
                    'last_seen' => $nowIso,
                    'expires_at' => now()->addSeconds(30)->timestamp,
                ];
                $nowTs = now()->timestamp;
                $registry = array_filter($registry, function ($a) use ($nowTs) {
                    return isset($a['expires_at']) && $a['expires_at'] > $nowTs;
                });
                Cache::put('agent_online_registry', $registry, now()->addMinutes(5));
            } catch (\Throwable $e) {}
        }

        // 1. Check for direct command targeted to this machine
        $cmd = null;
        if (!empty($hardwareId) || !empty($hostname)) {
            // Priority 1A: Look for newest 'pending' command
            $query = AgentCommand::where('status', 'pending');
            $query->where(function ($q) use ($hardwareId, $hostname) {
                if (!empty($hardwareId)) {
                    $q->where('target_hardware_id', $hardwareId);
                }
                if (!empty($hostname)) {
                    if (!empty($hardwareId)) {
                        $q->orWhere('target_hostname', $hostname);
                    } else {
                        $q->where('target_hostname', $hostname);
                    }
                }
            });
            $cmd = $query->orderBy('id', 'desc')->first();

            // Priority 1B: Look for recent 'processing' command (within last 3 minutes)
            if (!$cmd) {
                $cmd = AgentCommand::where('status', 'processing')
                    ->where('dispatched_at', '>=', now()->subMinutes(3))
                    ->where(function ($q) use ($hardwareId, $hostname) {
                        if (!empty($hardwareId)) {
                            $q->where('target_hardware_id', $hardwareId);
                        }
                        if (!empty($hostname)) {
                            if (!empty($hardwareId)) {
                                $q->orWhere('target_hostname', $hostname);
                            } else {
                                $q->where('target_hostname', $hostname);
                            }
                        }
                    })
                    ->orderBy('id', 'desc')
                    ->first();
            }
        }

        // 2. Fallback: Broadcast command (target_type = 'all')
        if (!$cmd) {
            $broadcast = AgentCommand::where('target_type', 'all')
                ->whereIn('status', ['pending', 'processing'])
                ->where('created_at', '>=', now()->subHours(2))
                ->orderBy('id', 'desc')
                ->first();

            if ($broadcast) {
                // Find or create an individual tracking record for this machine in this batch
                $cmd = AgentCommand::where('batch_id', $broadcast->batch_id)
                    ->where('target_type', 'single')
                    ->where(function ($q) use ($hardwareId, $hostname) {
                        if (!empty($hardwareId)) {
                            $q->where('target_hardware_id', $hardwareId);
                        }
                        if (!empty($hostname)) {
                            $q->orWhere('target_hostname', $hostname);
                        }
                    })
                    ->first();

                if (!$cmd) {
                    $cmd = AgentCommand::create([
                        'command' => 'scan',
                        'batch_id' => $broadcast->batch_id,
                        'target_type' => 'single',
                        'target_hardware_id' => $hardwareId ?: null,
                        'target_hostname' => $hostname ?: null,
                        'status' => 'pending',
                        'requested_by' => $broadcast->requested_by,
                        'parameters' => $broadcast->parameters,
                    ]);
                }
            }
        }

        if ($cmd) {
            if ($cmd->target_type === 'single') {
                $cmd->markAsDispatched($ip);
            }

            return response()->json([
                'has_command' => true,
                'command_id' => $cmd->id,
                'batch_id' => $cmd->batch_id,
                'command' => $cmd->command, // 'scan'
                'target_type' => $cmd->target_type,
                'parameters' => $cmd->parameters ?: [],
                'requested_at' => $cmd->created_at ? $cmd->created_at->toIso8601String() : now()->toIso8601String(),
                'server_time' => now()->toIso8601String(),
            ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'has_command' => false,
            'message' => 'ไม่มีคำสั่งสแกนค้างสำหรับเครื่องนี้',
            'poll_interval_sec' => 30,
            'server_time' => now()->toIso8601String(),
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Agent Command Direct Completion Endpoint
     */
    public function completeCommand(Request $request, $id)
    {
        $cmd = AgentCommand::find($id);
        if ($cmd) {
            $summary = $request->input('summary', 'สำเร็จผ่าน Agent Direct Ack');
            $cmd->markAsCompleted($summary, $request->ip());
            return response()->json(['success' => true, 'message' => 'บันทึกสถานะคำสั่งสำเร็จ']);
        }
        return response()->json(['success' => false, 'message' => 'ไม่พบคำสั่ง'], 404);
    }

    /**
     * Monitoring Dashboard & Fiscal Year Survey Hub
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้นที่มีสิทธิ์เข้าถึงระบบมอนิเตอร์สเปค');
        }

        $currentFiscalYear = $this->getCurrentFiscalYear();
        $fiscalYear = (int) $request->input('fiscal_year', $currentFiscalYear);
        $tab = $request->input('tab', 'pending');

        // Fleet scope: All active computers (PC, Notebook, All-in-One, Server)
        $fleetQuery = Asset::whereHas('deviceType', function ($q) {
            $q->whereIn('code', ['PC', 'NB', 'AIO', 'SERVER'])
              ->orWhere('name', 'like', '%คอมพิวเตอร์%')
              ->orWhere('name', 'like', '%โน้ตบุ๊ก%')
              ->orWhere('name', 'like', '%Server%');
        })->where('status', 'active');

        $totalFleet = (clone $fleetQuery)->count();
        if ($totalFleet === 0) {
            $totalFleet = Asset::where('status', 'active')->count(); // Fallback if device types not categorized
        }

        // Audited this fiscal year
        $auditedCount = Asset::where('last_audited_fiscal_year', $fiscalYear)
            ->where('status', 'active')
            ->count();

        // Pending submissions awaiting Admin review
        $pendingCount = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->where('status', 'pending')
            ->count();

        $approvedCount = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->where('status', 'approved')
            ->count();

        $rejectedCount = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->where('status', 'rejected')
            ->count();

        // Unlinked submissions count (Newly discovered computers awaiting asset linking or registration)
        $unlinkedCount = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->whereNull('asset_id')
            ->where('status', 'pending')
            ->count();

        $notAuditedCount = max(0, $totalFleet - $auditedCount);
        $auditedPercentage = $totalFleet > 0 ? round(($auditedCount / $totalFleet) * 100, 1) : 0;

        // MDES Upgrade Warning Count (RAM < 8GB or HDD)
        $mdesWarningCount = Asset::where('status', 'active')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('ram_capacity')->where('ram_capacity', '<', 8);
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('storage_type')->where('storage_type', 'like', '%HDD%');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('os_name')->where('os_name', 'like', '%Windows 7%');
                });
            })->count();

        // Tab 1: Pending Queue
        $pendingQuery = HardwareAudit::with(['asset.department', 'reviewer'])
            ->where('fiscal_year', $fiscalYear);

        if ($request->input('status') === 'unlinked') {
            $pendingQuery->whereNull('asset_id')->where('status', 'pending');
        } elseif ($request->filled('status')) {
            $pendingQuery->where('status', $request->status);
        } else {
            $pendingQuery->where('status', 'pending');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('cpu_model', 'like', "%{$search}%");
            });
        }

        $audits = $pendingQuery->latest()->paginate(15)->appends($request->query());

        // Tab 2: Department Audit Tracker
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($dept) use ($fiscalYear) {
                $totalInDept = Asset::where('department_id', $dept->id)->where('status', 'active')->count();
                $auditedInDept = Asset::where('department_id', $dept->id)
                    ->where('status', 'active')
                    ->where('last_audited_fiscal_year', $fiscalYear)
                    ->count();
                $percent = $totalInDept > 0 ? round(($auditedInDept / $totalInDept) * 100) : 0;
                
                return (object)[
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'total_assets' => $totalInDept,
                    'audited_assets' => $auditedInDept,
                    'pending_assets' => max(0, $totalInDept - $auditedInDept),
                    'percentage' => $percent,
                ];
            });

        // Tab 3: Annual Report Statistics
        $ramDistribution = Asset::where('status', 'active')
            ->whereNotNull('ram_capacity')
            ->select('ram_capacity', DB::raw('count(*) as total'))
            ->groupBy('ram_capacity')
            ->orderBy('ram_capacity')
            ->get();

        $osDistribution = Asset::where('status', 'active')
            ->whereNotNull('os_name')
            ->select('os_name', DB::raw('count(*) as total'))
            ->groupBy('os_name')
            ->orderByDesc('total')
            ->get();

        $storageDistribution = Asset::where('status', 'active')
            ->whereNotNull('storage_type')
            ->select('storage_type', DB::raw('count(*) as total'))
            ->groupBy('storage_type')
            ->orderByDesc('total')
            ->get();

        // Older than 5 years
        $fiveYearsAgo = Carbon::now()->subYears(5);
        $agedOver5YearsCount = Asset::where('status', 'active')
            ->whereNotNull('purchase_date')
            ->where('purchase_date', '<=', $fiveYearsAgo)
            ->count();

        // Available fiscal years for filter dropdown
        $availableFiscalYears = HardwareAudit::select('fiscal_year')
            ->distinct()
            ->pluck('fiscal_year')
            ->push($currentFiscalYear)
            ->unique()
            ->sortDesc()
            ->values();

        $deviceTypes = DeviceType::orderBy('name')->get();

        // Total devices with installed agent
        $agentInstalledCount = HardwareAudit::where(function($q) {
                $q->whereNotNull('client_agent_version')
                  ->orWhereNotNull('hardware_id');
            })
            ->distinct('hostname')
            ->count('hostname');
        if ($agentInstalledCount === 0) {
            $agentInstalledCount = HardwareAudit::distinct('hostname')->count('hostname');
        }

        return view('hardware_audits.index', compact(
            'fiscalYear',
            'currentFiscalYear',
            'availableFiscalYears',
            'tab',
            'totalFleet',
            'auditedCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'unlinkedCount',
            'notAuditedCount',
            'auditedPercentage',
            'mdesWarningCount',
            'audits',
            'departments',
            'deviceTypes',
            'ramDistribution',
            'osDistribution',
            'storageDistribution',
            'agedOver5YearsCount',
            'agentInstalledCount'
        ));
    }

    /**
     * Admin Action: Approve and Sync Scanned Specs into it_assets table
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือแอดมินเท่านั้นที่มีสิทธิ์อนุมัติสเปค');
        }

        $audit = HardwareAudit::findOrFail($id);

        $asset = null;
        if ($audit->asset_id) {
            $asset = Asset::find($audit->asset_id);
        }

        // If not matched, allow admin to link to existing asset by asset_id from form
        if (!$asset && $request->filled('target_asset_id')) {
            $asset = Asset::find($request->target_asset_id);
            if ($asset) {
                $audit->asset_id = $asset->id;
            }
        }

        // If asset found, update structured hardware specs
        if ($asset) {
            if ($audit->hardware_id) $asset->hardware_id = $audit->hardware_id;
            if ($audit->cpu_model) $asset->cpu_model = $audit->cpu_model;
            if ($audit->cpu_speed) $asset->cpu_speed = $audit->cpu_speed;
            if ($audit->ram_capacity) $asset->ram_capacity = $audit->ram_capacity;
            if ($audit->ram_type) $asset->ram_type = $audit->ram_type;
            if ($audit->ram_bus) $asset->ram_bus = $audit->ram_bus;
            if ($audit->ram_slots) $asset->ram_slots = $audit->ram_slots;
            if ($audit->storage_type) $asset->storage_type = $audit->storage_type;
            if ($audit->storage_capacity) $asset->storage_capacity = $audit->storage_capacity;
            if ($audit->storage_second) $asset->storage_second = $audit->storage_second;
            if ($audit->brand && empty($asset->brand)) $asset->brand = $audit->brand;
            if ($audit->model && empty($asset->model)) $asset->model = $audit->model;
            if ($audit->os_name) $asset->os_name = $audit->os_name;
            if ($audit->os_license) $asset->os_license = $audit->os_license;
            if ($audit->gpu_model) $asset->gpu_model = $audit->gpu_model;
            if ($audit->monitor_size) $asset->monitor_size = $audit->monitor_size;
            if ($audit->ip_address) $asset->ip_address = $audit->ip_address;
            if ($audit->mac_address) $asset->mac_address = $audit->mac_address;
            if ($audit->serial_number && empty($asset->serial_number)) {
                $asset->serial_number = $audit->serial_number;
            }

            // Mark as audited in this fiscal year!
            $asset->last_audited_at = now();
            $asset->last_audited_fiscal_year = $audit->fiscal_year;
            $asset->save();

            // Log activity
            AuditLog::record(
                'update',
                'assets',
                "อนุมัติอัปเดตสเปคฮาร์ดแวร์ประจำปีงบประมาณ {$audit->fiscal_year} ให้กับครุภัณฑ์ {$asset->asset_code} ({$asset->name})",
                $asset,
                null,
                null,
                $request,
                $user
            );
        }

        // Update audit record
        $audit->status = 'approved';
        $audit->reviewed_by = $user->id;
        $audit->reviewed_at = now();
        $audit->review_notes = $request->input('notes');
        $audit->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'อนุมัติและอัปเดตข้อมูลสเปคลงครุภัณฑ์เรียบร้อยแล้ว',
            ]);
        }

        return back()->with('success', "อนุมัติและอัปเดตสเปคเครื่อง '{$audit->hostname}' ประจำปีงบประมาณ {$audit->fiscal_year} เรียบร้อยแล้ว");
    }

    /**
     * Admin Action: Create New Asset directly from an unmatched/new Hardware Audit submission
     */
    public function createAssetFromAudit(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือแอดมินเท่านั้นที่มีสิทธิ์เพิ่มครุภัณฑ์ใหม่');
        }

        $audit = HardwareAudit::findOrFail($id);

        $validated = $request->validate([
            'asset_code' => 'required|string|max:100|unique:it_assets,asset_code',
            'name' => 'required|string|max:255',
            'device_type_id' => 'required|exists:it_device_types,id',
            'department_id' => 'nullable|exists:it_departments,id',
            'location_detail' => 'nullable|string|max:255',
            'custodian_name' => 'nullable|string|max:255',
            'budget_year' => 'nullable|string|max:10',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'hardware_id' => 'nullable|string|max:100',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Build composite specs string
        $specsParts = [];
        if ($audit->cpu_model) $specsParts[] = "CPU " . $audit->cpu_model . ($audit->cpu_speed ? " (" . $audit->cpu_speed . ")" : "");
        if ($audit->ram_capacity) $specsParts[] = "RAM " . $audit->ram_capacity . "GB " . ($audit->ram_type ?: '') . ($audit->ram_bus ? " (" . $audit->ram_bus . ")" : "");
        if ($audit->storage_capacity) $specsParts[] = ($audit->storage_type ?: 'Storage') . " " . $audit->storage_capacity;
        if ($audit->storage_second) $specsParts[] = "Drive 2: " . $audit->storage_second;
        if ($audit->os_name) $specsParts[] = "OS " . $audit->os_name;
        if ($audit->gpu_model) $specsParts[] = "GPU " . $audit->gpu_model;
        $specsString = implode(' | ', $specsParts);

        // Create Asset
        $asset = Asset::create([
            'asset_code' => $validated['asset_code'],
            'name' => $validated['name'],
            'device_type_id' => $validated['device_type_id'],
            'department_id' => $validated['department_id'] ?? null,
            'location_detail' => $validated['location_detail'] ?? null,
            'custodian_name' => $validated['custodian_name'] ?? null,
            'budget_year' => $validated['budget_year'] ?: (string)$audit->fiscal_year,
            'brand' => $validated['brand'] ?: $audit->brand,
            'model' => $validated['model'] ?: $audit->model,
            'serial_number' => $validated['serial_number'] ?: $audit->serial_number,
            'hardware_id' => $validated['hardware_id'] ?: $audit->hardware_id,
            'cpu_model' => $audit->cpu_model,
            'cpu_speed' => $audit->cpu_speed,
            'ram_capacity' => $audit->ram_capacity,
            'ram_type' => $audit->ram_type,
            'ram_bus' => $audit->ram_bus,
            'ram_slots' => $audit->ram_slots,
            'storage_type' => $audit->storage_type,
            'storage_capacity' => $audit->storage_capacity,
            'storage_second' => $audit->storage_second,
            'os_name' => $audit->os_name,
            'os_license' => $audit->os_license,
            'gpu_model' => $audit->gpu_model,
            'monitor_size' => $audit->monitor_size,
            'ip_address' => $audit->ip_address,
            'mac_address' => $audit->mac_address,
            'specs' => $specsString,
            'price' => $validated['price'] ?? null,
            'status' => 'active',
            'notes' => $validated['notes'] ?? ("สร้างจากผลการตรวจนับอัตโนมัติ (HardwareID: " . ($audit->hardware_id ?: '-') . ")"),
            'last_audited_at' => now(),
            'last_audited_fiscal_year' => $audit->fiscal_year,
        ]);

        // Link audit record to newly created asset and approve
        $audit->asset_id = $asset->id;
        $audit->status = 'approved';
        $audit->reviewed_by = $user->id;
        $audit->reviewed_at = now();
        $audit->review_notes = 'ลงทะเบียนเป็นครุภัณฑ์ใหม่เรียบร้อยแล้ว: ' . $asset->asset_code;
        $audit->save();

        // Audit Log
        AuditLog::record(
            'create',
            'assets',
            "เพิ่มครุภัณฑ์ใหม่ {$asset->asset_code} ({$asset->name}) จากผลตรวจนับฮาร์ดแวร์ประจำปีงบประมาณ {$audit->fiscal_year} (HardwareID: {$asset->hardware_id})",
            $asset,
            null,
            null,
            $request,
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "ลงทะเบียนครุภัณฑ์ใหม่ '{$asset->asset_code}' และเชื่อมโยงผลการตรวจนับเรียบร้อยแล้ว",
                'asset' => [
                    'id' => $asset->id,
                    'asset_code' => $asset->asset_code,
                    'name' => $asset->name,
                    'hardware_id' => $asset->hardware_id,
                ],
            ]);
        }

        return back()->with('success', "ลงทะเบียนครุภัณฑ์ใหม่ '{$asset->asset_code}' สำเร็จและบันทึกผลการตรวจนับเรียบร้อยแล้ว");
    }

    /**
     * AJAX Endpoint: Search active assets to link with an unmatched Hardware Audit
     */
    public function searchAssetsForLinking(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $q = trim((string) $request->input('q', ''));
        if (mb_strlen($q) < 1) {
            return response()->json(['success' => true, 'assets' => []]);
        }

        $assets = Asset::with(['department', 'deviceType'])
            ->where(function ($query) use ($q) {
                $query->where('asset_code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%")
                      ->orWhere('serial_number', 'like', "%{$q}%")
                      ->orWhere('hardware_id', 'like', "%{$q}%")
                      ->orWhere('custodian_name', 'like', "%{$q}%")
                      ->orWhere('brand', 'like', "%{$q}%")
                      ->orWhere('model', 'like', "%{$q}%")
                      ->orWhereHas('department', function ($sub) use ($q) {
                          $sub->where('name', 'like', "%{$q}%");
                      });
            })
            ->where('status', 'active')
            ->orderBy('asset_code')
            ->take(20)
            ->get();

        $formatted = $assets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'brand' => $asset->brand,
                'model' => $asset->model,
                'serial_number' => $asset->serial_number,
                'hardware_id' => $asset->hardware_id,
                'department_name' => $asset->department?->name ?? 'ไม่ระบุแผนก',
                'device_type_name' => $asset->deviceType?->name ?? 'คอมพิวเตอร์',
                'custodian_name' => $asset->custodian_name ?? '-',
                'location_detail' => $asset->location_detail ?? '-',
                'cpu_model' => $asset->cpu_model ?? '-',
                'ram_capacity' => $asset->ram_capacity ? ($asset->ram_capacity . ' GB ' . ($asset->ram_type ?? '')) : '-',
                'storage_capacity' => $asset->storage_capacity ? (($asset->storage_type ?? 'Drive') . ' ' . $asset->storage_capacity) : '-',
                'os_name' => $asset->os_name ?? '-',
                'specs' => $asset->specs ?? '-',
                'last_audited_fiscal_year' => $asset->last_audited_fiscal_year,
                'status_label' => $asset->status_label,
            ];
        });

        return response()->json([
            'success' => true,
            'assets' => $formatted,
            'count' => $formatted->count(),
        ]);
    }

    /**
     * Admin Action: Link an unmatched Hardware Audit to an existing Asset in it_assets
     */
    public function linkAsset(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือแอดมินเท่านั้นที่มีสิทธิ์เชื่อมโยงครุภัณฑ์');
        }

        $audit = HardwareAudit::findOrFail($id);

        $validated = $request->validate([
            'asset_id' => 'required|exists:it_assets,id',
            'sync_specs' => 'nullable|boolean',
            'sync_hwid' => 'nullable|boolean',
            'sync_sn' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

        // Check if another active asset already uses this hardware_id
        if ($audit->hardware_id) {
            $existing = Asset::where('hardware_id', $audit->hardware_id)
                ->where('id', '!=', $asset->id)
                ->first();
            if ($existing) {
                $existing->hardware_id = null;
                $existing->save();
            }
        }

        // Link audit record to asset
        $audit->asset_id = $asset->id;

        // Sync options (default true)
        $syncHwid = $request->boolean('sync_hwid', true);
        $syncSn = $request->boolean('sync_sn', true);
        $syncSpecs = $request->boolean('sync_specs', true);

        if ($syncHwid && !empty($audit->hardware_id)) {
            $asset->hardware_id = $audit->hardware_id;
        }

        if ($syncSn && !empty($audit->serial_number)) {
            $asset->serial_number = $audit->serial_number;
        }

        if ($syncSpecs) {
            if ($audit->cpu_model) $asset->cpu_model = $audit->cpu_model;
            if ($audit->cpu_speed) $asset->cpu_speed = $audit->cpu_speed;
            if ($audit->ram_capacity) $asset->ram_capacity = $audit->ram_capacity;
            if ($audit->ram_type) $asset->ram_type = $audit->ram_type;
            if ($audit->ram_bus) $asset->ram_bus = $audit->ram_bus;
            if ($audit->ram_slots) $asset->ram_slots = $audit->ram_slots;
            if ($audit->storage_type) $asset->storage_type = $audit->storage_type;
            if ($audit->storage_capacity) $asset->storage_capacity = $audit->storage_capacity;
            if ($audit->storage_second) $asset->storage_second = $audit->storage_second;
            if ($audit->os_name) $asset->os_name = $audit->os_name;
            if ($audit->os_license) $asset->os_license = $audit->os_license;
            if ($audit->gpu_model) $asset->gpu_model = $audit->gpu_model;
            if ($audit->monitor_size) $asset->monitor_size = $audit->monitor_size;
            if ($audit->ip_address) $asset->ip_address = $audit->ip_address;
            if ($audit->mac_address) $asset->mac_address = $audit->mac_address;
            if ($audit->brand && empty($asset->brand)) $asset->brand = $audit->brand;
            if ($audit->model && empty($asset->model)) $asset->model = $audit->model;

            // Composite specs string
            $specsParts = [];
            if ($audit->cpu_model) $specsParts[] = "CPU " . $audit->cpu_model . ($audit->cpu_speed ? " (" . $audit->cpu_speed . ")" : "");
            if ($audit->ram_capacity) $specsParts[] = "RAM " . $audit->ram_capacity . "GB " . ($audit->ram_type ?: '') . ($audit->ram_bus ? " (" . $audit->ram_bus . ")" : "");
            if ($audit->storage_capacity) $specsParts[] = ($audit->storage_type ?: 'Storage') . " " . $audit->storage_capacity;
            if ($audit->storage_second) $specsParts[] = "Drive 2: " . $audit->storage_second;
            if ($audit->os_name) $specsParts[] = "OS " . $audit->os_name;
            if ($audit->gpu_model) $specsParts[] = "GPU " . $audit->gpu_model;
            if (!empty($specsParts)) {
                $asset->specs = implode(' | ', $specsParts);
            }
        }

        // Mark as audited in this fiscal year
        $asset->last_audited_at = now();
        $asset->last_audited_fiscal_year = $audit->fiscal_year;
        $asset->save();

        // Update audit status to approved
        $audit->status = 'approved';
        $audit->reviewed_by = $user->id;
        $audit->reviewed_at = now();
        $audit->review_notes = 'เชื่อมโยงเข้ากับครุภัณฑ์เดิม: ' . $asset->asset_code . ($request->filled('notes') ? ' (' . $request->notes . ')' : '');
        $audit->save();

        // Audit Log
        AuditLog::record(
            'update',
            'hardware_audits',
            "เชื่อมโยงผลตรวจสเปคเครื่อง {$audit->hostname} (HWID: {$audit->hardware_id}) เข้ากับครุภัณฑ์ {$asset->asset_code} ({$asset->name})",
            $audit,
            null,
            null,
            $request,
            $user
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "เชื่อมโยงเครื่อง '{$audit->hostname}' เข้ากับครุภัณฑ์ '{$asset->asset_code}' เรียบร้อยแล้ว",
                'asset' => [
                    'id' => $asset->id,
                    'asset_code' => $asset->asset_code,
                    'name' => $asset->name,
                    'hardware_id' => $asset->hardware_id,
                ],
            ]);
        }

        return back()->with('success', "เชื่อมโยงเครื่อง '{$audit->hostname}' เข้ากับครุภัณฑ์ '{$asset->asset_code}' สำเร็จและอัปเดตสเปคเรียบร้อยแล้ว");
    }

    /**
     * Admin Action: Batch Approve multiple submissions
     */
    public function batchApprove(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือแอดมินเท่านั้นที่มีสิทธิ์อนุมัติสเปค');
        }

        $ids = $request->input('audit_ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'กรุณาเลือกรายการที่ต้องการอนุมัติอย่างน้อย 1 รายการ');
        }

        $count = 0;
        foreach ($ids as $id) {
            $audit = HardwareAudit::find($id);
            if ($audit && $audit->status === 'pending') {
                if ($audit->asset_id) {
                    $asset = Asset::find($audit->asset_id);
                    if ($asset) {
                        if ($audit->hardware_id) $asset->hardware_id = $audit->hardware_id;
                        if ($audit->cpu_model) $asset->cpu_model = $audit->cpu_model;
                        if ($audit->cpu_speed) $asset->cpu_speed = $audit->cpu_speed;
                        if ($audit->ram_capacity) $asset->ram_capacity = $audit->ram_capacity;
                        if ($audit->ram_type) $asset->ram_type = $audit->ram_type;
                        if ($audit->ram_bus) $asset->ram_bus = $audit->ram_bus;
                        if ($audit->ram_slots) $asset->ram_slots = $audit->ram_slots;
                        if ($audit->storage_type) $asset->storage_type = $audit->storage_type;
                        if ($audit->storage_capacity) $asset->storage_capacity = $audit->storage_capacity;
                        if ($audit->os_name) $asset->os_name = $audit->os_name;
                        if ($audit->os_license) $asset->os_license = $audit->os_license;
                        if ($audit->gpu_model) $asset->gpu_model = $audit->gpu_model;
                        if ($audit->monitor_size) $asset->monitor_size = $audit->monitor_size;
                        if ($audit->ip_address) $asset->ip_address = $audit->ip_address;
                        if ($audit->mac_address) $asset->mac_address = $audit->mac_address;
                        if ($audit->serial_number && empty($asset->serial_number)) {
                            $asset->serial_number = $audit->serial_number;
                        }
                        $asset->last_audited_at = now();
                        $asset->last_audited_fiscal_year = $audit->fiscal_year;
                        $asset->save();
                    }
                }

                $audit->status = 'approved';
                $audit->reviewed_by = $user->id;
                $audit->reviewed_at = now();
                $audit->save();
                $count++;
            }
        }

        return back()->with('success', "อนุมัติและอัปเดตสเปคลงครุภัณฑ์สำเร็จจำนวน {$count} รายการ");
    }

    /**
     * Admin Action: Reject submission
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'ไม่มีสิทธิ์ดำเนินการ');
        }

        $audit = HardwareAudit::findOrFail($id);
        $audit->status = 'rejected';
        $audit->reviewed_by = $user->id;
        $audit->reviewed_at = now();
        $audit->review_notes = $request->input('notes', 'แอดมินปฏิเสธ');
        $audit->save();

        return back()->with('success', "ปฏิเสธรายการสแกนสเปคของ '{$audit->hostname}' เรียบร้อยแล้ว");
    }

    /**
     * Admin/Technician Action: Delete a Hardware Audit record
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้นที่มีสิทธิ์ลบข้อมูลการตรวจนับ');
        }

        $audit = HardwareAudit::findOrFail($id);
        $hostname = $audit->hostname ?: ($audit->asset ? $audit->asset->name : 'เครื่อง ID #' . $audit->id);
        $fiscalYear = $audit->fiscal_year;

        // Record in AuditLog
        AuditLog::record(
            'delete',
            'hardware_audits',
            "ลบรายการตรวจนับสเปคเครื่อง {$hostname} ประจำปีงบประมาณ {$fiscalYear} (HardwareID: " . ($audit->hardware_id ?: '-') . ")",
            $audit,
            null,
            null,
            $request,
            $user
        );

        $audit->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "ลบรายการตรวจนับสเปคของ '{$hostname}' เรียบร้อยแล้ว",
            ]);
        }

        return back()->with('success', "ลบรายการตรวจนับสเปคของ '{$hostname}' เรียบร้อยแล้ว");
    }

    /**
     * Admin/Technician Action: Batch Delete Hardware Audit records
     */
    public function batchDestroy(Request $request)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้นที่มีสิทธิ์ลบข้อมูลการตรวจนับ');
        }

        $ids = $request->input('audit_ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'กรุณาเลือกรายการที่ต้องการลบอย่างน้อย 1 รายการ');
        }

        $count = 0;
        foreach ($ids as $id) {
            $audit = HardwareAudit::find($id);
            if ($audit) {
                AuditLog::record(
                    'delete',
                    'hardware_audits',
                    "ลบรายการตรวจนับสเปคเครื่อง {$audit->hostname} ประจำปีงบประมาณ {$audit->fiscal_year}",
                    $audit,
                    null,
                    null,
                    $request,
                    $user
                );
                $audit->delete();
                $count++;
            }
        }

        return back()->with('success', "ลบรายการตรวจนับที่เลือกสำเร็จจำนวน {$count} รายการ");
    }

    /**
     * Print Annual Fiscal Year Hardware Audit Report (A4 Document)
     */
    public function printAnnualReport(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isUser()) {
            abort(403, 'ไม่มีสิทธิ์เข้าถึงรายงาน');
        }

        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());

        $auditedAssets = Asset::with(['department', 'deviceType'])
            ->where('last_audited_fiscal_year', $fiscalYear)
            ->orderBy('department_id')
            ->orderBy('asset_code')
            ->get();

        $totalAudited = $auditedAssets->count();

        $totalFleet = Asset::where('status', 'active')->count();
        $percentage = $totalFleet > 0 ? round(($totalAudited / $totalFleet) * 100, 1) : 0;

        $deptSummary = Department::where('is_active', true)->orderBy('name')->get()->map(function ($d) use ($fiscalYear) {
            $audited = Asset::where('department_id', $d->id)->where('last_audited_fiscal_year', $fiscalYear)->count();
            $total = Asset::where('department_id', $d->id)->where('status', 'active')->count();
            return (object)[
                'name' => $d->name,
                'audited' => $audited,
                'total' => $total,
                'percentage' => $total > 0 ? round(($audited / $total) * 100) : 0,
            ];
        })->filter(fn($d) => $d->total > 0);

        return view('hardware_audits.print_report', compact(
            'fiscalYear',
            'auditedAssets',
            'totalAudited',
            'totalFleet',
            'percentage',
            'deptSummary'
        ));
    }

    /**
     * Export CSV of Fiscal Year Hardware Audit
     */
    public function exportCsv(Request $request)
    {
        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"hardware_audit_fy{$fiscalYear}_" . date('Ymd_His') . ".csv\"",
        ];

        $callback = function () use ($fiscalYear) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, [
                'ปีงบประมาณ',
                'รหัสครุภัณฑ์',
                'ชื่ออุปกรณ์',
                'แผนก/หน่วยงาน',
                'Serial Number (S/N)',
                'ยี่ห้อ/รุ่น',
                'CPU',
                'RAM (GB)',
                'ชนิด RAM',
                'บัส RAM',
                'Storage',
                'ความจุ Storage',
                'Storage เสริม',
                'OS',
                'GPU',
                'IP Address',
                'MAC Address',
                'สถานะการตรวจนับ',
                'วันที่ตรวจนับ',
            ]);

            $assets = Asset::with('department')
                ->where('last_audited_fiscal_year', $fiscalYear)
                ->get();

            foreach ($assets as $a) {
                fputcsv($handle, [
                    $fiscalYear,
                    $a->asset_code,
                    $a->name,
                    $a->department?->name ?? 'ไม่ระบุ',
                    $a->serial_number,
                    "{$a->brand} {$a->model}",
                    $a->cpu_model,
                    $a->ram_capacity,
                    $a->ram_type,
                    $a->ram_bus,
                    $a->storage_type,
                    $a->storage_capacity,
                    $a->storage_second ?: '-',
                    $a->os_name,
                    $a->gpu_model,
                    $a->ip_address,
                    $a->mac_address,
                    'ตรวจนับและอัปเดตสเปคแล้ว',
                    $a->last_audited_at ? $a->last_audited_at->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * AJAX Endpoint: Inspect detailed hardware audit telemetry & raw agent data
     */
    public function inspect($id)
    {
        $audit = HardwareAudit::with(['asset.department', 'reviewer'])->findOrFail($id);

        $mdesIssues = [];
        if ($audit->ram_capacity && $audit->ram_capacity < 8) {
            $mdesIssues[] = "หน่วยความจำ (RAM) ต่ำกว่าเกณฑ์มาตรฐานขั้นต่ำ (ปัจจุบัน {$audit->ram_capacity} GB, มาตรฐาน ICT >= 8 GB)";
        }
        if ($audit->storage_type && stripos($audit->storage_type, 'HDD') !== false) {
            $mdesIssues[] = "ไดรฟ์เก็บข้อมูลเป็นจานหมุน (HDD) ควรพิจารณาอัปเกรดเป็น Solid State Drive (SSD)";
        }
        if ($audit->os_name && (stripos($audit->os_name, 'Windows 7') !== false || stripos($audit->os_name, 'Windows 8') !== false)) {
            $mdesIssues[] = "ระบบปฏิบัติการ ({$audit->os_name}) สิ้นสุดระยะเวลาสนับสนุนความปลอดภัยแล้ว (End-of-Life)";
        }

        $rescanCommand = 'irm "' . url('/agent/thc_audit_agent.ps1') . '" | iex';

        // Extract brand and model from raw_payload or asset if not directly set
        $brand = $audit->brand ?: ($audit->raw_payload['system']['Manufacturer'] ?? ($audit->asset?->brand ?? null));
        $model = $audit->model ?: ($audit->raw_payload['system']['Model'] ?? ($audit->asset?->model ?? null));

        $auditData = $audit->toArray();
        $auditData['brand'] = $brand;
        $auditData['model'] = $model;
        $auditData['rescan_command'] = $rescanCommand;

        return response()->json([
            'success' => true,
            'audit' => $auditData,
            'asset' => $audit->asset,
            'mdes_evaluation' => [
                'is_standard' => empty($mdesIssues),
                'issues' => $mdesIssues,
            ],
            'mdes_issues' => $mdesIssues,
            'is_mdes_standard' => empty($mdesIssues),
            'rescan_command' => $rescanCommand,
        ]);
    }

    /**
     * Admin Action: Trigger remote scan for a single machine
     */
    public function triggerScanSingle(Request $request, $id)
    {
        $user = Auth::user();
        if ($user && $user->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือแอดมินเท่านั้นที่มีสิทธิ์สั่งสแกน');
        }

        $audit = HardwareAudit::findOrFail($id);

        // Check for existing pending command in the last 2 minutes
        $existing = AgentCommand::where('target_audit_id', $audit->id)
            ->whereIn('status', ['pending', 'processing'])
            ->where('created_at', '>=', now()->subMinutes(2))
            ->latest()
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => "มีคำสั่งสแกนสำหรับเครื่อง {$audit->hostname} ค้างอยู่ในระบบแล้ว กำลังรอเครื่องตอบกลับ...",
                'command_id' => $existing->id,
                'status' => $existing->status,
                'hostname' => $audit->hostname,
                'is_existing' => true,
            ]);
        }

        $cmd = AgentCommand::create([
            'command' => 'scan',
            'target_type' => 'single',
            'target_hardware_id' => $audit->hardware_id,
            'target_hostname' => $audit->hostname,
            'target_audit_id' => $audit->id,
            'target_asset_id' => $audit->asset_id,
            'status' => 'pending',
            'requested_by' => Auth::id(),
            'parameters' => [
                'fiscal_year' => $audit->fiscal_year,
                'trigger_source' => 'web_single_action',
            ],
        ]);

        AuditLog::record('scan_trigger_single', 'hardware_audits', "สั่งสแกนเครื่อง {$audit->hostname} (Audit #{$audit->id})", $audit);

        return response()->json([
            'success' => true,
            'message' => "ส่งคำสั่งสแกนไปยังเครื่อง {$audit->hostname} เรียบร้อยแล้ว (รอเครื่องลูกข่ายดึงคำสั่ง)",
            'command_id' => $cmd->id,
            'hostname' => $audit->hostname,
            'hardware_id' => $audit->hardware_id,
        ]);
    }

    /**
     * Admin Action: Trigger remote scan for ALL machines with Agent installed
     */
    public function triggerScanAll(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือแอดมินเท่านั้นที่มีสิทธิ์สั่งสแกน');
        }

        $fiscalYear = (int)$request->input('fiscal_year', $this->getCurrentFiscalYear());

        // Find all distinct machines that have ever reported with an agent
        $agentAudits = HardwareAudit::where(function($q) {
                $q->whereNotNull('client_agent_version')
                  ->orWhereNotNull('hardware_id');
            })
            ->latest()
            ->get()
            ->unique(function ($item) {
                return !empty($item->hardware_id) ? $item->hardware_id : $item->hostname;
            });

        if ($agentAudits->isEmpty()) {
            $agentAudits = HardwareAudit::latest()
                ->get()
                ->unique('hostname');
        }

        $batchId = 'batch_' . Str::random(12);

        // 1. Broadcast command
        $broadcast = AgentCommand::create([
            'command' => 'scan',
            'batch_id' => $batchId,
            'target_type' => 'all',
            'status' => 'pending',
            'requested_by' => Auth::id(),
            'parameters' => [
                'fiscal_year' => $fiscalYear,
                'trigger_source' => 'web_scan_all_button',
            ],
        ]);

        // 2. Individual target records for tracking in UI
        $targets = [];
        $existingHwIds = [];
        $existingHosts = [];

        foreach ($agentAudits as $aud) {
            if (empty($aud->hostname) && empty($aud->hardware_id)) continue;

            $cmd = AgentCommand::create([
                'command' => 'scan',
                'batch_id' => $batchId,
                'target_type' => 'single',
                'target_hardware_id' => $aud->hardware_id,
                'target_hostname' => $aud->hostname,
                'target_audit_id' => $aud->id,
                'target_asset_id' => $aud->asset_id,
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'parameters' => [
                    'fiscal_year' => $fiscalYear,
                    'trigger_source' => 'batch_scan_all',
                ],
            ]);

            if ($aud->hardware_id) $existingHwIds[] = $aud->hardware_id;
            if ($aud->hostname) $existingHosts[] = $aud->hostname;

            $targets[] = [
                'id' => $cmd->id,
                'hostname' => $aud->hostname ?: 'เครื่อง ID #' . $aud->id,
                'hardware_id' => $aud->hardware_id,
                'ip' => $aud->ip_address,
                'brand_model' => trim(($aud->brand ?: '') . ' ' . ($aud->model ?: '')),
                'status' => 'pending',
            ];
        }

        // Also query active computers in it_assets not yet in targets
        $computerAssets = Asset::whereHas('deviceType', function ($q) {
                $q->whereIn('code', ['PC', 'NB', 'AIO', 'SERVER'])
                  ->orWhere('name', 'like', '%คอมพิวเตอร์%');
            })
            ->where('status', 'active')
            ->get();

        foreach ($computerAssets as $cas) {
            $hId = $cas->hardware_id;
            $hName = $cas->name ?: $cas->asset_code;
            if (($hId && in_array($hId, $existingHwIds)) || ($hName && in_array($hName, $existingHosts))) {
                continue;
            }
            if (empty($hId) && empty($hName)) continue;

            $cmd = AgentCommand::create([
                'command' => 'scan',
                'batch_id' => $batchId,
                'target_type' => 'single',
                'target_hardware_id' => $hId,
                'target_hostname' => $hName,
                'target_asset_id' => $cas->id,
                'status' => 'pending',
                'requested_by' => Auth::id(),
                'parameters' => [
                    'fiscal_year' => $fiscalYear,
                    'trigger_source' => 'batch_scan_all_asset',
                ],
            ]);

            if ($hId) $existingHwIds[] = $hId;
            if ($hName) $existingHosts[] = $hName;

            $targets[] = [
                'id' => $cmd->id,
                'hostname' => $hName,
                'hardware_id' => $hId,
                'ip' => $cas->ip_address,
                'brand_model' => trim(($cas->brand ?: '') . ' ' . ($cas->model ?: '')),
                'status' => 'pending',
            ];
        }

        AuditLog::record('scan_trigger_all', 'hardware_audits', "สั่งสแกนคอมพิวเตอร์ทุกเครื่องที่มี Agent (จำนวน " . count($targets) . " เครื่อง)", null);

        return response()->json([
            'success' => true,
            'message' => "ส่งคำสั่งสแกนไปยังเครื่องทั้งหมด " . count($targets) . " เครื่อง เรียบร้อยแล้ว",
            'batch_id' => $batchId,
            'total_targets' => count($targets),
            'targets' => $targets,
        ]);
    }

    /**
     * Query status of a command or batch
     */
    public function getCommandStatus(Request $request)
    {
        if ($request->filled('command_id')) {
            $cmd = AgentCommand::with(['targetAudit', 'targetAsset'])->find($request->input('command_id'));
            if (!$cmd) {
                return response()->json(['success' => false, 'message' => 'ไม่พบคำสั่ง'], 404);
            }

            $audit = $cmd->targetAudit ?: HardwareAudit::where(function ($q) use ($cmd) {
                if ($cmd->target_hardware_id) $q->where('hardware_id', $cmd->target_hardware_id);
                if ($cmd->target_hostname) $q->orWhere('hostname', $cmd->target_hostname);
            })->latest()->first();

            $telemetry = null;
            if ($audit && $cmd->status === 'completed') {
                $telemetry = [
                    'audit_id' => $audit->id,
                    'received_at' => $audit->updated_at ? $audit->updated_at->format('H:i:s น.') : null,
                    'ip' => $audit->ip_address ?: $cmd->ip_address,
                    'cpu' => $audit->cpu_model,
                    'ram' => $audit->ram_capacity ? "{$audit->ram_capacity} GB {$audit->ram_type}" : null,
                    'storage' => "{$audit->storage_type} {$audit->storage_capacity}",
                    'os' => $audit->os_name,
                    'agent_version' => $audit->client_agent_version,
                ];
            }

            return response()->json([
                'success' => true,
                'command' => [
                    'id' => $cmd->id,
                    'status' => $cmd->status,
                    'hostname' => $cmd->target_hostname,
                    'hardware_id' => $cmd->target_hardware_id,
                    'ip' => $cmd->ip_address ?: ($audit?->ip_address),
                    'executed_at' => $cmd->executed_at ? $cmd->executed_at->format('d/m/Y H:i:s น.') : null,
                    'result_summary' => $cmd->result_summary,
                    'telemetry' => $telemetry,
                ]
            ]);
        }

        if ($request->filled('batch_id')) {
            $batchId = $request->input('batch_id');
            $commands = AgentCommand::where('batch_id', $batchId)
                ->where('target_type', 'single')
                ->with(['targetAudit', 'targetAsset'])
                ->get();

            $total = $commands->count();
            $completed = $commands->where('status', 'completed')->count();
            $processing = $commands->where('status', 'processing')->count();
            $pending = $commands->where('status', 'pending')->count();
            $failed = $commands->where('status', 'failed')->count();

            return response()->json([
                'success' => true,
                'batch_id' => $batchId,
                'total' => $total,
                'completed' => $completed,
                'processing' => $processing,
                'pending' => $pending,
                'failed' => $failed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'items' => $commands->map(function ($c) {
                    $audit = $c->targetAudit ?: HardwareAudit::where(function ($q) use ($c) {
                        if ($c->target_hardware_id) $q->where('hardware_id', $c->target_hardware_id);
                        if ($c->target_hostname) $q->orWhere('hostname', $c->target_hostname);
                    })->latest()->first();

                    $asset = $c->targetAsset;
                    $brandModel = $audit ? trim(($audit->brand ?: '') . ' ' . ($audit->model ?: '')) : ($asset ? trim(($asset->brand ?: '') . ' ' . ($asset->model ?: '')) : null);
                    $ip = $c->ip_address ?: ($audit ? $audit->ip_address : ($asset ? $asset->ip_address : null));

                    $telemetry = null;
                    if ($audit && $c->status === 'completed') {
                        $telemetry = [
                            'audit_id' => $audit->id,
                            'cpu' => $audit->cpu_model,
                            'ram' => $audit->ram_capacity ? "{$audit->ram_capacity} GB" : null,
                            'storage' => "{$audit->storage_type} {$audit->storage_capacity}",
                            'os' => $audit->os_name,
                            'received_at' => $audit->updated_at ? $audit->updated_at->format('H:i:s น.') : null,
                            'agent_version' => $audit->client_agent_version,
                        ];
                    }

                    return [
                        'id' => $c->id,
                        'hostname' => $c->target_hostname,
                        'hardware_id' => $c->target_hardware_id,
                        'ip' => $ip,
                        'brand_model' => $brandModel,
                        'status' => $c->status,
                        'executed_at' => $c->executed_at ? $c->executed_at->format('H:i:s น.') : null,
                        'result_summary' => $c->result_summary,
                        'telemetry' => $telemetry,
                    ];
                }),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'โปรดระบุ command_id หรือ batch_id'], 400);
    }

    /**
     * Get live online agents connected to the system
     */
    public function getOnlineStatus(Request $request)
    {
        $registry = Cache::get('agent_online_registry', []);
        if (!is_array($registry)) $registry = [];

        $nowTs = now()->timestamp;
        $activeAgents = array_filter($registry, function ($a) use ($nowTs) {
            return isset($a['expires_at']) && $a['expires_at'] > $nowTs;
        });

        return response()->json([
            'success' => true,
            'online_count' => count($activeAgents),
            'agents' => array_values($activeAgents),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
