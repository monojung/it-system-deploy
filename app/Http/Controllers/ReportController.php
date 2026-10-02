<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Repair;
use App\Models\RepairPart;
use App\Models\Asset;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\SparePart;
use App\Models\DataRequest;
use App\Models\BudgetSource;
use App\Models\AcquisitionMethod;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        if (Auth::user() && Auth::user()->isUser()) {
            return redirect()->route('reports.assets');
        }
        return redirect()->route('reports.monthly');
    }

    public function monthly(Request $request)
    {
        if (Auth::user() && Auth::user()->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้นที่มีสิทธิ์เข้าถึงรายงานสรุปประจำเดือน');
        }
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        // Repairs in this month
        $repairs = Repair::with(['department', 'technician', 'asset'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRepairs = $repairs->count();
        $completedCount = $repairs->where('status', 'completed')->count();
        $inProgressCount = $repairs->whereIn('status', ['in_progress', 'waiting_parts'])->count();
        $pendingCount = $repairs->where('status', 'pending')->count();
        $externalCount = $repairs->where('status', 'external')->count();
        $totalCost = $repairs->sum('total_cost');

        // Department breakdown
        $deptStats = Department::withCount(['repairs' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        }])->get()->filter(fn($d) => $d->repairs_count > 0)->sortByDesc('repairs_count');

        // Spare parts consumed in this month
        $partsConsumed = RepairPart::with(['sparePart', 'repair'])
            ->whereHas('repair', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('spare_part_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_amount'))
            ->groupBy('spare_part_id')
            ->get();

        // Data Requests in this month
        $dataRequests = DataRequest::with(['department', 'user', 'handler'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        $totalDataRequests = $dataRequests->count();
        $completedDataRequests = $dataRequests->where('status', 'completed')->count();
        $pendingDataRequests = $dataRequests->where('status', 'pending')->count();
        $drObjectiveStats = $dataRequests->groupBy('objective_type')->map(fn($g) => $g->count());

        $monthName = $this->getThaiMonthFull($month) . ' ' . ($year + 543);

        return view('reports.monthly', compact(
            'year', 'month', 'monthName',
            'repairs', 'totalRepairs', 'completedCount',
            'inProgressCount', 'pendingCount', 'externalCount',
            'totalCost', 'deptStats', 'partsConsumed',
            'dataRequests', 'totalDataRequests', 'completedDataRequests',
            'pendingDataRequests', 'drObjectiveStats'
        ));
    }

    public function quarterly(Request $request)
    {
        if (Auth::user() && Auth::user()->isUser()) {
            abort(403, 'เฉพาะเจ้าหน้าที่ไอทีหรือผู้ดูแลระบบเท่านั้นที่มีสิทธิ์เข้าถึงรายงานสรุปประจำไตรมาส');
        }

        // Thai Fiscal Year: Begins Oct 1 of previous Gregorian year and ends Sep 30
        // e.g. FY 2567 = Oct 1, 2023 - Sep 30, 2024
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $defaultFiscalYear = ($currentMonth >= 10) ? ($currentYear + 1) : $currentYear;
        
        $fiscalYear = (int) $request->input('fiscal_year', $defaultFiscalYear);
        $quarter = (int) $request->input('quarter', $this->calculateFiscalQuarter($currentMonth));

        // Date ranges for Fiscal Quarters:
        // Q1: Oct 1 - Dec 31 (prev year)
        // Q2: Jan 1 - Mar 31 (fiscal year)
        // Q3: Apr 1 - Jun 30 (fiscal year)
        // Q4: Jul 1 - Sep 30 (fiscal year)
        $gregorianPrevYear = $fiscalYear - 1;
        $gregorianCurrYear = $fiscalYear;

        switch ($quarter) {
            case 1:
                $startDate = Carbon::create($gregorianPrevYear, 10, 1)->startOfDay();
                $endDate = Carbon::create($gregorianPrevYear, 12, 31)->endOfDay();
                $quarterLabel = "ไตรมาสที่ 1 (ตุลาคม - ธันวาคม " . ($gregorianPrevYear + 543) . ")";
                break;
            case 2:
                $startDate = Carbon::create($gregorianCurrYear, 1, 1)->startOfDay();
                $endDate = Carbon::create($gregorianCurrYear, 3, 31)->endOfDay();
                $quarterLabel = "ไตรมาสที่ 2 (มกราคม - มีนาคม " . ($gregorianCurrYear + 543) . ")";
                break;
            case 3:
                $startDate = Carbon::create($gregorianCurrYear, 4, 1)->startOfDay();
                $endDate = Carbon::create($gregorianCurrYear, 6, 30)->endOfDay();
                $quarterLabel = "ไตรมาสที่ 3 (เมษายน - มิถุนายน " . ($gregorianCurrYear + 543) . ")";
                break;
            case 4:
            default:
                $startDate = Carbon::create($gregorianCurrYear, 7, 1)->startOfDay();
                $endDate = Carbon::create($gregorianCurrYear, 9, 30)->endOfDay();
                $quarterLabel = "ไตรมาสที่ 4 (กรกฎาคม - กันยายน " . ($gregorianCurrYear + 543) . ")";
                break;
        }

        $repairs = Repair::with(['department', 'technician', 'asset.deviceType'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRepairs = $repairs->count();
        $completedCount = $repairs->where('status', 'completed')->count();
        $completionRate = $totalRepairs > 0 ? round(($completedCount / $totalRepairs) * 100, 1) : 0;
        $totalCost = $repairs->sum('total_cost');

        // Department breakdown
        $deptStats = Department::withCount(['repairs' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        }])->get()->filter(fn($d) => $d->repairs_count > 0)->sortByDesc('repairs_count');

        // Device type breakdown
        $typeStats = DeviceType::withCount(['assets as repairs_count' => function ($q) use ($startDate, $endDate) {
            $q->whereHas('repairs', function ($rq) use ($startDate, $endDate) {
                $rq->whereBetween('created_at', [$startDate, $endDate]);
            });
        }])->get();

        // Parts consumed in quarter
        $partsConsumed = RepairPart::with(['sparePart', 'repair'])
            ->whereHas('repair', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select('spare_part_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_price) as total_amount'))
            ->groupBy('spare_part_id')
            ->get();

        // Data Requests in quarter
        $quarterlyDataRequests = DataRequest::with(['department', 'user', 'handler'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        $totalDrQuarter = $quarterlyDataRequests->count();
        $completedDrQuarter = $quarterlyDataRequests->where('status', 'completed')->count();

        return view('reports.quarterly', compact(
            'fiscalYear', 'quarter', 'quarterLabel',
            'repairs', 'totalRepairs', 'completedCount',
            'completionRate', 'totalCost', 'deptStats',
            'typeStats', 'partsConsumed',
            'quarterlyDataRequests', 'totalDrQuarter', 'completedDrQuarter'
        ));
    }

    public function assets(Request $request)
    {
        $user = Auth::user();
        $query = Asset::with(['deviceType', 'department', 'budgetSource', 'acquisitionMethod']);

        if ($user && $user->isUser()) {
            $departmentId = $user->department_id;
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $departmentId = $request->department_id;
            if ($departmentId) $query->where('department_id', $departmentId);
        }

        $status = $request->status;
        if ($status) $query->where('status', $status);

        $budgetSourceId = $request->budget_source_id;
        if ($budgetSourceId) {
            $query->where('budget_source_id', $budgetSourceId);
        }

        $acquisitionMethodId = $request->acquisition_method_id;
        if ($acquisitionMethodId) {
            $query->where('acquisition_method_id', $acquisitionMethodId);
        }

        $ownershipType = $request->ownership_type;
        if ($ownershipType) {
            $query->where('ownership_type', $ownershipType);
        }

        $rentalStatus = $request->rental_status;
        if ($rentalStatus) {
            $query->where('ownership_type', 'rented');
            $today = Carbon::today()->toDateString();
            $soon = Carbon::today()->addDays(30)->toDateString();

            if ($rentalStatus === 'active') {
                $query->where(function ($q) use ($today) {
                    $q->whereNull('rental_end_date')->orWhere('rental_end_date', '>=', $today);
                });
            } elseif ($rentalStatus === 'expiring_soon') {
                $query->whereNotNull('rental_end_date')->whereBetween('rental_end_date', [$today, $soon]);
            } elseif ($rentalStatus === 'expired') {
                $query->whereNotNull('rental_end_date')->where('rental_end_date', '<', $today);
            }
        }

        $assets = $query->orderBy('department_id')->orderBy('asset_code')->get();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $budgetSources = BudgetSource::where('is_active', true)->orderBy('name')->get();
        $acquisitionMethods = AcquisitionMethod::where('is_active', true)->orderBy('name')->get();

        $countQuery = Asset::query();
        if ($user && $user->isUser()) {
            $countQuery->where('department_id', $user->department_id ?: 0);
        } elseif ($departmentId) {
            $countQuery->where('department_id', $departmentId);
        }

        $totalCount = (clone $countQuery)->count();
        $totalValue = (clone $countQuery)->sum('price');

        // Status breakdown
        $statusCounts = [
            'active' => (clone $countQuery)->where('status', 'active')->count(),
            'spare' => (clone $countQuery)->where('status', 'spare')->count(),
            'repairing' => (clone $countQuery)->where('status', 'repairing')->count(),
            'broken' => (clone $countQuery)->where('status', 'broken')->count(),
            'disposed' => (clone $countQuery)->where('status', 'disposed')->count(),
        ];

        // Budget Sources Summary
        $budgetSourceStats = BudgetSource::withCount(['assets' => function ($q) use ($user, $departmentId) {
            if ($user && $user->isUser()) {
                $q->where('department_id', $user->department_id ?: 0);
            } elseif ($departmentId) {
                $q->where('department_id', $departmentId);
            }
        }])->withSum(['assets as total_value' => function ($q) use ($user, $departmentId) {
            if ($user && $user->isUser()) {
                $q->where('department_id', $user->department_id ?: 0);
            } elseif ($departmentId) {
                $q->where('department_id', $departmentId);
            }
        }], 'price')->get();

        // Acquisition Methods Summary
        $acquisitionMethodStats = AcquisitionMethod::withCount(['assets' => function ($q) use ($user, $departmentId) {
            if ($user && $user->isUser()) {
                $q->where('department_id', $user->department_id ?: 0);
            } elseif ($departmentId) {
                $q->where('department_id', $departmentId);
            }
        }])->get();

        // Leased & Rental Fleet Stats (เครื่องเช่า & เครื่องพิมพ์เช่า)
        $rentalBaseQuery = (clone $countQuery)->where('ownership_type', 'rented');
        $rentedTotal = (clone $rentalBaseQuery)->count();
        $rentedPrinters = (clone $rentalBaseQuery)->where(function ($q) {
            $q->whereHas('deviceType', function ($dq) {
                $dq->where('code', 'PRINTER')->orWhere('name', 'like', '%ปริ้น%')->orWhere('name', 'like', '%พิมพ์%');
            })->orWhere('name', 'like', '%ปริ้น%')
              ->orWhere('name', 'like', '%พิมพ์%')
              ->orWhere('brand', 'like', '%Fuji%')
              ->orWhere('brand', 'like', '%Ricoh%')
              ->orWhere('brand', 'like', '%Canon%')
              ->orWhere('brand', 'like', '%Epson%')
              ->orWhere('brand', 'like', '%HP%')
              ->orWhere('brand', 'like', '%Brother%');
        })->count();
        $rentedMonthlyTotal = (clone $rentalBaseQuery)->sum('rental_monthly_fee');
        $todayStr = Carbon::today()->toDateString();
        $soonStr = Carbon::today()->addDays(30)->toDateString();
        $rentedExpiringSoon = (clone $rentalBaseQuery)->whereNotNull('rental_end_date')
            ->whereBetween('rental_end_date', [$todayStr, $soonStr])
            ->count();
        $rentedExpired = (clone $rentalBaseQuery)->whereNotNull('rental_end_date')
            ->where('rental_end_date', '<', $todayStr)
            ->count();

        $rentalStats = [
            'total' => $rentedTotal,
            'printers' => $rentedPrinters,
            'monthly_fee' => $rentedMonthlyTotal,
            'expiring_soon' => $rentedExpiringSoon,
            'expired' => $rentedExpired,
        ];

        return view('reports.assets', compact(
            'assets', 'departments', 'budgetSources', 'acquisitionMethods',
            'totalCount', 'totalValue', 'statusCounts', 'budgetSourceStats',
            'acquisitionMethodStats', 'rentalStats', 'departmentId', 'status',
            'budgetSourceId', 'acquisitionMethodId', 'ownershipType', 'rentalStatus', 'user'
        ));
    }

    public function exportCsv(Request $request, $type)
    {
        $user = Auth::user();
        if ($user && $user->isUser() && $type !== 'assets') {
            abort(403, 'คุณไม่มีสิทธิ์ส่งออกรายงานนี้');
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"report_{$type}_" . date('Ymd_His') . ".csv\"",
        ];

        $callback = function () use ($type, $request, $user) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel Thai language support
            fputs($handle, "\xEF\xBB\xBF");

            if ($type === 'repairs') {
                fputcsv($handle, ['เลขที่แจ้งซ่อม', 'หัวข้อปัญหา', 'แผนก', 'ระดับความเร่งด่วน', 'สถานะ', 'ผู้แจ้ง', 'ช่างผู้รับผิดชอบ', 'ค่าใช้จ่าย (บาท)', 'วันที่แจ้ง', 'วันที่เสร็จสิ้น']);
                $repairs = Repair::with(['department', 'technician'])->latest()->get();
                foreach ($repairs as $r) {
                    fputcsv($handle, [
                        $r->ticket_number,
                        $r->title,
                        $r->department?->name,
                        $r->getUrgencyLabelAttribute(),
                        $r->getStatusLabelAttribute(),
                        $r->requester_name,
                        $r->technician?->name ?? 'ยังไม่ระบุ',
                        $r->total_cost,
                        $r->created_at->format('Y-m-d H:i'),
                        $r->completed_at ? $r->completed_at->format('Y-m-d H:i') : '-',
                    ]);
                }
            } elseif ($type === 'assets') {
                fputcsv($handle, [
                    'รหัสครุภัณฑ์', 'Serial Number', 'ชื่อรายการ', 'ประเภท', 'ยี่ห้อ/รุ่น',
                    'CPU', 'RAM (GB)', 'ชนิด RAM', 'Storage', 'ระบบ OS', 'สเปคสรุป',
                    'แผนก', 'สถานที่ตั้ง', 'ผู้ครอบครอง', 'สถานะ', 'ราคา (บาท)', 'ปีงบประมาณ',
                    'กรรมสิทธิ์', 'วิธีการได้มา', 'แหล่งเงินที่ใช้ซื้อ',
                    'เลขที่สัญญาเช่า', 'บริษัทผู้ให้เช่า', 'วันที่เริ่มสัญญาเช่า', 'วันที่สิ้นสุดสัญญาเช่า',
                    'ค่าเช่า/เดือน (บาท)', 'เบอร์ติดต่อผู้ให้เช่า', 'เงื่อนไขสัญญา'
                ]);
                $assetQuery = Asset::with(['deviceType', 'department', 'budgetSource', 'acquisitionMethod']);
                if ($user && $user->isUser()) {
                    $assetQuery->where('department_id', $user->department_id ?: 0);
                } else {
                    if ($request->filled('department_id')) {
                        $assetQuery->where('department_id', $request->department_id);
                    }
                }
                if ($request->filled('status')) {
                    $assetQuery->where('status', $request->status);
                }
                if ($request->filled('budget_source_id')) {
                    $assetQuery->where('budget_source_id', $request->budget_source_id);
                }
                if ($request->filled('acquisition_method_id')) {
                    $assetQuery->where('acquisition_method_id', $request->acquisition_method_id);
                }
                if ($request->filled('ownership_type')) {
                    $assetQuery->where('ownership_type', $request->ownership_type);
                }
                if ($request->filled('rental_status')) {
                    $assetQuery->where('ownership_type', 'rented');
                    $today = Carbon::today()->toDateString();
                    $soon = Carbon::today()->addDays(30)->toDateString();
                    if ($request->rental_status === 'active') {
                        $assetQuery->where(function ($q) use ($today) {
                            $q->whereNull('rental_end_date')->orWhere('rental_end_date', '>=', $today);
                        });
                    } elseif ($request->rental_status === 'expiring_soon') {
                        $assetQuery->whereNotNull('rental_end_date')->whereBetween('rental_end_date', [$today, $soon]);
                    } elseif ($request->rental_status === 'expired') {
                        $assetQuery->whereNotNull('rental_end_date')->where('rental_end_date', '<', $today);
                    }
                }

                $assets = $assetQuery->orderBy('department_id')->orderBy('asset_code')->get();
                foreach ($assets as $a) {
                    fputcsv($handle, [
                        $a->asset_code,
                        $a->serial_number,
                        $a->name,
                        $a->deviceType?->name,
                        $a->brand . ' ' . $a->model,
                        $a->cpu_model,
                        $a->ram_capacity,
                        $a->ram_type,
                        $a->storage_display,
                        $a->os_name,
                        $a->formatted_specs,
                        $a->department?->name,
                        $a->location_detail,
                        $a->custodian_name,
                        $a->getStatusLabelAttribute(),
                        $a->price,
                        $a->budget_year,
                        $a->ownership_label,
                        $a->acquisitionMethod?->name ?? '-',
                        $a->budgetSource?->name ?? '-',
                        $a->rental_contract_no ?? '-',
                        $a->rental_vendor ?? '-',
                        $a->rental_start_date ? $a->rental_start_date->format('Y-m-d') : '-',
                        $a->rental_end_date ? $a->rental_end_date->format('Y-m-d') : '-',
                        $a->rental_monthly_fee !== null ? number_format($a->rental_monthly_fee, 2) : '-',
                        $a->rental_contact_phone ?? '-',
                        $a->rental_conditions ?? '-',
                    ]);
                }
            } elseif ($type === 'spare_parts') {
                fputcsv($handle, ['รหัสพัสดุ', 'ชื่อรายการ', 'หมวดหมู่', 'คงเหลือ', 'หน่วยนับ', 'จุดเตือนขั้นต่ำ', 'ราคาต่อหน่วย (บาท)', 'สถานที่จัดเก็บ']);
                $parts = SparePart::all();
                foreach ($parts as $p) {
                    fputcsv($handle, [
                        $p->part_code,
                        $p->name,
                        $p->category,
                        $p->stock_quantity,
                        $p->unit,
                        $p->minimum_quantity,
                        $p->unit_price,
                        $p->location,
                    ]);
                }
            } elseif ($type === 'data_requests' || $type === 'data-requests') {
                fputcsv($handle, ['เลขที่คำขอ', 'วันที่ยื่นคำขอ', 'หัวข้อข้อมูลสารสนเทศ', 'วัตถุประสงค์', 'หน่วยงาน/แผนก', 'ผู้ขอ', 'ความเร่งด่วน', 'สถานะ', 'ผู้รับผิดชอบ', 'วันที่ดำเนินการเสร็จ']);
                $drs = DataRequest::with(['department', 'user', 'handler'])->latest()->get();
                foreach ($drs as $dr) {
                    fputcsv($handle, [
                        $dr->request_no,
                        $dr->created_at->format('Y-m-d H:i'),
                        $dr->title,
                        $dr->objective_label,
                        $dr->department?->name,
                        $dr->user?->name,
                        $dr->urgency_label,
                        $dr->status_label,
                        $dr->handler?->name ?? '-',
                        $dr->completed_at ? $dr->completed_at->format('Y-m-d H:i') : '-',
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function calculateFiscalQuarter($month): int
    {
        if (in_array($month, [10, 11, 12])) return 1;
        if (in_array($month, [1, 2, 3])) return 2;
        if (in_array($month, [4, 5, 6])) return 3;
        return 4;
    }

    private function getThaiMonthFull($month): string
    {
        $thaiMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        return $thaiMonths[(int)$month] ?? '';
    }
}
