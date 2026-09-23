<?php
/**
 * init_git.php: สคริปต์ Standalone สำหรับเริ่มต้นและเชื่อมต่อ Git Repository บนเซิร์ฟเวอร์
 * แก้ไขปัญหา "ไม่พบ Git Repository บนเซิร์ฟเวอร์" ในกรณีที่ติดตั้งระบบผ่านไฟล์ ZIP
 * กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน
 */

header('Content-Type: text/html; charset=utf-8');
@ini_set('display_errors', '1');
@error_reporting(E_ALL);
@set_time_limit(300);
@ini_set('max_execution_time', '300');
@ini_set('memory_limit', '256M');

$key = isset($_GET['key']) ? (string)$_GET['key'] : '';
$expectedKey = 'thc11143';

if (empty($key) || !hash_equals($expectedKey, $key)) {
    http_response_code(403);
    die('<!DOCTYPE html><html lang="th"><head><meta charset="utf-8"><title>ปฏิเสธการเข้าถึง</title>
    <style>body{font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:40px 20px;display:flex;justify-content:center;}
    .card{background:#1e293b;border:1px solid #ef4444;border-radius:12px;padding:30px;max-width:600px;width:100%;text-align:center;}
    h2{color:#f87171;margin-top:0;}</style>
    </head><body><div class="card"><h2>⛔ ปฏิเสธการเข้าถึง (Unauthorized)</h2>
    <p style="color:#cbd5e1;">กรุณาระบุ Security Key ใน URL เช่น: <code>init_git.php?key=thc11143</code></p>
    </div></body></html>');
}

$repoUrl = 'https://github.com/monojung/it-system-deploy.git';
$targetDir = __DIR__;
$posixDir = str_replace('\\', '/', $targetDir);

$logs = [];
$hasErrors = false;

// 1. ตรวจสอบ Git Binary
$gitBin = 'git';
$gitCheck = @shell_exec('git --version 2>&1');
if (!$gitCheck || str_contains($gitCheck, 'not recognized') || str_contains($gitCheck, 'not found')) {
    // ตรวจสอบตำแหน่งมาตรฐานบน Windows
    $winPaths = [
        'C:\Program Files\Git\cmd\git.exe',
        'C:\Program Files\Git\bin\git.exe',
        'C:\Git\cmd\git.exe',
        'C:\xampp\git\cmd\git.exe',
    ];
    $found = false;
    foreach ($winPaths as $wp) {
        if (file_exists($wp)) {
            $gitBin = '"' . $wp . '"';
            $gitCheck = @shell_exec($gitBin . ' --version 2>&1');
            $found = true;
            break;
        }
    }
    if (!$found) {
        $hasErrors = true;
        $logs[] = "❌ ไม่พบคำสั่ง 'git' บนเซิร์ฟเวอร์เครื่องนี้\n" .
                  "คำแนะนำ:\n" .
                  "1. หากเซิร์ฟเวอร์ไม่มี Git ให้ใช้วิธี 'อัปเดตระบบด้วยไฟล์แพตช์ ZIP (Manual Patch Upload)' ทางหน้า /system-updates แทนได้ทันที\n" .
                  "2. หากติดตั้ง Git ไว้แล้ว ให้เพิ่ม Path ของ Git ลงใน System PATH ของ Windows หรือระบุ GIT_PATH ใน .env";
    }
}

if (!$hasErrors) {
    $logs[] = "✔ ตรวจพบโปรแกรม Git: " . trim($gitCheck);

    $commands = [
        "1. สร้างโฟลเดอร์ .git (git init)" => "{$gitBin} init",
        "2. อนุญาตความปลอดภัย (safe.directory)" => "{$gitBin} config --global --add safe.directory \"{$posixDir}\"",
        "3. ลบ Remote เก่าถ้ามี" => "{$gitBin} remote remove deploy 2>&1",
        "4. เชื่อมต่อไปยัง Deploy Repository" => "{$gitBin} remote add deploy \"{$repoUrl}\"",
        "5. ดึงข้อมูลโค้ดล่าสุด (git fetch)" => "{$gitBin} fetch deploy main 2>&1",
        "6. กำหนด Branch หลักเป็น main" => "{$gitBin} branch -M main 2>&1",
        "7. ป้องกันปัญหา Divergence (pull.rebase false)" => "{$gitBin} config pull.rebase false 2>&1",
        "8. บังคับเปลี่ยน Branch ไปที่ deploy/main (Checkout Force)" => "{$gitBin} checkout -f -B main deploy/main 2>&1",
        "9. ซิงค์และรีเซ็ตโค้ดให้ตรงกับ Remote (Clean Reset)" => "{$gitBin} reset --hard deploy/main 2>&1",
        "10. ตั้งค่า Upstream Tracking" => "{$gitBin} branch --set-upstream-to=deploy/main main 2>&1",
    ];

    foreach ($commands as $step => $cmd) {
        $cmdOutput = @shell_exec($cmd);
        $trimmed = trim((string)$cmdOutput);
        $logs[] = "[{$step}]\nคำสั่ง: {$cmd}\nผลลัพธ์: " . ($trimmed ?: "(สำเร็จ ไม่มีข้อผิดพลาด)");
    }

    // ตรวจสอบสถานะหลังเชื่อมต่อ
    $statusCheck = @shell_exec("{$gitBin} status --short 2>&1");
    $branchCheck = @shell_exec("{$gitBin} rev-parse --abbrev-ref HEAD 2>&1");
    $commitCheck = @shell_exec("{$gitBin} rev-parse --short HEAD 2>&1");

    $logs[] = "==========================================================\n" .
              "สรุปสถานะ Git:\n" .
              "- Branch: " . trim((string)$branchCheck) . "\n" .
              "- Commit Hash: " . trim((string)$commitCheck) . "\n" .
              "- Remote URL: " . $repoUrl . "\n" .
              "==========================================================";
}

$statusTitle = $hasErrors ? "เกิดข้อผิดพลาดในการเชื่อมต่อ Git" : "เริ่มต้นและเชื่อมต่อ Git Repository สำเร็จแล้ว!";
$themeColor = $hasErrors ? "#ef4444" : "#10b981";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $statusTitle ?> - โรงพยาบาลทุ่งหัวช้าง</title>
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
            <span class="badge"><?= $hasErrors ? 'ไม่พบ Git บนเซิร์ฟเวอร์' : 'เชื่อมต่อ Git สำเร็จ 100%' ?></span>
        </div>

        <h3 style="font-size:14px;color:#38bdf8;margin:16px 0 8px 0;text-transform:uppercase;">📋 รายละเอียดการทำงาน (Execution Log):</h3>
        <pre><?= htmlspecialchars(implode("\n\n", $logs)) ?></pre>

        <div class="actions">
            <?php if ($hasErrors): ?>
                <a href="init_git.php?key=<?= urlencode($key) ?>" class="btn btn-secondary">🔄 ลองใหม่อีกครั้ง (Retry)</a>
            <?php endif; ?>
            <a href="./system-updates" class="btn btn-primary">ไปยังศูนย์ควบคุมการอัปเดตระบบ (System Updates) &rarr;</a>
        </div>
    </div>
</body>
</html>
