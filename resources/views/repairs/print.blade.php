<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบสั่งซ่อมบำรุง - {{ $repair->ticket_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 20px;
            color: #000;
            background: #f8fafc;
            font-size: 14px;
        }

        .sheet {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #ddd;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-box {
            width: 60px;
            height: 60px;
            border: 2px solid #0f766e;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #0f766e;
            font-weight: bold;
        }

        .header-title h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .header-title h2 {
            margin: 2px 0 0 0;
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .header-right {
            text-align: right;
        }

        .ticket-no {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .badge-urgency {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #000;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
        }

        .info-label {
            width: 130px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .info-val {
            flex: 1;
            border-bottom: 1px dotted #999;
            padding-bottom: 2px;
        }

        .section-title {
            font-weight: 700;
            font-size: 14px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin: 20px 0 10px 0;
        }

        .desc-box {
            border: 1px solid #ccc;
            padding: 12px;
            min-height: 60px;
            font-size: 13.5px;
            line-height: 1.5;
            background: #fafafa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px 10px;
            font-size: 13px;
        }

        th {
            background-color: #f1f5f9;
        }

        .sign-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            text-align: center;
        }

        .sign-line {
            width: 200px;
            margin: 40px auto 8px auto;
            border-bottom: 1px solid #000;
        }

        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 15px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-print {
            padding: 8px 16px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .sheet {
                border: none;
                box-shadow: none;
                padding: 15px;
            }
            .no-print-bar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <a href="{{ route('repairs.show', $repair) }}" style="color: #475569; text-decoration: none; font-weight: 500;">
            &larr; กลับหน้ารายละเอียด
        </a>
        <button class="btn-print" onclick="window.print()">
            🖨️ สั่งพิมพ์เอกสาร (Print Job Ticket)
        </button>
    </div>

    <div class="sheet">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-box">IT</div>
                <div class="header-title">
                    <h1>ใบสั่งซ่อมบำรุงระบบสารสนเทศ (Service Job Ticket)</h1>
                    <h2>{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</h2>
                </div>
            </div>
            <div class="header-right">
                <div class="ticket-no">{{ $repair->ticket_number }}</div>
                <div class="badge-urgency">ความเร่งด่วน: {{ $repair->urgency_label }}</div>
            </div>
        </div>

        <!-- Requester & Location Info -->
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">วันที่แจ้งซ่อม:</div>
                <div class="info-val">{{ $repair->created_at->format('d/m/Y H:i') }} น.</div>
            </div>
            <div class="info-item">
                <div class="info-label">แผนก/หน่วยงาน:</div>
                <div class="info-val">{{ $repair->department?->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">ชื่อผู้แจ้ง:</div>
                <div class="info-val">{{ $repair->requester_name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">จุดที่ตั้ง/ห้อง:</div>
                <div class="info-val">{{ $repair->location_detail ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">เบอร์โทรติดต่อ:</div>
                <div class="info-val">{{ $repair->requester_phone }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">สถานะปัจจุบัน:</div>
                <div class="info-val" style="font-weight: bold;">{{ $repair->status_label }}</div>
            </div>
        </div>

        <!-- Asset / Equipment Info -->
        <div class="section-title">ข้อมูลอุปกรณ์ที่แจ้งซ่อม</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">รหัสครุภัณฑ์:</div>
                <div class="info-val">{{ $repair->asset?->asset_code ?? 'ไม่มีรหัสครุภัณฑ์' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Serial Number:</div>
                <div class="info-val">{{ $repair->asset?->serial_number ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">รายการ/ชนิด:</div>
                <div class="info-val">{{ $repair->asset?->name ?? ($repair->other_device_info ?? '-') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">ยี่ห้อ / รุ่น:</div>
                <div class="info-val">{{ $repair->asset ? $repair->asset->brand . ' ' . $repair->asset->model : '-' }}</div>
            </div>
        </div>

        <!-- Issue Description -->
        <div class="section-title">อาการเสีย / ปัญหาที่ได้รับแจ้ง</div>
        <div class="desc-box">
            <strong>หัวข้อ:</strong> {{ $repair->title }}<br>
            <strong>รายละเอียด:</strong> {{ $repair->description }}
        </div>

        <!-- Technician Action & Resolution -->
        <div class="section-title">การตรวจสภาพและผลการดำเนินงานของช่างเทคนิค</div>
        <div class="desc-box" style="min-height: 80px;">
            <strong>ช่างผู้รับผิดชอบ:</strong> {{ $repair->technician?->name ?? '........................................................' }}<br>
            <strong>สาเหตุของปัญหา:</strong> {{ $repair->cause ?? '-' }}<br>
            <strong>วิธีการแก้ไข:</strong> {{ $repair->solution ?? '-' }}
            @if($repair->external_repair_detail)
            <br><strong>ส่งซ่อมภายนอก:</strong> {{ $repair->external_repair_detail }}
            @endif
        </div>

        <!-- Spare parts table -->
        <div class="section-title">รายการอะไหล่และอุปกรณ์ที่ใช้</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">ลำดับ</th>
                    <th style="text-align: left;">รหัสพัสดุ - รายการ</th>
                    <th style="width: 80px; text-align: center;">จำนวน</th>
                    <th style="width: 100px; text-align: right;">ราคา/หน่วย</th>
                    <th style="width: 110px; text-align: right;">จำนวนเงิน (บาท)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($repair->parts as $idx => $part)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $part->sparePart?->part_code }} - {{ $part->sparePart?->name }}</td>
                    <td style="text-align: center;">{{ $part->quantity }} {{ $part->sparePart?->unit }}</td>
                    <td style="text-align: right;">{{ number_format($part->unit_price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($part->total_price, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #666;">- ไม่มีการใช้อะไหล่หรือวัสดุสิ้นเปลือง -</td>
                </tr>
                @endforelse
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">รวมค่าใช้จ่ายทั้งสิ้น:</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($repair->total_cost, 2) }} บาท</td>
                </tr>
            </tbody>
        </table>

        <!-- Signatures -->
        <div class="sign-grid">
            <div>
                <div>ลงชื่อ..........................................................ผู้แจ้งซ่อม/ผู้รับเครื่องคืน</div>
                <div style="margin-top: 4px;">( {{ $repair->requester_name }} )</div>
                <div style="margin-top: 4px; font-size: 12px;">วันที่ ......../......../............</div>
            </div>

            <div>
                <div>ลงชื่อ..........................................................ช่างผู้ดำเนินการ</div>
                <div style="margin-top: 4px;">( {{ $repair->technician?->name ?? '..........................................................' }} )</div>
                <div style="margin-top: 4px; font-size: 12px;">วันที่ ......../......../............</div>
            </div>
        </div>
    </div>
</body>
</html>
