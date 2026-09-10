@extends('layouts.app')

@section('title', 'คลังคอมพิวเตอร์และครุภัณฑ์ IT')
@section('page-title', 'คลังคอมพิวเตอร์และครุภัณฑ์ IT (IT Asset Inventory)')
@section('breadcrumb', 'ระบบพัสดุและคลัง / ทะเบียนครุภัณฑ์ IT')

@section('topbar-actions')
<a href="{{ route('reports.export', 'assets') }}" class="topbar-btn" title="ส่งออกข้อมูลครุภัณฑ์เป็น CSV">
    <i class="bi bi-file-earmark-excel text-success"></i>
    <span>Export CSV</span>
</a>
@if(auth()->user()->isAdmin())
<button type="button" class="topbar-btn" onclick="openImportModal()" title="นำเข้าข้อมูลครุภัณฑ์จากไฟล์ CSV">
    <i class="bi bi-file-earmark-arrow-up text-primary"></i>
    <span>Import CSV</span>
</button>
@endif
@if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
<a href="{{ route('assets.create') }}" class="topbar-btn topbar-btn-primary" title="เพิ่มครุภัณฑ์ใหม่เข้าสู่ระบบ">
    <i class="bi bi-plus-circle"></i>
    <span>เพิ่มครุภัณฑ์ใหม่</span>
</a>
@endif
@endsection

