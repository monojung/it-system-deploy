<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ป้ายสติกเกอร์บาร์โค้ดครุภัณฑ์ - {{ $asset->it_code ?: $asset->asset_code }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --primary-light: #ccfbf1;
            --primary-bg: #f0fdfa;
            --text-main: #0f172a;
            --text-sub: #334155;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--text-main);
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .no-print {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-nav {
            padding: 9px 18px;
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .btn-nav:hover {
            background: #f8fafc;
            border-color: #94a3b8;
        }

        .btn-print {
            padding: 10px 22px;
            background: #0d9488;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(13, 148, 136, 0.2);
            transition: all 0.2s ease;
        }

        .btn-print:hover {
            background: #0f766e;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(13, 148, 136, 0.3);
        }

        /* Label Sticker Container (รองรับเครื่องพิมพ์สติกเกอร์และ A4) */
        .label-card {
            width: 400px;
            border: 2px solid #0f766e;
            border-radius: 12px;
            padding: 14px 16px;
            background: #ffffff;
            box-shadow: 0 6px 16px rgba(15, 118, 110, 0.08);
            position: relative;
            page-break-inside: avoid;
        }

        .label-header {
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .label-logo {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .label-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .label-header-text {
            flex: 1;
        }

        .label-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f766e;
            line-height: 1.2;
            letter-spacing: -0.2px;
        }

        .label-subtitle {
            font-size: 11px;
            color: #475569;
            font-weight: 500;
        }

        /* 1D Barcode Section */
        .barcode-section {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px 4px 10px;
            margin-bottom: 10px;
            text-align: center;
        }

        .barcode-svg-wrap {
            width: 100%;
            max-width: 340px;
            margin: 0 auto;
        }

        .barcode-svg-wrap svg {
            width: 100% !important;
            height: auto !important;
            max-height: 52px;
        }

        /* Dual Scan Body: QR Code + Meta Info */
        .label-body {
            display: flex;
            gap: 12px;
            align-items: stretch;
        }

        .qr-box {
            width: 86px;
            height: 86px;
            border: 1.5px solid #0f766e;
            border-radius: 6px;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #ffffff;
            box-shadow: inset 0 0 4px rgba(0,0,0,0.03);
        }

        .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: contain;
        }

        .label-info {
            flex: 1;
            font-size: 12px;
            line-height: 1.45;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tag-meta-bar {
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 6px;
            padding: 4px 8px;
            margin-bottom: 6px;
        }

        .it-code-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .it-code-text {
            font-family: 'JetBrains Mono', 'SFMono-Regular', Consolas, monospace;
            font-size: 16px;
            font-weight: 800;
            color: #0f766e;
            letter-spacing: 0.5px;
        }

        .tag-badge {
            background: #0d9488;
            color: #ffffff;
            font-size: 9px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .official-code-text {
            font-size: 11px;
            color: #475569;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 2px;
        }

        .info-row {
            margin-bottom: 5px;
            color: #334155;
            font-size: 12px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .info-row strong {
            color: #0f172a;
            font-weight: 700;
        }

        .footer-note {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px dotted #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #64748b;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .label-card {
                box-shadow: none !important;
                border: 1.5px solid #000000 !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('assets.show', $asset) }}" class="btn-nav">
            <i class="bi bi-arrow-left"></i> กลับหน้ารายละเอียด
        </a>
        <button class="btn-print" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> สั่งพิมพ์ป้ายสติกเกอร์ (Print Label)
        </button>
    </div>

    <div class="label-card">
        {{-- Header: Logo & Hospital Info --}}
        <div class="label-header">
            <div class="label-logo">
                <img src="{{ asset('images/logo.png') }}?v=2" alt="Hospital Logo">
            </div>
            <div class="label-header-text">
                <div class="label-title">{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                <div class="label-subtitle">{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} &bull; คลังครุภัณฑ์ไอที</div>
            </div>
        </div>

        {{-- 1D Barcode (Code 128) สำหรับเครื่องสแกนบาร์โค้ด --}}
        <div class="barcode-section" title="Code 128 Barcode สำหรับเครื่องอ่านบาร์โค้ด">
            <div class="barcode-svg-wrap">
                {!! \App\Services\BarcodeService::generateSvg($asset->it_code ?: ($asset->asset_code ?: 'ASSET'), 48, 2, true) !!}
            </div>
        </div>

        {{-- Body: QR Code + Asset Details --}}
        <div class="label-body">
            <!-- QR Code สำหรับสมาร์ตโฟน/แท็บเล็ต -->
            <div class="qr-box" title="สแกนเพื่อเปิดข้อมูลครุภัณฑ์">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('assets.show', $asset)) }}" alt="QR Code">
            </div>

            <div class="label-info">
                {{-- IT Code & Asset Code Banner --}}
                <div class="tag-meta-bar">
                    <div class="it-code-title">
                        <span class="it-code-text">{{ $asset->it_code }}</span>
                        <span class="tag-badge">IT TAG</span>
                    </div>
                    <div class="official-code-text">
                        พัสดุ: <strong>{{ $asset->official_asset_code_display }}</strong>
                    </div>
                </div>

                <div class="info-row" title="{{ $asset->name }}">
                    <strong>รายการ:</strong> {{ $asset->name }}
                </div>
                <div class="info-row">
                    <strong>แผนก:</strong> {{ $asset->department?->name ?? 'ส่วนกลาง' }}
                </div>
            </div>
        </div>

        <div class="footer-note">
            <span>Dual-Scan: Barcode 1D &amp; QR Code</span>
            <span>กลุ่มงานดิจิทัล &bull; IT Asset Management</span>
        </div>
    </div>
</body>
</html>
