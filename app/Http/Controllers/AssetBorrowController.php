<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetBorrow;
use App\Models\Asset;
use App\Models\Department;
use App\Models\Repair;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AssetBorrowController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = AssetBorrow::with(['asset.deviceType', 'department', 'user', 'repair']);

        // Scope regular user access
        if ($user && $user->isUser()) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            });
        }

        // Filter: Status
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $status);
            }
        }

        // Filter: Department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Search: borrow_no, borrower_name, purpose, asset_code, asset name, serial_number
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('borrow_no', 'like', "%{$search}%")
                  ->orWhere('borrower_name', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($qa) use ($search) {
                      $qa->where('name', 'like', "%{$search}%")
                         ->orWhere('asset_code', 'like', "%{$search}%")
                         ->orWhere('serial_number', 'like', "%{$search}%")
                         ->orWhere('brand', 'like', "%{$search}%")
                         ->orWhere('model', 'like', "%{$search}%");
                  });
            });
        }

        // Statistics
        $statsBase = AssetBorrow::query();
        if ($user && $user->isUser()) {
            $statsBase->where(function ($q) use ($user) {
                $q->where('user_id', $user->id);
                if ($user->department_id) {
                    $q->orWhere('department_id', $user->department_id);
                }
            });
        }

        $totalCount = (clone $statsBase)->count();
        $pendingCount = (clone $statsBase)->where('status', 'pending')->count();
        $activeBorrowedCount = (clone $statsBase)->where('status', 'borrowed')->count();
        $overdueCount = (clone $statsBase)->where('status', 'borrowed')->whereDate('expected_return_date', '<', Carbon::today())->count();
        $returnedThisMonthCount = (clone $statsBase)->where('status', 'returned')
            ->whereMonth('actual_return_date', Carbon::now()->month)
            ->whereYear('actual_return_date', Carbon::now()->year)
            ->count();

        $borrows = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('asset_borrows.index', compact(
            'borrows',
            'departments',
            'totalCount',
            'pendingCount',
            'activeBorrowedCount',
            'overdueCount',
            'returnedThisMonthCount'
        ));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $selectedAssetId = $request->input('asset_id');
        $repairId = $request->input('repair_id');
        $linkedRepair = null;
        $prefillBorrower = null;

        if ($repairId) {
            $linkedRepair = Repair::with(['asset', 'department'])->find($repairId);
            if ($linkedRepair) {
                $prefillBorrower = [
                    'name' => $linkedRepair->requester_name,
                    'department_id' => $linkedRepair->department_id,
                    'phone' => $linkedRepair->requester_phone,
                    'purpose' => "ใช้งานทดแทนอุปกรณ์ระหว่างส่งซ่อม (ใบแจ้งซ่อม #{$linkedRepair->ticket_number}: {$linkedRepair->title})",
                    'location' => $linkedRepair->location_detail,
                ];
            }
        }

        // Assets available for borrowing:
        // status IN ('spare', 'active') and not currently actively borrowed
        $borrowedAssetIds = AssetBorrow::whereIn('status', ['approved', 'borrowed'])->pluck('asset_id')->toArray();

        $availableAssets = Asset::with('deviceType', 'department')
            ->whereNotIn('status', ['repairing', 'broken', 'disposed'])
            ->where(function ($q) use ($borrowedAssetIds, $selectedAssetId) {
                $q->whereNotIn('id', $borrowedAssetIds);
                if ($selectedAssetId) {
                    $q->orWhere('id', $selectedAssetId);
                }
            })
            ->orderByRaw("CASE WHEN status = 'spare' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->get();

        $presetAccessories = [
            'Adapter ชาร์จ / สายไฟ AC',
            'กระเป๋าใส่โน้ตบุ๊ก',
            'เมาส์ (Mouse)',
            'สายแปลงสัญญาณ HDMI / VGA',
            'ปลั๊กพ่วงสายไฟ (Extension Cord)',
            'รีโมทคอนโทรล (Remote Control)',
            'สายสัญญาณ LAN (UTP)',
            'เคส / กระเป๋าใส่อุปกรณ์',
        ];

        return view('asset_borrows.create', compact(
            'availableAssets',
            'departments',
            'selectedAssetId',
            'presetAccessories',
            'user',
            'linkedRepair',
            'prefillBorrower'
        ));
    }

    public function store(Request $request)
    {
        // Normalize input aliases
        if (!$request->filled('contact_phone') && $request->filled('borrower_phone')) {
            $request->merge(['contact_phone' => $request->input('borrower_phone')]);
        }
        if (!$request->filled('location_used') && $request->filled('location_of_use')) {
            $request->merge(['location_used' => $request->input('location_of_use')]);
        }
        if (!$request->filled('notes') && $request->filled('borrower_note')) {
            $request->merge(['notes' => $request->input('borrower_note')]);
        }
        if (!$request->filled('department_id') && $request->filled('borrower_department')) {
            $dept = Department::where('name', $request->input('borrower_department'))->first();
            if (!$dept) {
                $dept = Department::create(['name' => $request->input('borrower_department'), 'is_active' => true]);
            }
            $request->merge(['department_id' => $dept->id]);
        }

        $validated = $request->validate([
            'asset_id' => 'required|exists:it_assets,id',
            'repair_id' => 'nullable|exists:it_repairs,id',
            'borrower_name' => 'required|string|max:100',
            'department_id' => 'required|exists:it_departments,id',
            'contact_phone' => 'required|string|max:50',
            'purpose' => 'required|string|max:1000',
            'location_used' => 'nullable|string|max:255',
            'borrow_date' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:borrow_date',
            'accessories' => 'nullable',
            'accessories_other' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify asset availability
        $alreadyBorrowed = AssetBorrow::where('asset_id', $validated['asset_id'])
            ->whereIn('status', ['approved', 'borrowed'])
            ->exists();

        if ($alreadyBorrowed) {
            return back()->withInput()->withErrors(['asset_id' => 'อุปกรณ์ชิ้นนี้กำลังอยู่ระหว่างการยืมใช้งาน หรือได้รับการอนุมัติแล้ว กรุณาเลือกอุปกรณ์อื่น']);
        }

        // Process accessories
        $accList = [];
        $rawAcc = $request->input('accessories');
        if (is_array($rawAcc)) {
            $accList = $rawAcc;
        } elseif (is_string($rawAcc) && trim($rawAcc) !== '') {
            $accList = array_map('trim', explode(',', $rawAcc));
        }
        if ($request->filled('accessories_other')) {
            $accList[] = trim($request->input('accessories_other'));
        }
        $accList = array_values(array_unique(array_filter($accList)));

        $borrowNo = AssetBorrow::generateBorrowNo();

        $borrow = AssetBorrow::create([
            'borrow_no' => $borrowNo,
            'user_id' => Auth::id(),
            'borrower_name' => $validated['borrower_name'],
            'department_id' => $validated['department_id'],
            'borrower_department' => $request->input('borrower_department') ?? Department::find($validated['department_id'])?->name,
            'borrower_position' => $request->input('borrower_position'),
            'contact_phone' => $validated['contact_phone'],
            'asset_id' => $validated['asset_id'],
            'repair_id' => $validated['repair_id'] ?? null,
            'purpose' => $validated['purpose'],
            'location_used' => $validated['location_used'] ?? null,
            'borrow_date' => $validated['borrow_date'],
            'expected_return_date' => $validated['expected_return_date'],
            'accessories' => $accList,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Send MOPH Notify notification for new borrow request
        try {
            \App\Services\MophNotifyService::sendAssetBorrowNotification($borrow, 'created');
            \App\Services\UserLineNotificationService::notifyBorrowUser($borrow, 'created');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending asset borrow notification: ' . $e->getMessage());
        }

        return redirect()->route('asset-borrows.show', $borrow->id)
            ->with('success', "บันทึกคำขอยืมอุปกรณ์เรียบร้อยแล้ว รหัสคำขอ: {$borrowNo}");
    }

    public function show($id)
    {
        $borrow = AssetBorrow::with([
            'asset.deviceType',
            'asset.department',
            'department',
            'user',
            'repair',
            'approver',
            'dispatcher',
            'receiver'
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->isUser()) {
            if ($borrow->user_id !== $user->id && $borrow->department_id !== $user->department_id) {
                abort(403, 'ท่านไม่มีสิทธิ์ดูข้อมูลคำขอนี้');
            }
        }

        return view('asset_borrows.show', compact('borrow'));
    }

    public function edit($id)
    {
        $borrow = AssetBorrow::with('asset')->findOrFail($id);
        $user = Auth::user();

        if ($user && $user->isUser() && $borrow->user_id !== $user->id) {
            abort(403, 'ท่านไม่มีสิทธิ์แก้ไขคำขอนี้');
        }

        if (!in_array($borrow->status, ['pending', 'rejected']) && !($user->isAdmin() || $user->isTechnician())) {
            return redirect()->route('asset-borrows.show', $borrow->id)
                ->with('error', 'ไม่สามารถแก้ไขคำขอที่ได้รับการอนุมัติหรือส่งมอบแล้ว');
        }

        $borrowedAssetIds = AssetBorrow::whereIn('status', ['approved', 'borrowed'])
            ->where('id', '!=', $borrow->id)
            ->pluck('asset_id')
            ->toArray();

        $availableAssets = Asset::with('deviceType')
            ->whereNotIn('status', ['repairing', 'broken', 'disposed'])
            ->where(function ($q) use ($borrowedAssetIds, $borrow) {
                $q->whereNotIn('id', $borrowedAssetIds)
                  ->orWhere('id', $borrow->asset_id);
            })
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->get();

        $presetAccessories = [
            'Adapter ชาร์จ / สายไฟ AC',
            'กระเป๋าใส่โน้ตบุ๊ก',
            'เมาส์ (Mouse)',
            'สายแปลงสัญญาณ HDMI / VGA',
            'ปลั๊กพ่วงสายไฟ (Extension Cord)',
            'รีโมทคอนโทรล (Remote Control)',
            'สายสัญญาณ LAN (UTP)',
            'เคส / กระเป๋าใส่อุปกรณ์',
        ];

        // Parse current accessories
        $currentAcc = array_map('trim', explode(',', $borrow->accessories ?? ''));

        return view('asset_borrows.edit', compact(
            'borrow',
            'availableAssets',
            'departments',
            'presetAccessories',
            'currentAcc'
        ));
    }

    public function update(Request $request, $id)
    {
        $borrow = AssetBorrow::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->isUser() && $borrow->user_id !== $user->id) {
            abort(403, 'ท่านไม่มีสิทธิ์แก้ไขคำขอนี้');
        }

        $validated = $request->validate([
            'asset_id' => 'required|exists:it_assets,id',
            'borrower_name' => 'required|string|max:100',
            'department_id' => 'required|exists:it_departments,id',
            'contact_phone' => 'required|string|max:50',
            'purpose' => 'required|string|max:1000',
            'location_used' => 'nullable|string|max:255',
            'borrow_date' => 'required|date',
            'expected_return_date' => 'required|date|after_or_equal:borrow_date',
            'accessories' => 'nullable|array',
            'accessories_other' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $accList = $request->input('accessories', []);
        if ($request->filled('accessories_other')) {
            $accList[] = trim($request->input('accessories_other'));
        }
        $accessoriesStr = !empty($accList) ? implode(', ', $accList) : null;

        $borrow->update([
            'asset_id' => $validated['asset_id'],
            'borrower_name' => $validated['borrower_name'],
            'department_id' => $validated['department_id'],
            'contact_phone' => $validated['contact_phone'],
            'purpose' => $validated['purpose'],
            'location_used' => $validated['location_used'],
            'borrow_date' => $validated['borrow_date'],
            'expected_return_date' => $validated['expected_return_date'],
            'accessories' => $accessoriesStr,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('asset-borrows.show', $borrow->id)
            ->with('success', 'แก้ไขข้อมูลคำขอยืมอุปกรณ์เรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $borrow = AssetBorrow::findOrFail($id);
        $user = Auth::user();

        if ($user && $user->isUser() && $borrow->user_id !== $user->id) {
            abort(403, 'ท่านไม่มีสิทธิ์ยกเลิกคำขอนี้');
        }

        if (in_array($borrow->status, ['borrowed'])) {
            return back()->with('error', 'ไม่สามารถลบคำขอที่อยู่ในระหว่างการยืมใช้งานได้ ต้องทำรายการส่งคืนก่อน');
        }

        $borrow->delete();

        return redirect()->route('asset-borrows.index')
            ->with('success', 'ยกเลิกคำขอยืมอุปกรณ์เรียบร้อยแล้ว');
    }

    // --- Admin / Technician Workflow Actions ---

    public function approve(Request $request, $id)
    {
        $borrow = AssetBorrow::findOrFail($id);
        
        $borrow->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => Carbon::now(),
        ]);

        // Send MOPH Notify notification for approved borrow request
        try {
            \App\Services\MophNotifyService::sendAssetBorrowNotification($borrow, 'approved');
            \App\Services\UserLineNotificationService::notifyBorrowUser($borrow, 'approved');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending asset borrow approval notification: ' . $e->getMessage());
        }

        return redirect()->route('asset-borrows.show', $borrow->id)
            ->with('success', 'อนุมัติคำขอยืมอุปกรณ์เรียบร้อยแล้ว (พร้อมส่งมอบอุปกรณ์)');
    }

    public function reject(Request $request, $id)
    {
        $borrow = AssetBorrow::findOrFail($id);
        $reason = trim($request->input('reason', $request->input('rejection_reason', '')));

        $borrow->update([
            'status' => 'rejected',
            'rejection_reason' => $reason ?: null,
            'notes' => $borrow->notes . ($reason ? "\n[เหตุผลที่ไม่อนุมัติ: {$reason}]" : ''),
        ]);

        // Send MOPH Notify notification for rejected borrow request
        try {
            \App\Services\MophNotifyService::sendAssetBorrowNotification($borrow, 'rejected');
            \App\Services\UserLineNotificationService::notifyBorrowUser($borrow, 'rejected');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending asset borrow rejection notification: ' . $e->getMessage());
        }

        return redirect()->route('asset-borrows.show', $borrow->id)
            ->with('info', 'ปฏิเสธคำขอยืมอุปกรณ์เรียบร้อยแล้ว');
    }

    public function dispatch(Request $request, $id)
    {
        $borrow = AssetBorrow::with('asset')->findOrFail($id);

        $condition = $request->input('dispatch_condition') 
            ?? $request->input('condition_on_dispatch') 
            ?? 'ปกติ สมบูรณ์ ครบถ้วน';

        $borrow->update([
            'status' => 'borrowed',
            'dispatched_by' => Auth::id(),
            'dispatched_at' => Carbon::now(),
            'dispatch_condition' => $condition,
        ]);

        // Update asset status
        if ($borrow->asset) {
            $borrow->asset->update(['status' => 'borrowed']);
        }

        // Send MOPH Notify notification for dispatched borrow request
        try {
            \App\Services\MophNotifyService::sendAssetBorrowNotification($borrow, 'dispatched');
            \App\Services\UserLineNotificationService::notifyBorrowUser($borrow, 'dispatched');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending asset borrow dispatch notification: ' . $e->getMessage());
        }

        return redirect()->route('asset-borrows.show', $borrow->id)
            ->with('success', 'ส่งมอบอุปกรณ์เรียบร้อยแล้ว สถานะเปลี่ยนเป็น "กำลังยืมใช้งาน"');
    }

    public function receiveReturn(Request $request, $id)
    {
        $borrow = AssetBorrow::with('asset')->findOrFail($id);

        $returnCondition = $request->input('return_condition') 
            ?? $request->input('condition_on_return') 
            ?? 'ปกติ สมบูรณ์';
        $returnNotes = $request->input('return_notes');
        $actualDate = $request->input('actual_return_date', Carbon::today()->toDateString());
        $targetStatus = $request->input('return_status') 
            ?? $request->input('target_asset_status') 
            ?? 'spare'; // spare or active

        $borrow->update([
            'status' => 'returned',
            'actual_return_date' => $actualDate,
            'received_by' => Auth::id(),
            'return_condition' => $returnCondition,
            'return_notes' => $returnNotes,
        ]);

        // Restore asset status
        if ($borrow->asset) {
            $borrow->asset->update([
                'status' => $returnCondition === 'ชำรุดเสียหาย' ? 'broken' : $targetStatus,
            ]);
        }

        // Send MOPH Notify notification for returned borrow request
        try {
            \App\Services\MophNotifyService::sendAssetBorrowNotification($borrow, 'returned');
            \App\Services\UserLineNotificationService::notifyBorrowUser($borrow, 'returned');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending asset borrow return notification: ' . $e->getMessage());
        }

        return redirect()->route('asset-borrows.show', $borrow->id)
            ->with('success', 'บันทึกการส่งคืนอุปกรณ์เรียบร้อยแล้ว ขอบคุณสำหรับการใช้งาน');
    }

    public function print($id)
    {
        $borrow = AssetBorrow::with([
            'asset.deviceType',
            'asset.department',
            'department',
            'repair',
            'user',
            'approver',
            'dispatcher',
            'receiver'
        ])->findOrFail($id);

        return view('asset_borrows.print', compact('borrow'));
    }
}
