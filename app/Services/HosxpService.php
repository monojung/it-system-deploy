<?php

namespace App\Services;

use PDO;
use Exception;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class HosxpService
{
    private ?PDO $pdo = null;
    private array $config;

    public function __construct()
    {
        $this->config = [
            'host' => SystemSetting::get('hosxp_db_host', '192.168.2.10'),
            'port' => SystemSetting::get('hosxp_db_port', '3306'),
            'database' => SystemSetting::get('hosxp_db_name', 'c3thchospital'),
            'username' => SystemSetting::get('hosxp_db_user', 'chang'),
            'password' => SystemSetting::get('hosxp_db_password', 'chang11143'),
        ];
    }

    public function getConnection(): PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->config['host'],
                $this->config['port'],
                $this->config['database']
            );

            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 4,
            ]);
        }

        return $this->pdo;
    }

    public function getPdo(): PDO
    {
        return $this->getConnection();
    }

    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            $pdo = $this->getConnection();
            $latency = round((microtime(true) - $start) * 1000, 2);

            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

            return [
                'success' => true,
                'message' => 'เชื่อมต่อฐานข้อมูล HosXP สำเร็จ',
                'latency_ms' => $latency,
                'version' => $version,
                'database' => $this->config['database'],
                'table_count' => count($tables),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อได้: ' . $e->getMessage(),
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }

    public function getServerMetrics(): array
    {
        try {
            $pdo = $this->getConnection();

            // Fetch server status variables
            $stmt = $pdo->query("SHOW STATUS WHERE Variable_name IN ('Threads_connected', 'Threads_running', 'Uptime', 'Slow_queries', 'Questions')");
            $statusRaw = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            // Calculate human uptime
            $uptimeSeconds = (int)($statusRaw['Uptime'] ?? 0);
            $days = floor($uptimeSeconds / 86400);
            $hours = floor(($uptimeSeconds % 86400) / 3600);
            $minutes = floor(($uptimeSeconds % 3600) / 60);
            $uptimeText = "{$days} วัน {$hours} ชม. {$minutes} นาที";

            return [
                'connected' => true,
                'threads_connected' => (int)($statusRaw['Threads_connected'] ?? 0),
                'threads_running' => (int)($statusRaw['Threads_running'] ?? 0),
                'uptime' => $uptimeText,
                'slow_queries' => (int)($statusRaw['Slow_queries'] ?? 0),
                'total_queries' => (int)($statusRaw['Questions'] ?? 0),
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getStaffFromDatabase(): array
    {
        try {
            // First check if gtwbackoffice.hrd_person is accessible
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=gtwbackoffice;charset=utf8mb4', $this->config['host'], $this->config['port']);
            $pdoBackoffice = new PDO($dsn, $this->config['username'], $this->config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

            $stmt = $pdoBackoffice->query("
                SELECT ID, HR_CID, HR_FNAME, HR_LNAME, HR_PHONE, HR_EMAIL, HR_DEPARTMENT_ID, HR_DEPARTMENT_SUB_ID
                FROM hrd_person
                ORDER BY ID ASC
            ");
            $persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check existing users in it_users to mark if already imported
            $existingCids = User::whereNotNull('cid')->pluck('cid')->toArray();

            $result = [];
            foreach ($persons as $p) {
                $cid = trim((string)$p['HR_CID']);
                $result[] = [
                    'id' => $p['ID'],
                    'cid' => $cid,
                    'name' => $p['HR_FNAME'] . ' ' . $p['HR_LNAME'],
                    'phone' => $p['HR_PHONE'],
                    'email' => $p['HR_EMAIL'],
                    'is_imported' => in_array($cid, $existingCids),
                ];
            }

            return $result;
        } catch (Exception $e) {
            // Return empty if not accessible
            return [];
        }
    }

    public function importStaff(array $cids, string $defaultPassword = 'thc1234'): array
    {
        $importedCount = 0;
        $skippedCount = 0;

        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=gtwbackoffice;charset=utf8mb4', $this->config['host'], $this->config['port']);
            $pdo = new PDO($dsn, $this->config['username'], $this->config['password']);

            // Get all requested staff
            $placeholders = implode(',', array_fill(0, count($cids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM hrd_person WHERE HR_CID IN ($placeholders)");
            $stmt->execute($cids);
            $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $defaultDept = Department::first();

            foreach ($staffList as $staff) {
                $cid = trim((string)$staff['HR_CID']);
                if (empty($cid)) continue;

                // Check if already in it_users
                $exists = User::where('cid', $cid)->exists();
                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // Generate username from cid or name
                $username = 'thc_' . substr($cid, -6);
                // Ensure unique username
                if (User::where('username', $username)->exists()) {
                    $username = 'thc_' . $cid;
                }

                User::create([
                    'name' => $staff['HR_FNAME'] . ' ' . $staff['HR_LNAME'],
                    'username' => $username,
                    'cid' => $cid,
                    'email' => !empty($staff['HR_EMAIL']) ? $staff['HR_EMAIL'] : null,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'user',
                    'department_id' => $defaultDept ? $defaultDept->id : null,
                    'phone' => $staff['HR_PHONE'] ?? null,
                    'is_active' => true,
                ]);

                $importedCount++;
            }

            return [
                'success' => true,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
