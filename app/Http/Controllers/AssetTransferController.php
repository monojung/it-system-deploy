<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetTransfer;
use App\Models\Asset;
use App\Models\Department;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\MophNotifyService;
use App\Services\UserLineNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetTransferController extends Controller
{
    /**
     * Display a listing of asset transfers.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AssetTransfer::with(['asset.deviceType', 'fromDepartment', 'toDepartment', 'user', 'technician'])->latest();

        // If regular user, show their requests or transfers involving their department
        if ($user && $user->isUser()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->department_id) {
                    $q->orWhere('from_department_id', $user->department_id)
                      ->orWhere('to_department_id', $user->department_id);
                }
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter: Transfer Type
        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->transfer_type);
        }

        // Filter: Department (From or To)
        if ($request->filled('department_id')) {
            $deptId = $request->department_id;
            $query->where(function ($q) use ($deptId) {
                $q->where('from_department_id', $deptId)
                  ->orWhere('to_department_id', $deptId);
            });
        }

        // Search: transfer_no, asset_code, asset name, serial_number, custodian, location, reason
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transfer_no', 'like', "%{$search}%")
                  ->orWhere('from_location_detail', 'like', "%{$search}%")
                  ->orWhere('to_location_detail', 'like', "%{$search}%")
                  ->orWhere('from_custodian_name', 'like', "%{$search}%")
                  ->orWhere('to_custodian_name', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($qa) use ($search) {
                      $qa->where('name', 'like', "%{$search}%")
                         ->orWhere('asset_code', 'like', "%{$search}%")
                         ->orWhere('serial_number', 'like', "%{$search}%")
                         ->orWhere('brand', 'like', "%{$search}%")
                         ->orWhere('model', 'like', "%{$search}%");
                  });
            });
        }

        // Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->paginate(15)->withQueryString();

        // Metrics Summary
        $baseQuery = AssetTransfer::query();
        if ($user && $user->isUser()) {
            $baseQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->department_id) {
                    $q->orWhere('from_department_id', $user->department_id)
                      ->orWhere('to_department_id', $user->department_id);
                }
            });
        }
        $metrics = [
            'total' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'in_progress' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
        ];

        $departments = Department::orderBy('name')->get();

        return view('asset_transfers.index', compact('transfers', 'metrics', 'departments'));
    }

    /**
     * Show the form for creating a new asset transfer.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isUser() && !$user->department_id) {
            return redirect()->route('asset-transfers.index')
                ->with('error', 'กรุณาระบุกลุ่มงาน/แผนกสังกัดของท่านก่อนทำรายการย้ายครุภัณฑ์');
        }

        $selectedAsset = null;
        if ($request->filled('asset_id')) {
            $selectedAssetQuery = Asset::with(['department', 'deviceType']);
            if ($user && $user->isUser()) {
                $selectedAssetQuery->where('department_id', $user->department_id);
            }
            $selectedAsset = $selectedAssetQuery->find($request->asset_id);
            if (!$selectedAsset && $user && $user->isUser()) {
                return redirect()->route('asset-transfers.create')
                    ->with('error', 'ผู้ใช้งานทั่วไปสามารถย้ายได้เฉพาะครุภัณฑ์ภายในกลุ่มงาน/แผนกเดิมเท่านั้น');
            }
        }

        $assetsQuery = Asset::with(['department', 'deviceType'])
            ->whereNotIn('status', ['disposed'])
            ->orderBy('asset_code');

        // Regular users can only select assets from their own department
        if ($user && $user->isUser()) {
            $assetsQuery->where('department_id', $user->department_id);
        }

        $assets = $assetsQuery->get(['id', 'asset_code', 'name', 'brand', 'model', 'serial_number', 'department_id', 'location_detail', 'custodian_name', 'ip_address', 'device_type_id', 'status']);

        $departments = Department::orderBy('name')->get();
        $technicians = User::whereIn('role', ['admin', 'technician'])->where('is_active', true)->orderBy('name')->get();

        return view('asset_transfers.create', compact('selectedAsset', 'assets', 'departments', 'technicians'));
    }

    /**
     * Store a newly created asset transfer.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $isStaff = $user && ($user->isAdmin() || $user->isTechnician());

        $request->validate([
            'asset_id' => 'required|exists:it_assets,id',
            'transfer_type' => 'required|in:relocation,department_transfer,temporary_move',
            'to_department_id' => 'nullable|exists:it_departments,id',
            'to_location_detail' => 'required|string|max:255',
            'to_custodian_name' => 'nullable|string|max:255',
            'to_ip_address' => 'nullable|string|max:50',
            'transfer_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'test_result' => 'nullable|string|max:500',
            'receiver_name' => 'nullable|string|max:255',
            'complete_immediately' => 'nullable|boolean',
        ]);

        $asset = Asset::with('department')->findOrFail($request->asset_id);

        // Security / Policy Restriction: Regular users can ONLY transfer within their own department/group
        if ($user && $user->isUser()) {
            if (!$user->department_id) {
                return back()->withInput()->with('error', 'กรุณาระบุกลุ่มงาน/แผนกสังกัดของท่านก่อนทำรายการย้ายครุภัณฑ์');
            }

            // Asset must belong to user's department
            if ($asset->department_id != $user->department_id) {
                return back()->withInput()->with('error', 'ผู้ใช้งานทั่วไปสามารถย้ายได้เฉพาะครุภัณฑ์ภายในกลุ่มงานเดิมของท่านเท่านั้น');
            }

            // Regular user cannot perform department_transfer across departments
            if ($request->transfer_type === 'department_transfer') {
                return back()->withInput()->with('error', 'ผู้ใช้งานทั่วไปสามารถย้ายครุภัณฑ์ได้เฉพาะภายในกลุ่มงานเดิมเท่านั้น (ไม่สามารถโอนย้ายข้ามหน่วยงานได้)');
            }

            // If to_department_id was sent, it must match user's department
            if ($request->filled('to_department_id') && $request->to_department_id != $user->department_id) {
                return back()->withInput()->with('error', 'ผู้ใช้งานทั่วไปสามารถย้ายครุภัณฑ์ได้เฉพาะภายในกลุ่มงานเดิมเท่านั้น');
            }

            $toDepartmentId = $user->department_id;
        } else {
            $toDepartmentId = $request->to_department_id ?: $asset->department_id;
        }

        $toDept = $toDepartmentId ? Department::find($toDepartmentId) : null;
        $completeImmediately = $isStaff && $request->boolean('complete_immediately');

        DB::beginTransaction();
        try {
            $transfer = new AssetTransfer();
            $transfer->transfer_no = AssetTransfer::generateTransferNo();
            $transfer->asset_id = $asset->id;
            $transfer->user_id = $user ? $user->id : null;
            $transfer->transfer_type = $request->transfer_type;

            // Snapshot from asset
            $transfer->from_department_id = $asset->department_id;
            $transfer->from_department_name = $asset->department?->name;
            $transfer->from_location_detail = $asset->location_detail;
            $transfer->from_custodian_name = $asset->custodian_name;
            $transfer->from_ip_address = $asset->ip_address;

            // Destination fields (Restricted to same department for regular users)
            $transfer->to_department_id = $toDepartmentId;
            $transfer->to_department_name = $toDept ? $toDept->name : ($asset->department?->name);
            $transfer->to_location_detail = $request->to_location_detail;
            $transfer->to_custodian_name = $request->to_custodian_name ?: $asset->custodian_name;
            $transfer->to_ip_address = $request->filled('to_ip_address') ? $request->to_ip_address : $asset->ip_address;

            $transfer->transfer_date = $request->transfer_date;
            $transfer->reason = $request->reason;
            $transfer->notes = $request->notes;

            if ($completeImmediately) {
                $transfer->status = 'completed';
                $transfer->completed_at = Carbon::now();
                $transfer->technician_id = $user->id;
                $transfer->test_result = $request->test_result ?: 'ตรวจสอบการเชื่อมต่อเครือข่ายและระบบสารสนเทศใช้งานได้ปกติ';
                $transfer->receiver_name = $request->receiver_name ?: $transfer->to_custodian_name;

                // Update Asset master record
                $asset->department_id = $transfer->to_department_id;
                $asset->location_detail = $transfer->to_location_detail;
                if ($request->filled('to_custodian_name')) {
                    $asset->custodian_name = $request->to_custodian_name;
                }
                if ($request->filled('to_ip_address')) {
                    $asset->ip_address = $request->to_ip_address;
                }
                $asset->save();
            } else {
                $transfer->status = 'pending';
                if ($request->filled('technician_id') && $isStaff) {
                    $transfer->technician_id = $request->technician_id;
                }
            }

            $transfer->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
        }

        // Send LINE MOPH notification
        try {
            MophNotifyService::sendAssetTransferNotification($transfer, $transfer->status);
            UserLineNotificationService::notifyTransferUser($transfer, $transfer->status);
        } catch (\Throwable $e) {
            // Log and continue
        }

        $msg = $completeImmediately 
            ? "บันทึกการย้ายจุดติดตั้งครุภัณฑ์เรียบร้อยแล้ว (#{$transfer->transfer_no}) และอัปเดตข้อมูลครุภัณฑ์ทันที"
            : "ส่งคำขอย้ายจุดติดตั้งครุภัณฑ์เรียบร้อยแล้ว (#{$transfer->transfer_no}) รอเจ้าหน้าที่ดำเนินการ";

        return redirect()->route('asset-transfers.show', $transfer)->with('success', $msg);
    }

    /**
     * Display the specified asset transfer.
     */
    public function show(AssetTransfer $assetTransfer)
    {
        $assetTransfer->load(['asset.deviceType', 'fromDepartment', 'toDepartment', 'user', 'technician']);
        $technicians = User::whereIn('role', ['admin', 'technician'])->where('is_active', true)->orderBy('name')->get();

        return view('asset_transfers.show', compact('assetTransfer', 'technicians'));
    }

    /**
     * Show the form for editing the specified asset transfer.
     */
    public function edit(AssetTransfer $assetTransfer)
    {
        $user = Auth::user();
        if ($assetTransfer->status === 'completed' && (!$user || !$user->isAdmin())) {
            return redirect()->route('asset-transfers.show', $assetTransfer)
                ->with('error', 'รายการที่ย้ายเสร็จสิ้นแล้วไม่สามารถแก้ไขได้');
        }

        if ($user && $user->isUser()) {
            if ($assetTransfer->user_id !== $user->id) {
                return redirect()->route('asset-transfers.show', $assetTransfer)
                    ->with('error', 'ท่านไม่มีสิทธิ์แก้ไขคำขอย้ายของผู้อื่น');
            }
            if ($assetTransfer->from_department_id != $user->department_id) {
                return redirect()->route('asset-transfers.show', $assetTransfer)
                    ->with('error', 'ท่านสามารถแก้ไขได้เฉพาะรายการย้ายภายในกลุ่มงานเดิมของท่านเท่านั้น');
            }
        }

        $departments = Department::orderBy('name')->get();
        $technicians = User::whereIn('role', ['admin', 'technician'])->where('is_active', true)->orderBy('name')->get();

        return view('asset_transfers.edit', compact('assetTransfer', 'departments', 'technicians'));
    }

    /**
     * Update the specified asset transfer in storage.
     */
    public function update(Request $request, AssetTransfer $assetTransfer)
    {
        $user = Auth::user();
        if ($assetTransfer->status === 'completed' && (!$user || !$user->isAdmin())) {
            return redirect()->route('asset-transfers.show', $assetTransfer)
                ->with('error', 'รายการที่ย้ายเสร็จสิ้นแล้วไม่สามารถแก้ไขได้');
        }

        if ($user && $user->isUser()) {
            if ($assetTransfer->user_id !== $user->id) {
                return redirect()->route('asset-transfers.show', $assetTransfer)
                    ->with('error', 'ท่านไม่มีสิทธิ์แก้ไขคำขอย้ายของผู้อื่น');
            }
            if ($request->transfer_type === 'department_transfer') {
                return back()->withInput()->with('error', 'ผู้ใช้งานทั่วไปสามารถย้ายครุภัณฑ์ได้เฉพาะภายในกลุ่มงานเดิมเท่านั้น (ไม่สามารถโอนย้ายข้ามหน่วยงานได้)');
            }
            if ($request->filled('to_department_id') && $request->to_department_id != $user->department_id) {
                return back()->withInput()->with('error', 'ผู้ใช้งานทั่วไปสามารถย้ายครุภัณฑ์ได้เฉพาะภายในกลุ่มงานเดิมเท่านั้น');
            }
        }

        $request->validate([
            'transfer_type' => 'required|in:relocation,department_transfer,temporary_move',
            'to_department_id' => 'nullable|exists:it_departments,id',
            'to_location_detail' => 'required|string|max:255',
            'to_custodian_name' => 'nullable|string|max:255',
            'to_ip_address' => 'nullable|string|max:50',
            'transfer_date' => 'required|date',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $toDepartmentId = ($user && $user->isUser())
            ? $user->department_id
            : ($request->to_department_id ?: $assetTransfer->to_department_id);

        $toDept = $toDepartmentId ? Department::find($toDepartmentId) : null;

        $assetTransfer->transfer_type = $request->transfer_type;
        $assetTransfer->to_department_id = $toDepartmentId;
        $assetTransfer->to_department_name = $toDept ? $toDept->name : $assetTransfer->to_department_name;
        $assetTransfer->to_location_detail = $request->to_location_detail;
        $assetTransfer->to_custodian_name = $request->to_custodian_name;
        $assetTransfer->to_ip_address = $request->to_ip_address;
        $assetTransfer->transfer_date = $request->transfer_date;
        $assetTransfer->reason = $request->reason;
        $assetTransfer->notes = $request->notes;

        if ($request->filled('technician_id') && ($user && ($user->isAdmin() || $user->isTechnician()))) {
            $assetTransfer->technician_id = $request->technician_id;
        }

        $assetTransfer->save();

        return redirect()->route('asset-transfers.show', $assetTransfer)->with('success', 'ปรับปรุงข้อมูลการย้ายครุภัณฑ์เรียบร้อยแล้ว');
    }

    /**
     * Update the workflow status of the transfer (In Progress, Complete, Cancel).
     */
    public function updateStatus(Request $request, AssetTransfer $assetTransfer)
    {
        $user = Auth::user();
        if (!$user || (!$user->isAdmin() && !$user->isTechnician())) {
            abort(403, 'เฉพาะเจ้าหน้าที่และผู้ดูแลระบบเท่านั้นที่มีสิทธิ์ดำเนินการ');
        }

        $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
            'technician_id' => 'nullable|exists:it_users,id',
            'test_result' => 'nullable|string|max:500',
            'receiver_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            $assetTransfer->status = $newStatus;

            if ($request->filled('technician_id')) {
                $assetTransfer->technician_id = $request->technician_id;
            } elseif (!$assetTransfer->technician_id) {
                $assetTransfer->technician_id = $user->id;
            }

            if ($request->filled('notes')) {
                $assetTransfer->notes = $request->notes;
            }

            if ($newStatus === 'completed') {
                $assetTransfer->completed_at = Carbon::now();
                $assetTransfer->test_result = $request->test_result ?: 'ตรวจสอบการเชื่อมต่อเครือข่ายและระบบสารสนเทศใช้งานได้ปกติ';
                $assetTransfer->receiver_name = $request->receiver_name ?: ($assetTransfer->to_custodian_name ?: 'เจ้าหน้าที่หน่วยงาน');

                // Update Master Asset location and custodian
                $asset = $assetTransfer->asset;
                if ($asset) {
                    if ($assetTransfer->to_department_id) {
                        $asset->department_id = $assetTransfer->to_department_id;
                    }
                    if ($assetTransfer->to_location_detail) {
                        $asset->location_detail = $assetTransfer->to_location_detail;
                    }
                    if ($assetTransfer->to_custodian_name) {
                        $asset->custodian_name = $assetTransfer->to_custodian_name;
                    }
                    if ($assetTransfer->to_ip_address) {
                        $asset->ip_address = $assetTransfer->to_ip_address;
                    }
                    $asset->save();
                }
            }

            $assetTransfer->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'ไม่สามารถบันทึกสถานะได้: ' . $e->getMessage());
        }

        // Send LINE notification
        try {
            MophNotifyService::sendAssetTransferNotification($assetTransfer, $newStatus);
            UserLineNotificationService::notifyTransferUser($assetTransfer, $newStatus);
        } catch (\Throwable $e) {
            // Log and continue
        }

        $statusMsg = match($newStatus) {
            'in_progress' => 'รับเรื่องและเปลี่ยนสถานะเป็นกำลังดำเนินการย้าย',
            'completed' => 'บันทึกการย้ายและติดตั้งเสร็จสมบูรณ์ พร้อมอัปเดตข้อมูลครุภัณฑ์ทันที',
            'cancelled' => 'ยกเลิกคำขอย้ายจุดติดตั้งครุภัณฑ์เรียบร้อยแล้ว',
            default => 'อัปเดตสถานะเรียบร้อยแล้ว',
        };

        return redirect()->route('asset-transfers.show', $assetTransfer)->with('success', $statusMsg);
    }

    /**
     * Remove the specified asset transfer from storage.
     */
    public function destroy(AssetTransfer $assetTransfer)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถลบรายการได้');
        }

        $no = $assetTransfer->transfer_no;
        $assetTransfer->delete();

        return redirect()->route('asset-transfers.index')->with('success', "ลบรายการย้ายครุภัณฑ์ #{$no} เรียบร้อยแล้ว");
    }

    /**
     * Printable Transfer Slip (ใบแจ้งย้าย/ส่งมอบจุดติดตั้งครุภัณฑ์คอมพิวเตอร์และ IT)
     */
    public function print(AssetTransfer $assetTransfer)
    {
        $assetTransfer->load(['asset.deviceType', 'fromDepartment', 'toDepartment', 'user', 'technician']);
        return view('asset_transfers.print', compact('assetTransfer'));
    }

    /**
     * Export transfers to CSV.
     */
    public function exportCsv(Request $request)
    {
        $query = AssetTransfer::with(['asset.deviceType', 'fromDepartment', 'toDepartment', 'user', 'technician'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('transfer_type')) {
            $query->where('transfer_type', $request->transfer_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transfer_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transfer_date', '<=', $request->date_to);
        }

        $transfers = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="it-asset-transfers-' . date('Ymd-His') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($transfers) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Thai language Excel support
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'เลขที่เอกสาร',
                'วันที่ย้าย',
                'รหัสครุภัณฑ์',
                'ชื่อครุภัณฑ์',
                'ประเภทอุปกรณ์',
                'ยี่ห้อ/รุ่น',
                'Serial Number',
                'ประเภทการย้าย',
                'แผนกเดิม',
                'จุดติดตั้งเดิม',
                'ผู้ครอบครองเดิม',
                'แผนกใหม่',
                'จุดติดตั้งใหม่',
                'ผู้ครอบครองใหม่',
                'IP ใหม่',
                'เหตุผลการย้าย',
                'ผู้ทำรายการ',
                'ช่างผู้ย้าย',
                'ผู้รับมอบ',
                'ผลการทดสอบ',
                'สถานะ',
                'วันเวลาเสร็จสิ้น',
            ]);

            foreach ($transfers as $t) {
                fputcsv($handle, [
                    $t->transfer_no,
                    $t->transfer_date ? $t->transfer_date->format('d/m/Y') : '-',
                    $t->asset?->asset_code ?: '-',
                    $t->asset?->name ?: '-',
                    $t->asset?->deviceType?->name ?: '-',
                    trim(($t->asset?->brand ?? '') . ' ' . ($t->asset?->model ?? '')),
                    $t->asset?->serial_number ?: '-',
                    $t->transfer_type_label,
                    $t->fromDepartment?->name ?: ($t->from_department_name ?: '-'),
                    $t->from_location_detail ?: '-',
                    $t->from_custodian_name ?: '-',
                    $t->toDepartment?->name ?: ($t->to_department_name ?: '-'),
                    $t->to_location_detail ?: '-',
                    $t->to_custodian_name ?: '-',
                    $t->to_ip_address ?: '-',
                    $t->reason ?: '-',
                    $t->user?->name ?: '-',
                    $t->technician?->name ?: '-',
                    $t->receiver_name ?: '-',
                    $t->test_result ?: '-',
                    $t->status_label,
                    $t->completed_at ? $t->completed_at->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
