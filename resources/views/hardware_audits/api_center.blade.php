@extends('layouts.app')

@section('title', 'REST API Center & Web Audit - THC Hardware Scanner Hub')

@section('page_title', 'REST API Center & Web Audit')
@section('page_subtitle', 'ศูนย์ติดตั้งตัวสแกนฮาร์ดแวร์มาตรฐานความปลอดภัยไซเบอร์ และแผงควบคุมดึงข้อมูลสเปกคอมพิวเตอร์ระยะไกล ประจำปีงบประมาณ ' . $fiscalYear)

@push('styles')
<style>
    /* ==========================================================================
       THC HARDWARE SCANNER & REMOTE AUDIT HUB (CYBERSECURITY COMPLIANT)
       Modern, responsive, zero-bootstrap-dependent design system.
       ========================================================================== */

    .scanner-hub-container {
        width: 100%;
        max-width: 1400px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* 1. HERO BANNER */
    .hub-hero-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #0f766e 100%);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        color: #ffffff;
        box-shadow: 0 12px 32px -8px rgba(15, 23, 42, 0.45);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        padding: 28px 32px;
    }

    .hub-hero-blob-1 {
        position: absolute;
        right: -60px;
        top: -60px;
        width: 320px;
        height: 320px;
        background: radial-gradient(circle, rgba(45, 212, 191, 0.22) 0%, rgba(15, 118, 110, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .hub-hero-blob-2 {
        position: absolute;
        left: 25%;
        bottom: -80px;
        width: 260px;
        height: 260px;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, rgba(2, 132, 199, 0) 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .hub-hero-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 28px;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    @media (max-width: 992px) {
        .hub-hero-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }

    .hub-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(13, 148, 136, 0.35);
        color: #2dd4bf;
        border: 1px solid rgba(45, 212, 191, 0.45);
        border-radius: 9999px;
        padding: 5px 14px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .hub-status-pulse {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 10px #10b981;
        animation: pulseDot 2s infinite;
    }

    @keyframes pulseDot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.4); opacity: 0.6; }
    }

    .hub-hero-title {
        font-size: 26px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        line-height: 1.3;
    }

    .hub-hero-desc {
        color: #cbd5e1;
        font-size: 14.5px;
        line-height: 1.6;
        margin-bottom: 20px;
        max-width: 820px;
    }

    .hub-hero-badges-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .hub-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.16);
        border-radius: 6px;
        padding: 4px 10px;
        font-size: 11.5px;
        color: #e2e8f0;
        font-weight: 500;
    }

    .hub-hero-statcard {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 16px;
        padding: 20px 22px;
    }

    .hub-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hub-stat-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .hub-stat-label {
        font-size: 13px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .hub-stat-value {
        font-size: 14px;
        font-weight: 700;
        color: #ffffff;
    }

    .hub-stat-value.highlight-green {
        color: #34d399;
    }

    .hub-stat-value.highlight-teal {
        color: #2dd4bf;
    }

    /* 2. SEGMENTED TABS BAR (EXACTLY 3 EQUAL COLUMNS - 100% WIDTH) */
    .hub-tabs-bar {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        background: #f1f5f9;
        padding: 6px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
        border: 1px solid #e2e8f0;
        width: 100%;
        box-sizing: border-box;
    }

    .hub-tab-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px 14px;
        border-radius: 12px;
        color: #475569;
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
        width: 100%;
        min-height: 64px;
        gap: 4px;
        box-sizing: border-box;
    }

    .hub-tab-btn-title {
        font-size: 14.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        white-space: nowrap;
    }

    .hub-tab-btn-sub {
        font-size: 12px;
        color: #64748b;
        font-weight: 500;
        white-space: nowrap;
    }

    .hub-tab-badge {
        font-size: 10px;
        font-weight: 700;
        background: #10b981;
        color: #ffffff;
        padding: 2px 8px;
        border-radius: 99px;
        margin-left: 6px;
        display: inline-block;
    }

    .hub-tab-btn:hover {
        color: #0f766e;
        background: rgba(255, 255, 255, 0.7);
    }

    .hub-tab-btn:hover .hub-tab-btn-sub {
        color: #0d9488;
    }

    .hub-tab-btn.active {
        background: #ffffff;
        color: #0f766e;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }

    .hub-tab-btn.active .hub-tab-btn-sub {
        color: #0d9488;
        font-weight: 600;
    }

    @media (max-width: 860px) {
        .hub-tabs-bar {
            grid-template-columns: 1fr;
        }
        .hub-tab-btn {
            min-height: 52px;
        }
    }

    /* 3. TAB PANES */
    .hub-tab-pane {
        display: none;
    }

    .hub-tab-pane.active {
        display: block;
        animation: tabFadeIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes tabFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* 4. CARDS & SECTIONS */
    .hub-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .hub-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
    }

    .hub-card-title {
        font-size: 18.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hub-card-desc {
        font-size: 13.5px;
        color: #64748b;
        margin-top: 5px;
    }

    /* 5. DOWNLOAD CARDS */
    .download-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .download-card {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .download-card:hover {
        border-color: #0d9488;
        transform: translateY(-3px);
        box-shadow: 0 10px 24px -6px rgba(13, 148, 136, 0.15);
        background: #ffffff;
    }

    .download-card-featured {
        border-color: #0d9488;
        background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 100%);
    }

    .download-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-bottom: 14px;
    }

    .icon-exe {
        background: #e0f2fe;
        color: #0284c7;
    }

    .icon-bat {
        background: #ecfdf5;
        color: #059669;
    }

    .icon-del {
        background: #fef2f2;
        color: #dc2626;
    }

    .icon-json {
        background: #fef3c7;
        color: #d97706;
    }

    .download-card-name {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 6px;
        font-family: 'Consolas', monospace;
    }

    .download-card-desc {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    .download-meta {
        font-size: 11.5px;
        color: #94a3b8;
        display: flex;
        justify-content: space-between;
        margin-bottom: 14px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .download-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .download-btn-primary {
        background: #0d9488;
        color: #ffffff;
    }

    .download-btn-primary:hover {
        background: #0f766e;
        color: #ffffff;
    }

    .download-btn-secondary {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }

    .download-btn-secondary:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* 6. CYBERSECURITY PILLARS GRID */
    .cyber-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .cyber-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        gap: 16px;
        transition: all 0.2s ease;
    }

    .cyber-card:hover {
        border-color: #0d9488;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .cyber-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .cyber-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 5px;
    }

    .cyber-desc {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.55;
    }

    /* 7. HASH BOX */
    .hash-box {
        background: #0f172a;
        color: #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
        font-family: 'Consolas', monospace;
        font-size: 13px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
    }

    .hash-val {
        color: #38bdf8;
        word-break: break-all;
    }

    /* 8. FLEET TABLE & REMOTE PULL */
    .fleet-action-banner {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        border-radius: 16px;
        padding: 22px 28px;
        color: #ffffff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 24px;
        box-shadow: 0 6px 20px -4px rgba(13, 148, 136, 0.4);
    }

    .fleet-action-info {
        max-width: 700px;
    }

    .fleet-action-title {
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .fleet-action-desc {
        font-size: 13.5px;
        color: #ccfbf1;
        line-height: 1.5;
    }

    .btn-pull-all {
        background: #ffffff;
        color: #0f766e;
        font-size: 14px;
        font-weight: 700;
        padding: 12px 24px;
        border-radius: 12px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-pull-all:hover {
        background: #f0fdfa;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }

    .btn-pull-single {
        background: #0d9488;
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .btn-pull-single:hover {
        background: #0f766e;
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-pull-single:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }

    .fleet-table-container {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow-x: auto;
        background: #ffffff;
    }

    .fleet-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }

    .fleet-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        padding: 12px 16px;
        border-bottom: 1.5px solid #e2e8f0;
        white-space: nowrap;
    }

    .fleet-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }

    .fleet-table tr:hover td {
        background: #f8fafc;
    }

    .badge-status-online {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
        border-radius: 99px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .badge-status-offline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #cbd5e1;
        border-radius: 99px;
        padding: 3px 10px;
        font-size: 11.5px;
        font-weight: 600;
    }

    /* 9. COMMAND LOGS */
    .cmd-badge {
        font-size: 11px;
        font-weight: 700;
        border-radius: 6px;
        padding: 2px 8px;
        display: inline-block;
    }

    .cmd-badge-pending {
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }

    .cmd-badge-processing {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .cmd-badge-completed {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .cmd-badge-failed {
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    /* 10. SCHEMA TABLE */
    .schema-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .schema-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        padding: 12px 14px;
        border-bottom: 2px solid #e2e8f0;
    }

    .schema-table td {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
    }

    .schema-field {
        font-family: 'Consolas', monospace;
        font-weight: 700;
        color: #0f766e;
    }

    .schema-type {
        font-family: 'Consolas', monospace;
        color: #64748b;
        font-size: 12px;
    }

    .btn-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #ffffff;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="scanner-hub-container">

    <!-- ============================================================== -->
    <!-- 1. HERO BANNER                                                 -->
    <!-- ============================================================== -->
    <div class="hub-hero-banner api-hero-banner">
        <div class="hub-hero-blob-1"></div>
        <div class="hub-hero-blob-2"></div>

        <div class="hub-hero-grid">
            <div>
                <div class="hub-badge-pill">
                    <span class="hub-status-pulse"></span>
                    <span>CYBERSECURITY TELEMETRY ENGINE &bull; ZERO INBOUND PORTS</span>
                </div>
                <h1 class="hub-hero-title">
                    <i class="bi bi-shield-check" style="color: #2dd4bf;"></i>
                    THC Hardware Audit REST API &amp; Standalone Hub
                </h1>
                <p class="hub-hero-desc">
                    ระบบส่งข้อมูลสเปกคอมพิวเตอร์ผ่าน Standalone Client และแผงควบคุมสั่งดึงข้อมูลสเปกระยะไกล (Remote On-Demand Telemetry Pull)
                    ปลอดภัยตามมาตรฐานความปลอดภัยไซเบอร์ ไม่เปิดพอร์ตรับการเชื่อมต่อขาเข้าบนเครื่องลูกข่าย (Zero Inbound Ports)
                    และทำงานเฉพาะเมื่อผู้ดูแลระบบกดปุ่มสั่งดึงข้อมูลเท่านั้น
                </p>
                <div class="hub-hero-badges-row">
                    <div class="hub-tag"><i class="bi bi-shield-lock-fill" style="color: #34d399;"></i> Zero Inbound Ports</div>
                    <div class="hub-tag"><i class="bi bi-arrow-repeat" style="color: #38bdf8;"></i> Reverse HTTPS Polling</div>
                    <div class="hub-tag"><i class="bi bi-file-earmark-code" style="color: #fbbf24;"></i> Anti-RCE Protected</div>
                    <div class="hub-tag"><i class="bi bi-check2-circle" style="color: #a7f3d0;"></i> Read-Only WMI</div>
                    <div class="hub-tag"><i class="bi bi-cpu" style="color: #e2e8f0;"></i> Native .NET 4.5+</div>
                </div>
            </div>

            <!-- Quick Metrics Card -->
            <div class="hub-hero-statcard">
                <div style="font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                    <i class="bi bi-activity me-1" style="color: #2dd4bf;"></i> สถานะระบบตรวจนับ ปี {{ $fiscalYear }}
                </div>
                <div class="hub-stat-row">
                    <span class="hub-stat-label"><i class="bi bi-laptop"></i> เครื่องที่เชื่อมต่อสแตนด์บาย</span>
                    <span class="hub-stat-value highlight-green" id="statOnlineCount">{{ $onlineCount }} เครื่อง</span>
                </div>
                <div class="hub-stat-row">
                    <span class="hub-stat-label"><i class="bi bi-hdd-network"></i> สเปกที่จัดเก็บสำเร็จ</span>
                    <span class="hub-stat-value highlight-teal">{{ number_format($auditedCount) }} เครื่อง</span>
                </div>
                <div class="hub-stat-row">
                    <span class="hub-stat-label"><i class="bi bi-pc-display"></i> ครุภัณฑ์คอมพิวเตอร์ในระบบ</span>
                    <span class="hub-stat-value">{{ number_format($totalAssets) }} รายการ</span>
                </div>
                <div class="hub-stat-row">
                    <span class="hub-stat-label"><i class="bi bi-shield-fill-check"></i> มาตรฐานความปลอดภัย</span>
                    <span class="hub-stat-value" style="color: #38bdf8;">ระดับ Enterprise</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. SEGMENTED TABS BAR (EXACTLY 3 EQUAL COLUMNS - 100% WIDTH)   -->
    <!-- ============================================================== -->
    <div class="hub-tabs-bar api-tabs-bar" role="tablist">
        <button class="hub-tab-btn active api-tab-btn" id="btnTabStandalone" onclick="switchApiTab('standalone')">
            <div class="hub-tab-btn-title">
                <i class="bi bi-laptop-fill" style="color: #0d9488;"></i>
                <span>1. ตัวติดตั้งโปรแกรม THC Hardware Scanner</span>
            </div>
            <div class="hub-tab-btn-sub">แพ็กเกจติดตั้งมาตรฐาน Cybersecurity (.exe / .bat)</div>
        </button>

        <button class="hub-tab-btn api-tab-btn" id="btnTabRemotePull" onclick="switchApiTab('remotepull')">
            <div class="hub-tab-btn-title">
                <i class="bi bi-broadcast" style="color: #0284c7;"></i>
                <span>2. แผงควบคุมดึงข้อมูลจากระบบ (Remote Pull)</span>
                <span class="hub-tab-badge" id="tabOnlineBadge">{{ $onlineCount }} เครื่อง</span>
            </div>
            <div class="hub-tab-btn-sub">แอดมินกดปุ่มสั่งดึงสเปกเครื่อง &bull; Live Fleet Hub</div>
        </button>

        <button class="hub-tab-btn api-tab-btn" id="btnTabSchema" onclick="switchApiTab('schema')">
            <div class="hub-tab-btn-title">
                <i class="bi bi-database-check" style="color: #64748b;"></i>
                <span>3. โครงสร้างข้อมูลสเปก</span>
            </div>
            <div class="hub-tab-btn-sub">พจนานุกรมตาราง it_hardware_audits</div>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: INSTALLER & CYBERSECURITY STANDARDS                     -->
    <!-- ============================================================== -->
    <div class="hub-tab-pane active api-tab-pane" id="paneStandalone">
        <!-- Download Artifacts Card -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <h2 class="hub-card-title">
                        <i class="bi bi-box-seam-fill" style="color: #0d9488;"></i>
                        ชุดติดตั้งสมบูรณ์ THC Hardware Scanner (Cybersecurity Compliant)
                    </h2>
                    <div class="hub-card-desc">
                        ซอฟต์แวร์ตรวจนับสเปกคอมพิวเตอร์และเชื่อมส่งข้อมูลเข้าระบบตรวจนับอัตโนมัติ พัฒนาด้วย C# .NET Framework 4.5+ เข้ากันได้กับ Windows 7, 8, 10, 11 และ Windows Server ทุกรุ่น
                    </div>
                </div>
                <div>
                    <a href="{{ asset('downloads/manifest.json') }}" target="_blank" class="download-btn download-btn-secondary" style="width: auto; padding: 6px 14px;">
                        <i class="bi bi-filetype-json"></i> ดู Release Manifest.json
                    </a>
                </div>
            </div>

            <!-- Download Grid (4 Columns) -->
            <div class="download-grid">
                <!-- 1. Executable -->
                <div class="download-card download-card-featured">
                    <div>
                        <div class="download-card-icon icon-exe">
                            <i class="bi bi-laptop"></i>
                        </div>
                        <div class="download-card-name">THC_Hardware_Scanner.exe</div>
                        <div class="download-card-desc">
                            โปรแกรมสแกนเนอร์หลัก ทำงานได้ทั้งแบบ GUI แสดงสเปกทันที และแบบ Service ในพื้นหลังเพื่อรอรับคำสั่งดึงข้อมูลจากระบบ
                        </div>
                    </div>
                    <div>
                        <div class="download-meta">
                            <span>เวอร์ชัน 3.0.0</span>
                            <span>ขนาด ~28 KB</span>
                        </div>
                        <a href="{{ asset('downloads/THC_Hardware_Scanner.exe') }}" download class="download-btn download-btn-primary">
                            <i class="bi bi-download"></i> ดาวน์โหลด EXE
                        </a>
                    </div>
                </div>

                <!-- 2. Installer Batch -->
                <div class="download-card">
                    <div>
                        <div class="download-card-icon icon-bat">
                            <i class="bi bi-terminal-fill"></i>
                        </div>
                        <div class="download-card-name">install_scanner.bat</div>
                        <div class="download-card-desc">
                            สคริปต์ติดตั้งอัตโนมัติ 1 คลิก คัดลอกโปรแกรมไปยัง Program Files, ตั้งค่า config.json และสร้าง Windows Scheduled Task
                        </div>
                    </div>
                    <div>
                        <div class="download-meta">
                            <span>สิทธิ์ Administrator</span>
                            <span>1-Click Deploy</span>
                        </div>
                        <a href="{{ asset('downloads/install_scanner.bat') }}" download class="download-btn download-btn-primary" style="background: #059669;">
                            <i class="bi bi-lightning-charge-fill"></i> ดาวน์โหลดตัวติดตั้ง BAT
                        </a>
                    </div>
                </div>

                <!-- 3. Uninstaller Batch -->
                <div class="download-card">
                    <div>
                        <div class="download-card-icon icon-del">
                            <i class="bi bi-trash-fill"></i>
                        </div>
                        <div class="download-card-name">uninstall_scanner.bat</div>
                        <div class="download-card-desc">
                            สคริปต์ถอนการติดตั้งสมบูรณ์ หยุดโปรแกรม, ลบ Scheduled Task และล้างไฟล์โปรแกรมทั้งหมด ไม่มีไฟล์ตกค้างในระบบ
                        </div>
                    </div>
                    <div>
                        <div class="download-meta">
                            <span>Clean Removal</span>
                            <span>Zero Residual Trace</span>
                        </div>
                        <a href="{{ asset('downloads/uninstall_scanner.bat') }}" download class="download-btn download-btn-secondary" style="color: #dc2626; border-color: #fca5a5;">
                            <i class="bi bi-trash"></i> ดาวน์โหลดตัวถอนการติดตั้ง
                        </a>
                    </div>
                </div>

                <!-- 4. Hash Manifest -->
                <div class="download-card">
                    <div>
                        <div class="download-card-icon icon-json">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="download-card-name">manifest.json</div>
                        <div class="download-card-desc">
                            ไฟล์ลายเซ็นดิจิทัลและรายการค่าแฮชตรวจสอบความสมบูรณ์ของซอฟต์แวร์ (Cryptographic Integrity Verification)
                        </div>
                    </div>
                    <div>
                        <div class="download-meta">
                            <span>SHA-256 Digest</span>
                            <span>JSON Manifest</span>
                        </div>
                        <a href="{{ asset('downloads/manifest.json') }}" target="_blank" class="download-btn download-btn-secondary">
                            <i class="bi bi-file-earmark-check"></i> ตรวจสอบ Manifest
                        </a>
                    </div>
                </div>
            </div>

            <!-- SHA-256 Checksum Display -->
            <div class="hash-box">
                <div>
                    <span style="color: #94a3b8; margin-right: 8px;">SHA-256 Checksum (THC_Hardware_Scanner.exe):</span>
                    <span class="hash-val" id="hashText">F7AE241D43DBDB359C4DF3ADBAFC8C6BEC4F3F25CE88E1377A4F2FE484689B06</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="copyHash()" style="font-size: 11px; padding: 3px 10px;">
                    <i class="bi bi-clipboard"></i> คัดลอกค่าแฮช
                </button>
            </div>
        </div>

        <!-- Cybersecurity Standards Matrix Card -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <h2 class="hub-card-title">
                        <i class="bi bi-shield-lock-fill" style="color: #0f766e;"></i>
                        มาตรฐานความปลอดภัยไซเบอร์ (Cybersecurity Architecture &amp; Compliance)
                    </h2>
                    <div class="hub-card-desc">
                        สถาปัตยกรรมความปลอดภัย 6 ประการเพื่อป้องกันความเสี่ยงต่อโครงสร้างพื้นฐานไอทีของโรงพยาบาล
                    </div>
                </div>
            </div>

            <div class="cyber-grid">
                <!-- Pillar 1 -->
                <div class="cyber-card">
                    <div class="cyber-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-door-closed-fill"></i>
                    </div>
                    <div>
                        <div class="cyber-title">1. ปราศจากพอร์ตเปิดรับการเชื่อมต่อ (Zero Inbound Ports)</div>
                        <div class="cyber-desc">
                            โปรแกรมไม่เปิด Socket หรือ Port ใดๆ บนเครื่องลูกข่าย (เช่น พอร์ต 8080 หรือ 5000) ทำให้ไม่เป็นเป้าหมายของการสแกนพอร์ต (Port Scanning) และไม่ต้องเจาะข้อยกเว้นไฟร์วอลล์
                        </div>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="cyber-card">
                    <div class="cyber-icon" style="background: #ecfdf5; color: #059669;">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div>
                        <div class="cyber-title">2. การสื่อสารแบบ Reverse HTTPS Polling</div>
                        <div class="cyber-desc">
                            เครื่องลูกข่ายทำหน้าที่เพียงส่ง Heartbeat ขาออก (Outbound HTTPS) ไปยังเซิร์ฟเวอร์ระบบตรวจนับตามระยะเวลาที่กำหนด เพื่อตรวจสอบว่ามีคำสั่งจากผู้ดูแลระบบหรือไม่
                        </div>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="cyber-card">
                    <div class="cyber-icon" style="background: #fef2f2; color: #dc2626;">
                        <i class="bi bi-slash-circle-fill"></i>
                    </div>
                    <div>
                        <div class="cyber-title">3. ป้องกันการรันโค้ดอันตราย (Anti-RCE Protected)</div>
                        <div class="cyber-desc">
                            โปรแกรมมีชุดคำสั่งจำกัดเฉพาะที่ระบุไว้ล่วงหน้า (Immutable Command Set: <code>scan</code> และ <code>uninstall</code>) ไม่รับสคริปต์ CMD, PowerShell หรือคำสั่งภายนอกมาประมวลผลเด็ดขาด
                        </div>
                    </div>
                </div>

                <!-- Pillar 4 -->
                <div class="cyber-card">
                    <div class="cyber-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <div>
                        <div class="cyber-title">4. การอ่านสเปกแบบ Read-Only WMI</div>
                        <div class="cyber-desc">
                            อ่านสเปกคอมพิวเตอร์ผ่าน Windows Management Instrumentation (WMI) เฉพาะคลาสข้อมูลฮาร์ดแวร์เพื่ออ่านเท่านั้น ไม่มีการแก้ไขไฟล์ระบบ เรจิสทรี หรือสิทธิ์ของผู้ใช้งาน
                        </div>
                    </div>
                </div>

                <!-- Pillar 5 -->
                <div class="cyber-card">
                    <div class="cyber-icon" style="background: #f3e8ff; color: #7c3aed;">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <div class="cyber-title">5. การยืนยันสิทธิ์ด้วยโทเค็น (API Token Authentication)</div>
                        <div class="cyber-desc">
                            ทุกการสื่อสารระหว่างไคลเอนต์และเซิร์ฟเวอร์ได้รับการปกป้องด้วย Header <code>X-API-Token</code> คำขอที่ไม่ผ่านการพิสูจน์ตัวตนจะถูกปฏิเสธทันที (HTTP 401 Unauthorized)
                        </div>
                    </div>
                </div>

                <!-- Pillar 6 -->
                <div class="cyber-card">
                    <div class="cyber-icon" style="background: #e0e7ff; color: #4338ca;">
                        <i class="bi bi-power"></i>
                    </div>
                    <div>
                        <div class="cyber-title">6. แอดมินเป็นผู้ควบคุมการดึงข้อมูล (On-Demand Admin Pull)</div>
                        <div class="cyber-desc">
                            โปรแกรมจะอยู่ในโหมด Silent Standby ไม่สแกนสเปกซ้ำซ้อนตอนเปิดเครื่องเพื่อลดภาระ CPU และการจราจรในเครือข่าย โดยจะเริ่มสแกนเมื่อผู้ดูแลระบบกดปุ่มสั่งดึงข้อมูลจากระบบเท่านั้น
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deployment Guide Card -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <h2 class="hub-card-title">
                        <i class="bi bi-hdd-stack" style="color: #0284c7;"></i>
                        ขั้นตอนการติดตั้งและใช้งานสำหรับเจ้าหน้าที่ไอที (Deployment Guide)
                    </h2>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- Step 1 -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                    <div style="font-size: 13px; font-weight: 700; color: #0d9488; margin-bottom: 8px;">
                        วิธีที่ 1: ติดตั้งอัตโนมัติด้วย Batch Script (แนะนำสำหรับเครือข่าย)
                    </div>
                    <ol style="font-size: 12.5px; color: #475569; padding-left: 18px; margin: 0; line-height: 1.6;">
                        <li>ดาวน์โหลดไฟล์ <code>THC_Hardware_Scanner.exe</code> และ <code>install_scanner.bat</code> ไว้ในโฟลเดอร์เดียวกัน</li>
                        <li>คลิกขวาที่ไฟล์ <code>install_scanner.bat</code> แล้วเลือก <strong>"Run as administrator"</strong></li>
                        <li>สคริปต์จะคัดลอกไฟล์ไปยัง Program Files และสร้าง Scheduled Task ให้ทำงานอัตโนมัติในโหมดสแตนด์บาย</li>
                    </ol>
                </div>

                <!-- Step 2 -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                    <div style="font-size: 13px; font-weight: 700; color: #0284c7; margin-bottom: 8px;">
                        วิธีที่ 2: ติดตั้งผ่านหน้าต่างโปรแกรม (GUI Mode)
                    </div>
                    <ol style="font-size: 12.5px; color: #475569; padding-left: 18px; margin: 0; line-height: 1.6;">
                        <li>ดับเบิลคลิกไฟล์ <code>THC_Hardware_Scanner.exe</code> เพื่อเปิดหน้าต่างโปรแกรม</li>
                        <li>กดปุ่ม <strong>"ติดตั้งบริการ Standby อัตโนมัติ"</strong> ด้านล่างขวาของหน้าต่าง</li>
                        <li>โปรแกรมจะลงทะเบียนบริการและเริ่มสแตนด์บายเพื่อรอรับคำสั่งดึงข้อมูลจากระบบ</li>
                    </ol>
                </div>

                <!-- Step 3 -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                    <div style="font-size: 13px; font-weight: 700; color: #7c3aed; margin-bottom: 8px;">
                        วิธีที่ 3: กระจายติดตั้งผ่าน GPO หรือ Active Directory
                    </div>
                    <ol style="font-size: 12.5px; color: #475569; padding-left: 18px; margin: 0; line-height: 1.6;">
                        <li>กำหนด Startup Script หรือ Scheduled Task ใน Active Directory Group Policy</li>
                        <li>สั่งรันคำสั่ง: <code>THC_Hardware_Scanner.exe /install /server:{{ url('/api/hardware-audits') }}</code></li>
                        <li>เครื่องคอมพิวเตอร์ในโดเมนจะเชื่อมต่อเข้าสู่แผงควบคุมและพร้อมรับคำสั่งดึงสเปกทันที</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: REMOTE PULL CONTROL HUB (ADMIN PRESSES BUTTON)          -->
    <!-- ============================================================== -->
    <div class="hub-tab-pane api-tab-pane" id="paneRemotePull">
        <!-- Big Action Banner -->
        <div class="fleet-action-banner">
            <div class="fleet-action-info">
                <div class="fleet-action-title">
                    <i class="bi bi-broadcast-pin"></i>
                    แผงควบคุมการดึงข้อมูลสเปกระยะไกล (Remote On-Demand Telemetry Pull)
                </div>
                <div class="fleet-action-desc">
                    แอดมินสามารถกดปุ่มสั่งดึงข้อมูลสเปกคอมพิวเตอร์จากระบบได้ทันที เครื่องลูกข่ายที่ติดตั้ง THC Hardware Scanner ในโหมดสแตนด์บายจะทำการสแกนฮาร์ดแวร์ผ่าน WMI และส่งข้อมูลสเปกกลับเข้าระบบตรวจนับปีงบประมาณ {{ $fiscalYear }} โดยอัตโนมัติ
                </div>
            </div>

            <div>
                <button type="button" class="btn-pull-all" id="btnPullAllMachines" onclick="triggerPullAllFleet()">
                    <i class="bi bi-lightning-charge-fill" style="color: #f59e0b; font-size: 18px;"></i>
                    <span>กดปุ่มสั่งดึงข้อมูลสเปกทุกเครื่องเดี๋ยวนี้</span>
                </button>
            </div>
        </div>

        <!-- Connected Fleet Table Card -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <h2 class="hub-card-title">
                        <i class="bi bi-hdd-network" style="color: #0284c7;"></i>
                        รายการเครื่องคอมพิวเตอร์ที่เชื่อมต่อในระบบ (Connected Endpoint Fleet)
                    </h2>
                    <div class="hub-card-desc">
                        แสดงสถานะการเชื่อมต่อสแตนด์บายของคอมพิวเตอร์ในหน่วยงาน พร้อมปุ่มกดสั่งดึงข้อมูลสเปกรายเครื่อง
                    </div>
                </div>

                <div style="display: flex; gap: 10px; align-items: center;">
                    <div class="input-group" style="width: 260px;">
                        <input type="text" id="fleetSearchInput" class="form-control form-control-sm" placeholder="ค้นหา Hostname, IP, แผนก..." onkeyup="filterFleetTable()">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshFleetStatus()" title="รีเฟรชสถานะ">
                        <i class="bi bi-arrow-clockwise"></i> รีเฟรช
                    </button>
                </div>
            </div>

            <!-- Fleet Table -->
            <div class="fleet-table-container">
                <table class="fleet-table" id="fleetTable">
                    <thead>
                        <tr>
                            <th style="width: 130px;">สถานะเชื่อมต่อ</th>
                            <th>ชื่อเครื่อง (Hostname)</th>
                            <th>IP / MAC Address</th>
                            <th>หน่วยงาน / แผนก</th>
                            <th>สเปกล่าสุดในระบบ</th>
                            <th>เชื่อมต่อล่าสุด</th>
                            <th style="text-align: right; width: 180px;">สั่งการดึงข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agentFleet as $agent)
                        <tr class="fleet-row" data-search="{{ strtolower(($agent['hostname'] ?? '') . ' ' . ($agent['ip_address'] ?? '') . ' ' . ($agent['department_name'] ?? '')) }}">
                            <td>
                                @if(!empty($agent['is_online']))
                                    <span class="badge-status-online">
                                        <span class="hub-status-pulse" style="width: 6px; height: 6px;"></span>
                                        ออนไลน์
                                    </span>
                                @else
                                    <span class="badge-status-offline">
                                        <i class="bi bi-circle"></i>
                                        ออฟไลน์
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #0f172a;">{{ $agent['hostname'] ?: 'Unknown Host' }}</div>
                                @if(!empty($agent['hardware_id']))
                                    <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">UUID: {{ Str::limit($agent['hardware_id'], 18) }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="font-family: monospace; font-size: 12px; color: #334155;">{{ $agent['ip_address'] ?: '-' }}</div>
                                <div style="font-family: monospace; font-size: 11px; color: #94a3b8;">{{ $agent['mac_address'] ?: '' }}</div>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: #475569;">{{ $agent['department_name'] ?: 'ยังไม่ระบุแผนก' }}</span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: #334155;">
                                    <strong>CPU:</strong> {{ Str::limit($agent['cpu_model'] ?? '-', 24) }}
                                </div>
                                <div style="font-size: 11.5px; color: #64748b;">
                                    RAM {{ $agent['ram_capacity'] ? $agent['ram_capacity'] . ' GB' : '-' }} &bull; {{ $agent['os_name'] ?? '-' }}
                                </div>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: #64748b;">{{ $agent['last_seen'] ?? '-' }}</span>
                            </td>
                            <td style="text-align: right;">
                                @if(!empty($agent['audit_id']))
                                    <button type="button" class="btn-pull-single" onclick="triggerPullSingle({{ $agent['audit_id'] }}, '{{ $agent['hostname'] }}')">
                                        <i class="bi bi-broadcast"></i> ดึงสเปกเครื่องนี้
                                    </button>
                                @else
                                    <button type="button" class="btn-pull-single" onclick="triggerPullByHwid('{{ $agent['hardware_id'] }}', '{{ $agent['hostname'] }}')">
                                        <i class="bi bi-broadcast"></i> สั่งดึงสเปก
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px; color: #94a3b8;">
                                <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                ยังไม่มีข้อมูลคอมพิวเตอร์ที่เชื่อมต่อ ติดตั้งโปรแกรม THC Hardware Scanner บนเครื่องลูกข่ายเพื่อเริ่มตรวจนับ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Commands Queue Card -->
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <h2 class="hub-card-title">
                        <i class="bi bi-clock-history" style="color: #64748b;"></i>
                        ประวัติและคิวคำสั่งดึงข้อมูลล่าสุด (Recent Pull Commands Queue)
                    </h2>
                    <div class="hub-card-desc">
                        ติดตามสถานะคำสั่งดึงสเปกแบบเรียลไทม์ (รอดำเนินการ, กำลังสแกน, สแกนสำเร็จ)
                    </div>
                </div>
            </div>

            <div class="fleet-table-container">
                <table class="fleet-table">
                    <thead>
                        <tr>
                            <th>รหัสคำสั่ง (ID)</th>
                            <th>คำสั่ง</th>
                            <th>เครื่องเป้าหมาย (Target)</th>
                            <th>สถานะ</th>
                            <th>เวลาสั่งการ</th>
                            <th>เวลาที่เสร็จสิ้น</th>
                            <th>สรุปผลการทำงาน</th>
                        </tr>
                    </thead>
                    <tbody id="commandQueueBody">
                        @forelse($recentCommands as $cmd)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700;">#{{ $cmd->id }}</td>
                            <td>
                                <span class="badge bg-secondary" style="font-size: 11px;">{{ strtoupper($cmd->command) }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600;">{{ $cmd->target_hostname ?: ($cmd->targetAudit ? $cmd->targetAudit->hostname : 'All Machines') }}</div>
                                @if($cmd->target_ip)
                                    <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">IP: {{ $cmd->target_ip }}</div>
                                @endif
                            </td>
                            <td>
                                @if($cmd->status === 'completed')
                                    <span class="cmd-badge cmd-badge-completed"><i class="bi bi-check-circle me-1"></i> สำเร็จ</span>
                                @elseif($cmd->status === 'processing')
                                    <span class="cmd-badge cmd-badge-processing"><i class="bi bi-arrow-repeat me-1"></i> กำลังสแกน...</span>
                                @elseif($cmd->status === 'pending')
                                    <span class="cmd-badge cmd-badge-pending"><i class="bi bi-clock me-1"></i> รอดึงข้อมูล</span>
                                @else
                                    <span class="cmd-badge cmd-badge-failed"><i class="bi bi-x-circle me-1"></i> ล้มเหลว</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: #64748b;">{{ $cmd->created_at ? $cmd->created_at->format('d/m/Y H:i:s') : '-' }}</td>
                            <td style="font-size: 12px; color: #64748b;">{{ $cmd->completed_at ? \Carbon\Carbon::parse($cmd->completed_at)->format('d/m/Y H:i:s') : '-' }}</td>
                            <td style="font-size: 12px; color: #475569;">{{ Str::limit($cmd->result_summary ?: '-', 40) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                                ยังไม่มีประวัติคำสั่งดึงข้อมูลล่าสุด
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: DATA SCHEMA SPECIFICATION                               -->
    <!-- ============================================================== -->
    <div class="hub-tab-pane api-tab-pane" id="paneSchema">
        <div class="hub-card">
            <div class="hub-card-header">
                <div>
                    <h2 class="hub-card-title">
                        <i class="bi bi-table" style="color: #64748b;"></i>
                        พจนานุกรมข้อมูลสเปกคอมพิวเตอร์ (Table: it_hardware_audits)
                    </h2>
                    <div class="hub-card-desc">
                        ฟิลด์ข้อมูลฮาร์ดแวร์ที่โปรแกรม THC Hardware Scanner รวบรวมผ่าน WMI และจัดเก็บในระบบฐานข้อมูล
                    </div>
                </div>
            </div>

            <div class="fleet-table-container">
                <table class="schema-table">
                    <thead>
                        <tr>
                            <th>ชื่อฟิลด์ (Field Name)</th>
                            <th>ประเภทข้อมูล (Type)</th>
                            <th>จำเป็น (Required)</th>
                            <th>คำอธิบาย (Description)</th>
                            <th>ตัวอย่างข้อมูล (Sample Value)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="schema-field">hostname</td>
                            <td class="schema-type">VARCHAR(100)</td>
                            <td><span class="badge bg-danger">Required</span></td>
                            <td>ชื่อเครื่องคอมพิวเตอร์ในระบบเครือข่าย</td>
                            <td><code>OPD-DOC-01</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">serial_number</td>
                            <td class="schema-type">VARCHAR(100)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>หมายเลขซีเรียลนัมเบอร์ของตัวเครื่อง (BIOS Serial)</td>
                            <td><code>8CG1234567</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">hardware_id</td>
                            <td class="schema-type">VARCHAR(128)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>UUID เมนบอร์ด สำหรับระบุเอกลักษณ์เครื่อง</td>
                            <td><code>4C4C4544-0032-4710-8034-C8C04F343232</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">brand</td>
                            <td class="schema-type">VARCHAR(100)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ยี่ห้อผู้ผลิตคอมพิวเตอร์</td>
                            <td><code>Dell Inc. / HP / Lenovo</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">model</td>
                            <td class="schema-type">VARCHAR(150)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>รุ่นของเครื่องคอมพิวเตอร์</td>
                            <td><code>OptiPlex 7090 Tower</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">device_type_code</td>
                            <td class="schema-type">VARCHAR(20)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>รหัสประเภทอุปกรณ์ (PC, NB, AIO, SERVER)</td>
                            <td><code>PC</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">cpu_model</td>
                            <td class="schema-type">VARCHAR(150)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>รุ่นและสถาปัตยกรรมหน่วยประมวลผลกลาง</td>
                            <td><code>Intel Core i7-11700 @ 2.50GHz</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">cpu_speed</td>
                            <td class="schema-type">VARCHAR(60)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ความเร็วและจำนวนคอร์/เธรดของซีพียู</td>
                            <td><code>2.50 GHz (8 Cores / 16 Threads)</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">ram_capacity</td>
                            <td class="schema-type">INT</td>
                            <td><span class="badge bg-danger">Required</span></td>
                            <td>ขนาดหน่วยความจำหลัก แรม (หน่วย: Gigabyte)</td>
                            <td><code>16</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">ram_type</td>
                            <td class="schema-type">VARCHAR(30)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ประเภทเทคโนโลยีแรม (DDR4, DDR5)</td>
                            <td><code>DDR4</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">ram_bus</td>
                            <td class="schema-type">VARCHAR(30)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ความเร็วบัสของแรม (หน่วย: MHz)</td>
                            <td><code>3200 MHz</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">storage_capacity</td>
                            <td class="schema-type">VARCHAR(50)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ความจุฮาร์ดดิสก์หรือสตอเรจหลัก</td>
                            <td><code>512 GB</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">storage_type</td>
                            <td class="schema-type">VARCHAR(50)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ประเภทสื่อบันทึกข้อมูล (SSD NVMe, SSD SATA, HDD)</td>
                            <td><code>SSD NVMe M.2</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">os_name</td>
                            <td class="schema-type">VARCHAR(150)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ระบบปฏิบัติการและรุ่น Edition</td>
                            <td><code>Windows 11 Pro 64-bit</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">gpu_model</td>
                            <td class="schema-type">VARCHAR(150)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>การ์ดแสดงผลกราฟิก (GPU)</td>
                            <td><code>Intel UHD Graphics 750</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">ip_address</td>
                            <td class="schema-type">VARCHAR(45)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>หมายเลข IP แอดเดรสในระบบเครือข่าย</td>
                            <td><code>192.168.10.45</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">mac_address</td>
                            <td class="schema-type">VARCHAR(50)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>หมายเลขฮาร์ดแวร์ Physical MAC Address</td>
                            <td><code>00:1A:2B:3C:4D:5E</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">department_name</td>
                            <td class="schema-type">VARCHAR(150)</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>ชื่อแผนกหรือหน่วยงานที่ประจำการ</td>
                            <td><code>งานผู้ป่วยนอก (OPD)</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">fiscal_year</td>
                            <td class="schema-type">INT</td>
                            <td><span class="badge bg-danger">Required</span></td>
                            <td>ปีงบประมาณที่จัดเก็บข้อมูลสเปก (พ.ศ.)</td>
                            <td><code>{{ $fiscalYear }}</code></td>
                        </tr>
                        <tr>
                            <td class="schema-field">command_id</td>
                            <td class="schema-type">BIGINT</td>
                            <td><span class="badge bg-secondary">Optional</span></td>
                            <td>รหัสคำสั่งดึงข้อมูลที่แอดมินสั่งการ (ถ้ามี)</td>
                            <td><code>1042</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // =========================================================================
    // TAB NAVIGATION CONTROLLER (3 BALANCED TABS)
    // =========================================================================
    function switchApiTab(tabKey) {
        // Map alias keys
        let targetKey = tabKey;
        if (tabKey === 'installer' || tabKey === 'scanner' || tabKey === 'standalone') {
            targetKey = 'standalone';
        } else if (tabKey === 'pull' || tabKey === 'remotepull' || tabKey === 'fleet') {
            targetKey = 'remotepull';
        } else if (tabKey === 'schema') {
            targetKey = 'schema';
        }

        // Deactivate all buttons
        const allBtns = ['btnTabStandalone', 'btnTabRemotePull', 'btnTabSchema'];
        allBtns.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.classList.remove('active');
        });

        // Hide all panes
        const allPanes = ['paneStandalone', 'paneRemotePull', 'paneSchema'];
        allPanes.forEach(id => {
            const pane = document.getElementById(id);
            if (pane) pane.classList.remove('active');
        });

        // Activate matching button and pane
        if (targetKey === 'standalone') {
            const btn = document.getElementById('btnTabStandalone');
            const pane = document.getElementById('paneStandalone');
            if (btn) btn.classList.add('active');
            if (pane) pane.classList.add('active');
        } else if (targetKey === 'remotepull') {
            const btn = document.getElementById('btnTabRemotePull');
            const pane = document.getElementById('paneRemotePull');
            if (btn) btn.classList.add('active');
            if (pane) pane.classList.add('active');
        } else if (targetKey === 'schema') {
            const btn = document.getElementById('btnTabSchema');
            const pane = document.getElementById('paneSchema');
            if (btn) btn.classList.add('active');
            if (pane) pane.classList.add('active');
        }

        history.replaceState(null, null, `#${targetKey}`);
    }

    // =========================================================================
    // ADMIN ON-DEMAND REMOTE PULL ACTIONS
    // =========================================================================

    // 1. Trigger Pull for ALL connected machines
    function triggerPullAllFleet() {
        const btn = document.getElementById('btnPullAllMachines');
        const originalHtml = btn.innerHTML;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันสั่งดึงข้อมูลสเปกทุกเครื่อง?',
                text: 'ระบบจะส่งสัญญาณคำสั่งสแกนไปยังเครื่องคอมพิวเตอร์ลูกข่ายทุกเครื่องที่กำลังเชื่อมต่อเพื่อดึงสเปกเข้าสู่ปีงบประมาณ {{ $fiscalYear }}',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-broadcast"></i> สั่งดึงข้อมูลทันที',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    dispatchPullAllRequest(btn, originalHtml);
                }
            });
        } else {
            if (confirm('ยืนยันสั่งดึงข้อมูลสเปกทุกเครื่องในระบบ?')) {
                dispatchPullAllRequest(btn, originalHtml);
            }
        }
    }

    function dispatchPullAllRequest(btn, originalHtml) {
        btn.disabled = true;
        btn.innerHTML = '<span class="btn-spinner" style="margin-right: 8px;"></span> กำลังส่งคำสั่งดึงสเปก...';

        fetch("{{ route('hardware-audits.trigger-scan-all') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ fiscal_year: {{ $fiscalYear }} })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งคำสั่งดึงข้อมูลสำเร็จ!',
                        text: data.message || `ส่งคำสั่งสแกนไปยัง ${data.dispatched_count || 'ทุก'} เครื่องเรียบร้อยแล้ว`,
                        confirmButtonColor: '#0d9488'
                    });
                } else {
                    alert('ส่งคำสั่งดึงข้อมูลสำเร็จ!');
                }
                setTimeout(() => { refreshFleetStatus(); }, 2000);
            } else {
                throw new Error(data.message || 'ไม่สามารถส่งคำสั่งได้');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'แจ้งเตือน',
                    text: err.message,
                    confirmButtonColor: '#0d9488'
                });
            } else {
                alert(err.message);
            }
        });
    }

    // 2. Trigger Pull for Single Machine by Audit ID
    function triggerPullSingle(auditId, hostname) {
        const url = `/hardware-audits/${auditId}/trigger-scan`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `สั่งดึงสเปกเครื่อง ${hostname} แล้ว`,
                        showConfirmButton: false,
                        timer: 2500
                    });
                } else {
                    alert(`สั่งดึงสเปกเครื่อง ${hostname} แล้ว`);
                }
                setTimeout(() => { refreshFleetStatus(); }, 2000);
            } else {
                throw new Error(data.message || 'ส่งคำสั่งไม่สำเร็จ');
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'เกิดข้อผิดพลาด',
                    text: err.message
                });
            } else {
                alert(err.message);
            }
        });
    }

    // 3. Trigger Pull by Hardware ID
    function triggerPullByHwid(hwid, hostname) {
        fetch("{{ route('hardware-audits.trigger-scan-batch') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                targets: [{ hardware_id: hwid, hostname: hostname }],
                fiscal_year: {{ $fiscalYear }}
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: `สั่งดึงสเปกเครื่อง ${hostname} แล้ว`,
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
                setTimeout(() => { refreshFleetStatus(); }, 2000);
            } else {
                throw new Error(data.message || 'ส่งคำสั่งไม่สำเร็จ');
            }
        })
        .catch(err => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: err.message });
            }
        });
    }

    // 4. Refresh Fleet & Online Status
    function refreshFleetStatus() {
        fetch("{{ route('hardware-audits.online-status') }}")
        .then(res => res.json())
        .then(data => {
            if (data.online_count !== undefined) {
                const el = document.getElementById('statOnlineCount');
                if (el) el.innerText = `${data.online_count} เครื่อง`;
                const tabBadge = document.getElementById('tabOnlineBadge');
                if (tabBadge) tabBadge.innerText = `${data.online_count} เครื่อง`;
            }
        })
        .catch(() => {});
    }

    // 5. Filter Fleet Table
    function filterFleetTable() {
        const query = (document.getElementById('fleetSearchInput').value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#fleetTable tbody tr.fleet-row');
        rows.forEach(r => {
            const text = (r.getAttribute('data-search') || '').toLowerCase();
            r.style.display = text.includes(query) ? '' : 'none';
        });
    }

    // 6. Copy Checksum Hash
    function copyHash() {
        const hash = document.getElementById('hashText').innerText.trim();
        navigator.clipboard.writeText(hash).then(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'คัดลอกค่า SHA-256 Checksum แล้ว',
                    showConfirmButton: false,
                    timer: 1500
                });
            } else {
                alert('คัดลอกค่า SHA-256 Checksum แล้ว');
            }
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        // Check hash in URL for tab selection
        if (window.location.hash) {
            const h = window.location.hash.substring(1).toLowerCase();
            if (['standalone', 'remotepull', 'fleet', 'pull', 'schema', 'installer'].includes(h)) {
                switchApiTab(h);
            }
        }

        // Auto poll fleet status every 15 seconds
        setInterval(refreshFleetStatus, 15000);
    });
</script>
@endpush
