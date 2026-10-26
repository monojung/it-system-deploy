<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ไม่สามารถเชื่อมต่อฐานข้อมูลได้ - ระบบสารสนเทศ รพ.ทุ่งหัวช้าง</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .error-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            max-width: 680px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
        }
        .card-header {
            background: linear-gradient(135deg, #0f172a 0%, #0f766e 100%);
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid #334155;
        }
        .emblem {
            width: 48px;
            height: 48px;
            background: #0d9488;
            border: 2px solid #2dd4bf;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .header-title h1 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
        }
        .header-title p {
            font-size: 0.875rem;
            color: #ccfbf1;
        }
        .card-body {
            padding: 28px 24px;
        }
        .alert-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #450a0a;
            color: #fecaca;
            border: 1px solid #dc2626;
            border-radius: 9999px;
            padding: 6px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .error-desc {
            font-size: 1rem;
            line-height: 1.6;
            color: #e2e8f0;
            margin-bottom: 20px;
        }
        .info-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 0.85rem;
            color: #cbd5e1;
            word-break: break-all;
        }
        .steps-box {
            background: rgba(15, 118, 110, 0.1);
            border: 1px solid #0f766e;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .steps-box h3 {
            font-size: 0.95rem;
            color: #2dd4bf;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .steps-box ul {
            padding-left: 20px;
            font-size: 0.875rem;
            line-height: 1.7;
            color: #e2e8f0;
        }
        .steps-box code {
            background: #0f172a;
            color: #5eead4;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        .card-footer {
            padding: 16px 24px;
            background: #0f172a;
            border-top: 1px solid #334155;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
        }
        .btn-primary {
            background: #0d9488;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #0f766e;
        }
        .btn-secondary {
            background: #334155;
            color: #f1f5f9;
        }
        .btn-secondary:hover {
            background: #475569;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="card-header">
            <div class="emblem">🏥</div>
            <div class="header-title">
                <h1>โรงพยาบาลทุ่งหัวช้าง</h1>
                <p>ระบบสารสนเทศ กลุ่มงานสุขภาพดิจิทัล (Digital Health & IT System)</p>
            </div>
        </div>
        <div class="card-body">
            <div class="alert-pill">
                <span>🔴</span>
                <span>SQLSTATE[HY000] [2002] การเชื่อมต่อฐานข้อมูลถูกปฏิเสธ (Connection Refused)</span>
            </div>

            <p class="error-desc">
                ระบบไม่สามารถเชื่อมต่อไปยังเซิร์ฟเวอร์ฐานข้อมูล MariaDB/MySQL ได้ในขณะนี้ 
                เนื่องจากเครื่องเซิร์ฟเวอร์ปลายทางปฏิเสธการเชื่อมต่อบนพอร์ต 3306 (Port closed, firewalled หรือบริการฐานข้อมูลหยุดทำงาน)
            </p>

            <div class="info-box">
                <strong>Host:</strong> {{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port', 3306) }}<br>
                <strong>Database:</strong> {{ config('database.connections.mysql.database') }}<br>
                <strong>Username:</strong> {{ config('database.connections.mysql.username') }}
            </div>

            <div class="steps-box">
                <h3>💡 แนวทางแก้ไข (How to Allow & Fix Connection):</h3>
                <ul>
                    <li><strong>หากใช้งานในเครือข่าย รพ. (เซิร์ฟเวอร์ 192.168.2.10):</strong> ตรวจสอบว่าบริการ MariaDB กำลังทำงาน และเปิดพอร์ต 3306 ที่ Firewall (เช่น <code>firewall-cmd --permanent --add-port=3306/tcp && firewall-cmd --reload</code>) และผู้ใช้ได้รับสิทธิ์อนุญาตเชื่อมต่อ (<code>GRANT ALL ... TO 'chang'@'%'</code>)</li>
                    <li><strong>หากพัฒนาหรือทดสอบในเครื่อง Local:</strong> ตรวจสอบไฟล์ <code>.env</code> และตั้งค่า <code>DB_HOST=127.0.0.1</code> หรือเปิดใช้งาน MariaDB/MySQL ในเครื่อง</li>
                    <li><strong>ระบบเซสชัน (Sessions):</strong> ระบบรองรับการสลับไปจัดเก็บชั่วคราวผ่านไฟล์ (File Driver) อัตโนมัติ เพื่อป้องกันระบบล่ม</li>
                </ul>
            </div>
        </div>
        <div class="card-footer">
            <a href="javascript:location.reload();" class="btn btn-primary">
                🔄 ลองเชื่อมต่อใหม่อีกครั้ง (Retry)
            </a>
            <a href="{{ url('/') }}" class="btn btn-secondary">
                🏠 หน้าหลัก
            </a>
        </div>
    </div>
</body>
</html>
