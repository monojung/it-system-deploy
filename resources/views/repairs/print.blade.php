<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="pageTitle">{{ ($printType === 'external' ? 'ใบขออนุมัติส่งซ่อมภายนอก - ' : ($printType === 'memo' ? 'บันทึกข้อความขออนุมัติส่งซ่อม - ' : 'ใบสั่งซ่อมบำรุง - ')) . $repair->ticket_number }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 20px;
            color: #0f172a;
            background: #f1f5f9;
            font-size: 13px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Top control bar (Screen only) */
        .no-print-bar {
            max-width: 860px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .btn-back {
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-back:hover {
            color: #0f172a;
        }

        .tab-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }

        .tab-btn {
            border: none;
            background: transparent;
            padding: 6px 14px;
            font-size: 12.5px;
            font-family: inherit;
            font-weight: 600;
            color: #475569;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .tab-btn:hover {
            color: #0f172a;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #0f766e;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }

        .tab-btn.active-external {
            background: #7e22ce !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.12);
        }

        .tab-btn.active-memo {
            background: #0369a1 !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.12);
        }

        .btn-print {
            padding: 8px 18px;
            background: #0d9488;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-weight: 600;
            font-size: 13.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .btn-print:hover {
            background: #0f766e;
        }

        /* Paper sheet */
        .sheet {
            max-width: 860px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px 36px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border-radius: 4px;
            min-height: 297mm;
            position: relative;
        }

        .sheet-doc {
            display: none;
        }

        .sheet-doc.active {
            display: block;
        }

        /* Common Document Styles */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header-external {
            border-bottom: 2.5px solid #7e22ce !important;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-box {
            width: 58px;
            height: 58px;
            border: 2px solid #0f766e;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #0f766e;
            font-weight: 800;
            flex-shrink: 0;
            background: #f0fdfa;
        }

        .logo-box-img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .logo-box-purple {
            border-color: #7e22ce !important;
            color: #7e22ce !important;
            background: #faf5ff !important;
        }

        .header-title h1 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.3;
        }

        .header-title h2 {
            margin: 3px 0 0 0;
            font-size: 13px;
            font-weight: 500;
            color: #0f766e;
            line-height: 1.3;
        }

        .header-title h3 {
            margin: 2px 0 0 0;
            font-size: 11.5px;
            font-weight: 400;
            color: #64748b;
        }

        .header-right {
            text-align: right;
            flex-shrink: 0;
        }

        .ticket-no {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #0f766e;
            font-family: monospace;
        }

        .ticket-no-purple {
            color: #7e22ce !important;
        }

        .badge-urgency {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #0f172a;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
            background: #f8fafc;
        }

        .badge-external {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #7e22ce;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 4px;
            background: #faf5ff;
            color: #7e22ce;
        }

        /* Banner Box */
        .proposal-banner {
            background: #faf5ff;
            border: 1.5px solid #d8b4fe;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .proposal-banner strong {
            color: #6b21a8;
            font-size: 13.5px;
        }

        .proposal-banner span {
            color: #581c87;
            font-size: 12px;
        }

        /* Data Tables & Grids */
        .section-title {
            font-weight: 700;
            font-size: 13px;
            border-bottom: 1.5px solid #0f766e;
            padding-bottom: 4px;
            margin: 14px 0 8px 0;
            color: #0f766e;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-title-purple {
            border-bottom-color: #7e22ce !important;
            color: #7e22ce !important;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
            margin-bottom: 10px;
        }

        .info-item {
            display: flex;
            font-size: 12.5px;
            line-height: 1.4;
        }

        .info-label {
            width: 125px;
            font-weight: 600;
            color: #334155;
            flex-shrink: 0;
        }

        .info-val {
            flex: 1;
            border-bottom: 1px dotted #94a3b8;
            padding-bottom: 1px;
            color: #0f172a;
        }

        .desc-box {
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 12.5px;
            line-height: 1.45;
            background: #f8fafc;
            margin-bottom: 10px;
        }

        .highlight-box {
            border: 1.5px solid #d8b4fe;
            background: #faf5ff;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 12.5px;
            line-height: 1.45;
            margin-bottom: 12px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px 0;
            font-size: 12px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: left;
        }

        table.data-table th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #1e293b;
        }

        /* 4-Party Signatures Grid for External Repair (2 Columns x 2 Rows) */
        .signatures-grid-4, .signatures-grid-2x2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 12px;
        }

        .sig-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            background: #ffffff;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 148px;
            font-size: 11.5px;
        }

        .sig-card-purple {
            border-color: #c084fc;
            background: #fbf8ff;
        }

        .sig-header {
            font-weight: 700;
            color: #1e293b;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 5px;
            margin-bottom: 6px;
            font-size: 11.5px;
            line-height: 1.3;
        }

        .sig-header-purple {
            color: #6b21a8;
            border-bottom-color: #d8b4fe;
        }

        .sig-body {
            font-size: 11px;
            color: #475569;
            text-align: left;
            margin-bottom: 6px;
            line-height: 1.4;
            padding: 0 4px;
        }

        .sig-space {
            height: 38px;
        }

        .sig-line {
            width: 78%;
            max-width: 250px;
            border-bottom: 1.5px dotted #64748b;
            margin: 0 auto 4px auto;
        }

        .sig-name {
            font-size: 11px;
            color: #0f172a;
            font-weight: 600;
            line-height: 1.25;
        }

        .sig-pos {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.2;
        }

        .sig-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        .chkbox {
            font-size: 12px;
            font-family: monospace;
            display: inline-block;
            margin-right: 2px;
        }

        /* 2-Party Signatures for Standard Job Ticket */
        .sign-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 30px;
            text-align: center;
        }

        .sign-line-2 {
            width: 220px;
            margin: 36px auto 6px auto;
            border-bottom: 1px solid #000;
        }

        /* Document Footer */
        .doc-footer {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #94a3b8;
        }

        /* ============================================================
           VIEW 2: Official Memo (บันทึกข้อความ) Specific Styles
           ============================================================ */
        .memo-container {
            padding: 10px 15px;
            color: #000;
            font-size: 14px;
            line-height: 1.6;
        }

        .memo-top-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 8px;
            position: relative;
        }

        .memo-garuda-wrap {
            position: absolute;
            left: 0;
            top: 0;
            width: 60px;
            text-align: left;
        }

        .memo-title {
            width: 100%;
            text-align: center;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1px;
            padding-top: 10px;
        }

        .memo-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            margin-bottom: 14px;
        }

        .memo-header-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 14px;
            border: none;
        }

        .memo-body {
            text-align: justify;
            text-justify: inter-cluster;
            line-height: 1.65;
            font-size: 13.5px;
            margin-top: 14px;
        }

        .memo-indent {
            text-indent: 50px;
            margin-bottom: 10px;
        }

        .memo-sign-officer {
            margin-top: 24px;
            margin-left: 55%;
            text-align: center;
        }

        .memo-approval-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 24px;
            border-top: 1px dashed #999;
            padding-top: 14px;
        }

        .memo-approval-box {
            border: 1px solid #777;
            border-radius: 4px;
            padding: 10px 12px;
            font-size: 12.5px;
            line-height: 1.45;
            background: #fff;
        }

        .memo-approval-title {
            font-weight: 700;
            text-decoration: underline;
            margin-bottom: 6px;
            font-size: 13px;
        }

        /* Strict Print Rules for A4 Single Page */
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 12px !important;
                line-height: 1.35 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .sheet {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                min-height: auto !important;
            }

            .sheet-doc {
                display: none !important;
            }

            .sheet-doc.active {
                display: block !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .section-title {
                margin: 10px 0 6px 0 !important;
                padding-bottom: 2px !important;
            }

            .info-grid {
                gap: 5px 14px !important;
                margin-bottom: 6px !important;
            }

            .desc-box, .highlight-box {
                padding: 6px 10px !important;
                margin-bottom: 6px !important;
            }

            .signatures-grid-4, .signatures-grid-2x2 {
                gap: 8px !important;
                margin-top: 8px !important;
            }

            .sig-card {
                min-height: 125px !important;
                padding: 6px 10px !important;
            }

            .sig-space {
                height: 30px !important;
            }

            .doc-footer {
                margin-top: 8px !important;
                padding-top: 4px !important;
            }
        }
    </style>
