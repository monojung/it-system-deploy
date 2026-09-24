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
     * Get configured update repository settings
     */
    public function getUpdateConfig(): array
    {
        return [
            'repo_url' => config('version.update_repo_url', env('SYSTEM_UPDATE_REPO_URL', 'https://github.com/monojung/it-system-deploy.git')),
            'branch' => config('version.update_branch', env('SYSTEM_UPDATE_BRANCH', 'main')),
            'remote_name' => config('version.update_remote_name', env('SYSTEM_UPDATE_REMOTE_NAME', 'deploy')),
        ];
    }

    /**
     * Ensure a git remote exists pointing to the target update repository.
     * Returns the name of the remote to use (e.g. 'deploy' or 'origin').
     */
    public function ensureUpdateRemote(): string
    {
        $config = $this->getUpdateConfig();
        $targetUrl = trim($config['repo_url']);
        $preferredRemote = trim($config['remote_name'] ?: 'deploy');

        try {
            $remotesOutput = $this->runProcess(['git', 'remote', '-v'], 10);
            $lines = explode("\n", trim($remotesOutput));

            // Check if any existing remote already points to targetUrl
            foreach ($lines as $line) {
                if (preg_match('/^(\S+)\s+(\S+)\s+\(fetch\)$/', trim($line), $matches)) {
                    $name = $matches[1];
                    $url = $matches[2];
                    $cleanTarget = rtrim($targetUrl, '.git');
                    $cleanCurrent = rtrim($url, '.git');
                    if ($cleanCurrent === $cleanTarget) {
                        return $name;
                    }
                }
            }

            // If not found, check if preferredRemote exists
            $preferredExists = false;
            foreach ($lines as $line) {
                if (preg_match('/^(\S+)\s+/', trim($line), $matches)) {
                    if ($matches[1] === $preferredRemote) {
                        $preferredExists = true;
                        break;
                    }
                }
            }

            if ($preferredExists) {
                $this->runProcess(['git', 'remote', 'set-url', $preferredRemote, $targetUrl], 10);
            } else {
                $this->runProcess(['git', 'remote', 'add', $preferredRemote, $targetUrl], 10);
            }

            return $preferredRemote;
        } catch (Exception $e) {
            Log::warning('SystemUpdateService::ensureUpdateRemote failed: ' . $e->getMessage());
            return $preferredRemote;
        }
    }

    /**
     * Initialize git repository in base_path and connect to deploy repository
     */
    public function initializeGitRepository(): array
    {
        $basePath = base_path();
        $config = $this->getUpdateConfig();
        $targetRepoUrl = $config['repo_url'];
        $targetBranch = $config['branch'] ?: 'main';
        $remoteName = $config['remote_name'] ?: 'deploy';

        $outputLogs = [];
        $append = function (string $text) use (&$outputLogs) {
            $outputLogs[] = $text;
        };

        // 0. Purge any stale Git lock files
        $cleared = $this->clearGitLocks();
        if (!empty($cleared)) {
            $append("0. ปลดล็อก Git ตกค้าง: ลบไฟล์ " . implode(', ', $cleared));
        }

        // 1. Verify Git executable exists
        try {
            $gitVer = $this->runProcess(['git', '--version'], 5);
            $append("ตรวจพบโปรแกรม Git: " . trim($gitVer));
        } catch (\Exception $e) {
            throw new \Exception("ไม่พบโปรแกรม Git บนเซิร์ฟเวอร์เครื่องนี้ ({$e->getMessage()}) กรุณาติดตั้ง Git หรืออัปเดตผ่านไฟล์แพตช์ ZIP แทน");
        }

        // 2. git init
        $out = $this->runProcess(['git', 'init'], 10);
        $append("1. สร้าง Git Repository (git init): " . trim($out));

        // 3. safe.directory config (Windows / Linux ownership safety)
        try {
            $this->runProcess(['git', 'config', '--global', '--add', 'safe.directory', str_replace('\\', '/', $basePath)], 5);
            $append("2. ตั้งค่า git safe.directory สำเร็จ");
        } catch (\Exception $e) {
            // ignore
        }

        // 4. remote setup
        try {
            $this->runProcess(['git', 'remote', 'remove', $remoteName], 5);
        } catch (\Exception $e) {
            // ignore
        }
        $out = $this->runProcess(['git', 'remote', 'add', $remoteName, $targetRepoUrl], 10);
        $append("3. เพิ่ม Remote '{$remoteName}' ({$targetRepoUrl}) สำเร็จ");

        // 5. git fetch
        $this->clearGitLocks();
        $append("4. กำลังดึงข้อมูลจาก Deploy Repo (git fetch {$remoteName} {$targetBranch})...");
        $this->runProcess(['git', 'fetch', $remoteName, $targetBranch], 60);
        $append("   Fetch สำเร็จ");

        // 6. align local branch to remote
        $remoteRef = "{$remoteName}/{$targetBranch}";
        try {
            try {
                $this->runProcess(['git', 'config', 'pull.rebase', 'false'], 5);
            } catch (\Exception $e) {
                // ignore
            }
            $this->clearGitLocks();
            $this->runProcess(['git', 'branch', '-M', $targetBranch], 5);
            try {
                $this->clearGitLocks();
                $this->runProcess(['git', 'checkout', '-f', '-B', $targetBranch, $remoteRef], 10);
            } catch (\Exception $coEx) {
                // ignore
            }
            $this->clearGitLocks();
            $this->runProcess(['git', 'reset', '--hard', $remoteRef], 15);
            $this->runProcess(['git', 'branch', "--set-upstream-to={$remoteRef}", $targetBranch], 5);
            $append("5. ซิงค์ Local Branch '{$targetBranch}' กับ {$remoteRef} แบบ Clean Reset สำเร็จ");
        } catch (\Exception $e) {
            $append("หมายเหตุในการซิงค์ Branch: " . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'เริ่มต้นและเชื่อมต่อ Git Repository กับเซิร์ฟเวอร์เรียบร้อยแล้ว!',
            'log' => implode("\n", $outputLogs),
        ];
    }

    /**
     * Check if git is available and get repository update status from target deploy repo
     */
    public function checkRemoteUpdates(): array
    {
        $basePath = base_path();
        $config = $this->getUpdateConfig();
        $targetRepoUrl = $config['repo_url'];
        $targetBranch = $config['branch'];

        // 1. Verify if .git directory exists
        if (!File::isDirectory($basePath . DIRECTORY_SEPARATOR . '.git')) {
            return [
                'success' => false,
                'is_git_repo' => false,
                'target_repo' => $targetRepoUrl,
                'target_branch' => $targetBranch,
                'remote_name' => 'none',
                'has_update' => false,
                'message' => 'ไม่พบโฟลเดอร์ .git ในโปรเจกต์ (เซิร์ฟเวอร์นี้ไม่ได้ติดตั้งผ่าน Git repository)',
                'last_checked_at' => now()->toDateTimeString(),
            ];
        }

        try {
            // Proactively clear any stale Git locks before checking remote updates
            $this->clearGitLocks();

            // Ensure remote points to the deploy repository
            $remoteName = $this->ensureUpdateRemote();

            // Get current local branch
            $currentBranch = trim($this->runProcess(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], 5));
            if (empty($currentBranch) || $currentBranch === 'HEAD') {
                $currentBranch = 'main';
            }

            // Get local commit
            $localCommit = trim($this->runProcess(['git', 'rev-parse', 'HEAD'], 5));
            $shortLocalCommit = substr($localCommit, 0, 7);

            // Fetch remote changes from target deploy repository (30s timeout)
            $this->runProcess(['git', 'fetch', $remoteName, $targetBranch], 30);

            // Get target remote commit
            $remoteRef = "{$remoteName}/{$targetBranch}";
            $remoteCommit = '';
            try {
                $remoteCommit = trim($this->runProcess(['git', 'rev-parse', $remoteRef], 5));
            } catch (Exception $e) {
                // If direct ref parse fails, try fallback
                $remoteCommit = '';
            }

            $shortRemoteCommit = $remoteCommit ? substr($remoteCommit, 0, 7) : 'unknown';

            // Compare local commit vs remote commit
            $commitsBehind = 0;
            $commits = [];
            $hasUpdate = false;

            if ($remoteCommit && $localCommit === $remoteCommit) {
                // Exactly matched
                $hasUpdate = false;
                $commitsBehind = 0;
            } elseif ($remoteCommit) {
                // Commits differ, check if history is related or not
                try {
                    $behindCountOutput = trim($this->runProcess(['git', 'rev-list', '--count', "HEAD..{$remoteRef}"], 5));
                    $commitsBehind = is_numeric($behindCountOutput) ? (int) $behindCountOutput : 0;
                } catch (Exception $ex) {
                    $commitsBehind = 1;
                }

                if ($commitsBehind > 0) {
                    $hasUpdate = true;
                    try {
                        $logOutput = $this->runProcess([
                            'git', 'log', "HEAD..{$remoteRef}",
                            '--pretty=format:%h||%an||%ad||%s',
                            '--date=short'
                        ], 10);

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
                    } catch (Exception $ex) {
                        // In case of unrelated history, fetch recent commits on remoteRef
                        try {
                            $logOutput = $this->runProcess([
                                'git', 'log', '-n', '5', $remoteRef,
                                '--pretty=format:%h||%an||%ad||%s',
                                '--date=short'
                            ], 10);
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
                        } catch (Exception $ex2) {
                            // ignore
                        }
                    }
                } else {
                    if ($localCommit !== $remoteCommit) {
                        $hasUpdate = true;
                        $commitsBehind = 1;
                        // Fetch the latest remote commit info
                        try {
                            $logOutput = $this->runProcess([
                                'git', 'log', '-1', $remoteRef,
                                '--pretty=format:%h||%an||%ad||%s',
                                '--date=short'
                            ], 5);
                            $parts = explode('||', trim($logOutput));
                            if (count($parts) >= 4) {
                                $commits[] = [
                                    'hash' => trim($parts[0]),
                                    'author' => trim($parts[1]),
                                    'date' => trim($parts[2]),
                                    'message' => trim($parts[3]),
                                ];
                            }
                        } catch (Exception $ex3) {
                            // ignore
                        }
                    }
                }
            }

            return [
                'success' => true,
                'is_git_repo' => true,
                'target_repo' => $targetRepoUrl,
                'target_branch' => $targetBranch,
                'remote_name' => $remoteName,
                'branch' => $currentBranch,
                'local_commit' => $localCommit,
                'short_local_commit' => $shortLocalCommit,
                'remote_commit' => $remoteCommit,
                'short_remote_commit' => $shortRemoteCommit,
                'commits_behind' => $commitsBehind,
                'has_update' => $hasUpdate,
                'commits' => $commits,
                'current_version' => function_exists('app_version') ? app_version() : config('version.version', '2.2.2'),
                'last_checked_at' => now()->toDateTimeString(),
            ];

        } catch (Exception $e) {
            Log::warning('SystemUpdateService::checkRemoteUpdates failed: ' . $e->getMessage());

            // Provide partial info even if remote fetch failed (e.g. offline)
            $localCommit = '';
            try {
                $localCommit = trim($this->runProcess(['git', 'rev-parse', 'HEAD'], 5));
            } catch (Exception $ex) {
                // ignore
            }

            $rawError = $e->getMessage();
            $errorType = 'เครือข่ายหรือสิทธิ์การเข้าถึง';
            $hint = 'กรุณาตรวจสอบการเชื่อมต่อเครือข่ายของเซิร์ฟเวอร์';

            if (str_contains($rawError, 'not recognized as an internal or external command') || str_contains($rawError, 'git: command not found') || str_contains($rawError, 'No such file or directory')) {
                $errorType = 'ไม่พบโปรแกรม Git บนสภาพแวดล้อมของเซิร์ฟเวอร์ (Git Not Found in PATH)';
                $hint = 'เซิร์ฟเวอร์เว็บ (Apache/XAMPP) ไม่พบคำสั่ง git ใน System PATH กรุณาระบุ GIT_PATH ในไฟล์ .env เช่น GIT_PATH="C:\\Program Files\\Git\\cmd\\git.exe" หรือเพิ่ม Git ลงใน PATH ของระบบ';
            } elseif (str_contains($rawError, 'Could not resolve host')) {
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
                'target_repo' => $targetRepoUrl,
                'target_branch' => $targetBranch,
                'remote_name' => $remoteName ?? 'deploy',
                'has_update' => false,
                'local_commit' => $localCommit,
                'short_local_commit' => substr($localCommit, 0, 7),
                'remote_commit' => '',
                'short_remote_commit' => 'unknown',
                'error' => $rawError,
                'error_type' => $errorType,
                'hint' => $hint,
                'message' => "ไม่สามารถเชื่อมต่อกับ Git Remote [{$targetRepoUrl}] ได้ ({$errorType}): {$hint}",
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

            // STEP 2: Automatic Database Backup (if enabled)
            $backupFile = null;
            if (setting('backup_auto_enabled', false)) {
                $notify(2, 'สำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)', 'กำลังสำรองโครงสร้างและข้อมูลตาราง it_*');
                try {
                    $backupController = app(BackupController::class);
                    $backupLog = $backupController->executeBackup('it_tables', 'Auto-backup ก่อนอัปเดตระบบอัตโนมัติ', 'pre_update');
                    $backupFile = $backupLog->filename;
                    $updateRecord->update(['backup_file' => $backupFile]);
                    $appendLog("Database auto-backup created successfully: {$backupFile} (" . number_format($backupLog->file_size) . " bytes)");
                } catch (Exception $e) {
                    $appendLog("Warning on database backup: " . $e->getMessage() . " (ดำเนินการต่อ)");
                }
            } else {
                $notify(2, 'ข้ามขั้นตอนสำรองฐานข้อมูลอัตโนมัติ', 'ระบบปิดการสำรองข้อมูลอัตโนมัติไว้ (Auto-Backup Disabled)');
                $appendLog("Step 2 skipped: Auto-backup is disabled in system settings.");
            }

            // STEP 3: Git Fetch & Force Sync to latest release
            $remoteName = $this->ensureUpdateRemote();
            $updateConfig = $this->getUpdateConfig();
            $targetBranch = $updateConfig['branch'] ?? 'main';
            $remoteRef = "{$remoteName}/{$targetBranch}";

            // Proactively purge any stale Git lock files before fetch & sync
            $clearedLocks = $this->clearGitLocks();
            if (!empty($clearedLocks)) {
                $appendLog("Cleared stale Git locks before sync: " . implode(', ', $clearedLocks));
            }

            $notify(3, 'ดึงโค้ดล่าสุดจาก Git Deploy Repository', "กำลังดึงโค้ดจาก {$remoteRef}");
            $gitOutput = '';
            try {
                // 1. Fetch latest commits from remote repository (60s timeout for slower network)
                $this->clearGitLocks();
                $fetchOutput = $this->runProcess(['git', 'fetch', $remoteName, $targetBranch], 60);
                if (!empty(trim($fetchOutput))) {
                    $appendLog("Git fetch output:\n" . trim($fetchOutput));
                }

                // 2. Set pull.rebase = false to silence divergent branch hint
                try {
                    $this->runProcess(['git', 'config', 'pull.rebase', 'false'], 5);
                } catch (Exception $e) {
                    // non-critical
                }

                // 3. Attempt standard pull
                $syncSuccessful = false;
                try {
                    $this->clearGitLocks();
                    $gitOutput = $this->runProcess(['git', 'pull', '--no-edit', $remoteName, $targetBranch], 30);
                    $syncSuccessful = true;
                } catch (Exception $pullEx) {
                    $pullErr = $pullEx->getMessage();
                    $appendLog("Notice: Standard git pull failed ({$pullErr}). Attempting automatic recovery...");

                    // If unrelated histories, attempt with --allow-unrelated-histories
                    if (str_contains($pullErr, 'unrelated histories')) {
                        try {
                            $this->clearGitLocks();
                            $gitOutput = $this->runProcess(['git', 'pull', '--no-edit', '--allow-unrelated-histories', $remoteName, $targetBranch], 30);
                            $syncSuccessful = true;
                        } catch (Exception $unrelatedEx) {
                            $appendLog("Notice: Pull with unrelated histories also failed. Proceeding with robust force reset...");
                        }
                    }
                }

                // 4. Automatic Conflict Recovery / Force Reset:
                // Production servers are deployment targets; they must cleanly match the latest release on $remoteRef.
                // If standard pull failed due to local uncommitted changes, divergent branches, or CRLF differences:
                // We cleanly reset tracked files to $remoteRef (safely preserving untracked files: .env, storage/*, uploads/*, sqlite).
                if (!$syncSuccessful) {
                    $appendLog("Overcoming local changes/divergence: Synchronizing codebase cleanly to {$remoteRef}...");

                    // Purge locks before checkout & reset
                    $this->clearGitLocks();

                    // Force checkout target branch
                    try {
                        $this->runProcess(['git', 'checkout', '-f', '-B', $targetBranch, $remoteRef], 20);
                    } catch (Exception $coEx) {
                        $appendLog("Checkout note: " . $coEx->getMessage());
                    }

                    // Reset index and tracked files to the exact fetched commit
                    $this->clearGitLocks();
                    $gitOutput = $this->runProcess(['git', 'reset', '--hard', $remoteRef], 30);

                    // Re-set tracking branch
                    try {
                        $this->runProcess(['git', 'branch', "--set-upstream-to={$remoteRef}", $targetBranch], 10);
                    } catch (Exception $upEx) {
                        // non-critical
                    }

                    $appendLog("Successfully synchronized codebase to {$remoteRef} via force reset.");
                }

                $appendLog("Git output:\n" . $gitOutput);
            } catch (Exception $e) {
                $this->clearGitLocks();
                throw new Exception("ไม่สามารถดึงโค้ดจาก Git Deploy Repository ได้: " . $e->getMessage());
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

                // Direct purge of all bootstrap cache files to guarantee no stale routes or configs linger
                $bootstrapCacheFiles = [
                    base_path('bootstrap/cache/config.php'),
                    base_path('bootstrap/cache/routes-v7.php'),
                    base_path('bootstrap/cache/routes.php'),
                    base_path('bootstrap/cache/events.php'),
                    base_path('bootstrap/cache/services.php'),
                    base_path('bootstrap/cache/packages.php'),
                ];
                $purgedFiles = [];
                foreach ($bootstrapCacheFiles as $cFile) {
                    if (file_exists($cFile)) {
                        @unlink($cFile);
                        $purgedFiles[] = basename($cFile);
                    }
                }
                if (!empty($purgedFiles)) {
                    $appendLog("Direct bootstrap cache purge: " . implode(', ', $purgedFiles));
                }

                // NOTE: Do NOT call route:cache or config:cache here within the active web request,
                // because the in-memory route collection was booted before git pull replaced files on disk.
                // Keeping routes dynamic allows Laravel to load routes/web.php fresh on the next request.
                if (config('app.env') === 'production') {
                    try {
                        Artisan::call('view:cache');
                        $appendLog("Application view cache pre-warmed.");
                    } catch (Exception $vEx) {
                        $appendLog("View cache note: " . $vEx->getMessage());
                    }
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

            // Re-read version freshly from disk (as in-memory config may be stale after git pull)
            $versionConfig = @include config_path('version.php');
            $newVersion = is_array($versionConfig) && !empty($versionConfig['version'])
                ? (string) $versionConfig['version']
                : (function_exists('app_version') ? app_version() : config('version.version', '2.4.0'));

            // Keep system setting synchronized
            try {
                \App\Models\SystemSetting::set('app_version', $newVersion, 'version', 'text', 'เวอร์ชันระบบสารสนเทศ');
            } catch (Exception $sEx) {
                // ignore
            }

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
            // Guarantee no stale Git lock files linger after failure
            $this->clearGitLocks();

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

            // STEP 2: Automatic Database Backup (if enabled)
            $backupFile = null;
            if (setting('backup_auto_enabled', false)) {
                $notify(2, 'สำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)', 'กำลังสำรองข้อมูลก่อนเริ่มติดตั้งแพตช์');
                try {
                    $backupController = app(BackupController::class);
                    $backupLog = $backupController->executeBackup('it_tables', 'Auto-backup ก่อนติดตั้ง Patch ZIP', 'pre_update');
                    $backupFile = $backupLog->filename;
                    $updateRecord->update(['backup_file' => $backupFile]);
                    $appendLog("Database auto-backup created: {$backupFile}");
                } catch (Exception $e) {
                    $appendLog("Warning on database backup: " . $e->getMessage() . " (ดำเนินการต่อ)");
                }
            } else {
                $notify(2, 'ข้ามขั้นตอนสำรองฐานข้อมูลอัตโนมัติ', 'ระบบปิดการสำรองข้อมูลอัตโนมัติไว้ (Auto-Backup Disabled)');
                $appendLog("Step 2 skipped: Auto-backup is disabled in system settings.");
            }

            // STEP 3: Extract Patch ZIP over base_path
            $notify(3, 'แตกไฟล์แพตช์ลงในโปรเจกต์', 'กำลังคลายไฟล์จาก Patch ZIP ตรงตามโครงสร้างโฟลเดอร์ 100%');
            $basePath = base_path();
            $extractStats = $this->extractZipArchive(
                zipPath: $zipPath,
                targetDir: $basePath,
                protectedPaths: ['.env', 'storage', 'database/database.sqlite', '.git'],
                logCallback: $appendLog
            );

            $extractedCount = $extractStats['extracted_count'];
            $createdDirs = $extractStats['created_dirs'];
            $totalMb = round($extractStats['total_bytes'] / 1048576, 2);
            $appendLog("แตกไฟล์แพตช์สำเร็จ: แตกไฟล์ {$extractedCount} ไฟล์ ({$totalMb} MB), สร้าง/ตรวจสอบโครงสร้างโฟลเดอร์ {$createdDirs} โฟลเดอร์");
            if (!empty($extractStats['errors'])) {
                $appendLog("คำเตือนการแตกไฟล์: " . implode('; ', array_slice($extractStats['errors'], 0, 5)));
            }

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

            // Re-read version freshly from disk
            $versionConfig = @include config_path('version.php');
            $newVersion = is_array($versionConfig) && !empty($versionConfig['version'])
                ? (string) $versionConfig['version']
                : (function_exists('app_version') ? app_version() : config('version.version', '2.4.0'));

            // Keep system setting synchronized
            try {
                \App\Models\SystemSetting::set('app_version', $newVersion, 'version', 'text', 'เวอร์ชันระบบสารสนเทศ');
            } catch (Exception $sEx) {
                // ignore
            }

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

            // Clean any stale Git locks so repository is immediately healthy
            $this->clearGitLocks();

            return $updateRecord;

        } catch (Exception $e) {
            $this->clearGitLocks();
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
     * Resolve git executable path and ensure Git environment
     */
    public function resolveGitBinary(): string
    {
        $configured = config('version.git_path', env('GIT_PATH'));
        if ($configured && (file_exists($configured) || is_executable($configured))) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates = [
                'C:\\Program Files\\Git\\cmd\\git.exe',
                'C:\\Program Files\\Git\\bin\\git.exe',
                'C:\\Program Files (x86)\\Git\\cmd\\git.exe',
                'C:\\Program Files (x86)\\Git\\bin\\git.exe',
                'D:\\Git\\cmd\\git.exe',
                'D:\\Git\\bin\\git.exe',
                'E:\\Git\\cmd\\git.exe',
                'E:\\Git\\bin\\git.exe',
                'C:\\Git\\cmd\\git.exe',
                'C:\\Git\\bin\\git.exe',
            ];
            $localApp = getenv('LOCALAPPDATA');
            if ($localApp) {
                $candidates[] = $localApp . '\\Programs\\Git\\cmd\\git.exe';
                $candidates[] = $localApp . '\\Programs\\Git\\bin\\git.exe';
            }

            foreach ($candidates as $candidate) {
                if (file_exists($candidate)) {
                    return $candidate;
                }
            }
        }

        return 'git';
    }

    /**
     * Prepare environment variables for running processes (ensuring Git paths are in PATH)
     */
    protected function getProcessEnv(): array
    {
        $env = $_ENV;
        $currentPath = getenv('PATH') ?: '';

        if (PHP_OS_FAMILY === 'Windows') {
            $gitDirs = [
                'C:\\Program Files\\Git\\cmd',
                'C:\\Program Files\\Git\\bin',
                'C:\\Program Files\\Git\\usr\\bin',
                'C:\\Program Files (x86)\\Git\\cmd',
                'C:\\Program Files (x86)\\Git\\bin',
                'D:\\Git\\cmd',
                'D:\\Git\\bin',
                'E:\\Git\\cmd',
                'E:\\Git\\bin',
            ];
            $extraPaths = [];
            foreach ($gitDirs as $dir) {
                if (is_dir($dir) && !str_contains($currentPath, $dir)) {
                    $extraPaths[] = $dir;
                }
            }
            if (!empty($extraPaths)) {
                $currentPath = implode(';', $extraPaths) . ';' . $currentPath;
            }
        }

        $env['PATH'] = $currentPath;
        return $env;
    }

    /**
     * Check if an error output string indicates a Git lock collision
     */
    public function isGitLockError(string $error): bool
    {
        $lower = strtolower($error);
        return str_contains($lower, '.lock') ||
               str_contains($lower, 'cannot lock ref') ||
               (str_contains($lower, 'unable to create') && str_contains($lower, 'lock')) ||
               (str_contains($lower, 'file exists') && str_contains($lower, 'lock')) ||
               str_contains($lower, 'another git process seems to be running') ||
               str_contains($lower, 'index.lock') ||
               str_contains($lower, 'head.lock');
    }

    /**
     * Purge all stale Git lock files (.git/*.lock, index.lock, HEAD.lock, refs, logs)
     * to guarantee uninterrupted updates.
     *
     * @param string|null $basePath
     * @return array List of removed lock file paths
     */
    public function clearGitLocks(?string $basePath = null): array
    {
        $base = $basePath ?: base_path();
        $gitDir = $base . DIRECTORY_SEPARATOR . '.git';
        $cleared = [];

        if (!File::isDirectory($gitDir)) {
            return $cleared;
        }

        // 1. Common top-level lock & merge files in .git
        $topLevelLocks = [
            $gitDir . DIRECTORY_SEPARATOR . 'HEAD.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'index.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'config.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'packed-refs.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'FETCH_HEAD.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'ORIG_HEAD.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'COMMIT_EDITMSG.lock',
            $gitDir . DIRECTORY_SEPARATOR . 'MERGE_HEAD',
            $gitDir . DIRECTORY_SEPARATOR . 'AUTO_MERGE',
        ];

        foreach ($topLevelLocks as $lockFile) {
            if (file_exists($lockFile)) {
                if (@unlink($lockFile)) {
                    $cleared[] = str_replace($base . DIRECTORY_SEPARATOR, '', $lockFile);
                }
            }
        }

        // 2. Scan recursively for any nested .lock files (e.g. .git/refs/heads/*.lock, .git/logs/**/*.lock)
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($gitDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $item) {
                if ($item->isFile() && str_ends_with(strtolower($item->getFilename()), '.lock')) {
                    $filePath = $item->getRealPath();
                    if ($filePath && file_exists($filePath)) {
                        if (@unlink($filePath)) {
                            $rel = str_replace($base . DIRECTORY_SEPARATOR, '', $filePath);
                            if (!in_array($rel, $cleared, true)) {
                                $cleared[] = $rel;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('SystemUpdateService::clearGitLocks scan exception: ' . $e->getMessage());
        }

        if (!empty($cleared)) {
            Log::info('SystemUpdateService::clearGitLocks cleared: ' . implode(', ', $cleared));
        }

        return $cleared;
    }

    /**
     * Helper to run Symfony Process with timeout and error checking
     * Features automatic Git lock recovery and single auto-retry.
     */
    protected function runProcess(array $command, int $timeout = 30, bool $autoRetryOnLock = true): string
    {
        if (!empty($command) && $command[0] === 'git') {
            $gitBinary = $this->resolveGitBinary();
            if ($gitBinary !== 'git') {
                $command[0] = $gitBinary;
            }

            // Proactively clear stale locks on write commands
            $subCmd = $command[1] ?? '';
            if (in_array($subCmd, ['reset', 'checkout', 'pull', 'fetch', 'merge', 'branch', 'init'], true)) {
                $this->clearGitLocks();
            }
        }

        $process = new Process($command, base_path(), $this->getProcessEnv());
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            $errorOutput = $process->getErrorOutput() ?: $process->getOutput();
            $errorText = trim($errorOutput);

            // Automatic recovery for Git lock file collisions (HEAD.lock, index.lock, File exists)
            if ($autoRetryOnLock && $this->isGitLockError($errorText)) {
                Log::warning("Git lock collision detected during [" . implode(' ', $command) . "]: {$errorText}. Purging stale locks and retrying command...");
                $this->clearGitLocks();
                usleep(250000); // 250ms pause for file handles to release

                // Retry command once with autoRetryOnLock disabled to prevent infinite loop
                return $this->runProcess($command, $timeout, false);
            }

            throw new Exception("Command [" . implode(' ', $command) . "] failed: " . $errorText);
        }

        return $process->getOutput();
    }

    /**
     * แตกไฟล์ ZIP ตรงตามโครงสร้างโฟลเดอร์สมบูรณ์ 100% (มาตรฐานเดียวกันกับ unzip.php)
     * รองรับทั้ง Windows (\) และ Linux (/), ป้องกัน Directory Traversal,
     * ตรวจสอบ/สร้างโฟลเดอร์แม่ล่วงหน้า, ป้องกันไฟล์ระบบสำคัญ (.env, storage, sqlite),
     * และตัด prefix โฟลเดอร์ wrapper อัตโนมัติหากมี
     *
     * @param string $zipPath พาธไฟล์ ZIP
     * @param string $targetDir โฟลเดอร์ปลายทาง (เช่น base_path())
     * @param array $protectedPaths รายการไฟล์หรือโฟลเดอร์ที่ห้ามเขียนทับ
     * @param callable|null $logCallback คอลแบ็กสำหรับบันทึกข้อความลง Log
     * @return array{extracted_count: int, created_dirs: int, total_bytes: int, errors: array}
     * @throws Exception
     */
    public function extractZipArchive(
        string $zipPath,
        string $targetDir,
        array $protectedPaths = ['.env', 'storage', 'database/database.sqlite', '.git'],
        ?callable $logCallback = null
    ): array {
        if (!class_exists('\ZipArchive')) {
            throw new Exception('ไม่พบโมดูล ZipArchive ใน PHP ของเซิร์ฟเวอร์ กรุณาเปิดใช้งาน extension=zip');
        }

        if (!file_exists($zipPath)) {
            throw new Exception("ไม่พบไฟล์ ZIP ที่ระบุ: {$zipPath}");
        }

        $zip = new \ZipArchive();
        $res = $zip->open($zipPath);
        if ($res !== true) {
            throw new Exception("ไม่สามารถเปิดไฟล์ ZIP ได้ (Code: {$res})");
        }

        $log = function (string $msg) use ($logCallback) {
            if (is_callable($logCallback)) {
                $logCallback($msg);
            }
        };

        $extractedCount = 0;
        $createdDirs = 0;
        $totalBytes = 0;
        $errors = [];

        // ตรวจสอบโฟลเดอร์ครอบส่วนเกิน (Wrapper Directory) เช่น update-patch/ หรือ it-system/
        $stripPrefix = '';
        $standardRoots = [
            'app', 'bootstrap', 'config', 'database', 'lang', 'public',
            'resources', 'routes', 'vendor', 'scripts', 'agent',
            'artisan', 'composer.json', 'composer.lock', 'server_init.php', 'index.php'
        ];
        $firstSegments = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $n = str_replace('\\', '/', ltrim($stat['name'], '/'));
            $n = str_replace('../', '', $n);
            if ($n === '' || $n === '.' || $n === './') {
                continue;
            }
            $parts = explode('/', $n);
            if (count($parts) > 1) {
                $firstSegments[$parts[0]] = true;
            } else {
                $firstSegments[$parts[0]] = false;
            }
        }

        if (count($firstSegments) === 1) {
            $onlyRoot = array_key_first($firstSegments);
            if (!in_array($onlyRoot, $standardRoots, true)) {
                $stripPrefix = $onlyRoot . '/';
                $log("ตรวจพบโฟลเดอร์ครอบ '{$onlyRoot}/' ใน ZIP - ระบบตัด prefix ออกให้อัตโนมัติเพื่อให้แตกไฟล์ลงโฟลเดอร์หลักอย่างถูกต้อง");
            }
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $rawName = $stat['name'];

            // 1. แปลง Backslash (\) ทั้งหมดเป็น Forward Slash (/) ตามมาตรฐาน Posix ป้องกันโฟลเดอร์เพี้ยน
            $normalized = str_replace('\\', '/', $rawName);
            $normalized = ltrim($normalized, '/');

            // 2. ป้องกัน Directory Traversal
            $normalized = str_replace('../', '', $normalized);

            if ($normalized === '' || $normalized === '.' || $normalized === './') {
                continue;
            }

            // ตัดโฟลเดอร์ wrapper ส่วนเกิน (ถ้ามี)
            if ($stripPrefix !== '' && str_starts_with($normalized, $stripPrefix)) {
                $normalized = substr($normalized, strlen($stripPrefix));
                if ($normalized === '' || $normalized === '.' || $normalized === './') {
                    continue;
                }
            }

            // ตรวจสอบและข้ามไฟล์/โฟลเดอร์ที่ต้องได้รับการคุ้มครองความปลอดภัย
            $skip = false;
            foreach ($protectedPaths as $protected) {
                $cleanProtected = str_replace('\\', '/', trim($protected, '/'));
                if (
                    $normalized === $cleanProtected ||
                    str_starts_with($normalized, $cleanProtected . '/') ||
                    ($cleanProtected === '.env' && str_starts_with($normalized, '.env.'))
                ) {
                    $log("ข้ามไฟล์/โฟลเดอร์ปลอดภัย: {$normalized}");
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                continue;
            }

            $destPath = rtrim($targetDir, '/\\') . '/' . $normalized;

            // 3. กรณีเป็นไดเรกทอรี (ลงท้ายด้วย / หรือ \ ใน zip)
            $isDir = str_ends_with($normalized, '/') || substr($rawName, -1) === '\\' || substr($rawName, -1) === '/';
            if ($isDir) {
                if (!is_dir($destPath)) {
                    if (@mkdir($destPath, 0777, true)) {
                        $createdDirs++;
                    }
                }
                @chmod($destPath, 0755);
                continue;
            }

            // 4. กรณีเป็นไฟล์: ตรวจสอบและสร้างโฟลเดอร์แม่ (Parent Directory) ให้ตรงตามโครงสร้าง 100% ก่อนเขียนไฟล์
            $parentDir = dirname($destPath);
            if (!is_dir($parentDir)) {
                if (@mkdir($parentDir, 0777, true)) {
                    $createdDirs++;
                }
            }

            // 5. แตกไฟล์ด้วย Stream Copy เพื่อประหยัด Memory และรวดเร็ว (แบบเดียวกับ unzip.php)
            $srcStream = $zip->getStream($rawName);
            if ($srcStream) {
                $destStream = @fopen($destPath, 'wb');
                if ($destStream) {
                    $bytes = stream_copy_to_stream($srcStream, $destStream);
                    $totalBytes += $bytes;
                    fclose($destStream);
                    $extractedCount++;
                    @chmod($destPath, 0644);
                } else {
                    // Fallback: file_put_contents
                    $content = $zip->getFromIndex($i);
                    if ($content !== false && @file_put_contents($destPath, $content) !== false) {
                        $totalBytes += strlen($content);
                        $extractedCount++;
                        @chmod($destPath, 0644);
                    } else {
                        $errors[] = "ไม่สามารถเขียนไฟล์: {$normalized}";
                        $log("ข้อผิดพลาด: ไม่สามารถเขียนไฟล์: {$normalized}");
                    }
                }
                fclose($srcStream);
            } else {
                // Fallback: getFromIndex
                $content = $zip->getFromIndex($i);
                if ($content !== false && @file_put_contents($destPath, $content) !== false) {
                    $totalBytes += strlen($content);
                    $extractedCount++;
                    @chmod($destPath, 0644);
                } else {
                    $errors[] = "ไม่สามารถอ่านไฟล์จาก ZIP: {$rawName}";
                    $log("ข้อผิดพลาด: ไม่สามารถอ่านไฟล์จาก ZIP: {$rawName}");
                }
            }
        }

        $zip->close();

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        return [
            'extracted_count' => $extractedCount,
            'created_dirs' => $createdDirs,
            'total_bytes' => $totalBytes,
            'errors' => $errors,
        ];
    }
}
