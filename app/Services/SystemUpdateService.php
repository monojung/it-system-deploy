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
                'branch' => 'main',
                'local_commit' => '',
                'short_local_commit' => 'unknown',
                'remote_commit' => '',
                'short_remote_commit' => 'unknown',
                'commits_behind' => 0,
                'commits' => [],
                'has_update' => false,
                'current_version' => function_exists('app_version') ? app_version() : config('version.version', '2.5.8'),
                'current_build' => config('version.build', '20261001.2'),
                'remote_version' => null,
                'remote_build' => null,
                'remote_release_date' => null,
                'remote_release_name' => null,
                'has_newer_version' => false,
                'cleared_locks_count' => 0,
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

            // Extract remote version, build, and release details from remote config/version.php
            $remoteVersion = null;
            $remoteBuild = null;
            $remoteReleaseDate = null;
            $remoteReleaseName = null;

            if (!empty($remoteCommit)) {
                try {
                    $remoteVersionPhp = $this->runProcess(['git', 'show', "{$remoteRef}:config/version.php"], 5);
                    if (!empty($remoteVersionPhp)) {
                        if (preg_match("/'version'\s*=>\s*env\(['\"]APP_VERSION['\"],\s*['\"]([^'\"]+)['\"]\)/", $remoteVersionPhp, $vm) ||
                            preg_match("/'version'\s*=>\s*['\"]([^'\"]+)['\"]/", $remoteVersionPhp, $vm)) {
                            $remoteVersion = $vm[1];
                        }
                        if (preg_match("/'build'\s*=>\s*['\"]([^'\"]+)['\"]/", $remoteVersionPhp, $bm)) {
                            $remoteBuild = $bm[1];
                        }
                        if (preg_match("/'release_date'\s*=>\s*['\"]([^'\"]+)['\"]/", $remoteVersionPhp, $dm)) {
                            $remoteReleaseDate = $dm[1];
                        }
                        if (preg_match("/'release_name'\s*=>\s*['\"]([^'\"]+)['\"]/", $remoteVersionPhp, $nm)) {
                            $remoteReleaseName = $nm[1];
                        }
                    }
                } catch (Exception $e) {
                    // non-critical fallback
                }

                // Check for git tag on remote
                try {
                    $tagOut = trim($this->runProcess(['git', 'describe', '--tags', '--exact-match', $remoteRef], 5));
                    if (!empty($tagOut)) {
                        if (!$remoteVersion) {
                            $remoteVersion = ltrim($tagOut, 'v');
                        }
                    }
                } catch (Exception $e) {
                    // non-critical
                }
            }

            $currentVersion = function_exists('app_version') ? app_version() : config('version.version', '2.5.2');
            $hasNewerVersion = false;
            if ($remoteVersion) {
                $hasNewerVersion = version_compare($remoteVersion, $currentVersion, '>');
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
                'has_update' => $hasUpdate || $hasNewerVersion,
                'commits' => $commits,
                'current_version' => $currentVersion,
                'current_build' => config('version.build', '20260925.2'),
                'remote_version' => $remoteVersion,
                'remote_build' => $remoteBuild,
                'remote_release_date' => $remoteReleaseDate,
                'remote_release_name' => $remoteReleaseName,
                'has_newer_version' => $hasNewerVersion,
                'cleared_locks_count' => count($clearedLocks),
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

            // Attempt fallback via direct HTTP(S) query to GitHub raw content
            // to still retrieve the latest remote version information if Git CLI network is blocked
            $currentVersion = function_exists('app_version') ? app_version() : config('version.version', '2.5.7');
            $fallbackVersionInfo = $this->fetchRemoteVersionViaHttp($targetRepoUrl, $targetBranch);
            $hasNewerVersion = false;
            $remoteVersion = null;
            $remoteBuild = null;
            $remoteReleaseDate = null;
            $remoteReleaseName = null;

            if ($fallbackVersionInfo && !empty($fallbackVersionInfo['remote_version'])) {
                $remoteVersion = $fallbackVersionInfo['remote_version'];
                $remoteBuild = $fallbackVersionInfo['remote_build'] ?? null;
                $remoteReleaseDate = $fallbackVersionInfo['remote_release_date'] ?? null;
                $remoteReleaseName = $fallbackVersionInfo['remote_release_name'] ?? null;
                $hasNewerVersion = version_compare($remoteVersion, $currentVersion, '>');

                return [
                    'success' => true,
                    'connection_mode' => 'https_hub',
                    'is_git_repo' => File::isDirectory($basePath . DIRECTORY_SEPARATOR . '.git'),
                    'target_repo' => $targetRepoUrl,
                    'target_branch' => $targetBranch,
                    'remote_name' => $remoteName ?? 'deploy',
                    'branch' => $currentBranch ?? 'main',
                    'local_commit' => $localCommit ?? '',
                    'short_local_commit' => !empty($localCommit) ? substr($localCommit, 0, 7) : 'unknown',
                    'remote_commit' => '',
                    'short_remote_commit' => 'latest',
                    'commits_behind' => $hasNewerVersion ? 1 : 0,
                    'commits' => [],
                    'has_update' => $hasNewerVersion,
                    'current_version' => $currentVersion,
                    'current_build' => config('version.build', 'Latest'),
                    'remote_version' => $remoteVersion,
                    'remote_build' => $remoteBuild,
                    'remote_release_date' => $remoteReleaseDate,
                    'remote_release_name' => $remoteReleaseName,
                    'has_newer_version' => $hasNewerVersion,
                    'cleared_locks_count' => 0,
                    'http_fallback' => true,
                    'error' => null,
                    'message' => 'เชื่อมต่อผ่านระบบ HTTPS Fail-Safe Hub สำเร็จ (ระบบพร้อมให้อัปเดตอัตโนมัติ 100%)',
                    'last_checked_at' => now()->toDateTimeString(),
                ];
            }

            $rawError = $e->getMessage();
            $errorType = 'เครือข่ายหรือสิทธิ์การเข้าถึง';
            $hint = 'กรุณาตรวจสอบการเชื่อมต่อเครือข่ายของเซิร์ฟเวอร์ หรือใช้วิธีอัปเดตผ่านไฟล์แพตช์ ZIP ด้านล่าง';

            if (str_contains($rawError, 'not recognized as an internal or external command') || str_contains($rawError, 'git: command not found') || str_contains($rawError, 'No such file or directory')) {
                $errorType = 'ไม่พบโปรแกรม Git บนสภาพแวดล้อมของเซิร์ฟเวอร์ (Git Not Found in PATH)';
                $hint = 'เซิร์ฟเวอร์เว็บไม่พบคำสั่ง git ใน System PATH สามารถอัปเดตผ่านไฟล์แพตช์ ZIP ได้ทันทีโดยไม่ต้องติดตั้ง Git';
            } elseif (str_contains($rawError, 'detected dubious ownership')) {
                $errorType = 'สิทธิ์การครอบครองโฟลเดอร์ Git (Dubious Ownership)';
                $hint = 'ระบบได้เปิดการข้าม safe.directory=* เรียบร้อยแล้ว กรุณากดปุ่มตรวจสอบเวอร์ชันใหม่อีกครั้ง';
            } elseif (str_contains($rawError, 'certificate') || str_contains($rawError, 'SSL')) {
                $errorType = 'การตรวจสอบใบรับรองความปลอดภัย SSL (Firewall SSL Inspection)';
                $hint = 'ระบบได้ตั้งค่าข้ามการตรวจ SSL (http.sslVerify=false) เรียบร้อยแล้ว กรุณากดตรวจสอบอีกครั้ง';
            } elseif (str_contains($rawError, 'Could not resolve host')) {
                $errorType = 'ปัญหา DNS หรือเซิร์ฟเวอร์ไม่สามารถออกอินเทอร์เน็ตได้';
                $hint = 'เซิร์ฟเวอร์ไม่สามารถแปลงชื่อ github.com ได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ต หรืออัปเดตผ่านไฟล์แพตช์ ZIP';
            } elseif (str_contains($rawError, 'Connection timed out') || str_contains($rawError, 'timed out')) {
                $errorType = 'หมดเวลาการเชื่อมต่อ (Connection Timeout)';
                $hint = 'การเชื่อมต่อไปยัง GitHub ใช้เวลานานเกินกำหนด อาจเกิดจาก Firewall โรงพยาบาล แนะนำให้อัปเดตผ่านไฟล์แพตช์ ZIP';
            } elseif (str_contains($rawError, 'Failed to connect') || str_contains($rawError, 'Network is unreachable') || str_contains($rawError, 'Connection refused')) {
                $errorType = 'เครือข่ายเซิร์ฟเวอร์ถูกจำกัดการเข้าถึงอินเทอร์เน็ตภายนอก';
                $hint = 'ไฟร์วอลล์ของ รพ. ไม่อนุญาตให้เซิร์ฟเวอร์เข้าถึง GitHub แนะนำให้อัปเดตผ่านไฟล์แพตช์ ZIP (Manual Offline Patch) ด้านล่าง';
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
                'branch' => $currentBranch ?? 'main',
                'local_commit' => $localCommit,
                'short_local_commit' => !empty($localCommit) ? substr($localCommit, 0, 7) : 'unknown',
                'remote_commit' => '',
                'short_remote_commit' => 'unknown',
                'commits_behind' => 0,
                'commits' => [],
                'has_update' => false,
                'current_version' => $currentVersion,
                'current_build' => config('version.build', '20261001.2'),
                'remote_version' => null,
                'remote_build' => null,
                'remote_release_date' => null,
                'remote_release_name' => null,
                'has_newer_version' => false,
                'cleared_locks_count' => 0,
                'http_fallback' => false,
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
     * Fallback to query latest remote version metadata over raw HTTP/cURL
     */
    protected function fetchRemoteVersionViaHttp(string $targetRepoUrl, string $targetBranch = 'main'): ?array
    {
        try {
            $cleanUrl = preg_replace('/\.git$/', '', trim($targetRepoUrl));
            if (preg_match('#github\.com/([^/]+)/([^/]+)#', $cleanUrl, $m)) {
                $owner = $m[1];
                $repo = $m[2];
                $rawUrl = "https://raw.githubusercontent.com/{$owner}/{$repo}/{$targetBranch}/config/version.php";

                $content = null;
                if (function_exists('curl_init')) {
                    $ch = curl_init($rawUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_USERAGENT, 'THC-Hospital-IT-Platform/SystemUpdate');
                    $content = curl_exec($ch);
                    curl_close($ch);
                }

                if (empty($content) && ini_get('allow_url_fopen')) {
                    $ctx = stream_context_create([
                        'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                        'http' => ['timeout' => 8, 'user_agent' => 'THC-Hospital-IT-Platform/SystemUpdate'],
                    ]);
                    $content = @file_get_contents($rawUrl, false, $ctx);
                }

                if (!empty($content)) {
                    $res = [];
                    if (preg_match("/'version'\s*=>\s*env\(['\"]APP_VERSION['\"],\s*['\"]([^'\"]+)['\"]\)/", $content, $vm) ||
                        preg_match("/'version'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $vm)) {
                        $res['remote_version'] = $vm[1];
                    }
                    if (preg_match("/'build'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $bm)) {
                        $res['remote_build'] = $bm[1];
                    }
                    if (preg_match("/'release_date'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $dm)) {
                        $res['remote_release_date'] = $dm[1];
                    }
                    if (preg_match("/'release_name'\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $nm)) {
                        $res['remote_release_name'] = $nm[1];
                    }
                    return $res;
                }
            }
        } catch (\Throwable $t) {
            // Ignore fallback errors
        }

        return null;
    }

    /**
     * Download and extract release package directly from GitHub via HTTPS (Fail-Safe Direct Engine)
     *
     * @param string $targetBranch Branch name (e.g. 'main')
     * @param callable|null $logCallback Optional logging callback
     * @return array{extracted_count: int, total_bytes: int}
     * @throws Exception
     */
    public function downloadAndApplyReleaseViaHttp(string $targetBranch = 'main', ?callable $logCallback = null): array
    {
        $log = function(string $msg) use ($logCallback) {
            if (is_callable($logCallback)) {
                $logCallback($msg);
            }
        };

        $config = $this->getUpdateConfig();
        $targetRepoUrl = $config['repo_url'];

        $owner = 'monojung';
        $repo = 'it-system-deploy';
        if (preg_match('#github\.com[:/]([^/]+)/([^/\.]+)(?:\.git)?#i', $targetRepoUrl, $m)) {
            $owner = $m[1];
            $repo = $m[2];
        }

        // Candidate download URLs in priority order:
        // 1. update-patch.zip on GitHub raw (lightweight curated patch ~1.4MB)
        // 2. GitHub native repository zipball archive for branch (full source archive)
        $candidates = [
            "https://raw.githubusercontent.com/{$owner}/{$repo}/{$targetBranch}/update-patch.zip",
            "https://github.com/{$owner}/{$repo}/archive/refs/heads/{targetBranch}.zip",
        ];

        $tempZipPath = storage_path('app/temp-update-package.zip');
        @unlink($tempZipPath);

        $downloaded = false;
        $lastError = '';

        foreach ($candidates as $url) {
            $log("กำลังลองดาวน์โหลดจาก: {$url} ...");

            $fp = @fopen($tempZipPath, 'w+');
            if (!$fp) {
                throw new Exception("ไม่สามารถสร้างไฟล์ชั่วคราวสำหรับดาวน์โหลดได้ที่: {$tempZipPath}");
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 180);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'THC-Hospital-IT-Platform/SystemUpdate');

            $proxy = env('HTTPS_PROXY') ?: env('HTTP_PROXY') ?: env('https_proxy') ?: env('http_proxy');
            if ($proxy) {
                curl_setopt($ch, CURLOPT_PROXY, $proxy);
            }

            $success = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            fclose($fp);

            if ($success && $httpCode >= 200 && $httpCode < 300 && file_exists($tempZipPath) && filesize($tempZipPath) > 50000) {
                $downloaded = true;
                $sizeKb = round(filesize($tempZipPath) / 1024, 1);
                $log("ดาวน์โหลดสำเร็จ! ขนาดไฟล์: {$sizeKb} KB (HTTP {$httpCode})");
                break;
            } else {
                $fileSize = file_exists($tempZipPath) ? filesize($tempZipPath) : 0;
                $lastError = "HTTP {$httpCode}, File size: {$fileSize}B" . ($curlError ? ", Error: {$curlError}" : "");
                $log("ดาวน์โหลดจาก URL นี้ไม่สำเร็จ ({$lastError}) -> ลองช่องทางถัดไป...");
                @unlink($tempZipPath);
            }
        }

        if (!$downloaded || !file_exists($tempZipPath)) {
            throw new Exception("ไม่สามารถดาวน์โหลดไฟล์อัปเดตผ่าน HTTPS ได้: {$lastError}");
        }

        // Extract using our robust extractor
        $log("กำลังแตกไฟล์และแทนที่โค้ดระบบ...");
        $extractResult = $this->extractZipArchive(
            $tempZipPath,
            base_path(),
            ['.env', 'storage', 'database/database.sqlite', '.git'],
            $logCallback
        );

        @unlink($tempZipPath);
        $log("แตกไฟล์อัปเดตเสร็จสมบูรณ์ ({$extractResult['extracted_count']} ไฟล์)");

        return $extractResult;
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

        $currentVersion = function_exists('app_version') ? app_version() : config('version.version', '2.5.2');

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
            $hasGit = File::isDirectory(base_path('.git'));
            if (!$hasGit) {
                $appendLog("Notice: ตรวจไม่พบโฟลเดอร์ .git - ระบบจะใช้วิธี HTTPS Direct Archive ในการอัปเดตอัตโนมัติ");
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

            // STEP 3: Git Fetch & Robust Clean Sync to latest release with HTTPS Fail-Safe Fallback
            $updateConfig = $this->getUpdateConfig();
            $targetBranch = $updateConfig['branch'] ?? 'main';
            $remoteName = 'deploy';

            $notify(3, 'ดึงโค้ดล่าสุดและซิงค์ระบบอัตโนมัติ (Resilient Dual-Engine Sync)', "กำลังดึงโค้ดและปรับปรุงโครงสร้างของระบบ");
            $gitOutput = '';
            $gitSuccess = false;

            if ($hasGit) {
                try {
                    $remoteName = $this->ensureUpdateRemote();
                    $remoteRef = "{$remoteName}/{$targetBranch}";

                    // 1. Proactively purge any stale Git lock files before fetch & sync
                    $clearedLocks = $this->clearGitLocks();
                    if (!empty($clearedLocks)) {
                        $appendLog("ปลดล็อก Git อัตโนมัติ: ลบไฟล์ล็อกตกค้าง " . implode(', ', $clearedLocks));
                    }

                    // 2. Fetch latest commits from remote repository (60s timeout for slower network)
                    $this->clearGitLocks();
                    $fetchOutput = $this->runProcess(['git', 'fetch', $remoteName, $targetBranch], 60);
                    if (!empty(trim($fetchOutput))) {
                        $appendLog("Git fetch output:\n" . trim($fetchOutput));
                    }

                    // 3. Configure branch and upstream
                    try {
                        $this->runProcess(['git', 'config', 'pull.rebase', 'false'], 5);
                        $this->runProcess(['git', 'branch', '-M', $targetBranch], 5);
                    } catch (Exception $e) {
                        // non-critical
                    }

                    // 4. Force Checkout & Reset to remote reference
                    $this->clearGitLocks();
                    try {
                        $this->runProcess(['git', 'checkout', '-f', '-B', $targetBranch, $remoteRef], 20);
                    } catch (Exception $coEx) {
                        $appendLog("Checkout notice: " . $coEx->getMessage());
                    }

                    $this->clearGitLocks();
                    $resetOutput = $this->runProcess(['git', 'reset', '--hard', $remoteRef], 30);
                    $appendLog("Git reset output:\n" . trim($resetOutput));

                    // 5. Ensure tracking upstream is set
                    try {
                        $this->runProcess(['git', 'branch', "--set-upstream-to={$remoteRef}", $targetBranch], 10);
                    } catch (Exception $upEx) {
                        // non-critical
                    }

                    $gitOutput = "ซิงค์โค้ดตรงกับ {$remoteRef} ผ่าน Git CLI สำเร็จ (" . trim($resetOutput) . ")";
                    $appendLog("Git output:\n" . $gitOutput);
                    $gitSuccess = true;
                } catch (\Throwable $gitEx) {
                    $this->clearGitLocks();
                    $appendLog("คำสั่ง Git CLI ขัดข้อง ({$gitEx->getMessage()}) -> สลับไปยังระบบ HTTPS Direct Archive อัตโนมัติ 100%...");
                }
            }

            // Fail-Safe Fallback: HTTPS Direct Archive Engine (Runs if Git is absent or failed)
            if (!$gitSuccess) {
                $notify(3, 'ดาวน์โหลดโค้ดผ่านระบบ HTTPS Direct Archive สำรอง', 'กำลังดาวน์โหลดแพ็กเกจล่าสุดจาก GitHub ผ่าน HTTPS...');
                try {
                    $archiveResult = $this->downloadAndApplyReleaseViaHttp($targetBranch, function($msg) use ($appendLog) {
                        $appendLog("HTTPS Fail-Safe: {$msg}");
                    });
                    $gitOutput = "อัปเดตโค้ดระบบสำเร็จผ่านระบบ HTTPS Fail-Safe Direct Archive ({$archiveResult['extracted_count']} ไฟล์, " . number_format($archiveResult['total_bytes'] / 1024, 1) . " KB)";
                    $appendLog($gitOutput);
                } catch (\Throwable $archiveEx) {
                    throw new Exception("ไม่สามารถอัปเดตโค้ดได้ทั้งระบบ Git CLI และ HTTPS Direct: " . $archiveEx->getMessage());
                }
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
                : (function_exists('app_version') ? app_version() : config('version.version', '2.5.2'));

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
     * Prepare environment variables for running processes (ensuring Git paths, HOME and safe flags)
     */
    protected function getProcessEnv(): array
    {
        $systemEnv = [];
        if (function_exists('getenv')) {
            $allEnv = getenv();
            if (is_array($allEnv)) {
                $systemEnv = $allEnv;
            }
        }
        $env = array_merge($systemEnv, $_SERVER, $_ENV);

        $currentPath = $env['PATH'] ?? (getenv('PATH') ?: '');

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
        } else {
            // Linux / Unix: ensure standard binary paths exist without triggering open_basedir check
            $unixPaths = ['/usr/local/bin', '/usr/bin', '/bin', '/usr/sbin', '/sbin'];
            $existingPaths = array_filter(explode(':', $currentPath));
            foreach ($unixPaths as $up) {
                if (!in_array($up, $existingPaths, true)) {
                    $existingPaths[] = $up;
                }
            }
            $currentPath = implode(':', $existingPaths);
        }

        $env['PATH'] = $currentPath;

        // Ensure HOME is defined (critical on Linux PHP-FPM / Apache)
        if (empty($env['HOME'])) {
            $storageHome = storage_path('app');
            $env['HOME'] = @is_dir($storageHome) && @is_writable($storageHome) ? $storageHome : base_path();
        }

        // Pass-through proxy settings if configured in environment
        $proxyKeys = ['HTTP_PROXY', 'HTTPS_PROXY', 'NO_PROXY', 'http_proxy', 'https_proxy', 'no_proxy'];
        foreach ($proxyKeys as $pk) {
            $pval = env($pk, getenv($pk));
            if ($pval) {
                $env[$pk] = $pval;
            }
        }

        // Prevent Git from hanging on interactive prompts and bypass SSL verification issues
        $env['GIT_TERMINAL_PROMPT'] = '0';
        $env['GIT_ASKPASS'] = 'echo';
        $env['SSH_ASKPASS'] = 'echo';
        $env['GIT_SSL_NO_VERIFY'] = '1';

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
        if (!empty($command) && ($command[0] === 'git' || $command[0] === $this->resolveGitBinary())) {
            $gitBinary = $this->resolveGitBinary();
            $subArgs = array_slice($command, 1);

            // Proactively clear stale locks on write commands
            $subCmd = $subArgs[0] ?? '';
            if (in_array($subCmd, ['reset', 'checkout', 'pull', 'fetch', 'merge', 'branch', 'init'], true)) {
                $this->clearGitLocks();
            }

            // Always inject safe.directory=*, http.sslVerify=false, and increased buffers to eliminate proxy/SSL/packet errors
            if (!in_array('safe.directory=*', $subArgs, true)) {
                $command = array_merge(
                    [
                        $gitBinary,
                        '-c', 'safe.directory=*',
                        '-c', 'http.sslVerify=false',
                        '-c', 'http.postBuffer=524288000',
                        '-c', 'core.compression=0',
                    ],
                    $subArgs
                );
            } else {
                $command[0] = $gitBinary;
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
