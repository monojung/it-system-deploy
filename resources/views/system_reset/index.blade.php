@extends('layouts.app')

@section('title', 'ศูนย์จัดการข้อมูลและล้างระบบ')
@section('page_title', 'ศูนย์ควบคุมการจัดการข้อมูลและล้างระบบสารสนเทศ (Data Management & System Reset Center)')
@section('page_subtitle', 'กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง • จัดการระเบียนข้อมูล, ล้างข้อมูลเฉพาะโมดูล, ตรวจสอบความสมบูรณ์ และรีเซ็ตระบบ')

@section('topbar-actions')
    <a href="{{ route('backups.index') }}" class="topbar-btn" title="สร้างจุดสำรองฐานข้อมูลฉุกเฉิน">
        <i class="bi bi-cloud-arrow-down-fill"></i>
        <span>สำรองข้อมูลก่อนดำเนินการ</span>
    </a>
    <a href="{{ route('audit-logs.index') }}" class="topbar-btn" title="ดูประวัติกิจกรรมระบบ">
        <i class="bi bi-shield-check"></i>
        <span>Audit Logs</span>
    </a>
@endsection

@push('styles')
    <style>
        .reset-nav-wrapper {
            background: #ffffff;
            border-radius: 14px;
            padding: 6px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            margin-bottom: 24px;
            overflow-x: auto;
        }

        .reset-nav-pills {
            display: flex;
            gap: 6px;
            list-style: none;
            margin: 0;
            padding: 0;
            min-width: max-content;
        }

        .reset-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 600;
            color: #64748b;
            background: transparent;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            white-space: nowrap;
        }

        .reset-tab-btn:hover {
            color: var(--primary);
            background: rgba(13, 148, 136, 0.08);
        }

        .reset-tab-btn.active {
            color: #ffffff !important;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .reset-tab-btn.tab-danger {
            color: #dc2626;
        }

        .reset-tab-btn.tab-danger:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .reset-tab-btn.tab-danger.active {
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .tab-panel {
            display: none;
            animation: fadeInTab 0.25s ease-out;
        }

        .tab-panel.active {
            display: block;
        }

        @keyframes fadeInTab {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .module-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 22px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
        }

        .module-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .module-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .badge-active {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .badge-amber {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .badge-muted {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        .stat-badge-chip {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 10px;
            font-size: 12px;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 6px;
        }

        .btn-action-clean {
            padding: 8px 14px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid #fed7aa;
            background: #fff7ed;
            color: #c2410c;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            width: 100%;
            justify-content: center;
            margin-top: 14px;
        }

        .btn-action-clean:hover {
            background: #ffedd5;
            color: #9a3412;
            border-color: #fdba74;
        }

        .btn-action-danger {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .btn-action-danger:hover {
            background: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
        }

        .copy-pill {
            cursor: pointer;
            user-select: all;
            font-family: monospace;
            background: #fee2e2;
            color: #dc2626;
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px dashed #f87171;
            transition: background 0.2s;
        }

        .copy-pill:hover {
            background: #fecaca;
        }
    </style>
@endpush

@section('content')
    <div class="content-container" style="max-width: 1200px; margin: 0 auto; padding-bottom: 50px;">

        <!-- Top Banner Header -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f766e 100%); border-radius: 18px; padding: 28px 32px; color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);">
            <div style="display: flex; align-items: center; gap: 18px;">
                <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(239, 68, 68, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 28px; color: #f87171; border: 1.5px solid rgba(248, 113, 113, 0.4);">
                    <i class="bi bi-radioactive"></i>
                </div>
                <div>
                    <h1 style="font-size: 21px; font-weight: 700; margin: 0; color: white; letter-spacing: -0.3px;">
                        ศูนย์ควบคุมการจัดการข้อมูลและล้างระบบสารสนเทศ
                    </h1>
                    <div style="font-size: 13px; color: #94a3b8; margin-top: 4px;">
                        {{ setting('hospital_name_th', 'โรงพยาบาลทุ่งหัวช้าง') }} &bull; โครงสร้างฐานข้อมูลเชื่อมโยงทุก 12 โมดูลระบบงาน
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" onclick="refreshLiveStats()" class="btn btn-sm" style="background: rgba(255, 255, 255, 0.12); color: white; border: 1px solid rgba(255, 255, 255, 0.2); font-size: 12.5px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;">
                    <i class="bi bi-arrow-repeat" id="refreshIcon"></i> รีเฟรชสถิติ
                </button>
                <a href="{{ route('backups.index') }}" class="btn btn-sm" style="background: #0d9488; color: white; border: 1px solid #14b8a6; font-size: 12.5px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px;">
                    <i class="bi bi-cloud-arrow-down-fill"></i> จุดสำรองข้อมูลฉุกเฉิน
                </a>
            </div>
        </div>

        <!-- KPI Quick Glance Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #ef4444; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">ธุรกรรมและงานปฏิบัติการ</span>
                    <i class="bi bi-activity text-danger" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                    {{ number_format(($stats['repairs']['count'] ?? 0) + ($stats['data_requests']['count'] ?? 0) + ($stats['asset_borrows']['count'] ?? 0) + ($stats['asset_transfers']['count'] ?? 0) + ($stats['hardware_audits']['count'] ?? 0)) }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    ใบแจ้งซ่อม, ขอข้อมูล, ยืม-คืน, ย้าย, ตรวจสเปค
                </div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #10b981; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">ครุภัณฑ์ & อะไหล่ในคลัง</span>
                    <i class="bi bi-laptop text-success" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #10b981; margin-top: 4px;">
                    {{ number_format(($stats['assets']['count'] ?? 0) + ($stats['spare_parts']['count'] ?? 0)) }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    {{ $stats['assets']['count'] ?? 0 }} เครื่อง &bull; {{ $stats['spare_parts']['count'] ?? 0 }} รายการอะไหล่
                </div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #3b82f6; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">ความปลอดภัย & Audit Logs</span>
                    <i class="bi bi-shield-check text-primary" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #3b82f6; margin-top: 4px;">
                    {{ number_format($stats['audit_logs']['count'] ?? 0) }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    วันนี้ {{ $stats['audit_logs']['today'] ?? 0 }} รายการ &bull; สำคัญ {{ $stats['audit_logs']['critical'] ?? 0 }}
                </div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #8b5cf6; background: #ffffff;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">ความสมบูรณ์ฐานข้อมูล (Health)</span>
                    <i class="bi bi-heart-pulse-fill text-purple" style="font-size: 18px; color: #8b5cf6;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: {{ count($integrity) === 0 ? '#10b981' : '#f59e0b' }}; margin-top: 4px;">
                    {{ count($integrity) === 0 ? '100% ปกติ' : count($integrity) . ' รายการผิดปกติ' }}
                </div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                    {{ count($integrity) === 0 ? 'Foreign Keys เชื่อมโยงสมบูรณ์' : 'พบรายการอ้างอิงกำพร้า' }}
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="reset-nav-wrapper">
            <div class="reset-nav-pills">
                <button type="button" class="reset-tab-btn active" onclick="switchResetTab('matrix', this)" id="btn_tab_matrix">
                    <i class="bi bi-grid-3x3-gap-fill"></i> 1. ภาพรวมข้อมูลทุกโมดูล (Live Module Matrix)
                </button>
                <button type="button" class="reset-tab-btn" onclick="switchResetTab('modular', this)" id="btn_tab_modular">
                    <i class="bi bi-ui-checks"></i> 2. จัดการและล้างข้อมูลเฉพาะโมดูล (Modular Clean)
                </button>
                <button type="button" class="reset-tab-btn tab-danger" onclick="switchResetTab('tiered', this)" id="btn_tab_tiered">
                    <i class="bi bi-exclamation-octagon-fill"></i> 3. ล้างระบบระดับโครงสร้าง (System Resets)
                </button>
                <button type="button" class="reset-tab-btn" onclick="switchResetTab('integrity', this)" id="btn_tab_integrity">
                    <i class="bi bi-wrench-adjustable"></i> 4. ตรวจสอบและซ่อมแซมความสมบูรณ์ (Integrity & Repair)
                </button>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- Tab 1: Live Module Matrix                                           -->
        <!-- =================================================================== -->
        <div id="panel-matrix" class="tab-panel active">
            <div class="module-grid">
                <!-- 1. Repairs -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-tools"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">งานแจ้งซ่อมบำรุง IT</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_repairs, it_repair_logs, it_repair_parts</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['repairs']['count'] }} งาน</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            ใบแจ้งซ่อมคอมพิวเตอร์, ประวัติช่างซ่อมบำรุง, และบันทึกการเบิกอะไหล่ในแต่ละงานซ่อม
                        </div>
                        <div class="stat-badge-chip">
                            <span>รอดำเนินการ:</span>
                            <strong style="color: #ef4444;">{{ $stats['repairs']['pending'] }} งาน</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>กำลังดำเนินการ / รออะไหล่:</span>
                            <strong style="color: #f59e0b;">{{ $stats['repairs']['in_progress'] }} งาน</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>เสร็จสิ้นแล้ว:</span>
                            <strong style="color: #10b981;">{{ $stats['repairs']['completed'] }} งาน</strong>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('repairs', 'งานแจ้งซ่อมบำรุง IT', '{{ $stats['repairs']['count'] }} งาน', 'CLEAR-REPAIRS')">
                        <i class="bi bi-trash3"></i> ล้างข้อมูลงานแจ้งซ่อมเฉพาะส่วนนี้
                    </button>
                </div>

                <!-- 2. Data Requests -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-file-earmark-bar-graph"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">คำขอข้อมูล HosXP & สถิติ</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_data_requests</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['data_requests']['count'] }} งาน</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            คำขอข้อมูลสารสนเทศทางการแพทย์ HosXP, ข้อมูลสถิติ, ไฟล์ผลลัพธ์ Excel/CSV และตัวอย่าง
                        </div>
                        <div class="stat-badge-chip">
                            <span>รอพิจารณา / รอรับเรื่อง:</span>
                            <strong style="color: #ef4444;">{{ $stats['data_requests']['pending'] }} งาน</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>ส่งมอบข้อมูลเสร็จสิ้น:</span>
                            <strong style="color: #10b981;">{{ $stats['data_requests']['completed'] }} งาน</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>อัปเดตล่าสุด:</span>
                            <span>{{ $stats['data_requests']['last_activity'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('data_requests', 'คำขอข้อมูลสารสนเทศ HosXP', '{{ $stats['data_requests']['count'] }} งาน', 'CLEAR-REQUESTS')">
                        <i class="bi bi-trash3"></i> ล้างคำขอข้อมูลเฉพาะส่วนนี้
                    </button>
                </div>

                <!-- 3. Asset Borrows -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #ccfbf1; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-arrow-left-right"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">ขอยืม-คืนอุปกรณ์ IT</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_asset_borrows</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['asset_borrows']['count'] }} รายการ</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            บันทึกการขอยืมโน้ตบุ๊ก, โปรเจกเตอร์, คอมพิวเตอร์สำรองระหว่างซ่อม และการคืนของ
                        </div>
                        <div class="stat-badge-chip">
                            <span>รออนุมัติขอยืม:</span>
                            <strong style="color: #ef4444;">{{ $stats['asset_borrows']['pending'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>กำลังถูกยืมใช้งาน:</span>
                            <strong style="color: #0284c7;">{{ $stats['asset_borrows']['borrowed'] }} เครื่อง</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>ส่งคืนเรียบร้อยแล้ว:</span>
                            <strong style="color: #10b981;">{{ $stats['asset_borrows']['returned'] }} รายการ</strong>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('asset_borrows', 'ประวัติการขอยืม-คืนอุปกรณ์ IT', '{{ $stats['asset_borrows']['count'] }} รายการ', 'CLEAR-BORROWS')">
                        <i class="bi bi-trash3"></i> ล้างประวัติการยืม-คืนเฉพาะส่วนนี้
                    </button>
                </div>

                <!-- 4. Asset Transfers -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-arrows-move"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">โอนย้ายครุภัณฑ์ & จุดติดตั้ง</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_asset_transfers</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['asset_transfers']['count'] }} รายการ</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            ประวัติการย้ายจุดติดตั้งเครื่องคอมพิวเตอร์ระหว่างตึก แผนก ห้องปฏิบัติการ พร้อมเอกสารส่งมอบ
                        </div>
                        <div class="stat-badge-chip">
                            <span>รอดำเนินการย้าย:</span>
                            <strong style="color: #ef4444;">{{ $stats['asset_transfers']['pending'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>โอนย้ายเสร็จสมบูรณ์:</span>
                            <strong style="color: #10b981;">{{ $stats['asset_transfers']['completed'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>อัปเดตล่าสุด:</span>
                            <span>{{ $stats['asset_transfers']['last_activity'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('asset_transfers', 'ประวัติการโอนย้ายครุภัณฑ์', '{{ $stats['asset_transfers']['count'] }} รายการ', 'CLEAR-TRANSFERS')">
                        <i class="bi bi-trash3"></i> ล้างประวัติการโอนย้ายเฉพาะส่วนนี้
                    </button>
                </div>

                <!-- 5. Hardware Audits & Agent Commands -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-cpu-fill"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">ตรวจนับสเปค & IT Agent</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_hardware_audits, it_agent_commands</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['hardware_audits']['count'] }} เครื่อง</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            ข้อมูลสเปคฮาร์ดแวร์จริงที่ดึงผ่าน IT Agent ประจำปีงบประมาณ และคิวคำสั่งสแกนระยะไกล
                        </div>
                        <div class="stat-badge-chip">
                            <span>ออนไลน์เรียลไทม์:</span>
                            <strong style="color: #10b981;">{{ $stats['hardware_audits']['online'] }} เครื่อง</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>รอตรวจสอบ / อนุมัติสเปค:</span>
                            <strong style="color: #f59e0b;">{{ $stats['hardware_audits']['pending'] }} เครื่อง</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>คำสั่ง Agent ในประวัติ:</span>
                            <span>{{ $stats['hardware_audits']['commands_count'] }} คำสั่ง</span>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('hardware_audits', 'ข้อมูลตรวจนับสเปคและคำสั่ง Agent', '{{ $stats['hardware_audits']['count'] }} เครื่อง', 'CLEAR-AUDITS')">
                        <i class="bi bi-trash3"></i> ล้างสเปคตรวจนับและคำสั่ง Agent
                    </button>
                </div>

                <!-- 6. Assets & Inventory -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-laptop"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">คลังคอมพิวเตอร์ & ครุภัณฑ์ IT</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_assets</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['assets']['count'] }} เครื่อง</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            ทะเบียนคอมพิวเตอร์, รหัสครุภัณฑ์, Serial Number, สเปกเครื่อง, แผนกที่ประจำ, สติกเกอร์ QR
                        </div>
                        <div class="stat-badge-chip">
                            <span>ใช้งานปกติ:</span>
                            <strong style="color: #10b981;">{{ $stats['assets']['active'] }} เครื่อง</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>อยู่ระหว่างส่งซ่อม:</span>
                            <strong style="color: #f59e0b;">{{ $stats['assets']['in_repair'] }} เครื่อง</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>ชำรุด / รอจำหน่าย:</span>
                            <strong style="color: #ef4444;">{{ $stats['assets']['damaged'] }} เครื่อง</strong>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean btn-action-danger" onclick="openModuleCleanModal('assets_reset', 'รีเซ็ตทะเบียนคลังคอมพิวเตอร์เป็นค่าเริ่มต้น', '{{ $stats['assets']['count'] }} เครื่อง', 'RESET-ASSETS')">
                        <i class="bi bi-arrow-counterclockwise"></i> รีเซ็ตทะเบียนครุภัณฑ์เป็นแม่แบบตั้งต้น
                    </button>
                </div>

                <!-- 7. Spare Parts -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #fef9c3; color: #854d0e; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">คลังอะไหล่ & พัสดุสิ้นเปลือง</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_spare_parts</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['spare_parts']['count'] }} รายการ</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            สต็อกอะไหล่สำรองซ่อม RAM, SSD, Power Supply, สายแลน, เม้าส์, คีย์บอร์ด, หมึกพิมพ์
                        </div>
                        <div class="stat-badge-chip">
                            <span>สินค้าใกล้หมด (ต่ำกว่าเกณฑ์):</span>
                            <strong style="color: #f59e0b;">{{ $stats['spare_parts']['low_stock'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>สินค้าหมดสต็อก:</span>
                            <strong style="color: #ef4444;">{{ $stats['spare_parts']['out_of_stock'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>อัปเดตล่าสุด:</span>
                            <span>{{ $stats['spare_parts']['last_activity'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean btn-action-danger" onclick="openModuleCleanModal('spare_parts_reset', 'รีเซ็ตคลังพัสดุและอะไหล่เป็นค่าเริ่มต้น', '{{ $stats['spare_parts']['count'] }} รายการ', 'RESET-PARTS')">
                        <i class="bi bi-arrow-counterclockwise"></i> รีเซ็ตแคตตาล็อกอะไหล่เป็นแม่แบบ
                    </button>
                </div>

                <!-- 8. Audit Logs -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">บันทึกกิจกรรมระบบ (Audit Log)</strong>
                                    <div style="font-size: 11px; color: #64748b;">it_audit_logs</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['audit_logs']['count'] }} บันทึก</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            บันทึกหลักฐานความมั่นคงปลอดภัยไซเบอร์ ประวัติการเข้าสู่ระบบ, การแก้ไขข้อมูล, IP Address
                        </div>
                        <div class="stat-badge-chip">
                            <span>บันทึกวันนี้:</span>
                            <strong style="color: #0284c7;">{{ $stats['audit_logs']['today'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>เหตุการณ์สำคัญ (Critical):</span>
                            <strong style="color: #ef4444;">{{ $stats['audit_logs']['critical'] }} รายการ</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>บันทึกล่าสุด:</span>
                            <span>{{ $stats['audit_logs']['last_activity'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('audit_logs', 'บันทึกความปลอดภัย Audit Logs', '{{ $stats['audit_logs']['count'] }} รายการ', 'CLEAR-LOGS')">
                        <i class="bi bi-trash3"></i> ล้างบันทึก Audit Logs เก่า / ทั้งหมด
                    </button>
                </div>

                <!-- 9. Storage Files -->
                <div class="module-card">
                    <div>
                        <div class="module-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi bi-hdd-stack-fill"></i>
                                </div>
                                <div>
                                    <strong style="font-size: 14.5px; color: #0f172a;">พื้นที่จัดเก็บ Storage & แคช</strong>
                                    <div style="font-size: 11px; color: #64748b;">storage/app, uploads, cache</div>
                                </div>
                            </div>
                            <span class="module-badge badge-active">{{ $stats['storage']['total_size'] }}</span>
                        </div>
                        <div style="font-size: 12px; color: #475569; line-height: 1.5; margin-bottom: 12px;">
                            ไฟล์แนบรูปภาพงานซ่อม, ไฟล์ส่งมอบข้อมูล, ไฟล์สำรองฐานข้อมูล และไฟล์ชั่วคราวในแคช
                        </div>
                        <div class="stat-badge-chip">
                            <span>ไฟล์อัปโหลดและเอกสาร:</span>
                            <strong>{{ $stats['storage']['uploads_size'] }}</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>ไฟล์สำรองฐานข้อมูล SQL:</span>
                            <strong>{{ $stats['storage']['backups_size'] }}</strong>
                        </div>
                        <div class="stat-badge-chip">
                            <span>สำรองข้อมูลล่าสุด:</span>
                            <span>{{ $stats['backups']['last_backup'] }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-action-clean" onclick="openModuleCleanModal('storage_temp', 'ไฟล์ชั่วคราวและไฟล์ขยะในระบบ Storage', '{{ $stats['storage']['total_size'] }}', 'CLEAR-TEMP')">
                        <i class="bi bi-broom"></i> ล้างไฟล์ชั่วคราวและขยะ Storage
                    </button>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- Tab 2: Modular Clean Center                                         -->
        <!-- =================================================================== -->
        <div id="panel-modular" class="tab-panel">
            <div class="card" style="border-radius: 14px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 24px;">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 18px 24px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-ui-checks text-primary" style="font-size: 20px;"></i>
                        <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                            เลือกโมดูลที่ต้องการล้างข้อมูล (Targeted Selective Clean)
                        </h3>
                    </div>
                    <span style="font-size: 12px; color: #64748b;">
                        ปลอดภัยสูงสุด: โมดูลอื่นจะไม่ได้รับผลกระทบ และมีระบบ Auto-Backup ก่อนล้างเสมอ
                    </span>
                </div>
                <div class="card-body" style="padding: 24px;">
                    <p style="font-size: 13.5px; color: #475569; margin-bottom: 20px;">
                        ท่านสามารถเลือกทำความสะอาดข้อมูลเฉพาะระบบงานที่ต้องการรีเซ็ตหรือล้างข้อมูลทดสอบ (Test Data)
                        โดยระบบจะตัดความสัมพันธ์อย่างปลอดภัย ไม่ทำให้เกิดข้อผิดพลาด Foreign Key:
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                        <!-- Rep 1 -->
                        <div style="border: 1px solid #fed7aa; border-radius: 12px; padding: 18px; background: #fffaf5;">
                            <div style="font-weight: 700; color: #9a3412; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-tools text-danger me-1"></i> ล้างงานแจ้งซ่อมบำรุง
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 14px; min-height: 48px;">
                                ลบใบแจ้งซ่อมทั้งหมด ({{ $stats['repairs']['count'] }} งาน), Timeline ช่างซ่อม, รายการอะไหล่ที่เบิก และรูปภาพความเสียหาย
                            </div>
                            <button type="button" class="btn btn-warning btn-sm w-100" onclick="openModuleCleanModal('repairs', 'งานแจ้งซ่อมบำรุง IT', '{{ $stats['repairs']['count'] }} งาน', 'CLEAR-REPAIRS')">
                                <i class="bi bi-trash3-fill"></i> ล้างข้อมูลงานซ่อม ({{ $stats['repairs']['count'] }})
                            </button>
                        </div>

                        <!-- Rep 2 -->
                        <div style="border: 1px solid #bae6fd; border-radius: 12px; padding: 18px; background: #f0f9ff;">
                            <div style="font-weight: 700; color: #0369a1; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-file-earmark-bar-graph text-info me-1"></i> ล้างคำขอข้อมูล HosXP
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 14px; min-height: 48px;">
                                ลบคำขอบริการข้อมูลสารสนเทศทางการแพทย์ ({{ $stats['data_requests']['count'] }} รายการ) พร้อมผลลัพธ์ Excel/CSV ที่สร้างไว้
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" onclick="openModuleCleanModal('data_requests', 'คำขอข้อมูลสารสนเทศ HosXP', '{{ $stats['data_requests']['count'] }} งาน', 'CLEAR-REQUESTS')">
                                <i class="bi bi-trash3-fill"></i> ล้างคำขอข้อมูล ({{ $stats['data_requests']['count'] }})
                            </button>
                        </div>

                        <!-- Rep 3 -->
                        <div style="border: 1px solid #99f6e4; border-radius: 12px; padding: 18px; background: #f0fdfa;">
                            <div style="font-weight: 700; color: #0f766e; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-arrow-left-right text-success me-1"></i> ล้างประวัติการยืม-คืนอุปกรณ์
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 14px; min-height: 48px;">
                                ลบประวัติขอยืมอุปกรณ์ ({{ $stats['asset_borrows']['count'] }} รายการ) และปรับคืนสถานะครุภัณฑ์ทั้งหมดเป็น 'พร้อมใช้งาน'
                            </div>
                            <button type="button" class="btn btn-sm w-100" style="background: #0d9488; color: white;" onclick="openModuleCleanModal('asset_borrows', 'ประวัติการขอยืม-คืนอุปกรณ์ IT', '{{ $stats['asset_borrows']['count'] }} รายการ', 'CLEAR-BORROWS')">
                                <i class="bi bi-trash3-fill"></i> ล้างการยืม-คืน ({{ $stats['asset_borrows']['count'] }})
                            </button>
                        </div>

                        <!-- Rep 4 -->
                        <div style="border: 1px solid #ddd6fe; border-radius: 12px; padding: 18px; background: #faf5ff;">
                            <div style="font-weight: 700; color: #6b21a8; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-arrows-move me-1" style="color: #7c3aed;"></i> ล้างประวัติการโอนย้ายครุภัณฑ์
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 14px; min-height: 48px;">
                                ลบประวัติการขอเปลี่ยนสถานที่ติดตั้งครุภัณฑ์ ({{ $stats['asset_transfers']['count'] }} รายการ) พร้อมเอกสารแนบ
                            </div>
                            <button type="button" class="btn btn-sm w-100" style="background: #7c3aed; color: white;" onclick="openModuleCleanModal('asset_transfers', 'ประวัติการโอนย้ายครุภัณฑ์', '{{ $stats['asset_transfers']['count'] }} รายการ', 'CLEAR-TRANSFERS')">
                                <i class="bi bi-trash3-fill"></i> ล้างการโอนย้าย ({{ $stats['asset_transfers']['count'] }})
                            </button>
                        </div>

                        <!-- Rep 5 -->
                        <div style="border: 1px solid #fde68a; border-radius: 12px; padding: 18px; background: #fffbeb;">
                            <div style="font-weight: 700; color: #92400e; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-cpu-fill text-warning me-1"></i> ล้างข้อมูลตรวจสเปค & Agent
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 14px; min-height: 48px;">
                                ลบสเปคคอมฯ ที่ได้รับจาก Agent ({{ $stats['hardware_audits']['count'] }} เครื่อง) และคำสั่งสแกนค้าง เพื่อเริ่มรอบสำรวจใหม่
                            </div>
                            <button type="button" class="btn btn-sm w-100" style="background: #d97706; color: white;" onclick="openModuleCleanModal('hardware_audits', 'ข้อมูลตรวจนับสเปคและคำสั่ง Agent', '{{ $stats['hardware_audits']['count'] }} เครื่อง', 'CLEAR-AUDITS')">
                                <i class="bi bi-trash3-fill"></i> ล้างสเปคสำรวจ ({{ $stats['hardware_audits']['count'] }})
                            </button>
                        </div>

                        <!-- Rep 6 -->
                        <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #f8fafc;">
                            <div style="font-weight: 700; color: #334155; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-shield-check text-primary me-1"></i> ล้างบันทึก Audit Logs
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-bottom: 14px; min-height: 48px;">
                                เลือกล้างบันทึกกิจกรรมความปลอดภัยทั้งหมด ({{ $stats['audit_logs']['count'] }} รายการ) หรือลบเฉพาะที่เก่ากว่ากำหนด
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm w-100" onclick="openModuleCleanModal('audit_logs', 'บันทึกความปลอดภัย Audit Logs', '{{ $stats['audit_logs']['count'] }} รายการ', 'CLEAR-LOGS')">
                                <i class="bi bi-trash3-fill"></i> ล้างบันทึก Audit Logs
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- Tab 3: Tiered System Resets (Operational & Factory Reset)           -->
        <!-- =================================================================== -->
        <div id="panel-tiered" class="tab-panel">
            <div class="card" style="border: 2px solid #fee2e2; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(220, 38, 38, 0.08); margin-bottom: 24px;">
                <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 22px 28px; color: white; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: white;">พื้นที่ควบคุมพิเศษ: ล้างระบบระดับโครงสร้าง (System-Wide Reset)</h3>
                            <div style="font-size: 13px; opacity: 0.92; margin-top: 2px;">เฉพาะผู้ดูแลระบบสารสนเทศ (Super Admin) เท่านั้น &bull; มีระบบสร้างจุดสำรองข้อมูลฉุกเฉินอัตโนมัติ</div>
                        </div>
                    </div>
                    <a href="{{ route('backups.index') }}" class="btn btn-sm" style="background: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px;">
                        <i class="bi bi-cloud-arrow-down-fill"></i> ไปหน้าสำรองฐานข้อมูล
                    </a>
                </div>

                <div class="card-body" style="padding: 28px;">
                    <div style="background: #fff5f5; border: 1px solid #fecaca; border-radius: 12px; padding: 18px 22px; margin-bottom: 28px; display: flex; align-items: flex-start; gap: 16px;">
                        <i class="bi bi-shield-slash-fill text-danger" style="font-size: 24px; flex-shrink: 0; margin-top: 2px;"></i>
                        <div style="font-size: 13.5px; color: #991b1b; line-height: 1.6;">
                            <strong>มาตรการความปลอดภัยขั้นสูงสุด (Fail-Safe Mechanism):</strong><br>
                            การล้างข้อมูลจะเป็นการลบระเบียนข้อมูลออกจากฐานข้อมูลจริงอย่างถาวร อย่างไรก็ตาม ระบบได้รับการออกแบบให้
                            <strong>"สร้างจุดสำรองฐานข้อมูลฉุกเฉินอัตโนมัติ (Pre-Wipe Snapshot)"</strong> ไว้ในระบบก่อนเริ่มดำเนินการทุกครั้ง
                            เพื่อให้สามารถกู้คืนข้อมูล (Restore) กลับมาได้ตลอดเวลาหากเกิดข้อผิดพลาด
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">

                        <!-- Action Card 1: Operational Data Wipe -->
                        <div style="border: 1.5px solid #fed7aa; border-radius: 14px; padding: 26px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
                            <div>
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                        <i class="bi bi-eraser-fill"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0; font-size: 16.5px; font-weight: 700; color: #0f172a;">แบบที่ 1: ล้างข้อมูลการดำเนินงานทั้งหมด</h4>
                                        <span style="font-size: 12px; color: #64748b;">(Full Operational Clean Slate)</span>
                                    </div>
                                </div>
                                <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 14px;">
                                    เหมาะสำหรับการล้างข้อมูลทดสอบ (Test Data) หรือการเริ่มรอบบันทึกข้อมูลปีงบประมาณใหม่
                                    โดยล้างข้อมูลธุรกรรมที่เกิดขึ้นทั้งหมดแต่ยังคงรักษาข้อมูลแม่แบบหลักไว้ครบถ้วน:
                                </p>
                                <ul style="font-size: 12.5px; color: #64748b; padding-left: 20px; line-height: 1.8; margin-bottom: 20px;">
                                    <li><strong style="color: #dc2626;">ลบ:</strong> ใบแจ้งซ่อมทั้งหมด ({{ $stats['repairs']['count'] }} งาน) พร้อม Timeline และอะไหล่ที่ใช้</li>
                                    <li><strong style="color: #dc2626;">ลบ:</strong> คำขอข้อมูลสารสนเทศ HosXP ทั้งหมด ({{ $stats['data_requests']['count'] }} งาน)</li>
                                    <li><strong style="color: #dc2626;">ลบ:</strong> ประวัติการขอยืม-คืนอุปกรณ์ ({{ $stats['asset_borrows']['count'] }} รายการ)</li>
                                    <li><strong style="color: #dc2626;">ลบ:</strong> ประวัติการโอนย้ายครุภัณฑ์ ({{ $stats['asset_transfers']['count'] }} รายการ)</li>
                                    <li><strong style="color: #dc2626;">ลบ:</strong> ข้อมูลสเปคคอมฯ ที่ได้รับจาก Agent ({{ $stats['hardware_audits']['count'] }} เครื่อง) และคำสั่งสแกน</li>
                                    <li><strong style="color: #dc2626;">ลบ:</strong> บันทึกประวัติกิจกรรม Audit Logs ({{ $stats['audit_logs']['count'] }} รายการ)</li>
                                    <li><strong style="color: #16a34a;">คงไว้สมบูรณ์ 100%:</strong> ทะเบียนครุภัณฑ์คอมพิวเตอร์ ({{ $stats['assets']['count'] }} เครื่อง), แคตตาล็อกอะไหล่ ({{ $stats['spare_parts']['count'] }} รายการ), บัญชีผู้ใช้งานทั้งหมด, แผนก, ประเภทอุปกรณ์, และการตั้งค่าระบบ</li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-warning" onclick="openWipeModal('operational')" style="width: 100%; padding: 13px 18px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 10px;">
                                <i class="bi bi-trash3-fill"></i> ล้างข้อมูลการดำเนินงานทั้งหมด (Operational Wipe)
                            </button>
                        </div>

                        <!-- Action Card 2: Full System Factory Reset -->
                        <div style="border: 1.5px solid #fca5a5; border-radius: 14px; padding: 26px; background: #fffaf0; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 10px rgba(220, 38, 38, 0.04);">
                            <div>
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                        <i class="bi bi-radioactive"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0; font-size: 16.5px; font-weight: 700; color: #991b1b;">แบบที่ 2: รีเซ็ตระบบทั้งหมดเป็นค่าเริ่มต้น</h4>
                                        <span style="font-size: 12px; color: #b91c1c;">(Full Factory Reset & Hospital Seeding)</span>
                                    </div>
                                </div>
                                <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 14px;">
                                    ล้างข้อมูลทุกตารางในระบบ และรีเซ็ตกลับเป็นค่าเริ่มต้นมาตรฐานโรงพยาบาลทุ่งหัวช้าง
                                    (สร้างแม่แบบแผนก, ประเภทอุปกรณ์, ตัวอย่างครุภัณฑ์คอมพิวเตอร์, และแคตตาล็อกอะไหล่ใหม่ทั้งหมด):
                                </p>
                                <ul style="font-size: 12.5px; color: #64748b; padding-left: 20px; line-height: 1.8; margin-bottom: 20px;">
                                    <li><strong style="color: #dc2626;">ลบ:</strong> ธุรกรรมการแจ้งซ่อม, คำขอข้อมูล, ยืม-คืน, โอนย้าย, ผลตรวจ Agent, Audit Logs ทั้งหมด</li>
                                    <li><strong style="color: #dc2626;">รีเซ็ต:</strong> ข้อมูลครุภัณฑ์และสต็อกอะไหล่กลับสู่แม่แบบโรงพยาบาลทุ่งหัวช้าง</li>
                                    <li><strong style="color: #dc2626;">รีเซ็ต:</strong> รายชื่อแผนก และประเภทอุปกรณ์ กลับสู่ค่าตั้งต้น</li>
                                    <li><strong style="color: #16a34a;">การป้องกันความปลอดภัย:</strong> <strong>บัญชีผู้ดูแลระบบของคุณ ({{ auth()->user()->name }}) จะไม่ถูกลบ</strong> และคงสิทธิ์ Super Admin เข้าใช้งานได้ตามเดิม</li>
                                </ul>
                            </div>
                            <button type="button" class="btn btn-danger" onclick="openWipeModal('full')" style="width: 100%; padding: 13px 18px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 10px;">
                                <i class="bi bi-arrow-counterclockwise"></i> ล้างข้อมูลและรีเซ็ตระบบทั้งหมด (Factory Reset)
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- Tab 4: Database Integrity & Orphan Cleaner                          -->
        <!-- =================================================================== -->
        <div id="panel-integrity" class="tab-panel">
            <div class="card" style="border-radius: 14px; border: 1px solid var(--border); overflow: hidden; margin-bottom: 24px;">
                <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-wrench-adjustable text-primary" style="font-size: 20px;"></i>
                        <div>
                            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">เครื่องมือตรวจสอบและซ่อมแซมความสมบูรณ์ของฐานข้อมูล (Database Integrity Scanner)</h3>
                            <div style="font-size: 12px; color: #64748b;">สแกนหารายการกำพร้า (Orphaned records) และการอ้างอิง Foreign Key ที่ขาดหายไป</div>
                        </div>
                    </div>
                    <form action="{{ route('system-reset.repair-integrity') }}" method="POST" id="repairIntegrityForm" onsubmit="return confirmRepair(event)">
                        @csrf
                        <input type="hidden" name="admin_password" id="repair_admin_password" value="">
                        <button type="submit" class="btn btn-primary" style="font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 8px;">
                            <i class="bi bi-magic"></i> สแกนและซ่อมแซมความสมบูรณ์อัตโนมัติ (Auto-Heal)
                        </button>
                    </form>
                </div>
                <div class="card-body" style="padding: 24px;">
                    @if(empty($integrity))
                        <div style="text-align: center; padding: 40px 20px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px;">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 48px;"></i>
                            <h4 style="margin: 12px 0 6px 0; color: #166534; font-size: 18px;">ฐานข้อมูลมีความสมบูรณ์ 100%</h4>
                            <p style="margin: 0; font-size: 13.5px; color: #15803d;">
                                ไม่พบรายการกำพร้า หรือความสัมพันธ์ Foreign Key ที่ผิดปกติในระบบ ทุกโมดูลเชื่อมโยงอย่างสมบูรณ์แบบ
                            </p>
                        </div>
                    @else
                        <div style="margin-bottom: 18px; font-size: 13.5px; color: #475569;">
                            พบรายการความสัมพันธ์ที่อาจมีปัญหา <strong>{{ count($integrity) }}</strong> จุด โปรดตรวจสอบและกดปุ่มซ่อมแซมเพื่อปรับค่าให้อัตโนมัติ:
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            @foreach($integrity as $issue)
                                <div style="border: 1px solid {{ $issue['severity'] === 'high' ? '#fca5a5' : '#fed7aa' }}; border-radius: 10px; padding: 16px 20px; background: {{ $issue['severity'] === 'high' ? '#fff5f5' : '#fffaf0' }}; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                            <span class="badge" style="background: {{ $issue['severity'] === 'high' ? '#dc2626' : '#d97706' }}; font-size: 11px;">
                                                {{ strtoupper($issue['severity']) }}
                                            </span>
                                            <strong style="color: #0f172a; font-size: 14.5px;">{{ $issue['title'] }}</strong>
                                            <span style="font-size: 12px; font-weight: 700; color: #dc2626;">({{ $issue['count'] }} รายการ)</span>
                                        </div>
                                        <div style="font-size: 12.5px; color: #64748b;">
                                            {{ $issue['description'] }}
                                        </div>
                                    </div>
                                    <div style="font-size: 12px; color: #0f766e; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 6px 12px; border-radius: 6px;">
                                        <strong>การแก้ไข:</strong> {{ $issue['fix_action'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- =================================================================== -->
    <!-- Modal 1: Selective Module Clean Modal                               -->
    <!-- =================================================================== -->
    <div id="moduleCleanModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 520px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.25); border: 1px solid var(--border);">
            <div style="background: #ea580c; color: white; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-trash3-fill" style="font-size: 20px;"></i>
                    <h3 id="clean_modal_title" style="margin: 0; font-size: 16px; font-weight: 700; color: white;">ยืนยันการล้างข้อมูลโมดูล</h3>
                </div>
                <button type="button" onclick="closeModuleCleanModal()" style="background: none; border: none; color: white; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form action="{{ route('system-reset.module') }}" method="POST" id="moduleCleanForm" style="padding: 24px;">
                @csrf
                <input type="hidden" name="module" id="clean_module_input" value="">

                <div id="clean_modal_desc" style="font-size: 13.5px; color: #475569; line-height: 1.6; margin-bottom: 18px;">
                </div>

                <!-- Auto Backup Checkbox -->
                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: #0369a1; background: #f0f9ff; padding: 12px 14px; border-radius: 8px; border: 1.5px solid #bae6fd; margin-bottom: 18px;">
                    <input type="checkbox" name="auto_backup" value="1" checked style="width: 18px; height: 18px; accent-color: #0d9488;">
                    <span>สร้างจุดสำรองฐานข้อมูลฉุกเฉินก่อนล้าง (Auto-Backup)</span>
                </label>

                <!-- Admin Password Verification -->
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label required" for="clean_admin_password" style="font-weight: 600; font-size: 13px;">
                        1. ป้อนรหัสผ่านของผู้ดูแลระบบ (Admin) เพื่อยืนยันสิทธิ์:
                    </label>
                    <input type="password" id="clean_admin_password" name="admin_password" class="form-control" placeholder="รหัสผ่านเข้าสู่ระบบของคุณ" required autocomplete="current-password">
                </div>

                <!-- Confirmation Phrase -->
                <div class="form-group" style="margin-bottom: 22px;">
                    <label class="form-label required" for="clean_confirmation_text" style="font-weight: 600; font-size: 13px;">
                        2. พิมพ์คำว่า <span class="copy-pill" id="clean_required_phrase" onclick="copyPhrase(this)" title="คลิกเพื่อคัดลอก"></span> ในช่องด้านล่าง:
                    </label>
                    <input type="text" id="clean_confirmation_text" name="confirmation_text" class="form-control" placeholder="" required autocomplete="off" style="font-weight: 700; letter-spacing: 0.5px;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-secondary" onclick="closeModuleCleanModal()" style="padding: 8px 18px; font-size: 13.5px;">
                        ยกเลิก
                    </button>
                    <button type="submit" id="btn_submit_module_clean" class="btn btn-danger" style="padding: 8px 22px; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-check2-circle"></i> ยืนยันดำเนินการล้างข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- Modal 2: Tiered System Wipe Modal (Operational / Full Factory Reset)-->
    <!-- =================================================================== -->
    <div id="wipeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 520px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.25); border: 1px solid var(--border);">
            <div id="modal_header_bar" style="background: #dc2626; color: white; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-exclamation-octagon-fill" style="font-size: 20px;"></i>
                    <h3 id="modal_title" style="margin: 0; font-size: 16px; font-weight: 700; color: white;">ยืนยันการล้างข้อมูลระบบ</h3>
                </div>
                <button type="button" onclick="closeWipeModal()" style="background: none; border: none; color: white; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form action="{{ route('settings.clear-data') }}" method="POST" id="wipeDataForm" style="padding: 24px;">
                @csrf
                <input type="hidden" name="wipe_scope" id="modal_wipe_scope" value="">

                <div id="modal_warning_text" style="font-size: 13.5px; color: #475569; line-height: 1.6; margin-bottom: 18px;">
                </div>

                <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: #0369a1; background: #f0f9ff; padding: 12px 14px; border-radius: 8px; border: 1.5px solid #bae6fd; margin-bottom: 18px;">
                    <input type="checkbox" name="auto_backup" value="1" checked style="width: 18px; height: 18px; accent-color: #0d9488;">
                    <span>สร้างจุดสำรองฐานข้อมูลฉุกเฉินก่อนล้าง (Auto-Backup)</span>
                </label>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label required" for="admin_password" style="font-weight: 600; font-size: 13px;">
                        1. ป้อนรหัสผ่านของผู้ดูแลระบบเพื่อยืนยันสิทธิ์:
                    </label>
                    <input type="password" id="admin_password" name="admin_password" class="form-control" placeholder="รหัสผ่านเข้าสู่ระบบของคุณ" required autocomplete="current-password">
                </div>

                <div class="form-group" style="margin-bottom: 22px;">
                    <label class="form-label required" for="confirmation_text" style="font-weight: 600; font-size: 13px;">
                        2. พิมพ์คำว่า <span class="copy-pill" id="required_phrase" onclick="copyPhrase(this)" title="คลิกเพื่อคัดลอก"></span> ในช่องด้านล่าง:
                    </label>
                    <input type="text" id="confirmation_text" name="confirmation_text" class="form-control" placeholder="" required autocomplete="off" style="font-weight: 700; letter-spacing: 0.5px;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border);">
                    <button type="button" class="btn btn-secondary" onclick="closeWipeModal()" style="padding: 8px 18px; font-size: 13.5px;">
                        ยกเลิก
                    </button>
                    <button type="submit" id="modal_submit_btn" class="btn btn-danger" style="padding: 8px 22px; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-check2-circle"></i> ยืนยันดำเนินการล้างข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function switchResetTab(tabId, element) {
            document.querySelectorAll('.reset-tab-btn').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(el => el.classList.remove('active'));

            if (element) {
                element.classList.add('active');
            } else {
                const btn = document.getElementById('btn_tab_' + tabId);
                if (btn) btn.classList.add('active');
            }

            const panel = document.getElementById('panel-' + tabId);
            if (panel) panel.classList.add('active');

            window.location.hash = tabId;
        }

        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                const targetPanel = document.getElementById('panel-' + hash);
                if (targetPanel) {
                    switchResetTab(hash, null);
                }
            }
        });

        function copyPhrase(element) {
            const text = element.innerText.trim();
            navigator.clipboard.writeText(text).then(() => {
                const input = element.closest('form').querySelector('input[name="confirmation_text"]');
                if (input) {
                    input.value = text;
                    input.focus();
                }
                const originalHtml = element.innerHTML;
                element.innerHTML = '<i class="bi bi-check"></i> คัดลอกแล้ว';
                setTimeout(() => {
                    element.innerHTML = originalHtml;
                }, 1500);
            });
        }

        // Modular Clean Modal Open
        function openModuleCleanModal(moduleKey, moduleTitle, countText, confirmPhrase) {
            document.getElementById('clean_module_input').value = moduleKey;
            document.getElementById('clean_modal_title').innerText = 'ยืนยันการล้าง: ' + moduleTitle;
            document.getElementById('clean_required_phrase').innerText = confirmPhrase;
            document.getElementById('clean_confirmation_text').value = '';
            document.getElementById('clean_confirmation_text').placeholder = 'พิมพ์ ' + confirmPhrase;
            document.getElementById('clean_admin_password').value = '';

            let desc = 'ท่านกำลังจะดำเนินการล้างข้อมูล <strong>' + moduleTitle + '</strong> (' + countText + ') ข้อมูลจะถูกลบออกจากฐานข้อมูลและไฟล์แนบที่เกี่ยวข้องจะถูกนำออก';
            document.getElementById('clean_modal_desc').innerHTML = desc;

            const modal = document.getElementById('moduleCleanModal');
            modal.style.display = 'flex';
        }

        function closeModuleCleanModal() {
            document.getElementById('moduleCleanModal').style.display = 'none';
        }

        // Tiered System Wipe Modal Open
        function openWipeModal(scope) {
            const modal = document.getElementById('wipeModal');
            const scopeInput = document.getElementById('modal_wipe_scope');
            const title = document.getElementById('modal_title');
            const headerBar = document.getElementById('modal_header_bar');
            const warningText = document.getElementById('modal_warning_text');
            const requiredPhrase = document.getElementById('required_phrase');
            const confirmationInput = document.getElementById('confirmation_text');
            const submitBtn = document.getElementById('modal_submit_btn');

            scopeInput.value = scope;
            document.getElementById('admin_password').value = '';
            confirmationInput.value = '';

            if (scope === 'operational') {
                title.innerText = 'ยืนยันการล้างข้อมูลการดำเนินงาน (Operational Wipe)';
                headerBar.style.background = '#d97706';
                submitBtn.className = 'btn btn-warning';
                submitBtn.innerHTML = '<i class="bi bi-trash3-fill"></i> ยืนยันล้างข้อมูลการดำเนินงาน';
                requiredPhrase.innerText = 'CLEAR-OPERATIONAL';
                confirmationInput.placeholder = 'พิมพ์ CLEAR-OPERATIONAL';
                warningText.innerHTML = `
                    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 12px; margin-bottom: 12px; color: #92400e;">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>คำเตือน:</strong> ระบบจะลบใบแจ้งซ่อม, คำขอข้อมูล HosXP, การยืม-คืนอุปกรณ์, การโอนย้าย, ผลตรวจ Agent, และ Audit Logs ทั้งหมด
                        โดยคงข้อมูลครุภัณฑ์, อะไหล่, และผู้ใช้ไว้ครบถ้วน
                    </div>
                `;
            } else if (scope === 'full') {
                title.innerText = 'ยืนยันการรีเซ็ตระบบทั้งหมด (Full Factory Reset)';
                headerBar.style.background = '#dc2626';
                submitBtn.className = 'btn btn-danger';
                submitBtn.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i> ยืนยันรีเซ็ตระบบทั้งหมด';
                requiredPhrase.innerText = 'RESET-ALL-DATA';
                confirmationInput.placeholder = 'พิมพ์ RESET-ALL-DATA';
                warningText.innerHTML = `
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin-bottom: 12px; color: #991b1b;">
                        <i class="bi bi-radioactive"></i> <strong>คำเตือนสูงสุด:</strong> ระบบจะล้างข้อมูลทุกอย่าง และติดตั้งค่าเริ่มต้นมาตรฐานโรงพยาบาลทุ่งหัวช้างใหม่
                        (บัญชี Super Admin ของคุณจะไม่ถูกลบ)
                    </div>
                `;
            }

            modal.style.display = 'flex';
        }

        function closeWipeModal() {
            document.getElementById('wipeModal').style.display = 'none';
        }

        // Close on backdrop click
        window.addEventListener('click', (e) => {
            const m1 = document.getElementById('moduleCleanModal');
            const m2 = document.getElementById('wipeModal');
            if (e.target === m1) closeModuleCleanModal();
            if (e.target === m2) closeWipeModal();
        });

        // Confirm Repair Integrity
        function confirmRepair(event) {
            event.preventDefault();

            Swal.fire({
                title: 'สแกนและซ่อมแซมความสมบูรณ์?',
                text: 'ระบบจะทำการปลดล็อกความสัมพันธ์ครุภัณฑ์ที่ถูกลบและล้างรายการประวัติกำพร้าให้อัตโนมัติ กรุณาระบุรหัสผ่าน Admin เพื่อยืนยัน:',
                input: 'password',
                inputPlaceholder: 'รหัสผ่านผู้ดูแลระบบ',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ยืนยันซ่อมแซม',
                cancelButtonText: 'ยกเลิก',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาระบุรหัสผ่านผู้ดูแลระบบ!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('repair_admin_password').value = result.value;
                    document.getElementById('repairIntegrityForm').submit();
                }
            });
            return false;
        }

        // Refresh stats via AJAX
        function refreshLiveStats() {
            const icon = document.getElementById('refreshIcon');
            if (icon) icon.classList.add('spin-animation');

            fetch("{{ route('system-reset.stats') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'อัปเดตสถิติเรียลไทม์แล้ว (' + data.timestamp + ')',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(() => {
                location.reload();
            })
            .finally(() => {
                if (icon) icon.classList.remove('spin-animation');
            });
        }
    </script>
    <style>
        .spin-animation {
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
@endpush
