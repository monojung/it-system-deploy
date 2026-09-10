<?php
/**
 * server_init.php: สคริปต์ Standalone สำหรับเริ่มต้นระบบและรัน Migration
 * รองรับการทำงานโดยตรงผ่านเบราว์เซอร์ ไม่ต้องพึ่งพา .htaccess rewrite
 */
header('Content-Type: text/html; charset=utf-8');
@ini_set('display_errors', '1');
@ini_set('display_startup_errors', '1');
@error_reporting(E_ALL);
@set_time_limit(300);
@ini_set('max_execution_time', '300');
@ini_set('memory_limit', '256M');

$key = isset($_GET['key']) ? (string)$_GET['key'] : '';
$expectedKey = 'thc11143';

if (empty($key) || !hash_equals($expectedKey, $key)) {
    http_response_code(403);
    die('<div style="font-family:sans-serif;padding:30px;background:#fef2f2;color:#991b1b;border-radius:8px;max-width:650px;margin:50px auto;border:1px solid #f87171;line-height:1.6;">
        <h2 style="margin-top:0;">⛔ ปฏิเสธการเข้าถึง (Unauthorized)</h2>
        <p>กรุณาระบุ Security Key ใน URL เช่น: <code>server_init.php?key=thc11143&migrate=1</code></p>
    </div>');
}

$logs = [];
$hasErrors = false;

// 1. PHP Version & Extensions Check
$phpVersion = PHP_VERSION;
$requiredExtensions = ['pdo_mysql', 'mbstring', 'openssl', 'curl', 'zip', 'json'];
$missingExtensions = [];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}
if (!empty($missingExtensions)) {
    $hasErrors = true;
    $logs[] = "[PHP Extensions Error]: ขาด Extensions ที่จำเป็น: " . implode(', ', $missingExtensions);
} else {
    $logs[] = "[PHP Environment]: PHP {$phpVersion} และ Extensions พร้อมสมบูรณ์";
}

// 2. Prepare Storage & Cache Directories
$storageFolders = [
    __DIR__ . '/storage/app/public',
    __DIR__ . '/storage/framework/cache/data',
    __DIR__ . '/storage/framework/sessions',
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/storage/logs',
    __DIR__ . '/bootstrap/cache',
];
foreach ($storageFolders as $folder) {
    if (!is_dir($folder)) {
        @mkdir($folder, 0777, true);
    }
    @chmod($folder, 0777);
}
$logs[] = "[Storage & Cache Directories]: ตรวจสอบและสร้างโฟลเดอร์สำหรับเขียนข้อมูลเรียบร้อยแล้ว";

// 3. Check and Initialize .env file
$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';
if (!file_exists($envFile) && file_exists($envExample)) {
    @copy($envExample, $envFile);
    $logs[] = "[Environment File]: คัดลอก .env.example เป็น .env เรียบร้อยแล้ว (กรุณาตั้งค่าฐานข้อมูลใน .env หากยังไม่ได้กำหนด)";
}

// 4. Load Laravel Bootstrap
$app = null;
try {
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        throw new Exception("ไม่พบโฟลเดอร์ vendor/autoload.php กรุณาแตกไฟล์ it-system.zip ให้ครบถ้วน");
    }
    require_once __DIR__ . '/vendor/autoload.php';

    if (!file_exists(__DIR__ . '/bootstrap/app.php')) {
        throw new Exception("ไม่พบไฟล์ bootstrap/app.php");
    }
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    // Bind public path
    if (method_exists($app, 'usePublicPath')) {
        $app->usePublicPath(__DIR__);
    }
    
    // Bootstrap console kernel for artisan calls
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    $logs[] = "[Laravel Framework]: โหลดระบบ Laravel " . $app->version() . " สำเร็จ";
} catch (Throwable $e) {
    $hasErrors = true;
    $logs[] = "[Framework Bootstrap Error]: " . $e->getMessage() . " (" . basename($e->getFile()) . ":" . $e->getLine() . ")";
}

// 5. Generate APP_KEY if empty
if ($app) {
    try {
        $appKey = config('app.key');
        if (empty($appKey)) {
            \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
            $logs[] = "[Application Key]: ตรวจพบ APP_KEY ว่างเปล่า -> ได้ทำการ Generate APP_KEY ใหม่และบันทึกลงไฟล์ .env เรียบร้อยแล้ว";
        } else {
            $logs[] = "[Application Key]: ตรวจพบ APP_KEY มีความพร้อมสมบูรณ์";
        }
    } catch (Throwable $e) {
        $logs[] = "[Application Key Warning]: " . $e->getMessage();
    }
}

// 6. Storage Link
if ($app) {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        $output = trim(\Illuminate\Support\Facades\Artisan::output());
        $logs[] = "[Storage Link]: " . ($output ?: 'เชื่อมโยง public/storage เรียบร้อยแล้ว');
    } catch (Throwable $e) {
        $logs[] = "[Storage Link Warning]: " . $e->getMessage();
    }
}

