<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THC Hardware Audit Telemetry API - โรงพยาบาลทุ่งหัวช้าง</title>

    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --secondary: #0284c7;
            --success: #10b981;
            --dark: #0f172a;
            --dark-card: #1e293b;
            --bg-body: #090d16;
            --border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(at 0% 0%, rgba(13, 148, 136, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(2, 132, 199, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(15, 23, 42, 0.5) 0px, transparent 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .container {
            width: 100%;
            max-width: 860px;
        }

        .header-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 32px 28px;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            margin-bottom: 24px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(13, 148, 136, 0.18);
            color: #2dd4bf;
            border: 1px solid rgba(45, 212, 191, 0.3);
            border-radius: 9999px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.65; }
        }

        h1 {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.3;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 28px;
        }

        .stat-box {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            padding: 16px 18px;
            transition: border-color 0.2s;
        }

        .stat-box:hover {
            border-color: rgba(45, 212, 191, 0.3);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stat-value {
            font-size: 17px;
            font-weight: 700;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-value.highlight {
            color: #38bdf8;
        }

        .section-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #e2e8f0;
            margin: 20px 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .code-box {
            position: relative;
            background: #020617;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .code-content {
            font-family: 'JetBrains Mono', Consolas, monospace;
            font-size: 13px;
            color: #38bdf8;
            word-break: break-all;
            white-space: pre-wrap;
            line-height: 1.5;
            padding-right: 70px;
            display: block;
        }

        .copy-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            font-family: 'Prompt', sans-serif;
        }

        .copy-btn:hover {
            background: #0d9488;
            border-color: #0d9488;
            color: #ffffff;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            font-family: 'Prompt', sans-serif;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);
        }

        .btn-primary:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.25);
        }

        .footer-note {
            text-align: center;
            color: #64748b;
            font-size: 12.5px;
            margin-top: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #10b981;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1000;
            pointer-events: none;
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header-card">
            
            <div class="brand-badge">
                <span class="status-dot"></span>
                <span>API STATUS: ONLINE (200 OK)</span>
            </div>

            <h1>
                <i class="bi bi-hdd-network-fill" style="color: #2dd4bf;"></i>
                THC Hardware Audit Telemetry API
            </h1>

            <p class="subtitle">
                ระบบ Endpoint รับข้อมูลตรวจนับและติดตามสเปคเครื่องคอมพิวเตอร์ลูกข่ายอัตโนมัติ<br>
                กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง (Thung Hua Chang Hospital IT Platform)
            </p>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-label"><i class="bi bi-link-45deg"></i> Endpoint Method</div>
                    <div class="stat-value highlight">POST / GET</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label"><i class="bi bi-filetype-json"></i> Content Type</div>
                    <div class="stat-value">application/json</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label"><i class="bi bi-calendar-check"></i> ปีงบประมาณ</div>
                    <div class="stat-value">{{ $fiscalYear }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label"><i class="bi bi-cpu-fill"></i> สถิติการตรวจนับปีนี้</div>
                    <div class="stat-value">{{ number_format($auditedCount) }} เครื่อง</div>
                </div>
            </div>

            <div class="section-title">
                <i class="bi bi-terminal-fill" style="color: #38bdf8;"></i> คำสั่ง PowerShell One-Liner (สั่งรันตรวจนับและส่งสเปคจากเครื่องลูกข่าย):
            </div>
            <div class="code-box">
                <code class="code-content" id="psCmd">[Net.ServicePointManager]::SecurityProtocol = 3072; $w = New-Object Net.WebClient; $w.Encoding = [Text.Encoding]::UTF8; iex ($w.DownloadString('{{ url('/agent/thc_audit_agent.ps1') }}'))</code>
                <button type="button" class="copy-btn" onclick="copyText('psCmd')">
                    <i class="bi bi-clipboard"></i> คัดลอก
                </button>
            </div>

            <div class="section-title">
                <i class="bi bi-code-square" style="color: #f59e0b;"></i> ตัวอย่างคำสั่ง cURL POST สำหรับทดสอบส่งข้อมูลเข้า API:
            </div>
            <div class="code-box">
                <code class="code-content" id="curlCmd">curl -X POST "{{ url('/api/hardware-audit/submit') }}" \
  -H "Content-Type: application/json; charset=utf-8" \
  -d '{"hostname":"PC-DEMO","brand":"Lenovo","model":"ThinkCentre","cpu_model":"Intel Core i5","ram_capacity":16}'</code>
                <button type="button" class="copy-btn" onclick="copyText('curlCmd')">
                    <i class="bi bi-clipboard"></i> คัดลอก
                </button>
            </div>

            <div class="button-group">
                <a href="{{ route('hardware-audits.index') }}" class="btn btn-primary">
                    <i class="bi bi-speedometer2"></i> เข้าสู่หน้าจัดการตรวจนับ (Hardware Audit Dashboard)
                </a>
                <a href="{{ url('/api/hardware-audit/submit?format=json') }}" class="btn btn-outline" target="_blank">
                    <i class="bi bi-filetype-json"></i> ดูผลลัพธ์แบบ JSONดิบ (?format=json)
                </a>
                <a href="{{ url('/agent/thc_audit_agent.ps1') }}" class="btn btn-outline" target="_blank">
                    <i class="bi bi-file-earmark-code"></i> สคริปต์ thc_audit_agent.ps1
                </a>
            </div>

        </div>

        <div class="footer-note">
            <i class="bi bi-shield-lock-fill"></i>
            <span>THC Hospital Telemetry Agent Platform v2.1.0 • CSRF-Exempt API Protected</span>
        </div>
    </div>

    <div id="toast" class="toast">
        <i class="bi bi-check-circle-fill me-1"></i> คัดลอกคำสั่งเรียบร้อยแล้ว
    </div>

    <script>
        function copyText(elementId) {
            const text = document.getElementById(elementId).innerText.trim();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showToast();
                });
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                showToast();
            }
        }

        function showToast() {
            const toast = document.getElementById('toast');
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 2000);
        }
    </script>
</body>
</html>
