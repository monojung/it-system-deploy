<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบขอยืม-คืนพัสดุและอุปกรณ์คอมพิวเตอร์ - {{ $borrow->borrow_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 20px 16px;
            color: #0f172a;
            background: #cbd5e1;
            font-size: 13.5px;
            line-height: 1.5;
        }

        /* Top control bar */
        .no-print-bar {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s;
        }

        .btn-primary { background: #0d9488; color: #ffffff; }
        .btn-primary:hover { background: #0f766e; }
        .btn-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .btn-secondary:hover { background: #e2e8f0; }

        /* A4 Page container */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm 15mm 20mm;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
        }

        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .doc-title {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .doc-subtitle {
            font-size: 13px;
            color: #475569;
            margin-top: 4px;
        }

        .doc-meta {
            text-align: right;
            font-size: 12px;
            color: #475569;
        }

        .doc-meta strong {
            font-size: 14px;
            color: #0d9488;
            font-family: monospace;
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #0d9488;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin: 16px 0 10px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .table-data th, .table-data td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }

        .table-data th {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            text-align: left;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 24px;
        }

        .sig-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px;
            background: #ffffff;
            text-align: center;
        }

        .sig-title {
            font-weight: 700;
            font-size: 13px;
            color: #334155;
            margin-bottom: 36px;
            text-align: left;
        }

        .sig-line {
            border-bottom: 1px dotted #94a3b8;
            margin: 0 auto 6px auto;
            width: 80%;
        }

        .rules-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 11.5px;
            color: #64748b;
            margin-top: 16px;
            line-height: 1.5;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .page {
                box-shadow: none;
                margin: 0;
                width: 100%;
                min-height: auto;
                padding: 10mm 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- Control Bar -->
    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('asset-borrows.show', $borrow->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> กลับหน้ารายละเอียด
            </a>
            <span style="font-weight: 600; color: #475569;">ใบขอยืม-คืนอุปกรณ์ไอที: <code>{{ $borrow->borrow_no }}</code></span>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> สั่งพิมพ์เอกสาร (Print)
            </button>
        </div>
    </div>

    <!-- Printable A4 Document -->
    <div class="page">
        <!-- Header -->
        <div class="doc-header">
            <div>
                <h1 class="doc-title">แบบฟอร์มการขอยืม - คืนอุปกรณ์และครุภัณฑ์คอมพิวเตอร์</h1>
                <div class="doc-subtitle">
                    กลุ่มงานเวชสารสนเทศและเทคโนโลยี โรงพยาบาลทุ่งหัวช้าง ({{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }})
                </div>
            </div>
            <div class="doc-meta">
                <div>เลขที่คำขอ: <strong>{{ $borrow->borrow_no }}</strong></div>
                <div>วันที่ยื่นคำขอ: {{ $borrow->created_at ? $borrow->created_at->format('d/m/Y') : date('d/m/Y') }}</div>
                <div>สถานะ: <strong>{{ $borrow->status_label }}</strong></div>
            </div>
        </div>

        <!-- Section 1: ข้อมูลผู้ขอยืม -->
        <div class="section-title">
            <i class="bi bi-person-fill"></i> 1. ข้อมูลผู้ขอยืมและหน่วยงานที่ขอใช้งาน
        </div>
        <table class="table-data">
            <tr>
                <th style="width: 20%;">ชื่อ-สกุล ผู้ขอยืม:</th>
                <td style="width: 30%;"><strong>{{ $borrow->borrower_name }}</strong></td>
                <th style="width: 20%;">กลุ่มงาน / แผนก:</th>
                <td style="width: 30%;">{{ $borrow->department?->name ?? 'ไม่ระบุ' }}</td>
            </tr>
            <tr>
                <th>เบอร์โทรศัพท์ติดต่อ:</th>
                <td>{{ $borrow->contact_phone }}</td>
                <th>สถานที่นำไปใช้งาน:</th>
                <td>{{ $borrow->location_used ?: 'ภายในโรงพยาบาลทุ่งหัวช้าง' }}</td>
            </tr>
            <tr>
                <th>วัตถุประสงค์การยืม:</th>
                <td colspan="3">{{ $borrow->purpose }}</td>
            </tr>
            <tr>
                <th>กำหนดการยืม-คืน:</th>
                <td colspan="3">
                    วันที่ต้องการยืม: <strong>{{ $borrow->borrow_date ? $borrow->borrow_date->format('d/m/Y') : '-' }}</strong> &nbsp;&bull;&nbsp;
                    กำหนดส่งคืน: <strong>{{ $borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : '-' }}</strong>
                    (รวมระยะเวลา {{ $borrow->duration_days }} วัน)
                </td>
            </tr>
        </table>

        <!-- Section 2: รายการอุปกรณ์และอุปกรณ์เสริม -->
        <div class="section-title">
            <i class="bi bi-pc-display"></i> 2. รายการอุปกรณ์คอมพิวเตอร์และอุปกรณ์ต่อพ่วงที่ขอยืม
        </div>
        <table class="table-data">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="width: 8%; text-align: center;">ลำดับ</th>
                    <th style="width: 25%;">รหัสครุภัณฑ์ / S/N</th>
                    <th>ชื่อรายการ / ยี่ห้อ / รุ่น</th>
                    <th style="width: 25%;">รายละเอียดสเปค</th>
                    <th style="width: 15%; text-align: center;">จำนวน</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center;">1</td>
                    <td>
                        <strong>{{ $borrow->asset?->asset_code ?? '-' }}</strong>
                        @if($borrow->asset?->serial_number)
                            <div style="font-size: 11px; color: #64748b;">S/N: {{ $borrow->asset->serial_number }}</div>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $borrow->asset?->name ?? '-' }}</strong>
                        <div style="font-size: 12px; color: #475569;">{{ $borrow->asset?->brand }} {{ $borrow->asset?->model }}</div>
                    </td>
                    <td style="font-size: 12px;">{{ $borrow->asset?->formatted_specs ?? '-' }}</td>
                    <td style="text-align: center;">1 เครื่อง/ชุด</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: -6px; margin-bottom: 14px; font-size: 13px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px;">
            <strong>รายการอุปกรณ์เสริมที่ยืมไปด้วย:</strong>
            @if(!empty($borrow->accessories))
                <span style="color: #0f766e; font-weight: 600;">{{ $borrow->accessories_text }}</span>
            @else
                <span style="color: #64748b;">(เฉพาะตัวเครื่อง ไม่มีอุปกรณ์เสริมเพิ่มเติม)</span>
            @endif
        </div>

        <!-- Section 3: บันทึกการส่งมอบและการรับคืน -->
        <div class="section-title">
            <i class="bi bi-clipboard2-check"></i> 3. บันทึกการตรวจสอบสภาพ ส่งมอบ และรับมอบคืน
        </div>
        <table class="table-data">
            <tr>
                <th style="width: 20%;">สภาพก่อนส่งมอบ:</th>
                <td style="width: 30%;">{{ $borrow->dispatch_condition ?: 'ปกติ สมบูรณ์ ครบถ้วนตามรายการ' }}</td>
                <th style="width: 20%;">สภาพเมื่อรับคืน:</th>
                <td style="width: 30%;">{{ $borrow->return_condition ?: 'รอการส่งมอบคืน' }}</td>
            </tr>
            <tr>
                <th>วันที่ส่งมอบจริง:</th>
                <td>{{ $borrow->dispatched_at ? $borrow->dispatched_at->format('d/m/Y H:i') : '-' }}</td>
                <th>วันที่รับคืนจริง:</th>
                <td>{{ $borrow->actual_return_date ? $borrow->actual_return_date->format('d/m/Y') : '-' }}</td>
            </tr>
            @if($borrow->return_notes)
            <tr>
                <th>ข้อสังเกตการรับคืน:</th>
                <td colspan="3">{{ $borrow->return_notes }}</td>
            </tr>
            @endif
        </table>

        <!-- Rules Box -->
        <div class="rules-box">
            <strong>เงื่อนไขและข้อตกลงการยืมพัสดุคอมพิวเตอร์และอุปกรณ์ไอที:</strong><br>
            1. ผู้ขอยืมต้องดูแลรักษาอุปกรณ์ให้อยู่ในสภาพเรียบร้อย ปลอดภัย และไม่นำไปใช้งานผิดวัตถุประสงค์ของทางราชการ<br>
            2. ต้องส่งคืนอุปกรณ์ตามกำหนดเวลา หากมีความจำเป็นต้องใช้ต่อ ต้องแจ้งขอขยายเวลาล่วงหน้าต่อกลุ่มงานสุขภาพดิจิทัล<br>
            3. หากอุปกรณ์เกิดการชำรุดเสียหายหรือสูญหายอันเกิดจากความประมาทเลินเล่อ ผู้ยืมยินดีรับผิดชอบดำเนินการแก้ไขหรือชดใช้ตามระเบียบพัสดุ
        </div>

        <!-- Section 4: ลงนาม 4 ฝ่าย -->
        <div class="signature-grid">
            {{-- ฝ่ายที่ 1: ผู้ขอยืม --}}
            <div class="sig-card">
                <div class="sig-title">1. ผู้ขอยืมอุปกรณ์</div>
                <div class="sig-line"></div>
                <div>( {{ $borrow->borrower_name }} )</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">ตำแหน่ง: {{ $borrow->borrower_position ?: '............................................................' }}</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">วันที่: ...... / ...... / ..........</div>
            </div>

            {{-- ฝ่ายที่ 2: หัวหน้ากลุ่มงาน/ผู้บังคับบัญชา --}}
            <div class="sig-card">
                <div class="sig-title">2. หัวหน้ากลุ่มงาน / ผู้อนุมัติ</div>
                <div class="sig-line"></div>
                <div>( ............................................................ )</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">ตำแหน่ง: ............................................................</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">วันที่: ...... / ...... / ..........</div>
            </div>

            {{-- ฝ่ายที่ 3: เจ้าหน้าที่ไอทีผู้ส่งมอบ --}}
            <div class="sig-card">
                <div class="sig-title">3. เจ้าหน้าที่ผู้จ่ายอุปกรณ์</div>
                <div class="sig-line"></div>
                <div>( {{ $borrow->dispatcher?->name ?? '............................................................' }} )</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">เจ้าหน้าที่กลุ่มงานสุขภาพดิจิทัล</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                    วันที่: {{ $borrow->dispatched_at ? $borrow->dispatched_at->format('d/m/Y') : '...... / ...... / ..........' }}
                </div>
            </div>

            {{-- ฝ่ายที่ 4: เจ้าหน้าที่ไอทีผู้รับคืน --}}
            <div class="sig-card">
                <div class="sig-title">4. เจ้าหน้าที่ผู้รับคืนอุปกรณ์</div>
                <div class="sig-line"></div>
                <div>( {{ $borrow->receiver?->name ?? '............................................................' }} )</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">เจ้าหน้าที่กลุ่มงานสุขภาพดิจิทัล</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                    วันที่: {{ $borrow->actual_return_date ? $borrow->actual_return_date->format('d/m/Y') : '...... / ...... / ..........' }}
                </div>
            </div>
        </div>

    </div>

</body>
</html>