@push('styles')
<style>
    /* Hero Header Banner */
    .asset-hero-banner {
        background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
        border-radius: 16px;
        padding: 24px 28px;
        color: #ffffff;
        margin-bottom: 24px;
        box-shadow: 0 8px 24px rgba(13, 148, 136, 0.18);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
        position: relative;
        overflow: hidden;
    }

    .asset-hero-banner::after {
        content: '';
        position: absolute;
        right: -30px;
        bottom: -40px;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .asset-hero-content {
        display: flex;
        align-items: center;
        gap: 18px;
        z-index: 1;
    }

    .asset-hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.18);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
    }

    .asset-hero-text h1 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 4px 0;
        letter-spacing: -0.3px;
        color: #ffffff;
    }

    .asset-hero-text p {
        font-size: 13.5px;
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        line-height: 1.4;
    }

    .asset-hero-actions {
        display: flex;
        gap: 10px;
        z-index: 1;
    }

    .btn-hero-white {
        background: #ffffff;
        color: #0f766e;
        border: none;
        font-weight: 600;
        padding: 9px 18px;
        border-radius: 10px;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: all 0.2s ease;
    }

    .btn-hero-white:hover {
        background: #f0fdfa;
        color: #0d9488;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }

    .btn-hero-ghost {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-weight: 500;
        padding: 9px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        backdrop-filter: blur(4px);
        transition: all 0.2s ease;
    }

    .btn-hero-ghost:hover {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* KPI Status Grid */
    .status-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 14px;
        margin-bottom: 24px;
    }

    .status-kpi-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 16px 18px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        color: inherit;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .status-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
    }

    .status-kpi-card.active-filter {
        border-width: 2px;
        box-shadow: 0 6px 20px rgba(13, 148, 136, 0.12);
    }

    .status-kpi-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .status-kpi-number {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    .status-kpi-label {
        font-size: 13px;
        font-weight: 500;
        margin-top: 3px;
        color: #64748b;
    }

    /* Filter Card */
    .filter-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--border);
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .filter-form-grid {
        display: grid;
        grid-template-columns: 2.2fr 1.3fr 1.3fr 1.2fr auto;
        gap: 14px;
        align-items: end;
    }

    @media (max-width: 1080px) {
        .filter-form-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .filter-form-grid {
            grid-template-columns: 1fr;
        }
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-label {
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-input-wrap {
        position: relative;
    }

    .filter-input-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
    }

    .filter-input-wrap .form-control {
        padding-left: 36px;
        height: 40px;
        font-size: 13.5px;
        border-radius: 8px;
    }

    .filter-select {
        height: 40px;
        font-size: 13.5px;
        border-radius: 8px;
    }

    /* Main Data Card & Table */
    .assets-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .assets-card-header {
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        background: #ffffff;
    }

    .assets-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
    }

    .assets-count-badge {
        font-size: 12.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
    }

    /* Table Typography & Layout */
    .assets-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .assets-table th {
        background: #f8fafc;
        color: #475569;
        font-size: 13px;
        font-weight: 600;
        padding: 13px 16px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }

    .assets-table td {
        padding: 15px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
        color: #334155;
    }

    .assets-table tr:hover td {
        background-color: #f8fafc;
    }

    .asset-code-link {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-weight: 700;
        font-size: 13.5px;
        color: #0284c7;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 8px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .asset-code-link:hover {
        background: #e0f2fe;
        color: #0369a1;
        border-color: #7dd3fc;
    }

    .asset-sn-text {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 11.5px;
        color: #64748b;
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .asset-name-text {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 3px;
    }

    .asset-name-text a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .asset-name-text a:hover {
        color: var(--primary);
    }

    .asset-sub-text {
        font-size: 12.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .asset-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 9px;
        border-radius: 6px;
        font-size: 12.5px;
        font-weight: 500;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }

    .asset-dept-title {
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .asset-location-sub {
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .asset-custodian {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: #334155;
    }

    .asset-ip-badge {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #0284c7;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    /* Action Buttons */
    .action-btn-group {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .action-icon-btn {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 13.5px;
        border: 1px solid var(--border);
        background: #ffffff;
        color: #475569;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .action-icon-btn:hover {
        background: #f1f5f9;
        color: var(--primary);
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    .action-icon-btn-danger {
        color: #dc2626;
        background: #fff5f5;
        border-color: #fed7d7;
    }

    .action-icon-btn-danger:hover {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fca5a5;
    }

    /* Modal Backdrop & Dialog */
    .custom-modal-backdrop {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .custom-modal-dialog {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-width: 520px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        animation: modalScaleIn 0.22s ease-out;
    }

    @keyframes modalScaleIn {
        from {
            opacity: 0;
            transform: scale(0.94) translateY(10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-hero-header {
        background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
        padding: 20px 24px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-hero-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .modal-icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .modal-close-btn {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.8);
        font-size: 22px;
        cursor: pointer;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.2s;
    }

    .modal-close-btn:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.15);
    }

    .modal-body-content {
        padding: 24px;
    }

    .modal-asset-card {
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 16px;
        margin-bottom: 20px;
    }

    .modal-asset-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .modal-code-chip {
        font-family: 'SFMono-Regular', Consolas, monospace;
        font-weight: 700;
        font-size: 14px;
        color: #0284c7;
        background: #e0f2fe;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #bae6fd;
    }

    .modal-info-row {
        display: flex;
        font-size: 13px;
        color: #475569;
        margin-bottom: 6px;
        gap: 8px;
    }

    .modal-info-row strong {
        color: #1e293b;
        min-width: 90px;
    }

    .modal-alert-box {
        border-radius: 10px;
        padding: 14px 16px;
        font-size: 13.5px;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    .modal-alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .modal-alert-warning {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    /* Hardware Specs Explorer Hub */
    .hw-explorer-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        margin-bottom: 22px;
        overflow: hidden;
        transition: all 0.25s ease;
    }

    .hw-explorer-header {
        padding: 14px 20px;
        background: linear-gradient(to right, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .hw-explorer-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
    }

    .hw-explorer-body {
        padding: 16px 20px;
    }

    .hw-filter-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .hw-filter-row:last-child {
        margin-bottom: 0;
    }

    .hw-row-label {
        width: 135px;
        flex-shrink: 0;
        font-size: 12.5px;
        font-weight: 600;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .hw-chips-wrap {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        flex: 1;
    }

    .hw-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: 12.5px;
        font-weight: 500;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #334155;
        text-decoration: none;
        transition: all 0.18s ease;
        user-select: none;
        cursor: pointer;
    }

    .hw-chip:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
    }

    .hw-chip-count {
        background: #e2e8f0;
        color: #475569;
        padding: 1px 7px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        transition: all 0.18s ease;
    }

    .hw-chip.active {
        background: #0284c7;
        border-color: #0284c7;
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 3px 10px rgba(2, 132, 199, 0.28);
    }

    .hw-chip.active .hw-chip-count {
        background: rgba(255, 255, 255, 0.28);
        color: #ffffff;
    }

    .hw-chip-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 2px;
        font-size: 12px;
        opacity: 0.8;
    }

    .hw-chip-remove:hover {
        opacity: 1;
    }

    /* Active Spec Notice Banner */
    .hw-active-banner {
        background: linear-gradient(135deg, #e0f2fe 0%, #ede9fe 100%);
        border: 1px solid #7dd3fc;
        border-radius: 10px;
        padding: 10px 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 13px;
        color: #0369a1;
    }

    .hw-active-tags {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .hw-active-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #0284c7;
        color: #ffffff;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .hw-active-pill a {
        color: #ffffff;
        opacity: 0.85;
        text-decoration: none;
    }

    .hw-active-pill a:hover {
        opacity: 1;
    }

    /* Table Micro Spec Badges */
    .asset-spec-chips-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 5px;
    }

    .spec-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .spec-badge:hover {
        transform: translateY(-1px);
    }

    .spec-badge-cpu {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }
    .spec-badge-cpu:hover {
        background: #dbeafe;
        color: #1e40af;
    }

    .spec-badge-ram {
        background: #ecfdf5;
        color: #047857;
        border-color: #a7f3d0;
    }
    .spec-badge-ram:hover {
        background: #d1fae5;
        color: #065f46;
    }

    .spec-badge-storage {
        background: #fdf4ff;
        color: #86198f;
        border-color: #f5d0fe;
    }
    .spec-badge-storage:hover {
        background: #fae8ff;
        color: #701a75;
    }

    .spec-badge-os {
        background: #f8fafc;
        color: #0f766e;
        border-color: #99f6e4;
    }
    .spec-badge-os:hover {
        background: #ccfbf1;
        color: #0d9488;
    }
</style>
@endpush

@section('content')
<!-- Hero Header Banner -->
<div class="asset-hero-banner">
    <div class="asset-hero-content">
        <div class="asset-hero-icon">
            <i class="bi bi-pc-display-horizontal"></i>
        </div>
        <div class="asset-hero-text">
            <h1>คลังคอมพิวเตอร์และครุภัณฑ์ IT</h1>
            <p>โรงพยาบาลทุ่งหัวช้าง จ.ลำพูน — ทะเบียนและติดตามสถานะครุภัณฑ์คอมพิวเตอร์และระบบเครือข่าย</p>
        </div>
    </div>
    <div class="asset-hero-actions">
        <a href="{{ route('reports.export', 'assets') }}" class="btn-hero-ghost" title="ส่งออกไฟล์ Excel/CSV">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Export CSV</span>
        </a>
        @if(auth()->user()->isAdmin())
        <button type="button" onclick="openImportModal()" class="btn-hero-ghost" style="cursor: pointer;" title="นำเข้าข้อมูลครุภัณฑ์จากไฟล์ CSV (เฉพาะ Admin)">
            <i class="bi bi-file-earmark-arrow-up"></i>
            <span>Import CSV</span>
        </button>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
        <a href="{{ route('assets.create') }}" class="btn-hero-white" title="ลงทะเบียนครุภัณฑ์ใหม่">
            <i class="bi bi-plus-circle-fill"></i>
            <span>เพิ่มครุภัณฑ์ใหม่</span>
        </a>
        @endif
    </div>
</div>

<!-- Scoping Notice for Regular Users -->
@if(auth()->user()->isUser())
<div style="padding: 12px 20px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; color: #065f46; font-size: 13.5px; display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
    <i class="bi bi-info-circle-fill text-success" style="font-size: 16px;"></i>
    <span>ระบบแสดงเฉพาะครุภัณฑ์คอมพิวเตอร์ประจำ <strong>{{ auth()->user()->department->name ?? 'แผนกของท่าน' }}</strong></span>
</div>
@endif

<!-- Top Status KPI Cards -->
<div class="status-kpi-grid">
    {{-- Total --}}
    <a href="{{ route('assets.index', array_merge(request()->except(['status', 'page']))) }}" 
       class="status-kpi-card {{ !request('status') ? 'active-filter' : '' }}"
       style="{{ !request('status') ? 'border-color: #0284c7; background: #f0f9ff;' : '' }}">
        <div class="status-kpi-icon" style="background: #e0f2fe; color: #0284c7;">
            <i class="bi bi-grid-fill"></i>
        </div>
        <div>
            <div class="status-kpi-number" style="color: #0f172a;">{{ number_format($statusCounts['total']) }}</div>
            <div class="status-kpi-label">ครุภัณฑ์ทั้งหมด</div>
        </div>
    </a>

    {{-- Active --}}
    <a href="{{ route('assets.index', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}" 
       class="status-kpi-card {{ request('status') == 'active' ? 'active-filter' : '' }}"
       style="{{ request('status') == 'active' ? 'border-color: #10b981; background: #ecfdf5;' : '' }}">
        <div class="status-kpi-icon" style="background: #d1fae5; color: #059669;">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <div>
            <div class="status-kpi-number" style="color: #065f46;">{{ number_format($statusCounts['active']) }}</div>
            <div class="status-kpi-label" style="color: #047857;">ใช้งานปกติ</div>
        </div>
    </a>

    {{-- Spare --}}
    <a href="{{ route('assets.index', array_merge(request()->except(['status', 'page']), ['status' => 'spare'])) }}" 
       class="status-kpi-card {{ request('status') == 'spare' ? 'active-filter' : '' }}"
       style="{{ request('status') == 'spare' ? 'border-color: #06b6d4; background: #ecfeff;' : '' }}">
        <div class="status-kpi-icon" style="background: #cffafe; color: #0891b2;">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <div class="status-kpi-number" style="color: #0e7490;">{{ number_format($statusCounts['spare']) }}</div>
            <div class="status-kpi-label" style="color: #0e7490;">เครื่องสำรอง</div>
        </div>
    </a>

    {{-- Repairing --}}
    <a href="{{ route('assets.index', array_merge(request()->except(['status', 'page']), ['status' => 'repairing'])) }}" 
       class="status-kpi-card {{ request('status') == 'repairing' ? 'active-filter' : '' }}"
       style="{{ request('status') == 'repairing' ? 'border-color: #f59e0b; background: #fffbeb;' : '' }}">
        <div class="status-kpi-icon" style="background: #fef3c7; color: #d97706;">
            <i class="bi bi-wrench-adjustable-circle-fill"></i>
        </div>
        <div>
            <div class="status-kpi-number" style="color: #b45309;">{{ number_format($statusCounts['repairing']) }}</div>
            <div class="status-kpi-label" style="color: #b45309;">กำลังส่งซ่อม</div>
        </div>
    </a>

    {{-- Broken --}}
    <a href="{{ route('assets.index', array_merge(request()->except(['status', 'page']), ['status' => 'broken'])) }}" 
       class="status-kpi-card {{ request('status') == 'broken' ? 'active-filter' : '' }}"
       style="{{ request('status') == 'broken' ? 'border-color: #ef4444; background: #fef2f2;' : '' }}">
        <div class="status-kpi-icon" style="background: #fee2e2; color: #dc2626;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div>
            <div class="status-kpi-number" style="color: #b91c1c;">{{ number_format($statusCounts['broken']) }}</div>
            <div class="status-kpi-label" style="color: #b91c1c;">ชำรุดรอซ่อม</div>
        </div>
    </a>

    {{-- Disposed --}}
    <a href="{{ route('assets.index', array_merge(request()->except(['status', 'page']), ['status' => 'disposed'])) }}" 
       class="status-kpi-card {{ request('status') == 'disposed' ? 'active-filter' : '' }}"
       style="{{ request('status') == 'disposed' ? 'border-color: #64748b; background: #f8fafc;' : '' }}">
        <div class="status-kpi-icon" style="background: #e2e8f0; color: #475569;">
            <i class="bi bi-archive-fill"></i>
        </div>
        <div>
            <div class="status-kpi-number" style="color: #475569;">{{ number_format($statusCounts['disposed']) }}</div>
            <div class="status-kpi-label" style="color: #475569;">รอจำหน่าย</div>
        </div>
    </a>
</div>

{{-- Active Hardware Filters Notice Bar --}}
@if(request()->hasAny(['cpu', 'ram_type', 'ram_capacity', 'os', 'storage_type', 'storage_capacity']))
<div class="hw-active-banner">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <i class="bi bi-funnel-fill text-primary" style="font-size: 15px;"></i>
        <span style="font-weight: 600;">กำลังกรองตามสเปกเครื่อง:</span>
        <div class="hw-active-tags">
            @if(request('cpu'))
                <span class="hw-active-pill">
                    <i class="bi bi-cpu"></i> CPU: {{ request('cpu') }}
                    <a href="{{ route('assets.index', request()->except('cpu', 'page')) }}" title="ลบตัวกรอง CPU">✕</a>
                </span>
            @endif
            @if(request('ram_type'))
                <span class="hw-active-pill">
                    <i class="bi bi-memory"></i> RAM Type: {{ request('ram_type') }}
                    <a href="{{ route('assets.index', request()->except('ram_type', 'page')) }}" title="ลบตัวกรอง RAM Type">✕</a>
                </span>
            @endif
            @if(request('ram_capacity'))
                <span class="hw-active-pill">
                    <i class="bi bi-memory"></i> RAM: {{ request('ram_capacity') }} GB
                    <a href="{{ route('assets.index', request()->except('ram_capacity', 'page')) }}" title="ลบตัวกรอง RAM">✕</a>
                </span>
            @endif
            @if(request('os'))
                <span class="hw-active-pill">
                    <i class="bi bi-windows"></i> OS: {{ request('os') }}
                    <a href="{{ route('assets.index', request()->except('os', 'page')) }}" title="ลบตัวกรอง OS">✕</a>
                </span>
            @endif
            @if(request('storage_type'))
                <span class="hw-active-pill">
                    <i class="bi bi-device-hdd"></i> Storage: {{ request('storage_type') }}
                    <a href="{{ route('assets.index', request()->except('storage_type', 'page')) }}" title="ลบตัวกรอง Storage">✕</a>
                </span>
            @endif
        </div>
        <span style="font-weight: 700; color: #0f172a; margin-left: 6px;">(พบ {{ number_format($assets->total()) }} เครื่อง)</span>
    </div>
    <a href="{{ route('assets.index', request()->except(['cpu', 'ram_type', 'ram_capacity', 'os', 'storage_type', 'storage_capacity', 'page'])) }}" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-size: 12px; padding: 4px 12px; background: #ffffff;">
        <i class="bi bi-x-circle me-1"></i> ล้างตัวกรองสเปกทั้งหมด
    </a>
</div>
@endif

{{-- Hardware Specs Explorer Hub --}}
<div class="hw-explorer-card">
    <div class="hw-explorer-header">
        <div class="hw-explorer-title">
            <i class="bi bi-cpu-fill text-primary" style="font-size: 19px;"></i>
            <span>สเปกฮาร์ดแวร์ & ตัวกรองอัจฉริยะ (Hardware Specs Explorer)</span>
            <span class="badge bg-primary" style="font-size: 11.5px; padding: 3px 9px; border-radius: 999px;">
                {{ $hardwareStats['total_computers'] }} เครื่อง
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-light border" onclick="toggleAdvancedFilter()" style="font-size: 12.5px; border-radius: 8px;">
                <i class="bi bi-sliders me-1 text-primary"></i> <span id="advFilterToggleText">{{ request()->hasAny(['cpu', 'ram_type', 'ram_capacity', 'os', 'storage_type', 'storage_capacity']) ? 'ซ่อนฟอร์มสเปกละเอียด' : 'ฟอร์มค้นหาสเปกละเอียด' }}</span>
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openFleetModal()" style="font-size: 12.5px; border-radius: 8px;">
                <i class="bi bi-bar-chart-fill me-1"></i> สรุปภาพรวมสเปก รพ.
            </button>
        </div>
    </div>

    <div class="hw-explorer-body">
        {{-- Row 1: RAM Type (DDR4 / DDR5) --}}
        <div class="hw-filter-row">
            <div class="hw-row-label">
                <i class="bi bi-memory text-success"></i> ชนิด RAM:
            </div>
            <div class="hw-chips-wrap">
                @foreach($hardwareStats['ram_types'] as $type => $count)
                    @php
                        $isActive = request('ram_type') == $type;
                        $queryParam = $isActive ? request()->except('ram_type', 'page') : array_merge(request()->query(), ['ram_type' => $type, 'page' => 1]);
                    @endphp
                    <a href="{{ route('assets.index', $queryParam) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่องที่ใช้แรม {{ $type }}">
                        <span>{{ $type }}</span>
                        <span class="hw-chip-count">{{ $count }} เครื่อง</span>
                        @if($isActive)
                            <span class="hw-chip-remove" title="ยกเลิก">✕</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Row 2: Windows / OS --}}
        <div class="hw-filter-row">
            <div class="hw-row-label">
                <i class="bi bi-windows text-info"></i> ระบบ OS:
            </div>
            <div class="hw-chips-wrap">
                @foreach($hardwareStats['os_list'] as $os => $count)
                    @php
                        $isActive = request('os') == $os;
                        $queryParam = $isActive ? request()->except('os', 'page') : array_merge(request()->query(), ['os' => $os, 'page' => 1]);
                    @endphp
                    <a href="{{ route('assets.index', $queryParam) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่องที่ใช้ {{ $os }}">
                        <span>{{ $os }}</span>
                        <span class="hw-chip-count">{{ $count }} เครื่อง</span>
                        @if($isActive)
                            <span class="hw-chip-remove">✕</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Row 3: CPU Processors --}}
        <div class="hw-filter-row">
            <div class="hw-row-label">
                <i class="bi bi-cpu text-primary"></i> ซีพียู (CPU):
            </div>
            <div class="hw-chips-wrap">
                @if($hardwareStats['cpu_families']['i5'] > 0)
                    @php $isActive = request('cpu') == 'i5'; @endphp
                    <a href="{{ route('assets.index', $isActive ? request()->except('cpu', 'page') : array_merge(request()->query(), ['cpu' => 'i5', 'page' => 1])) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่อง Intel Core i5">
                        <span>Core i5</span>
                        <span class="hw-chip-count">{{ $hardwareStats['cpu_families']['i5'] }} เครื่อง</span>
                        @if($isActive)<span class="hw-chip-remove">✕</span>@endif
                    </a>
                @endif
                @if($hardwareStats['cpu_families']['i3'] > 0)
                    @php $isActive = request('cpu') == 'i3'; @endphp
                    <a href="{{ route('assets.index', $isActive ? request()->except('cpu', 'page') : array_merge(request()->query(), ['cpu' => 'i3', 'page' => 1])) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่อง Intel Core i3">
                        <span>Core i3</span>
                        <span class="hw-chip-count">{{ $hardwareStats['cpu_families']['i3'] }} เครื่อง</span>
                        @if($isActive)<span class="hw-chip-remove">✕</span>@endif
                    </a>
                @endif
                @if($hardwareStats['cpu_families']['i7'] > 0)
                    @php $isActive = request('cpu') == 'i7'; @endphp
                    <a href="{{ route('assets.index', $isActive ? request()->except('cpu', 'page') : array_merge(request()->query(), ['cpu' => 'i7', 'page' => 1])) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่อง Intel Core i7">
                        <span>Core i7</span>
                        <span class="hw-chip-count">{{ $hardwareStats['cpu_families']['i7'] }} เครื่อง</span>
                        @if($isActive)<span class="hw-chip-remove">✕</span>@endif
                    </a>
                @endif
                @if($hardwareStats['cpu_families']['ryzen'] > 0)
                    @php $isActive = request('cpu') == 'Ryzen'; @endphp
                    <a href="{{ route('assets.index', $isActive ? request()->except('cpu', 'page') : array_merge(request()->query(), ['cpu' => 'Ryzen', 'page' => 1])) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่อง AMD Ryzen">
                        <span>AMD Ryzen</span>
                        <span class="hw-chip-count">{{ $hardwareStats['cpu_families']['ryzen'] }} เครื่อง</span>
                        @if($isActive)<span class="hw-chip-remove">✕</span>@endif
                    </a>
                @endif
            </div>
        </div>

        {{-- Row 4: RAM Capacity --}}
        <div class="hw-filter-row">
            <div class="hw-row-label">
                <i class="bi bi-speedometer2 text-warning"></i> ความจุ RAM:
            </div>
            <div class="hw-chips-wrap">
                @foreach($hardwareStats['ram_capacities'] as $cap => $count)
                    @php
                        $isActive = request('ram_capacity') == $cap;
                        $queryParam = $isActive ? request()->except('ram_capacity', 'page') : array_merge(request()->query(), ['ram_capacity' => $cap, 'page' => 1]);
                    @endphp
                    <a href="{{ route('assets.index', $queryParam) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่อง RAM {{ $cap }} GB">
                        <span>{{ $cap }} GB</span>
                        <span class="hw-chip-count">{{ $count }} เครื่อง</span>
                        @if($isActive)
                            <span class="hw-chip-remove">✕</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Row 5: Storage Type --}}
        <div class="hw-filter-row">
            <div class="hw-row-label">
                <i class="bi bi-device-hdd text-secondary"></i> ไดรฟ์เก็บข้อมูล:
            </div>
            <div class="hw-chips-wrap">
                @foreach($hardwareStats['storage_types'] as $st => $count)
                    @php
                        $isActive = request('storage_type') == $st;
                        $queryParam = $isActive ? request()->except('storage_type', 'page') : array_merge(request()->query(), ['storage_type' => $st, 'page' => 1]);
                    @endphp
                    <a href="{{ route('assets.index', $queryParam) }}" class="hw-chip {{ $isActive ? 'active' : '' }}" title="คลิกเพื่อแสดงเครื่อง {{ $st }}">
                        <span>{{ $st }}</span>
                        <span class="hw-chip-count">{{ $count }} เครื่อง</span>
                        @if($isActive)
                            <span class="hw-chip-remove">✕</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Expandable Advanced Specs Filter Form --}}
    <div id="advancedSpecsFilterForm" style="display: {{ request()->hasAny(['cpu', 'ram_type', 'ram_capacity', 'os', 'storage_type', 'storage_capacity']) ? 'block' : 'none' }}; background: #f8fafc; border-top: 1px dashed #cbd5e1; padding: 18px 20px;">
        <form action="{{ route('assets.index') }}" method="GET">
            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
            @if(request('device_type_id')) <input type="hidden" name="device_type_id" value="{{ request('device_type_id') }}"> @endif
            @if(request('department_id')) <input type="hidden" name="department_id" value="{{ request('department_id') }}"> @endif
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-sliders text-primary"></i> ค้นหาระบุสเปกฮาร์ดแวร์แบบละเอียด
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end;">
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">CPU / หน่วยประมวลผล</label>
                    <input type="text" name="cpu" class="form-control form-control-sm" placeholder="เช่น i5, i7, 12500, Ryzen" value="{{ request('cpu') }}">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">ประเภท RAM</label>
                    <select name="ram_type" class="form-select form-select-sm">
                        <option value="">-- ทุกประเภท RAM --</option>
                        <option value="DDR5" {{ request('ram_type') == 'DDR5' ? 'selected' : '' }}>DDR5</option>
                        <option value="DDR4" {{ request('ram_type') == 'DDR4' ? 'selected' : '' }}>DDR4</option>
                        <option value="DDR3" {{ request('ram_type') == 'DDR3' ? 'selected' : '' }}>DDR3</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">ความจุ RAM</label>
                    <select name="ram_capacity" class="form-select form-select-sm">
                        <option value="">-- ทุกความจุ --</option>
                        <option value="4" {{ request('ram_capacity') == 4 ? 'selected' : '' }}>4 GB</option>
                        <option value="8" {{ request('ram_capacity') == 8 ? 'selected' : '' }}>8 GB</option>
                        <option value="16" {{ request('ram_capacity') == 16 ? 'selected' : '' }}>16 GB</option>
                        <option value="32" {{ request('ram_capacity') == 32 ? 'selected' : '' }}>32 GB</option>
                        <option value="64" {{ request('ram_capacity') == 64 ? 'selected' : '' }}>64 GB</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">ระบบปฏิบัติการ (OS)</label>
                    <select name="os" class="form-select form-select-sm">
                        <option value="">-- ทุกระบบ OS --</option>
                        <option value="Windows 11" {{ str_contains(request('os', ''), 'Windows 11') ? 'selected' : '' }}>Windows 11</option>
                        <option value="Windows 10" {{ str_contains(request('os', ''), 'Windows 10') ? 'selected' : '' }}>Windows 10</option>
                        <option value="Linux" {{ request('os') == 'Linux' ? 'selected' : '' }}>Linux</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px; display: block;">ชนิด Storage</label>
                    <select name="storage_type" class="form-select form-select-sm">
                        <option value="">-- ทุกชนิดไดรฟ์ --</option>
                        <option value="SSD NVMe" {{ str_contains(request('storage_type', ''), 'NVMe') ? 'selected' : '' }}>SSD NVMe</option>
                        <option value="SSD SATA" {{ str_contains(request('storage_type', ''), 'SATA') ? 'selected' : '' }}>SSD SATA</option>
                        <option value="HDD" {{ str_contains(request('storage_type', ''), 'HDD') ? 'selected' : '' }}>HDD SATA</option>
                    </select>
                </div>
                <div style="display: flex; gap: 6px;">
                    <button type="submit" class="btn btn-sm btn-primary" style="height: 31px; font-weight: 600; flex: 1;">
                        <i class="bi bi-funnel-fill"></i> กรองสเปก
                    </button>
                    <a href="{{ route('assets.index', request()->except(['cpu', 'ram_type', 'ram_capacity', 'os', 'storage_type', 'storage_capacity', 'page'])) }}" class="btn btn-sm btn-outline-secondary" title="ล้างค่าสเปก">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-card">
    <form action="{{ route('assets.index') }}" method="GET">
        <div class="filter-form-grid">
            {{-- Search Box --}}
            <div class="filter-group">
                <label class="filter-label">
                    <i class="bi bi-search text-primary"></i>
                    <span>ค้นหาข้อมูล (Search)</span>
                </label>
                <div class="filter-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" 
                           placeholder="รหัสครุภัณฑ์, S/N, ชื่อเครื่อง, ยี่ห้อ, IP, ผู้ครอบครอง" 
                           value="{{ request('search') }}">
                </div>
            </div>

            {{-- Device Type --}}
            <div class="filter-group">
                <label class="filter-label">
                    <i class="bi bi-cpu text-primary"></i>
                    <span>ประเภทอุปกรณ์</span>
                </label>
                <select name="device_type_id" class="form-select filter-select">
                    <option value="">-- ทุกประเภทอุปกรณ์ --</option>
                    @foreach($deviceTypes as $type)
                        <option value="{{ $type->id }}" {{ request('device_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Department --}}
            <div class="filter-group">
                <label class="filter-label">
                    <i class="bi bi-building text-primary"></i>
                    <span>แผนก / สถานที่ตั้ง</span>
                </label>
                @if(auth()->user()->isUser())
                    <input type="text" class="form-control filter-select" value="{{ auth()->user()->department->name ?? 'แผนกของท่าน' }}" readonly style="background: #f1f5f9; color: #475569;">
                @else
                    <select name="department_id" class="form-select filter-select">
                        <option value="">-- ทุกแผนก / หน่วยงาน --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Status --}}
            <div class="filter-group">
                <label class="filter-label">
                    <i class="bi bi-flag text-primary"></i>
                    <span>สถานะครุภัณฑ์</span>
                </label>
                <select name="status" class="form-select filter-select">
                    <option value="">-- ทุกสถานะ --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>🟢 ใช้งานปกติ</option>
                    <option value="spare" {{ request('status') == 'spare' ? 'selected' : '' }}>🔵 เครื่องสำรอง</option>
                    <option value="repairing" {{ request('status') == 'repairing' ? 'selected' : '' }}>🟠 กำลังส่งซ่อม</option>
                    <option value="broken" {{ request('status') == 'broken' ? 'selected' : '' }}>🔴 ชำรุดรอซ่อม</option>
                    <option value="disposed" {{ request('status') == 'disposed' ? 'selected' : '' }}>⚪ รอจำหน่าย/แทงจำหน่าย</option>
                </select>
            </div>

            {{-- Hidden per_page --}}
            <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">

            {{-- Action Buttons --}}
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="height: 40px; padding: 0 18px; font-weight: 600;">
                    <i class="bi bi-funnel-fill"></i> ค้นหา
                </button>
                <a href="{{ route('assets.index') }}" class="btn btn-secondary" style="height: 40px; width: 40px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" title="ล้างตัวกรอง">
                    <i class="bi bi-arrow-counterclockwise" style="font-size: 16px;"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Main Asset Table Card -->
<div class="assets-card">
    <div class="assets-card-header">
        <div class="assets-card-title">
            <i class="bi bi-pc text-primary" style="font-size: 20px;"></i>
            <span>ทะเบียนรายการครุภัณฑ์คอมพิวเตอร์</span>
            <span class="assets-count-badge">{{ number_format($assets->total()) }} รายการ</span>
        </div>
    </div>

    <div class="table-responsive" style="margin: 0;">
        <table class="assets-table">
            <thead>
                <tr>
                    <th style="width: 170px;">รหัสครุภัณฑ์ / S/N</th>
                    <th style="min-width: 220px;">ชื่อรายการ / รายละเอียด</th>
                    <th style="width: 140px;">ประเภทอุปกรณ์</th>
                    <th style="min-width: 180px;">แผนก / สถานที่ตั้ง</th>
                    <th style="width: 140px;">ผู้ครอบครอง</th>
                    <th style="width: 140px;">IP Address</th>
                    <th style="width: 120px;">สถานะ</th>
                    <th style="width: 130px; text-align: center;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assets as $asset)
                <tr>
                    {{-- Asset Code & Serial Number --}}
                    <td>
                        <a href="{{ route('assets.show', $asset) }}" class="asset-code-link" title="คลิกดูข้อมูลครุภัณฑ์">
                            <i class="bi bi-tag-fill text-primary" style="font-size: 11px;"></i>
                            <span>{{ $asset->asset_code }}</span>
                        </a>
                        @if($asset->serial_number)
                            <div class="asset-sn-text" title="Serial Number">
                                <i class="bi bi-upc"></i>
                                <span>S/N: {{ $asset->serial_number }}</span>
                            </div>
                        @endif
                    </td>

                    {{-- Name & Specs --}}
                    <td>
                        <div class="asset-name-text">
                            <a href="{{ route('assets.show', $asset) }}">{{ $asset->name }}</a>
                        </div>
                        <div class="asset-sub-text">
                            @if($asset->brand || $asset->model)
                                <span style="font-weight: 500;">{{ $asset->brand }} {{ $asset->model }}</span>
                            @endif
                            @if($asset->repairs_count > 0)
                                <span class="badge badge-warning" style="font-size: 11px; padding: 2px 7px;" title="ประวัติการซ่อมบำรุง">
                                    <i class="bi bi-wrench"></i> เคยซ่อม {{ $asset->repairs_count }} ครั้ง
                                </span>
                            @endif
                        </div>

                        {{-- Interactive Spec Badges --}}
                        @if($asset->cpu_model || $asset->ram_capacity || $asset->os_name || $asset->storage_capacity)
                            <div class="asset-spec-chips-row">
                                @if($asset->cpu_model)
                                    <a href="{{ route('assets.index', array_merge(request()->query(), ['cpu' => explode(' ', $asset->cpu_model)[2] ?? $asset->cpu_model, 'page' => 1])) }}" class="spec-badge spec-badge-cpu" title="กรองเฉพาะ CPU ตระกูลนี้">
                                        <i class="bi bi-cpu"></i> {{ $asset->cpu_model }}
                                    </a>
                                @endif
                                @if($asset->ram_capacity)
                                    <a href="{{ route('assets.index', array_merge(request()->query(), ['ram_type' => $asset->ram_type, 'ram_capacity' => $asset->ram_capacity, 'page' => 1])) }}" class="spec-badge spec-badge-ram" title="กรองเฉพาะ RAM ขนาด/ชนิดนี้">
                                        <i class="bi bi-memory"></i> {{ $asset->ram_capacity }}GB {{ $asset->ram_type }}
                                    </a>
                                @endif
                                @if($asset->storage_capacity || $asset->storage_type)
                                    <a href="{{ route('assets.index', array_merge(request()->query(), ['storage_type' => $asset->storage_type, 'page' => 1])) }}" class="spec-badge spec-badge-storage" title="กรองเฉพาะ Storage นี้">
                                        <i class="bi bi-device-hdd"></i> {{ $asset->storage_capacity ?? $asset->storage_type }}
                                    </a>
                                @endif
                                @if($asset->os_name)
                                    <a href="{{ route('assets.index', array_merge(request()->query(), ['os' => $asset->os_name, 'page' => 1])) }}" class="spec-badge spec-badge-os" title="กรองเฉพาะ OS นี้">
                                        <i class="bi bi-windows"></i> {{ $asset->os_name }}
                                    </a>
                                @endif
                            </div>
                        @elseif($asset->specs)
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                                <i class="bi bi-info-circle"></i> {{ Str::limit($asset->specs, 55) }}
                            </div>
                        @endif
                    </td>

                    {{-- Device Type --}}
                    <td>
                        <span class="asset-type-badge">
                            <i class="bi bi-cpu text-muted"></i>
                            <span>{{ $asset->deviceType?->name ?? 'อุปกรณ์ทั่วไป' }}</span>
                        </span>
                    </td>

                    {{-- Department & Location --}}
                    <td>
                        <div class="asset-dept-title">
                            <i class="bi bi-building text-primary" style="font-size: 13px;"></i>
                            <span>{{ $asset->department?->name ?? 'ไม่ระบุแผนก' }}</span>
                        </div>
                        @if($asset->location_detail)
                            <div class="asset-location-sub" title="จุดติดตั้ง/ห้อง">
                                <i class="bi bi-geo-alt"></i>
                                <span>{{ $asset->location_detail }}</span>
                            </div>
                        @endif
                    </td>

                    {{-- Custodian --}}
                    <td>
                        <div class="asset-custodian">
                            <i class="bi bi-person-badge text-muted"></i>
                            <span>{{ $asset->custodian_name ?: '-' }}</span>
                        </div>
                    </td>

                    {{-- IP Address --}}
                    <td>
                        @if($asset->ip_address)
                            <span class="asset-ip-badge" title="Network IP Address">
                                <i class="bi bi-hdd-network"></i>
                                <span>{{ $asset->ip_address }}</span>
                            </span>
                        @else
                            <span style="color: #94a3b8; font-size: 13px;">-</span>
                        @endif
                    </td>

                    {{-- Status Badge --}}
                    <td>
                        <span class="badge {{ $asset->status_badge }}" style="font-size: 12px; padding: 5px 10px;">
                            {{ $asset->status_label }}
                        </span>
                    </td>

                    {{-- Action Buttons --}}
                    <td style="text-align: center;">
                        <div class="action-btn-group">
                            {{-- View details --}}
                            <a href="{{ route('assets.show', $asset) }}" class="action-icon-btn" title="ดูรายละเอียดและประวัติการซ่อม">
                                <i class="bi bi-eye"></i>
                            </a>

                            {{-- Print QR Code Label --}}
                            <a href="{{ route('assets.label', $asset) }}" target="_blank" class="action-icon-btn" title="พิมพ์ป้ายสติกเกอร์ QR Code">
                                <i class="bi bi-qr-code"></i>
                            </a>

                            @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
                                {{-- Edit --}}
                                <a href="{{ route('assets.edit', $asset) }}" class="action-icon-btn" title="แก้ไขข้อมูล">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                {{-- Delete with Custom Confirmation Modal --}}
                                <button type="button" class="action-icon-btn action-icon-btn-danger" title="ลบครุภัณฑ์"
                                        onclick="openDeleteModal('{{ $asset->id }}', '{{ addslashes($asset->asset_code) }}', '{{ addslashes($asset->name) }}', '{{ addslashes($asset->brand . ' ' . $asset->model) }}', '{{ addslashes($asset->department?->name ?? 'ไม่ระบุแผนก') }}', '{{ addslashes($asset->deviceType?->name ?? '-') }}', '{{ addslashes($asset->status_label) }}', '{{ $asset->status_badge }}', {{ (int)$asset->repairs_count }})">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 48px 24px; color: #64748b;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px auto; font-size: 24px; color: #94a3b8;">
                            <i class="bi bi-pc-display"></i>
                        </div>
                        <div style="font-weight: 600; font-size: 15px; color: #334155; margin-bottom: 4px;">ไม่พบรายการครุภัณฑ์ตามเงื่อนไขที่ระบุ</div>
                        <div style="font-size: 13px; color: #94a3b8;">ลองเปลี่ยนคำค้นหา หรือกดปุ่มล้างตัวกรองเพื่อดูครุภัณฑ์ทั้งหมด</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination Strip --}}
    <div style="padding: 16px 24px; border-top: 1px solid var(--border); background: #ffffff;">
        {{ $assets->links('vendor.pagination.custom') }}
    </div>
