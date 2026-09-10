<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use App\Models\DeviceType;
use App\Models\Department;
use App\Services\IctStandardCatalog;
use Carbon\Carbon;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Asset::with(['deviceType', 'department'])->withCount('repairs');

        // Regular users can only see assets of their own department
        if ($user && $user->isUser()) {
            if ($user->department_id) {
                $query->where('department_id', $user->department_id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('device_type_id')) {
            $query->where('device_type_id', $request->device_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('budget_year')) {
            $query->where('budget_year', $request->budget_year);
        }

        // Hardware Specs Filtering
        if ($request->filled('cpu')) {
            $cpu = $request->cpu;
            $query->where('cpu_model', 'like', "%{$cpu}%");
        }

        if ($request->filled('ram_type')) {
            $query->where('ram_type', $request->ram_type);
        }

        if ($request->filled('ram_capacity')) {
            $query->where('ram_capacity', (int)$request->ram_capacity);
        }

        if ($request->filled('os')) {
            $os = $request->os;
            $query->where('os_name', 'like', "%{$os}%");
        }

        if ($request->filled('storage_type')) {
            $query->where('storage_type', 'like', "%{$request->storage_type}%");
        }

        if ($request->filled('storage_capacity')) {
            $query->where('storage_capacity', 'like', "%{$request->storage_capacity}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('custodian_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('specs', 'like', "%{$search}%")
                  ->orWhere('cpu_model', 'like', "%{$search}%")
                  ->orWhere('ram_type', 'like', "%{$search}%")
                  ->orWhere('os_name', 'like', "%{$search}%")
                  ->orWhere('storage_type', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((int)$request->input('per_page'), [10, 20, 50, 100]) ? (int)$request->input('per_page') : 10;
        $assets = $query->latest('id')->paginate($perPage)->withQueryString();

        $deviceTypes = DeviceType::orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $budgetYears = Asset::select('budget_year')->whereNotNull('budget_year')->distinct()->orderBy('budget_year', 'desc')->pluck('budget_year');

        // Summary counts (scoped to department if regular user)
        $countQuery = Asset::query();
        if ($user && $user->isUser()) {
            $countQuery->where('department_id', $user->department_id ?: 0);
        }

        $statusCounts = [
            'total' => (clone $countQuery)->count(),
            'active' => (clone $countQuery)->where('status', 'active')->count(),
            'spare' => (clone $countQuery)->where('status', 'spare')->count(),
            'repairing' => (clone $countQuery)->where('status', 'repairing')->count(),
            'broken' => (clone $countQuery)->where('status', 'broken')->count(),
            'disposed' => (clone $countQuery)->where('status', 'disposed')->count(),
        ];

        // Hardware Specs Statistics & Fleet Breakdown
        $baseHardwareQuery = clone $countQuery;

        $ramTypeStats = (clone $baseHardwareQuery)
            ->whereNotNull('ram_type')
            ->where('ram_type', '!=', '')
            ->select('ram_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('ram_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'ram_type')
            ->toArray();

        $ramCapacityStats = (clone $baseHardwareQuery)
            ->whereNotNull('ram_capacity')
            ->select('ram_capacity', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('ram_capacity')
            ->orderBy('ram_capacity')
            ->pluck('count', 'ram_capacity')
            ->toArray();

        $osStats = (clone $baseHardwareQuery)
            ->whereNotNull('os_name')
            ->where('os_name', '!=', '')
            ->select('os_name', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('os_name')
            ->orderBy('count', 'desc')
            ->pluck('count', 'os_name')
            ->toArray();

        $cpuCounts = [
            'i5' => (clone $baseHardwareQuery)->where('cpu_model', 'like', '%i5%')->count(),
            'i3' => (clone $baseHardwareQuery)->where('cpu_model', 'like', '%i3%')->count(),
            'i7' => (clone $baseHardwareQuery)->where('cpu_model', 'like', '%i7%')->count(),
            'ryzen' => (clone $baseHardwareQuery)->where('cpu_model', 'like', '%Ryzen%')->count(),
        ];

        $storageTypeStats = (clone $baseHardwareQuery)
            ->whereNotNull('storage_type')
            ->where('storage_type', '!=', '')
            ->select('storage_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->groupBy('storage_type')
            ->orderBy('count', 'desc')
            ->pluck('count', 'storage_type')
            ->toArray();

        $hardwareStats = [
            'ram_types' => $ramTypeStats,
            'ram_capacities' => $ramCapacityStats,
            'os_list' => $osStats,
            'cpu_families' => $cpuCounts,
            'storage_types' => $storageTypeStats,
            'total_computers' => (clone $baseHardwareQuery)->where(function($q) {
                $q->whereNotNull('cpu_model')
                  ->orWhereNotNull('ram_capacity')
                  ->orWhereHas('deviceType', function($dt) {
                      $dt->whereIn('code', ['PC', 'NB', 'AIO', 'SRV']);
                  });
            })->count(),
        ];

        return view('assets.index', compact('assets', 'deviceTypes', 'departments', 'budgetYears', 'statusCounts', 'hardwareStats', 'user'));
    }

    public function create()
    {
        $deviceTypes = DeviceType::orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $ictStandardsGrouped = IctStandardCatalog::grouped();
        $ictStandardsAll = IctStandardCatalog::all();

        return view('assets.create', compact('deviceTypes', 'departments', 'ictStandardsGrouped', 'ictStandardsAll'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_code' => 'required|string|max:100|unique:it_assets,asset_code',
            'serial_number' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'device_type_id' => 'required|exists:it_device_types,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
            'cpu_model' => 'nullable|string|max:150',
            'cpu_speed' => 'nullable|string|max:50',
            'ram_capacity' => 'nullable|integer|min:1|max:1024',
            'ram_type' => 'nullable|string|max:30',
            'ram_bus' => 'nullable|string|max:30',
            'ram_slots' => 'nullable|string|max:50',
            'storage_type' => 'nullable|string|max:50',
            'storage_capacity' => 'nullable|string|max:50',
            'storage_second' => 'nullable|string|max:100',
            'os_name' => 'nullable|string|max:100',
            'os_license' => 'nullable|string|max:100',
            'gpu_model' => 'nullable|string|max:150',
            'monitor_size' => 'nullable|string|max:50',
            'ip_address' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:it_departments,id',
            'location_detail' => 'nullable|string|max:255',
            'custodian_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,spare,repairing,broken,disposed',
            'purchase_date' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'warranty_expire_date' => 'nullable|date',
            'budget_year' => 'nullable|string|max:10',
            'image' => 'nullable|image|max:3072',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except(['image']);

        // Auto-format fallback specs string if left empty
        if (empty($data['specs'])) {
            $parts = [];
            if (!empty($data['cpu_model'])) $parts[] = $data['cpu_model'];
            if (!empty($data['ram_capacity'])) $parts[] = 'RAM ' . $data['ram_capacity'] . 'GB' . (!empty($data['ram_type']) ? ' ' . $data['ram_type'] : '');
            if (!empty($data['storage_capacity']) || !empty($data['storage_type'])) $parts[] = trim(($data['storage_type'] ?? '') . ' ' . ($data['storage_capacity'] ?? ''));
            if (!empty($data['os_name'])) $parts[] = $data['os_name'];
            if (!empty($parts)) $data['specs'] = implode(', ', $parts);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'asset_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/assets'), $filename);
            $data['image'] = 'uploads/assets/' . $filename;
        }

        $asset = Asset::create($data);

        return redirect()->route('assets.show', $asset)->with('success', "เพิ่มครุภัณฑ์รหัส {$asset->asset_code} เรียบร้อยแล้ว");
    }

    public function show(Asset $asset)
    {
        $user = Auth::user();
        if ($user && $user->isUser() && $asset->department_id !== $user->department_id) {
            abort(403, 'คุณมีสิทธิ์เข้าถึงเฉพาะข้อมูลครุภัณฑ์ประจำแผนกของคุณเท่านั้น');
        }

        $asset->load(['deviceType', 'department', 'repairs.department', 'repairs.technician'])->loadCount('repairs');
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $deviceTypes = DeviceType::orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $ictStandardsGrouped = IctStandardCatalog::grouped();
        $ictStandardsAll = IctStandardCatalog::all();

        return view('assets.edit', compact('asset', 'deviceTypes', 'departments', 'ictStandardsGrouped', 'ictStandardsAll'));
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'asset_code' => 'required|string|max:100|unique:it_assets,asset_code,' . $asset->id,
            'serial_number' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'device_type_id' => 'required|exists:it_device_types,id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'specs' => 'nullable|string',
            'cpu_model' => 'nullable|string|max:150',
            'cpu_speed' => 'nullable|string|max:50',
            'ram_capacity' => 'nullable|integer|min:1|max:1024',
            'ram_type' => 'nullable|string|max:30',
            'ram_bus' => 'nullable|string|max:30',
            'ram_slots' => 'nullable|string|max:50',
            'storage_type' => 'nullable|string|max:50',
            'storage_capacity' => 'nullable|string|max:50',
            'storage_second' => 'nullable|string|max:100',
            'os_name' => 'nullable|string|max:100',
            'os_license' => 'nullable|string|max:100',
            'gpu_model' => 'nullable|string|max:150',
            'monitor_size' => 'nullable|string|max:50',
            'ip_address' => 'nullable|string|max:45',
            'mac_address' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:it_departments,id',
            'location_detail' => 'nullable|string|max:255',
            'custodian_name' => 'nullable|string|max:255',
            'status' => 'required|in:active,spare,repairing,broken,disposed',
            'purchase_date' => 'nullable|date',
            'price' => 'nullable|numeric|min:0',
            'warranty_expire_date' => 'nullable|date',
            'budget_year' => 'nullable|string|max:10',
            'image' => 'nullable|image|max:3072',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except(['image']);

        // Auto-format fallback specs string if left empty
        if (empty($data['specs'])) {
            $parts = [];
            if (!empty($data['cpu_model'])) $parts[] = $data['cpu_model'];
            if (!empty($data['ram_capacity'])) $parts[] = 'RAM ' . $data['ram_capacity'] . 'GB' . (!empty($data['ram_type']) ? ' ' . $data['ram_type'] : '');
            if (!empty($data['storage_capacity']) || !empty($data['storage_type'])) $parts[] = trim(($data['storage_type'] ?? '') . ' ' . ($data['storage_capacity'] ?? ''));
            if (!empty($data['os_name'])) $parts[] = $data['os_name'];
            if (!empty($parts)) $data['specs'] = implode(', ', $parts);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'asset_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/assets'), $filename);
            $data['image'] = 'uploads/assets/' . $filename;
        }

        $asset->update($data);

        return redirect()->route('assets.show', $asset)->with('success', 'บันทึกการแก้ไขข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->repairs()->count() > 0) {
            return back()->with('error', 'ไม่สามารถลบครุภัณฑ์นี้ได้ เนื่องจากมีประวัติการแจ้งซ่อมในระบบ');
        }

        $code = $asset->asset_code;
        $asset->delete();

        return redirect()->route('assets.index')->with('success', "ลบครุภัณฑ์รหัส {$code} เรียบร้อยแล้ว");
    }

    public function label(Asset $asset)
    {
        $user = Auth::user();
        if ($user && $user->isUser() && $asset->department_id !== $user->department_id) {
            abort(403, 'คุณมีสิทธิ์เข้าถึงเฉพาะข้อมูลครุภัณฑ์ประจำแผนกของคุณเท่านั้น');
        }

        $asset->load(['deviceType', 'department']);
        return view('assets.label', compact('asset'));
    }

    /**
     * Download CSV template with sample data for asset importing
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="it_asset_import_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Microsoft Excel compatibility on Thai Windows
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'รหัสครุภัณฑ์',
                'Serial Number',
                'ชื่อรายการครุภัณฑ์',
                'ประเภทอุปกรณ์',
                'ยี่ห้อ',
                'รุ่น',
                'CPU',
                'ความเร็ว CPU',
                'RAM (GB)',
                'ชนิด RAM',
                'บัส RAM',
                'สล็อต RAM',
                'ชนิด Storage',
                'ความจุ Storage',
                'ไดรฟ์สำรอง',
                'ระบบ OS',
                'ลิขสิทธิ์ OS',
                'การ์ดจอ',
                'ขนาดจอภาพ',
                'สเปกเพิ่มเติม',
                'แผนก',
                'สถานที่ตั้ง',
                'ผู้ครอบครอง',
                'สถานะ',
                'ราคา (บาท)',
                'ปีงบประมาณ',
                'วันที่ซื้อ (YYYY-MM-DD)',
                'วันหมดประกัน (YYYY-MM-DD)',
                'IP Address',
                'MAC Address',
                'หมายเหตุ',
            ]);

            // Sample 1: PC with DDR5
            fputcsv($handle, [
                '7440-001-0021/67',
                'DL-OPT-7020-02',
                'คอมพิวเตอร์ประมวลผลงานเภสัชกรรม',
                'คอมพิวเตอร์ตั้งโต๊ะ (PC)',
                'Dell',
                'OptiPlex 7020 Plus',
                'Intel Core i5-14500',
                '2.6 GHz Turbo 5.0 GHz',
                '16',
                'DDR5',
                '5600 MHz',
                '1 แถว (16GB x 1)',
                'SSD NVMe M.2',
                '512 GB',
                '',
                'Windows 11 Pro',
                'OEM (ติดเครื่อง/BIOS)',
                'Intel UHD Graphics 770',
                '24 นิ้ว FHD IPS',
                '',
                'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
                'ห้องจ่ายยา ช่องบริการ 1',
                'ภญ.พิมพ์ใจ รักยา',
                'active',
                '28900.00',
                '2567',
                '2024-04-10',
                '2027-04-10',
                '192.168.2.75',
                '00:14:22:88:99:AA',
                'เครื่องประจำห้องจ่ายยา',
            ]);

            // Sample 2: Notebook with DDR4
            fputcsv($handle, [
                '7440-006-0003/67',
                'LN-T14-01',
                'โน้ตบุ๊กงานเวชระเบียนและสถิติ',
                'คอมพิวเตอร์พกพา (Notebook)',
                'Lenovo',
                'ThinkPad T14 Gen 4',
                'AMD Ryzen 7 7730U',
                '2.0 GHz Turbo 4.5 GHz',
                '16',
                'DDR4',
                '3200 MHz',
                '1 แถว (16GB x 1)',
                'SSD NVMe M.2',
                '512 GB',
                '',
                'Windows 11 Pro',
                'OEM (ติดเครื่อง/BIOS)',
                'AMD Radeon Graphics',
                '14 นิ้ว FHD',
                '',
                'กลุ่มงานประกันสุขภาพและเวชระเบียน',
                'ห้องเวชระเบียน อาคาร OPD',
                'เจ้าหน้าที่เวชระเบียน',
                'active',
                '27500.00',
                '2567',
                '2024-03-15',
                '2027-03-15',
                '192.168.2.106',
                '48:2A:E3:44:55:77',
                'โน้ตบุ๊กประจำกลุ่มงาน',
            ]);

            // Sample 3: All-in-One PC with DDR4
            fputcsv($handle, [
                '7440-002-0006/66',
                'HP-AIO-24-01',
                'คอมพิวเตอร์ AIO จุดประชาสัมพันธ์',
                'คอมพิวเตอร์ All-in-One (AIO)',
                'HP',
                'ProOne 240 G10',
                'Intel Core i3-1315U',
                '1.2 GHz Turbo 4.5 GHz',
                '8',
                'DDR4',
                '3200 MHz',
                '1 แถว (8GB x 1)',
                'SSD NVMe M.2',
                '256 GB',
                '',
                'Windows 10 Pro',
                'Volume License (KMS/MAK)',
                'Intel UHD Graphics',
                '23.8 นิ้ว FHD',
                '',
                'กลุ่มงานบริหารทั่วไป (ธุรการ/สารบรรณ)',
                'เคาน์เตอร์ประชาสัมพันธ์ อาคารผู้ป่วยนอก',
                'เจ้าหน้าที่ประชาสัมพันธ์',
                'active',
                '19500.00',
                '2566',
                '2023-11-20',
                '2026-11-20',
                '192.168.2.93',
                '94:C6:91:22:33:55',
                '',
            ]);

            // Sample 4: Laser Printer
            fputcsv($handle, [
                '7440-002-0002/67',
                'BR-HL-L2370-01',
                'เครื่องพิมพ์เลเซอร์ แผนกเภสัชกรรม',
                'เครื่องพิมพ์เลเซอร์/อิงค์เจ็ท (Laser/Inkjet)',
                'Brother',
                'HL-L2370DN',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Laser Duplex Network 30 ppm',
                'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค',
                'เคาน์เตอร์จ่ายยา ช่อง 2',
                'ภญ.พิมพ์ใจ รักยา',
                'active',
                '4900.00',
                '2567',
                '2024-02-15',
                '2027-02-15',
                '192.168.2.152',
                '00:80:77:AA:BB:DD',
                'พิมพ์ใบสั่งยา',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Import assets from CSV file (Admin only)
     */
    public function importCsv(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถนำเข้าข้อมูลครุภัณฑ์ได้');
        }

        $file = $request->file('csv_file') ?? $request->file('file');
        if (!$file) {
            return back()->with('error', 'กรุณาเลือกไฟล์ CSV สำหรับนำเข้าข้อมูล');
        }

        $request->validate([
            'csv_file' => 'nullable|file|max:10240',
            'file' => 'nullable|file|max:10240',
            'overwrite' => 'nullable|boolean',
        ]);

        $path = $file->getRealPath();
        $overwrite = $request->boolean('overwrite');

        $content = file_get_contents($path);
        // Strip BOM if present
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Auto-detect delimiter (, or ;)
        $firstLine = strtok($content, "\r\n");
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        if (count($lines) < 2) {
            return back()->with('error', 'ไฟล์ CSV ไม่มีข้อมูล หรือมีเฉพาะหัวตาราง');
        }

        $headerLine = array_shift($lines);
        $headers = str_getcsv($headerLine, $delimiter);
        $normalizedHeaders = [];
        foreach ($headers as $idx => $h) {
            $key = mb_strtolower(trim(trim($h, "\xEF\xBB\xBF\"' ")));
            $normalizedHeaders[$key] = $idx;
        }

        $deviceTypes = DeviceType::all();
        $departments = Department::all();

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $parseDate = function ($v) {
            if (!$v) return null;
            $clean = trim($v);
            $ts = strtotime(str_replace('/', '-', $clean));
            return $ts ? date('Y-m-d', $ts) : null;
        };

        foreach ($lines as $lineIndex => $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;

            $row = str_getcsv($line, $delimiter);
            if (empty(array_filter($row))) continue;

            $getVal = function ($names, $default = null) use ($row, $normalizedHeaders) {
                foreach ((array)$names as $name) {
                    $key = mb_strtolower(trim($name));
                    if (isset($normalizedHeaders[$key]) && isset($row[$normalizedHeaders[$key]])) {
                        $v = trim($row[$normalizedHeaders[$key]]);
                        if ($v !== '') return $v;
                    }
                }
                return $default;
            };

            $assetCode = $getVal(['รหัสครุภัณฑ์', 'asset_code', 'code']);
            if (!$assetCode) {
                $errorCount++;
                continue;
            }

            $name = $getVal(['ชื่อรายการครุภัณฑ์', 'ชื่อรายการ', 'name'], 'อุปกรณ์ IT');
            $deviceTypeName = $getVal(['ประเภทอุปกรณ์', 'device_type', 'type']);
            $deviceType = null;
            if ($deviceTypeName) {
                $deviceType = $deviceTypes->first(function ($dt) use ($deviceTypeName) {
                    return mb_stripos($dt->name, $deviceTypeName) !== false || 
                           strcasecmp($dt->code, $deviceTypeName) === 0;
                });
            }
            if (!$deviceType) {
                $deviceType = $deviceTypes->firstWhere('code', 'PC') ?? $deviceTypes->first();
            }

            $deptName = $getVal(['แผนก', 'department', 'หน่วยงาน']);
            $dept = null;
            if ($deptName) {
                $dept = $departments->first(function ($d) use ($deptName) {
                    return mb_stripos($d->name, $deptName) !== false || 
                           strcasecmp($d->code, $deptName) === 0;
                });
            }

            $rawStatus = mb_strtolower($getVal(['สถานะ', 'status'], 'active'));
            $status = match (true) {
                str_contains($rawStatus, 'spare') || str_contains($rawStatus, 'สำรอง') => 'spare',
                str_contains($rawStatus, 'repair') || str_contains($rawStatus, 'ซ่อม') => 'repairing',
                str_contains($rawStatus, 'broken') || str_contains($rawStatus, 'ชำรุด') => 'broken',
                str_contains($rawStatus, 'dispos') || str_contains($rawStatus, 'จำหน่าย') => 'disposed',
                default => 'active',
            };

            $rawRamCap = $getVal(['ram (gb)', 'ram_capacity', 'ram', 'ความจุ ram']);
            $ramCapacity = null;
            if ($rawRamCap && preg_match('/(\d+)/', $rawRamCap, $m)) {
                $ramCapacity = (int)$m[1];
            }

            $ramType = $getVal(['ชนิด ram', 'ram_type', 'ประเภท ram']);
            if (!$ramType && $ramCapacity) {
                $ramType = 'DDR4';
            }

            $rawPrice = $getVal(['ราคา (บาท)', 'ราคา', 'price']);
            $price = is_numeric(str_replace(',', '', $rawPrice ?? '')) ? (float)str_replace(',', '', $rawPrice) : null;

            $assetData = [
                'asset_code' => $assetCode,
                'serial_number' => $getVal(['serial number', 's/n', 'serial_number']),
                'name' => $name,
                'device_type_id' => $deviceType?->id,
                'brand' => $getVal(['ยี่ห้อ', 'brand']),
                'model' => $getVal(['รุ่น', 'model']),
                'cpu_model' => $getVal(['cpu', 'cpu_model', 'หน่วยประมวลผล']),
                'cpu_speed' => $getVal(['ความเร็ว cpu', 'cpu_speed', 'speed']),
                'ram_capacity' => $ramCapacity,
                'ram_type' => $ramType,
                'ram_bus' => $getVal(['บัส ram', 'ram_bus', 'bus']),
                'ram_slots' => $getVal(['สล็อต ram', 'ram_slots', 'slots']),
                'storage_type' => $getVal(['ชนิด storage', 'storage_type', 'ชนิดไดรฟ์']),
                'storage_capacity' => $getVal(['ความจุ storage', 'storage_capacity', 'ขนาดไดรฟ์']),
                'storage_second' => $getVal(['ไดรฟ์สำรอง', 'storage_second']),
                'os_name' => $getVal(['ระบบ os', 'os_name', 'os', 'ระบบปฏิบัติการ']),
                'os_license' => $getVal(['ลิขสิทธิ์ os', 'os_license', 'ลิขสิทธิ์']),
                'gpu_model' => $getVal(['การ์ดจอ', 'gpu_model', 'gpu']),
                'monitor_size' => $getVal(['ขนาดจอภาพ', 'monitor_size', 'จอภาพ']),
                'specs' => $getVal(['สเปกเพิ่มเติม', 'specs', 'รายละเอียดสเปก']),
                'department_id' => $dept?->id,
                'location_detail' => $getVal(['สถานที่ตั้ง', 'location_detail', 'จุดวาง', 'ห้อง']),
                'custodian_name' => $getVal(['ผู้ครอบครอง', 'custodian_name', 'ผู้ดูแล']),
                'status' => $status,
                'price' => $price,
                'budget_year' => $getVal(['ปีงบประมาณ', 'budget_year']),
                'purchase_date' => $parseDate($getVal(['วันที่ซื้อ (yyyy-mm-dd)', 'purchase_date', 'วันที่ซื้อ'])),
                'warranty_expire_date' => $parseDate($getVal(['วันหมดประกัน (yyyy-mm-dd)', 'warranty_expire_date', 'วันหมดประกัน'])),
                'ip_address' => $getVal(['ip address', 'ip_address', 'ip']),
                'mac_address' => $getVal(['mac address', 'mac_address', 'mac']),
                'notes' => $getVal(['หมายเหตุ', 'notes']),
            ];

            if (empty($assetData['specs'])) {
                $parts = [];
                if (!empty($assetData['cpu_model'])) $parts[] = $assetData['cpu_model'];
                if (!empty($assetData['ram_capacity'])) $parts[] = 'RAM ' . $assetData['ram_capacity'] . 'GB' . (!empty($assetData['ram_type']) ? ' ' . $assetData['ram_type'] : '');
                if (!empty($assetData['storage_capacity']) || !empty($assetData['storage_type'])) $parts[] = trim(($assetData['storage_type'] ?? '') . ' ' . ($assetData['storage_capacity'] ?? ''));
                if (!empty($assetData['os_name'])) $parts[] = $assetData['os_name'];
                if (!empty($parts)) $assetData['specs'] = implode(', ', $parts);
            }

            $existing = Asset::where('asset_code', $assetCode)->first();
            if ($existing) {
                if ($overwrite) {
                    $existing->update($assetData);
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            } else {
                Asset::create($assetData);
                $createdCount++;
            }
        }

        $summaryParts = ["นำเข้าข้อมูลเสร็จสิ้น: สร้างใหม่ {$createdCount} รายการ"];
        if ($updatedCount > 0) $summaryParts[] = "อัปเดต {$updatedCount} รายการ";
        if ($skippedCount > 0) $summaryParts[] = "ข้ามรหัสซ้ำ {$skippedCount} รายการ";
        if ($errorCount > 0) $summaryParts[] = "ข้อมูลไม่สมบูรณ์ {$errorCount} แถว";

        return redirect()->route('assets.index')->with('success', implode(' | ', $summaryParts));
    }
}
