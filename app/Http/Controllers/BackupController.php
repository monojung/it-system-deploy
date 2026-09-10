<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Models\BackupLog;
use App\Models\AuditLog;
use Carbon\Carbon;
use PDO;
use Exception;

class BackupController extends Controller
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }

        $backups = BackupLog::with('creator')->latest()->paginate($perPage)->withQueryString();
        $allBackups = BackupLog::latest()->get(['id', 'filename', 'file_size', 'created_at', 'type']);

        // Calculate total backup storage
        $totalStorageBytes = 0;
        if (File::exists($this->backupDir)) {
            foreach (File::files($this->backupDir) as $file) {
                $totalStorageBytes += $file->getSize();
            }
        }

        $formattedStorage = $this->formatBytes($totalStorageBytes);

        // Auto-backup status and orphan files detection
        $autoBackupEnabled = (bool) setting('backup_auto_enabled', false);
        $allDbFilenames = BackupLog::pluck('filename')->toArray();
        $orphanCount = 0;
        $orphanSizeBytes = 0;
        if (File::exists($this->backupDir)) {
            foreach (File::files($this->backupDir) as $file) {
                if (!in_array($file->getFilename(), $allDbFilenames)) {
                    $orphanCount++;
                    $orphanSizeBytes += $file->getSize();
                }
            }
        }
        $orphanSizeFormatted = $this->formatBytes($orphanSizeBytes);

        // Database connection info
        $dbHost = config('database.connections.mysql.host');
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');

        return view('backups.index', compact('backups', 'allBackups', 'formattedStorage', 'dbHost', 'dbName', 'dbUser', 'autoBackupEnabled', 'orphanCount', 'orphanSizeFormatted'));
    }

    public function create(Request $request)
    {
        $scope = $request->input('scope', 'it_tables'); // 'it_tables' or 'full_db'
        $notes = $request->input('notes', 'สำรองข้อมูลผ่านระบบเว็บ') . ($scope === 'it_tables' ? ' (เฉพาะตาราง it_*)' : ' (ฐานข้อมูลทั้งหมด)');

        try {
            $backup = $this->executeBackup($scope, $notes, 'manual');
            return redirect()->route('backups.index')->with('success', "สำรองข้อมูลสำเร็จ! ไฟล์: {$backup->filename} (ขนาด {$this->formatBytes($backup->file_size)})");
        } catch (Exception $e) {
            return redirect()->route('backups.index')->with('error', 'เกิดข้อผิดพลาดในการสำรองข้อมูล: ' . $e->getMessage());
        }
    }

    public function executeBackup(string $scope = 'it_tables', string $notes = 'สำรองข้อมูลอัตโนมัติ', string $type = 'manual'): BackupLog
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "backup_{$scope}_{$timestamp}.sql";
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;

        // Perform backup
        $success = $this->performDump($filePath, $scope);

        if ($success && File::exists($filePath)) {
            $fileSize = File::size($filePath);

            $backup = BackupLog::create([
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'type' => $type,
                'status' => 'completed',
                'created_by' => Auth::id(),
                'notes' => $notes,
            ]);

            AuditLog::record('backup', 'backups', "สำรองฐานข้อมูล ({$scope}): {$filename} ขนาด {$this->formatBytes($fileSize)}", $backup);

            return $backup;
        } else {
            throw new Exception("ไม่สามารถสร้างไฟล์สำรองข้อมูลได้");
        }
    }

    public function download(BackupLog $backup)
    {
        if (!File::exists($backup->file_path)) {
            return back()->with('error', 'ไม่พบไฟล์สำรองข้อมูลในเซิร์ฟเวอร์');
        }

        AuditLog::record('download', 'backups', "ดาวน์โหลดไฟล์สำรองฐานข้อมูล: {$backup->filename}", $backup);

        return response()->download($backup->file_path, $backup->filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_id' => 'nullable|exists:it_backups,id',
            'backup_file' => 'nullable|file|max:102400', // max 100MB
            'confirm_text' => 'required|in:RESTORE',
        ], [
            'confirm_text.in' => 'กรุณาพิมพ์คำว่า RESTORE เพื่อยืนยันการกู้คืนฐานข้อมูล',
        ]);

        if ($request->hasFile('backup_file')) {
            $ext = strtolower($request->file('backup_file')->getClientOriginalExtension());
            if (!in_array($ext, ['sql', 'txt'])) {
                return back()->with('error', 'ไฟล์ที่อัปโหลดต้องเป็นไฟล์นามสกุล .sql หรือ .txt เท่านั้น');
            }
        }

        try {
            $restoreFilePath = null;

            if ($request->hasFile('backup_file')) {
                $uploadedFile = $request->file('backup_file');
                $tempFilename = 'upload_restore_' . time() . '.sql';
                $uploadedFile->move($this->backupDir, $tempFilename);
                $restoreFilePath = $this->backupDir . DIRECTORY_SEPARATOR . $tempFilename;
            } elseif ($request->filled('backup_id')) {
                $backup = BackupLog::findOrFail($request->backup_id);
                $restoreFilePath = $backup->file_path;
            }

            if (!$restoreFilePath || !File::exists($restoreFilePath)) {
                return back()->with('error', 'ไม่พบไฟล์สำหรับการกู้คืน');
            }

            // Auto-backup before restore only if enabled in system settings or explicitly requested
            $shouldAutoBackup = (bool) setting('backup_auto_enabled', false) || $request->boolean('auto_backup', false);
            $preRestoreFilename = null;

            if ($shouldAutoBackup) {
                $preRestoreFilename = 'pre_restore_auto_' . Carbon::now()->format('Y-m-d_His') . '.sql';
                $preRestorePath = $this->backupDir . DIRECTORY_SEPARATOR . $preRestoreFilename;
                $this->performDump($preRestorePath, 'it_tables');

                if (File::exists($preRestorePath)) {
                    BackupLog::create([
                        'filename' => $preRestoreFilename,
                        'file_path' => $preRestorePath,
                        'file_size' => File::size($preRestorePath),
                        'type' => 'pre_restore',
                        'status' => 'completed',
                        'created_by' => Auth::id(),
                        'notes' => 'สำรองข้อมูลอัตโนมัติก่อนทำ Restore',
                    ]);
                }
            }

            // Execute SQL Restore
            $this->performRestore($restoreFilePath);

            AuditLog::record('restore', 'backups', "กู้คืนฐานข้อมูลจากไฟล์: " . basename($restoreFilePath), null, null, [
                'restored_file' => basename($restoreFilePath),
                'auto_backup' => $preRestoreFilename ?? null,
            ]);

            $successMsg = 'กู้คืนฐานข้อมูลสำเร็จเรียบร้อยแล้ว';
            if ($preRestoreFilename) {
                $successMsg .= ' (ระบบได้สำรองข้อมูลก่อนหน้าไว้ที่ ' . $preRestoreFilename . ' เพื่อความปลอดภัย)';
            }

            return redirect()->route('backups.index')->with('success', $successMsg);
        } catch (Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการกู้คืน: ' . $e->getMessage());
        }
    }

    public function destroy(BackupLog $backup)
    {
        $filePath = $backup->file_path;
        $fallbackPath = $this->backupDir . DIRECTORY_SEPARATOR . $backup->filename;

        if ($filePath && File::exists($filePath)) {
            File::delete($filePath);
        } elseif (File::exists($fallbackPath)) {
            File::delete($fallbackPath);
        }

        $filename = $backup->filename;
        AuditLog::record('delete', 'backups', "ลบไฟล์สำรองฐานข้อมูล: {$filename}", $backup);
        $backup->delete();

        return redirect()->route('backups.index')->with('success', "ลบไฟล์สำรอง {$filename} เรียบร้อยแล้ว");
    }

    /**
     * Clean up orphan backup files on disk that do not exist in database
     */
    public function cleanOrphans()
    {
        $allDbFilenames = BackupLog::pluck('filename')->toArray();
        $deletedCount = 0;
        $freedBytes = 0;

        if (File::exists($this->backupDir)) {
            foreach (File::files($this->backupDir) as $file) {
                if (!in_array($file->getFilename(), $allDbFilenames)) {
                    $freedBytes += $file->getSize();
                    File::delete($file->getPathname());
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            AuditLog::record('clear_old', 'backups', "ล้างไฟล์สำรองตกค้างที่ไม่มีในฐานข้อมูลจำนวน {$deletedCount} ไฟล์ (คืนพื้นที่ {$this->formatBytes($freedBytes)})");
            return redirect()->route('backups.index')->with('success', "ล้างไฟล์สำรองตกค้างเรียบร้อยแล้ว {$deletedCount} ไฟล์ (คืนพื้นที่จัดเก็บ {$this->formatBytes($freedBytes)})");
        }

        return redirect()->route('backups.index')->with('info', 'ไม่พบไฟล์สำรองตกค้างในพื้นที่จัดเก็บ');
    }

    private function performDump(string $outputPath, string $scope): bool
    {
        $host = config('database.connections.mysql.host', '192.168.2.10');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'c3thchospital');
        $username = config('database.connections.mysql.username', 'chang');
        $password = config('database.connections.mysql.password', 'chang11143');

        $mysqldumpPath = env('MYSQLDUMP_PATH', 'E:/xampp/mysql/bin/mysqldump.exe');

        // Check if mysqldump CLI exists
        if (File::exists($mysqldumpPath)) {
            $tableArgs = '';
            if ($scope === 'it_tables') {
                $tables = DB::select("SHOW TABLES LIKE 'it_%'");
                $tableNames = array_map(function ($t) {
                    return array_values((array)$t)[0];
                }, $tables);

                // Exclude transient/session/cache tables to prevent logout on restore
                $excluded = ['it_sessions', 'it_cache', 'it_cache_locks', 'it_jobs', 'it_failed_jobs', 'it_job_batches'];
                $tableNames = array_values(array_filter($tableNames, function ($t) use ($excluded) {
                    return !in_array($t, $excluded);
                }));

                $tableArgs = implode(' ', $tableNames);
            }

            $cmd = "\"{$mysqldumpPath}\" --host={$host} --port={$port} --user={$username} --password={$password} --default-character-set=utf8mb4 --single-transaction --quick {$database} {$tableArgs} > \"{$outputPath}\" 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar === 0 && File::exists($outputPath) && File::size($outputPath) > 0) {
                return true;
            }
        }

        // Fallback: Pure PHP PDO Database Exporter
        return $this->pdoDump($outputPath, $scope);
    }

    private function pdoDump(string $outputPath, string $scope): bool
    {
        $pdo = DB::connection()->getPdo();
        $handle = fopen($outputPath, 'w');

        fwrite($handle, "-- MariaDB SQL Backup generated by Thung Hua Chang Hospital IT System\n");
        fwrite($handle, "-- Date: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $sql = ($scope === 'it_tables') ? "SHOW TABLES LIKE 'it_%'" : "SHOW TABLES";
        $stmt = $pdo->query($sql);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $excluded = ['it_sessions', 'it_cache', 'it_cache_locks', 'it_jobs', 'it_failed_jobs', 'it_job_batches'];
        if ($scope === 'it_tables') {
            $tables = array_values(array_filter($tables, function ($t) use ($excluded) {
                return !in_array($t, $excluded);
            }));
        }

        foreach ($tables as $table) {
            fwrite($handle, "-- --------------------------------------------------\n");
            fwrite($handle, "-- Table structure for table `{$table}`\n");
            fwrite($handle, "-- --------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow = $createStmt->fetch(PDO::FETCH_ASSOC);
            $createSql = $createRow['Create Table'] ?? '';
            fwrite($handle, $createSql . ";\n\n");

            // Dump data
            $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                fwrite($handle, "-- Dumping data for table `{$table}`\n");
                $columns = array_keys($rows[0]);
                $colList = '`' . implode('`, `', $columns) . '`';

                foreach (array_chunk($rows, 50) as $chunk) {
                    $insertVals = [];
                    foreach ($chunk as $row) {
                        $values = array_map(function ($val) use ($pdo) {
                            if (is_null($val)) return 'NULL';
                            return $pdo->quote($val);
                        }, array_values($row));
                        $insertVals[] = '(' . implode(', ', $values) . ')';
                    }
                    fwrite($handle, "INSERT INTO `{$table}` ({$colList}) VALUES\n" . implode(",\n", $insertVals) . ";\n");
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);

        return true;
    }

    private function performRestore(string $sqlFilePath): bool
    {
        $host = config('database.connections.mysql.host', '192.168.2.10');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'c3thchospital');
        $username = config('database.connections.mysql.username', 'chang');
        $password = config('database.connections.mysql.password', 'chang11143');

        $mysqlPath = env('MYSQL_PATH', 'E:/xampp/mysql/bin/mysql.exe');

        if (File::exists($mysqlPath)) {
            $cmd = "\"{$mysqlPath}\" --host={$host} --port={$port} --user={$username} --password={$password} --default-character-set=utf8mb4 {$database} < \"{$sqlFilePath}\" 2>&1";
            exec($cmd, $output, $returnVar);
            if ($returnVar === 0) {
                return true;
            }
        }

        // Fallback: PDO statement runner
        $sql = File::get($sqlFilePath);
        DB::unprepared($sql);
        return true;
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