</div>

<!-- Asset Deletion Confirmation Modal -->
<div id="deleteAssetModal" class="custom-modal-backdrop">
    <div class="custom-modal-dialog">
        {{-- Modal Header --}}
        <div class="modal-hero-header">
            <div class="modal-hero-left">
                <div class="modal-icon-badge">
                    <i class="bi bi-trash3-fill"></i>
                </div>
                <div>
                    <div style="font-size: 16.5px; font-weight: 700; letter-spacing: -0.2px;">ยืนยันการลบรายการครุภัณฑ์</div>
                    <div style="font-size: 12px; color: rgba(255, 255, 255, 0.85);">Asset Deletion Confirmation & Safety Check</div>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDeleteModal()" title="ปิดหน้าต่าง">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="modal-body-content">
            {{-- Asset Summary Card --}}
            <div class="modal-asset-card">
                <div class="modal-asset-header">
                    <span id="del_asset_code" class="modal-code-chip">-</span>
                    <span id="del_asset_status" class="badge badge-secondary">-</span>
                </div>
                <div style="font-weight: 700; font-size: 14.5px; color: #0f172a; margin-bottom: 8px;" id="del_asset_name">-</div>
                <div class="modal-info-row">
                    <strong>ยี่ห้อ / รุ่น:</strong>
                    <span id="del_asset_brand_model">-</span>
                </div>
                <div class="modal-info-row">
                    <strong>แผนก / ที่ตั้ง:</strong>
                    <span id="del_asset_dept">-</span>
                </div>
                <div class="modal-info-row" style="margin-bottom: 0;">
                    <strong>ประเภท:</strong>
                    <span id="del_asset_type">-</span>
                </div>
            </div>

            {{-- Case 1: Cannot Delete Due to Repair Records --}}
            <div id="del_blocked_section" style="display: none;">
                <div class="modal-alert-box modal-alert-warning">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="bi bi-shield-exclamation text-warning" style="font-size: 20px; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="color: #92400e; font-size: 14px;">ไม่สามารถลบครุภัณฑ์นี้ได้</strong>
                            <div style="margin-top: 4px; color: #78350f;">
                                ครุภัณฑ์นี้มีประวัติการแจ้งซ่อมในระบบจำนวน <strong id="del_repair_count_num" style="color: #b45309; font-size: 15px;">0</strong> รายการ เพื่อรักษาประวัติการซ่อมบำรุงตามระเบียบงานสารสนเทศโรงพยาบาล
                            </div>
                            <div style="margin-top: 8px; font-size: 12.5px; color: #92400e; background: rgba(245, 158, 11, 0.12); padding: 8px 12px; border-radius: 8px;">
                                💡 <strong>คำแนะนำ:</strong> หากอุปกรณ์ใช้งานไม่ได้ ท่านสามารถเข้าไป <strong>"แก้ไขข้อมูล"</strong> แล้วเปลี่ยนสถานะเป็น <strong>"ชำรุดรอซ่อม"</strong> หรือ <strong>"รอจำหน่าย/แทงจำหน่าย"</strong> แทนการลบ
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <a id="del_view_repairs_btn" href="#" class="btn btn-primary">
                        <i class="bi bi-clock-history"></i> ดูประวัติการซ่อม
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                        ปิดหน้าต่าง
                    </button>
                </div>
            </div>

            {{-- Case 2: Can Delete Safely (0 Repairs) --}}
            <div id="del_allowed_section" style="display: none;">
                <div class="modal-alert-box modal-alert-danger">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="bi bi-exclamation-triangle-fill" style="font-size: 20px; flex-shrink: 0; margin-top: 1px;"></i>
                        <div>
                            <strong style="font-size: 14px;">คำเตือน: ยืนยันการลบถาวร</strong>
                            <div style="margin-top: 4px;">
                                ข้อมูลครุภัณฑ์และประวัติทั้งหมดจะถูกลบออกจากระบบ และไม่สามารถย้อนคืนได้
                            </div>
                            <div style="font-size: 12px; margin-top: 6px; color: #991b1b;">
                                🛡️ ระบบจะทำการบันทึกข้อมูลการลบนี้ลงใน <strong>Audit Log</strong> เพื่อการตรวจสอบย้อนหลัง
                            </div>
                        </div>
                    </div>
                </div>

                <form id="deleteAssetForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                            ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-danger" style="box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);">
                            <i class="bi bi-trash3-fill"></i>
                            <span>ยืนยันลบครุภัณฑ์นี้</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Hardware Fleet Overview Modal --}}
