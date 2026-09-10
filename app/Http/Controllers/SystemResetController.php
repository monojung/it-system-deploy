<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Repair;
use App\Models\RepairLog;
use App\Models\RepairPart;
use App\Models\DataRequest;
use App\Models\AuditLog;
use App\Models\Asset;
use App\Models\SparePart;
use App\Models\BackupLog;
use Exception;

class SystemResetController extends Controller
{
    /**
     * Clear data according to scope (operational or full).
     */
    public function clearData(Request $request)
    {
        $user = Auth::user();

        // 1. Authorization check
        if (!$user || !$user->isAdmin()) {
            abort(403, 'เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถดำเนินการล้างข้อมูลได้');
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
        $autoBackupSuccess = false;
        $backupFilename = null;
        if ($request->boolean('auto_backup', true)) {
            try {
                $backupController = app(BackupController::class);
                $notes = "สำรองข้อมูลอัตโนมัติก่อนล้างระบบ (" . ($scope === 'operational' ? 'Operational Wipe' : 'Full Factory Reset') . ") โดย " . $user->name;
                $backup = $backupController->executeBackup('it_tables', $notes, 'pre_reset');
                $autoBackupSuccess = true;
                $backupFilename = $backup->filename;
            } catch (Exception $e) {
                \Log::warning("Auto-backup before clear failed: " . $e->getMessage());
            }
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $deletedRepairsCount = Repair::count();
            $deletedRequestsCount = DataRequest::count();
            $deletedAuditLogsCount = AuditLog::count();
            $deletedAssetsCount = 0;
            $deletedPartsCount = 0;

            if ($scope === 'operational') {
                // Clear operational tables
                RepairPart::query()->delete();
                RepairLog::query()->delete();
                Repair::query()->delete();
                DataRequest::query()->delete();

                // Delete uploaded files in storage
                $this->clearStorageDirectories(['repairs', 'data_requests', 'public/repairs', 'public/data_requests']);

                // Reset auto increment
                DB::statement('ALTER TABLE it_repair_parts AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repair_logs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repairs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_data_requests AUTO_INCREMENT = 1;');

                // Clear audit logs
                AuditLog::query()->delete();
                DB::statement('ALTER TABLE it_audit_logs AUTO_INCREMENT = 1;');

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');

                // Record Audit Log of this clear operation
                AuditLog::record(
                    'clear_data',
                    'settings',
                    "ผู้ดูแลระบบ ({$user->name}) ล้างข้อมูลการดำเนินงานทั้งหมด: ลบใบแจ้งซ่อม {$deletedRepairsCount} รายการ, คำขอข้อมูล {$deletedRequestsCount} รายการ, บันทึก Audit Log {$deletedAuditLogsCount} รายการ" . ($backupFilename ? " [สำรองข้อมูลก่อนล้าง: {$backupFilename}]" : ""),
                    null,
                    null,
                    [
                        'scope' => 'operational',
                        'auto_backup' => $backupFilename,
                        'repairs_deleted' => $deletedRepairsCount,
                        'data_requests_deleted' => $deletedRequestsCount,
                    ]
                );

                $msg = "ล้างข้อมูลการดำเนินงานทั้งหมดเรียบร้อยแล้ว (ลบใบแจ้งซ่อม {$deletedRepairsCount} รายการ, คำขอข้อมูล {$deletedRequestsCount} รายการ)";
                if ($backupFilename) {
                    $msg .= " — ระบบได้สร้างจุดสำรองข้อมูลฉุกเฉินไว้ที่ {$backupFilename}";
                }
                return back()->with('success', $msg);

            } elseif ($scope === 'full') {
                // Clear operational data
                RepairPart::query()->delete();
                RepairLog::query()->delete();
                Repair::query()->delete();
                DataRequest::query()->delete();

                // Clear assets & spare parts
                $deletedAssetsCount = Asset::count();
                $deletedPartsCount = SparePart::count();
                Asset::query()->delete();
                SparePart::query()->delete();

                // Clean files
                $this->clearStorageDirectories(['repairs', 'data_requests', 'public/repairs', 'public/data_requests', 'assets', 'public/assets']);

                // Preserve current logged in admin user!
                $currentAdminId = $user->id;
                User::where('id', '!=', $currentAdminId)->delete();

                // Reset Auto Increment
                DB::statement('ALTER TABLE it_repair_parts AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repair_logs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_repairs AUTO_INCREMENT = 1;');
                DB::statement('ALTER TABLE it_data_requests AUTO_INCREMENT = 1;');
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
                    "ผู้ดูแลระบบ ({$user->name}) ทำการรีเซ็ตระบบทั้งหมดเป็นค่าเริ่มต้น (Factory Reset)" . ($backupFilename ? " [สำรองข้อมูลก่อนล้าง: {$backupFilename}]" : ""),
                    null,
                    null,
                    [
                        'scope' => 'full',
                        'auto_backup' => $backupFilename,
                    ]
                );

                $msg = "รีเซ็ตระบบทั้งหมดเป็นค่าเริ่มต้นโรงพยาบาลทุ่งหัวช้างเรียบร้อยแล้ว (บัญชีของคุณยังคงใช้งานได้ตามปกติ)";
                if ($backupFilename) {
                    $msg .= " — ระบบได้สร้างจุดสำรองข้อมูลฉุกเฉินไว้ที่ {$backupFilename}";
                }
                return back()->with('success', $msg);
            }

        } catch (Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'เกิดข้อผิดพลาดในการล้างข้อมูล: ' . $e->getMessage());
        }
    }

    private function clearStorageDirectories(array $directories): void
    {
        foreach ($directories as $dir) {
            $path = storage_path('app/' . $dir);
            if (File::exists($path)) {
                File::cleanDirectory($path);
            }
        }
    }
}
