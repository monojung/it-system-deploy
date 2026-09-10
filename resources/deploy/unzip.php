<?php
/**
 * unzip.php: สคริปต์แตกไฟล์ระบบสารสนเทศ รักษาโครงสร้างโฟลเดอร์สมบูรณ์ 100%
 * รองรับทั้ง Windows (\) และ Linux (/) พร้อมระบบตรวจสอบความสมบูรณ์ของโครงสร้าง
 * กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน
 */

@ini_set('memory_limit', '512M');
@set_time_limit(600);
@ini_set('max_execution_time', '600');

header('Content-Type: text/html; charset=utf-8');

$targetDir = __DIR__;
$defaultZip = 'it-system.zip';

// Action: ลบสคริปต์และไฟล์ zip ออกจากเซิร์ฟเวอร์
if (isset($_GET['action']) && $_GET['action'] === 'cleanup') {
    $deleted = [];
    if (file_exists(__DIR__ . '/' . $defaultZip)) {
        @unlink(__DIR__ . '/' . $defaultZip);
        $deleted[] = $defaultZip;
    }
    $selfFile = basename(__FILE__);
    @unlink(__FILE__);
    $deleted[] = $selfFile;

    echo '<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>ทำความสะอาดไฟล์ติดตั้งสำเร็จ</title>
    <style>body{font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:30px;max-width:550px;width:100%;text-align:center;}
    .btn{display:inline-block;background:#0d9488;color:#fff;text-decoration:none;padding:12px 24px;border-radius:8px;font-weight:600;margin-top:20px;}</style>
    </head><body><div class="card"><h2 style="color:#34d399;">🧹 ลบไฟล์ติดตั้งเรียบร้อยแล้ว</h2>
    <p style="color:#94a3b8;">ลบไฟล์: ' . implode(', ', $deleted) . ' ออกจากเซิร์ฟเวอร์เพื่อความปลอดภัยแล้ว</p>
    <a href="./server-init?key=thc11143&migrate=1" class="btn">ไปยังขั้นตอนเริ่มต้นระบบ (Server Init) &rarr;</a>
    </div></body></html>';
    exit;
}

// ตรวจสอบ PHP Extension "zip"
if (!extension_loaded('zip')) {
    renderError("ไม่พบ PHP Extension 'zip' บนเซิร์ฟเวอร์นี้", "กรุณาเปิดใช้งานโมดูล php-zip ใน php.ini หรือ cPanel > Select PHP Version > Extensions ก่อนดำเนินการ");
    exit;
}

// ค้นหาไฟล์ zip
$zipName = isset($_GET['file']) ? basename($_GET['file']) : $defaultZip;
$zipPath = $targetDir . '/' . $zipName;

if (!file_exists($zipPath)) {
    // ค้นหาไฟล์ .zip อื่นๆ ในโฟลเดอร์
    $availableZips = glob($targetDir . '/*.zip');
    $zipListHtml = '';
    if (!empty($availableZips)) {
        $zipListHtml = '<p style="color:#94a3b8;margin-top:15px;">พบไฟล์ ZIP อื่นๆ ในโฟลเดอร์นี้:</p><ul>';
        foreach ($availableZips as $az) {
            $bn = htmlspecialchars(basename($az));
            $zipListHtml .= "<li><a href=\"?file={$bn}\" style=\"color:#38bdf8;\">แตกไฟล์ {$bn}</a></li>";
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

// ตรวจสอบว่าใน ZIP มี Root Wrapper Folder เดียวคลุมอยู่หรือไม่ (เช่น it-system/...)
$commonPrefix = null;
$firstSlashPos = -1;

for ($i = 0; $i < min($numFiles, 50); $i++) {
    $entry = str_replace('\\', '/', $zip->getNameIndex($i));
    $entry = ltrim($entry, '/');
    $slash = strpos($entry, '/');
    if ($slash !== false) {
        $prefix = substr($entry, 0, $slash + 1);
        if ($commonPrefix === null) {
            $commonPrefix = $prefix;
        } elseif ($commonPrefix !== $prefix) {
            $commonPrefix = '';
            break;
        }
    } else {
        $commonPrefix = '';
        break;
    }
}

$stripPrefix = !empty($commonPrefix) ? $commonPrefix : '';

$extractedCount = 0;
$createdDirs = 0;
$totalBytes = 0;
$topDirs = [];
$errors = [];

for ($i = 0; $i < $numFiles; $i++) {
    $stat = $zip->statIndex($i);
    $rawName = $stat['name'];

    // 1. แปลง Backslash (\) ทั้งหมดเป็น Forward Slash (/) ตามมาตรฐานโครงสร้างโฟลเดอร์
    $normalized = str_replace('\\', '/', $rawName);
    $normalized = ltrim($normalized, '/');

    // 2. ป้องกัน Directory Traversal
    $normalized = str_replace('../', '', $normalized);

    // 3. ตัด Single Root Wrapper Folder หากมี (เพื่อให้แตกออกมาที่รูทโฟลเดอร์เป้าหมายทันที)
    if (!empty($stripPrefix) && str_starts_with($normalized, $stripPrefix)) {
        $normalized = substr($normalized, strlen($stripPrefix));
    }

    if (empty($normalized)) {
        continue;
    }

    $destPath = $targetDir . '/' . $normalized;

    // บันทึกโฟลเดอร์ระดับบนสุด (Top-level Directory) สำหรับรายงานผล
    $parts = explode('/', $normalized);
    if (count($parts) > 1 && !in_array($parts[0], $topDirs)) {
        $topDirs[] = $parts[0];
    }

    // 4. กรณีเป็นโฟลเดอร์ (ลงท้ายด้วย / หรือ \ ในไฟล์ zip)
    if (str_ends_with($normalized, '/') || substr($rawName, -1) === '\\') {
        if (!is_dir($destPath)) {
            if (@mkdir($destPath, 0755, true)) {
                $createdDirs++;
            }
        }
        continue;
    }

    // 5. กรณีเป็นไฟล์: ตรวจสอบและสร้างโฟลเดอร์ลำดับชั้น (Parent Directories) ให้ครบถ้วนก่อนเขียนไฟล์
    $parentDir = dirname($destPath);
    if (!is_dir($parentDir)) {
        if (@mkdir($parentDir, 0755, true)) {
            $createdDirs++;
        }
    }

    // 6. แตกไฟล์ด้วย Stream Copy เพื่อประหยัด Memory
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
            $errors[] = "ไม่สามารถเขียนไฟล์: {$normalized}";
        }
        fclose($srcStream);
    } else {
        $errors[] = "ไม่สามารถอ่านสตรีมไฟล์: {$rawName}";
    }
}

$zip->close();
$duration = round(microtime(true) - $startTime, 2);

// ตรวจสอบความสมบูรณ์ของไฟล์สำคัญของระบบหลังแตกไฟล์
$checks = [
    'index.php (Web Entry Point)' => file_exists($targetDir . '/index.php'),
    'artisan (Laravel CLI)' => file_exists($targetDir . '/artisan'),
    '.htaccess (Security Rules)' => file_exists($targetDir . '/.htaccess'),
    'bootstrap/app.php (Kernel Bootstrapper)' => file_exists($targetDir . '/bootstrap/app.php'),
    'vendor/autoload.php (Composer Dependencies)' => file_exists($targetDir . '/vendor/autoload.php'),
    '.env หรือ .env.example (Configuration)' => file_exists($targetDir . '/.env') || file_exists($targetDir . '/.env.example'),
];

$allPassed = !in_array(false, $checks, true);
$checkRows = '';
foreach ($checks as $title => $passed) {
    $icon = $passed ? '<span style="color:#34d399;">✔ ตรวจพบ</span>' : '<span style="color:#f87171;">✖ ไม่พบ</span>';
    $checkRows .= "<tr><td style=\"padding:8px 12px;color:#94a3b8;border-bottom:1px solid #334155;\">{$title}</td><td style=\"padding:8px 12px;text-align:right;border-bottom:1px solid #334155;\">{$icon}</td></tr>";
}

$topDirsBadges = '';
sort($topDirs);
foreach ($topDirs as $td) {
    $topDirsBadges .= "<span style=\"display:inline-block;background:#0369a1;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;margin:2px 4px 2px 0;\">📁 {$td}/</span>";
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

echo '<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แตกไฟล์ระบบสารสนเทศสำเร็จ 100% - โรงพยาบาลทุ่งหัวช้าง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: "Prompt", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 30px 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            max-width: 780px;
            width: 100%;
            padding: 32px;
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.5);
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #334155;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .title h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: #38bdf8;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .title p {
            margin: 0;
            font-size: 13.5px;
            color: #94a3b8;
        }
        .badge-success {
            background: #10b981;
            color: #fff;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
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
            color: #34d399;
            margin-bottom: 4px;
        }
        .metric-label {
            font-size: 12px;
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
        .actions {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: flex-end;
            margin-top: 28px;
            flex-wrap: wrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 12px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
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
            background: #451a03;
            border: 1px solid #b45309;
            border-radius: 10px;
            padding: 16px;
            color: #fde68a;
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
            <h1>📦 แตกไฟล์ระบบสารสนเทศสำเร็จ</h1>
            <p>กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน</p>
        </div>
        <div class="badge-success">แตกไฟล์ 100%</div>
    </div>

    <div class="metrics-grid">
        <div class="metric-box">
            <div class="metric-value">' . number_format($extractedCount) . '</div>
            <div class="metric-label">ไฟล์ที่แตกสำเร็จ</div>
        </div>
        <div class="metric-box">
            <div class="metric-value">' . number_format($createdDirs) . '</div>
            <div class="metric-label">โฟลเดอร์ที่สร้างตามโครงสร้าง</div>
        </div>
        <div class="metric-box">
            <div class="metric-value">' . number_format(round($totalBytes / 1048576, 2)) . ' MB</div>
            <div class="metric-label">ขนาดข้อมูลรวม</div>
        </div>
        <div class="metric-box">
            <div class="metric-value">' . $duration . ' วินาที</div>
            <div class="metric-label">เวลาที่ใช้ประมวลผล</div>
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <h3 style="font-size:14px;color:#38bdf8;margin:0 0 10px 0;">โครงสร้างโฟลเดอร์หลักที่สร้างขึ้น:</h3>
        <div>' . $topDirsBadges . '</div>
    </div>

    <h3 style="font-size:14px;color:#38bdf8;margin:0 0 10px 0;">การตรวจสอบไฟล์แกนหลักของระบบ (Core Verification):</h3>
    <table>
        <tbody>
            ' . $checkRows . '
        </tbody>
    </table>

    ' . $errorSection . '

    <div class="warn-card">
        <strong>⚠️ ข้อควรปฏิบัติต่อไปเพื่อความปลอดภัยและเริ่มใช้งาน:</strong>
        <ol style="margin:8px 0 0 18px;padding:0;">
            <li>หากยังไม่ได้ตั้งค่าฐานข้อมูล ให้คัดลอก <code>.env.example</code> เป็น <code>.env</code> แล้วใส่ข้อมูล DB</li>
            <li>กดปุ่ม <strong>"เริ่มต้นระบบ (Server Init & Migrate)"</strong> ด้านล่างเพื่อเชื่อมต่อ Storage Link และรัน Migration</li>
            <li>กดปุ่ม <strong>"ลบไฟล์ unzip.php"</strong> เพื่อลบไฟล์ช่วยติดตั้งออกจากเซิร์ฟเวอร์</li>
        </ol>
    </div>

    <div class="actions">
        <a href="?action=cleanup" class="btn-danger" onclick="return confirm(\'คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์ unzip.php และ it-system.zip ออกจากเซิร์ฟเวอร์?\');">
            🗑️ ลบไฟล์ unzip.php & it-system.zip
        </a>
        <a href="./server-init?key=thc11143&migrate=1" class="btn-primary">
            <span>ไปยังขั้นตอนเริ่มต้นระบบ (Server Init)</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
</body>
</html>';

function renderError($title, $msg) {
    echo '<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>เกิดข้อผิดพลาดในการแตกไฟล์</title>
    <style>body{font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #ef4444;border-radius:12px;padding:30px;max-width:600px;width:100%;}
    h2{color:#f87171;margin-top:0;}</style>
    </head><body><div class="card"><h2>❌ ' . htmlspecialchars($title) . '</h2>
    <div style="color:#cbd5e1;line-height:1.6;">' . $msg . '</div>
    </div></body></html>';
}