<div id="fleetModal" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-dialog" style="max-width: 680px;">
        <div class="modal-hero-header" style="background: linear-gradient(135deg, #0f766e 0%, #0284c7 100%);">
            <div class="modal-hero-left">
                <div class="modal-icon-badge">
                    <i class="bi bi-pie-chart-fill"></i>
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #ffffff;">สรุปภาพรวมสเปกคอมพิวเตอร์และระบบ (Fleet Overview)</h3>
                    <p style="font-size: 12.5px; margin: 3px 0 0 0; color: rgba(255,255,255,0.9);">
                        กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง — ทั้งหมด {{ $hardwareStats['total_computers'] }} เครื่อง
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeFleetModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body-content" style="max-height: 75vh; overflow-y: auto;">
            {{-- RAM Types Distribution --}}
            <div style="margin-bottom: 20px;">
                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span><i class="bi bi-memory text-success me-1"></i> ประเภท RAM (DDR4 vs DDR5)</span>
                    <span style="font-size: 12px; color: #64748b;">(รวม {{ array_sum($hardwareStats['ram_types']) }} เครื่อง)</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                    @php $totalRam = max(array_sum($hardwareStats['ram_types']), 1); @endphp
                    @foreach($hardwareStats['ram_types'] as $type => $count)
                        @php $pct = round(($count / $totalRam) * 100); @endphp
                        <div style="margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                                <strong style="color: #1e293b;">{{ $type }}</strong>
                                <span>{{ $count }} เครื่อง ({{ $pct }}%)</span>
                            </div>
                            <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; display: flex;">
                                <div style="width: {{ $pct }}%; background: {{ $type == 'DDR5' ? '#0d9488' : '#3b82f6' }}; border-radius: 4px;"></div>
                            </div>
                            <div style="text-align: right; margin-top: 4px;">
                                <a href="{{ route('assets.index', ['ram_type' => $type]) }}" class="btn btn-sm btn-link p-0" style="font-size: 11.5px; text-decoration: none;">
                                    คลิกดูรายการเครื่อง <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- OS Distribution --}}
            <div style="margin-bottom: 20px;">
                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                    <span><i class="bi bi-windows text-info me-1"></i> สัดส่วนระบบปฏิบัติการ (Operating System)</span>
                    <span style="font-size: 12px; color: #64748b;">(รวม {{ array_sum($hardwareStats['os_list']) }} เครื่อง)</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px;">
                    @php $totalOs = max(array_sum($hardwareStats['os_list']), 1); @endphp
                    @foreach($hardwareStats['os_list'] as $os => $count)
                        @php $pct = round(($count / $totalOs) * 100); @endphp
                        <div style="margin-bottom: 10px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                                <strong style="color: #1e293b;">{{ $os }}</strong>
                                <span>{{ $count }} เครื่อง ({{ $pct }}%)</span>
                            </div>
                            <div style="height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden; display: flex;">
                                <div style="width: {{ $pct }}%; background: {{ str_contains($os, '11') ? '#0284c7' : '#6366f1' }}; border-radius: 4px;"></div>
                            </div>
                            <div style="text-align: right; margin-top: 4px;">
                                <a href="{{ route('assets.index', ['os' => $os]) }}" class="btn btn-sm btn-link p-0" style="font-size: 11.5px; text-decoration: none;">
                                    คลิกดูรายการเครื่อง <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Storage Types --}}
            <div>
                <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">
                    <i class="bi bi-device-hdd text-secondary me-1"></i> ชนิดไดรฟ์จัดเก็บข้อมูล
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px;">
                    @foreach($hardwareStats['storage_types'] as $st => $count)
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center;">
                            <div style="font-size: 12px; color: #64748b;">{{ $st }}</div>
                            <div style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 4px 0;">{{ $count }} เครื่อง</div>
                            <a href="{{ route('assets.index', ['storage_type' => $st]) }}" class="btn btn-sm btn-outline-primary" style="font-size: 11.5px; padding: 2px 10px; border-radius: 6px;">
                                ดูรายการ
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; text-align: right;">
            <button type="button" class="btn btn-secondary" onclick="closeFleetModal()">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

