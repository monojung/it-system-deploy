<?php
/**
 * clean.php: เครื่องมือทำความสะอาดและบำรุงรักษาพื้นที่เซิร์ฟเวอร์ (IT System Maintenance & Cleaner)
 * กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน
 */

@ini_set('memory_limit', '512M');
@set_time_limit(300);
@ini_set('max_execution_time', '300');
header('Content-Type: text/html; charset=utf-8');

session_start();

$expectedKey = 'thc11143';
$targetDir = __DIR__;
$selfFile = basename(__FILE__);

// ตรวจสอบ Security Key จาก GET, POST หรือ Session
$currentKey = $_GET['key'] ?? $_POST['key'] ?? $_SESSION['clean_auth_key'] ?? '';
$isAuthenticated = ($currentKey === $expectedKey);

if ($isAuthenticated) {
    $_SESSION['clean_auth_key'] = $expectedKey;
}

// Action: Logout / Clear Session
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['clean_auth_key']);
    header('Location: ' . $selfFile);
    exit;
}

// Action: Self-Delete clean.php
if ($isAuthenticated && isset($_POST['action']) && $_POST['action'] === 'self_delete') {
    @unlink(__FILE__);
    unset($_SESSION['clean_auth_key']);
    die('<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>ลบไฟล์ clean.php สำเร็จ</title>
    <style>body{font-family:"Prompt",sans-serif;background:#0f172a;color:#f8fafc;padding:50px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #334155;border-radius:12px;padding:32px;max-width:520px;text-align:center;}
    .btn{display:inline-block;background:#0d9488;color:#fff;text-decoration:none;padding:10px 20px;border-radius:8px;font-weight:600;margin-top:20px;}</style>
    </head><body><div class="card"><h2 style="color:#34d399;margin-top:0;">🧹 ลบไฟล์ clean.php ออกจากเซิร์ฟเวอร์แล้ว</h2>
    <p style="color:#94a3b8;font-size:14px;">ไฟล์สคริปต์นี้ถูกลบเรียบร้อยแล้วเพื่อความปลอดภัยสูงสุดของระบบ</p>
    <a href="./login" class="btn">ไปยังหน้าเข้าสู่ระบบ (Login) &rarr;</a>
    </div></body></html>');
}

// Helper: แปลงขนาดหน่วยไบต์
function formatSizeUnits($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}

// Helper: คำนวณขนาดโฟลเดอร์
function getDirectorySize($dir) {
    $size = 0;
    if (!is_dir($dir)) return 0;
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    } catch (Exception $e) {
        // ignore
    }
    return $size;
}

// Helper: ลบโฟลเดอร์แบบ Recursive พร้อมข้อยกเว้น
function recursiveRemoveDirectory($dir, $excludeList = [], &$deletedFiles = [], &$freedBytes = 0) {
    if (!is_dir($dir)) return;

    $items = @scandir($dir);
    if ($items === false) return;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $relPath = ltrim(str_replace(__DIR__, '', $path), '/\\');
        $relPathNormalized = str_replace('\\', '/', $relPath);

        // ตรวจสอบ Exclude List
        $skip = false;
        foreach ($excludeList as $exc) {
            $excNormalized = str_replace('\\', '/', trim($exc, '/'));
            if ($relPathNormalized === $excNormalized || str_starts_with($relPathNormalized, $excNormalized . '/')) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;

        if (is_dir($path)) {
            recursiveRemoveDirectory($path, $excludeList, $deletedFiles, $freedBytes);
            if (@rmdir($path)) {
                $deletedFiles[] = $relPathNormalized . '/ (โฟลเดอร์)';
            }
        } else {
            $bytes = @filesize($path) ?: 0;
            if (@unlink($path)) {
                $deletedFiles[] = $relPathNormalized;
                $freedBytes += $bytes;
            }
        }
    }
}

// ประมวลผลคำสั่งทำความสะอาด
$actionResult = null;
if ($isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';
    $deletedFiles = [];
    $freedBytes = 0;
    $actionTitle = '';
    $actionMsg = '';

    if ($mode === 'clean_cache') {
        // โหมด 1: ล้างแคชและ Log เก่า
        $actionTitle = 'ล้างแคชระบบและไฟล์ชั่วคราวเรียบร้อยแล้ว';
        
        // 1.1 Bootstrap Cache
        $bCache = $targetDir . '/bootstrap/cache';
        if (is_dir($bCache)) {
            foreach (glob($bCache . '/*.php') as $cf) {
                $bytes = @filesize($cf) ?: 0;
                if (@unlink($cf)) {
                    $deletedFiles[] = 'bootstrap/cache/' . basename($cf);
                    $freedBytes += $bytes;
                }
            }
        }

        // 1.2 Storage Framework Cache / Views / Sessions
        $storagePaths = [
            $targetDir . '/storage/framework/cache/data',
            $targetDir . '/storage/framework/views',
        ];
        foreach ($storagePaths as $sp) {
            if (is_dir($sp)) {
                recursiveRemoveDirectory($sp, [], $deletedFiles, $freedBytes);
            }
        }

        // 1.3 ล้างไฟล์ Logs ใน storage/logs
        $logDir = $targetDir . '/storage/logs';
        if (is_dir($logDir)) {
            foreach (glob($logDir . '/*.log') as $lf) {
                $bytes = @filesize($lf) ?: 0;
                // ตัดทอนขนาดไฟล์ log ให้เป็น 0 แทนการลบเพื่อป้องกันสิทธิ์ permission
                if (@file_put_contents($lf, '') !== false) {
                    $deletedFiles[] = 'storage/logs/' . basename($lf) . ' (ล้างเนื้อหา)';
                    $freedBytes += $bytes;
                }
            }
        }

        $actionMsg = "ล้างแคช Framework, View Templates และตัดทอนไฟล์ Log ทั้งหมดเพื่อคืนพื้นที่สำเร็จ";

    } elseif ($mode === 'clean_artifacts') {
        // โหมด 2: ลบไฟล์ติดตั้งและ ZIP ที่ใช้งานเสร็จแล้ว
        $actionTitle = 'ทำความสะอาดไฟล์ติดตั้งและแพตช์ ZIP สำเร็จ';
        $artifacts = [
            'unzip.php',
            'server_init.php',
            'update.php',
            'init_git.php',
            'info_test.php',
            'DEPLOY_MANUAL_PHP8.1.txt',
            'DEPLOY_MANUAL_PHP8.2.txt',
            'คู่มือการติดตั้ง_PHP8.1.txt',
            'คู่มือการอัปเดตระบบ_PATCH.txt',
            'คู่มือการอัปโหลดผ่าน_WinSCP.txt',
        ];

        // ลบไฟล์ติดตั้งสคริปต์
        foreach ($artifacts as $art) {
            $p = $targetDir . '/' . $art;
            if (file_exists($p)) {
                $bytes = @filesize($p) ?: 0;
                if (@unlink($p)) {
                    $deletedFiles[] = $art;
                    $freedBytes += $bytes;
                }
            }
        }

        // ลบไฟล์ ZIP ทั้งหมดในไดเรกทอรีนี้ (ถ้าเลือกให้ลบ zip)
        if (!empty($_POST['include_zips'])) {
            foreach (glob($targetDir . '/*.zip') as $zf) {
                $bytes = @filesize($zf) ?: 0;
                if (@unlink($zf)) {
                    $deletedFiles[] = basename($zf);
                    $freedBytes += $bytes;
                }
            }
        }

        // ลบไฟล์ clean.php ตัวเองหากผู้ใช้ระบุ
        if (!empty($_POST['delete_self'])) {
            @unlink(__FILE__);
            unset($_SESSION['clean_auth_key']);
        }

        $actionMsg = "ลบไฟล์ติดตั้งและเอกสารคู่มือออกจากเซิร์ฟเวอร์เรียบร้อยแล้วเพื่อความปลอดภัย";

    } elseif ($mode === 'wipe_fresh') {
        // โหมด 3: ล้างพื้นที่เพื่อเตรียมติดตั้งใหม่ (Factory Reset Prep)
        $confirmText = trim($_POST['confirm_text'] ?? '');
        if ($confirmText !== 'WIPE') {
            $actionResult = [
                'success' => false,
                'title' => 'การยืนยันไม่ถูกต้อง',
                'message' => 'กรุณาพิมพ์คำว่า WIPE เพื่อยืนยันการล้างไฟล์ระบบทั้งหมด',
                'deleted' => [],
                'freed' => 0
            ];
        } else {
            $actionTitle = 'ล้างไฟล์ระบบเพื่อเตรียมติดตั้งใหม่เรียบร้อยแล้ว';
            $keepEnv = !empty($_POST['keep_env']);
            $keepStorage = !empty($_POST['keep_storage']);

            $exclude = [$selfFile];
            if ($keepEnv) {
                $exclude[] = '.env';
                $exclude[] = '.env.example';
            }
            if ($keepStorage) {
                $exclude[] = 'storage';
            }

            recursiveRemoveDirectory($targetDir, $exclude, $deletedFiles, $freedBytes);
            $actionMsg = "ระบบได้ทำการล้างไฟล์และโฟลเดอร์เดิมออกเรียบร้อยแล้ว เพื่อให้พร้อมสำหรับการแตกไฟล์ zip ติดตั้งใหม่";
        }
    }

    if ($actionResult === null) {
        $actionResult = [
            'success' => true,
            'title' => $actionTitle,
            'message' => $actionMsg,
            'deleted' => $deletedFiles,
            'freed' => $freedBytes,
        ];
    }
}

// รวบรวมข้อมูลสถานะเซิร์ฟเวอร์
$freeDiskSpace = @disk_free_space($targetDir);
$totalDiskSpace = @disk_total_space($targetDir);
$usedDiskSpace = ($totalDiskSpace && $freeDiskSpace) ? ($totalDiskSpace - $freeDiskSpace) : 0;
$diskPercent = ($totalDiskSpace > 0) ? round(($usedDiskSpace / $totalDiskSpace) * 100, 1) : 0;

// ตรวจสอบไฟล์ติดตั้งที่ยังหลงเหลือ
$foundArtifacts = [];
$checkFiles = [
    'unzip.php' => 'สคริปต์แตกไฟล์ระบบ',
    'server_init.php' => 'สคริปต์เริ่มต้นระบบ (Server Init)',
    'update.php' => 'สคริปต์อัปเดตระบบด้วย patch',
    'init_git.php' => 'สคริปต์เชื่อมต่อ Git',
    'info_test.php' => 'สคริปต์ตรวจสอบ PHP Info',
];
foreach ($checkFiles as $cf => $desc) {
    if (file_exists($targetDir . '/' . $cf)) {
        $foundArtifacts[] = ['file' => $cf, 'desc' => $desc, 'size' => formatSizeUnits(filesize($targetDir . '/' . $cf))];
    }
}
$foundZips = [];
foreach (glob($targetDir . '/*.zip') as $zf) {
    $foundZips[] = ['file' => basename($zf), 'size' => formatSizeUnits(filesize($zf))];
}

// ตรวจสอบแคชระบบ
$cacheSize = getDirectorySize($targetDir . '/bootstrap/cache') +
             getDirectorySize($targetDir . '/storage/framework/cache') +
             getDirectorySize($targetDir . '/storage/framework/views');
$logsSize = getDirectorySize($targetDir . '/storage/logs');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ศูนย์ทำความสะอาดและบำรุงรักษาเซิร์ฟเวอร์ - รพ.ทุ่งหัวช้าง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --bg-body: #0b1120;
            --bg-card: #1e293b;
            --bg-card-sub: #0f172a;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #0d9488;
            --primary-hover: #0f766e;
            --accent: #0284c7;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --warning: #f59e0b;
            --success: #10b981;
        }

        * { box-sizing: border-box; }
        body {
            font-family: "Prompt", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 30px 16px;
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            width: 100%;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
            margin-bottom: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .header-title h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: #f8fafc;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title p {
            margin: 0;
            font-size: 13.5px;
            color: var(--text-muted);
        }

        .badge-pulse {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(13, 148, 136, 0.2);
            color: #2dd4bf;
            border: 1px solid rgba(45, 212, 191, 0.3);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12.5px;
            font-weight: 600;
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .metric-box {
            background: var(--bg-card-sub);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 14px;
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
            color: var(--text-muted);
        }

        /* Actions Section */
        .action-card {
            background: var(--bg-card-sub);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            transition: all 0.2s ease;
        }
        .action-card:hover {
            border-color: #475569;
        }

        .action-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 12px;
        }

        .action-title {
            font-size: 16px;
            font-weight: 600;
            color: #f1f5f9;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); transform: translateY(-1px); }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { background: #0369a1; transform: translateY(-1px); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: var(--danger-hover); transform: translateY(-1px); }
        .btn-secondary { background: #334155; color: #fff; }
        .btn-secondary:hover { background: #475569; }

        .auth-card {
            max-width: 440px;
            margin: 40px auto;
            text-align: center;
        }
        .input-group {
            margin: 20px 0;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 8px;
        }
        .input-field {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            color: #fff;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
        }
        .input-field:focus { border-color: var(--primary); }

        pre {
            background: #090d16;
            border: 1px solid #1e293b;
            color: #a5f3fc;
            padding: 16px;
            border-radius: 8px;
            font-size: 13px;
            white-space: pre-wrap;
            max-height: 280px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
<div class="container">

<?php if (!$isAuthenticated): ?>
    <!-- หน้าจอ Login Security Key -->
    <div class="card auth-card">
        <div style="font-size: 40px; margin-bottom: 12px;">🛡️</div>
        <h2 style="font-size: 20px; font-weight: 700; margin: 0 0 6px 0;">การยืนยันตัวตนเพื่อบำรุงรักษา</h2>
        <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">กรุณาระบุ Security Key เพื่อเข้าสู่หน้าเครื่องมือ clean.php</p>

        <form method="POST" action="">
            <div class="input-group">
                <label for="key">Security Key:</label>
                <input type="password" name="key" id="key" class="input-field" placeholder="ระบุคีย์ความปลอดภัย (เช่น thc11143)" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
                <i class="bi bi-shield-lock-fill"></i> เข้าสู่ระบบบำรุงรักษา
            </button>
        </form>
        <div style="margin-top: 16px; font-size: 12px; color: #64748b;">กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง</div>
    </div>

<?php else: ?>

    <!-- หน้า Dashboard บำรุงรักษาเซิร์ฟเวอร์ -->
    <div class="card">
        <div class="header">
            <div class="header-title">
                <h1><i class="bi bi-trash3-fill" style="color: #38bdf8;"></i> ศูนย์ทำความสะอาดและบำรุงรักษาเซิร์ฟเวอร์</h1>
                <p>กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน | ไดเรกทอรี: <code><?= htmlspecialchars($targetDir) ?></code></p>
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <span class="badge-pulse"><i class="bi bi-shield-check"></i> ได้รับอนุญาตแล้ว</span>
                <a href="?action=logout" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;"><i class="bi bi-box-arrow-right"></i> ออก</a>
            </div>
        </div>

        <?php if ($actionResult): ?>
            <!-- แจ้งผลการทำงาน -->
            <div style="background: <?= $actionResult['success'] ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' ?>; border: 1px solid <?= $actionResult['success'] ? '#10b981' : '#ef4444' ?>; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                <h3 style="margin: 0 0 8px 0; color: <?= $actionResult['success'] ? '#34d399' : '#f87171' ?>; font-size: 16px;">
                    <i class="bi <?= $actionResult['success'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                    <?= htmlspecialchars($actionResult['title']) ?>
                </h3>
                <p style="margin: 0 0 12px 0; font-size: 13.5px; color: #cbd5e1;"><?= htmlspecialchars($actionResult['message']) ?></p>
                <?php if (!empty($actionResult['deleted'])): ?>
                    <div style="font-size: 12.5px; color: #94a3b8; margin-bottom: 6px;">
                        คืนพื้นที่ได้: <strong style="color: #38bdf8;"><?= formatSizeUnits($actionResult['freed']) ?></strong> | ลบ/ล้างข้อมูลทั้งหมด: <?= count($actionResult['deleted']) ?> รายการ
                    </div>
                    <pre><?= htmlspecialchars(implode("\n", $actionResult['deleted'])) ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- สถิติพื้นที่จัดเก็บ -->
        <div class="metrics-grid">
            <div class="metric-box">
                <div class="metric-value"><?= formatSizeUnits($freeDiskSpace) ?></div>
                <div class="metric-label">พื้นที่ว่างบน Disk (Free Space)</div>
            </div>
            <div class="metric-box">
                <div class="metric-value"><?= formatSizeUnits($cacheSize) ?></div>
                <div class="metric-label">แคชระบบ (Framework & Views)</div>
            </div>
            <div class="metric-box">
                <div class="metric-value"><?= formatSizeUnits($logsSize) ?></div>
                <div class="metric-label">ขนาดไฟล์ Logs ทั้งหมด</div>
            </div>
            <div class="metric-box">
                <div class="metric-value"><?= count($foundZips) ?> ไฟล์</div>
                <div class="metric-label">ไฟล์ ZIP ในโฟลเดอร์นี้</div>
            </div>
        </div>

        <!-- รายการปฏิบัติการ (Cleaning Modes) -->
        <h3 style="font-size: 15px; color: #38bdf8; margin: 0 0 14px 0; display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-gear-wide-connected"></i> เลือกรูปแบบการทำความสะอาด:
        </h3>

        <!-- Mode 1: Clean Cache & Logs -->
        <div class="action-card">
            <div class="action-header">
                <div class="action-title"><i class="bi bi-stars" style="color: #38bdf8;"></i> 1. ล้างแคชระบบและไฟล์ Log ชั่วคราว (Safe Cleanup)</div>
                <form method="POST" onsubmit="return confirmAction(event, 'ยืนยันการล้างแคชและ Log?', 'ระบบจะล้าง bootstrap cache, view cache, และตัดทอนไฟล์ log โดยไม่กระทบฐานข้อมูล');">
                    <input type="hidden" name="mode" value="clean_cache">
                    <button type="submit" class="btn btn-accent"><i class="bi bi-stars"></i> ล้างแคชและ Log ทันที</button>
                </form>
            </div>
            <div class="action-desc">
                ลบไฟล์แคชทั้งหมดใน <code>bootstrap/cache/</code>, <code>storage/framework/views/</code>, <code>storage/framework/cache/</code> และล้างไฟล์บันทึกใน <code>storage/logs/*.log</code> เพื่อคืนพื้นที่และแก้ปัญหา Cache ค้างอย่างปลอดภัย
            </div>
        </div>

        <!-- Mode 2: Clean Setup Artifacts -->
        <div class="action-card">
            <div class="action-header">
                <div class="action-title"><i class="bi bi-shield-x" style="color: #f59e0b;"></i> 2. ลบไฟล์ติดตั้งและสคริปต์แพตช์ (Setup Artifacts Cleanup)</div>
                <form method="POST" id="formArtifacts">
                    <input type="hidden" name="mode" value="clean_artifacts">
                    <input type="hidden" name="include_zips" id="include_zips" value="0">
                    <input type="hidden" name="delete_self" id="delete_self" value="0">
                    <button type="button" onclick="confirmCleanArtifacts()" class="btn btn-primary">
                        <i class="bi bi-trash3"></i> ทำความสะอาดไฟล์ติดตั้ง
                    </button>
                </form>
            </div>
            <div class="action-desc">
                ลบสคริปต์ติดตั้งที่ทำงานเสร็จแล้วออกจากเซิร์ฟเวอร์เพื่อความปลอดภัย (เช่น <code>unzip.php</code>, <code>update.php</code>, <code>server_init.php</code>, <code>init_git.php</code>, และคู่มือติดตั้ง)
                <div style="margin-top: 10px; font-size: 13px;">
                    <?php if (!empty($foundArtifacts)): ?>
                        <span style="color: #fbbf24;">ตรวจพบไฟล์ติดตั้ง:</span>
                        <?php foreach ($foundArtifacts as $fa): ?>
                            <span style="background: #334155; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 4px;"><?= $fa['file'] ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color: #34d399;">✔ ไม่พบไฟล์ติดตั้งตกค้างในโฟลเดอร์นี้</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mode 3: Factory Reset Prep -->
        <div class="action-card" style="border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.04);">
            <div class="action-header">
                <div class="action-title" style="color: #f87171;"><i class="bi bi-exclamation-triangle-fill" style="color: #ef4444;"></i> 3. ล้างไฟล์ระบบทั้งหมดเพื่อเตรียมติดตั้งใหม่ (Factory Reset Prep)</div>
                <button type="button" onclick="confirmFactoryWipe()" class="btn btn-danger">
                    <i class="bi bi-fire"></i> ล้างพื้นที่เตรียมติดตั้งใหม่
                </button>
            </div>
            <div class="action-desc" style="color: #fca5a5;">
                <strong>⚠️ คำเตือนความปลอดภัยสูง:</strong> ลบไฟล์และโฟลเดอร์ทั้งหมดในโปรเจกต์ เพื่อเตรียมพื้นที่ให้ว่างเปล่าสำหรับการแตกไฟล์ <code>it-system.zip</code> ใหม่ โดยระบบจะเก็บไฟล์ <code>.env</code> และโฟลเดอร์ <code>storage/</code> ไว้ให้โดยอัตโนมัติ (เว้นแต่จะระบุให้ลบทั้งหมด)
            </div>
        </div>

        <!-- Self-Destruct clean.php -->
        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 13px; color: var(--text-muted);">
                เมื่อบำรุงรักษาเสร็จแล้ว แนะนำให้ลบไฟล์ <code>clean.php</code> ออกจากเซิร์ฟเวอร์
            </div>
            <form method="POST" onsubmit="return confirmAction(event, 'ลบไฟล์ clean.php ทันที?', 'สคริปต์นี้จะถูกลบออกจากเซิร์ฟเวอร์ทันทีเพื่อความปลอดภัย');">
                <input type="hidden" name="action" value="self_delete">
                <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 13px;">
                    <i class="bi bi-trash-fill"></i> ลบสคริปต์ clean.php ทิ้งทันที
                </button>
            </form>
        </div>
    </div>

    <!-- Hidden Form for Factory Wipe -->
    <form method="POST" id="formFactoryWipe" style="display: none;">
        <input type="hidden" name="mode" value="wipe_fresh">
        <input type="hidden" name="confirm_text" id="wipe_confirm_text">
        <input type="hidden" name="keep_env" id="wipe_keep_env" value="1">
        <input type="hidden" name="keep_storage" id="wipe_keep_storage" value="1">
    </form>

<?php endif; ?>

</div>

<script>
function confirmAction(e, title, text) {
    e.preventDefault();
    const form = e.target;
    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d9488',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ยืนยันดำเนินการ',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
    return false;
}

function confirmCleanArtifacts() {
    Swal.fire({
        title: 'ทำความสะอาดไฟล์ติดตั้ง?',
        html: `<div style="text-align: left; font-size: 13.5px; line-height: 1.6; color: #334155;">
               ระบบจะลบไฟล์ติดตั้ง (unzip.php, update.php, server_init.php, init_git.php และคู่มือ)<br><br>
               <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px;">
                   <input type="checkbox" id="chk_zips" checked> ลบไฟล์ ZIP ทั้งหมดในโฟลเดอร์นี้ด้วย (it-system*.zip, update-patch*.zip)
               </label>
               <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #ef4444;">
                   <input type="checkbox" id="chk_self"> ลบไฟล์ clean.php นี้ด้วยเมื่อเสร็จสิ้น
               </label>
               </div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0d9488',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'เริ่มทำความสะอาด',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('include_zips').value = document.getElementById('chk_zips').checked ? '1' : '0';
            document.getElementById('delete_self').value = document.getElementById('chk_self').checked ? '1' : '0';
            document.getElementById('formArtifacts').submit();
        }
    });
}

function confirmFactoryWipe() {
    Swal.fire({
        title: '⚠️ ยืนยันการล้างพื้นที่ทั้งหมด?',
        html: `<div style="text-align: left; font-size: 13px; line-height: 1.6; color: #334155;">
               <div style="background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 12px; border: 1px solid #fca5a5;">
                   <strong>คำเตือน:</strong> ไฟล์โปรเจกต์ทั้งหมดจะถูกลบเพื่อเตรียมพื้นที่ติดตั้งใหม่!
               </div>
               <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 8px; font-weight: 600; color: #059669;">
                   <input type="checkbox" id="wipe_env" checked> เก็บไฟล์ .env (การตั้งค่าฐานข้อมูล) ไว้
               </label>
               <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin-bottom: 14px; font-weight: 600; color: #059669;">
                   <input type="checkbox" id="wipe_storage" checked> เก็บโฟลเดอร์ storage/ (ไฟล์อัปโหลดเดิม) ไว้
               </label>
               พิมพ์คำว่า <strong>WIPE</strong> ด้านล่างเพื่อยืนยัน:
               <input type="text" id="wipe_input" class="swal2-input" placeholder="พิมพ์ WIPE ที่นี่" style="text-transform: uppercase;">
               </div>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'ล้างไฟล์ระบบทันที',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        preConfirm: () => {
            const input = document.getElementById('wipe_input').value.trim();
            if (input !== 'WIPE') {
                Swal.showValidationMessage('กรุณาพิมพ์คำว่า WIPE ให้ถูกต้องเพื่อยืนยัน');
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('wipe_confirm_text').value = 'WIPE';
            document.getElementById('wipe_keep_env').value = document.getElementById('wipe_env').checked ? '1' : '0';
            document.getElementById('wipe_keep_storage').value = document.getElementById('wipe_storage').checked ? '1' : '0';
            document.getElementById('formFactoryWipe').submit();
        }
    });
}
</script>
</body>
</html>
