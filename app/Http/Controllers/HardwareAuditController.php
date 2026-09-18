<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HardwareAudit;
use App\Models\Asset;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
     * API Endpoint: Receive hardware specs submission from client-side PowerShell Agent
     * Publicly accessible by client agent (CSRF-exempt)
     */
    public function submit(Request $request)
    {
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
        ]);
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
            'agedOver5YearsCount'
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
}
