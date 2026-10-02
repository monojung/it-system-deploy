<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\BudgetSource;
use App\Models\AcquisitionMethod;
use App\Models\Asset;
use App\Models\AssetBorrow;
use App\Models\AssetTransfer;
use App\Models\HardwareAudit;
use App\Models\AgentCommand;
use App\Models\Repair;
use App\Models\RepairLog;
use App\Models\RepairPart;
use App\Models\DataRequest;
use App\Models\AuditLog;
use App\Models\SparePart;
use App\Models\BackupLog;
use Carbon\Carbon;
use Exception;

class SystemResetController extends Controller
{
    /**
     * Display the System Data Management & Reset Center.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบสารสนเทศ (Admin) เท่านั้นที่สามารถเข้าถึงศูนย์จัดการข้อมูลและล้างระบบได้');
        }

        $stats = $this->gatherSystemMetrics();
        $integrity = $this->scanIntegrityMetrics();
        $versionInfo = app_version_info();

        return view('system_reset.index', compact('stats', 'integrity', 'versionInfo'));
    }

    /**
     * Return live statistics in JSON for dynamic dashboard updates.
     */
    public function getStats()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = $this->gatherSystemMetrics();
        $integrity = $this->scanIntegrityMetrics();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'integrity' => $integrity,
            'timestamp' => Carbon::now()->format('H:i:s d/m/Y'),
        ]);
    }

    /**
     * Clear data for a specific single module (Selective Modular Cleanup).
     */
    public function clearModule(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบสารสนเทศ (Admin) เท่านั้นที่สามารถดำเนินการล้างข้อมูลได้');
        }

        $request->validate([
            'module' => 'required|string|in:repairs,data_requests,asset_borrows,asset_transfers,hardware_audits,audit_logs,storage_temp,assets_reset,spare_parts_reset',
            'admin_password' => 'required|string',
            'confirmation_text' => 'required|string',
            'auto_backup' => 'nullable|boolean',
            'retention_days' => 'nullable|integer|min:7|max:3650',
            'logs_scope' => 'nullable|string|in:all,days',
        ]);

        $module = $request->input('module');

        // Verify Admin Password
        if (!Hash::check($request->input('admin_password'), $user->password)) {
            return back()->with('error', 'รหัสผ่านผู้ดูแลระบบไม่ถูกต้อง ไม่สามารถดำเนินการได้');
        }

        // Verify Specific Confirmation Phrase
        $expectedPhrase = match ($module) {
            'repairs' => 'CLEAR-REPAIRS',
            'data_requests' => 'CLEAR-REQUESTS',
            'asset_borrows' => 'CLEAR-BORROWS',
            'asset_transfers' => 'CLEAR-TRANSFERS',
            'hardware_audits' => 'CLEAR-AUDITS',
            'audit_logs' => 'CLEAR-LOGS',
            'storage_temp' => 'CLEAR-TEMP',
            'assets_reset' => 'RESET-ASSETS',
            'spare_parts_reset' => 'RESET-PARTS',
            default => 'CONFIRM',
        };

        if (trim($request->input('confirmation_text')) !== $expectedPhrase) {
            return back()->with('error', "ข้อความยืนยันไม่ถูกต้อง (ต้องพิมพ์ \"{$expectedPhrase}\" ตัวพิมพ์ใหญ่ทุกตัว)");
        }

        // Automatic Pre-cleanup Backup
        $backupFilename = null;
        if ($request->boolean('auto_backup', true)) {
            $backupFilename = $this->createSafetyBackup("สำรองข้อมูลอัตโนมัติก่อนล้างโมดูล {$module} โดย {$user->name}");
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $resultMsg = '';

            switch ($module) {
                case 'repairs':
                    // Detach repair_id from borrows if any
                    AssetBorrow::whereNotNull('repair_id')->update(['repair_id' => null]);

                    $partsCount = RepairPart::count();
                    $logsCount = RepairLog::count();
                    $repairsCount = Repair::count();

                    RepairPart::query()->delete();
                    RepairLog::query()->delete();
                    Repair::query()->delete();

                    $this->clearStorageDirectories([
                        'repairs',
                        'public/repairs',
                        'uploads/repairs',
                        'public/uploads/repairs',
                    ]);

                    DB::statement('ALTER TABLE it_repair_parts AUTO_INCREMENT = 1;');
                    DB::statement('ALTER TABLE it_repair_logs AUTO_INCREMENT = 1;');
                    DB::statement('ALTER TABLE it_repairs AUTO_INCREMENT = 1;');

                    $resultMsg = "ล้างข้อมูลระบบแจ้งซ่อมบำรุงเรียบร้อยแล้ว (ลบใบแจ้งซ่อม {$repairsCount} รายการ, ประวัติ {$logsCount} รายการ, อะไหล่ใช้ไป {$partsCount} รายการ)";
                    break;

                case 'data_requests':
                    $requestsCount = DataRequest::count();
                    DataRequest::query()->delete();

                    $this->clearStorageDirectories([
                        'data_requests',
                        'public/data_requests',
                        'uploads/data_requests',
                        'uploads/data_requests_samples',
                    ]);

                    DB::statement('ALTER TABLE it_data_requests AUTO_INCREMENT = 1;');

                    $resultMsg = "ล้างข้อมูลคำขอบริการสารสนเทศ HosXP เรียบร้อยแล้ว (ลบคำขอ {$requestsCount} รายการ พร้อมไฟล์แนบ)";
                    break;

                case 'asset_borrows':
                    // Revert borrowed assets back to active status
                    Asset::where('status', 'borrowed')->update(['status' => 'active']);

                    $borrowsCount = AssetBorrow::count();
                    AssetBorrow::query()->delete();

                    DB::statement('ALTER TABLE it_asset_borrows AUTO_INCREMENT = 1;');

                    $resultMsg = "ล้างประวัติการขอยืม-คืนอุปกรณ์ IT เรียบร้อยแล้ว (ลบ {$borrowsCount} รายการ และปรับคืนสถานะครุภัณฑ์เป็นพร้อมใช้งาน)";
                    break;

                case 'asset_transfers':
                    $transfersCount = AssetTransfer::count();
                    AssetTransfer::query()->delete();

                    $this->clearStorageDirectories([
                        'asset_transfers',
                        'public/asset_transfers',
                        'uploads/asset_transfers',
                    ]);

                    DB::statement('ALTER TABLE it_asset_transfers AUTO_INCREMENT = 1;');

                    $resultMsg = "ล้างประวัติการโอนย้ายครุภัณฑ์เรียบร้อยแล้ว (ลบ {$transfersCount} รายการ พร้อมเอกสารแนบ)";
                    break;

                case 'hardware_audits':
                    // Unlink hardware_id on assets so they are not tied to deleted audit specs
                    Asset::whereNotNull('hardware_id')->update(['hardware_id' => null]);

                    $auditsCount = HardwareAudit::count();
                    $commandsCount = AgentCommand::count();

                    AgentCommand::query()->delete();
                    HardwareAudit::query()->delete();

                    DB::statement('ALTER TABLE it_agent_commands AUTO_INCREMENT = 1;');
                    DB::statement('ALTER TABLE it_hardware_audits AUTO_INCREMENT = 1;');

                    $resultMsg = "ล้างข้อมูลการตรวจนับสเปคคอมพิวเตอร์และคำสั่ง Agent เรียบร้อยแล้ว (ลบประวัติสเปค {$auditsCount} เครื่อง, คำสั่ง {$commandsCount} รายการ)";
                    break;

                case 'audit_logs':
                    $scope = $request->input('logs_scope', 'all');
                    if ($scope === 'days' && $request->filled('retention_days')) {
                        $days = (int) $request->input('retention_days');
                        $cutoff = Carbon::now()->subDays($days);
                        $logsCount = AuditLog::where('created_at', '<', $cutoff)->count();
                        AuditLog::where('created_at', '<', $cutoff)->delete();
                        $resultMsg = "ล้างบันทึก Audit Logs ที่เก่ากว่า {$days} วัน เรียบร้อยแล้ว (ลบ {$logsCount} รายการ)";
                    } else {
                        $logsCount = AuditLog::count();
                        AuditLog::query()->delete();
                        DB::statement('ALTER TABLE it_audit_logs AUTO_INCREMENT = 1;');
                        $resultMsg = "ล้างบันทึก Audit Logs ทั้งหมดเรียบร้อยแล้ว (ลบ {$logsCount} รายการ)";
                    }
                    break;

                case 'storage_temp':
                    $freedBytes = $this->purgeTemporaryStorageFiles();
                    $formattedFreed = $this->formatBytes($freedBytes);
                    $resultMsg = "ทำความสะอาดไฟล์ชั่วคราวและไฟล์ขยะในระบบ Storage เรียบร้อยแล้ว (คืนพื้นที่ {$formattedFreed})";
                    break;

                case 'assets_reset':
                    // Safe reset: detach references from active operational records
                    AssetBorrow::query()->delete();
                    AssetTransfer::query()->delete();
                    HardwareAudit::whereNotNull('asset_id')->update(['asset_id' => null]);
                    Repair::whereNotNull('asset_id')->update(['asset_id' => null]);

                    $deletedAssets = Asset::count();
                    Asset::query()->delete();
                    DB::statement('ALTER TABLE it_assets AUTO_INCREMENT = 1;');

                    // Clean assets photos
                    $this->clearStorageDirectories(['assets', 'public/assets', 'uploads/assets']);

                    // Re-seed standard hospital computer templates
                    if (class_exists(\Database\Seeders\HardwareSpecsSeeder::class)) {
                        Artisan::call('db:seed', ['--class' => 'HardwareSpecsSeeder']);
                    } else {
                        Artisan::call('db:seed');
                    }

                    $reseededCount = Asset::count();
                    $resultMsg = "รีเซ็ตทะเบียนคลังคอมพิวเตอร์และครุภัณฑ์เรียบร้อยแล้ว (ลบข้อมูลเดิม {$deletedAssets} เครื่อง, สร้างแม่แบบตั้งต้น {$reseededCount} เครื่อง)";
                    break;

                case 'spare_parts_reset':
                    RepairPart::query()->delete();
                    DB::statement('ALTER TABLE it_repair_parts AUTO_INCREMENT = 1;');

                    $deletedParts = SparePart::count();
                    SparePart::query()->delete();
                    DB::statement('ALTER TABLE it_spare_parts AUTO_INCREMENT = 1;');

                    // Re-seed spare parts
                    Artisan::call('db:seed');

                    $reseededParts = SparePart::count();
                    $resultMsg = "รีเซ็ตคลังพัสดุและสต็อกอะไหล่ IT เรียบร้อยแล้ว (ลบ {$deletedParts} รายการ, ติดตั้งแคตตาล็อกตั้งต้น {$reseededParts} รายการ)";
                    break;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Record Audit Log
            AuditLog::record(
                'clear_module',
                'settings',
                "ผู้ดูแลระบบ ({$user->name}) ทำการล้างข้อมูลโมดูล [{$module}]: {$resultMsg}" . ($backupFilename ? " [สำรองข้อมูล: {$backupFilename}]" : ""),
                null,
                null,
                [
                    'module' => $module,
                    'auto_backup' => $backupFilename,
                    'user_id' => $user->id,
                ]
            );

            if ($backupFilename) {
                $resultMsg .= " — สร้างจุดสำรองข้อมูลไว้ที่ {$backupFilename}";
            }

            return back()->with('success', $resultMsg);

        } catch (Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'เกิดข้อผิดพลาดในการล้างข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * Clear data according to scope (operational or full factory reset).
     */
    public function clearData(Request $request)
    {
        $user = Auth::user();

        // 1. Authorization check
        if (!$user || !$user->isAdmin()) {
            abort(403, 'เฉพาะแอดมินระบบ (Admin) เท่านั้นที่สามารถดำเนินการล้างข้อมูลได้');
        }

        // 2. Validate input
        $request->validate([
            'wipe_scope' => 'required|in:operational,full',
            'admin_password' => 'required|string',
            'confirmation_text' => 'required|string',
            'auto_backup' => 'nullable|boolean',
        ]);

        $scope = $request->input('wipe_scope');
        $expectedConfirmation = ($scope === 'operational') ? 'CLEAR-OPERATIONAL' : 'RESET-ALL-DATA';

        // 3. Verify Admin Password
        if (!Hash::check($request->input('admin_password'), $user->password)) {
            return back()->with('error', 'รหัสผ่านผู้ดูแลระบบไม่ถูกต้อง ไม่สามารถดำเนินการล้างข้อมูลได้');
        }

        // 4. Verify Confirmation Text
        if (trim($request->input('confirmation_text')) !== $expectedConfirmation) {
            return back()->with('error', "ข้อความยืนยันไม่ถูกต้อง (ต้องพิมพ์ \"{$expectedConfirmation}\" ตัวพิมพ์ใหญ่ทุกตัว)");
        }

        // 5. Auto Safety Backup
        $backupFilename = null;
        if ($request->boolean('auto_backup', true)) {
            $notes = "สำรองข้อมูลอัตโนมัติก่อนล้างระบบ (" . ($scope === 'operational' ? 'Operational Wipe' : 'Full Factory Reset') . ") โดย " . $user->name;
            $backupFilename = $this->createSafetyBackup($notes);
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Snapshot counts for reporting
            $counts = [
                'repairs' => Repair::count(),
                'repair_parts' => RepairPart::count(),
                'repair_logs' => RepairLog::count(),
                'data_requests' => DataRequest::count(),
                'asset_borrows' => AssetBorrow::count(),
                'asset_transfers' => AssetTransfer::count(),
                'hardware_audits' => HardwareAudit::count(),
                'agent_commands' => AgentCommand::count(),
                'audit_logs' => AuditLog::count(),
                'assets' => Asset::count(),
                'spare_parts' => SparePart::count(),
                'users' => User::count(),
            ];

            if ($scope === 'operational') {
                // -------------------------------------------------------------
                // 1. Wipe Operational Tables across ALL subsystems
                // -------------------------------------------------------------
                RepairPart::query()->delete();
                RepairLog::query()->delete();
                Repair::query()->delete();

                DataRequest::query()->delete();
                AssetBorrow::query()->delete();
                AssetTransfer::query()->delete();

                AgentCommand::query()->delete();
                HardwareAudit::query()->delete();

                // Revert any borrowed or transfer-pending assets back to active
                Asset::whereIn('status', ['borrowed', 'in_repair', 'moving'])->update(['status' => 'active']);
                Asset::whereNotNull('hardware_id')->update(['hardware_id' => null]);

                // Clear operational storage upload directories
                $this->clearStorageDirectories([
                    'repairs',
                    'public/repairs',
                    'uploads/repairs',
                    'public/uploads/repairs',
                    'data_requests',
                    'public/data_requests',
                    'uploads/data_requests',
                    'uploads/data_requests_samples',
                    'asset_transfers',
                    'public/asset_transfers',
                    'uploads/asset_transfers',
                ]);

                // Reset auto-increment on operational tables
                DB::statement('ALTER TABLE it_repair_parts AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repair_logs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repairs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_data_requests AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_asset_borrows AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_asset_transfers AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_agent_commands AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_hardware_audits AUTO_INCREMENT = 1;');

                // Clear audit logs and reset
                AuditLog::query()->delete();
                DB::statement('ALTER TABLE it_audit_logs AUTO_INCREMENT = 1;');

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                // Record Audit Log of this clear operation
                AuditLog::record(
                    'clear_data',
                    'settings',
                    "ผู้ดูแลระบบ ({$user->name}) ล้างข้อมูลการดำเนินงานทั้งหมด: ใบแจ้งซ่อม {$counts['repairs']} งาน, คำขอข้อมูล {$counts['data_requests']} งาน, การยืม-คืน {$counts['asset_borrows']} รายการ, การโอนย้าย {$counts['asset_transfers']} รายการ, สเปคตรวจนับ {$counts['hardware_audits']} เครื่อง, Audit Logs {$counts['audit_logs']} รายการ" . ($backupFilename ? " [สำรองข้อมูล: {$backupFilename}]" : ""),
                    null,
                    null,
                    [
                        'scope' => 'operational',
                        'auto_backup' => $backupFilename,
                        'deleted_counts' => $counts,
                    ]
                );

                $msg = "ล้างข้อมูลการดำเนินงานทุกระบบเรียบร้อยแล้ว (ลบใบแจ้งซ่อม {$counts['repairs']} งาน, ขอข้อมูล {$counts['data_requests']} งาน, ยืม-คืน {$counts['asset_borrows']} รายการ, โอนย้าย {$counts['asset_transfers']} รายการ, สเปคตรวจนับ {$counts['hardware_audits']} เครื่อง โดยรักษาครุภัณฑ์, อะไหล่ และผู้ใช้ไว้ครบถ้วน)";
                if ($backupFilename) {
                    $msg .= " — จุดสำรองข้อมูลฉุกเฉิน: {$backupFilename}";
                }
                return back()->with('success', $msg);

            } elseif ($scope === 'full') {
                // -------------------------------------------------------------
                // 2. Full System Factory Reset
                // -------------------------------------------------------------
                RepairPart::query()->delete();
                RepairLog::query()->delete();
                Repair::query()->delete();

                DataRequest::query()->delete();
                AssetBorrow::query()->delete();
                AssetTransfer::query()->delete();

                AgentCommand::query()->delete();
                HardwareAudit::query()->delete();

                Asset::query()->delete();
                SparePart::query()->delete();

                // Clean all upload and document storage directories
                $this->clearStorageDirectories([
                    'repairs',
                    'public/repairs',
                    'uploads/repairs',
                    'public/uploads/repairs',
                    'data_requests',
                    'public/data_requests',
                    'uploads/data_requests',
                    'uploads/data_requests_samples',
                    'asset_transfers',
                    'public/asset_transfers',
                    'uploads/asset_transfers',
                    'assets',
                    'public/assets',
                    'uploads/assets',
                ]);

                // Preserve the currently logged-in Super Admin!
                $currentAdminId = $user->id;
                User::where('id', '!=', $currentAdminId)->delete();

                // Reset Auto Increments
                DB::statement('ALTER TABLE it_repair_parts AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repair_logs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repairs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_data_requests AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_asset_borrows AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_asset_transfers AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_agent_commands AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_hardware_audits AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_assets AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_spare_parts AUTO_INCREMENT = 1;');

                // Run seeders to restore clean default hospital templates
                Artisan::call('db:seed');

                // Clear audit logs and reset auto increment
                AuditLog::query()->delete();
                DB::statement('ALTER TABLE it_audit_logs AUTO_INCREMENT = 1;');

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                // Record Audit Log of this full reset
                AuditLog::record(
                    'system_reset',
                    'settings',
                    "ผู้ดูแลระบบ ({$user->name}) ทำการรีเซ็ตระบบทั้งหมดเป็นค่าเริ่มต้นโรงพยาบาลทุ่งหัวช้าง (Full Factory Reset)" . ($backupFilename ? " [สำรองข้อมูล: {$backupFilename}]" : ""),
                    null,
                    null,
                    [
                        'scope' => 'full',
                        'auto_backup' => $backupFilename,
                        'preserved_admin_id' => $currentAdminId,
                    ]
                );

                $msg = "รีเซ็ตระบบทั้งหมดเป็นค่าเริ่มต้นมาตรฐานโรงพยาบาลทุ่งหัวช้างเรียบร้อยแล้ว (บัญชีผู้ดูแลระบบ {$user->name} ยังคงเข้าใช้งานได้ตามเดิม)";
                if ($backupFilename) {
                    $msg .= " — จุดสำรองข้อมูลฉุกเฉิน: {$backupFilename}";
                }
                return back()->with('success', $msg);
            }

        } catch (Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'เกิดข้อผิดพลาดในการล้างข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * Scan database integrity and check for broken/orphaned foreign keys.
     */
    public function checkIntegrity(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $issues = $this->scanIntegrityMetrics();

        return response()->json([
            'success' => true,
            'issues' => $issues,
            'total_issues' => array_sum(array_column($issues, 'count')),
            'checked_at' => Carbon::now()->format('H:i:s d/m/Y'),
        ]);
    }

    /**
     * Auto-repair and purge orphaned/broken foreign key references.
     */
    public function repairIntegrity(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'admin_password' => 'required|string',
        ]);

        if (!Hash::check($request->input('admin_password'), $user->password)) {
            return back()->with('error', 'รหัสผ่านผู้ดูแลระบบไม่ถูกต้อง ไม่สามารถดำเนินการซ่อมแซมได้');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $repaired = [
                'repairs_detached_assets' => 0,
                'borrows_detached_assets' => 0,
                'transfers_detached_assets' => 0,
                'audits_detached_assets' => 0,
                'repair_logs_purged' => 0,
                'repair_parts_purged' => 0,
                'borrows_detached_repairs' => 0,
            ];

            // 1. Repairs pointing to non-existent assets -> set asset_id = NULL
            $invalidRepairAssetIds = DB::table('it_repairs')
                ->whereNotNull('asset_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_repairs.asset_id');
                })->pluck('id');
            if ($invalidRepairAssetIds->isNotEmpty()) {
                $repaired['repairs_detached_assets'] = DB::table('it_repairs')->whereIn('id', $invalidRepairAssetIds)->update(['asset_id' => null]);
            }

            // 2. Asset Borrows pointing to non-existent assets -> delete orphan borrows
            $invalidBorrowAssetIds = DB::table('it_asset_borrows')
                ->whereNotNull('asset_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_asset_borrows.asset_id');
                })->pluck('id');
            if ($invalidBorrowAssetIds->isNotEmpty()) {
                $repaired['borrows_detached_assets'] = DB::table('it_asset_borrows')->whereIn('id', $invalidBorrowAssetIds)->delete();
            }

            // 3. Asset Borrows pointing to non-existent repairs -> set repair_id = NULL
            $invalidBorrowRepairIds = DB::table('it_asset_borrows')
                ->whereNotNull('repair_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_repairs')->whereColumn('it_repairs.id', 'it_asset_borrows.repair_id');
                })->pluck('id');
            if ($invalidBorrowRepairIds->isNotEmpty()) {
                $repaired['borrows_detached_repairs'] = DB::table('it_asset_borrows')->whereIn('id', $invalidBorrowRepairIds)->update(['repair_id' => null]);
            }

            // 4. Asset Transfers pointing to non-existent assets -> delete orphan transfers
            $invalidTransferAssetIds = DB::table('it_asset_transfers')
                ->whereNotNull('asset_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_asset_transfers.asset_id');
                })->pluck('id');
            if ($invalidTransferAssetIds->isNotEmpty()) {
                $repaired['transfers_detached_assets'] = DB::table('it_asset_transfers')->whereIn('id', $invalidTransferAssetIds)->delete();
            }

            // 5. Hardware Audits pointing to non-existent assets -> set asset_id = NULL
            $invalidAuditAssetIds = DB::table('it_hardware_audits')
                ->whereNotNull('asset_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_hardware_audits.asset_id');
                })->pluck('id');
            if ($invalidAuditAssetIds->isNotEmpty()) {
                $repaired['audits_detached_assets'] = DB::table('it_hardware_audits')->whereIn('id', $invalidAuditAssetIds)->update(['asset_id' => null]);
            }

            // 6. Repair Logs without existing repairs -> delete
            $orphanLogIds = DB::table('it_repair_logs')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_repairs')->whereColumn('it_repairs.id', 'it_repair_logs.repair_id');
                })->pluck('id');
            if ($orphanLogIds->isNotEmpty()) {
                $repaired['repair_logs_purged'] = DB::table('it_repair_logs')->whereIn('id', $orphanLogIds)->delete();
            }

            // 7. Repair Parts without existing repairs -> delete
            $orphanPartIds = DB::table('it_repair_parts')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_repairs')->whereColumn('it_repairs.id', 'it_repair_parts.repair_id');
                })->pluck('id');
            if ($orphanPartIds->isNotEmpty()) {
                $repaired['repair_parts_purged'] = DB::table('it_repair_parts')->whereIn('id', $orphanPartIds)->delete();
            }

            // 8. Assets pointing to non-existent budget sources -> set null
            $invalidBsAssetIds = DB::table('it_assets')
                ->whereNotNull('budget_source_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_budget_sources')->whereColumn('it_budget_sources.id', 'it_assets.budget_source_id');
                })->pluck('id');
            if ($invalidBsAssetIds->isNotEmpty()) {
                $repaired['assets_detached_budget_sources'] = DB::table('it_assets')->whereIn('id', $invalidBsAssetIds)->update(['budget_source_id' => null]);
            }

            // 9. Assets pointing to non-existent acquisition methods -> set null
            $invalidAmAssetIds = DB::table('it_assets')
                ->whereNotNull('acquisition_method_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))->from('it_acquisition_methods')->whereColumn('it_acquisition_methods.id', 'it_assets.acquisition_method_id');
                })->pluck('id');
            if ($invalidAmAssetIds->isNotEmpty()) {
                $repaired['assets_detached_acquisition_methods'] = DB::table('it_assets')->whereIn('id', $invalidAmAssetIds)->update(['acquisition_method_id' => null]);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $totalFixed = array_sum($repaired);

            AuditLog::record(
                'repair_integrity',
                'settings',
                "ผู้ดูแลระบบ ({$user->name}) ทำการสแกนและซ่อมแซมความสมบูรณ์ของฐานข้อมูล (แก้ไขรายการผิดปกติ {$totalFixed} รายการ)",
                null,
                null,
                $repaired
            );

            return back()->with('success', "ตรวจสอบและซ่อมแซมความสมบูรณ์ของฐานข้อมูลเรียบร้อยแล้ว (แก้ไขและล้างข้อมูลผิดปกติรวม {$totalFixed} รายการ)");

        } catch (Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'เกิดข้อผิดพลาดในการซ่อมแซมข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * Gather comprehensive system metrics for all 12 modules.
     */
    private function gatherSystemMetrics(): array
    {
        $storageUploads = $this->calculateDirectorySize(storage_path('app'));
        $storageBackups = $this->calculateDirectorySize(storage_path('app/backups'));

        return [
            // 1. Repairs & Maintenance
            'repairs' => [
                'name' => 'งานแจ้งซ่อมบำรุง & ประวัติช่าง',
                'module' => 'repairs',
                'icon' => 'bi-tools',
                'color' => '#dc2626',
                'count' => Repair::count(),
                'pending' => Repair::where('status', 'pending')->count(),
                'in_progress' => Repair::whereIn('status', ['in_progress', 'waiting_parts'])->count(),
                'completed' => Repair::where('status', 'completed')->count(),
                'logs_count' => RepairLog::count(),
                'parts_used' => RepairPart::count(),
                'last_activity' => Repair::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 2. Data Requests
            'data_requests' => [
                'name' => 'ขอข้อมูลสารสนเทศ HosXP & สถิติ',
                'module' => 'data_requests',
                'icon' => 'bi-file-earmark-bar-graph',
                'color' => '#0284c7',
                'count' => DataRequest::count(),
                'pending' => DataRequest::where('status', 'pending')->count(),
                'completed' => DataRequest::where('status', 'completed')->count(),
                'last_activity' => DataRequest::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 3. Asset Borrows
            'asset_borrows' => [
                'name' => 'ขอยืม-คืนอุปกรณ์ & เครื่องสำรอง',
                'module' => 'asset_borrows',
                'icon' => 'bi-arrow-left-right',
                'color' => '#0d9488',
                'count' => AssetBorrow::count(),
                'pending' => AssetBorrow::where('status', 'pending')->count(),
                'borrowed' => AssetBorrow::whereIn('status', ['approved', 'borrowed'])->whereNull('actual_return_date')->count(),
                'returned' => AssetBorrow::where('status', 'returned')->count(),
                'last_activity' => AssetBorrow::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 4. Asset Transfers
            'asset_transfers' => [
                'name' => 'โอนย้ายครุภัณฑ์ & ย้ายจุดติดตั้ง',
                'module' => 'asset_transfers',
                'icon' => 'bi-arrows-move',
                'color' => '#7c3aed',
                'count' => AssetTransfer::count(),
                'pending' => AssetTransfer::where('status', 'pending')->count(),
                'completed' => AssetTransfer::where('status', 'completed')->count(),
                'last_activity' => AssetTransfer::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 5. Hardware Audits & Agent Fleet
            'hardware_audits' => [
                'name' => 'สำรวจสเปคคอมฯ & IT Agent Telemetry',
                'module' => 'hardware_audits',
                'icon' => 'bi-cpu-fill',
                'color' => '#f59e0b',
                'count' => HardwareAudit::count(),
                'online' => (function () {
                    $registry = \Illuminate\Support\Facades\Cache::get('agent_online_registry', []);
                    $nowTs = now()->timestamp;
                    $online = is_array($registry) ? count(array_filter($registry, fn($a) => isset($a['expires_at']) && $a['expires_at'] > $nowTs)) : 0;
                    return $online ?: HardwareAudit::where('updated_at', '>=', Carbon::now()->subMinutes(10))->count();
                })(),
                'pending' => HardwareAudit::where('status', 'pending')->count(),
                'approved' => HardwareAudit::where('status', 'approved')->count(),
                'commands_count' => AgentCommand::count(),
                'last_activity' => HardwareAudit::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 6. Asset Inventory
            'assets' => [
                'name' => 'คลังคอมพิวเตอร์ & ครุภัณฑ์ IT',
                'module' => 'assets',
                'icon' => 'bi-laptop',
                'color' => '#10b981',
                'count' => Asset::count(),
                'active' => Asset::where('status', 'active')->count(),
                'in_repair' => Asset::where('status', 'in_repair')->count(),
                'damaged' => Asset::where('status', 'damaged')->count(),
                'borrowed' => Asset::where('status', 'borrowed')->count(),
                'last_activity' => Asset::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 7. Spare Parts Inventory
            'spare_parts' => [
                'name' => 'คลังอะไหล่ & พัสดุสิ้นเปลือง',
                'module' => 'spare_parts',
                'icon' => 'bi-box-seam',
                'color' => '#eab308',
                'count' => SparePart::count(),
                'low_stock' => SparePart::whereColumn('stock_quantity', '<=', 'minimum_quantity')->count(),
                'out_of_stock' => SparePart::where('stock_quantity', '<=', 0)->count(),
                'last_activity' => SparePart::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 8. Audit Logs
            'audit_logs' => [
                'name' => 'บันทึกความปลอดภัย (Audit Logs)',
                'module' => 'audit_logs',
                'icon' => 'bi-shield-check',
                'color' => '#3b82f6',
                'count' => AuditLog::count(),
                'today' => AuditLog::whereDate('created_at', Carbon::today())->count(),
                'critical' => AuditLog::whereIn('action', ['delete', 'restore', 'clear_data', 'system_reset', 'clear_module'])->count(),
                'last_activity' => AuditLog::latest('created_at')->value('created_at')?->diffForHumans() ?? '-',
            ],

            // 9. Database Backups
            'backups' => [
                'name' => 'ไฟล์สำรองฐานข้อมูล (Backups)',
                'module' => 'backups',
                'icon' => 'bi-cloud-arrow-down-fill',
                'color' => '#06b6d4',
                'count' => BackupLog::count(),
                'total_size' => $this->formatBytes(BackupLog::sum('file_size')),
                'last_backup' => BackupLog::latest('created_at')->value('created_at')?->diffForHumans() ?? 'ยังไม่มีการสำรอง',
            ],

            // 10. Users & Roles
            'users' => [
                'name' => 'บัญชีผู้ใช้งาน & สิทธิ์เข้าถึง',
                'module' => 'users',
                'icon' => 'bi-people-fill',
                'color' => '#6366f1',
                'count' => User::count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
                'admin' => User::where('role', 'admin')->count(),
                'user' => User::where('role', 'user')->count(),
                'pending_approval' => User::where('approval_status', 'pending')->count(),
                'last_activity' => User::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 11. Departments & Master Data
            'master_data' => [
                'name' => 'โครงสร้างองค์กร & แม่แบบข้อมูล',
                'module' => 'master_data',
                'icon' => 'bi-diagram-3-fill',
                'color' => '#8b5cf6',
                'departments_count' => Department::count(),
                'device_types_count' => DeviceType::count(),
                'budget_sources_count' => BudgetSource::count(),
                'acquisition_methods_count' => AcquisitionMethod::count(),
                'last_activity' => Department::latest('updated_at')->value('updated_at')?->diffForHumans() ?? '-',
            ],

            // 12. Storage & Disk Usage
            'storage' => [
                'name' => 'พื้นที่จัดเก็บเอกสารและไฟล์ระบบ',
                'module' => 'storage',
                'icon' => 'bi-hdd-stack-fill',
                'color' => '#64748b',
                'uploads_size' => $this->formatBytes($storageUploads),
                'backups_size' => $this->formatBytes($storageBackups),
                'total_size' => $this->formatBytes($storageUploads + $storageBackups),
            ],
        ];
    }

    /**
     * Scan integrity across relationships to detect broken or orphaned records.
     */
    private function scanIntegrityMetrics(): array
    {
        $issues = [];

        // 1. Repairs with deleted/missing asset_id
        $brokenRepairAssets = DB::table('it_repairs')
            ->whereNotNull('asset_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_repairs.asset_id');
            })->count();
        if ($brokenRepairAssets > 0) {
            $issues[] = [
                'type' => 'repairs_missing_asset',
                'title' => 'ใบแจ้งซ่อมที่อ้างอิงครุภัณฑ์ที่ไม่มีอยู่ในระบบ',
                'count' => $brokenRepairAssets,
                'description' => 'พบใบแจ้งซ่อมที่ระบุรหัสครุภัณฑ์แต่ครุภัณฑ์ดังกล่าวถูกลบออกไปแล้ว',
                'severity' => 'medium',
                'fix_action' => 'ปรับฟิลด์ asset_id เป็นค่าว่าง (NULL) โดยคงประวัติการซ่อมไว้',
            ];
        }

        // 2. Asset Borrows with deleted/missing asset_id
        $brokenBorrowAssets = DB::table('it_asset_borrows')
            ->whereNotNull('asset_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_asset_borrows.asset_id');
            })->count();
        if ($brokenBorrowAssets > 0) {
            $issues[] = [
                'type' => 'borrows_missing_asset',
                'title' => 'รายการยืมอุปกรณ์ที่ไม่มีครุภัณฑ์อยู่ในระบบ',
                'count' => $brokenBorrowAssets,
                'description' => 'พบประวัติการยืมที่ผูกกับครุภัณฑ์ที่ไม่มีอยู่ในทะเบียนคลัง',
                'severity' => 'high',
                'fix_action' => 'ล้างประวัติการยืมที่ขาดความสมบูรณ์',
            ];
        }

        // 3. Asset Transfers with deleted/missing asset_id
        $brokenTransferAssets = DB::table('it_asset_transfers')
            ->whereNotNull('asset_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_asset_transfers.asset_id');
            })->count();
        if ($brokenTransferAssets > 0) {
            $issues[] = [
                'type' => 'transfers_missing_asset',
                'title' => 'รายการโอนย้ายที่ไม่มีครุภัณฑ์ในระบบ',
                'count' => $brokenTransferAssets,
                'description' => 'พบประวัติการโอนย้ายที่ผูกกับครุภัณฑ์ที่ไม่มีอยู่ในทะเบียนคลัง',
                'severity' => 'high',
                'fix_action' => 'ล้างประวัติการโอนย้ายที่ขาดความสมบูรณ์',
            ];
        }

        // 4. Hardware Audits with missing asset link
        $brokenAuditAssets = DB::table('it_hardware_audits')
            ->whereNotNull('asset_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_assets')->whereColumn('it_assets.id', 'it_hardware_audits.asset_id');
            })->count();
        if ($brokenAuditAssets > 0) {
            $issues[] = [
                'type' => 'audits_missing_asset',
                'title' => 'สเปคเครื่อง Agent ที่ผูกกับครุภัณฑ์ที่ถูกลบ',
                'count' => $brokenAuditAssets,
                'description' => 'พบผลสำรวจสเปคคอมฯ ที่ผูกกับรหัสครุภัณฑ์ที่ไม่มีอยู่ในระบบแล้ว',
                'severity' => 'low',
                'fix_action' => 'ปลดล็อกการผูกครุภัณฑ์ให้เป็นอิสระ (Unlink)',
            ];
        }

        // 5. Repair Logs without existing repairs
        $orphanLogs = DB::table('it_repair_logs')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_repairs')->whereColumn('it_repairs.id', 'it_repair_logs.repair_id');
            })->count();
        if ($orphanLogs > 0) {
            $issues[] = [
                'type' => 'orphan_repair_logs',
                'title' => 'บันทึกประวัติการซ่อมกำพร้า (ไม่มีใบแจ้งซ่อมหลัก)',
                'count' => $orphanLogs,
                'description' => 'พบบันทึก Timeline การซ่อมที่ไม่สัมพันธ์กับใบแจ้งซ่อมใดๆ',
                'severity' => 'medium',
                'fix_action' => 'ลบบันทึกประวัติกำพร้าออกจากระบบ',
            ];
        }

        // 6. Repair Parts without existing repairs
        $orphanParts = DB::table('it_repair_parts')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_repairs')->whereColumn('it_repairs.id', 'it_repair_parts.repair_id');
            })->count();
        if ($orphanParts > 0) {
            $issues[] = [
                'type' => 'orphan_repair_parts',
                'title' => 'บันทึกการเบิกอะไหล่กำพร้า (ไม่มีใบแจ้งซ่อมหลัก)',
                'count' => $orphanParts,
                'description' => 'พบการเบิกอะไหล่ที่ไม่มีใบแจ้งซ่อมรองรับในระบบ',
                'severity' => 'medium',
                'fix_action' => 'ลบรายการเบิกอะไหล่กำพร้า',
            ];
        }

        // 7. Assets with invalid budget_source_id
        $brokenBudgetAssets = DB::table('it_assets')
            ->whereNotNull('budget_source_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_budget_sources')->whereColumn('it_budget_sources.id', 'it_assets.budget_source_id');
            })->count();
        if ($brokenBudgetAssets > 0) {
            $issues[] = [
                'type' => 'assets_invalid_budget_source',
                'title' => 'ครุภัณฑ์ที่ผูกกับแหล่งเงินที่ถูกลบ',
                'count' => $brokenBudgetAssets,
                'description' => 'พบครุภัณฑ์ที่ผูกกับรหัสแหล่งเงินงบประมาณที่ไม่มีอยู่ในระบบแล้ว',
                'severity' => 'low',
                'fix_action' => 'ปลดล็อกรหัสแหล่งเงินเป็นค่าว่าง (Set NULL)',
            ];
        }

        // 8. Assets with invalid acquisition_method_id
        $brokenAcqAssets = DB::table('it_assets')
            ->whereNotNull('acquisition_method_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))->from('it_acquisition_methods')->whereColumn('it_acquisition_methods.id', 'it_assets.acquisition_method_id');
            })->count();
        if ($brokenAcqAssets > 0) {
            $issues[] = [
                'type' => 'assets_invalid_acquisition_method',
                'title' => 'ครุภัณฑ์ที่ผูกกับวิธีการได้มาที่ถูกลบ',
                'count' => $brokenAcqAssets,
                'description' => 'พบครุภัณฑ์ที่ผูกกับวิธีการได้มาที่ไม่มีอยู่ในระบบแล้ว',
                'severity' => 'low',
                'fix_action' => 'ปลดล็อกวิธีการได้มาเป็นค่าว่าง (Set NULL)',
            ];
        }

        return $issues;
    }

    /**
     * Helper to safely execute a pre-wipe database backup.
     */
    private function createSafetyBackup(string $notes): ?string
    {
        try {
            $backupController = app(BackupController::class);
            $backup = $backupController->executeBackup('it_tables', $notes, 'pre_reset');
            return $backup->filename;
        } catch (Exception $e) {
            \Log::warning("Auto-backup before wipe failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Purge temporary files from storage.
     */
    private function purgeTemporaryStorageFiles(): int
    {
        $freedBytes = 0;
        $tempDirs = [
            storage_path('app/temp'),
            storage_path('app/exports'),
            storage_path('framework/cache/data'),
            storage_path('framework/views'),
        ];

        foreach ($tempDirs as $dir) {
            if (File::exists($dir)) {
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    if ($file->getFilename() === '.gitignore') continue;
                    $freedBytes += $file->getSize();
                    @File::delete($file->getPathname());
                }
            }
        }

        return $freedBytes;
    }

    /**
     * Clear specific directories inside storage.
     */
    private function clearStorageDirectories(array $directories): void
    {
        foreach ($directories as $dir) {
            $candidates = [
                storage_path('app/' . $dir),
                public_path($dir),
                base_path($dir),
            ];

            foreach ($candidates as $path) {
                if (File::exists($path) && File::isDirectory($path)) {
                    $files = File::allFiles($path);
                    foreach ($files as $f) {
                        if ($f->getFilename() === '.gitignore' || $f->getFilename() === '.gitkeep') continue;
                        @File::delete($f->getPathname());
                    }
                }
            }
        }
    }

    /**
     * Calculate directory size recursively.
     */
    private function calculateDirectorySize(string $path): int
    {
        $size = 0;
        if (!File::exists($path)) return 0;

        try {
            foreach (File::allFiles($path) as $file) {
                $size += $file->getSize();
            }
        } catch (Exception $e) {
            // ignore
        }
        return $size;
    }

    /**
     * Format bytes into human-readable string.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 0) {
            return $bytes . ' B';
        }
        return '0 B';
    }
}
