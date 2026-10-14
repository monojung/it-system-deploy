<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ป้ายสติกเกอร์ครุภัณฑ์ - {{ $asset->asset_code }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .label-card {
            width: 380px;
            border: 2px solid #0f766e;
            border-radius: 12px;
            padding: 16px;
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: relative;
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .label-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .label-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f766e;
            line-height: 1.2;
        }

        .label-subtitle {
            font-size: 11px;
            color: #475569;
        }

        .label-body {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .qr-box {
            width: 100px;
            height: 100px;
            border: 1px solid #cbd5e1;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #ffffff;
        }

        .qr-box img {
            width: 100%;
            height: 100%;
        }

        .label-info {
            flex: 1;
            font-size: 12px;
            line-height: 1.4;
        }

        .code-box {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            font-family: monospace;
            background: #f0fdfa;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            border: 1px solid #ccfbf1;
        }

        .info-row {
            margin-bottom: 2px;
            color: #334155;
        }

        .info-row strong {
            color: #0f172a;
        }

        .no-print {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-print {
            padding: 10px 20px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .label-card {
                box-shadow: none;
                border: 1.5px solid #000;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ route('assets.show', $asset) }}" style="padding: 10px 18px; background: #e2e8f0; color: #334155; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 500;">
            &larr; กลับ
        </a>
        <button class="btn-print" onclick="window.print()">
            🖨️ สั่งพิมพ์ป้ายสติกเกอร์ (Print Label)
        </button>
    </div>

    <div class="label-card">
        <div class="label-header">
            <div class="label-logo">
                <img src="{{ asset('images/logo.png') }}?v=2" alt="Logo">
            </div>
            <div>
                <div class="label-title">{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                <div class="label-subtitle">{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} (ครุภัณฑ์คอมพิวเตอร์)</div>
            </div>
        </div>

        <div class="label-body">
            <!-- QR Code via QR Server API -->
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('assets.show', $asset)) }}" alt="QR Code">
            </div>

            <div class="label-info">
                {{-- Dual-Tag Code Banner --}}
                <div style="background: #f0fdfa; border: 1.5px solid #0d9488; border-radius: 6px; padding: 4px 8px; margin-bottom: 6px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-family: monospace; font-size: 15.5px; font-weight: 800; color: #0f766e; letter-spacing: 0.3px;">
                            {{ $asset->it_asset_code }}
                        </span>
                        <span style="background: #0d9488; color: white; font-size: 8.5px; font-weight: 700; padding: 1px 4px; border-radius: 3px;">
                            IT TAG
                        </span>
                    </div>
                    <div style="font-size: 10px; color: #475569; margin-top: 1px; font-family: monospace;">
                        พัสดุ: <strong>{{ $asset->official_asset_code_display }}</strong>
                    </div>
                </div>

                <div class="info-row"><strong>รายการ:</strong> {{ $asset->name }}</div>
                <div class="info-row"><strong>รุ่น:</strong> {{ $asset->brand }} {{ $asset->model }}</div>
                <div class="info-row"><strong>แผนก:</strong> {{ $asset->department?->name ?? 'ส่วนกลาง' }}</div>
                <div class="info-row"><strong>ผู้ดูแล:</strong> {{ $asset->custodian_name ?? '-' }}</div>
                @if($asset->formatted_specs && $asset->formatted_specs !== '-')
                <div class="info-row" style="font-size: 11px; color: #0284c7;"><strong>สเปค:</strong> {{ Str::limit($asset->formatted_specs, 48) }}</div>
                @endif
                @if($asset->ip_address)
                <div class="info-row"><strong>IP:</strong> {{ $asset->ip_address }}</div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
