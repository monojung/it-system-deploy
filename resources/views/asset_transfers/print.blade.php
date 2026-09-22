<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบแจ้งย้ายและส่งมอบจุดติดตั้งครุภัณฑ์ IT - {{ $assetTransfer->transfer_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        @page {
            size: A4 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 16px 12px;
            color: #0f172a;
            background: #cbd5e1;
            font-size: 12px;
            line-height: 1.4;
        }

        .no-print-bar {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto 12px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 10px 18px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s;
        }

        .btn-primary { background: #6366f1; color: #ffffff; }
        .btn-primary:hover { background: #4f46e5; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .btn-secondary:hover { background: #e2e8f0; }

        .page {
            width: 210mm;
            max-width: 100%;
            min-height: 275mm;
            padding: 10mm 12mm;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
        }

        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .doc-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.25;
        }

        .doc-subtitle {
            font-size: 11.5px;
            color: #475569;
            margin-top: 3px;
        }

        .doc-meta {
            text-align: right;
            font-size: 11.5px;
        }

        .doc-no {
            font-family: monospace;
            font-weight: 800;
            font-size: 14px;
            color: #6366f1;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1e1b4b;
            background: #eef2ff;
            padding: 5px 10px;
            border-left: 4px solid #6366f1;
            margin: 10px 0 8px 0;
            border-radius: 0 4px 4px 0;
        }

        table.info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 11.5px;
        }

        table.info-table th, table.info-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            vertical-align: middle;
        }

        table.info-table th {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            text-align: left;
        }

        .compare-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .compare-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .compare-box.origin {
            background: #f8fafc;
            border-left: 3px solid #94a3b8;
        }

        .compare-box.dest {
            background: #f0fdf4;
            border-left: 3px solid #16a34a;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 24px;
            text-align: center;
        }

        .signature-box {
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 12px 8px 8px 8px;
            background: #fafafa;
        }

        .sign-line {
            height: 44px;
            border-bottom: 1px dotted #94a3b8;
            margin-bottom: 6px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
                margin: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .page {
                box-shadow: none;
                padding: 0;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <a href="{{ route('asset-transfers.show', $assetTransfer) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> กลับหน้ารายละเอียด
            </a>
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> สั่งพิมพ์เอกสาร (Print)
            </button>
        </div>
    </div>

    <div class="page">
        <!-- Header -->
        <div class="doc-header">
            <div>
                <h1 class="doc-title">ใบแจ้งย้ายและส่งมอบจุดติดตั้งครุภัณฑ์คอมพิวเตอร์และ IT</h1>
                <div class="doc-subtitle">
                    {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} &bull; กลุ่มงานสารสนเทศและเทคโนโลยี
                </div>
            </div>
            <div class="doc-meta">
                <div>เลขที่เอกสาร: <span class="doc-no">{{ $assetTransfer->transfer_no }}</span></div>
                <div style="color: #64748b; margin-top: 2px;">
                    วันที่ย้าย: <strong>{{ $assetTransfer->transfer_date ? $assetTransfer->transfer_date->format('d/m/Y') : '-' }}</strong>
                </div>
                <div style="color: #64748b;">
                    ประเภท: <strong>{{ $assetTransfer->transfer_type_label }}</strong>
                </div>
            </div>
        </div>

        <!-- Section 1: Equipment Profile -->
        <div class="section-title">1. ข้อมูลครุภัณฑ์และอุปกรณ์ IT</div>
        <table class="info-table">
            <tr>
                <th style="width: 18%;">รหัสครุภัณฑ์:</th>
                <td style="width: 32%; font-weight: 700; color: #1e1b4b; font-family: monospace;">
                    {{ $assetTransfer->asset?->asset_code ?: '-' }}
                </td>
                <th style="width: 18%;">ประเภทอุปกรณ์:</th>
                <td style="width: 32%;">
                    {{ $assetTransfer->asset?->deviceType?->name ?: '-' }}
                </td>
            </tr>
            <tr>
                <th>ชื่อครุภัณฑ์:</th>
                <td style="font-weight: 600;">{{ $assetTransfer->asset?->name ?: '-' }}</td>
                <th>ยี่ห้อ / รุ่น:</th>
                <td>{{ $assetTransfer->asset?->brand }} {{ $assetTransfer->asset?->model }}</td>
            </tr>
            <tr>
                <th>Serial Number (S/N):</th>
                <td style="font-family: monospace;">{{ $assetTransfer->asset?->serial_number ?: '-' }}</td>
                <th>ปีงบประมาณ:</th>
                <td>{{ $assetTransfer->asset?->budget_year ?: '-' }}</td>
            </tr>
            @if($assetTransfer->asset && $assetTransfer->asset->formatted_specs !== '-')
            <tr>
                <th>คุณลักษณะ / สเปค:</th>
                <td colspan="3" style="font-size: 11px; color: #475569;">
                    {{ $assetTransfer->asset->formatted_specs }}
                </td>
            </tr>
            @endif
        </table>

        <!-- Section 2: Relocation Mapping (From -> To) -->
        <div class="section-title">2. ข้อมูลการย้ายจุดติดตั้ง (เปรียบเทียบจุดเดิม vs จุดใหม่)</div>
        <div class="compare-grid">
            <div class="compare-box origin">
                <div style="font-weight: 700; color: #475569; margin-bottom: 6px; font-size: 12px;">
                    <i class="bi bi-geo-alt me-1"></i>จุดติดตั้งเดิม (From)
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: #64748b;">หน่วยงาน/กลุ่มงาน:</span>
                    <strong>{{ $assetTransfer->fromDepartment?->name ?? $assetTransfer->from_department_name ?? '-' }}</strong>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: #64748b;">จุดติดตั้ง/ห้อง:</span>
                    <span>{{ $assetTransfer->from_location_detail ?: '-' }}</span>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: #64748b;">ผู้ครอบครอง/ดูแลเดิม:</span>
                    <span>{{ $assetTransfer->from_custodian_name ?: '-' }}</span>
                </div>
                <div>
                    <span style="color: #64748b;">IP Address เดิม:</span>
                    <span style="font-family: monospace;">{{ $assetTransfer->from_ip_address ?: '-' }}</span>
                </div>
            </div>

            <div class="compare-box dest">
                <div style="font-weight: 700; color: #166534; margin-bottom: 6px; font-size: 12px;">
                    <i class="bi bi-geo-alt-fill me-1"></i>จุดติดตั้งใหม่ (To)
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: #166534;">หน่วยงาน/กลุ่มงาน:</span>
                    <strong style="color: #14532d;">{{ $assetTransfer->toDepartment?->name ?? $assetTransfer->to_department_name ?? '-' }}</strong>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: #166534;">จุดติดตั้งใหม่/ห้อง:</span>
                    <strong>{{ $assetTransfer->to_location_detail ?: '-' }}</strong>
                </div>
                <div style="margin-bottom: 4px;">
                    <span style="color: #166534;">ผู้ครอบครอง/ดูแลใหม่:</span>
                    <strong>{{ $assetTransfer->to_custodian_name ?: '-' }}</strong>
                </div>
                <div>
                    <span style="color: #166534;">IP Address ใหม่:</span>
                    <span style="font-family: monospace;">{{ $assetTransfer->to_ip_address ?: ($assetTransfer->from_ip_address ?: 'DHCP / คงเดิม') }}</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Reason & Testing Checklist -->
        <div class="section-title">3. เหตุผลความจำเป็นและผลการทดสอบการติดตั้ง</div>
        <table class="info-table">
            <tr>
                <th style="width: 25%;">เหตุผลในการย้าย:</th>
                <td colspan="3">{{ $assetTransfer->reason ?: '-' }}</td>
            </tr>
            <tr>
                <th>ผลการทดสอบหลังติดตั้ง:</th>
                <td colspan="3" style="color: #166534; font-weight: 600;">
                    {{ $assetTransfer->test_result ?: 'ตรวจสอบการเชื่อมต่อเครือข่าย สาย LAN สัญญาณ และระบบโปรแกรม รพ. ใช้งานได้ตามปกติ' }}
                </td>
            </tr>
            @if($assetTransfer->notes)
            <tr>
                <th>หมายเหตุเพิ่มเติม:</th>
                <td colspan="3">{{ $assetTransfer->notes }}</td>
            </tr>
            @endif
            <tr>
                <th>สถานะการดำเนินงาน:</th>
                <td><strong>{{ $assetTransfer->status_label }}</strong></td>
                <th>วันที่เสร็จสมบูรณ์:</th>
                <td>{{ $assetTransfer->completed_at ? $assetTransfer->completed_at->format('d/m/Y H:i') . ' น.' : '-' }}</td>
            </tr>
        </table>

        <!-- Section 4: Signatures -->
        <div class="signature-grid">
            <div class="signature-box">
                <div style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 8px;">
                    ผู้ส่งมอบ / ผู้ยื่นคำขอย้าย
                </div>
                <div class="sign-line"></div>
                <div style="font-size: 11.5px; font-weight: 600;">( {{ $assetTransfer->from_custodian_name ?: $assetTransfer->user?->name ?: '......................................................' }} )</div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                    ตำแหน่ง: ....................................................
                </div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                    วันที่ ...... / ...... / ............
                </div>
            </div>

            <div class="signature-box">
                <div style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 8px;">
                    ช่าง IT ผู้ดำเนินการย้ายและติดตั้ง
                </div>
                <div class="sign-line"></div>
                <div style="font-size: 11.5px; font-weight: 600;">( {{ $assetTransfer->technician?->name ?: '......................................................' }} )</div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                    เจ้าหน้าที่งานเทคโนโลยีสารสนเทศ
                </div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                    วันที่ ...... / ...... / ............
                </div>
            </div>

            <div class="signature-box">
                <div style="font-size: 11px; font-weight: 600; color: #475569; margin-bottom: 8px;">
                    ผู้รับมอบ ณ จุดติดตั้งใหม่
                </div>
                <div class="sign-line"></div>
                <div style="font-size: 11.5px; font-weight: 600;">( {{ $assetTransfer->receiver_name ?: $assetTransfer->to_custodian_name ?: '......................................................' }} )</div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                    ตำแหน่ง: ....................................................
                </div>
                <div style="font-size: 10.5px; color: #64748b; margin-top: 2px;">
                    วันที่ ...... / ...... / ............
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px;">
            เอกสารบันทึกการโอนย้ายจุดติดตั้งครุภัณฑ์คอมพิวเตอร์และสารสนเทศ &bull; พิมพ์เมื่อ {{ now()->addYears(543)->format('d/m/Y H:i') }} น. &bull; รหัสอ้างอิง: {{ $assetTransfer->transfer_no }}
        </div>
    </div>

</body>
</html>