@include('assets._import_modal')
@endsection

@push('scripts')
<script>
    function toggleAdvancedFilter() {
        const form = document.getElementById('advancedSpecsFilterForm');
        const text = document.getElementById('advFilterToggleText');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            if (text) text.textContent = 'ซ่อนฟอร์มสเปกละเอียด';
        } else {
            form.style.display = 'none';
            if (text) text.textContent = 'ฟอร์มค้นหาสเปกละเอียด';
        }
    }

    function openFleetModal() {
        const modal = document.getElementById('fleetModal');
        if (modal) modal.style.display = 'flex';
    }

    function closeFleetModal() {
        const modal = document.getElementById('fleetModal');
        if (modal) modal.style.display = 'none';
    }

    function openDeleteModal(id, code, name, brandModel, dept, type, statusLabel, statusBadge, repairsCount) {
        document.getElementById('del_asset_code').textContent = code;
        document.getElementById('del_asset_name').textContent = name;
        document.getElementById('del_asset_brand_model').textContent = brandModel && brandModel.trim() ? brandModel : '-';
        document.getElementById('del_asset_dept').textContent = dept;
        document.getElementById('del_asset_type').textContent = type;

        const statusElem = document.getElementById('del_asset_status');
        statusElem.textContent = statusLabel;
        statusElem.className = 'badge ' + statusBadge;

        if (repairsCount > 0) {
            // Block deletion
            document.getElementById('del_repair_count_num').textContent = repairsCount;
            document.getElementById('del_view_repairs_btn').href = '/assets/' + id;
            document.getElementById('del_blocked_section').style.display = 'block';
            document.getElementById('del_allowed_section').style.display = 'none';
        } else {
            // Allow deletion
            document.getElementById('deleteAssetForm').action = '/assets/' + id;
            document.getElementById('del_blocked_section').style.display = 'none';
            document.getElementById('del_allowed_section').style.display = 'block';
        }

        const modal = document.getElementById('deleteAssetModal');
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteAssetModal');
        modal.style.display = 'none';
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const delModal = document.getElementById('deleteAssetModal');
        if (e.target === delModal) {
            closeDeleteModal();
        }
        const fleetModal = document.getElementById('fleetModal');
        if (e.target === fleetModal) {
            closeFleetModal();
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
            closeFleetModal();
        }
    });
</script>
@endpush
