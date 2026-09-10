<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มขอข้อมูลสารสนเทศ - {{ $dataRequest->request_no }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            margin: 0;
            padding: 24px 16px;
            color: #0f172a;
            background: #cbd5e1;
            font-size: 13px;
            line-height: 1.45;
        }

        /* Top control bar - Screen only */
        .no-print-bar {
            width: 210mm;
            max-width: 100%;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .bar-left, .bar-right {
            display: flex;
            align-items: center;
            gap: 12px;
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
            font-family: 'Sarabun', sans-serif;
            transition: all 0.2s ease;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
        }
        .btn-primary {
            background: #0d9488;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(13, 148, 136, 0.35);
        }
        .btn-primary:hover {
            background: #0f766e;
        }

        .a4-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: #0f766e;
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
        }

        .scale-selector {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: #475569;
        }

        .scale-selector select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1.5px solid #cbd5e1;
            font-family: inherit;
            font-size: 12.5px;
            background: #fff;
            color: #0f172a;
            font-weight: 500;
            cursor: pointer;
        }

        /* The Full A4 Sheet (210mm x 297mm) */
        .sheet {
            width: 210mm;
            min-height: 297mm;
            height: 297mm;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 12mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
            border-radius: 2px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        /* Hospital Official Header */
        .doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2.5px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .hospital-emblem {
            width: 56px;
            height: 56px;
            border: 2px solid #0f766e;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #0f766e;
            background: #f0fdfa;
            flex-shrink: 0;
        }

        .hospital-title h1 {
            margin: 0;
            font-size: 16.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
        }
        .hospital-title h2 {
            margin: 3px 0 0 0;
            font-size: 13.5px;
            font-weight: 500;
            color: #0f766e;
            line-height: 1.25;
        }
        .hospital-title h3 {
            margin: 2px 0 0 0;
            font-size: 11px;
            font-weight: 400;
            color: #64748b;
            line-height: 1.2;
        }

        .header-meta {
            text-align: right;
            line-height: 1.3;
        }
        .req-badge-title {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .req-no {
            font-size: 18px;
            font-weight: 800;
            color: #0f766e;
            letter-spacing: 0.5px;
        }
        .urgency-badge {
            display: inline-block;
            padding: 2.5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 3px;
        }
        .urgency-normal { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .urgency-urgent { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .urgency-very_urgent { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        /* Form Title Banner */
        .doc-title-box {
            text-align: center;
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
        }
        .doc-title-box h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.35;
        }
        .doc-title-box div {
            font-size: 11.5px;
            color: #475569;
            margin-top: 2px;
            line-height: 1.3;
        }

        /* Section Boxes */
        .section-box {
            margin-bottom: 12px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            background: #ffffff;
        }
        .section-title {
            background: #f1f5f9;
            padding: 5px 12px;
            font-weight: 700;
            font-size: 12.5px;
            color: #1e293b;
            border-bottom: 1.5px solid #cbd5e1;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .section-body {
            padding: 10px 14px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 20px;
        }
        .data-row {
            display: flex;
            align-items: baseline;
            font-size: 12.5px;
            line-height: 1.4;
        }
        .data-label {
            width: 140px;
            flex-shrink: 0;
            color: #475569;
            font-weight: 500;
        }
        .data-val {
            flex-grow: 1;
            color: #0f172a;
            font-weight: 600;
        }

        .detail-content {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            white-space: pre-line;
            line-height: 1.45;
            margin-top: 6px;
            min-height: 65px;
            max-height: 95px;
            overflow: hidden;
        }

        /* PDPA Compliance Statement */
        .pdpa-box {
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 11px;
            color: #166534;
            line-height: 1.4;
            margin-bottom: 12px;
        }
        .pdpa-title {
            font-weight: 700;
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11.5px;
        }

        /* 4 Parties Signatures */
        .signatures-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 6px;
        }
        .sig-card {
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 6px;
            text-align: center;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 105px;
        }
        .sig-title {
            font-weight: 700;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.3;
            margin-bottom: 24px;
        }
        .sig-line {
            border-bottom: 1.5px dotted #64748b;
            width: 88%;
            margin: 0 auto 5px auto;
        }
        .sig-name {
            font-size: 10.5px;
            color: #334155;
            line-height: 1.25;
            word-break: break-word;
        }
        .sig-date {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
            line-height: 1.2;
        }

        /* Document Footer */
        .doc-footer {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1.5px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: #94a3b8;
            line-height: 1.25;
        }

        /* STRICT FULL-PAGE A4 PRINT ENFORCEMENT */
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }

        @media print {
            html, body {
                width: 210mm !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                font-size: 12.5px !important;
                line-height: 1.4 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .sheet {
                width: 100% !important;
                max-width: 100% !important;
                height: 277mm !important;
                min-height: 277mm !important;
                max-height: 277mm !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                box-sizing: border-box !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-after: avoid !important;
                break-inside: avoid !important;
            }

            .doc-header, 
            .doc-title-box, 
            .section-box, 
            .pdpa-box, 
            .signatures-grid, 
            .doc-footer {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .detail-content {
                max-height: 90px !important;
                overflow: hidden !important;
            }

            .sig-card {
                min-height: 102px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Toolbar (Screen only) -->
    <div class="no-print-bar">
        <div class="bar-left">
            <a href="{{ route('data-requests.show', $dataRequest->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> ย้อนกลับหน้ารายละเอียด
            </a>
            <span class="a4-badge">
                <i class="bi bi-file-earmark-check-fill"></i> จัดหน้าพิมพ์เต็ม 1 หน้ากระดาษ A4 (Full-page A4 Fit)
            </span>
        </div>
        <div class="bar-right">
            <div class="scale-selector">
                <span>ปรับขนาด:</span>
                <select id="scaleSelect" onchange="adjustPrintScale(this.value)">
                    <option value="1" selected>100% เต็มหน้า A4 มาตรฐาน</option>
                    <option value="0.97">97% กะทัดรัด</option>
                    <option value="0.94">94% ข้อมูลยาวพิเศษ</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer"></i> สั่งพิมพ์เอกสาร A4 (Print)
            </button>
        </div>
    </div>

    <!-- Printable A4 Document Sheet (Fills full 297mm height) -->
    <div class="sheet" id="printableSheet">
        <!-- TOP SECTION: HEADER, TITLE & BODY SECTIONS -->
        <div>
            <!-- Hospital Header -->
            <div class="doc-header">
                <div class="header-brand">
                    <div class="hospital-emblem">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div class="hospital-title">
                        <h1>{{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} {{ setting('hospital_address') ? '(' . Str::limit(setting('hospital_address'), 40) . ')' : '' }}</h1>
                        <h2>{{ setting('department_name', 'กลุ่มงานสุขภาพดิจิทัล') }} (งานคอมพิวเตอร์และเวชสารสนเทศ)</h2>
                        <h3>{{ setting('hospital_name_en', 'Thung Hua Chang Hospital') }} &bull; Digital Health Division</h3>
                    </div>
                </div>
                <div class="header-meta">
                    <div class="req-badge-title">เลขที่คำขอ / Request No.</div>
                    <div class="req-no">{{ $dataRequest->request_no }}</div>
                    <div>
                        <span class="urgency-badge urgency-{{ $dataRequest->urgency }}">
                            ความเร่งด่วน: {{ $dataRequest->urgency_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Document Title -->
            <div class="doc-title-box">
                <h2>แบบฟอร์มขอรับบริการข้อมูลสารสนเทศทางการแพทย์และสถิติ</h2>
                <div>ตามระเบียบการขอใช้ข้อมูลระบบสารสนเทศโรงพยาบาล และ พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562</div>
            </div>

            <!-- Section 1: ข้อมูลผู้ขอ -->
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-person-badge"></i>
                    <span>ส่วนที่ 1: ข้อมูลผู้ยื่นคำขอและหน่วยงาน</span>
                </div>
                <div class="section-body">
                    <div class="data-grid">
                        <div class="data-row">
                            <span class="data-label">ชื่อ - สกุล ผู้ยื่นคำขอ:</span>
                            <span class="data-val">{{ $dataRequest->user->name ?? '-' }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">ตำแหน่ง:</span>
                            <span class="data-val">{{ $dataRequest->user->position ?? 'บุคลากร' }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">กลุ่มงาน / แผนก:</span>
                            <span class="data-val">{{ $dataRequest->department->name ?? '-' }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">เบอร์โทรศัพท์ติดต่อ:</span>
                            <span class="data-val">{{ $dataRequest->user->phone ?? 'ไม่ระบุ' }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">วันที่ยื่นคำขอ:</span>
                            <span class="data-val">{{ $dataRequest->created_at->format('d/m/Y H:i') }} น.</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">สถานะปัจจุบัน:</span>
                            <span class="data-val">{{ $dataRequest->status_label }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: รายละเอียดข้อมูลที่ขอ -->
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-file-earmark-text"></i>
                    <span>ส่วนที่ 2: วัตถุประสงค์และรายละเอียดของข้อมูลที่ขอ</span>
                </div>
                <div class="section-body">
                    <div class="data-row" style="margin-bottom: 6px;">
                        <span class="data-label">หัวข้อข้อมูลที่ต้องการ:</span>
                        <span class="data-val" style="font-size: 13.5px; color: #0f766e;">{{ $dataRequest->title }}</span>
                    </div>

                    <div class="data-grid" style="margin-bottom: 6px;">
                        <div class="data-row">
                            <span class="data-label">วัตถุประสงค์การนำไปใช้:</span>
                            <span class="data-val">{{ $dataRequest->objective_label }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">รูปแบบไฟล์ที่ต้องการ:</span>
                            <span class="data-val" style="text-transform: uppercase;">{{ $dataRequest->file_format }}</span>
                        </div>
                    </div>

                    @if($dataRequest->objective_detail)
                    <div class="data-row" style="margin-bottom: 6px;">
                        <span class="data-label">รายละเอียดวัตถุประสงค์:</span>
                        <span class="data-val" style="font-weight: normal;">{{ $dataRequest->objective_detail }}</span>
                    </div>
                    @endif

                    <div class="data-row" style="margin-bottom: 6px;">
                        <span class="data-label">ช่วงเวลาข้อมูลที่ต้องการ:</span>
                        <span class="data-val">
                            @if($dataRequest->data_start_date && $dataRequest->data_end_date)
                                ตั้งแต่วันที่ {{ $dataRequest->data_start_date->format('d/m/Y') }} ถึงวันที่ {{ $dataRequest->data_end_date->format('d/m/Y') }}
                            @elseif($dataRequest->data_start_date)
                                ตั้งแต่วันที่ {{ $dataRequest->data_start_date->format('d/m/Y') }} เป็นต้นไป
                            @else
                                ไม่ระบุช่วงเวลาชัดเจน
                            @endif
                        </span>
                    </div>

                    <div>
                        <span class="data-label" style="display: block; margin-bottom: 3px;">เงื่อนไขและคอลัมน์ข้อมูลที่ต้องการ (Criteria & Fields):</span>
                        <div class="detail-content">{{ $dataRequest->criteria_detail }}</div>
                    </div>
                </div>
            </div>

            <!-- Section 3: PDPA Consent Statement -->
            <div class="pdpa-box">
                <div class="pdpa-title">
                    <i class="bi bi-shield-check"></i>
                    <span>คำรับรองการคุ้มครองข้อมูลส่วนบุคคล (PDPA Compliance Statement)</span>
                </div>
                <div>
                    ข้าพเจ้ารับรองว่าข้อมูลสารสนเทศทางการแพทย์และสถิติที่ได้รับ จะถูกนำไปใช้เพื่อประโยชน์ในการพัฒนาคุณภาพบริการ การศึกษาวิจัย หรือการบริหารงานตามวัตถุประสงค์ที่ระบุไว้เท่านั้น และขอรับรองว่าจะปฏิบัติตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 โดยไม่เปิดเผยข้อมูลส่วนบุคคลหรือข้อมูลสุขภาพแก่บุคคลภายนอกโดยมิได้รับอนุญาต
                </div>
            </div>

            <!-- Section 4: การดำเนินการของกลุ่มงานสุขภาพดิจิทัล -->
            <div class="section-box">
                <div class="section-title">
                    <i class="bi bi-tools"></i>
                    <span>ส่วนที่ 3: ผลการดำเนินการของกลุ่มงานสุขภาพดิจิทัล (สำหรับเจ้าหน้าที่ IT)</span>
                </div>
                <div class="section-body">
                    <div class="data-grid">
                        <div class="data-row">
                            <span class="data-label">เจ้าหน้าที่ผู้รับผิดชอบ:</span>
                            <span class="data-val">{{ $dataRequest->handler->name ?? '..........................................................' }}</span>
                        </div>
                        <div class="data-row">
                            <span class="data-label">วันที่ดำเนินการเสร็จสิ้น:</span>
                            <span class="data-val">{{ $dataRequest->completed_at ? $dataRequest->completed_at->format('d/m/Y H:i') . ' น.' : '..........................................................' }}</span>
                        </div>
                    </div>

                    @if($dataRequest->admin_notes)
                    <div class="data-row" style="margin-top: 6px;">
                        <span class="data-label">หมายเหตุการส่งมอบ:</span>
                        <span class="data-val" style="font-weight: normal;">{{ $dataRequest->admin_notes }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- BOTTOM SECTION: 4 SIGNATURE CARDS & FOOTER -->
        <div>
            <!-- Section 5: Signatures 4 Parties in 1 Row -->
            <div class="signatures-grid">
                <!-- 1. Requester -->
                <div class="sig-card">
                    <div class="sig-title">1. ผู้ยื่นคำขอข้อมูล</div>
                    <div>
                        <div class="sig-line"></div>
                        <div class="sig-name">({{ $dataRequest->user->name ?? '..................................................' }})</div>
                        <div class="sig-date">วันที่ {{ $dataRequest->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>

                <!-- 2. Department Head / Approver -->
                <div class="sig-card">
                    <div class="sig-title">2. หัวหน้ากลุ่มงาน / ผู้อนุมัติ</div>
                    <div>
                        <div class="sig-line"></div>
                        <div class="sig-name">(..................................................)</div>
                        <div class="sig-date">วันที่ ......./......./............</div>
                    </div>
                </div>

                <!-- 3. IT Officer / Data Analyst -->
                <div class="sig-card">
                    <div class="sig-title">3. เจ้าหน้าที่ IT ผู้จัดทำข้อมูล</div>
                    <div>
                        <div class="sig-line"></div>
                        <div class="sig-name">({{ $dataRequest->handler->name ?? '..................................................' }})</div>
                        <div class="sig-date">วันที่ {{ $dataRequest->completed_at ? $dataRequest->completed_at->format('d/m/Y') : '...../...../..........' }}</div>
                    </div>
                </div>

                <!-- 4. Receiver -->
                <div class="sig-card">
                    <div class="sig-title">4. ผู้รับมอบข้อมูล / ตรวจรับ</div>
                    <div>
                        <div class="sig-line"></div>
                        <div class="sig-name">(..................................................)</div>
                        <div class="sig-date">วันที่ ......./......./............</div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="doc-footer">
                <div>ระบบบริหารจัดการสารสนเทศ กลุ่มงานสุขภาพดิจิทัล รพ.ทุ่งหัวช้าง (เอกสารควบคุมภายใน)</div>
                <div>พิมพ์เมื่อ {{ now()->format('d/m/Y H:i') }} น. &bull; หน้า 1/1 (เต็มหน้า A4)</div>
            </div>
        </div>
    </div>

    <script>
        function adjustPrintScale(scale) {
            const sheet = document.getElementById('printableSheet');
            if (sheet) {
                if (scale === '1') {
                    sheet.style.transform = 'none';
                    sheet.style.transformOrigin = 'top center';
                } else {
                    sheet.style.transform = `scale(${scale})`;
                    sheet.style.transformOrigin = 'top center';
                }
            }
        }
    </script>
</body>
</html>
