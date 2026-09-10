<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class ServerInitController extends Controller
{
    /**
     * Handle initial server setup (storage link, cache clearing, optional migration).
     */
    public function init(Request $request)
    {
        $expectedKey = env('SERVER_INIT_KEY', 'thc11143');
        $providedKey = $request->query('key');

        if (empty($providedKey) || !hash_equals((string)$expectedKey, (string)$providedKey)) {
            return response()->make(
                $this->renderHtml(
                    '⛔ ปฏิเสธการเข้าถึง (Unauthorized)',
                    'error',
                    [
                        'ข้อความแจ้งเตือน' => 'Security Key ไม่ถูกต้อง หรือไม่ได้ระบุในคำขอ',
                        'คำแนะนำ' => 'กรุณาระบุคีย์ยืนยันความปลอดภัยใน URL เช่น: <code>/server-init?key=thc11143</code>',
                    ],
                    ''
                ),
                403,
                ['Content-Type' => 'text/html; charset=utf-8']
            );
        }

        $logs = [];
        $hasErrors = false;

        // 1. Storage Link
        $storageLinkStatus = 'สำเร็จ (Linked)';
        try {
            $publicStorage = public_path('storage');
            $targetStorage = storage_path('app/public');

            if (!File::exists($targetStorage)) {
                File::makeDirectory($targetStorage, 0777, true, true);
            }

            Artisan::call('storage:link');
            $linkOutput = trim(Artisan::output());
            $logs[] = "[Storage Link]: " . ($linkOutput ?: 'เชื่อมโยง public/storage เรียบร้อยแล้ว');
        } catch (Throwable $e) {
            $storageLinkStatus = 'แจ้งเตือน (Symlink Exception)';
            $logs[] = "[Storage Link Warning]: " . $e->getMessage() . " (หากเซิร์ฟเวอร์ปิดฟังก์ชัน symlink กรุณาตรวจสอบการเข้าถึง storage ผ่าน Document Root)";
        }

        // 2. Optimize Clear
        $optimizeStatus = 'สำเร็จ (Cleared)';
        try {
            Artisan::call('optimize:clear');
            $clearOutput = trim(Artisan::output());
            $logs[] = "[Optimize Clear]:\n" . ($clearOutput ?: 'ล้างแคชการตั้งค่าและ View สำเร็จ');
        } catch (Throwable $e) {
            $optimizeStatus = 'ข้อผิดพลาด (Failed)';
            $hasErrors = true;
            $logs[] = "[Optimize Clear Error]: " . $e->getMessage();
        }

        // 3. Database Check & Optional Migration
        $dbStatus = 'ตรวจสอบสำเร็จ (Connected)';
        $migrateStatus = 'ข้าม (ไม่ได้ระบุ &migrate=1)';
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $logs[] = "[Database Connection]: เชื่อมต่อฐานข้อมูล '{$dbName}' สำเร็จ";

            if ($request->query('migrate') === '1') {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = trim(Artisan::output());
                $migrateStatus = 'สำเร็จ (Migrated)';
                $logs[] = "[Database Migration]:\n" . ($migrateOutput ?: 'ตารางฐานข้อมูลเป็นเวอร์ชันล่าสุดแล้ว');
            }

            if ($request->query('seed') === '1') {
                Artisan::call('db:seed', ['--force' => true]);
                $seedOutput = trim(Artisan::output());
                $logs[] = "[Database Seeder]:\n" . ($seedOutput ?: 'ข้อมูลเริ่มต้นนำเข้าสำเร็จ');
            }
        } catch (Throwable $e) {
            $dbStatus = 'ข้อผิดพลาด (Connection Error)';
            $hasErrors = true;
            $logs[] = "[Database Error]: " . $e->getMessage();
        }

        $summary = [
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Storage Link' => $storageLinkStatus,
            'Optimize Clear' => $optimizeStatus,
            'Database' => $dbStatus,
            'Migration' => $migrateStatus,
        ];

        $html = $this->renderHtml(
            '🚀 เริ่มต้นระบบสารสนเทศ (Server Initialized)',
            $hasErrors ? 'warning' : 'success',
            $summary,
            implode("\n\n", $logs)
        );

        return response()->make($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * Render modern hospital IT status HTML template.
     */
    private function renderHtml(string $title, string $type, array $summary, string $logContent): string
    {
        $badgeColor = ($type === 'success') ? '#10b981' : (($type === 'warning') ? '#f59e0b' : '#ef4444');
        $badgeText = ($type === 'success') ? 'ระบบพร้อมใช้งาน' : (($type === 'warning') ? 'มีข้อควรระวัง' : 'การเข้าถึงถูกระงับ');

        $rowsHtml = '';
        foreach ($summary as $label => $value) {
            $rowsHtml .= "<tr><td style=\"padding:8px 12px;font-weight:600;color:#94a3b8;width:180px;border-bottom:1px solid #334155;\">{$label}</td><td style=\"padding:8px 12px;color:#f1f5f9;border-bottom:1px solid #334155;\">{$value}</td></tr>";
        }

        $logSection = '';
        if (!empty($logContent)) {
            $logSection = "<h3 style=\"font-size:14px;color:#38bdf8;margin:24px 0 8px 0;text-transform:uppercase;letter-spacing:0.5px;\">📋 รายละเอียดการทำงาน (Execution Log):</h3>
            <pre style=\"background:#0b1120;border:1px solid #1e293b;color:#a5f3fc;padding:16px;border-radius:8px;font-size:13px;line-height:1.5;overflow-x:auto;white-space:pre-wrap;margin:0;\">" . htmlspecialchars($logContent) . "</pre>";
        }

        $loginUrl = url('/login');

        return <<<HTML
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - โรงพยาบาลทุ่งหัวช้าง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 40px 16px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            box-sizing: border-box;
        }
        .container {
            max-width: 760px;
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
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
        .title-group h1 {
            font-size: 22px;
            font-weight: 700;
            color: #f8fafc;
            margin: 0 0 6px 0;
        }
        .title-group p {
            margin: 0;
            font-size: 13.5px;
            color: #94a3b8;
        }
        .badge {
            background: {$badgeColor};
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #0f172a;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #334155;
        }
        .footer-action {
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
        .btn-secondary {
            background: #334155;
            color: #f1f5f9;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="title-group">
            <h1>{$title}</h1>
            <p>กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน</p>
        </div>
        <div class="badge">{$badgeText}</div>
    </div>

    <table>
        <tbody>
            {$rowsHtml}
        </tbody>
    </table>

    {$logSection}

    <div class="footer-action">
        <a href="{$loginUrl}" class="btn-primary">
            <span>เข้าสู่ระบบ (Go to Login)</span>
            <span>&rarr;</span>
        </a>
    </div>
</div>
</body>
</html>
HTML;
    }
}
