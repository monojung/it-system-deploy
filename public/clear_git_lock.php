<?php
/**
 * clear_git_lock.php: Standalone Emergency Git Unlock Script
 * ปลดล็อก Git Repository บนเซิร์ฟเวอร์แบบ Standalone (กรณีเกิดข้อผิดพลาด HEAD.lock, index.lock, File exists)
 * กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน
 */

header('Content-Type: text/html; charset=utf-8');
@ini_set('display_errors', '1');
@error_reporting(E_ALL);

$key = isset($_GET['key']) ? (string)$_GET['key'] : '';
$expectedKey = 'thc11143';

if (empty($key) || !hash_equals($expectedKey, $key)) {
    http_response_code(403);
    die('<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>ปฏิเสธการเข้าถึง</title>
    <style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0f172a;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #ef4444;border-radius:12px;padding:30px;max-width:600px;width:100%;text-align:center;}
    h2{color:#f87171;margin-top:0;}</style>
    </head><body><div class="card"><h2>⛔ ปฏิเสธการเข้าถึง (Unauthorized)</h2>
    <p style="color:#cbd5e1;">กรุณาระบุ Security Key ใน URL เช่น: <code>clear_git_lock.php?key=thc11143</code></p>
    </div></body></html>');
}

// Find .git directory
$candidates = [
    __DIR__ . '/.git',
    dirname(__DIR__) . '/.git',
    __DIR__ . '/../.git',
    '/var/www/clients/client3/web3/web/it-system/.git',
];

$gitDir = null;
foreach ($candidates as $candidate) {
    if (is_dir($candidate)) {
        $gitDir = realpath($candidate);
        break;
    }
}

$clearedFiles = [];
$failedFiles = [];
$messages = [];

if (!$gitDir) {
    $messages[] = '⚠️ ไม่พบโฟลเดอร์ .git ในไดเรกทอรีนี้';
} else {
    // 1. Direct top-level lock files
    $topLevelLocks = [
        $gitDir . '/HEAD.lock',
        $gitDir . '/index.lock',
        $gitDir . '/config.lock',
        $gitDir . '/packed-refs.lock',
        $gitDir . '/FETCH_HEAD.lock',
        $gitDir . '/ORIG_HEAD.lock',
        $gitDir . '/COMMIT_EDITMSG.lock',
        $gitDir . '/MERGE_HEAD',
        $gitDir . '/AUTO_MERGE',
    ];

    foreach ($topLevelLocks as $lockFile) {
        if (file_exists($lockFile)) {
            if (@unlink($lockFile)) {
                $clearedFiles[] = basename($lockFile);
            } else {
                $failedFiles[] = basename($lockFile);
            }
        }
    }

    // 2. Scan recursively for any other .lock files
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($gitDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isFile() && str_ends_with(strtolower($item->getFilename()), '.lock')) {
                $realPath = $item->getRealPath();
                if ($realPath && file_exists($realPath)) {
                    $rel = str_replace($gitDir . DIRECTORY_SEPARATOR, '', $realPath);
                    if (!in_array($rel, $clearedFiles, true)) {
                        if (@unlink($realPath)) {
                            $clearedFiles[] = $rel;
                        } else {
                            $failedFiles[] = $rel;
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $messages[] = 'Scan note: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Git Unlock Utility - โรงพยาบาลทุ่งหัวช้าง</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 40px 20px; line-height: 1.6; }
        .card { max-width: 680px; margin: 0 auto; background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.4); }
        h1 { color: #38bdf8; font-size: 22px; margin-top: 0; display: flex; align-items: center; gap: 10px; }
        pre { background: #090d16; border: 1px solid #1e293b; color: #a5f3fc; padding: 18px; border-radius: 8px; font-size: 13.5px; overflow-x: auto; white-space: pre-wrap; }
        .success { color: #34d399; font-weight: bold; }
        .warn { color: #fbbf24; }
        .error { color: #f87171; font-weight: bold; }
        .badge { background: #0d9488; color: #fff; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .btn { display: inline-block; background: #0d9488; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; margin-top: 15px; margin-right: 10px; }
        .btn-sec { background: #334155; border: 1px solid #475569; }
    </style>
</head>
<body>
<div class="card">
    <h1>🔓 สคริปต์ปลดล็อก Git Repository <span class="badge">Standalone</span></h1>
    <p style="color:#94a3b8;">ตรวจสอบและลบไฟล์ล็อกตกค้าง (เช่น <code>HEAD.lock</code>, <code>index.lock</code>) ที่ขัดขวางการอัปเดตระบบ</p>
    <pre><?php
    if ($gitDir) {
        echo "📂 ตำแหน่ง Git Repository: " . htmlspecialchars($gitDir) . "\n\n";
    }

    if (!empty($clearedFiles)) {
        echo '<span class="success">✔ ปลดล็อกสำเร็จ! ได้ทำการลบไฟล์ล็อกตกค้างแล้ว ' . count($clearedFiles) . " ไฟล์:</span>\n";
        foreach ($clearedFiles as $cf) {
            echo "   - .git/" . htmlspecialchars($cf) . "\n";
        }
        echo "\n<span class=\"success\">🚀 ขณะนี้ Git Repository ปลดล็อกเรียบร้อยแล้ว คุณสามารถกลับไปกดอัปเดตระบบได้ทันที!</span>\n";
    } elseif (!empty($failedFiles)) {
        echo '<span class="error">❌ พบไฟล์ล็อกแต่ไม่สามารถลบได้ (ปัญหา Permissions):</span>' . "\n";
        foreach ($failedFiles as $ff) {
            echo "   - .git/" . htmlspecialchars($ff) . "\n";
        }
    } else {
        echo '<span class="success">✔ ตรวจสอบแล้ว: ไม่พบไฟล์ล็อกตกค้างในระบบ (Git Repository พร้อมทำงานตามปกติ)</span>' . "\n";
    }

    if (!empty($messages)) {
        echo "\n" . implode("\n", $messages) . "\n";
    }
    ?></pre>

    <div style="margin-top: 20px;">
        <a href="./system-updates" class="btn">🚀 ไปที่ศูนย์ควบคุมการอัปเดตระบบ (System Updates) &rarr;</a>
        <a href="./login" class="btn btn-sec">เข้าสู่ระบบ (Login)</a>
    </div>
</div>
</body>
</html>
