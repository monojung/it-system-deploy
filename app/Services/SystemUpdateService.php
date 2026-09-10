<?php

namespace App\Services;

use App\Http\Controllers\BackupController;
use App\Models\AuditLog;
use App\Models\SystemUpdate;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SystemUpdateService
{
    /**
     * Check if git is available and get repository update status
     */
    public function checkRemoteUpdates(): array
    {
        $basePath = base_path();

        // 1. Verify if .git directory exists
        if (!File::isDirectory($basePath . DIRECTORY_SEPARATOR . '.git')) {
            return [
                'success' => false,
                'is_git_repo' => false,
                'has_update' => false,
                'message' => 'ไม่พบโฟลเดอร์ .git ในโปรเจกต์ (ไม่ได้ติดตั้งผ่าน Git repository)',
                'last_checked_at' => now()->toDateTimeString(),
            ];
        }

        try {
            // Get current branch
            $currentBranch = trim($this->runProcess(['git', 'rev-parse', '--abbrev-ref', 'HEAD']));
            if (empty($currentBranch) || $currentBranch === 'HEAD') {
                $currentBranch = 'main';
            }

            // Get local commit
            $localCommit = trim($this->runProcess(['git', 'rev-parse', 'HEAD']));
            $shortLocalCommit = substr($localCommit, 0, 7);

            // Fetch remote changes (with 30s timeout)
            $fetchOutput = $this->runProcess(['git', 'fetch', 'origin', $currentBranch], 30);

            // Get tracking branch or default to origin/{branch}
            $remoteRef = "origin/{$currentBranch}";
            $remoteCommit = '';
            try {
                $remoteCommit = trim($this->runProcess(['git', 'rev-parse', $remoteRef]));
            } catch (Exception $e) {
                // Fallback to origin/main
                $remoteCommit = trim($this->runProcess(['git', 'rev-parse', 'origin/main']));
                $remoteRef = 'origin/main';
            }

            $shortRemoteCommit = substr($remoteCommit, 0, 7);

            // Count commits behind
            $behindCountOutput = trim($this->runProcess(['git', 'rev-list', '--count', "HEAD..{$remoteRef}"]));
            $commitsBehind = is_numeric($behindCountOutput) ? (int) $behindCountOutput : 0;

            // Get changelog list of new commits
            $commits = [];
            if ($commitsBehind > 0) {
                $logOutput = $this->runProcess([
                    'git', 'log', "HEAD..{$remoteRef}",
                    '--pretty=format:%h||%an||%ad||%s',
                    '--date=short'
                ]);

                $lines = array_filter(explode("\n", trim($logOutput)));
                foreach ($lines as $line) {
                    $parts = explode('||', $line);
                    if (count($parts) >= 4) {
                        $commits[] = [
                            'hash' => trim($parts[0]),
                            'author' => trim($parts[1]),
                            'date' => trim($parts[2]),
                            'message' => trim($parts[3]),
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'is_git_repo' => true,
                'branch' => $currentBranch,
                'local_commit' => $localCommit,
                'short_local_commit' => $shortLocalCommit,
                'remote_commit' => $remoteCommit,
                'short_remote_commit' => $shortRemoteCommit,
                'commits_behind' => $commitsBehind,
                'has_update' => $commitsBehind > 0,
                'commits' => $commits,
                'current_version' => function_exists('app_version') ? app_version() : config('version.version', '2.2.1'),
                'last_checked_at' => now()->toDateTimeString(),
            ];

        } catch (Exception $e) {
            Log::warning('SystemUpdateService::checkRemoteUpdates failed: ' . $e->getMessage());

            // Provide partial info even if remote fetch failed (e.g. offline)
            $localCommit = '';
            try {
                $localCommit = trim($this->runProcess(['git', 'rev-parse', 'HEAD']));
            } catch (Exception $ex) {
                // ignore
            }

            $rawError = $e->getMessage();
            $errorType = 'เครือข่ายหรือสิทธิ์การเข้าถึง';
            $hint = 'กรุณาตรวจสอบการเชื่อมต่อเครือข่ายของเซิร์ฟเวอร์';

            if (str_contains($rawError, 'Could not resolve host')) {
                $errorType = 'ปัญหา DNS หรือเซิร์ฟเวอร์ไม่สามารถออกอินเทอร์เน็ตได้';
                $hint = 'เซิร์ฟเวอร์ไม่สามารถแปลงชื่อ github.com ได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต, DNS หรือการตั้งค่า Proxy ของโรงพยาบาล';
            } elseif (str_contains($rawError, 'Connection timed out') || str_contains($rawError, 'timed out')) {
                $errorType = 'หมดเวลาการเชื่อมต่อ (Connection Timeout)';
                $hint = 'การเชื่อมต่อไปยัง GitHub ใช้เวลานานเกินกำหนด อาจเกิดจาก Firewall หรือเน็ตช้า';
            } elseif (str_contains($rawError, 'Permission denied') || str_contains($rawError, 'Authentication failed')) {
                $errorType = 'สิทธิ์การเข้าถึง Git Repository ไม่ถูกต้อง';
                $hint = 'กรุณาตรวจสอบสิทธิ์ของ SSH Key หรือ Personal Access Token (PAT) สำหรับเข้าถึง Repository';
            }

            return [
                'success' => false,
                'is_git_repo' => true,
                'has_update' => false,
                'local_commit' => $localCommit,
                'short_local_commit' => substr($localCommit, 0, 7),
                'error' => $rawError,
                'error_type' => $errorType,
                'hint' => $hint,
                'message' => "ไม่สามารถเชื่อมต่อกับ Git Remote ได้ ({$errorType}): {$hint}",
                'raw_error' => $rawError,
                'last_checked_at' => now()->toDateTimeString(),
            ];
        }
    }

    /**
     * Execute full update pipeline
     *
     * @param int|null $userId User ID triggering the update
     * @param callable|null $logCallback function(int $step, int $totalSteps, string $title, string $details)
     * @return SystemUpdate
     */
    public function executeUpdate(?int $userId = null, ?callable $logCallback = null): SystemUpdate
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '256M');

        $startTime = microtime(true);
        $totalSteps = 6;
        $fullOutputLogs = [];

        $appendLog = function (string $text) use (&$fullOutputLogs) {
            $timestamp = date('Y-m-d H:i:s');
            $fullOutputLogs[] = "[{$timestamp}] {$text}";
        };

        // 1. Initial State Recording
        $previousCommit = '';
        try {
            $previousCommit = trim($this->runProcess(['git', 'rev-parse', 'HEAD'], 5));
        } catch (Exception $e) {
            $previousCommit = 'unknown';
        }

        $currentVersion = function_exists('app_version') ? app_version() : config('version.version', '2.2.2');

        $updateRecord = SystemUpdate::create([
            'version' => $currentVersion,
            'previous_commit' => $previousCommit,
            'status' => 'running',
            'triggered_by' => $userId,
            'started_at' => now(),
            'output_log' => "เริ่มกระบวนการอัปเดตระบบ...\n",
        ]);

        $notify = function (int $step, string $title, string $details = '') use ($logCallback, $totalSteps, $appendLog) {
            $appendLog("Step {$step}/{$totalSteps}: {$title}" . ($details ? " - {$details}" : ''));
            if (is_callable($logCallback)) {
                $logCallback($step, $totalSteps, $title, $details);
            }
        };

        try {
            // Check immediately if .git exists
            if (!File::isDirectory(base_path('.git'))) {
                throw new Exception('เซิร์ฟเวอร์นี้ไม่ได้ติดตั้งผ่าน Git Repository (ไม่พบโฟลเดอร์ .git) กรุณาใช้วิธี "อัปเดตด้วยไฟล์แพตช์ ZIP (Manual Patch Upload)" ในหน้าจอแทน');
            }

            // STEP 1: Pre-flight checks & Maintenance Mode
            $notify(1, 'เปิดโหมดปรับปรุงระบบ (Maintenance Mode)', 'กำลังเปิด php artisan down เพื่อความปลอดภัยของผู้ใช้');
            try {
                Artisan::call('down', [
                    '--refresh' => 15,
                ]);
                $appendLog("Maintenance mode activated: " . Artisan::output());
            } catch (Exception $e) {
                $appendLog("Warning on artisan down: " . $e->getMessage());
            }

            // STEP 2: Automatic Database Backup
            $notify(2, 'สำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)', 'กำลังสำรองโครงสร้างและข้อมูลตาราง it_*');
            $backupFile = null;
            try {
                $backupController = app(BackupController::class);
                $backupLog = $backupController->executeBackup('it_tables', 'Auto-backup ก่อนอัปเดตระบบอัตโนมัติ', 'pre_update');
                $backupFile = $backupLog->filename;
                $updateRecord->update(['backup_file' => $backupFile]);
                $appendLog("Database auto-backup created successfully: {$backupFile} (" . number_format($backupLog->file_size) . " bytes)");
            } catch (Exception $e) {
                $appendLog("Warning on database backup: " . $e->getMessage() . " (ดำเนินการต่อ)");
            }

            // STEP 3: Git Pull / Fetch & Reset to latest
            $notify(3, 'ดึงโค้ดล่าสุดจาก Git Repository', 'กำลังดำเนินการ git pull origin main');
            $gitOutput = '';
            try {
                // Get current branch
                $branch = trim($this->runProcess(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], 10));
                if (empty($branch) || $branch === 'HEAD') {
                    $branch = 'main';
                }

                // First fetch with 10s timeout
                $this->runProcess(['git', 'fetch', 'origin', $branch], 10);

                // Then pull with 15s timeout
                $gitOutput = $this->runProcess(['git', 'pull', 'origin', $branch], 15);
                $appendLog("Git output:\n" . $gitOutput);
            } catch (Exception $e) {
                throw new Exception("ไม่สามารถดึงโค้ดจาก Git ได้: " . $e->getMessage());
            }

            // STEP 4: Database Migration
            $notify(4, 'ปรับปรุงโครงสร้างฐานข้อมูล (Database Migration)', 'กำลังรัน php artisan migrate --force');
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = Artisan::output();
                $appendLog("Migration output:\n" . ($migrateOutput ?: "ไม่มีตารางที่ต้อง Migrate เพิ่มเติม\n"));
            } catch (Exception $e) {
                throw new Exception("เกิดข้อผิดพลาดในการรัน Database Migration: " . $e->getMessage());
            }

            // STEP 5: Clear and Rebuild Cache
            $notify(5, 'ล้างและสร้างแคชระบบใหม่ (Optimize & Cache)', 'กำลังรัน php artisan optimize:clear');
            try {
                Artisan::call('optimize:clear');
                $appendLog("Optimize clear output:\n" . Artisan::output());

                // Optional: cache config in production
                if (config('app.env') === 'production') {
                    Artisan::call('config:cache');
                    Artisan::call('route:cache');
                    Artisan::call('view:cache');
                    $appendLog("Application caches warmed up.");
                }
            } catch (Exception $e) {
                $appendLog("Warning on optimize: " . $e->getMessage());
            }

            // STEP 6: Bring System Up & Finalize
            $notify(6, 'เปิดระบบพร้อมใช้งาน (Bring Application Up)', 'กำลังปิดโหมดบำรุงรักษาและบันทึกประวัติ');
            try {
                Artisan::call('up');
                $appendLog("System is now ONLINE.");
            } catch (Exception $e) {
                $appendLog("Warning on artisan up: " . $e->getMessage());
            }

            // Capture new commit
            $newCommit = '';
            try {
                $newCommit = trim($this->runProcess(['git', 'rev-parse', 'HEAD']));
            } catch (Exception $e) {
                $newCommit = $previousCommit;
            }

            $duration = (int) round(microtime(true) - $startTime);
            $newVersion = function_exists('app_version') ? app_version() : config('version.version', '2.2.1');

            $updateRecord->update([
                'version' => $newVersion,
                'commit_hash' => $newCommit,
                'status' => 'success',
                'output_log' => implode("\n", $fullOutputLogs),
                'duration_seconds' => $duration,
                'completed_at' => now(),
            ]);

            // Record Audit Log
            AuditLog::record(
                'system_update',
                "อัปเดตระบบสำเร็จเป็นเวอร์ชัน {$newVersion} (Commit: " . substr($newCommit, 0, 7) . ") ใช้เวลา {$duration} วินาที",
                $updateRecord
            );

            return $updateRecord;

        } catch (Exception $e) {
            // ALWAYS ensure system is brought back UP on failure
            try {
                Artisan::call('up');
                $appendLog("Recovery: System brought back online after failure.");
            } catch (Exception $recoveryEx) {
                // log recovery fail
                Log::error('Recovery artisan up failed: ' . $recoveryEx->getMessage());
            }

            $duration = (int) round(microtime(true) - $startTime);
            $appendLog("ERROR: " . $e->getMessage());

            $updateRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'output_log' => implode("\n", $fullOutputLogs),
                'duration_seconds' => $duration,
                'completed_at' => now(),
            ]);

            AuditLog::record(
                'system_update_failed',
                "การอัปเดตระบบล้มเหลว: {$e->getMessage()}",
                $updateRecord
            );

            throw $e;
        }
    }

    /**
     * Apply a patch ZIP file directly into the application
     *
     * @param string $zipPath Path to uploaded patch zip
     * @param int|null $userId
     * @param callable|null $logCallback
     * @return SystemUpdate
     */
    public function applyPatchZip(string $zipPath, ?int $userId = null, ?callable $logCallback = null): SystemUpdate
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');
        @ini_set('memory_limit', '256M');

        $startTime = microtime(true);
        $totalSteps = 6;
        $fullOutputLogs = [];

        $appendLog = function (string $text) use (&$fullOutputLogs) {
            $timestamp = date('Y-m-d H:i:s');
            $fullOutputLogs[] = "[{$timestamp}] {$text}";
        };

        $currentVersion = function_exists('app_version') ? app_version() : config('version.version', '2.2.2');

        $updateRecord = SystemUpdate::create([
            'version' => $currentVersion,
            'previous_commit' => 'patch_upload',
            'status' => 'running',
            'triggered_by' => $userId,
            'started_at' => now(),
            'output_log' => "เริ่มกระบวนการติดตั้งไฟล์แพตช์อัปเดต (Patch ZIP)...\n",
        ]);

        $notify = function (int $step, string $title, string $details = '') use ($logCallback, $totalSteps, $appendLog) {
            $appendLog("Step {$step}/{$totalSteps}: {$title}" . ($details ? " - {$details}" : ''));
            if (is_callable($logCallback)) {
                $logCallback($step, $totalSteps, $title, $details);
            }
        };

        try {
            // STEP 1: Pre-flight checks & Maintenance Mode
            $notify(1, 'เปิดโหมดปรับปรุงระบบ (Maintenance Mode)', 'กำลังเปิด php artisan down ชั่วคราว');
            try {
                Artisan::call('down', ['--refresh' => 15]);
                $appendLog("Maintenance mode activated: " . Artisan::output());
            } catch (Exception $e) {
                $appendLog("Warning on artisan down: " . $e->getMessage());
            }

            // STEP 2: Automatic Database Backup
            $notify(2, 'สำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)', 'กำลังสำรองข้อมูลก่อนเริ่มติดตั้งแพตช์');
            $backupFile = null;
            try {
                $backupController = app(BackupController::class);
                $backupLog = $backupController->executeBackup('it_tables', 'Auto-backup ก่อนติดตั้ง Patch ZIP', 'pre_update');
                $backupFile = $backupLog->filename;
                $updateRecord->update(['backup_file' => $backupFile]);
                $appendLog("Database auto-backup created: {$backupFile}");
            } catch (Exception $e) {
                $appendLog("Warning on database backup: " . $e->getMessage() . " (ดำเนินการต่อ)");
            }

            // STEP 3: Extract Patch ZIP over base_path
            $notify(3, 'แตกไฟล์แพตช์ลงในโปรเจกต์', 'กำลังคลายไฟล์จาก Patch ZIP');
            if (!class_exists('\ZipArchive')) {
                throw new Exception('ไม่พบโมดูล ZipArchive ใน PHP ของเซิร์ฟเวอร์ กรุณาเปิดใช้งาน extension=zip');
            }

            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if ($res !== true) {
                throw new Exception("ไม่สามารถเปิดไฟล์ ZIP ได้ (Code: {$res})");
            }

            $basePath = base_path();
            // List of sensitive files/paths that must NOT be overwritten by patch
            $protectedFiles = ['.env', 'storage', 'database/database.sqlite'];
            $extractedCount = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $filename = $stat['name'];

                // Skip protected files
                $skip = false;
                foreach ($protectedFiles as $protected) {
                    if (str_starts_with($filename, $protected) || $filename === $protected) {
                        $appendLog("Skipped protected file: {$filename}");
                        $skip = true;
                        break;
                    }
                }

                if ($skip) {
                    continue;
                }

                if ($zip->extractTo($basePath, $filename)) {
                    $extractedCount++;
                }
            }
            $zip->close();
            $appendLog("Successfully extracted {$extractedCount} files from patch.");

            // STEP 4: Database Migration
            $notify(4, 'ปรับปรุงโครงสร้างฐานข้อมูล (Database Migration)', 'กำลังรัน php artisan migrate --force');
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = Artisan::output();
                $appendLog("Migration output:\n" . ($migrateOutput ?: "ไม่มีตารางที่ต้อง Migrate เพิ่มเติม\n"));
            } catch (Exception $e) {
                $appendLog("Migration note: " . $e->getMessage());
            }

            // STEP 5: Clear and Rebuild Cache
            $notify(5, 'ล้างและสร้างแคชระบบใหม่ (Optimize & Cache)', 'กำลังรัน php artisan optimize:clear');
            try {
                Artisan::call('optimize:clear');
                $appendLog("Optimize clear output:\n" . Artisan::output());
            } catch (Exception $e) {
                $appendLog("Warning on optimize:clear: " . $e->getMessage());
            }

            // STEP 6: Deactivate Maintenance Mode
            $notify(6, 'เปิดระบบกลับมาให้บริการตามปกติ (Bring Online)', 'กำลังปิด php artisan up');
            try {
                Artisan::call('up');
                $appendLog("Maintenance mode deactivated: System is back ONLINE.");
            } catch (Exception $e) {
                $appendLog("Warning on artisan up: " . $e->getMessage());
            }

            $duration = (int) round(microtime(true) - $startTime);
            $newVersion = function_exists('app_version') ? app_version() : config('version.version', '2.2.2');

            $updateRecord->update([
                'version' => $newVersion,
                'commit_hash' => 'patch-' . date('YmdHis'),
                'status' => 'success',
                'output_log' => implode("\n", $fullOutputLogs),
                'duration_seconds' => $duration,
                'completed_at' => now(),
            ]);

            AuditLog::record(
                'patch_update',
                'system_update',
                "ติดตั้งไฟล์แพตช์ระบบสำเร็จเป็นเวอร์ชัน {$newVersion} ใช้เวลา {$duration} วินาที",
                $updateRecord
            );

            return $updateRecord;

        } catch (Exception $e) {
            try {
                Artisan::call('up');
                $appendLog("Recovery: System brought back online after failure.");
            } catch (Exception $recoveryEx) {
                Log::error('Recovery artisan up failed: ' . $recoveryEx->getMessage());
            }

            $duration = (int) round(microtime(true) - $startTime);
            $appendLog("ERROR: " . $e->getMessage());

            $updateRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'output_log' => implode("\n", $fullOutputLogs),
                'duration_seconds' => $duration,
                'completed_at' => now(),
            ]);

            AuditLog::record(
                'patch_update_failed',
                'system_update',
                "การติดตั้งไฟล์แพตช์ระบบล้มเหลว: {$e->getMessage()}",
                $updateRecord
            );

            throw $e;
        }
    }

    /**
     * Helper to run Symfony Process with timeout and error checking
     */
    protected function runProcess(array $command, int $timeout = 30): string
    {
        $process = new Process($command, base_path());
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput() ?: $process->getOutput();
            throw new Exception("Command [" . implode(' ', $command) . "] failed: " . trim($errorOutput));
        }

        return $process->getOutput();
    }
}
