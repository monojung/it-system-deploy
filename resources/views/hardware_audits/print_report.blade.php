<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปผลการสำรวจและตรวจสอบสเปคคอมพิวเตอร์ ปีงบประมาณ พ.ศ. {{ $fiscalYear }} - โรงพยาบาลทุ่งหัวช้าง</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: "TH Sarabun New", "Sarabun", "Tahoma", sans-serif;
            font-size: 14pt;
            line-height: 1.3;
            color: #000000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .header h2 {
            font-size: 15pt;
            font-weight: bold;
            margin: 0 0 4px 0;
        }
        .header p {
            font-size: 13pt;
            margin: 0;
            color: #333333;
        }
        .summary-box {
            border: 1px solid #000000;
            padding: 10px 14px;
            margin-bottom: 16px;
            background: #fdfdfd;
            font-size: 13pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11pt;
        }
        th, td {
            border: 1px solid #000000;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .signatures {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            font-size: 13pt;
        }
        .sig-line {
            margin-top: 40px;
            border-bottom: 1px dotted #000000;
            width: 200px;
            display: inline-block;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background: #e2e8f0;
        }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12pt; }
            th { background-color: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding: 8px 20px; font-size: 14px; font-weight: bold; cursor: pointer; background: #0f766e; color: #ffffff; border: none; border-radius: 6px;">
        🖨️ สั่งพิมพ์เอกสารนี้ (Print A4)
    </button>
    <button onclick="window.close()" style="padding: 8px 16px; font-size: 14px; cursor: pointer; margin-left: 10px;">
        ปิดหน้าต่าง
    </button>
</div>

<div class="header">
    <h1>โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน</h1>
    <h2>รายงานสรุปผลการสำรวจ ตรวจนับ และตรวจสอบคุณลักษณะครุภัณฑ์คอมพิวเตอร์</h2>
    <p>ประจำปีงบประมาณ พ.ศ. {{ $fiscalYear }} (ข้อมูล ณ วันที่ {{ Carbon\Carbon::now()->locale('th')->isoFormat('D MMMM YYYY') }})</p>
</div>

<div class="summary-box">
    <strong>สรุปภาพรวมการตรวจนับ:</strong>
    จำนวนครุภัณฑ์คอมพิวเตอร์เป้าหมายทั้งสิ้น <strong>{{ number_format($totalFleet) }}</strong> เครื่อง | 
    ดำเนินการตรวจนับและอัปเดตสเปคแล้วเสร็จ <strong>{{ number_format($totalAudited) }}</strong> เครื่อง 
    (คิดเป็นร้อยละ <strong>{{ $percentage }}%</strong>) | 
    คงเหลือยังไม่ได้ตรวจนับ <strong>{{ number_format(max(0, $totalFleet - $totalAudited)) }}</strong> เครื่อง
</div>

<h3 style="font-size: 13pt; margin-bottom: 8px;">1. สรุปผลการตรวจนับแยกตามแผนก/หน่วยงาน</h3>
<table>
    <thead>
        <tr>
            <th style="width: 5%;">ลำดับ</th>
            <th style="text-align: left;">แผนก / หน่วยงาน</th>
            <th style="width: 18%;">เป้าหมาย (เครื่อง)</th>
            <th style="width: 18%;">ตรวจนับแล้ว (เครื่อง)</th>
            <th style="width: 18%;">ร้อยละความสำเร็จ</th>
        </tr>
    </thead>
    <tbody>
        @php $idx = 1; @endphp
        @foreach($deptSummary as $dept)
        <tr>
            <td class="text-center">{{ $idx++ }}</td>
            <td>{{ $dept->name }}</td>
            <td class="text-center">{{ number_format($dept->total) }}</td>
            <td class="text-center">{{ number_format($dept->audited) }}</td>
            <td class="text-center"><strong>{{ $dept->percentage }}%</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>

<h3 style="font-size: 13pt; margin-bottom: 8px; page-break-before: auto;">2. รายละเอียดครุภัณฑ์คอมพิวเตอร์ที่ได้รับการตรวจนับและตรวจสอบสเปคแล้ว (ปีงบประมาณ {{ $fiscalYear }})</h3>
<table>
    <thead>
        <tr>
            <th style="width: 4%;">ที่</th>
            <th style="width: 12%;">รหัสครุภัณฑ์</th>
            <th style="width: 15%;">ชื่อเครื่อง / รุ่น</th>
            <th style="width: 12%;">แผนก</th>
            <th style="width: 11%;">Serial Number</th>
            <th style="width: 18%;">CPU</th>
            <th style="width: 10%;">RAM</th>
            <th style="width: 10%;">Storage</th>
            <th style="width: 8%;">OS</th>
        </tr>
    </thead>
    <tbody>
        @php $row = 1; @endphp
        @forelse($auditedAssets as $a)
        <tr>
            <td class="text-center">{{ $row++ }}</td>
            <td><strong>{{ $a->asset_code }}</strong></td>
            <td>{{ $a->name }}</td>
            <td>{{ $a->department?->name ?? '-' }}</td>
            <td style="font-family: monospace; font-size: 9pt;">{{ $a->serial_number ?: '-' }}</td>
            <td style="font-size: 9pt;">{{ $a->cpu_model ?: '-' }}</td>
            <td class="text-center">{{ $a->ram_capacity ? $a->ram_capacity . 'GB ' . $a->ram_type : '-' }}</td>
            <td class="text-center" style="font-size: 9pt;">{{ $a->storage_capacity ?: '-' }}</td>
            <td class="text-center" style="font-size: 9pt;">{{ $a->os_name ?: '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center" style="padding: 20px;">
                ยังไม่มีข้อมูลครุภัณฑ์ที่ได้รับการอนุมัติการตรวจนับสำหรับปีงบประมาณนี้
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="signatures">
    <div class="sig-block">
        <div>ลงชื่อ..............................................................</div>
        <div style="margin-top: 4px;">(..............................................................)</div>
        <div>ตำแหน่ง เจ้าหน้าที่ผู้ดำเนินการสำรวจ/ตรวจนับ</div>
        <div>วันที่ ...... / ...... / ..........</div>
    </div>
    <div class="sig-block">
        <div>ลงชื่อ..............................................................</div>
        <div style="margin-top: 4px;">(..............................................................)</div>
        <div>ตำแหน่ง หัวหน้ากลุ่มงานสารสนเทศทางการแพทย์</div>
        <div>วันที่ ...... / ...... / ..........</div>
    </div>
</div>

<div class="signatures" style="margin-top: 25px;">
    <div class="sig-block" style="grid-column: 1 / span 2; text-align: center;">
        <div>ทราบและเห็นชอบผลการตรวจนับครุภัณฑ์คอมพิวเตอร์</div>
        <div style="margin-top: 30px;">ลงชื่อ..............................................................</div>
        <div style="margin-top: 4px;">(..............................................................)</div>
        <div>ผู้อำนวยการโรงพยาบาลทุ่งหัวช้าง</div>
        <div>วันที่ ...... / ...... / ..........</div>
    </div>
</div>

</body>
</html>
