<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HardwareAudit;
use App\Models\Asset;
use App\Models\Department;
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
     * API Endpoint: Receive hardware specs submission from client-side PowerShell Agent
     * Publicly accessible by client agent (CSRF-exempt)
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'hostname' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'mac_address' => 'nullable|string|max:50',
            'ip_address' => 'nullable|string|max:45',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'device_type_code' => 'nullable|string|max:20',
            'cpu_model' => 'nullable|string|max:150',
            'cpu_speed' => 'nullable|string|max:50',
            'ram_capacity' => 'nullable|integer',
            'ram_type' => 'nullable|string|max:30',
            'ram_bus' => 'nullable|string|max:30',
            'ram_slots' => 'nullable|string|max:50',
            'storage_type' => 'nullable|string|max:50',
            'storage_capacity' => 'nullable|string|max:50',
            'os_name' => 'nullable|string|max:100',
            'os_license' => 'nullable|string|max:100',
            'gpu_model' => 'nullable|string|max:150',
            'monitor_size' => 'nullable|string|max:50',
            'client_agent_version' => 'nullable|string|max:30',
        ]);

        $fiscalYear = (int) $request->input('fiscal_year', $this->getCurrentFiscalYear());
        $ip = $request->ip() ?: $request->input('ip_address');

        // Clean Serial Number
        $serial = trim($request->input('serial_number', ''));
        if (preg_match('/Default string|To be filled by O\.E\.M\.|None/i', $serial)) {
            $serial = '';
        }

        // Try Auto-matching with existing Asset in it_assets table
        $matchedAsset = null;
        if (!empty($serial)) {
            $matchedAsset = Asset::where('serial_number', $serial)->first();
        }

        if (!$matchedAsset && !empty($request->input('mac_address'))) {
            $mac = trim($request->input('mac_address'));
            $matchedAsset = Asset::where('mac_address', $mac)->first();
        }

        if (!$matchedAsset && !empty($request->input('hostname'))) {
            $hostname = trim($request->input('hostname'));
            $matchedAsset = Asset::where('name', 'like', "%{$hostname}%")
                ->orWhere('asset_code', 'like', "%{$hostname}%")
                ->first();
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

        // Upsert into it_hardware_audits as 'pending'
        // Staged for Admin review (Do NOT overwrite asset automatically!)
        $audit = HardwareAudit::updateOrCreate(
            [
                'fiscal_year' => $fiscalYear,
                'hostname' => $request->input('hostname'),
                'serial_number' => $serial ?: null,
            ],
            [
                'asset_id' => $matchedAsset ? $matchedAsset->id : null,
                'mac_address' => $request->input('mac_address'),
                'ip_address' => $ip,
                'brand' => $request->input('brand'),
                'model' => $request->input('model'),
                'device_type_code' => $request->input('device_type_code', 'PC'),
                'cpu_model' => $request->input('cpu_model'),
                'cpu_speed' => $request->input('cpu_speed'),
                'ram_capacity' => $request->input('ram_capacity'),
                'ram_type' => $request->input('ram_type'),
                'ram_bus' => $request->input('ram_bus'),
                'ram_slots' => $request->input('ram_slots'),
                'storage_type' => $request->input('storage_type'),
                'storage_capacity' => $request->input('storage_capacity'),
                'os_name' => $request->input('os_name'),
                'os_license' => $request->input('os_license'),
                'gpu_model' => $request->input('gpu_model'),
                'monitor_size' => $request->input('monitor_size'),
                'raw_payload' => $request->all(),
                'specs_diff' => !empty($diff) ? $diff : null,
                'client_agent_version' => $request->input('client_agent_version', '1.0.0'),
                'status' => 'pending', // Awaiting Admin Approval
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'ส่งรายงานสเปคคอมพิวเตอร์เข้าสู่ระบบเรียบร้อยแล้ว (สถานะ: รอแอดมินตรวจสอบและอนุมัติ)',
            'audit_id' => $audit->id,
            'fiscal_year' => $fiscalYear,
            'matched_asset' => $matchedAsset ? [
                'id' => $matchedAsset->id,
                'asset_code' => $matchedAsset->asset_code,
                'name' => $matchedAsset->name,
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

        if ($request->filled('status')) {
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
            'notAuditedCount',
            'auditedPercentage',
            'mdesWarningCount',
            'audits',
            'departments',
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
}