// 7. Optimize Clear
if ($app) {
    try {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $output = trim(\Illuminate\Support\Facades\Artisan::output());
        $logs[] = "[Optimize Clear]:\n" . ($output ?: 'ล้างแคชระบบและ Views สำเร็จ');
    } catch (Throwable $e) {
        $logs[] = "[Optimize Clear Error]: " . $e->getMessage();
    }
}

// 8. Database Check & Migration
$dbStatus = 'ไม่ได้ทดสอบ';
if ($app) {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        $dbStatus = "เชื่อมต่อฐานข้อมูล '{$dbName}' สำเร็จ";
        $logs[] = "[Database Connection]: " . $dbStatus;

        if (isset($_GET['migrate']) && $_GET['migrate'] === '1') {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = trim(\Illuminate\Support\Facades\Artisan::output());
            $logs[] = "[Database Migration]:\n" . ($output ?: 'ตารางฐานข้อมูลเป็นเวอร์ชันล่าสุดแล้ว');
        } else {
            $logs[] = "[Database Migration]: ข้ามขั้นตอน (ระบุ &migrate=1 ใน URL เพื่อสั่งสร้างตาราง)";
        }
    } catch (Throwable $e) {
        $hasErrors = true;
        $dbStatus = "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล";
        $logs[] = "[Database Error]: " . $e->getMessage() . "\nกรุณาตรวจสอบการตั้งค่า DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD ในไฟล์ .env";
    }
}

// 9. Git Repository Check & Initialization
$gitDirExists = is_dir(__DIR__ . '/.git');
if (isset($_GET['init_git']) && $_GET['init_git'] === '1') {
    if ($app) {
        try {
            $updateService = $app->make(\App\Services\SystemUpdateService::class);
            $gitInitRes = $updateService->initializeGitRepository();
            $logs[] = "[Git Repository Initialization]:\n" . $gitInitRes['message'] . "\n" . ($gitInitRes['log'] ?? '');
            $gitDirExists = true;
        } catch (Throwable $e) {
            $logs[] = "[Git Initialization Error]: " . $e->getMessage();
        }
    }
} else {
    if ($gitDirExists) {
        $logs[] = "[Git Repository]: ตรวจพบโฟลเดอร์ .git พร้อมสำหรับการอัปเดตระบบอัตโนมัติ";
    } else {
        $logs[] = "[Git Repository]: ไม่พบโฟลเดอร์ .git (คลิกปุ่ม 'เชื่อมต่อ Git Repository' ด้านล่างเพื่อเริ่มใช้งาน)";
    }
}

$themeColor = $hasErrors ? '#ef4444' : '#10b981';
$statusTitle = $hasErrors ? '⚠️ เริ่มต้นระบบ (มีข้อความแจ้งเตือนหรือข้อผิดพลาด)' : '🚀 เริ่มต้นระบบสำเร็จสมบูรณ์ (Server Initialized)';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Initialization - โรงพยาบาลทุ่งหัวช้าง</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: #0b1120; color: #f1f5f9; padding: 24px 16px; margin: 0; }
        .card { max-width: 850px; margin: 20px auto; background: #1e293b; border-radius: 12px; border: 1px solid #334155; padding: 28px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #334155; padding-bottom: 18px; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        h1 { font-size: 20px; margin: 0 0 6px 0; color: #f8fafc; font-weight: 700; }
        .badge { background: <?= $themeColor ?>; color: #ffffff; padding: 6px 14px; border-radius: 9999px; font-size: 13px; font-weight: 600; }
        pre { background: #0f172a; border: 1px solid #334155; color: #a5f3fc; padding: 16px; border-radius: 8px; font-size: 13px; line-height: 1.5; white-space: pre-wrap; overflow-x: auto; }
        .actions { margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end; flex-wrap: wrap; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #0d9488; color: #ffffff; }
        .btn-primary:hover { background: #0f766e; }
        .btn-secondary { background: #334155; color: #f8fafc; }
        .btn-secondary:hover { background: #475569; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div>
                <h1><?= $statusTitle ?></h1>
                <div style="font-size:13px;color:#94a3b8;">กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน</div>
            </div>
            <span class="badge"><?= $hasErrors ? 'โปรดตรวจสอบข้อผิดพลาด' : 'ผ่านสมบูรณ์' ?></span>
        </div>

        <h3 style="font-size:14px;color:#38bdf8;margin:16px 0 8px 0;text-transform:uppercase;">📋 รายละเอียดการทำงาน (Execution Log):</h3>
        <pre><?= htmlspecialchars(implode("\n\n", $logs)) ?></pre>

        <div class="actions">
            <?php if (!$gitDirExists): ?>
                <a href="server_init.php?key=<?= urlencode($key) ?>&migrate=1&init_git=1" class="btn" style="background:#0284c7;color:#fff;">⚡ เชื่อมต่อ Git Repository</a>
            <?php endif; ?>
            <?php if ($hasErrors): ?>
                <a href="server_init.php?key=<?= urlencode($key) ?>&migrate=1" class="btn btn-secondary">🔄 ลองใหม่อีกครั้ง (Retry)</a>
            <?php endif; ?>
            <a href="login" class="btn btn-primary">เข้าสู่ระบบ (Go to Login) &rarr;</a>
        </div>
    </div>
</body>
</html>
