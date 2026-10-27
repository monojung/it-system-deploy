<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HardwareAudit;
use App\Models\Asset;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HardwareAuditApiController extends Controller
{
    /**
     * Get current Thai fiscal year (e.g. 2568, 2569)
     */
    protected function getCurrentFiscalYear(): int
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $defaultFiscalYear = ($currentMonth >= 10) ? ($currentYear + 1 + 543) : ($currentYear + 543);
        if (function_exists('setting')) {
            return (int) setting('fiscal_year_current', $defaultFiscalYear);
        }
        return (int) $defaultFiscalYear;
    }

    /**
     * Ensure text is clean, valid UTF-8 and heal ISO-8859-1 mojibake
     */
    protected function sanitizeUtf8(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        if (preg_match('/[àáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ]/', $text)) {
            $attempt = @mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
            if ($attempt !== false && preg_match('/[\x{0E00}-\x{0E7F}]/u', $attempt)) {
                $text = $attempt;
            }
        }

        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = @mb_convert_encoding($text, 'UTF-8', 'Windows-874, TIS-620, ISO-8859-1, ASCII');
        }

        return trim($text);
    }

    /**
     * Normalize and sanitize payload fields
     */
    protected function normalizePayload(Request $request): void
    {
        if (!$request->filled('client_agent_version') && $request->filled('agent_version')) {
            $request->merge(['client_agent_version' => $request->input('agent_version')]);
        }
        if (!$request->filled('monitor_size') && $request->filled('monitor_info')) {
            $request->merge(['monitor_size' => $request->input('monitor_info')]);
        }
        if ($request->has('ram_capacity') && is_numeric($request->input('ram_capacity'))) {
            $request->merge(['ram_capacity' => (int) round((float) $request->input('ram_capacity'))]);
        }
        if ($request->has('storage_capacity') && is_numeric($request->input('storage_capacity'))) {
            $scVal = (float) $request->input('storage_capacity');
            $scFormatted = $scVal >= 900 ? (round($scVal / 1000, 1) . ' TB') : (round($scVal) . ' GB');
            $request->merge(['storage_capacity' => $scFormatted]);
        }
    }

    /**
     * 1. GET /api/hardware-audits or /api/hardware-audit/submit
     * List audits or provide API service health/schema if requested via GET
     */
    public function index(Request $request)
    {
        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());
        $status = $request->input('status');
        $search = trim((string) $request->input('search', ''));
        $departmentId = $request->input('department_id');
        $perPage = min(100, max(5, (int) $request->input('per_page', 20)));

        $query = HardwareAudit::with(['asset.department', 'asset.deviceType', 'reviewer']);

        if ($fiscalYear > 0) {
            $query->where('fiscal_year', $fiscalYear);
        }

        if (!empty($status) && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('hostname', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('hardware_id', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('cpu_model', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('asset_code', 'like', "%{$search}%")
                         ->orWhere('it_code', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($departmentId)) {
            $query->whereHas('asset', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $counts = [
            'total' => HardwareAudit::where('fiscal_year', $fiscalYear)->count(),
            'pending' => HardwareAudit::where('fiscal_year', $fiscalYear)->where('status', 'pending')->count(),
            'approved' => HardwareAudit::where('fiscal_year', $fiscalYear)->where('status', 'approved')->count(),
            'rejected' => HardwareAudit::where('fiscal_year', $fiscalYear)->where('status', 'rejected')->count(),
        ];

        $audits = $query->latest('id')->paginate($perPage);

        return response()->json([
            'success' => true,
            'service' => 'THC Hardware Audit REST API',
            'fiscal_year' => $fiscalYear,
            'counts' => $counts,
            'data' => $audits->items(),
            'pagination' => [
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'per_page' => $audits->perPage(),
                'total' => $audits->total(),
            ],
            'timestamp' => now()->toIso8601String(),
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 2. POST /api/hardware-audits and /api/hardware-audit/submit
     * Ingest/record hardware specifications directly from any PC, script, or web client
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
                'endpoint' => url('/api/hardware-audits'),
                'version' => '3.0.0-REST',
                'current_fiscal_year' => $fiscalYear,
                'supported_methods' => ['POST', 'GET'],
                'payload_format' => 'application/json',
                'stats' => [
                    'current_fiscal_year_audits' => $auditedCount,
                ],
                'message' => 'ระบบ REST API ตรวจนับและติดตามสเปคคอมพิวเตอร์พร้อมใช้งาน (กรุณาส่งข้อมูลฮาร์ดแวร์ผ่าน HTTP POST ด้วยรูปแบบ application/json)',
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

        $this->normalizePayload($request);

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
            'ram_capacity' => 'nullable|numeric',
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
            'asset_id' => 'nullable|integer|exists:it_assets,id',
            'fiscal_year' => 'nullable|integer',
        ]);

        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());
        $ip = $request->ip() ?: $request->input('ip_address');

        // Clean Brand, Model, and CPU Model for optimal readability
        $brand = trim((string) $request->input('brand', ''));
        if (preg_match('/To be filled by O\.E\.M\.|System manufacturer|Default string/i', $brand) || $brand === '') {
            $brand = 'เครื่องประกอบ (Custom PC)';
        }

        $model = trim((string) $request->input('model', ''));
        if (preg_match('/To be filled by O\.E\.M\.|System Product Name|Default string/i', $model) || $model === '') {
            $model = 'เครื่องประกอบ (DIY/Clone)';
        }

        $cpuModel = trim((string) $request->input('cpu_model', ''));
        if (!empty($cpuModel)) {
            $cpuModel = preg_replace('/\s*@\s*[\d\.]+\s*GHz/i', '', $cpuModel);
            $cpuModel = str_ireplace(['(R)', '(TM)'], '', $cpuModel);
            $cpuModel = trim(preg_replace('/\s+/', ' ', $cpuModel));
        }

        // Clean HardwareID and Serial Number
        $hardwareId = trim((string) $request->input('hardware_id', ''));
        if (preg_match('/^[0F-]{36}$/i', $hardwareId) || preg_match('/Default string|To be filled by O\.E\.M\.|None/i', $hardwareId)) {
            $hardwareId = '';
        }

        $serial = trim((string) $request->input('serial_number', ''));
        if (preg_match('/Default string|To be filled by O\.E\.M\.|None/i', $serial)) {
            $serial = '';
        }

        $rawHostname = trim((string) $request->input('hostname', ''));
        $cleanMac = trim((string) $request->input('mac_address', ''));

        // Locate existing audit record for this machine in the current fiscal year
        $auditQuery = HardwareAudit::where('fiscal_year', $fiscalYear);
        if (!empty($hardwareId)) {
            $auditQuery->where('hardware_id', $hardwareId);
        } elseif (!empty($serial)) {
            $auditQuery->where('serial_number', $serial);
        } elseif (!empty($rawHostname)) {
            $auditQuery->where('hostname', $rawHostname);
        } else {
            // If neither hardwareId, serial, nor hostname provided, create a new record
            $auditQuery->whereRaw('1 = 0');
        }

        $audit = $auditQuery->first();
        $isNew = false;
        if (!$audit) {
            $audit = new HardwareAudit();
            $audit->fiscal_year = $fiscalYear;
            $audit->status = 'pending';
            $isNew = true;
        }

        // Explicit asset link priority: from request or existing verified link
        $matchedAsset = null;
        if ($request->filled('asset_id')) {
            $matchedAsset = Asset::find($request->input('asset_id'));
        } elseif (!empty($audit->asset_id)) {
            $matchedAsset = Asset::find($audit->asset_id);
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
                $oldVal = (string) ($matchedAsset->$field ?? '');
                $newVal = (string) ($request->input($field) ?? '');
                if (!empty($newVal) && !empty($oldVal) && $oldVal !== $newVal) {
                    $diff[$field] = [
                        'label' => $label,
                        'old' => $oldVal,
                        'new' => $newVal,
                    ];
                }
            }
        }

        // Populate Audit Record
        if ($matchedAsset) {
            $audit->asset_id = $matchedAsset->id;
        }
        $audit->hostname = $this->sanitizeUtf8($rawHostname);
        $audit->hardware_id = $hardwareId;
        $audit->serial_number = $this->sanitizeUtf8($serial);
        $audit->mac_address = $cleanMac;
        $audit->ip_address = $ip;
        $audit->brand = $this->sanitizeUtf8($brand);
        $audit->model = $this->sanitizeUtf8($model);
        $audit->device_type_code = strtoupper(trim((string) $request->input('device_type_code', 'PC')));
        $audit->cpu_model = $this->sanitizeUtf8($cpuModel);
        $audit->cpu_speed = $this->sanitizeUtf8((string) $request->input('cpu_speed', ''));
        $audit->ram_capacity = $request->input('ram_capacity');
        $audit->ram_type = $this->sanitizeUtf8((string) $request->input('ram_type', ''));
        $audit->ram_bus = $this->sanitizeUtf8((string) $request->input('ram_bus', ''));
        $audit->ram_slots = $this->sanitizeUtf8((string) $request->input('ram_slots', ''));
        $audit->storage_type = $this->sanitizeUtf8((string) $request->input('storage_type', ''));
        $audit->storage_capacity = $this->sanitizeUtf8((string) $request->input('storage_capacity', ''));
        $audit->storage_second = $this->sanitizeUtf8((string) $request->input('storage_second', ''));
        $audit->os_name = $this->sanitizeUtf8((string) $request->input('os_name', ''));
        $audit->os_license = $this->sanitizeUtf8((string) $request->input('os_license', ''));
        $audit->gpu_model = $this->sanitizeUtf8((string) $request->input('gpu_model', ''));
        $audit->monitor_size = $this->sanitizeUtf8((string) $request->input('monitor_size', ''));
        $audit->client_agent_version = '3.0.0-API';
        $audit->raw_payload = $request->all();
        $audit->specs_diff = !empty($diff) ? $diff : null;
        $audit->save();

        // Acknowledge AgentCommand if dispatched by Admin
        $commandId = $request->input('command_id');
        if (!empty($commandId)) {
            $cmd = \App\Models\AgentCommand::find($commandId);
            if ($cmd) {
                $cmd->markAsCompleted('สำเร็จ: ได้รับข้อมูลสเปคและบันทึกผลแล้ว (Audit ID: #' . $audit->id . ')', $ip);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $isNew ? 'บันทึกข้อมูลตรวจนับสเปกคอมพิวเตอร์เข้าสู่ระบบเรียบร้อยแล้ว' : 'อัปเดตข้อมูลสเปกคอมพิวเตอร์เรียบร้อยแล้ว',
            'audit_id' => $audit->id,
            'fiscal_year' => $audit->fiscal_year,
            'status' => $audit->status,
            'hostname' => $audit->hostname,
            'is_linked' => !empty($audit->asset_id),
            'linked_asset' => $matchedAsset ? [
                'id' => $matchedAsset->id,
                'asset_code' => $matchedAsset->asset_code,
                'it_code' => $matchedAsset->it_code,
                'name' => $matchedAsset->name,
            ] : null,
            'specs_diff' => $audit->specs_diff,
            'data' => $audit,
            'timestamp' => now()->toIso8601String(),
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 3. GET /api/hardware-audits/{id}
     * Get specific audit details
     */
    public function show($id)
    {
        $audit = HardwareAudit::with(['asset.department', 'asset.deviceType', 'reviewer'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $audit,
            'linked_asset' => $audit->asset,
            'specs_diff' => $audit->specs_diff,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 4. PUT/PATCH /api/hardware-audits/{id}
     * Update audit record specs manually
     */
    public function update(Request $request, $id)
    {
        $audit = HardwareAudit::findOrFail($id);

        $this->normalizePayload($request);

        $fields = [
            'hostname', 'hardware_id', 'serial_number', 'mac_address', 'ip_address',
            'brand', 'model', 'device_type_code', 'cpu_model', 'cpu_speed',
            'ram_capacity', 'ram_type', 'ram_bus', 'ram_slots',
            'storage_type', 'storage_capacity', 'storage_second',
            'os_name', 'os_license', 'gpu_model', 'monitor_size', 'status', 'review_notes',
        ];

        foreach ($fields as $f) {
            if ($request->has($f)) {
                $audit->$f = $this->sanitizeUtf8((string) $request->input($f));
            }
        }

        if ($request->filled('ram_capacity')) {
            $audit->ram_capacity = (int) round((float) $request->input('ram_capacity'));
        }

        $audit->save();

        return response()->json([
            'success' => true,
            'message' => 'แก้ไขข้อมูลผลการตรวจนับเรียบร้อยแล้ว',
            'data' => $audit,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 5. POST /api/hardware-audits/{id}/approve
     * Approve audit and synchronize hardware specs to the linked asset
     */
    public function approve(Request $request, $id)
    {
        $audit = HardwareAudit::findOrFail($id);

        $userId = Auth::id() ?: $request->input('reviewed_by');

        $audit->status = 'approved';
        $audit->reviewed_by = $userId;
        $audit->reviewed_at = now();
        $audit->review_notes = $request->input('notes') ?: 'อนุมัติผลการตรวจนับผ่าน API';
        $audit->save();

        // Sync specs to linked asset if linked
        $asset = $audit->asset;
        if ($asset) {
            if (!empty($audit->cpu_model)) $asset->cpu_model = $audit->cpu_model;
            if (!empty($audit->cpu_speed)) $asset->cpu_speed = $audit->cpu_speed;
            if (!empty($audit->ram_capacity)) $asset->ram_capacity = $audit->ram_capacity;
            if (!empty($audit->ram_type)) $asset->ram_type = $audit->ram_type;
            if (!empty($audit->ram_bus)) $asset->ram_bus = $audit->ram_bus;
            if (!empty($audit->ram_slots)) $asset->ram_slots = $audit->ram_slots;
            if (!empty($audit->storage_type)) $asset->storage_type = $audit->storage_type;
            if (!empty($audit->storage_capacity)) $asset->storage_capacity = $audit->storage_capacity;
            if (!empty($audit->storage_second)) $asset->storage_second = $audit->storage_second;
            if (!empty($audit->os_name)) $asset->os_name = $audit->os_name;
            if (!empty($audit->os_license)) $asset->os_license = $audit->os_license;
            if (!empty($audit->gpu_model)) $asset->gpu_model = $audit->gpu_model;
            if (!empty($audit->monitor_size)) $asset->monitor_size = $audit->monitor_size;
            if (!empty($audit->ip_address)) $asset->ip_address = $audit->ip_address;
            if (!empty($audit->mac_address)) $asset->mac_address = $audit->mac_address;
            if (!empty($audit->hardware_id)) $asset->hardware_id = $audit->hardware_id;

            $asset->last_audited_at = now();
            $asset->last_audited_fiscal_year = $audit->fiscal_year;
            $asset->save();

            $audit->specs_diff = null;
            $audit->save();
        }

        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => 'approve_hardware_audit_api',
                'description' => "อนุมัติข้อมูลสเปกเครื่อง {$audit->hostname} (ID: {$audit->id})",
                'ip_address' => $request->ip(),
            ]);
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'อนุมัติผลการตรวจนับและอัปเดตสเปกลงฐานข้อมูลครุภัณฑ์เรียบร้อยแล้ว',
            'audit' => $audit,
            'asset' => $asset,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 6. POST /api/hardware-audits/{id}/reject
     * Reject an audit record
     */
    public function reject(Request $request, $id)
    {
        $audit = HardwareAudit::findOrFail($id);
        $userId = Auth::id() ?: $request->input('reviewed_by');

        $audit->status = 'rejected';
        $audit->reviewed_by = $userId;
        $audit->reviewed_at = now();
        $audit->review_notes = $request->input('notes') ?: 'ปฏิเสธผลตรวจนับผ่าน API';
        $audit->save();

        return response()->json([
            'success' => true,
            'message' => 'ปฏิเสธผลการตรวจนับเรียบร้อยแล้ว',
            'audit' => $audit,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 7. POST /api/hardware-audits/{id}/link-asset
     * Link audit to existing asset
     */
    public function linkAsset(Request $request, $id)
    {
        $request->validate([
            'asset_id' => 'required|integer|exists:it_assets,id',
        ]);

        $audit = HardwareAudit::findOrFail($id);
        $asset = Asset::findOrFail($request->input('asset_id'));

        $audit->asset_id = $asset->id;

        // Recalculate specs diff
        $checks = [
            'cpu_model' => 'รุ่น CPU',
            'ram_capacity' => 'ความจุ RAM',
            'ram_type' => 'ชนิด RAM',
            'storage_type' => 'ชนิดพื้นที่จัดเก็บ',
            'storage_capacity' => 'ขนาดพื้นที่จัดเก็บ',
            'storage_second' => 'พื้นที่จัดเก็บเสริม',
            'os_name' => 'ระบบปฏิบัติการ',
            'gpu_model' => 'การ์ดจอ GPU',
        ];

        $diff = [];
        foreach ($checks as $field => $label) {
            $oldVal = (string) ($asset->$field ?? '');
            $newVal = (string) ($audit->$field ?? '');
            if (!empty($newVal) && !empty($oldVal) && $oldVal !== $newVal) {
                $diff[$field] = [
                    'label' => $label,
                    'old' => $oldVal,
                    'new' => $newVal,
                ];
            }
        }

        $audit->specs_diff = !empty($diff) ? $diff : null;
        $audit->save();

        return response()->json([
            'success' => true,
            'message' => "ผูกผลตรวจนับเข้ากับครุภัณฑ์ {$asset->asset_code} ({$asset->name}) เรียบร้อยแล้ว",
            'audit' => $audit,
            'asset' => $asset,
            'specs_diff' => $audit->specs_diff,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 8. POST /api/hardware-audits/{id}/unlink-asset
     * Unlink audit from asset
     */
    public function unlinkAsset(Request $request, $id)
    {
        $audit = HardwareAudit::findOrFail($id);
        $audit->asset_id = null;
        $audit->specs_diff = null;
        $audit->save();

        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกการผูกครุภัณฑ์เรียบร้อยแล้ว',
            'audit' => $audit,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 9. POST /api/hardware-audits/{id}/create-asset
     * Create brand new Asset record from audit data
     */
    public function createAsset(Request $request, $id = null)
    {
        $auditId = $id ?: $request->input('audit_id');
        $audit = $auditId ? HardwareAudit::findOrFail($auditId) : null;

        $request->validate([
            'asset_code' => 'required|string|max:100|unique:it_assets,asset_code',
            'department_id' => 'required|integer|exists:it_departments,id',
            'name' => 'nullable|string|max:255',
            'device_type_id' => 'nullable|integer|exists:it_device_types,id',
        ]);

        $deviceTypeId = $request->input('device_type_id');
        if (!$deviceTypeId && $audit) {
            $code = $audit->device_type_code ?: 'PC';
            $dt = DeviceType::where('code', $code)->first();
            if ($dt) $deviceTypeId = $dt->id;
        }

        $asset = new Asset();
        $asset->asset_code = trim($request->input('asset_code'));
        $asset->name = trim($request->input('name') ?: ($audit ? "คอมพิวเตอร์ {$audit->hostname} ({$audit->brand} {$audit->model})" : 'คอมพิวเตอร์'));
        $asset->department_id = $request->input('department_id');
        $asset->device_type_id = $deviceTypeId;
        $asset->status = 'active';

        if ($audit) {
            $asset->brand = $audit->brand;
            $asset->model = $audit->model;
            $asset->serial_number = $audit->serial_number;
            $asset->mac_address = $audit->mac_address;
            $asset->ip_address = $audit->ip_address;
            $asset->hardware_id = $audit->hardware_id;
            $asset->cpu_model = $audit->cpu_model;
            $asset->cpu_speed = $audit->cpu_speed;
            $asset->ram_capacity = $audit->ram_capacity;
            $asset->ram_type = $audit->ram_type;
            $asset->ram_bus = $audit->ram_bus;
            $asset->ram_slots = $audit->ram_slots;
            $asset->storage_type = $audit->storage_type;
            $asset->storage_capacity = $audit->storage_capacity;
            $asset->storage_second = $audit->storage_second;
            $asset->os_name = $audit->os_name;
            $asset->os_license = $audit->os_license;
            $asset->gpu_model = $audit->gpu_model;
            $asset->monitor_size = $audit->monitor_size;
            $asset->last_audited_at = now();
            $asset->last_audited_fiscal_year = $audit->fiscal_year;
        }

        $asset->save();

        if ($audit) {
            $audit->asset_id = $asset->id;
            $audit->status = 'approved';
            $audit->reviewed_by = Auth::id();
            $audit->reviewed_at = now();
            $audit->review_notes = 'สร้างครุภัณฑ์ใหม่และอนุมัติอัตโนมัติ';
            $audit->specs_diff = null;
            $audit->save();
        }

        return response()->json([
            'success' => true,
            'message' => "สร้างครุภัณฑ์ใหม่รหัส {$asset->asset_code} และผูกผลตรวจนับเรียบร้อยแล้ว",
            'asset' => $asset,
            'audit' => $audit,
        ], 201, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 10. DELETE /api/hardware-audits/{id}
     * Delete an audit record
     */
    public function destroy($id)
    {
        $audit = HardwareAudit::findOrFail($id);
        $hostname = $audit->hostname;
        $audit->delete();

        return response()->json([
            'success' => true,
            'message' => "ลบรายการตรวจนับของเครื่อง {$hostname} (ID: {$id}) เรียบร้อยแล้ว",
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 11. POST /api/hardware-audits/batch-approve
     * Batch approve audit records
     */
    public function batchApprove(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรายการที่เลือก'], 400);
        }

        $audits = HardwareAudit::with('asset')->whereIn('id', $ids)->get();
        $approvedCount = 0;

        DB::transaction(function () use ($audits, &$approvedCount) {
            foreach ($audits as $audit) {
                $audit->status = 'approved';
                $audit->reviewed_by = Auth::id();
                $audit->reviewed_at = now();
                $audit->review_notes = 'อนุมัติแบบกลุ่มผ่าน REST API';
                $audit->save();

                if ($audit->asset) {
                    $asset = $audit->asset;
                    if (!empty($audit->cpu_model)) $asset->cpu_model = $audit->cpu_model;
                    if (!empty($audit->ram_capacity)) $asset->ram_capacity = $audit->ram_capacity;
                    if (!empty($audit->storage_capacity)) $asset->storage_capacity = $audit->storage_capacity;
                    if (!empty($audit->os_name)) $asset->os_name = $audit->os_name;
                    $asset->last_audited_at = now();
                    $asset->last_audited_fiscal_year = $audit->fiscal_year;
                    $asset->save();

                    $audit->specs_diff = null;
                    $audit->save();
                }
                $approvedCount++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "อนุมัติผลการตรวจนับทั้งหมด {$approvedCount} รายการเรียบร้อยแล้ว",
            'count' => $approvedCount,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 12. POST /api/hardware-audits/batch-delete
     * Batch delete audit records
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรายการที่เลือก'], 400);
        }

        $deletedCount = HardwareAudit::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => "ลบรายการตรวจนับทั้งหมด {$deletedCount} รายการเรียบร้อยแล้ว",
            'count' => $deletedCount,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 13. GET /api/hardware-audits/stats
     * Telemetry and hardware statistics for charts & dashboard
     */
    public function stats(Request $request)
    {
        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());

        $total = HardwareAudit::where('fiscal_year', $fiscalYear)->count();
        $approved = HardwareAudit::where('fiscal_year', $fiscalYear)->where('status', 'approved')->count();
        $pending = HardwareAudit::where('fiscal_year', $fiscalYear)->where('status', 'pending')->count();
        $rejected = HardwareAudit::where('fiscal_year', $fiscalYear)->where('status', 'rejected')->count();

        // MDES Standard Compliance (RAM >= 8GB, SSD storage, Win 10/11)
        $mdesPass = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->where('ram_capacity', '>=', 8)
            ->where(function ($q) {
                $q->where('storage_type', 'like', '%SSD%')
                  ->orWhere('storage_type', 'like', '%NVMe%');
            })
            ->where(function ($q) {
                $q->where('os_name', 'like', '%Windows 10%')
                  ->orWhere('os_name', 'like', '%Windows 11%');
            })
            ->count();

        // OS Breakdown
        $osBreakdown = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->select('os_name', DB::raw('count(*) as count'))
            ->groupBy('os_name')
            ->orderByDesc('count')
            ->get();

        // RAM Breakdown
        $ramBreakdown = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->select('ram_capacity', DB::raw('count(*) as count'))
            ->groupBy('ram_capacity')
            ->orderBy('ram_capacity')
            ->get();

        // Storage Breakdown
        $storageBreakdown = HardwareAudit::where('fiscal_year', $fiscalYear)
            ->select('storage_type', DB::raw('count(*) as count'))
            ->groupBy('storage_type')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'success' => true,
            'fiscal_year' => $fiscalYear,
            'summary' => [
                'total_audits' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
                'approval_rate_percent' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
                'mdes_compliant_count' => $mdesPass,
                'mdes_compliant_percent' => $total > 0 ? round(($mdesPass / $total) * 100, 1) : 0,
            ],
            'os_breakdown' => $osBreakdown,
            'ram_breakdown' => $ramBreakdown,
            'storage_breakdown' => $storageBreakdown,
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 14. GET /api/hardware-audits/schema
     * Data dictionary schema for client integration
     */
    public function schema()
    {
        return response()->json([
            'success' => true,
            'model' => 'HardwareAudit',
            'table' => 'it_hardware_audits',
            'fiscal_year_current' => $this->getCurrentFiscalYear(),
            'fields' => [
                'fiscal_year' => ['type' => 'integer', 'required' => false, 'default' => 'current_fiscal_year', 'description' => 'ปีงบประมาณไทย เช่น 2568, 2569'],
                'hostname' => ['type' => 'string(100)', 'required' => true, 'description' => 'ชื่อเครื่องคอมพิวเตอร์ (Computer Name / NetBIOS)'],
                'hardware_id' => ['type' => 'string(100)', 'required' => false, 'description' => 'รหัส Hardware UUID / Motherboard Serial'],
                'serial_number' => ['type' => 'string(100)', 'required' => false, 'description' => 'หมายเลขซีเรียลนัมเบอร์ของตัวเครื่อง (BIOS Serial)'],
                'mac_address' => ['type' => 'string(50)', 'required' => false, 'description' => 'MAC Address ของการ์ดเครือข่ายหลัก'],
                'ip_address' => ['type' => 'string(45)', 'required' => false, 'description' => 'หมายเลข IP Address ในระบบเครือข่าย'],
                'brand' => ['type' => 'string(100)', 'required' => false, 'description' => 'ยี่ห้อผู้ผลิต เช่น Dell, HP, Lenovo, Acer, DIY'],
                'model' => ['type' => 'string(100)', 'required' => false, 'description' => 'รุ่นของคอมพิวเตอร์ เช่น OptiPlex 3080, ThinkCentre M70s'],
                'device_type_code' => ['type' => 'string(20)', 'required' => false, 'default' => 'PC', 'description' => 'รหัสประเภทอุปกรณ์ เช่น PC, NB, AIO, SVR'],
                'cpu_model' => ['type' => 'string(150)', 'required' => false, 'description' => 'รุ่นตัวประมวลผล CPU เช่น Intel Core i5-12400'],
                'cpu_speed' => ['type' => 'string(50)', 'required' => false, 'description' => 'ความเร็วสัญญาณนาฬิกา เช่น 2.50 GHz (6 Cores / 12 Threads)'],
                'ram_capacity' => ['type' => 'integer', 'required' => false, 'description' => 'ความจุหน่วยความจำ RAM ทั้งหมด (หน่วย: GB) เช่น 8, 16, 32'],
                'ram_type' => ['type' => 'string(30)', 'required' => false, 'description' => 'ชนิด RAM เช่น DDR4, DDR5, DDR3'],
                'ram_bus' => ['type' => 'string(30)', 'required' => false, 'description' => 'ความถี่บัสแรม เช่น 3200 MHz, 2666 MHz'],
                'ram_slots' => ['type' => 'string(50)', 'required' => false, 'description' => 'การใช้งานสล็อตแรม เช่น 1 Slot (16GB x 1)'],
                'storage_type' => ['type' => 'string(50)', 'required' => false, 'description' => 'ชนิดพื้นที่จัดเก็บ เช่น SSD NVMe M.2, SSD SATA, HDD SATA'],
                'storage_capacity' => ['type' => 'string(50)', 'required' => false, 'description' => 'ขนาดความจุฮาร์ดดิสก์หลัก เช่น 256 GB, 512 GB, 1 TB'],
                'storage_second' => ['type' => 'string(255)', 'required' => false, 'description' => 'ไดรฟ์เสริมสำรองข้อมูล เช่น HDD SATA 1 TB'],
                'os_name' => ['type' => 'string(100)', 'required' => false, 'description' => 'ชื่อระบบปฏิบัติการ เช่น Windows 11 Pro, Windows 10 Pro'],
                'os_license' => ['type' => 'string(100)', 'required' => false, 'description' => 'สิทธิ์การใช้งาน OS เช่น OEM (ติดเครื่อง/BIOS), Retail, Volume'],
                'gpu_model' => ['type' => 'string(150)', 'required' => false, 'description' => 'รุ่นการ์ดจอแสดงผล เช่น Intel UHD Graphics 730, NVIDIA GTX 1650'],
                'monitor_size' => ['type' => 'string(50)', 'required' => false, 'description' => 'ขนาดหน้าจอแสดงผล เช่น 23.8 นิ้ว IPS FHD, 15.6 นิ้ว FHD'],
                'asset_id' => ['type' => 'integer', 'required' => false, 'description' => 'รหัส ID ครุภัณฑ์ในระบบ (หากต้องการระบุเจาะจงผูกทันที)'],
            ],
            'sample_payload' => [
                'hostname' => 'OPD-MED-PC01',
                'brand' => 'Dell',
                'model' => 'OptiPlex 3090',
                'serial_number' => '8F9X4B2',
                'device_type_code' => 'PC',
                'cpu_model' => 'Intel Core i5-11500',
                'cpu_speed' => '2.70 GHz (6 Cores / 12 Threads)',
                'ram_capacity' => 16,
                'ram_type' => 'DDR4',
                'ram_bus' => '3200 MHz',
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'os_name' => 'Windows 11 Pro',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 750',
                'monitor_size' => '23.8 นิ้ว IPS FHD',
                'ip_address' => '192.168.2.45',
                'mac_address' => '00:1A:2B:3C:4D:5E',
            ],
        ], 200, ['Content-Type' => 'application/json; charset=utf-8'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
