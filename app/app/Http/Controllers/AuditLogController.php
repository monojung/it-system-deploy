<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with(['user.department'])->orderBy('id', 'desc');

        // Summary KPI statistics
        $totalLogs = AuditLog::count();
        $todayLogs = AuditLog::whereDate('created_at', Carbon::today())->count();
        $criticalLogs = AuditLog::whereIn('action', ['delete', 'restore', 'toggle_active', 'toggle_enforce_mfa', 'clear_data', 'clear_old'])->count();
        $activeUsersToday = AuditLog::whereDate('created_at', Carbon::today())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Quick filter preset
        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'critical':
                    $query->whereIn('action', ['delete', 'restore', 'toggle_active', 'toggle_enforce_mfa', 'clear_data', 'clear_old']);
                    break;
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('user_name', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%")
                  ->orWhere('action', 'like', "%{$s}%")
                  ->orWhere('module', 'like', "%{$s}%");
            });
        }

        // Module filter
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $logs = $query->paginate($perPage)->withQueryString();

        $modules = [
            'auth' => 'ความปลอดภัย & ยืนยันตัวตน',
            'repairs' => 'ระบบแจ้งซ่อมบำรุง',
            'data_requests' => 'บริการข้อมูล & สารสนเทศ',
            'assets' => 'คลังครุภัณฑ์ & ฮาร์ดแวร์',
            'spare_parts' => 'คลังพัสดุ & อะไหล่ IT',
            'backups' => 'สำรอง & กู้คืนฐานข้อมูล',
            'users' => 'จัดการผู้ใช้งาน & สิทธิ์',
            'departments' => 'จัดการแผนก / หน่วยงาน',
            'settings' => 'ตั้งค่าระบบกลาง',
            'reports' => 'รายงานและสถิติ',
        ];

        $actions = [
            'login' => 'เข้าสู่ระบบ (Login)',
            'logout' => 'ออกจากระบบ (Logout)',
            'create' => 'สร้างข้อมูลใหม่ (Create)',
            'update' => 'แก้ไขข้อมูล (Update)',
            'delete' => 'ลบข้อมูล (Delete)',
            'status_change' => 'เปลี่ยนสถานะ (Status Change)',
            'stock_adjust' => 'ปรับปรุงสต็อก (Stock Adjust)',
            'import' => 'นำเข้าข้อมูล (Import)',
            'export' => 'ส่งออกข้อมูล (Export)',
            'download' => 'ดาวน์โหลดไฟล์ (Download)',
            'download_sample' => 'ดาวน์โหลดตัวอย่าง (Sample)',
            'backup' => 'สำรองฐานข้อมูล (Backup)',
            'restore' => 'กู้คืนฐานข้อมูล (Restore)',
            'toggle_active' => 'ปรับสถานะผู้ใช้ (Toggle Active)',
            'toggle_mfa' => 'ตั้งค่า 2FA/MFA',
        ];

        $users = User::orderBy('name')->get();
        $retentionSettingDays = (int) setting('audit_log_retention_days', 90);

        return view('audit_logs.index', compact(
            'logs',
            'totalLogs',
            'todayLogs',
            'criticalLogs',
            'activeUsersToday',
            'modules',
            'actions',
            'users',
            'perPage',
            'retentionSettingDays'
        ));
    }

    public function show($id)
    {
        $log = AuditLog::with(['user.department'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'id' => $log->id,
            'action' => $log->action,
            'action_name' => $log->action_name,
            'action_badge' => $log->action_badge,
            'module' => $log->module,
            'module_name' => $log->module_name,
            'module_icon' => $log->module_icon,
            'description' => $log->description,
            'user_name' => $log->user_name,
            'user_role' => $log->user?->role_name ?? ucfirst($log->user_role ?? 'guest'),
            'user_department' => $log->user?->department?->name ?? '-',
            'user_avatar' => $log->user?->avatar ? asset($log->user->avatar) : null,
            'ip_address' => $log->ip_address ?? '127.0.0.1',
            'browser_info' => $log->browser_info,
            'user_agent' => $log->user_agent,
            'url' => $log->url,
            'method' => $log->method,
            'created_at' => $log->created_at->format('d/m/Y H:i:s'),
            'created_at_human' => $log->created_at->diffForHumans(),
            'old_values' => $log->old_values,
            'new_values' => $log->new_values,
            'target_url' => $log->target_url,
            'target_label' => $log->target_label,
            'target_icon' => $log->target_icon,
        ]);
    }

    public function export(Request $request)
    {
        $query = AuditLog::with(['user.department'])->orderBy('id', 'desc');

        if ($request->filled('filter')) {
            switch ($request->filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'critical':
                    $query->whereIn('action', ['delete', 'restore', 'toggle_active', 'toggle_enforce_mfa', 'clear_data', 'clear_old']);
                    break;
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('description', 'like', "%{$s}%")
                  ->orWhere('user_name', 'like', "%{$s}%")
                  ->orWhere('ip_address', 'like', "%{$s}%");
            });
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $list = $query->limit(5000)->get();
        $filename = 'audit_logs_' . date('Ymd_His') . '.csv';

        AuditLog::record('export', 'auth', "ส่งออกรายงาน Audit Logs เป็นไฟล์ CSV (จำนวน " . count($list) . " รายการ)", null);

        return response()->streamDownload(function () use ($list) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($handle, [
                'ลำดับ',
                'วัน-เวลาที่เกิดรายการ',
                'ผู้ดำเนินการ',
                'สิทธิ์การใช้งาน',
                'แผนก/หน่วยงาน',
                'โมดูลระบบ',
                'ประเภทการทำงาน',
                'รายการที่เกี่ยวข้อง',
                'รายละเอียดกิจกรรม',
                'IP Address',
                'เบราว์เซอร์/อุปกรณ์',
                'URL',
                'HTTP Method',
            ]);

            foreach ($list as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name,
                    $log->user?->role_name ?? ucfirst($log->user_role ?? 'guest'),
                    $log->user?->department?->name ?? '-',
                    $log->module_name,
                    $log->action_name,
                    $log->target_label ?? '-',
                    $log->description,
                    $log->ip_address ?? '-',
                    $log->browser_info,
                    $log->url ?? '-',
                    $log->method ?? '-',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function clearOld(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:7|max:3650',
            'confirm_text' => 'required|in:CLEAR',
        ], [
            'confirm_text.in' => 'กรุณาพิมพ์คำว่า CLEAR ให้ถูกต้องเพื่อยืนยันการล้างข้อมูล',
        ]);

        $days = (int) $request->days;
        $cutoff = Carbon::now()->subDays($days);

        $count = AuditLog::where('created_at', '<', $cutoff)->count();
        AuditLog::where('created_at', '<', $cutoff)->delete();

        AuditLog::record('clear_old', 'auth', "ล้างข้อมูล Audit Logs ที่เก่ากว่า {$days} วัน เรียบร้อยแล้ว (ลบ {$count} รายการ)", null);

        return redirect()->route('audit-logs.index')->with('success', "ล้างประวัติการใช้งานที่เก่ากว่า {$days} วัน สำเร็จแล้ว (รวม {$count} รายการ)");
    }
}
