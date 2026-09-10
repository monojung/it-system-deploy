@extends('layouts.app')

@section('title', 'การตั้งค่าระบบ')
@section('page_title', 'ศูนย์ควบคุมและการตั้งค่าระบบสารสนเทศ (System Configuration)')
@section('page_subtitle', 'กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง • จัดการข้อมูลหน่วยบริการ, SLA, การแจ้งเตือน และความปลอดภัย')

@section('topbar-actions')
<a href="{{ route('audit-logs.index') }}" class="topbar-btn" title="ดูบันทึกกิจกรรมระบบ">
    <i class="bi bi-shield-check"></i>
    <span>Audit Logs</span>
</a>
<a href="{{ route('backups.index') }}" class="topbar-btn" title="จัดการสำรองฐานข้อมูล">
    <i class="bi bi-cloud-arrow-down-fill"></i>
    <span>สำรองข้อมูล</span>
</a>
@endsection

@push('styles')
<style>
    /* Modern Settings Navigation Tabs */
    .settings-nav-wrapper {
        background: #ffffff;
        border-radius: 14px;
        padding: 6px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        margin-bottom: 24px;
        overflow-x: auto;
    }

    .settings-nav-pills {
        display: flex;
        gap: 6px;
        list-style: none;
        margin: 0;
        padding: 0;
        min-width: max-content;
    }

    .settings-tab-btn {
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

    .settings-tab-btn:hover {
        color: var(--primary);
        background: rgba(13, 148, 136, 0.08);
    }

    .settings-tab-btn.active {
        color: #ffffff !important;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%) !important;
        box-shadow: 0 4px 12px var(--primary-glow);
    }

    .settings-tab-btn.tab-danger {
        color: #dc2626;
    }

    .settings-tab-btn.tab-danger:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    .settings-tab-btn.tab-danger.active {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .tab-content-panel {
        display: none;
        animation: fadeInTab 0.25s ease-out;
    }

    .tab-content-panel.active {
        display: block;
    }

    @keyframes fadeInTab {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .settings-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .settings-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #ffffff;
    }

    .settings-card-body {
        padding: 26px 28px;
    }

    .form-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 8px;
        border-bottom: 1.5px dashed var(--border);
    }

    .info-sync-banner {
        background: linear-gradient(135deg, #f0fdfa 0%, #f0f9ff 100%);
        border: 1px solid #ccfbf1;
        border-radius: 10px;
        padding: 12px 18px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: #0f766e;
    }

    .copy-btn {
        background: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .copy-btn:hover {
        background: #0284c7;
        color: #ffffff;
    }

    .test-action-box {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-top: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
    }

    .status-pill-success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .status-pill-secondary {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .status-pill-info {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
</style>
@endpush

@section('content')
<div class="content-container" style="max-width: 1160px; margin: 0 auto; padding-bottom: 40px;">

    <!-- Hero Banner Header -->
    <div style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border-radius: 16px; padding: 26px 32px; color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(13, 148, 136, 0.15);">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 26px; color: white; border: 1px solid rgba(255, 255, 255, 0.25);">
                <i class="bi bi-sliders2-vertical"></i>
            </div>
            <div>
                <h1 style="font-size: 20px; font-weight: 700; margin: 0; letter-spacing: -0.2px; color: white;">
                    ศูนย์ควบคุมและการตั้งค่าระบบสารสนเทศ (System Configuration Hub)
                </h1>
                <div style="font-size: 13.5px; opacity: 0.92; margin-top: 4px; font-weight: 300;">
                    {{ $settings['hospital_name_th'] }} &bull; {{ $settings['department_name'] }} (รหัสหน่วยบริการ {{ $settings['hospital_code'] }})
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <span style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-shield-check"></i> สิทธิ์ผู้ดูแลระบบ (Admin)
            </span>
            <span style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-tag-fill"></i> v{{ $versionInfo['version'] }}
            </span>
        </div>
    </div>

    <!-- Segmented Navigation Pills -->
    <div class="settings-nav-wrapper">
        <div class="settings-nav-pills">
            <button type="button" class="settings-tab-btn active" onclick="switchTab('hospital', this)" id="tab_btn_hospital">
                <i class="bi bi-hospital"></i> ข้อมูลหน่วยบริการ & องค์กร
            </button>
            <button type="button" class="settings-tab-btn" onclick="switchTab('helpdesk', this)" id="tab_btn_helpdesk">
                <i class="bi bi-stopwatch"></i> มาตรฐาน SLA งานซ่อม
            </button>
            <button type="button" class="settings-tab-btn" onclick="switchTab('assets', this)" id="tab_btn_assets">
                <i class="bi bi-pc-display"></i> ครุภัณฑ์ & คลังพัสดุ
            </button>
            <button type="button" class="settings-tab-btn" onclick="switchTab('notification', this)" id="tab_btn_notification">
                <i class="bi bi-bell"></i> แจ้งเตือน LINE & อีเมล
            </button>
            <button type="button" class="settings-tab-btn" onclick="switchTab('security', this)" id="tab_btn_security">
                <i class="bi bi-shield-lock"></i> ความปลอดภัย & การยืนยันตัวตน
            </button>
            <button type="button" class="settings-tab-btn" onclick="switchTab('version', this)" id="tab_btn_version">
                <i class="bi bi-info-circle-fill"></i> ข้อมูลเวอร์ชัน & อัปเดต
            </button>
            <button type="button" class="settings-tab-btn tab-danger" onclick="switchTab('danger-zone', this)" id="tab_btn_danger_zone">
                <i class="bi bi-exclamation-triangle-fill"></i> จัดการข้อมูล & ล้างระบบ
            </button>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 1: Hospital & Organization Info                              -->
    <!-- ================================================================ -->
    <div id="tab-hospital" class="tab-content-panel active">
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-hospital"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">ข้อมูลหน่วยบริการและโครงสร้างกลุ่มงานสารสนเทศ</strong>
                        <div style="font-size: 12px; color: #64748b;">เชื่อมต่ออัตโนมัติกับ Sidebar, Footer, ใบสั่งซ่อม, ใบคำขอข้อมูล และสติกเกอร์ QR Code ครุภัณฑ์</div>
                    </div>
                </div>
                <span class="status-pill status-pill-success">
                    <i class="bi bi-check-circle-fill"></i> ข้อมูลพร้อมใช้งาน
                </span>
            </div>
            <div class="settings-card-body">
                <div class="info-sync-banner">
                    <i class="bi bi-info-circle-fill" style="font-size: 18px; flex-shrink: 0;"></i>
                    <div>
                        <strong>ระบบเชื่อมโยงอัตโนมัติ (Dynamic Sync):</strong> ข้อมูลที่บันทึกในหน้านี้จะนำไปแสดงเป็นหัวกระดาษของรายงานราชการ, ใบแจ้งซ่อม A4, ป้ายสติกเกอร์ครุภัณฑ์ และการลงนามโดยอัตโนมัติ
                    </div>
                </div>

                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="setting_group" value="hospital">

                    <!-- Hospital Identity -->
                    <div class="form-section-title">
                        <i class="bi bi-building text-primary"></i> 1. ข้อมูลอัตลักษณ์สถานพยาบาล
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="hospital_name_th">ชื่อโรงพยาบาล (ภาษาไทย)</label>
                            <div style="position: relative;">
                                <input type="text" id="hospital_name_th" name="hospital_name_th" class="form-control" value="{{ old('hospital_name_th', $settings['hospital_name_th']) }}" required style="padding-left: 36px;">
                                <i class="bi bi-hospital" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="hospital_name_en">ชื่อโรงพยาบาล (ภาษาอังกฤษ)</label>
                            <div style="position: relative;">
                                <input type="text" id="hospital_name_en" name="hospital_name_en" class="form-control" value="{{ old('hospital_name_en', $settings['hospital_name_en']) }}" style="padding-left: 36px;">
                                <i class="bi bi-globe" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="hospital_code">รหัสสถานพยาบาล 5 หลัก (Ministry of Public Health Code)</label>
                            <input type="text" id="hospital_code" name="hospital_code" class="form-control" value="{{ old('hospital_code', $settings['hospital_code']) }}" required placeholder="เช่น 11143" style="font-family: monospace; font-weight: 600;">
                            <span class="form-text">รหัสหน่วยบริการกระทรวงสาธารณสุข สำหรับเชื่อมต่อ HosXP และระบบเบิกจ่าย</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="hospital_phone">เบอร์โทรศัพท์กลาง / เบอร์ติดต่อภายในศูนย์คอมพิวเตอร์</label>
                            <div style="position: relative;">
                                <input type="text" id="hospital_phone" name="hospital_phone" class="form-control" value="{{ old('hospital_phone', $settings['hospital_phone']) }}" placeholder="เช่น 053-595055" style="padding-left: 36px;">
                                <i class="bi bi-telephone" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="hospital_address">ที่อยู่และที่ตั้งโรงพยาบาล</label>
                        <input type="text" id="hospital_address" name="hospital_address" class="form-control" value="{{ old('hospital_address', $settings['hospital_address']) }}" placeholder="เลขที่ หมู่ ตำบล อำเภอ จังหวัด รหัสไปรษณีย์">
                    </div>

                    <!-- Leadership & IT Organization -->
                    <div class="form-section-title" style="margin-top: 30px;">
                        <i class="bi bi-people-fill text-primary"></i> 2. บุคลากรผู้บริหารและกลุ่มงานสารสนเทศ
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="department_name">ชื่อกลุ่มงาน / ฝ่ายไอที</label>
                            <input type="text" id="department_name" name="department_name" class="form-control" value="{{ old('department_name', $settings['department_name']) }}" required placeholder="เช่น กลุ่มงานสุขภาพดิจิทัล">
                            <span class="form-text">แสดงที่มุมซ้ายบนของเมนู Sidebar และหัวใบสั่งซ่อม</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="it_head_name">ชื่อ-สกุล หัวหน้ากลุ่มงานสารสนเทศ</label>
                            <input type="text" id="it_head_name" name="it_head_name" class="form-control" value="{{ old('it_head_name', $settings['it_head_name']) }}" placeholder="เช่น นายช่าง IT ประจำการ">
                            <span class="form-text">ใช้แสดงในช่องลงนามใบคำขอข้อมูลและใบสั่งซ่อม</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="director_name">ชื่อ-สกุล ผู้อำนวยการโรงพยาบาล</label>
                        <input type="text" id="director_name" name="director_name" class="form-control" value="{{ old('director_name', $settings['director_name']) }}" placeholder="เช่น นายแพทย์... ผู้อำนวยการโรงพยาบาลทุ่งหัวช้าง">
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                            <i class="bi bi-floppy-fill"></i> บันทึกข้อมูลหน่วยบริการ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 2: Helpdesk & SLA Settings                                   -->
    <!-- ================================================================ -->
    <div id="tab-helpdesk" class="tab-content-panel">
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-stopwatch"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">กำหนดเวลามาตรฐานในการให้บริการซ่อม (Service Level Agreement - SLA)</strong>
                        <div style="font-size: 12px; color: #64748b;">คำนวณวันเวลากำหนดเสร็จสิ้นอัตโนมัติบนใบแจ้งซ่อมและรายงานประสิทธิภาพช่าง</div>
                    </div>
                </div>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="setting_group" value="helpdesk">

                    <div class="form-section-title">
                        <i class="bi bi-clock-history text-primary"></i> 1. กรอบเวลาเป้าหมาย SLA แยกตามระดับความเร่งด่วน (ชั่วโมง)
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 24px;">
                        <!-- Normal -->
                        <div style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 18px; background: #ffffff;">
                            <div style="font-weight: 700; font-size: 14px; color: #475569; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                <i class="bi bi-check-circle" style="color: #64748b;"></i> ปกติ (Low)
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-bottom: 12px;">ไม่กระทบงานหลัก รอตามรอบคิว</div>
                            <label class="form-label required" for="sla_low" style="font-size: 12px;">ระยะเวลาแก้ไขสูงสุด (ชั่วโมง)</label>
                            <input type="number" id="sla_low" name="sla_low" class="form-control" value="{{ old('sla_low', $settings['sla_low']) }}" min="1" required style="font-weight: 700; font-size: 16px;">
                            <span class="form-text">ค่าแนะนำ: 48 ชม. (2 วันทำการ)</span>
                        </div>

                        <!-- Medium -->
                        <div style="border: 2px solid #7dd3fc; border-radius: 12px; padding: 18px; background: #f0f9ff;">
                            <div style="font-weight: 700; font-size: 14px; color: #0284c7; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                <i class="bi bi-info-circle-fill"></i> ปานกลาง (Normal)
                            </div>
                            <div style="font-size: 11.5px; color: #0369a1; margin-bottom: 12px;">รอได้ 1-2 วัน ไม่กระทบงานบริการ</div>
                            <label class="form-label required" for="sla_normal" style="font-size: 12px;">ระยะเวลาแก้ไขสูงสุด (ชั่วโมง)</label>
                            <input type="number" id="sla_normal" name="sla_normal" class="form-control" value="{{ old('sla_normal', $settings['sla_normal']) }}" min="1" required style="font-weight: 700; font-size: 16px; color: #0284c7;">
                            <span class="form-text">ค่าแนะนำ: 24 ชม. (1 วันทำการ)</span>
                        </div>

                        <!-- High -->
                        <div style="border: 2px solid #fde68a; border-radius: 12px; padding: 18px; background: #fffbeb;">
                            <div style="font-weight: 700; font-size: 14px; color: #d97706; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                <i class="bi bi-exclamation-triangle-fill"></i> ด่วน (High)
                            </div>
                            <div style="font-size: 11.5px; color: #92400e; margin-bottom: 12px;">ส่งผลต่องานประจำวัน เข้าตรวจในวันนี้</div>
                            <label class="form-label required" for="sla_high" style="font-size: 12px;">ระยะเวลาแก้ไขสูงสุด (ชั่วโมง)</label>
                            <input type="number" id="sla_high" name="sla_high" class="form-control" value="{{ old('sla_high', $settings['sla_high']) }}" min="1" required style="font-weight: 700; font-size: 16px; color: #d97706;">
                            <span class="form-text">ค่าแนะนำ: 4 ชม.</span>
                        </div>

                        <!-- Critical -->
                        <div style="border: 2px solid #fca5a5; border-radius: 12px; padding: 18px; background: #fff1f2;">
                            <div style="font-weight: 700; font-size: 14px; color: #dc2626; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                                <i class="bi bi-lightning-fill"></i> ด่วนที่สุด (Critical)
                            </div>
                            <div style="font-size: 11.5px; color: #b91c1c; margin-bottom: 12px;">กระทบการรักษา/ผู้ป่วย ช่างต้องเข้าทันที</div>
                            <label class="form-label required" for="sla_critical" style="font-size: 12px;">ระยะเวลาแก้ไขสูงสุด (ชั่วโมง)</label>
                            <input type="number" id="sla_critical" name="sla_critical" class="form-control" value="{{ old('sla_critical', $settings['sla_critical']) }}" min="1" required style="font-weight: 700; font-size: 16px; color: #dc2626;">
                            <span class="form-text">ค่าแนะนำ: 1 ชม. (เข้าแก้ไขทันที)</span>
                        </div>
                    </div>

                    <!-- Ticket Format & Defaults -->
                    <div class="form-section-title">
                        <i class="bi bi-ticket-detailed text-primary"></i> 2. รูปแบบรหัสตั๋วใบแจ้งซ่อม & เกณฑ์อะไหล่เริ่มต้น
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="ticket_prefix">คำนำหน้ารหัสใบแจ้งซ่อม (Ticket Code Prefix)</label>
                            <input type="text" id="ticket_prefix" name="ticket_prefix" class="form-control" value="{{ old('ticket_prefix', $settings['ticket_prefix']) }}" required style="font-weight: 700; font-family: monospace; letter-spacing: 0.5px;">
                            <span class="form-text">ตัวอย่างรหัสที่ระบบสร้าง: <code>{{ $settings['ticket_prefix'] }}-{{ now()->format('Ym') }}-0001</code></span>
                        </div>

                        <div class="form-group">
                            <label class="form-label required" for="default_min_stock">เกณฑ์เตือนสต็อกอะไหล่ขั้นต่ำเริ่มต้น (Default Min Stock)</label>
                            <div class="input-group" style="display: flex;">
                                <input type="number" id="default_min_stock" name="default_min_stock" class="form-control" value="{{ old('default_min_stock', $settings['default_min_stock']) }}" min="1" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <span style="background: #f1f5f9; border: 1px solid var(--border); border-left: none; padding: 8px 16px; font-size: 13.5px; color: #475569; border-top-right-radius: 8px; border-bottom-right-radius: 8px; display: flex; align-items: center;">ชิ้น</span>
                            </div>
                            <span class="form-text">ใช้เป็นค่าเริ่มต้นอัตโนมัติเมื่อสร้างรายการอะไหล่และพัสดุชิ้นใหม่</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                            <i class="bi bi-floppy-fill"></i> บันทึกค่ามาตรฐาน SLA & ตั๋วงานซ่อม
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 3: Assets & Inventory Standards                              -->
    <!-- ================================================================ -->
    <div id="tab-assets" class="tab-content-panel">
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-pc-display"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">นโยบายการจัดซื้อ ทะเบียนครุภัณฑ์ และสต็อกพัสดุไอที</strong>
                        <div style="font-size: 12px; color: #64748b;">สอดคล้องกับเกณฑ์ราคากลางและคุณลักษณะพื้นฐาน ICT กระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม (MDES)</div>
                    </div>
                </div>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="setting_group" value="assets">

                    <div class="form-section-title">
                        <i class="bi bi-calendar-check text-primary"></i> 1. นโยบายปีงบประมาณและรหัสครุภัณฑ์
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="fiscal_year_current">ปีงบประมาณปัจจุบัน (พ.ศ.)</label>
                            <input type="number" id="fiscal_year_current" name="fiscal_year_current" class="form-control" value="{{ old('fiscal_year_current', $settings['fiscal_year_current']) }}" min="2500" max="2600" required style="font-weight: 700; font-size: 15px;">
                            <span class="form-text">ใช้กำหนดเป็นปีงบประมาณเริ่มต้นในหน้าเพิ่มครุภัณฑ์ใหม่และรายงานสรุป</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label required" for="asset_code_prefix">รหัสคำนำหน้าครุภัณฑ์โรงพยาบาล (Asset Prefix)</label>
                            <input type="text" id="asset_code_prefix" name="asset_code_prefix" class="form-control" value="{{ old('asset_code_prefix', $settings['asset_code_prefix']) }}" required placeholder="เช่น THC-COM, THC-MED" style="font-family: monospace; font-weight: 700;">
                            <span class="form-text">ใช้สำหรับแนะนำรูปแบบรหัสครุภัณฑ์คอมพิวเตอร์และพิมพ์สติกเกอร์ QR Code</span>
                        </div>
                    </div>

                    <div class="form-group" style="max-width: 480px;">
                        <label class="form-label required" for="assets_default_min_stock">เกณฑ์เตือนสต็อกอะไหล่ขั้นต่ำเริ่มต้น (Default Minimum Stock)</label>
                        <div class="input-group" style="display: flex;">
                            <input type="number" id="assets_default_min_stock" name="default_min_stock" class="form-control" value="{{ old('default_min_stock', $settings['default_min_stock']) }}" min="1" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                            <span style="background: #f1f5f9; border: 1px solid var(--border); border-left: none; padding: 8px 16px; font-size: 13.5px; color: #475569; border-top-right-radius: 8px; border-bottom-right-radius: 8px; display: flex; align-items: center;">ชิ้น / ตลับ</span>
                        </div>
                    </div>

                    <div class="form-section-title" style="margin-top: 26px;">
                        <i class="bi bi-award text-primary"></i> 2. เกณฑ์มาตรฐาน ICT กระทรวงดิจิทัลฯ (MDES Standards)
                    </div>

                    <label style="border: 1.5px solid #ccfbf1; border-radius: 12px; padding: 16px 20px; cursor: pointer; display: flex; align-items: flex-start; gap: 14px; background: #f0fdfa; transition: all 0.2s;">
                        <input type="checkbox" name="enforce_ict_standard" value="1" {{ $settings['enforce_ict_standard'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488; margin-top: 2px;">
                        <div>
                            <div style="font-weight: 700; font-size: 14px; color: #0f766e;">
                                แนะนำและกรอกสเปกคอมพิวเตอร์ตามเกณฑ์ราคากลาง MDES อัตโนมัติ
                            </div>
                            <div style="font-size: 12.5px; color: #475569; margin-top: 4px; line-height: 1.5;">
                                เมื่อเปิดใช้งาน ระบบจะแสดงรายการสเปกมาตรฐานและราคากลางราชการ (เช่น PC สำนักงาน, PC ประมวลผลผล, จอภาพ, เครื่องพิมพ์เลเซอร์, สแกนเนอร์) ในหน้าเพิ่ม/แก้ไขครุภัณฑ์ พร้อมเติมสเปกฮาร์ดแวร์ให้อัตโนมัติ
                            </div>
                        </div>
                    </label>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                            <i class="bi bi-floppy-fill"></i> บันทึกค่านโยบายครุภัณฑ์และพัสดุ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 4: Notifications (LINE Notify & SMTP Email)                  -->
    <!-- ================================================================ -->
    <div id="tab-notification" class="tab-content-panel">
        <!-- LINE Notify Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #dcfce7; color: #06c755; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="bi bi-line"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">ระบบแจ้งเตือนผ่าน LINE Notify</strong>
                        <div style="font-size: 12px; color: #64748b;">ส่งการแจ้งเตือนช่างทันทีเมื่อมีผู้สร้างใบแจ้งซ่อมใหม่ พร้อมระบุอาการเสียและระดับความเร่งด่วน</div>
                    </div>
                </div>
                <span class="status-pill {{ $settings['line_notify_enabled'] ? 'status-pill-success' : 'status-pill-secondary' }}">
                    {{ $settings['line_notify_enabled'] ? '🟢 เปิดใช้งานแล้ว' : '⚪ ยังไม่เปิดใช้งาน' }}
                </span>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="setting_group" value="notification">

                    <!-- LINE Banner -->
                    <div style="background: linear-gradient(135deg, #06c755 0%, #059669 100%); color: white; border-radius: 12px; padding: 18px 24px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div>
                                <strong style="font-size: 15px;">LINE Notify Service สำหรับทีมช่าง รพ.ทุ่งหัวช้าง</strong>
                                <div style="font-size: 12.5px; opacity: 0.9; margin-top: 2px;">
                                    แจ้งเตือนทันใจผ่านกลุ่ม LINE เมื่อเกิดเคสด่วน/ด่วนที่สุด
                                </div>
                            </div>
                        </div>
                        <a href="https://notify-bot.line.me" target="_blank" class="btn btn-sm" style="background: #ffffff; color: #06c755; font-weight: 700; border: none; border-radius: 8px; padding: 6px 14px;">
                            <i class="bi bi-box-arrow-up-right"></i> ออก Token ที่ LINE Notify
                        </a>
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-label" for="line_notify_token">LINE Notify Token</label>
                        <div style="position: relative;">
                            <input type="password" id="line_notify_token" name="line_notify_token" class="form-control" value="{{ old('line_notify_token', $settings['line_notify_token']) }}" placeholder="วาง Token ที่สร้างจาก notify-bot.line.me" style="padding-right: 44px; font-family: monospace;">
                            <button type="button" onclick="togglePasswordVisibility('line_notify_token', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;" title="แสดง/ซ่อน Token">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <span class="form-text">นำ Token จาก notify-bot.line.me มาผูกกับกลุ่ม LINE ของทีมช่างไอที รพ.ทุ่งหัวช้าง</span>
                    </div>

                    <!-- Notification Conditions -->
                    <div class="form-section-title">
                        <i class="bi bi-toggles text-primary"></i> เงื่อนไขและเหตุการณ์ที่ต้องการให้ส่งแจ้งเตือน
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                        <label style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 14px; background: #ffffff;">
                            <input type="checkbox" name="line_notify_enabled" value="1" {{ $settings['line_notify_enabled'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">เปิดใช้งานระบบส่งแจ้งเตือนผ่าน LINE Notify</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 1px;">เปิด/ปิด การส่งข้อความแจ้งเตือนทั้งหมดชั่วคราว</div>
                            </div>
                        </label>

                        <label style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 14px; background: #ffffff;">
                            <input type="checkbox" name="notify_on_new_ticket" value="1" {{ $settings['notify_on_new_ticket'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">ส่งข้อความทันทีเมื่อมีผู้สร้างใบแจ้งซ่อมใหม่</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 1px;">แจ้งเตือนเข้ากลุ่มทันทีเพื่อให้ช่างรับเรื่องได้อย่างรวดเร็ว</div>
                            </div>
                        </label>

                        <label style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 14px; background: #ffffff;">
                            <input type="checkbox" name="notify_on_critical_only" value="1" {{ $settings['notify_on_critical_only'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">แจ้งเตือนเฉพาะเคส "ด่วน" และ "ด่วนที่สุด (Critical)" เท่านั้น</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 1px;">กรองไม่ส่งเคสทั่วไป เพื่อป้องกันข้อความรบกวนมากเกินไปในกลุ่ม</div>
                            </div>
                        </label>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                            <i class="bi bi-floppy-fill"></i> บันทึกการตั้งค่า LINE Notify
                        </button>
                    </div>
                </form>

                <!-- 1-Click Test LINE Notify Box -->
                <div class="test-action-box">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-broadcast text-primary" style="font-size: 22px;"></i>
                        <div>
                            <strong style="font-size: 13.5px; color: #0f172a;">ทดสอบส่งข้อความแจ้งเตือนเข้า LINE ทันที</strong>
                            <div style="font-size: 12px; color: #64748b;">ส่งข้อความทดสอบเพื่อยืนยันว่า Token ถูกต้องและบอทส่งเข้ากลุ่มได้จริง</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="runTestLineNotify()" id="btn_test_line" style="font-weight: 600;">
                        <i class="bi bi-send-fill text-success"></i> ทดสอบส่งแจ้งเตือนเข้า LINE
                    </button>
                </div>
            </div>
        </div>

        <!-- SMTP & Mail Settings Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #f0f9ff; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-envelope-at-fill"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">การตั้งค่าเซิร์ฟเวอร์อีเมล (SMTP & Mail Delivery)</strong>
                        <div style="font-size: 12px; color: #64748b;">ใช้สำหรับส่งลิงก์สร้างรหัสผ่านผู้ใช้งานใหม่ และรีเซ็ตรหัสผ่าน</div>
                    </div>
                </div>
                <span class="status-pill status-pill-info">
                    <i class="bi bi-server"></i> {{ strtoupper($settings['mail_mailer']) }}
                </span>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="setting_group" value="mail">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="mail_mailer">ไดรเวอร์การส่งอีเมล (Mailer)</label>
                            <select name="mail_mailer" id="mail_mailer" class="form-select">
                                <option value="smtp" {{ $settings['mail_mailer'] == 'smtp' ? 'selected' : '' }}>SMTP Server (แนะนำ)</option>
                                <option value="sendmail" {{ $settings['mail_mailer'] == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="log" {{ $settings['mail_mailer'] == 'log' ? 'selected' : '' }}>Log (เขียนลงไฟล์ storage/logs สำหรับทดสอบ)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label required" for="mail_host">SMTP Host Server</label>
                            <input type="text" id="mail_host" name="mail_host" class="form-control" value="{{ old('mail_host', $settings['mail_host']) }}" placeholder="เช่น smtp.gmail.com หรือ mail.thchospital.go.th" style="font-family: monospace;">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="mail_port">SMTP Port</label>
                            <input type="number" id="mail_port" name="mail_port" class="form-control" value="{{ old('mail_port', $settings['mail_port']) }}" placeholder="587 หรือ 465" style="font-family: monospace;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="mail_encryption">การเข้ารหัส (Encryption)</label>
                            <select name="mail_encryption" id="mail_encryption" class="form-select">
                                <option value="tls" {{ $settings['mail_encryption'] == 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                <option value="ssl" {{ $settings['mail_encryption'] == 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                <option value="null" {{ empty($settings['mail_encryption']) || $settings['mail_encryption'] == 'null' ? 'selected' : '' }}>ไม่เข้ารหัส (None)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="mail_username">SMTP Username / Email ผู้ส่ง</label>
                            <input type="text" id="mail_username" name="mail_username" class="form-control" value="{{ old('mail_username', $settings['mail_username']) }}" placeholder="user@gmail.com">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="mail_password">SMTP Password / App Password</label>
                            <div style="position: relative;">
                                <input type="password" id="mail_password" name="mail_password" class="form-control" value="{{ old('mail_password', $settings['mail_password']) }}" placeholder="รหัสผ่านอีเมล หรือ App Password 16 หลัก" style="padding-right: 44px; font-family: monospace;">
                                <button type="button" onclick="togglePasswordVisibility('mail_password', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;" title="แสดง/ซ่อน รหัสผ่าน">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="mail_from_address">อีเมลผู้ส่ง (From Address)</label>
                            <input type="email" id="mail_from_address" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" placeholder="noreply@thchospital.go.th">
                        </div>

                        <div class="form-group">
                            <label class="form-label required" for="mail_from_name">ชื่อผู้ส่ง (From Name)</label>
                            <input type="text" id="mail_from_name" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $settings['mail_from_name']) }}" placeholder="โรงพยาบาลทุ่งหัวช้าง">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                            <i class="bi bi-floppy-fill"></i> บันทึกการตั้งค่าระบบอีเมล
                        </button>
                    </div>
                </form>

                <!-- 1-Click Test Mail Delivery Box -->
                <div class="test-action-box">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-envelope-paper-fill text-primary" style="font-size: 22px;"></i>
                        <div>
                            <strong style="font-size: 13.5px; color: #0f172a;">ทดสอบส่งอีเมล (Test Mail Delivery)</strong>
                            <div style="font-size: 12px; color: #64748b;">ส่งอีเมลทดสอบไปยังบัญชีของคุณเพื่อตรวจเช็คการเชื่อมต่อ SMTP Server</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                        <input type="email" id="test_mail_recipient" class="form-control" placeholder="อีเมลผู้รับ" value="{{ auth()->user()?->email }}" style="width: 220px; padding: 6px 10px; font-size: 13px;">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="runTestMail()" id="btn_test_mail" style="font-weight: 600;">
                            <i class="bi bi-send-fill text-info"></i> ส่งอีเมลทดสอบ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 5: Security, SSO & Data Retention Policy                     -->
    <!-- ================================================================ -->
    <div id="tab-security" class="tab-content-panel">
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">ความปลอดภัยและการยืนยันตัวตน (Google OAuth & ThaID)</strong>
                        <div style="font-size: 12px; color: #64748b;">ระบบ Single Sign-On (SSO) ด้วยบัญชีองค์กร Google และบัตรประชาชนดิจิทัล ThaID กรมการปกครอง</div>
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <span class="status-pill {{ !empty($settings['google_client_id']) ? 'status-pill-success' : 'status-pill-secondary' }}">
                        Google SSO: {{ !empty($settings['google_client_id']) ? 'พร้อมใช้งาน' : 'ยังไม่ตั้งค่า' }}
                    </span>
                    <span class="status-pill {{ !empty($settings['thaid_client_id']) ? 'status-pill-success' : 'status-pill-secondary' }}">
                        ThaID: {{ !empty($settings['thaid_client_id']) ? 'พร้อมใช้งาน' : 'ยังไม่ตั้งค่า' }}
                    </span>
                </div>
            </div>
            <div class="settings-card-body">
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="setting_group" value="security">

                    <!-- Google OAuth Card -->
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                            <div style="font-weight: 700; font-size: 15px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-google" style="color: #ea4335; font-size: 18px;"></i>
                                <span>Google Sign-In (OAuth 2.0 & Google One-Tap)</span>
                            </div>
                            <span class="badge badge-secondary" style="font-size: 11px;">Google Cloud Console</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="google_client_id">Google Client ID</label>
                                <input type="text" id="google_client_id" name="google_client_id" class="form-control" value="{{ old('google_client_id', $settings['google_client_id']) }}" placeholder="xxxxxx.apps.googleusercontent.com" style="font-family: monospace;">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="google_client_secret">Google Client Secret</label>
                                <div style="position: relative;">
                                    <input type="password" id="google_client_secret" name="google_client_secret" class="form-control" value="{{ old('google_client_secret', $settings['google_client_secret']) }}" placeholder="GOCSPX-xxxxxx" style="font-family: monospace; padding-right: 44px;">
                                    <button type="button" onclick="togglePasswordVisibility('google_client_secret', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label" for="google_redirect_uri" style="display: flex; align-items: center; justify-content: space-between;">
                                <span>กำหนด Google Redirect URI เอง (Custom Override)</span>
                                <span style="font-size: 11px; font-weight: 400; color: #64748b;">(เว้นว่างไว้เพื่อใช้ค่าอัตโนมัติตาม Host ปัจจุบัน)</span>
                            </label>
                            <input type="text" id="google_redirect_uri" name="google_redirect_uri" class="form-control" value="{{ old('google_redirect_uri', $settings['google_redirect_uri']) }}" placeholder="{{ url('/auth/google/callback') }}" style="font-family: monospace; font-size: 13px;">
                        </div>

                        <!-- URI Solution Box -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="font-size: 12.5px; color: #334155;">
                                <strong>URI ปัจจุบัน:</strong> <code id="uri_google_current" style="color: #0284c7;">{{ !empty($settings['google_redirect_uri']) ? $settings['google_redirect_uri'] : url('/auth/google/callback') }}</code>
                            </div>
                            <button type="button" class="copy-btn" onclick="copyToClipboard('uri_google_current', this)">
                                <i class="bi bi-clipboard"></i> คัดลอก URI
                            </button>
                        </div>
                    </div>

                    <!-- ThaID Card -->
                    <div style="background: #ffffff; border: 1.5px solid #ccfbf1; border-radius: 12px; padding: 22px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(13, 148, 136, 0.04);">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                            <div style="font-weight: 700; font-size: 15px; color: #0f766e; display: flex; align-items: center; gap: 8px;">
                                <i class="bi bi-person-badge-fill" style="color: #0d9488; font-size: 18px;"></i>
                                <span>ThaID (OpenID Connect - กรมการปกครอง DOPA)</span>
                            </div>
                            <span class="badge badge-primary" style="font-size: 11px;">DOPA OpenID</span>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label" for="thaid_client_id">ThaID Client ID</label>
                                <input type="text" id="thaid_client_id" name="thaid_client_id" class="form-control" value="{{ old('thaid_client_id', $settings['thaid_client_id']) }}" placeholder="ThaID App Client ID" style="font-family: monospace;">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="thaid_client_secret">ThaID Client Secret</label>
                                <div style="position: relative;">
                                    <input type="password" id="thaid_client_secret" name="thaid_client_secret" class="form-control" value="{{ old('thaid_client_secret', $settings['thaid_client_secret']) }}" placeholder="ThaID Secret Key" style="font-family: monospace; padding-right: 44px;">
                                    <button type="button" onclick="togglePasswordVisibility('thaid_client_secret', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #64748b; cursor: pointer;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div style="background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                            <div style="font-size: 12.5px; color: #0f766e;">
                                <strong>ThaID Callback URI:</strong> <code id="uri_thaid">{{ url('/auth/thaid/callback') }}</code>
                            </div>
                            <button type="button" class="copy-btn" onclick="copyToClipboard('uri_thaid', this)">
                                <i class="bi bi-clipboard"></i> คัดลอก
                            </button>
                        </div>
                    </div>

                    <!-- Security & Data Retention Policies -->
                    <div class="form-section-title">
                        <i class="bi bi-shield-check text-primary"></i> นโยบายความปลอดภัยและการเก็บรักษาข้อมูล (Data Retention Policy)
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 22px;">
                        <label style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 14px; background: #ffffff;">
                            <input type="checkbox" name="allow_thai_id_login" value="1" {{ $settings['allow_thai_id_login'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">อนุญาตให้ล็อกอินด้วยเลขบัตรประชาชน (Thai National ID)</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 1px;">เปิดให้บุคลากรสามารถใช้เลขบัตรประชาชน 13 หลักเข้าสู่ระบบได้</div>
                            </div>
                        </label>

                        <label style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 14px; background: #ffffff;">
                            <input type="checkbox" name="enforce_mfa_all" value="1" {{ $settings['enforce_mfa_all'] ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">บังคับใช้การยืนยันตัวตนแบบหลายปัจจัย (Enforce MFA/2FA for All Staff)</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 1px;">เพิ่มความปลอดภัยสูงสุดตามมาตรฐานความมั่นคงปลอดภัยไซเบอร์โรงพยาบาล</div>
                            </div>
                        </label>

                        <label style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 14px; background: #ffffff;">
                            <input type="checkbox" name="backup_auto_enabled" value="1" {{ !empty($settings['backup_auto_enabled']) ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #0d9488;">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #0f172a;">ระบบสำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 1px;">เปิดการสำรองข้อมูลอัตโนมัติก่อนเริ่มกู้คืน (Restore) หรืออัปเดตระบบ (ปิดไว้เพื่อประหยัดพื้นที่และทำงานได้รวดเร็ว)</div>
                            </div>
                        </label>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label required" for="backup_retention_days">ระยะเวลาการเก็บรักษาไฟล์สำรองฐานข้อมูล (วัน)</label>
                            <div class="input-group" style="display: flex;">
                                <input type="number" id="backup_retention_days" name="backup_retention_days" class="form-control" value="{{ old('backup_retention_days', $settings['backup_retention_days']) }}" min="7" max="365" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <span style="background: #f1f5f9; border: 1px solid var(--border); border-left: none; padding: 8px 16px; font-size: 13.5px; color: #475569; border-top-right-radius: 8px; border-bottom-right-radius: 8px; display: flex; align-items: center;">วัน</span>
                            </div>
                            <span class="form-text">ระบบจะแจ้งเตือนให้ล้างไฟล์สำรองเก่าที่มีอายุเกินกำหนดนี้</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label required" for="audit_log_retention_days">ระยะเวลาการเก็บรักษาบันทึกกิจกรรม Audit Logs (วัน)</label>
                            <div class="input-group" style="display: flex;">
                                <input type="number" id="audit_log_retention_days" name="audit_log_retention_days" class="form-control" value="{{ old('audit_log_retention_days', $settings['audit_log_retention_days']) }}" min="30" max="730" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                                <span style="background: #f1f5f9; border: 1px solid var(--border); border-left: none; padding: 8px 16px; font-size: 13.5px; color: #475569; border-top-right-radius: 8px; border-bottom-right-radius: 8px; display: flex; align-items: center;">วัน</span>
                            </div>
                            <span class="form-text">ตาม พ.ร.บ. ว่าด้วยการกระทำความผิดเกี่ยวกับคอมพิวเตอร์ (ไม่น้อยกว่า 90 วัน)</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 600;">
                            <i class="bi bi-floppy-fill"></i> บันทึกการตั้งค่าความปลอดภัย
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 6: System Info & Release Notes                              -->
    <!-- ================================================================ -->
    <div id="tab-version" class="tab-content-panel">
        <div class="settings-card">
            <div class="settings-card-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>
                    <div>
                        <strong style="font-size: 15px; color: #0f172a;">ข้อมูลรุ่นระบบและสภาพแวดล้อม (System Environment & Specs)</strong>
                        <div style="font-size: 12px; color: #64748b;">รายละเอียดเวอร์ชัน ซอฟต์แวร์ และประวัติการอัปเดตระบบ</div>
                    </div>
                </div>
                <span class="badge badge-primary" style="font-size: 12px; padding: 4px 10px;">v{{ $versionInfo['version'] }}</span>
            </div>
            <div class="settings-card-body">
                <!-- System Specs Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 26px;">
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 18px; border-left: 4px solid #0d9488;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 500;">เวอร์ชันระบบปัจจุบัน</div>
                        <div style="font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                            <span>v{{ $versionInfo['version'] }}</span>
                            <span class="badge" style="background: #10b981; color: white; font-size: 11px; padding: 2px 8px; border-radius: 12px;">Active</span>
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">Build {{ $versionInfo['build'] }}</div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 18px; border-left: 4px solid #0284c7;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 500;">สภาพแวดล้อมระบบ (Environment)</div>
                        <div style="font-size: 18px; font-weight: 700; color: #0284c7; margin-top: 4px;">
                            {{ ucfirst($versionInfo['environment']) }}
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">เผยแพร่: {{ $versionInfo['release_date'] }}</div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 18px; border-left: 4px solid #f59e0b;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 500;">PHP & Laravel Framework</div>
                        <div style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                            Laravel {{ $versionInfo['laravel_version'] }}
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">PHP {{ $versionInfo['php_version'] }}</div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 18px; border-left: 4px solid #8b5cf6;">
                        <div style="font-size: 12px; color: #64748b; font-weight: 500;">ฐานข้อมูลหลัก (Database)</div>
                        <div style="font-size: 18px; font-weight: 700; color: #0f172a; margin-top: 4px;">
                            MariaDB
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">{{ config('database.connections.mysql.host') }}</div>
                    </div>
                </div>

                <!-- Custom Version String Form -->
                <form action="{{ route('settings.update') }}" method="POST" style="background: #ffffff; border: 1.5px dashed var(--border); border-radius: 12px; padding: 20px; margin-bottom: 28px;">
                    @csrf
                    <input type="hidden" name="setting_group" value="version">
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-pencil-square text-primary"></i>
                        <span>ปรับเปลี่ยนเลขเวอร์ชันที่แสดงผล (Custom Version String)</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 240px;">
                            <label class="form-label" style="font-size: 13px; font-weight: 600;">เลขเวอร์ชันระบบ:</label>
                            <input type="text" name="app_version" class="form-control" value="{{ $settings['app_version'] }}" required style="font-weight: 600;">
                            <small class="text-muted">เช่น 2.1.0 หรือ 2.2.0-pro</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 13.5px; font-weight: 600;">
                            <i class="bi bi-save"></i> บันทึกเลขเวอร์ชัน
                        </button>
                    </div>
                </form>

                <!-- Changelog History -->
                <div class="form-section-title">
                    <i class="bi bi-journal-text text-primary"></i> ประวัติและบันทึกการอัปเดตระบบ (Changelog & Release Notes)
                </div>

                <div style="display: flex; flex-direction: column; gap: 18px;">
                    @foreach($versionInfo['changelog'] as $ver => $log)
                    <div style="border: 1px solid {{ $loop->first ? '#0d9488' : 'var(--border)' }}; border-radius: 12px; padding: 20px; background: {{ $loop->first ? 'linear-gradient(to right, #ffffff, #f0fdf4)' : '#ffffff' }}; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 17px; font-weight: 700; color: #0f172a;">v{{ $ver }}</span>
                                <span style="background: {{ $log['badge_color'] ?? '#0d9488' }}; color: white; font-size: 11px; padding: 2px 10px; border-radius: 12px; font-weight: 600;">
                                    {{ $log['badge'] ?? 'Release' }}
                                </span>
                            </div>
                            <span style="font-size: 12.5px; color: #64748b;">
                                <i class="bi bi-calendar3"></i> เผยแพร่: {{ $log['date'] }}
                            </span>
                        </div>
                        <div style="font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 10px;">
                            {{ $log['title'] }}
                        </div>
                        <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.7;">
                            @foreach($log['highlights'] as $item)
                            <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- Tab 7: Data Management & Danger Zone                             -->
    <!-- ================================================================ -->
    <div id="tab-danger-zone" class="tab-content-panel">
        <!-- Live Stats Overview Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div class="card" style="margin-bottom: 0; padding: 18px; border-left: 4px solid var(--primary); background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: var(--text-muted);">ใบแจ้งซ่อมทั้งหมด</div>
                    <i class="bi bi-tools text-primary" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: var(--text-main); margin-top: 4px;">{{ number_format($systemStats['repairs'] ?? 0) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">รายการงานแจ้งซ่อม</div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px; border-left: 4px solid #0284c7; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: var(--text-muted);">คำขอข้อมูล HosXP</div>
                    <i class="bi bi-file-earmark-medical text-info" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #0284c7; margin-top: 4px;">{{ number_format($systemStats['data_requests'] ?? 0) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">คำขอสถิติสารสนเทศ</div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px; border-left: 4px solid #7c3aed; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: var(--text-muted);">บันทึก Audit Logs</div>
                    <i class="bi bi-shield-check" style="color: #7c3aed; font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #7c3aed; margin-top: 4px;">{{ number_format($systemStats['audit_logs'] ?? 0) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">ประวัติกิจกรรมระบบ</div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px; border-left: 4px solid #10b981; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: var(--text-muted);">ครุภัณฑ์คอมพิวเตอร์</div>
                    <i class="bi bi-laptop text-success" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #10b981; margin-top: 4px;">{{ number_format($systemStats['assets'] ?? 0) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">อุปกรณ์ในทะเบียน</div>
            </div>

            <div class="card" style="margin-bottom: 0; padding: 18px; border-left: 4px solid #f59e0b; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 12px; color: var(--text-muted);">สต็อกอะไหล่ IT</div>
                    <i class="bi bi-box-seam text-warning" style="font-size: 18px;"></i>
                </div>
                <div style="font-size: 26px; font-weight: 700; color: #f59e0b; margin-top: 4px;">{{ number_format($systemStats['spare_parts'] ?? 0) }}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">รายการอะไหล่สิ้นเปลือง</div>
            </div>
        </div>

        <!-- Danger Zone Box -->
        <div class="card" style="border: 2px solid #fee2e2; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(220, 38, 38, 0.06);">
            <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); padding: 22px 28px; color: white; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: white;">พื้นที่ควบคุมพิเศษ: จัดการและล้างข้อมูลระบบ (System Data Wipe)</h3>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 2px;">เฉพาะผู้ดูแลระบบ (Admin) เท่านั้น โปรดตรวจสอบความถูกต้องก่อนดำเนินการ</div>
                    </div>
                </div>
                <a href="{{ route('backups.index') }}" class="btn btn-sm" style="background: rgba(255, 255, 255, 0.2); color: white; border: 1px solid rgba(255, 255, 255, 0.3); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px;">
                    <i class="bi bi-cloud-arrow-down-fill"></i> ไปหน้าสำรองข้อมูล
                </a>
            </div>

            <div class="card-body" style="padding: 28px;">
                <div style="background: #fff5f5; border: 1px solid #fecaca; border-radius: 10px; padding: 16px 20px; margin-bottom: 26px; display: flex; align-items: flex-start; gap: 14px;">
                    <i class="bi bi-shield-slash-fill text-danger" style="font-size: 22px; flex-shrink: 0; margin-top: 2px;"></i>
                    <div style="font-size: 13.5px; color: #991b1b; line-height: 1.6;">
                        <strong>ข้อควรระวังสำคัญ:</strong> การล้างข้อมูลจะเป็นการลบระเบียนข้อมูลออกจากฐานข้อมูลอย่างถาวร 
                        อย่างไรก็ตาม ระบบมีกลไก <strong>"สร้างจุดสำรองข้อมูลอัตโนมัติ (Auto-Backup)"</strong> ก่อนเริ่มลบทุกครั้ง 
                        เพื่อความปลอดภัยสูงสุดและสามารถกดกู้คืน (Restore) กลับมาได้ตลอดเวลา
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
                    
                    <!-- Action Card 1: Operational Data Wipe -->
                    <div style="border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 24px; background: #ffffff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                        <div>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                                <div style="width: 42px; height: 42px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    <i class="bi bi-eraser-fill"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">แบบที่ 1: ล้างข้อมูลการดำเนินงาน</h4>
                                    <span style="font-size: 12px; color: #64748b;">(ล้างเฉพาะใบแจ้งซ่อม, คำขอข้อมูล และ Audit Logs)</span>
                                </div>
                            </div>
                            <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 14px;">
                                เหมาะสำหรับการล้างข้อมูลทดสอบ (Test Data) หรือการเริ่มรอบบันทึกข้อมูลใหม่ โดยยังคงรักษาข้อมูลโครงสร้างหลักไว้:
                            </p>
                            <ul style="font-size: 12.5px; color: #64748b; padding-left: 20px; line-height: 1.8; margin-bottom: 20px;">
                                <li><strong style="color: #dc2626;">ลบ:</strong> ใบแจ้งซ่อมทั้งหมด ({{ $systemStats['repairs'] ?? 0 }} รายการ) พร้อมประวัติและอะไหล่ที่ใช้</li>
                                <li><strong style="color: #dc2626;">ลบ:</strong> คำขอข้อมูลสารสนเทศ HosXP ({{ $systemStats['data_requests'] ?? 0 }} รายการ)</li>
                                <li><strong style="color: #dc2626;">ลบ:</strong> บันทึกประวัติกิจกรรม Audit Logs ({{ $systemStats['audit_logs'] ?? 0 }} รายการ)</li>
                                <li><strong style="color: #16a34a;">คงไว้:</strong> บัญชีผู้ใช้งาน, ข้อมูลแผนก, ครุภัณฑ์, สต็อกอะไหล่, การตั้งค่าระบบ</li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-warning" onclick="openWipeModal('operational')" style="width: 100%; padding: 12px 18px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 8px;">
                            <i class="bi bi-trash3-fill"></i> ล้างข้อมูลการดำเนินงาน (Operational Wipe)
                        </button>
                    </div>

                    <!-- Action Card 2: Full System Factory Reset -->
                    <div style="border: 1.5px solid #fca5a5; border-radius: 12px; padding: 24px; background: #fffaf0; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 2px 8px rgba(220, 38, 38, 0.04);">
                        <div>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                                <div style="width: 42px; height: 42px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    <i class="bi bi-radioactive"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #991b1b;">แบบที่ 2: รีเซ็ตระบบทั้งหมด</h4>
                                    <span style="font-size: 12px; color: #b91c1c;">(Full System Factory Reset)</span>
                                </div>
                            </div>
                            <p style="font-size: 13px; color: #475569; line-height: 1.6; margin-bottom: 14px;">
                                ล้างข้อมูลทุกส่วนในระบบ และรีเซ็ตกลับเป็นค่าเริ่มต้นมาตรฐานโรงพยาบาลทุ่งหัวช้าง:
                            </p>
                            <ul style="font-size: 12.5px; color: #64748b; padding-left: 20px; line-height: 1.8; margin-bottom: 20px;">
                                <li><strong style="color: #dc2626;">ลบ:</strong> ข้อมูลใบแจ้งซ่อม, คำขอข้อมูล, Audit Logs ทั้งหมด</li>
                                <li><strong style="color: #dc2626;">รีเซ็ต:</strong> ข้อมูลครุภัณฑ์และสต็อกอะไหล่กลับสู่แม่แบบตั้งต้น</li>
                                <li><strong style="color: #16a34a;">ปลอดภัย:</strong> <strong>บัญชีผู้ดูแลระบบของคุณ ({{ auth()->user()->name }}) จะไม่ถูกลบ</strong> และคงสิทธิ์เข้าใช้งานตามเดิม</li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-danger" onclick="openWipeModal('full')" style="width: 100%; padding: 12px 18px; font-size: 14px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 8px;">
                            <i class="bi bi-arrow-counterclockwise"></i> ล้างข้อมูลและรีเซ็ตระบบทั้งหมด (Factory Reset)
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Safety Verification for Data Wipe -->
<div id="wipeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 520px; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.25); border: 1px solid var(--border);">
        
        <!-- Modal Header -->
        <div id="modal_header_bar" style="background: #dc2626; color: white; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-exclamation-octagon-fill" style="font-size: 20px;"></i>
                <h3 id="modal_title" style="margin: 0; font-size: 16px; font-weight: 700; color: white;">ยืนยันการล้างข้อมูลระบบ</h3>
            </div>
            <button type="button" onclick="closeWipeModal()" style="background: none; border: none; color: white; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
        </div>

        <!-- Modal Body -->
        <form action="{{ route('settings.clear-data') }}" method="POST" id="wipeDataForm" style="padding: 24px;">
            @csrf
            <input type="hidden" name="wipe_scope" id="modal_wipe_scope" value="">

            <div id="modal_warning_text" style="font-size: 13.5px; color: #475569; line-height: 1.6; margin-bottom: 18px;">
            </div>

            <!-- Auto Backup Checkbox -->
            <label style="cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: #0369a1; background: #f0f9ff; padding: 12px 14px; border-radius: 8px; border: 1.5px solid #bae6fd; margin-bottom: 18px;">
                <input type="checkbox" name="auto_backup" value="1" checked style="width: 18px; height: 18px; accent-color: #0d9488;">
                <span>สร้างจุดสำรองฐานข้อมูลฉุกเฉินก่อนล้าง (Auto-Backup)</span>
            </label>

            <!-- Admin Password Verification -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label required" for="admin_password" style="font-weight: 600; font-size: 13px;">
                    1. ป้อนรหัสผ่านของผู้ดูแลระบบเพื่อยืนยันสิทธิ์:
                </label>
                <input type="password" id="admin_password" name="admin_password" class="form-control" placeholder="รหัสผ่านเข้าสู่ระบบของคุณ" required autocomplete="current-password">
            </div>

            <!-- Confirmation Text Input -->
            <div class="form-group" style="margin-bottom: 22px;">
                <label class="form-label required" for="confirmation_text" style="font-weight: 600; font-size: 13px;">
                    2. พิมพ์คำว่า <code id="required_phrase" style="font-weight: 700; color: #dc2626; font-size: 13.5px; background: #fee2e2; padding: 2px 6px; border-radius: 4px;"></code> ในช่องด้านล่าง:
                </label>
                <input type="text" id="confirmation_text" name="confirmation_text" class="form-control" placeholder="" required autocomplete="off" style="font-weight: 600; letter-spacing: 0.5px;">
            </div>

            <!-- Modal Actions -->
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
    function switchTab(tabId, element) {
        document.querySelectorAll('.settings-tab-btn').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-content-panel').forEach(el => el.classList.remove('active'));

        if (element) {
            element.classList.add('active');
        } else {
            const btn = document.getElementById('tab_btn_' + tabId.replace(/-/g, '_'));
            if (btn) btn.classList.add('active');
        }

        const panel = document.getElementById('tab-' + tabId);
        if (panel) panel.classList.add('active');

        // Store hash
        window.location.hash = tabId;
    }

    // Restore tab from hash
    window.addEventListener('DOMContentLoaded', () => {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const targetPanel = document.getElementById('tab-' + hash);
            if (targetPanel) {
                switchTab(hash, null);
            }
        }
    });

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    function copyToClipboard(elementId, btn) {
        const text = document.getElementById(elementId).innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> คัดลอกแล้ว';
            btn.style.background = '#10b981';
            btn.style.color = '#ffffff';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        });
    }

    // Test LINE Notify via AJAX
    function runTestLineNotify() {
        const btn = document.getElementById('btn_test_line');
        const token = document.getElementById('line_notify_token').value;
        const originalHtml = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังส่งข้อความ...';

        fetch("{{ route('settings.test-line') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ token: token })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (res.body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'ส่งแจ้งเตือนสำเร็จ!',
                    text: res.body.message,
                    confirmButtonColor: '#0d9488',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'ทดสอบไม่สำเร็จ',
                    text: res.body.message || 'โปรดตรวจสอบ LINE Notify Token ของท่าน',
                    confirmButtonColor: '#ef4444',
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                text: err.toString(),
                confirmButtonColor: '#ef4444',
            });
        });
    }

    // Test Mail Delivery via AJAX
    function runTestMail() {
        const btn = document.getElementById('btn_test_mail');
        const email = document.getElementById('test_mail_recipient').value;
        const originalHtml = btn.innerHTML;

        if (!email) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณาระบุอีเมล',
                text: 'โปรดระบุอีเมลผู้รับเพื่อทดสอบการส่งจดหมาย',
                confirmButtonColor: '#0d9488',
            });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังส่ง...';

        fetch("{{ route('settings.test-mail') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (res.body.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'ส่งอีเมลทดสอบสำเร็จ!',
                    text: res.body.message,
                    confirmButtonColor: '#0d9488',
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'ส่งอีเมลไม่สำเร็จ',
                    text: res.body.message || 'โปรดตรวจสอบการตั้งค่า SMTP Host/Port/Password',
                    confirmButtonColor: '#ef4444',
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาดในการเชื่อมต่อ',
                text: err.toString(),
                confirmButtonColor: '#ef4444',
            });
        });
    }

    function openWipeModal(scope) {
        document.getElementById('modal_wipe_scope').value = scope;
        document.getElementById('admin_password').value = '';
        document.getElementById('confirmation_text').value = '';

        if (scope === 'operational') {
            document.getElementById('modal_title').textContent = 'ยืนยันการล้างข้อมูลการดำเนินงาน (Operational Wipe)';
            document.getElementById('modal_header_bar').style.background = '#d97706';
            document.getElementById('required_phrase').textContent = 'CLEAR-OPERATIONAL';
            document.getElementById('confirmation_text').placeholder = 'พิมพ์ CLEAR-OPERATIONAL';
            document.getElementById('modal_warning_text').innerHTML = 'คุณกำลังจะลบ <strong>ใบแจ้งซ่อมทั้งหมด, คำขอข้อมูล HosXP และ Audit Logs</strong> ข้อมูลส่วนนี้จะไม่สามารถย้อนคืนได้';
            document.getElementById('modal_submit_btn').className = 'btn btn-warning';
        } else {
            document.getElementById('modal_title').textContent = 'ยืนยันการรีเซ็ตระบบทั้งหมด (Full Factory Reset)';
            document.getElementById('modal_header_bar').style.background = '#dc2626';
            document.getElementById('required_phrase').textContent = 'RESET-ALL-DATA';
            document.getElementById('confirmation_text').placeholder = 'พิมพ์ RESET-ALL-DATA';
            document.getElementById('modal_warning_text').innerHTML = 'คุณกำลังจะ <strong>ล้างข้อมูลทั้งหมดในระบบและรีเซ็ตสู่ค่าเริ่มต้น</strong> (บัญชีผู้ดูแลระบบของคุณจะไม่ถูกลบ)';
            document.getElementById('modal_submit_btn').className = 'btn btn-danger';
        }

        const modal = document.getElementById('wipeModal');
        modal.style.display = 'flex';
    }

    function closeWipeModal() {
        const modal = document.getElementById('wipeModal');
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('wipeModal');
        if (event.target === modal) {
            closeWipeModal();
        }
    }
</script>
@endpush
