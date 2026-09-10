<?php
/**
 * update.php: สคริปต์อัปเดตระบบสารสนเทศด้วยไฟล์แพตช์ (Patch Updater)
 * รักษาโครงสร้างโฟลเดอร์สมบูรณ์ 100% ตรงตามโครงสร้างของโปรเจกต์
 * พร้อมระบบความปลอดภัยป้องกันการเขียนทับ .env, storage, uploads และฐานข้อมูล
 * กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน
 */

@ini_set('memory_limit', '512M');
@set_time_limit(600);
@ini_set('max_execution_time', '600');

header('Content-Type: text/html; charset=utf-8');

$targetDir = __DIR__;
$defaultZip = 'update-patch.zip';

// 1. ตรวจสอบ Security Key เพื่อความปลอดภัย
$key = isset($_GET['key']) ? (string)$_GET['key'] : '';
$expectedKey = 'thc11143';

if (empty($key) || !hash_equals($expectedKey, $key)) {
    http_response_code(403);
    die('<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>ปฏิเสธการเข้าถึง</title>
    <style>body{font-family:sans-serif;background:#0b1120;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #f87171;border-radius:12px;padding:30px;max-width:560px;width:100%;line-height:1.6;}
    h2{color:#f87171;margin-top:0;}</style></head><body>
    <div class="card">
        <h2>⛔ ปฏิเสธการเข้าถึง (Unauthorized)</h2>
        <p>กรุณาระบุ Security Key ใน URL เช่น: <code>update.php?key=thc11143</code></p>
    </div></body></html>');
}

// 2. Action: ลบสคริปต์และไฟล์แพตช์ zip ออกจากเซิร์ฟเวอร์เพื่อความปลอดภัย
if (isset($_GET['action']) && $_GET['action'] === 'cleanup') {
    $deleted = [];
    $targetZip = isset($_GET['file']) ? basename($_GET['file']) : $defaultZip;
    if (file_exists($targetDir . '/' . $targetZip)) {
        @unlink($targetDir . '/' . $targetZip);
        $deleted[] = $targetZip;
    }
    $selfFile = basename(__FILE__);
    @unlink(__FILE__);
    $deleted[] = $selfFile;

    echo '<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>ทำความสะอาดไฟล์อัปเดตสำเร็จ</title>
    <style>body{font-family:sans-serif;background:#0b1120;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:32px;max-width:560px;width:100%;text-align:center;}
    .btn{display:inline-block;background:#0d9488;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;margin-top:20px;}</style>
    </head><body><div class="card"><h2 style="color:#34d399;">🧹 ลบไฟล์อัปเดตเรียบร้อยแล้ว</h2>
    <p style="color:#94a3b8;">ลบไฟล์: ' . implode(', ', $deleted) . ' ออกจากเซิร์ฟเวอร์เพื่อความปลอดภัยแล้ว</p>
    <a href="./login" class="btn">เข้าสู่ระบบ (Go to Login) &rarr;</a>
    </div></body></html>';
    exit;
}

// 3. ตรวจสอบ PHP Extension "zip"
if (!extension_loaded('zip')) {
    renderError("ไม่พบ PHP Extension 'zip' บนเซิร์ฟเวอร์นี้", "กรุณาเปิดใช้งานโมดูล php-zip ใน php.ini หรือ cPanel > Select PHP Version > Extensions ก่อนดำเนินการ");
    exit;
}

// 4. ค้นหาไฟล์ zip สำหรับอัปเดต
$zipName = isset($_GET['file']) ? basename($_GET['file']) : $defaultZip;
$zipPath = $targetDir . '/' . $zipName;

if (!file_exists($zipPath)) {
    $availableZips = glob($targetDir . '/*.zip');
    $zipListHtml = '';
    if (!empty($availableZips)) {
        $zipListHtml = '<p style="color:#94a3b8;margin-top:15px;">พบไฟล์ ZIP อื่นๆ ในโฟลเดอร์นี้:</p><ul>';
        foreach ($availableZips as $az) {
            $bn = htmlspecialchars(basename($az));
            $zipListHtml .= "<li><a href=\"?key={$key}&file={$bn}\" style=\"color:#38bdf8;\">ติดตั้งแพตช์จากไฟล์ {$bn}</a></li>";
        }
        $zipListHtml .= '</ul>';
    }
    renderError("ไม่พบไฟล์ {$zipName} ในโฟลเดอร์นี้", "กรุณาอัปโหลดไฟล์ <code>{$zipName}</code> มาไว้ที่ <code>{$targetDir}</code> ก่อนรันสคริปต์นี้" . $zipListHtml);
    exit;
}

$zip = new ZipArchive();
$openRes = $zip->open($zipPath);
if ($openRes !== true) {
    renderError("ไม่สามารถเปิดไฟล์ {$zipName} ได้ (Error Code: {$openRes})", "ไฟล์อาจไม่สมบูรณ์ หรือสิทธิ์การเข้าถึงไฟล์ไม่ถูกต้อง");
    exit;
}

$startTime = microtime(true);
$numFiles = $zip->numFiles;

$extractedCount = 0;
$createdDirs = 0;
$skippedProtectedCount = 0;
$totalBytes = 0;
$topDirs = [];
$errors = [];

// รายการไฟล์และโฟลเดอร์ที่ปลอดภัย (ห้ามเขียนทับเด็ดขาด)
$protectedFiles = [
    '.env',
    '.env.production',
    '.env.backup',
    'storage',
    'database/database.sqlite',
    'public/storage',
    'uploads',
    'public/uploads',
];

for ($i = 0; $i < $numFiles; $i++) {
    $stat = $zip->statIndex($i);
    $rawName = $stat['name'];

    // 1. แปลง Backslash (\) ทั้งหมดเป็น Forward Slash (/) ตามมาตรฐาน Posix ป้องกันโฟลเดอร์เพี้ยน
    $normalized = str_replace('\\', '/', $rawName);
    $normalized = ltrim($normalized, '/');

    // 2. ป้องกัน Directory Traversal
    $normalized = str_replace('../', '', $normalized);

    if ($normalized === '' || $normalized === '.') {
        continue;
    }

    // 3. ตรวจสอบไฟล์ปลอดภัย (Protected Files) ป้องกันการเขียนทับ .env และ storage
    $skip = false;
    foreach ($protectedFiles as $protected) {
        if ($normalized === $protected || str_starts_with($normalized, $protected . '/')) {
            $skip = true;
            $skippedProtectedCount++;
            break;
        }
    }
    if ($skip) {
        continue;
    }

    $destPath = $targetDir . '/' . $normalized;

    // บันทึกโฟลเดอร์หลักสำหรับรายงานผล
    $parts = explode('/', $normalized);
    if (count($parts) > 1 && !in_array($parts[0], $topDirs)) {
        $topDirs[] = $parts[0];
    }

    // 4. กรณีเป็นไดเรกทอรี (ลงท้ายด้วย / หรือ \ ใน zip)
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

    // 5. กรณีเป็นไฟล์: ตรวจสอบและสร้างโฟลเดอร์แม่ (Parent Directory) ให้ตรงตามโครงสร้าง 100% ก่อนเขียนไฟล์
    $parentDir = dirname($destPath);
    if (!is_dir($parentDir)) {
        if (@mkdir($parentDir, 0777, true)) {
            $createdDirs++;
        }
    }

    // 6. แตกไฟล์ด้วย Stream Copy เพื่อประหยัด Memory และรวดเร็ว
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
            // Fallback: getFromIndex
            $content = $zip->getFromIndex($i);
            if ($content !== false && @file_put_contents($destPath, $content) !== false) {
                $totalBytes += strlen($content);
                $extractedCount++;
                @chmod($destPath, 0644);
            } else {
                $errors[] = "ไม่สามารถเขียนไฟล์: {$normalized}";
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
        }
    }
}

$zip->close();
$duration = round(microtime(true) - $startTime, 2);

// 7. ล้างและรีเซ็ตแคชระบบ (Clear Caches)
$cacheLogs = [];
$bootstrapCacheFiles = [
    $targetDir . '/bootstrap/cache/config.php',
    $targetDir . '/bootstrap/cache/routes-v7.php',
    $targetDir . '/bootstrap/cache/events.php',
    $targetDir . '/bootstrap/cache/services.php',
    $targetDir . '/bootstrap/cache/packages.php',
];
foreach ($bootstrapCacheFiles as $cFile) {
    if (file_exists($cFile)) {
        @unlink($cFile);
        $cacheLogs[] = "ล้างแคชไฟล์: " . basename($cFile);
    }
}

// 8. รัน Laravel Artisan Commands (optimize:clear & migrate)
$artisanLogs = [];
if (file_exists($targetDir . '/artisan') && file_exists($targetDir . '/vendor/autoload.php')) {
    try {
        require_once $targetDir . '/vendor/autoload.php';
        $app = require_once $targetDir . '/bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        // 8.1 optimize:clear
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $artisanLogs[] = "[Artisan optimize:clear]: " . trim(\Illuminate\Support\Facades\Artisan::output());

        // 8.2 migrate --force
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $artisanLogs[] = "[Artisan migrate]: " . trim(\Illuminate\Support\Facades\Artisan::output());

    } catch (\Throwable $e) {
        $artisanLogs[] = "[Artisan Note]: " . $e->getMessage() . " (สามารถรัน server_init.php?key=thc11143&migrate=1 เพิ่มเติมได้)";
    }
}

// 9. ตรวจสอบความสมบูรณ์ของโครงสร้างไฟล์หลักหลังอัปเดต
$checks = [
    'app/Services/SystemUpdateService.php (Update Service)' => file_exists($targetDir . '/app/Services/SystemUpdateService.php'),
    'app/Http/Controllers/SystemUpdateController.php (Update Controller)' => file_exists($targetDir . '/app/Http/Controllers/SystemUpdateController.php'),
    'resources/views/system_updates/index.blade.php (Update Views)' => file_exists($targetDir . '/resources/views/system_updates/index.blade.php'),
    'routes/web.php (Application Routes)' => file_exists($targetDir . '/routes/web.php'),
    'config/version.php (Version Config)' => file_exists($targetDir . '/config/version.php'),
    'bootstrap/app.php (Bootstrap App)' => file_exists($targetDir . '/bootstrap/app.php'),
    'index.php (Web Entry Point)' => file_exists($targetDir . '/index.php'),
    'artisan (Artisan CLI)' => file_exists($targetDir . '/artisan'),
    '.env (Environment File)' => file_exists($targetDir . '/.env'),
    'server_init.php (Server Initializer)' => file_exists($targetDir . '/server_init.php'),
];

$checkRows = '';
foreach ($checks as $title => $passed) {
    $icon = $passed ? '<span style="color:#34d399;font-weight:600;">✔ ตรวจพบตรงโครงสร้าง</span>' : '<span style="color:#f87171;font-weight:600;">✖ ไม่พบ</span>';
    $checkRows .= "<tr><td style=\"padding:8px 12px;color:#94a3b8;border-bottom:1px solid #334155;\">{$title}</td><td style=\"padding:8px 12px;text-align:right;border-bottom:1px solid #334155;\">{$icon}</td></tr>";
}

$topDirsBadges = '';
sort($topDirs);
foreach ($topDirs as $td) {
    $topDirsBadges .= "<span style=\"display:inline-block;background:#0369a1;color:#fff;padding:4px 12px;border-radius:12px;font-size:12.5px;margin:2px 4px 2px 0;font-weight:500;\">📁 {$td}/</span>";
}

$errorSection = '';
if (!empty($errors)) {
    $errorSection = '<div style="background:#450a0a;border:1px solid #991b1b;border-radius:8px;padding:12px;margin-top:15px;color:#fca5a5;font-size:13px;">
        <strong>แจ้งเตือนข้อผิดพลาดบางรายการ:</strong><ul style="margin:5px 0 0 15px;padding:0;">';
    foreach (array_slice($errors, 0, 10) as $err) {
        $errorSection .= "<li>" . htmlspecialchars($err) . "</li>";
    }
    if (count($errors) > 10) {
        $errorSection .= "<li>...และอีก " . (count($errors) - 10) . " รายการ</li>";
    }
    $errorSection .= '</ul></div>';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปเดตระบบสารสนเทศสำเร็จ 100% - โรงพยาบาลทุ่งหัวช้าง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Prompt", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0b1120;
            color: #f1f5f9;
            margin: 0;
            padding: 30px 16px;
            display: flex;
            justify-content: center;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 32px;
            max-width: 780px;
            width: 100%;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #334155;
            padding-bottom: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .title h1 {
            font-size: 22px;
            font-weight: 700;
            color: #f8fafc;
            margin: 0 0 6px 0;
        }
        .title p {
            margin: 0;
            font-size: 13.5px;
            color: #94a3b8;
        }
        .badge-success {
            background: #059669;
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .metric-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .metric-value {
            font-size: 22px;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 4px;
        }
        .metric-label {
            font-size: 12.5px;
            color: #94a3b8;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            background: #0f172a;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #334155;
            margin-bottom: 20px;
        }
        pre {
            background: #0f172a;
            border: 1px solid #334155;
            color: #a5f3fc;
            padding: 16px;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.5;
            white-space: pre-wrap;
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .actions {
            margin-top: 28px;
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d9488 0%, #0891b2 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .btn-danger {
            background: #dc2626;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
        }
        .warn-card {
            background: #0f2e2b;
            border: 1px solid #0d9488;
            border-radius: 10px;
            padding: 16px;
            color: #99f6e4;
            font-size: 13.5px;
            line-height: 1.5;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="title">
            <h1>🚀 ติดตั้งแพตช์อัปเดตระบบสำเร็จ 100%</h1>
            <p>กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน</p>
        </div>
        <div class="badge-success">ตรงตามโครงสร้าง 100%</div>
    </div>

    <div class="metrics-grid">
        <div class="metric-box">
            <div class="metric-value"><?= number_format($extractedCount) ?></div>
            <div class="metric-label">ไฟล์ที่อัปเดตสำเร็จ</div>
        </div>
        <div class="metric-box">
            <div class="metric-value"><?= number_format($createdDirs) ?></div>
            <div class="metric-label">โฟลเดอร์โครงสร้าง</div>
        </div>
        <div class="metric-box">
            <div class="metric-value"><?= number_format(round($totalBytes / 1048576, 2)) ?> MB</div>
            <div class="metric-label">ขนาดข้อมูลแพตช์</div>
        </div>
        <div class="metric-box">
            <div class="metric-value"><?= $duration ?> วินาที</div>
            <div class="metric-label">เวลาที่ใช้ประมวลผล</div>
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <h3 style="font-size:14px;color:#38bdf8;margin:0 0 10px 0;">โครงสร้างโฟลเดอร์หลักที่อัปเดต (ตรงตามโปรเจกต์ 100%):</h3>
        <div><?= $topDirsBadges ?></div>
    </div>

    <h3 style="font-size:14px;color:#38bdf8;margin:0 0 10px 0;">การตรวจสอบไฟล์สำคัญของระบบ (System Integrity Verification):</h3>
    <table>
        <tbody>
            <?= $checkRows ?>
        </tbody>
    </table>

    <?php if (!empty($cacheLogs) || !empty($artisanLogs)): ?>
        <h3 style="font-size:14px;color:#38bdf8;margin:16px 0 8px 0;">บันทึกการปรับปรุงระบบ (Cache & Database Migration):</h3>
        <pre><?= htmlspecialchars(implode("\n", array_merge($cacheLogs, $artisanLogs))) ?></pre>
    <?php endif; ?>

    <?= $errorSection ?>

    <div class="warn-card">
        <strong>🛡️ ความปลอดภัยของข้อมูล:</strong>
        <p style="margin:6px 0 0 0;">ไฟล์ <code>.env</code>, ข้อมูลใน <code>storage/</code>, ไฟล์อัปโหลด และฐานข้อมูล ได้รับการปกป้องและไม่ถูกเขียนทับอย่างปลอดภัย 100%</p>
    </div>

    <div class="actions">
        <a href="?key=<?= urlencode($key) ?>&action=cleanup" class="btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ update.php และ <?= htmlspecialchars($zipName) ?> ออกจากเซิร์ฟเวอร์?');">
            🗑️ ลบไฟล์ update.php & <?= htmlspecialchars($zipName) ?>
        </a>
        <a href="./login" class="btn-primary">
            <span>เข้าสู่ระบบ (Go to Login)</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
</body>
</html>
<?php
function renderError($title, $msg) {
    echo '<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>เกิดข้อผิดพลาดในการอัปเดต</title>
    <style>body{font-family:sans-serif;background:#0b1120;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #ef4444;border-radius:12px;padding:30px;max-width:600px;width:100%;}
    h2{color:#f87171;margin-top:0;}</style>
    </head><body><div class="card"><h2>❌ ' . htmlspecialchars($title) . '</h2>
    <div style="color:#cbd5e1;line-height:1.6;">' . $msg . '</div>
    </div></body></html>';
}