</head>
<body>

    <!-- SCREEN-ONLY CONTROL BAR -->
    <div class="no-print-bar">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <a href="{{ route('repairs.show', $repair) }}" class="btn-back">
                &larr; กลับหน้ารายละเอียดใบแจ้งซ่อม
            </a>

            <!-- Form Switcher Tabs -->
            <div class="tab-group" role="tablist">
                <button type="button" id="tab-external" class="tab-btn {{ $printType === 'external' ? 'active-external' : '' }}" onclick="switchDocType('external')">
                    📋 ใบขออนุมัติส่งซ่อมภายนอก (เสนอ หัวหน้า / ผอ.)
                </button>
                <button type="button" id="tab-memo" class="tab-btn {{ $printType === 'memo' ? 'active-memo' : '' }}" onclick="switchDocType('memo')">
                    📑 บันทึกข้อความขอส่งซ่อม (แบบราชการ)
                </button>
                <button type="button" id="tab-standard" class="tab-btn {{ $printType === 'standard' ? 'active' : '' }}" onclick="switchDocType('standard')">
                    📄 ใบงานซ่อมบำรุงทั่วไป (Standard Job Ticket)
                </button>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button class="btn-print" onclick="window.print()">
                🖨️ สั่งพิมพ์เอกสาร A4 (Print)
            </button>
        </div>
    </div>

    <!-- MAIN PRINTABLE PAPER CONTAINER -->
    <div class="sheet">

        <!-- =================================================================================
             DOCUMENT 1: ใบสั่งซ่อมบำรุงและขออนุมัติส่งซ่อมภายนอก (External Repair Request Ticket)
             ================================================================================= -->
        <div id="doc-external" class="sheet-doc {{ $printType === 'external' ? 'active' : '' }}">
            <!-- Header -->
            <div class="header header-external">
                <div class="header-left">
                    <img src="{{ asset('images/logo.png') }}?v=2" alt="Hospital Logo" class="logo-box-img" onerror="this.style.display='none'; document.getElementById('logoFallback1').style.display='flex';">
                    <div id="logoFallback1" class="logo-box logo-box-purple" style="display: none;">IT</div>
                    <div class="header-title">
                        <h1 style="color: #581c87;">ใบสั่งซ่อมบำรุงระบบสารสนเทศ (Service Job Ticket)</h1>
                        <h2 style="color: #7e22ce;">แบบเสนอขออนุมัติส่งซ่อมบำรุงภายนอก (External Repair Request)</h2>
                        <h3>{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} &bull; {{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }}</h3>
                    </div>
                </div>
                <div class="header-right">
                    <div class="ticket-no ticket-no-purple">{{ $repair->ticket_number }}</div>
                    <div>
                        <span class="badge-external">🟣 สถานะ: ส่งซ่อมภายนอก (External)</span>
                    </div>
                    <div>
                        <span class="badge-urgency">ความเร่งด่วน: {{ $repair->urgency_label }}</span>
                    </div>
                </div>
            </div>

            <!-- Banner info -->
            <div class="proposal-banner">
                <div>
                    <strong><i class="bi bi-file-earmark-check"></i> เอกสารเสนอขออนุมัติส่งซ่อมบำรุงภายนอกโรงพยาบาล</strong>
                    <div style="font-size: 11.5px; color: #6b21a8; margin-top: 1px;">
                        เสนอหัวหน้ากลุ่มงานสุขภาพดิจิทัล เพื่อโปรดพิจารณารับทราบ และเสนอผู้อำนวยการโรงพยาบาลเพื่อโปรดพิจารณาอนุมัติ
                    </div>
                </div>
                <div style="text-align: right; font-size: 11.5px; color: #581c87;">
                    วันที่พิมพ์: {{ thai_date(now(), 'short_time') }}
                </div>
            </div>

            <!-- Section 1: ข้อมูลผู้ขอรับบริการและสถานที่ -->
            <div class="section-title section-title-purple">
                <span>1. ข้อมูลผู้แจ้งและหน่วยงานที่ขอรับบริการ</span>
                <span style="font-size: 11px; font-weight: normal; color: #64748b;">แจ้งเมื่อ: {{ thai_date($repair->created_at, 'short_time') }}</span>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">ผู้แจ้งซ่อม:</div>
                    <div class="info-val"><strong>{{ $repair->requester_name }}</strong></div>
                </div>
                <div class="info-item">
                    <div class="info-label">แผนก / หน่วยงาน:</div>
                    <div class="info-val">{{ $repair->department?->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">เบอร์โทรติดต่อ:</div>
                    <div class="info-val">{{ $repair->requester_phone }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">จุดที่ตั้ง / ห้องทำงาน:</div>
                    <div class="info-val">{{ $repair->location_detail ?? '-' }}</div>
                </div>
            </div>

            <!-- Section 2: ข้อมูลอุปกรณ์และครุภัณฑ์ -->
            <div class="section-title section-title-purple">
                <span>2. ข้อมูลพัสดุและครุภัณฑ์คอมพิวเตอร์ที่ส่งซ่อม</span>
            </div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">หมายเลขครุภัณฑ์:</div>
                    <div class="info-val"><strong style="color: #0f766e;">{{ $repair->asset?->asset_code ?? 'ไม่มีรหัสครุภัณฑ์ (อุปกรณ์ทั่วไป)' }}</strong></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Serial Number (S/N):</div>
                    <div class="info-val">{{ $repair->asset?->serial_number ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">รายการ / ชนิดอุปกรณ์:</div>
                    <div class="info-val">{{ $repair->asset?->name ?? ($repair->other_device_info ?? '-') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ยี่ห้อ / รุ่น:</div>
                    <div class="info-val">{{ $repair->asset ? $repair->asset->brand . ' ' . $repair->asset->model : '-' }}</div>
                </div>
            </div>

            <!-- Section 3: ปัญหาและอาการเสียที่ได้รับแจ้ง -->
            <div class="section-title section-title-purple">
                <span>3. ปัญหาและอาการเสียที่ได้รับแจ้ง</span>
            </div>
            <div class="desc-box">
                <div><strong>หัวข้อปัญหา:</strong> {{ $repair->title }}</div>
                <div style="margin-top: 3px;"><strong>รายละเอียดอาการเสีย:</strong> {{ $repair->description }}</div>
            </div>

            <!-- Section 4: การตรวจสภาพและเหตุผลความจำเป็นในการส่งซ่อมภายนอก -->
            <div class="section-title section-title-purple">
                <span>4. ผลการตรวจสภาพและการประเมินความจำเป็นส่งซ่อมภายนอก (ช่างเทคนิค)</span>
            </div>
            <div class="highlight-box">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px 14px; margin-bottom: 6px;">
                    <div>
                        <strong>ช่างเทคนิคผู้ตรวจสภาพ:</strong> {{ $repair->technician?->name ?? 'เจ้าหน้าที่กลุ่มงานสุขภาพดิจิทัล' }}
                    </div>
                    <div>
                        <strong>วันที่ตรวจรับเรื่อง:</strong> {{ $repair->received_at ? thai_date($repair->received_at, 'short_time') : thai_date($repair->created_at, 'short_time') }}
                    </div>
                </div>
                <div>
                    <strong>สาเหตุของปัญหา / ผลการตรวจวินิจฉัย:</strong>
                    <span>{{ $repair->cause ?: 'อุปกรณ์ฮาร์ดแวร์ชำรุดเสียหาย ชิ้นส่วนหลักขัดข้อง ไม่สามารถดำเนินการซ่อมแซมได้เองภายในโรงพยาบาล' }}</span>
                </div>
                <div style="margin-top: 4px;">
                    <strong>เหตุผลความจำเป็นในการส่งซ่อมภายนอก:</strong>
                    <span>เกินขีดความสามารถของเจ้าหน้าที่ในการซ่อมแซมเองภายในโรงพยาบาล จำเป็นต้องอาศัยเครื่องมือเฉพาะทาง อะไหล่ตรงรุ่น หรือส่งซ่อม/เคลม ณ ศูนย์บริการตัวแทนจำหน่ายภายนอก</span>
                </div>
                <div style="margin-top: 4px; display: grid; grid-template-columns: 1.5fr 1fr; gap: 8px;">
                    <div>
                        <strong>ร้านค้า / ศูนย์บริการภายนอกที่ส่งซ่อม:</strong>
                        <span style="color: #6b21a8; font-weight: bold;">{{ $repair->external_repair_detail ?: 'ศูนย์บริการตัวแทนจำหน่าย / ร้านซ่อมภายนอกที่ได้มาตรฐาน' }}</span>
                    </div>
                    <div>
                        <strong>ประมาณการค่าใช้จ่าย:</strong>
                        <span>{{ $repair->total_cost > 0 ? number_format($repair->total_cost, 2) . ' บาท' : 'อยู่ระหว่างรอประเมินราคา / รอใบเสนอราคา' }}</span>
                    </div>
                </div>
                @if($repair->solution)
                <div style="margin-top: 4px;">
                    <strong>แนวทาง/แผนการดำเนินการ:</strong> {{ $repair->solution }}
                </div>
                @endif
            </div>

            <!-- Section 5: การเสนอขออนุมัติและลงนาม 4 ฝ่าย (จัดเรียง 2 แถว) -->
            <div class="section-title section-title-purple">
                <span>5. การเสนอความเห็นและลงนามอนุมัติ (เสนอ หัวหน้ารับทราบ / ผอ. เซ็นอนุมัติ)</span>
            </div>
            <div class="signatures-grid-2x2">
                <!-- แถวที่ 1 - ฝั่งซ้าย: 1. ผู้แจ้งซ่อม / ตรวจรับ -->
                <div class="sig-card">
                    <div>
                        <div class="sig-header">1. ผู้แจ้งซ่อม / ตรวจรับ</div>
                        <div class="sig-body">
                            รับทราบการส่งซ่อมภายนอก / ตรวจรับอุปกรณ์คืนในสภาพเรียบร้อย
                        </div>
                    </div>
                    <div>
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">({{ $repair->requester_name }})</div>
                        <div class="sig-pos">ผู้แจ้งซ่อม ({{ $repair->department?->name }})</div>
                        <div class="sig-date">วันที่ ...... / ...... / ..........</div>
                    </div>
                </div>

                <!-- แถวที่ 1 - ฝั่งขวา: 2. ช่างผู้ตรวจสภาพ / ผู้เสนอ -->
                <div class="sig-card">
                    <div>
                        <div class="sig-header">2. ช่างผู้ตรวจสภาพ / ผู้เสนอ</div>
                        <div class="sig-body">
                            ตรวจสภาพแล้วพบว่าไม่สามารถซ่อมแซมเองได้ เห็นควรเสนอขออนุมัติส่งซ่อมบำรุงภายนอก
                        </div>
                    </div>
                    <div>
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">({{ $repair->technician?->name ?? '..........................................................' }})</div>
                        <div class="sig-pos">{{ $repair->technician?->position ?? 'เจ้าหน้าที่เทคโนโลยีสารสนเทศ' }}</div>
                        <div class="sig-date">วันที่ ...... / ...... / ..........</div>
                    </div>
                </div>

                <!-- แถวที่ 2 - ฝั่งซ้าย: 3. หัวหน้ากลุ่มงาน -->
                <div class="sig-card">
                    <div>
                        <div class="sig-header">3. หัวหน้ากลุ่มงาน (รับทราบ)</div>
                        <div class="sig-body">
                            เรียน ผู้อำนวยการโรงพยาบาล<br>
                            <span class="chkbox">&#9744;</span> รับทราบ และเห็นควรเสนอขออนุมัติ<br>
                            <span class="chkbox">&#9744;</span> อื่นๆ ....................................................................
                        </div>
                    </div>
                    <div>
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">({{ setting('it_head_name', 'หัวหน้ากลุ่มงานสุขภาพดิจิทัล') }})</div>
                        <div class="sig-pos">หัวหน้า{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }}</div>
                        <div class="sig-date">วันที่ ...... / ...... / ..........</div>
                    </div>
                </div>

                <!-- แถวที่ 2 - ฝั่งขวา: 4. คำสั่งการ (ผอ. เซ็นอนุมัติ) -->
                <div class="sig-card sig-card-purple">
                    <div>
                        <div class="sig-header sig-header-purple">4. คำสั่งการ (ผอ. เซ็นอนุมัติ)</div>
                        <div class="sig-body">
                            คำสั่งการผู้อำนวยการโรงพยาบาล<br>
                            <span class="chkbox">&#9744;</span> <strong>อนุมัติ</strong> ให้ส่งซ่อมภายนอกตามเสนอ<br>
                            <span class="chkbox">&#9744;</span> ไม่อนุมัติ เนื่องจาก ........................................................
                        </div>
                    </div>
                    <div>
                        <div class="sig-space"></div>
                        <div class="sig-line"></div>
                        <div class="sig-name">({{ setting('director_name', 'ผู้อำนวยการโรงพยาบาลทุ่งหัวช้าง') }})</div>
                        <div class="sig-pos">ผู้อำนวยการ{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                        <div class="sig-date">วันที่ ...... / ...... / ..........</div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="doc-footer">
                <div>ระบบบริหารจัดการสารสนเทศและงานซ่อมบำรุง {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} (เอกสารควบคุมภายใน)</div>
                <div>พิมพ์เมื่อ {{ thai_date(now(), 'short_time') }} &bull; เอกสารเสนอขออนุมัติส่งซ่อมภายนอก หน้า 1/1</div>
            </div>
        </div>


        <!-- =================================================================================
             DOCUMENT 2: บันทึกข้อความ ขออนุมัติส่งซ่อมบำรุงภายนอก (Official Government Memo)
             ================================================================================= -->
        <div id="doc-memo" class="sheet-doc {{ $printType === 'memo' ? 'active' : '' }}">
            <div class="memo-container">
                <!-- Garuda Top Header -->
                <div class="memo-top-row">
                    <div class="memo-garuda-wrap">
                        <!-- Crisp Official Garuda Vector Silhouette -->
                        <svg width="60" height="65" viewBox="0 0 100 108" fill="#a00000">
                            <path d="M50 0 C48 8, 44 14, 40 18 C36 14, 32 10, 24 10 C20 18, 22 28, 26 36 C18 36, 8 38, 0 46 C10 48, 20 48, 28 46 C24 54, 20 64, 20 74 C28 72, 36 68, 42 62 C40 72, 40 84, 42 96 C46 92, 50 88, 50 82 C50 88, 54 92, 58 96 C60 84, 60 72, 58 62 C64 68, 72 72, 80 74 C80 64, 76 54, 72 46 C80 48, 90 48, 100 46 C92 38, 82 36, 74 36 C78 28, 80 18, 76 10 C68 10, 64 14, 60 18 C56 14, 52 8, 50 0 Z M50 20 C54 26, 54 34, 50 40 C46 34, 46 26, 50 20 Z"/>
                        </svg>
                    </div>
                    <div class="memo-title">บันทึกข้อความ</div>
                </div>

                <!-- Memo Meta Table -->
                <table class="memo-header-table">
                    <tr>
                        <td style="width: 58%;">
                            <strong>ส่วนราชการ:</strong> {{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} โทร. {{ setting('hospital_phone', '053-595055') }}
                        </td>
                        <td style="width: 42%; text-align: left;">
                            <strong>ที่:</strong> สธ ................................................................
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <strong>เรื่อง:</strong> ขออนุมัติส่งซ่อมบำรุงพัสดุ/ครุภัณฑ์คอมพิวเตอร์ภายนอกโรงพยาบาล (ใบแจ้งซ่อมเลขที่ {{ $repair->ticket_number }})
                        </td>
                        <td>
                            <strong>วันที่:</strong> {{ thai_date($repair->created_at, 'full') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding-top: 6px;">
                            <strong>เรียน:</strong> ผู้อำนวยการ{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} (ผ่าน หัวหน้า{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }})
                        </td>
                    </tr>
                </table>

                <div style="border-bottom: 1.5px solid #000; margin-bottom: 14px;"></div>

                <!-- Memo Body Paragraphs -->
                <div class="memo-body">
                    <div class="memo-indent">
                        ด้วย <strong>{{ $repair->department?->name ?? 'หน่วยงาน' }}</strong> โดย <strong>{{ $repair->requester_name }}</strong> ได้ส่งคำขอแจ้งซ่อมบำรุงพัสดุ/ครุภัณฑ์คอมพิวเตอร์ หมายเลขใบแจ้งซ่อม <strong>{{ $repair->ticket_number }}</strong> รายการ <strong>{{ $repair->asset?->name ?? ($repair->other_device_info ?? 'อุปกรณ์คอมพิวเตอร์') }}</strong>
                        @if($repair->asset?->asset_code) หมายเลขครุภัณฑ์: <strong>{{ $repair->asset->asset_code }}</strong> @endif
                        @if($repair->asset?->serial_number) หมายเลขเครื่อง (S/N): <strong>{{ $repair->asset->serial_number }}</strong> @endif
                        เนื่องจากมีปัญหา/อาการชำรุดเสียหายคือ <strong>{{ $repair->title }}</strong> ({{ $repair->description }}) นั้น
                    </div>

                    <div class="memo-indent">
                        งานเทคโนโลยีสารสนเทศ {{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} ได้ดำเนินการตรวจสภาพอุปกรณ์ดังกล่าวในเบื้องต้นแล้ว ปรากฏผลการตรวจสภาพพบว่า <strong>{{ $repair->cause ?: 'ชิ้นส่วนอุปกรณ์และระบบฮาร์ดแวร์หลักชำรุดเสียหาย ขัดข้อง เกินขีดความสามารถของเจ้าหน้าที่ในการซ่อมแซมได้เองภายในโรงพยาบาล' }}</strong> จึงมีความจำเป็นต้องส่งซ่อมบำรุง ณ ร้านค้า/ศูนย์บริการภายนอกที่มีเครื่องมือเฉพาะทาง ได้แก่ <strong>{{ $repair->external_repair_detail ?: 'ศูนย์บริการตัวแทนจำหน่าย/ร้านค้าผู้ชำนาญการภายนอก' }}</strong>
                        @if($repair->total_cost > 0)
                            โดยมีประมาณการค่าใช้จ่ายเบื้องต้นจำนวน <strong>{{ number_format($repair->total_cost, 2) }} บาท</strong>
                        @endif
                    </div>

                    <div class="memo-indent">
                        เพื่อให้พัสดุและอุปกรณ์ดังกล่าวสามารถนำกลับมาใช้งานในการให้บริการทางการแพทย์และการปฏิบัติงานราชการได้อย่างต่อเนื่องและมีประสิทธิภาพ จึงเรียนมาเพื่อโปรดพิจารณา:
                    </div>

                    <div style="margin-left: 60px; line-height: 1.6; margin-bottom: 10px;">
                        1. รับทราบรายงานผลการตรวจสภาพและความจำเป็นในการส่งซ่อมบำรุงภายนอก<br>
                        2. อนุมัติให้ดำเนินการส่งซ่อมบำรุงพัสดุดังกล่าว ณ ร้านค้า/ศูนย์บริการภายนอกตามเสนอ
                    </div>
                </div>

                <!-- Memo Signature 1: Officer -->
                <div class="memo-sign-officer">
                    <div style="margin-bottom: 24px;">(ลงชื่อ).................................................................... ผู้รายงาน/เสนอ</div>
                    <div>( {{ $repair->technician?->name ?? '....................................................................' }} )</div>
                    <div style="font-size: 12.5px; color: #444; margin-top: 2px;">ตำแหน่ง: {{ $repair->technician?->position ?? 'เจ้าหน้าที่เทคโนโลยีสารสนเทศ' }}</div>
                    <div style="font-size: 12.5px; color: #444; margin-top: 2px;">วันที่ ...... / ...... / ..........</div>
                </div>

                <!-- Memo Bottom Approvals: Head & Director -->
                <div class="memo-approval-grid">
                    <!-- Head Approval Box -->
                    <div class="memo-approval-box">
                        <div class="memo-approval-title">ความเห็นของหัวหน้ากลุ่มงาน (รับทราบ)</div>
                        <div>เรียน ผู้อำนวยการ{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                        <div style="margin: 4px 0;">
                            <span class="chkbox">&#9744;</span> รับทราบ และเห็นควรเสนอขออนุมัติส่งซ่อมภายนอกตามเสนอ<br>
                            <span class="chkbox">&#9744;</span> อื่นๆ ...................................................................................
                        </div>
                        <div style="text-align: center; margin-top: 22px;">
                            <div>(ลงชื่อ)....................................................................</div>
                            <div style="margin-top: 2px; font-weight: bold;">( {{ setting('it_head_name', 'หัวหน้ากลุ่มงานสุขภาพดิจิทัล') }} )</div>
                            <div style="font-size: 11.5px; color: #555; margin-top: 1px;">หัวหน้า{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }}</div>
                            <div style="font-size: 11.5px; color: #555; margin-top: 1px;">วันที่ ...... / ...... / ..........</div>
                        </div>
                    </div>

                    <!-- Director Approval Box -->
                    <div class="memo-approval-box" style="border-width: 1.5px; background: #fafafa;">
                        <div class="memo-approval-title" style="color: #0f172a;">คำสั่งการ / การอนุมัติ (ผอ. เซ็นอนุมัติ)</div>
                        <div style="margin: 6px 0;">
                            <span class="chkbox">&#9744;</span> <strong>อนุมัติ</strong> ให้ดำเนินการส่งซ่อมภายนอกตามเสนอ<br>
                            <span class="chkbox">&#9744;</span> ไม่อนุมัติ เนื่องจาก ..............................................................
                        </div>
                        <div style="text-align: center; margin-top: 26px;">
                            <div>(ลงชื่อ)....................................................................</div>
                            <div style="margin-top: 2px; font-weight: bold;">( {{ setting('director_name', 'ผู้อำนวยการโรงพยาบาลทุ่งหัวช้าง') }} )</div>
                            <div style="font-size: 11.5px; color: #555; margin-top: 1px;">ผู้อำนวยการ{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                            <div style="font-size: 11.5px; color: #555; margin-top: 1px;">วันที่ ...... / ...... / ..........</div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="doc-footer" style="margin-top: 18px;">
                    <div>ระบบบันทึกข้อความราชการ {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                    <div>พิมพ์เมื่อ {{ thai_date(now(), 'short_time') }} &bull; หน้า 1/1</div>
                </div>
            </div>
        </div>


        <!-- =================================================================================
             DOCUMENT 3: ใบสั่งซ่อมบำรุงทั่วไป (Standard Job Ticket)
             ================================================================================= -->
        <div id="doc-standard" class="sheet-doc {{ $printType === 'standard' ? 'active' : '' }}">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <img src="{{ asset('images/logo.png') }}?v=2" alt="Hospital Logo" class="logo-box-img" onerror="this.style.display='none'; document.getElementById('logoFallback2').style.display='flex';">
                    <div id="logoFallback2" class="logo-box" style="display: none;">IT</div>
                    <div class="header-title">
                        <h1>ใบสั่งซ่อมบำรุงระบบสารสนเทศ (Service Job Ticket)</h1>
                        <h2>{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</h2>
                    </div>
                </div>
                <div class="header-right">
                    <div class="ticket-no">{{ $repair->ticket_number }}</div>
                    <div>
                        <span class="badge-urgency">ความเร่งด่วน: {{ $repair->urgency_label }}</span>
                    </div>
                </div>
            </div>

            <!-- Requester & Location Info -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">วันที่แจ้งซ่อม:</div>
                    <div class="info-val">{{ thai_date($repair->created_at, 'short_time') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">แผนก/หน่วยงาน:</div>
                    <div class="info-val">{{ $repair->department?->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ชื่อผู้แจ้ง:</div>
                    <div class="info-val"><strong>{{ $repair->requester_name }}</strong></div>
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
                    <div class="info-label">สถานะงานซ่อม:</div>
                    <div class="info-val" style="font-weight: bold; color: #0f766e;">{{ $repair->status_label }}</div>
                </div>
            </div>

            <!-- Asset / Equipment Info -->
            <div class="section-title">ข้อมูลอุปกรณ์ที่แจ้งซ่อม</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">รหัสครุภัณฑ์:</div>
                    <div class="info-val"><strong>{{ $repair->asset?->asset_code ?? 'ไม่มีรหัสครุภัณฑ์' }}</strong></div>
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
            <div class="desc-box">
                <strong>ช่างผู้รับผิดชอบ:</strong> {{ $repair->technician?->name ?? '........................................................' }}<br>
                <strong>สาเหตุของปัญหา:</strong> {{ $repair->cause ?? '-' }}<br>
                <strong>วิธีการแก้ไข:</strong> {{ $repair->solution ?? '-' }}
                @if($repair->external_repair_detail)
                <br><strong style="color: #7e22ce;">ส่งซ่อมภายนอก:</strong> {{ $repair->external_repair_detail }}
                @endif
            </div>

            <!-- Spare parts table -->
            <div class="section-title">รายการอะไหล่และอุปกรณ์ที่ใช้</div>
            <table class="data-table">
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

            <!-- Standard 2-party Signatures -->
            <div class="sign-grid-2">
                <div>
                    <div>ลงชื่อ..........................................................ผู้แจ้งซ่อม/ผู้รับเครื่องคืน</div>
                    <div style="margin-top: 4px; font-weight: 600;">( {{ $repair->requester_name }} )</div>
                    <div style="margin-top: 2px; font-size: 11.5px; color: #666;">วันที่ ......../......../............</div>
                </div>

                <div>
                    <div>ลงชื่อ..........................................................ช่างผู้ดำเนินการ</div>
                    <div style="margin-top: 4px; font-weight: 600;">( {{ $repair->technician?->name ?? '..........................................................' }} )</div>
                    <div style="margin-top: 2px; font-size: 11.5px; color: #666;">วันที่ ......../......../............</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="doc-footer">
                <div>ระบบบริหารจัดการสารสนเทศและงานซ่อมบำรุง {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }}</div>
                <div>พิมพ์เมื่อ {{ thai_date(now(), 'short_time') }} &bull; ใบงานซ่อมมาตรฐาน หน้า 1/1</div>
            </div>
        </div>

    </div>

    <!-- JavaScript to toggle views and update page title -->
    <script>
        const ticketNo = "{{ $repair->ticket_number }}";

        function switchDocType(type) {
            // Hide all documents
            document.querySelectorAll('.sheet-doc').forEach(el => el.classList.remove('active'));
            // Remove active styles from buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'active-external', 'active-memo');
            });

            const docEl = document.getElementById('doc-' + type);
            const btnEl = document.getElementById('tab-' + type);
            const titleEl = document.getElementById('pageTitle');

            if (docEl && btnEl) {
                docEl.classList.add('active');

                if (type === 'external') {
                    btnEl.classList.add('active-external');
                    titleEl.innerText = 'ใบขออนุมัติส่งซ่อมภายนอก - ' + ticketNo;
                } else if (type === 'memo') {
                    btnEl.classList.add('active-memo');
                    titleEl.innerText = 'บันทึกข้อความขออนุมัติส่งซ่อม - ' + ticketNo;
                } else {
                    btnEl.classList.add('active');
                    titleEl.innerText = 'ใบสั่งซ่อมบำรุง - ' + ticketNo;
                }
            }

            // Update URL without reload for direct sharing/refresh
            const url = new URL(window.location);
            url.searchParams.set('type', type);
            window.history.replaceState({}, '', url);
        }
    </script>
</body>
</html>
