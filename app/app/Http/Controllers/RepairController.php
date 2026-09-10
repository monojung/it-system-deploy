<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Repair;
use App\Models\RepairLog;
use App\Models\RepairPart;
use App\Models\Asset;
use App\Models\Department;
use App\Models\SparePart;
use App\Models\User;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RepairController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Repair::with(['department', 'asset', 'technician', 'requester']);

        // General user filter
        if ($user->isUser() && $user->department_id) {
            // Can choose to see all department tickets or own
            if ($request->has('my_only')) {
                $query->where('requester_id', $user->id);
            } else {
                $query->where('department_id', $user->department_id);
            }
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('requester_name', 'like', "%{$search}%")
                  ->orWhere('other_device_info', 'like', "%{$search}%")
                  ->orWhereHas('asset', function ($aq) use ($search) {
                      $aq->where('asset_code', 'like', "%{$search}%")
                         ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }

        $repairs = $query->latest()->paginate($perPage)->withQueryString();

        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $technicians = User::whereIn('role', ['admin', 'technician'])->where('is_active', true)->orderBy('name')->get();

        return view('repairs.index', compact('repairs', 'departments', 'technicians'));
    }

    public function create()
    {
        $user = Auth::user();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        $assetsQuery = Asset::with(['deviceType', 'department'])
            ->whereIn('status', ['active', 'spare', 'broken'])
            ->orderBy('asset_code');

        // Regular users can only see assets of their own department
        if ($user && $user->isUser()) {
            if ($user->department_id) {
                $assetsQuery->where('department_id', $user->department_id);
            } else {
                $assetsQuery->whereRaw('1 = 0');
            }
        }

        $assets = $assetsQuery->get();

        return view('repairs.create', compact('departments', 'assets', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:it_departments,id',
            'urgency' => 'required|in:low,normal,high,critical',
            'requester_name' => 'required|string|max:255',
            'requester_phone' => 'required|string|max:100',
            'asset_id' => 'nullable|exists:it_assets,id',
            'other_device_info' => 'nullable|string|max:255',
            'location_detail' => 'nullable|string|max:255',
            'attachment_image' => 'nullable|image|max:5120',
        ]);

        $user = Auth::user();

        // Regular users can only submit repair tickets for assets of their own department
        if ($user && $user->isUser() && $request->filled('asset_id')) {
            $allowedAsset = Asset::where('id', $request->asset_id)
                ->where('department_id', $user->department_id)
                ->exists();
            if (!$allowedAsset) {
                return back()->withInput()->withErrors(['asset_id' => 'ท่านสามารถเลือกแจ้งซ่อมเฉพาะครุภัณฑ์ในแผนกของท่านเท่านั้น']);
            }
        }

        // Generate Ticket Number: {PREFIX}-YYYYMM-XXXX
        $customPrefix = trim((string) setting('ticket_prefix', 'REP'));
        if (empty($customPrefix)) {
            $customPrefix = 'REP';
        }
        $yearMonth = Carbon::now()->format('Ym');
        $prefix = "{$customPrefix}-{$yearMonth}-";
        $latest = Repair::where('ticket_number', 'like', "{$prefix}%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->ticket_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }
        $ticketNumber = $prefix . $nextNum;

        // Image upload
        $imagePath = null;
        if ($request->hasFile('attachment_image')) {
            $file = $request->file('attachment_image');
            $filename = 'repair_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/repairs'), $filename);
            $imagePath = 'uploads/repairs/' . $filename;
        }

        $repair = Repair::create([
            'ticket_number' => $ticketNumber,
            'title' => $request->title,
            'description' => $request->description,
            'asset_id' => $request->asset_id,
            'other_device_info' => $request->other_device_info,
            'department_id' => $request->department_id,
            'location_detail' => $request->location_detail,
            'urgency' => $request->urgency,
            'status' => 'pending',
            'requester_id' => $user ? $user->id : null,
            'requester_name' => $request->requester_name,
            'requester_phone' => $request->requester_phone,
            'attachment_image' => $imagePath,
        ]);

        // If linked to asset, change asset status to repairing/broken
        if ($request->asset_id) {
            Asset::where('id', $request->asset_id)->update(['status' => 'repairing']);
        }

        // Timeline log
        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => $user ? $user->id : null,
            'action' => 'created',
            'previous_status' => null,
            'new_status' => 'pending',
            'comment' => 'แจ้งซ่อมใหม่ โดย ' . $request->requester_name . ' (' . $repair->title . ')',
            'created_at' => Carbon::now(),
        ]);

        // Send LINE notification if enabled
        try {
            \App\Services\LineNotificationService::sendRepairTicketNotification($repair);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed sending LINE repair notification: ' . $e->getMessage());
        }

        return redirect()->route('repairs.show', $repair)->with('success', "สร้างรายการแจ้งซ่อมรหัส {$ticketNumber} สำเร็จ");
    }

    public function show(Repair $repair)
    {
        $repair->load(['department', 'asset.deviceType', 'technician', 'requester', 'logs.user', 'parts.sparePart']);

        $technicians = User::whereIn('role', ['admin', 'technician'])->where('is_active', true)->orderBy('name')->get();
        $spareParts = SparePart::where('stock_quantity', '>', 0)->orderBy('name')->get();

        return view('repairs.show', compact('repair', 'technicians', 'spareParts'));
    }

    public function edit(Repair $repair)
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $assets = Asset::with('deviceType')->orderBy('asset_code')->get();

        return view('repairs.edit', compact('repair', 'departments', 'assets'));
    }

    public function update(Request $request, Repair $repair)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:it_departments,id',
            'urgency' => 'required|in:low,normal,high,critical',
            'requester_name' => 'required|string|max:255',
            'requester_phone' => 'required|string|max:100',
            'asset_id' => 'nullable|exists:it_assets,id',
            'other_device_info' => 'nullable|string|max:255',
            'location_detail' => 'nullable|string|max:255',
        ]);

        $repair->update($request->only([
            'title', 'description', 'department_id', 'urgency',
            'requester_name', 'requester_phone', 'asset_id',
            'other_device_info', 'location_detail'
        ]));

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => Auth::id(),
            'action' => 'updated',
            'previous_status' => $repair->status,
            'new_status' => $repair->status,
            'comment' => 'แก้ไขข้อมูลรายละเอียดงานแจ้งซ่อม',
            'created_at' => Carbon::now(),
        ]);

        return redirect()->route('repairs.show', $repair)->with('success', 'บันทึกการแก้ไขข้อมูลเรียบร้อยแล้ว');
    }

    public function updateStatus(Request $request, Repair $repair)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,waiting_parts,completed,external,cancelled',
            'technician_id' => 'nullable|exists:it_users,id',
            'comment' => 'nullable|string',
            'cause' => 'nullable|string',
            'solution' => 'nullable|string',
            'external_repair_detail' => 'nullable|string',
        ]);

        $prevStatus = $repair->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        // Technician assignment
        if ($request->filled('technician_id')) {
            $updateData['technician_id'] = $request->technician_id;
        }

        // Received timestamp
        if ($newStatus === 'in_progress' && !$repair->received_at) {
            $updateData['received_at'] = Carbon::now();
        }

        // Completion details
        if ($newStatus === 'completed') {
            $updateData['completed_at'] = Carbon::now();
            if ($request->filled('cause')) $updateData['cause'] = $request->cause;
            if ($request->filled('solution')) $updateData['solution'] = $request->solution;

            // Reset asset status to active
            if ($repair->asset_id) {
                Asset::where('id', $repair->asset_id)->update(['status' => 'active']);
            }
        }

        if ($newStatus === 'external') {
            if ($request->filled('external_repair_detail')) {
                $updateData['external_repair_detail'] = $request->external_repair_detail;
            }
            if ($repair->asset_id) {
                Asset::where('id', $repair->asset_id)->update(['status' => 'repairing']);
            }
        }

        if ($newStatus === 'cancelled' && $repair->asset_id) {
            Asset::where('id', $repair->asset_id)->update(['status' => 'active']);
        }

        $repair->update($updateData);

        // Add Log
        $comment = $request->comment ?: "เปลี่ยนสถานะจาก {$repair->getStatusLabelAttribute()} ({$newStatus})";
        if ($newStatus === 'completed' && $request->filled('solution')) {
            $comment .= ' | วิธีแก้ไข: ' . $request->solution;
        }

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => Auth::id(),
            'action' => 'status_changed',
            'previous_status' => $prevStatus,
            'new_status' => $newStatus,
            'comment' => $comment,
            'created_at' => Carbon::now(),
        ]);

        AuditLog::record('status_change', 'repairs', "เปลี่ยนสถานะใบแจ้งซ่อม {$repair->ticket_number} จาก '{$prevStatus}' เป็น '{$newStatus}'", $repair, ['status' => $prevStatus], ['status' => $newStatus]);

        return back()->with('success', 'อัปเดตสถานะงานซ่อมเรียบร้อยแล้ว');
    }

    public function addPart(Request $request, Repair $repair)
    {
        $request->validate([
            'spare_part_id' => 'required|exists:it_spare_parts,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $part = SparePart::findOrFail($request->spare_part_id);

        if ($part->stock_quantity < $request->quantity) {
            return back()->with('error', "พัสดุ '{$part->name}' ในคลังมีไม่เพียงพอ (คงเหลือ {$part->stock_quantity} {$part->unit})");
        }

        $totalPrice = $part->unit_price * $request->quantity;

        // Cut stock
        $part->decrement('stock_quantity', $request->quantity);

        // Record repair part
        RepairPart::create([
            'repair_id' => $repair->id,
            'spare_part_id' => $part->id,
            'quantity' => $request->quantity,
            'unit_price' => $part->unit_price,
            'total_price' => $totalPrice,
        ]);

        // Update repair total_cost
        $repair->increment('total_cost', $totalPrice);

        // Log
        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => Auth::id(),
            'action' => 'part_used',
            'previous_status' => $repair->status,
            'new_status' => $repair->status,
            'comment' => "เบิกใช้อะไหล่: {$part->name} จำนวน {$request->quantity} {$part->unit} (รวม " . number_format($totalPrice, 2) . " บาท)",
            'created_at' => Carbon::now(),
        ]);

        AuditLog::record('stock_adjust', 'repairs', "เบิกอะไหล่ {$part->name} จำนวน {$request->quantity} {$part->unit} สำหรับใบแจ้งซ่อม {$repair->ticket_number}", $repair);

        return back()->with('success', "เบิกอะไหล่ '{$part->name}' เรียบร้อยแล้ว (ตัดสต็อก {$request->quantity} {$part->unit})");
    }

    public function removePart(Repair $repair, RepairPart $part)
    {
        if ($part->repair_id !== $repair->id) {
            abort(404);
        }

        // Restore stock
        $sparePart = $part->sparePart;
        if ($sparePart) {
            $sparePart->increment('stock_quantity', $part->quantity);
        }

        $repair->decrement('total_cost', $part->total_price);

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => Auth::id(),
            'action' => 'part_removed',
            'previous_status' => $repair->status,
            'new_status' => $repair->status,
            'comment' => "ยกเลิกการเบิกอะไหล่: {$sparePart->name} คืนสต็อก {$part->quantity} {$sparePart->unit}",
            'created_at' => Carbon::now(),
        ]);

        AuditLog::record('stock_adjust', 'repairs', "ยกเลิกเบิกอะไหล่ {$sparePart->name} คืนสต็อก สำหรับใบแจ้งซ่อม {$repair->ticket_number}", $repair);

        $part->delete();

        return back()->with('success', 'ยกเลิกรายการอะไหล่และคืนสต็อกเรียบร้อยแล้ว');
    }

    public function rate(Request $request, Repair $repair)
    {
        $request->validate([
            'satisfaction_score' => 'required|integer|min:1|max:5',
            'satisfaction_comment' => 'nullable|string|max:500',
        ]);

        $repair->update([
            'satisfaction_score' => $request->satisfaction_score,
            'satisfaction_comment' => $request->satisfaction_comment,
        ]);

        RepairLog::create([
            'repair_id' => $repair->id,
            'user_id' => Auth::id(),
            'action' => 'rated',
            'previous_status' => $repair->status,
            'new_status' => $repair->status,
            'comment' => 'ประเมินความพึงพอใจการบริการ: ' . $request->satisfaction_score . ' ดาว',
            'created_at' => Carbon::now(),
        ]);

        return back()->with('success', 'ขอบพระคุณสำหรับการประเมินความพึงพอใจ');
    }

    public function print(Repair $repair)
    {
        $repair->load(['department', 'asset.deviceType', 'technician', 'requester', 'parts.sparePart', 'logs']);
        return view('repairs.print', compact('repair'));
    }
}
