@extends('layouts.app')

@section('title', 'บันทึกกิจกรรมและความปลอดภัยระบบ (Audit Logs)')
@section('page_title', 'บันทึกกิจกรรมและความปลอดภัยระบบ (Audit Logs)')
@section('page_subtitle', 'ตรวจสอบประวัติการทำรายการ การเปลี่ยนแปลงข้อมูล และกิจกรรมความปลอดภัยตามมาตรฐาน PDPA / ISO 27001')

@push('styles')
<style>
    /* Metric Stat Widget */
    .stat-widget {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }
    .stat-widget:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        border-color: #cbd5e1;
        color: inherit;
    }
    .stat-widget.active-stat {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-light);
    }
    .stat-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    /* Filter Chip Pill */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        font-size: 12.5px;
        font-weight: 500;
        border-radius: 9999px;
        text-decoration: none;
        transition: all 0.15s ease;
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .filter-chip:hover {
        background: #e2e8f0;
        color: var(--text-main);
    }
    .filter-chip.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
        font-weight: 600;
        box-shadow: 0 2px 8px var(--primary-glow);
    }

    /* IP Copy Chip */
    .ip-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: monospace;
        font-size: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        padding: 2px 8px;
        border-radius: 6px;
        color: #0f172a;
    }
    .ip-copy-btn {
        background: none;
        border: none;
        padding: 0;
        color: #94a3b8;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        transition: color 0.15s;
    }
    .ip-copy-btn:hover {
        color: var(--primary);
    }

    /* Custom Table Row */
    .audit-table-row:hover {
        background-color: #f8fafc;
    }

    /* Custom Modal System (Pure Vanilla CSS - No Bootstrap Dependency) */
    .audit-modal-backdrop {
        display: none !important;
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 20px;
        box-sizing: border-box;
    }
    .audit-modal-backdrop.active {
        display: flex !important;
    }
    .audit-modal-dialog {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-width: 820px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
        overflow: hidden;
        animation: auditModalScaleIn 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        border: 1px solid rgba(255, 255, 255, 0.15);
        margin: auto;
    }
    .audit-modal-dialog-sm {
        max-width: 540px;
    }
    @keyframes auditModalScaleIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(12px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    .modal-hero-header {
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }
    .modal-close-btn {
        width: 34px;
        height: 34px;
        border-radius: 9999px;
        background: rgba(255, 255, 255, 0.18);
        border: none;
        color: #ffffff;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        flex-shrink: 0;
    }
    .modal-close-btn:hover {
        background: rgba(255, 255, 255, 0.32);
        transform: scale(1.06);
        color: #ffffff;
    }
    .modal-close-btn-dark {
        background: #f1f5f9;
        color: #64748b;
    }
    .modal-close-btn-dark:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div style="max-width: 1400px; margin: 0 auto;">

    <!-- KPI Metric Summary Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
        <!-- Total Logs -->
        <a href="{{ route('audit-logs.index') }}" class="stat-widget {{ !request()->hasAny(['filter', 'module', 'action', 'user_id', 'search']) ? 'active-stat' : '' }}">
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">กิจกรรมทั้งหมด</div>
                <div style="font-size: 24px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ number_format($totalLogs) }}</div>
                <div style="font-size: 11.5px; color: #0284c7; margin-top: 2px;">
                    <i class="bi bi-clock-history"></i> รายการทั้งหมดในระบบ
                </div>
            </div>
            <div class="stat-icon-box" style="background: #e0f2fe; color: #0284c7;">
                <i class="bi bi-journal-text"></i>
            </div>
        </a>

        <!-- Today Logs -->
        <a href="{{ route('audit-logs.index', ['filter' => 'today']) }}" class="stat-widget {{ request('filter') === 'today' ? 'active-stat' : '' }}" title="คลิกเพื่อดูกิจกรรมวันนี้">
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">กิจกรรมวันนี้</div>
                <div style="font-size: 24px; font-weight: 700; color: #0d9488; margin-top: 2px;">{{ number_format($todayLogs) }}</div>
                <div style="font-size: 11.5px; color: #0d9488; margin-top: 2px;">
                    <i class="bi bi-calendar-check"></i> วันที่ {{ date('d/m/Y') }}
                </div>
            </div>
            <div class="stat-icon-box" style="background: #ccfbf1; color: #0d9488;">
                <i class="bi bi-calendar2-day"></i>
            </div>
        </a>

        <!-- Critical Actions -->
        <a href="{{ route('audit-logs.index', ['filter' => 'critical']) }}" class="stat-widget {{ request('filter') === 'critical' ? 'active-stat' : '' }}" title="คลิกเพื่อดูรายการสำคัญ เช่น ลบ/กู้คืน/ระงับ">
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">ความเสี่ยงสูง (ลบ/ระงับ)</div>
                <div style="font-size: 24px; font-weight: 700; color: #dc2626; margin-top: 2px;">{{ number_format($criticalLogs) }}</div>
                <div style="font-size: 11.5px; color: #ef4444; margin-top: 2px;">
                    <i class="bi bi-shield-alert"></i> รายการสำคัญด้านความปลอดภัย
                </div>
            </div>
            <div class="stat-icon-box" style="background: #fee2e2; color: #dc2626;">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
        </a>

        <!-- Active Users Today -->
        <div class="stat-widget" style="cursor: default;">
            <div>
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">ผู้ใช้งานวันนี้</div>
                <div style="font-size: 24px; font-weight: 700; color: #b45309; margin-top: 2px;">{{ number_format($activeUsersToday) }}</div>
                <div style="font-size: 11.5px; color: #b45309; margin-top: 2px;">
                    <i class="bi bi-person-check"></i> บัญชีที่มีการเคลื่อนไหว
                </div>
            </div>
            <div class="stat-icon-box" style="background: #fef3c7; color: #b45309;">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <!-- Main Audit Trail Card -->
    <div class="card" style="margin-bottom: 24px;">
        
        <!-- Card Header: Title & Action Tools -->
        <div class="card-header" style="flex-wrap: wrap; gap: 14px; padding: 16px 22px; background: #ffffff;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #ccfbf1; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div style="font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <span>บันทึกกิจกรรมและความปลอดภัยระบบ (Audit Logs)</span>
                        <span class="badge badge-secondary" style="font-size: 11.5px;">{{ number_format($logs->total()) }} รายการ</span>
                        <span class="badge badge-primary" style="font-size: 11px;">PDPA / ISO 27001 Ready</span>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 1px;">
                        นโยบายจัดเก็บปัจจุบัน: <strong class="text-dark">{{ $retentionSettingDays }} วัน</strong>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('audit-logs.export', request()->query()) }}" class="btn btn-success btn-sm" title="ส่งออกรายงานเป็นไฟล์ CSV (UTF-8 BOM สำหรับ Excel)">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>ส่งออก CSV</span>
                </a>

                <button type="button" class="btn btn-secondary btn-sm" style="color: #b91c1c; border-color: #cbd5e1;" onclick="openAuditModal('retentionModal')" title="ล้างข้อมูลประวัติที่เก่ากว่ากำหนด">
                    <i class="bi bi-trash3"></i>
                    <span>ล้างประวัติเก่า</span>
                </button>

                <a href="{{ route('settings.index') }}#security" class="btn btn-secondary btn-sm" title="ตั้งค่านโยบายความปลอดภัยของระบบ">
                    <i class="bi bi-gear"></i>
                    <span>ตั้งค่านโยบาย</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Toolbar Section -->
        <div style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 16px 22px;">
            <!-- Row 1: Quick Filter Chips -->
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3 pb-3 border-bottom">
                <span style="font-size: 12px; font-weight: 600; color: #64748b; margin-right: 2px;">
                    <i class="bi bi-lightning-charge text-warning me-1"></i>ทางลัดกรองข้อมูล:
                </span>

                @php
                    $curMod = request('module');
                    $curFilter = request('filter');
                    $isAll = empty($curMod) && empty($curFilter) && !request()->hasAny(['action', 'user_id', 'date_from', 'date_to', 'search']);
                @endphp

                <a href="{{ route('audit-logs.index') }}" class="filter-chip {{ $isAll ? 'active' : '' }}">
                    <i class="bi bi-grid-fill"></i> ทั้งหมด
                </a>
                <a href="{{ route('audit-logs.index', ['filter' => 'today']) }}" class="filter-chip {{ $curFilter === 'today' ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check"></i> วันนี้
                </a>
                <a href="{{ route('audit-logs.index', ['filter' => 'critical']) }}" class="filter-chip {{ $curFilter === 'critical' ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i> ความเสี่ยงสูง (ลบ/ระงับ)
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'auth']) }}" class="filter-chip {{ $curMod === 'auth' ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> ความปลอดภัย & ล็อกอิน
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'repairs']) }}" class="filter-chip {{ $curMod === 'repairs' ? 'active' : '' }}">
                    <i class="bi bi-tools"></i> งานแจ้งซ่อม
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'assets']) }}" class="filter-chip {{ $curMod === 'assets' ? 'active' : '' }}">
                    <i class="bi bi-pc-display"></i> ครุภัณฑ์คอมพิวเตอร์
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'spare_parts']) }}" class="filter-chip {{ $curMod === 'spare_parts' ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> พัสดุ & อะไหล่ IT
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'data_requests']) }}" class="filter-chip {{ $curMod === 'data_requests' ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> คำขอข้อมูล
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'settings']) }}" class="filter-chip {{ $curMod === 'settings' ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i> ตั้งค่าระบบ
                </a>
                <a href="{{ route('audit-logs.index', ['module' => 'backups']) }}" class="filter-chip {{ $curMod === 'backups' ? 'active' : '' }}">
                    <i class="bi bi-database-gear"></i> สำรองฐานข้อมูล
                </a>
            </div>

            <!-- Row 2: Search Form Inputs -->
            <form action="{{ route('audit-logs.index') }}" method="GET">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end;">
                    <!-- Keyword Search -->
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ค้นหา (ข้อความ / ผู้ใช้ / IP)</label>
                        <div style="position: relative;">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="พิมพ์คำค้นหา..." value="{{ request('search') }}" style="padding-left: 32px; font-size: 13px;">
                            <i class="bi bi-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px;"></i>
                        </div>
                    </div>

                    <!-- Module Dropdown -->
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ระบบงาน (Module)</label>
                        <select name="module" class="form-select form-select-sm" style="font-size: 13px;">
                            <option value="">-- ทุกระบบงาน --</option>
                            @foreach($modules as $key => $title)
                                <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Dropdown -->
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ประเภทการทำงาน (Action)</label>
                        <select name="action" class="form-select form-select-sm" style="font-size: 13px;">
                            <option value="">-- ทุกประเภท --</option>
                            @foreach($actions as $key => $title)
                                <option value="{{ $key }}" {{ request('action') == $key ? 'selected' : '' }}>{{ $title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- User Dropdown -->
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ผู้ดำเนินการ</label>
                        <select name="user_id" class="form-select form-select-sm" style="font-size: 13px;">
                            <option value="">-- ทุกคน --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ตั้งแต่วันที่</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}" style="font-size: 12.5px;">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ถึงวันที่</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}" style="font-size: 12.5px;">
                    </div>

                    <!-- Per page selector & Buttons -->
                    <div style="display: flex; gap: 8px; align-items: end;">
                        <div style="flex: 1;">
                            <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">แสดงต่อหน้า:</label>
                            <select name="per_page" class="form-select form-select-sm" style="font-size: 13px;" onchange="this.form.submit()">
                                <option value="10" {{ ($perPage ?? 25) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ ($perPage ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ ($perPage ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ ($perPage ?? 25) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" style="padding: 7px 14px;" title="ค้นหาข้อมูล">
                            <i class="bi bi-funnel-fill"></i>
                        </button>
                        @if(request()->hasAny(['search', 'module', 'action', 'user_id', 'date_from', 'date_to', 'filter']))
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary btn-sm" style="padding: 7px 12px;" title="ล้างตัวกรอง">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Table View -->
        <div class="table-responsive" style="margin-bottom: 0;">
            <table class="table align-middle" style="margin-bottom: 0;">
                <thead>
                    <tr style="background: #f8fafc; font-size: 12.5px; color: #475569;">
                        <th style="width: 145px; padding-left: 20px;">วัน-เวลา</th>
                        <th style="width: 210px;">ผู้ดำเนินการ</th>
                        <th style="width: 140px;">ระบบงาน</th>
                        <th style="width: 130px;">การทำงาน</th>
                        <th>รายละเอียดกิจกรรม & รายการที่เกี่ยวข้อง</th>
                        <th style="width: 170px;">IP / อุปกรณ์</th>
                        <th style="width: 80px; text-align: center; padding-right: 20px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="audit-table-row" style="transition: background-color 0.15s ease;">
                        <!-- Timestamp -->
                        <td style="padding-left: 20px;">
                            <div style="font-size: 13px; font-weight: 600; color: #0f172a;">
                                {{ $log->created_at->format('d/m/Y') }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; font-family: monospace;">
                                {{ $log->created_at->format('H:i:s') }} น.
                            </div>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                {{ $log->created_at->diffForHumans() }}
                            </div>
                        </td>

                        <!-- User -->
                        <td>
                            <div style="font-size: 13.5px; font-weight: 600; color: #0f172a;" title="{{ $log->user_name }}">
                                {{ $log->user_name }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b;">
                                {{ $log->user?->department?->name ?? ($log->user_role ? ucfirst($log->user_role) : 'ผู้เยี่ยมชม') }}
                            </div>
                        </td>

                        <!-- Module -->
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: #334155; background: #f1f5f9; padding: 3px 8px; border-radius: 6px;">
                                <i class="bi {{ $log->module_icon }} text-primary"></i>
                                <span>{{ $log->module_name }}</span>
                            </span>
                        </td>

                        <!-- Action -->
                        <td>
                            <span class="badge {{ $log->action_badge }}" style="font-size: 11.5px; padding: 4px 8px;">
                                {{ $log->action_name }}
                            </span>
                        </td>

                        <!-- Description & Target Entity Link -->
                        <td>
                            <div style="font-size: 13.5px; color: #1e293b; line-height: 1.4;">
                                {{ $log->description }}
                            </div>

                            <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                <!-- Target entity quick link -->
                                @if($log->target_url)
                                    <a href="{{ $log->target_url }}" target="_blank" class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px; text-decoration: none; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;" title="คลิกเพื่อเปิดดูข้อมูลในระบบ">
                                        <i class="bi {{ $log->target_icon }}"></i>
                                        <span>{{ $log->target_label }}</span>
                                        <i class="bi bi-box-arrow-up-right" style="font-size: 9.5px;"></i>
                                    </a>
                                @elseif($log->target_label)
                                    <span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">
                                        <i class="bi {{ $log->target_icon }}"></i> {{ $log->target_label }}
                                    </span>
                                @endif

                                @if($log->method)
                                    <span style="font-size: 10px; font-family: monospace; background: #f1f5f9; color: #64748b; padding: 1px 5px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        {{ $log->method }}
                                    </span>
                                @endif

                                @if(!empty($log->old_values) || !empty($log->new_values))
                                    <span style="font-size: 10px; background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px;">
                                        <i class="bi bi-arrow-left-right"></i> มีบันทึกค่าก่อน-หลัง
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Network & Device -->
                        <td>
                            <div class="ip-chip">
                                <span>{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                <button type="button" class="ip-copy-btn" onclick="copyIp('{{ $log->ip_address ?? '127.0.0.1' }}', this)" title="คัดลอก IP">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 165px;" title="{{ $log->user_agent }}">
                                <i class="bi bi-display text-muted me-1"></i>{{ $log->browser_info }}
                            </div>
                        </td>

                        <!-- Action Button -->
                        <td style="text-align: center; padding-right: 20px;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="viewAuditDetails({{ $log->id }})" title="ดูรายละเอียดและข้อมูลเชิงลึก" style="padding: 4px 10px; font-size: 12px;">
                                <i class="bi bi-eye"></i> ดู
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px 20px;">
                            <div style="color: #cbd5e1; font-size: 40px; margin-bottom: 10px;">
                                <i class="bi bi-journal-x"></i>
                            </div>
                            <div style="font-size: 15px; font-weight: 600; color: #475569;">ไม่พบประวัติกิจกรรมตามเงื่อนไขที่เลือก</div>
                            <p style="font-size: 13px; color: #94a3b8; margin-top: 4px;">ลองเปลี่ยนคำค้นหา หรือรีเซ็ตตัวกรองด้านบน</p>
                            <a href="{{ route('audit-logs.index') }}" class="btn btn-primary btn-sm" style="margin-top: 6px;">
                                <i class="bi bi-arrow-counterclockwise"></i> ดูทั้งหมด
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer / Pagination -->
        @if($logs->hasPages() || $logs->total() > 0)
        <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #ffffff; padding: 14px 22px; border-top: 1px solid var(--border);">
            <div style="font-size: 13px; color: #64748b;">
                แสดง <strong>{{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }}</strong> จากทั้งหมด <strong>{{ number_format($logs->total()) }}</strong> รายการ
            </div>
            <div>
                {{ $logs->links() }}
            </div>
        </div>
        @endif

    </div>

</div>

<!-- Audit Log Detail & Data Diff Modal (Pure Vanilla Dialog) -->
<div id="auditDetailModal" class="audit-modal-backdrop" onclick="handleModalBackdropClick(event, 'auditDetailModal')">
    <div class="audit-modal-dialog" onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="modal-hero-header" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
            <div class="d-flex align-items-center gap-3">
                <div id="modalModuleIconBox" style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.12); display: flex; align-items: center; justify-content: center; font-size: 22px; color: #5eead4; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.2);">
                    <i class="bi bi-shield-check" id="modalModuleIcon"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 style="font-size: 16px; font-weight: 700; margin: 0; color: #ffffff;" id="modalTitle">
                            รายละเอียดกิจกรรม #<span id="modalId">-</span>
                        </h5>
                        <span class="badge" id="modalActionBadge" style="font-size: 11.5px; font-weight: 600;">-</span>
                    </div>
                    <div style="font-size: 12px; color: #94a3b8; margin-top: 3px;" id="modalTimeSubtitle">-</div>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeAuditModal('auditDetailModal')" title="ปิดหน้าต่าง">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div style="padding: 22px; max-height: 75vh; overflow-y: auto; background: #f8fafc; flex: 1;">
            
            <!-- Actor & Action Context Card -->
            <div class="card mb-3" style="border: 1px solid var(--border); border-radius: 12px; background: #ffffff; margin-bottom: 14px; box-shadow: var(--shadow-sm);">
                <div class="card-body" style="padding: 16px;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">ผู้ดำเนินการ</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div style="font-size: 14px; font-weight: 700; color: #0f172a;" id="modalUserName">-</div>
                                <span class="badge badge-secondary" style="font-size: 11px;" id="modalUserRole">-</span>
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;" id="modalUserDepartment">-</div>
                        </div>

                        <div class="col-md-6">
                            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">ระบบงาน & คำสั่ง</div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span style="font-size: 13.5px; font-weight: 600; color: #0f172a;" id="modalModuleName">-</span>
                            </div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;" id="modalMethodBadge">-</div>
                        </div>

                        <div class="col-12 pt-2 border-top">
                            <div style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">ข้อความบันทึกกิจกรรม</div>
                            <div style="font-size: 13.5px; font-weight: 500; color: #1e293b; margin-top: 4px; background: #f1f5f9; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; line-height: 1.5;" id="modalDescription">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Target Entity Banner (if available) -->
            <div id="modalTargetContainer" style="display: none; margin-bottom: 14px;">
                <div style="border-radius: 12px; padding: 12px 16px; border: 1px solid #bae6fd; background: #e0f2fe; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #0284c7; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                            <i class="bi bi-link-45deg"></i>
                        </div>
                        <div>
                            <div style="font-size: 11.5px; color: #0369a1; font-weight: 600; text-transform: uppercase;">รายการในระบบที่เกี่ยวข้อง:</div>
                            <div id="modalTargetLabel" style="font-size: 13.5px; font-weight: 700; color: #0c4a6e;">-</div>
                        </div>
                    </div>
                    <a href="#" id="modalTargetLink" target="_blank" class="btn btn-sm btn-primary" style="font-size: 12.5px; padding: 6px 14px; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 6px rgba(13, 148, 136, 0.25);">
                        <span>เปิดดูข้อมูลจริง</span>
                        <i class="bi bi-box-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Network & Client Info -->
            <div class="card mb-3" style="border: 1px solid var(--border); border-radius: 12px; background: #ffffff; margin-bottom: 14px; box-shadow: var(--shadow-sm);">
                <div class="card-body" style="padding: 16px;">
                    <h6 style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-hdd-network text-primary"></i> ข้อมูลเครือข่ายและระบบ (Network & Client)
                    </h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 13px;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 11px; color: #64748b;">IP Address</div>
                            <div style="font-family: monospace; font-weight: 600; color: #0f172a; margin-top: 2px;" id="modalIpAddress">-</div>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 11px; color: #64748b;">อุปกรณ์และระบบปฏิบัติการ</div>
                            <div style="font-weight: 500; color: #0f172a; margin-top: 2px;" id="modalBrowserInfo">-</div>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px;">
                            <div style="font-size: 11px; color: #64748b;">HTTP Endpoint</div>
                            <div style="font-family: monospace; font-size: 11.5px; color: #0f172a; margin-top: 2px; word-break: break-all;" id="modalEndpoint">-</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Diff Section -->
            <div class="card" style="border: 1px solid var(--border); border-radius: 12px; background: #ffffff; box-shadow: var(--shadow-sm);">
                <div class="card-body" style="padding: 16px;">
                    <h6 style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-left-right text-primary"></i> เปรียบเทียบข้อมูลก่อน-หลัง (Data Diff)
                    </h6>

                    <div id="diffContainer">
                        <!-- Loaded via AJAX -->
                    </div>
                </div>
            </div>

        </div>

        <!-- Modal Footer -->
        <div style="background: #ffffff; border-top: 1px solid var(--border); padding: 14px 24px; display: flex; align-items: center; justify-content: flex-end;">
            <button type="button" class="btn btn-secondary" onclick="closeAuditModal('auditDetailModal')" style="font-size: 13.5px; padding: 8px 18px; border-radius: 8px;">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<!-- Retention Policy Modal (Pure Vanilla Dialog) -->
<div id="retentionModal" class="audit-modal-backdrop" onclick="handleModalBackdropClick(event, 'retentionModal')">
    <div class="audit-modal-dialog audit-modal-dialog-sm" onclick="event.stopPropagation()">
        <form action="{{ route('audit-logs.clear-old') }}" method="POST" style="margin: 0; display: flex; flex-direction: column; width: 100%;">
            @csrf
            <!-- Modal Header -->
            <div class="modal-hero-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca;">
                <div class="d-flex align-items-center gap-2 text-danger">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div>
                        <h5 style="font-size: 16px; font-weight: 700; margin: 0; color: #991b1b;">
                            จัดการอายุข้อมูลและล้างประวัติเก่า
                        </h5>
                        <div style="font-size: 12px; color: #b91c1c; margin-top: 2px;">Data Retention & Purge Old Logs</div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn modal-close-btn-dark" onclick="closeAuditModal('retentionModal')" title="ปิดหน้าต่าง">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div style="padding: 22px; background: #ffffff;">
                <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" style="font-size: 13px; line-height: 1.5; border-radius: 10px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 14px;">
                    <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1" style="font-size: 16px;"></i>
                    <div>
                        <strong>คำเตือน:</strong> การล้างประวัติกิจกรรมจะลบข้อมูลออกจากฐานข้อมูลอย่างถาวร ไม่สามารถกู้คืนได้ และอาจส่งผลต่อการตรวจสอบย้อนหลังตามมาตรฐาน PDPA
                    </div>
                </div>

                <div class="p-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px;">
                    <div style="color: #64748b; font-size: 12px;">นโยบายจัดเก็บอัตโนมัติของระบบปัจจุบัน:</div>
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 2px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;">
                        <span>{{ $retentionSettingDays }} วัน</span>
                        <a href="{{ route('settings.index') }}#security" style="font-size: 12px; font-weight: 500; color: #0284c7; text-decoration: none;">
                            ปรับแต่งในการตั้งค่าระบบ <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label required" style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">เลือกระยะเวลาที่ต้องการล้าง</label>
                    <select name="days" class="form-select" required style="font-size: 13.5px; width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        <option value="{{ $retentionSettingDays }}" selected>ล้างข้อมูลที่เก่ากว่า {{ $retentionSettingDays }} วัน (ค่านโยบายปัจจุบันของระบบ)</option>
                        <option value="30">ล้างข้อมูลที่เก่ากว่า 30 วัน (1 เดือน)</option>
                        <option value="60">ล้างข้อมูลที่เก่ากว่า 60 วัน (2 เดือน)</option>
                        <option value="90">ล้างข้อมูลที่เก่ากว่า 90 วัน (3 เดือน - แนะนำ)</option>
                        <option value="180">ล้างข้อมูลที่เก่ากว่า 180 วัน (6 เดือน)</option>
                        <option value="365">ล้างข้อมูลที่เก่ากว่า 365 วัน (1 ปี - มาตรฐาน PDPA)</option>
                        <option value="730">ล้างข้อมูลที่เก่ากว่า 730 วัน (2 ปี)</option>
                    </select>
                </div>

                <div class="form-group mb-0">
                    <label class="form-label required" style="font-size: 13px; font-weight: 600; color: #1e293b; margin-bottom: 6px; display: block;">
                        พิมพ์คำว่า <strong class="text-danger">CLEAR</strong> เพื่อยืนยัน
                    </label>
                    <input type="text" name="confirm_text" class="form-control" placeholder="พิมพ์ CLEAR" required autocomplete="off" style="font-size: 14px; text-transform: uppercase; width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px; display: flex; align-items: center; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeAuditModal('retentionModal')" style="padding: 8px 18px; font-size: 13.5px; border-radius: 8px;">ยกเลิก</button>
                <button type="submit" class="btn btn-danger" style="padding: 8px 20px; font-size: 13.5px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-trash3-fill"></i>
                    <span>ยืนยันการล้างประวัติเก่า</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Custom Pure JS Modal Controller (No Bootstrap Dependency)
function openAuditModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeAuditModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function handleModalBackdropClick(event, modalId) {
    if (event.target && event.target.id === modalId) {
        closeAuditModal(modalId);
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.audit-modal-backdrop.active').forEach(m => {
            m.classList.remove('active');
        });
        document.body.style.overflow = '';
    }
});

// Dictionary to translate database field names to friendly Thai descriptions
const FIELD_DICT = {
    'name': 'ชื่อ / รายการ',
    'title': 'ชื่อเรื่อง / หัวข้อ',
    'status': 'สถานะ',
    'price': 'ราคา (บาท)',
    'cost': 'ราคา / ต้นทุน',
    'serial_number': 'Serial Number (S/N)',
    'asset_code': 'รหัสครุภัณฑ์',
    'ticket_number': 'เลขที่ใบแจ้งซ่อม',
    'request_no': 'เลขที่คำขอข้อมูล',
    'part_code': 'รหัสพัสดุ/อะไหล่',
    'brand': 'ยี่ห้อ (Brand)',
    'model': 'รุ่น (Model)',
    'device_type_id': 'รหัสประเภทอุปกรณ์',
    'department_id': 'รหัสแผนก / หน่วยงาน',
    'cpu_model': 'ซีพียู (CPU)',
    'cpu_speed': 'ความเร็วซีพียู',
    'ram_capacity': 'ความจุแรม (RAM GB)',
    'ram_type': 'ชนิดแรม (RAM Type)',
    'ram_bus': 'บัสแรม (Bus Speed)',
    'ram_slots': 'จำนวนสล็อตแรม',
    'storage_type': 'ชนิด Storage (SSD/HDD)',
    'storage_capacity': 'ความจุ Storage',
    'storage_second': 'ไดรฟ์สำรอง',
    'os_name': 'ระบบปฏิบัติการ (OS)',
    'os_license': 'ลิขสิทธิ์ระบบปฏิบัติการ',
    'gpu_model': 'การ์ดแสดงผล (GPU)',
    'monitor_size': 'ขนาดจอภาพ',
    'specs': 'รายละเอียดคุณลักษณะ / สเปก',
    'location_detail': 'สถานที่ตั้ง / จุดวาง',
    'custodian_name': 'ผู้ครอบครอง / ผู้ดูแล',
    'ip_address': 'หมายเลข IP Address',
    'mac_address': 'หมายเลข MAC Address',
    'budget_year': 'ปีงบประมาณ',
    'purchase_date': 'วันที่ซื้อ / รับเข้า',
    'warranty_expire_date': 'วันหมดประกัน',
    'notes': 'หมายเหตุ',
    'is_active': 'สถานะเปิดใช้งาน',
    'mfa_enabled': 'การเปิดใช้งาน 2FA (MFA)',
    'mfa_enforced': 'นโยบายบังคับใช้ 2FA',
    'role': 'บทบาท / สิทธิ์การใช้งาน',
    'stock_quantity': 'จำนวนคงเหลือในสต็อก',
    'unit': 'หน่วยนับ',
    'min_quantity': 'จุดสั่งซื้อต่ำสุด',
    'urgency': 'ระดับความเร่งด่วน',
    'description': 'รายละเอียด',
    'solution': 'แนวทางการแก้ไข',
    'technician_notes': 'บันทึกช่าง',
    'email': 'อีเมล',
    'phone': 'เบอร์โทรศัพท์',
};

function copyIp(text, btn) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = 'bi bi-check-lg text-success';
                setTimeout(() => { icon.className = 'bi bi-clipboard'; }, 1500);
            }
        });
    }
}

function viewAuditDetails(logId) {
    // Open modal immediately using custom pure JS controller
    openAuditModal('auditDetailModal');

    // Reset fields to loading state
    document.getElementById('modalId').textContent = logId;
    document.getElementById('modalTimeSubtitle').textContent = 'กำลังดึงข้อมูล...';
    document.getElementById('modalUserName').textContent = 'กำลังโหลด...';
    document.getElementById('modalUserRole').textContent = '...';
    document.getElementById('modalUserDepartment').textContent = '...';
    document.getElementById('modalModuleName').textContent = '...';
    
    const badge = document.getElementById('modalActionBadge');
    badge.className = 'badge badge-secondary';
    badge.textContent = 'กำลังโหลด';

    document.getElementById('modalDescription').textContent = 'กำลังโหลดรายละเอียดจากเซิร์ฟเวอร์...';
    document.getElementById('modalIpAddress').textContent = '...';
    document.getElementById('modalBrowserInfo').textContent = '...';
    document.getElementById('modalMethodBadge').textContent = '...';
    document.getElementById('modalEndpoint').textContent = '...';
    document.getElementById('modalTargetContainer').style.display = 'none';

    const diffContainer = document.getElementById('diffContainer');
    diffContainer.innerHTML = '<div style="text-align: center; padding: 30px; color: #64748b;"><div class="spinner-border spinner-border-sm me-2 text-primary"></div> กำลังโหลดข้อมูลเปรียบเทียบ (Data Diff)...</div>';

    fetch(`{{ url('/audit-logs') }}/${logId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('HTTP status ' + res.status);
        return res.json();
    })
    .then(data => {
        if (!data.success) {
            diffContainer.innerHTML = '<div class="alert alert-danger" style="margin: 10px 0;">ไม่สามารถโหลดข้อมูลได้</div>';
            return;
        }

        document.getElementById('modalId').textContent = data.id;
        document.getElementById('modalTimeSubtitle').textContent = `${data.created_at} (${data.created_at_human})`;
        document.getElementById('modalUserName').textContent = data.user_name || 'ระบบอัตโนมัติ / ไม่ระบุ';
        document.getElementById('modalUserRole').textContent = data.user_role || '-';
        document.getElementById('modalUserDepartment').textContent = data.user_department ? `แผนก: ${data.user_department}` : '-';
        document.getElementById('modalModuleName').textContent = data.module_name || data.module;
        
        const iconEl = document.getElementById('modalModuleIcon');
        iconEl.className = `bi ${data.module_icon || 'bi-shield-check'}`;

        badge.className = `badge ${data.action_badge || 'badge-secondary'}`;
        badge.textContent = data.action_name || data.action;

        document.getElementById('modalDescription').textContent = data.description || '-';
        document.getElementById('modalIpAddress').textContent = data.ip_address || '127.0.0.1';
        document.getElementById('modalBrowserInfo').textContent = data.browser_info || '-';
        document.getElementById('modalMethodBadge').textContent = `HTTP ${data.method || 'GET'}`;
        document.getElementById('modalEndpoint').textContent = `${data.method || 'GET'} ${data.url || '-'}`;

        // Target Entity link
        const targetBox = document.getElementById('modalTargetContainer');
        if (data.target_url) {
            document.getElementById('modalTargetLabel').textContent = data.target_label || 'รายการที่เกี่ยวข้อง';
            document.getElementById('modalTargetLink').href = data.target_url;
            targetBox.style.display = 'block';
        } else {
            targetBox.style.display = 'none';
        }

        // Render Diff
        renderDataDiff(data.old_values, data.new_values);
    })
    .catch(err => {
        diffContainer.innerHTML = '<div class="alert alert-danger" style="margin: 10px 0;">เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + err.message + '</div>';
    });
}

function renderDataDiff(oldVals, newVals) {
    const container = document.getElementById('diffContainer');
    
    if (!oldVals && !newVals) {
        container.innerHTML = `
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 18px; text-align: center; color: #64748b; font-size: 13px;">
                <i class="bi bi-info-circle me-1"></i> ไม่มีข้อมูลการเปลี่ยนแปลงของโครงสร้างข้อมูลในรายการนี้ (เป็นการดำเนินกิจกรรมเชิงระบบ เช่น เข้าสู่ระบบ หรือส่งออกรายงาน)
            </div>
        `;
        return;
    }

    const allKeys = Array.from(new Set([
        ...Object.keys(oldVals || {}),
        ...Object.keys(newVals || {})
    ]));

    if (allKeys.length === 0) {
        container.innerHTML = `
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 18px; text-align: center; color: #64748b; font-size: 13px;">
                ไม่มีข้อมูลฟิลด์ที่บันทึก
            </div>
        `;
        return;
    }

    let rowsHtml = '';
    allKeys.forEach(key => {
        const hasOld = oldVals && oldVals.hasOwnProperty(key);
        const hasNew = newVals && newVals.hasOwnProperty(key);
        const oldVal = hasOld ? formatDiffValue(oldVals[key]) : '<span style="color: #94a3b8;">-</span>';
        const newVal = hasNew ? formatDiffValue(newVals[key]) : '<span style="color: #94a3b8;">-</span>';
        
        const isChanged = hasOld && hasNew && JSON.stringify(oldVals[key]) !== JSON.stringify(newVals[key]);
        const isAdded = !hasOld && hasNew;
        const isDeleted = hasOld && !hasNew;

        let statusBadge = '';
        if (isAdded) {
            statusBadge = '<span class="badge badge-success" style="font-size: 10px; padding: 2px 6px;">+ เพิ่มใหม่</span>';
        } else if (isDeleted) {
            statusBadge = '<span class="badge badge-danger" style="font-size: 10px; padding: 2px 6px;">- ลบออก</span>';
        } else if (isChanged) {
            statusBadge = '<span class="badge badge-warning" style="font-size: 10px; padding: 2px 6px;"><i class="bi bi-pencil-fill" style="font-size: 9px;"></i> แก้ไข</span>';
        } else {
            statusBadge = '<span class="badge badge-secondary" style="font-size: 10px; padding: 2px 6px;">คงเดิม</span>';
        }

        const thTitle = FIELD_DICT[key] ?? null;

        rowsHtml += `
            <tr>
                <td style="width: 28%; vertical-align: top; padding: 8px 12px;">
                    ${thTitle ? `<div style="font-size: 12.5px; font-weight: 600; color: #0f172a;">${thTitle}</div>` : ''}
                    <div style="font-family: monospace; font-size: 11px; color: #64748b;">${key}</div>
                    <div style="margin-top: 3px;">${statusBadge}</div>
                </td>
                <td style="width: 36%; vertical-align: top; padding: 8px 12px; font-size: 12.5px; background: ${isChanged || isDeleted ? '#fef2f2' : 'transparent'}; word-break: break-word;">
                    ${oldVal}
                </td>
                <td style="width: 36%; vertical-align: top; padding: 8px 12px; font-size: 12.5px; background: ${isChanged || isAdded ? '#ecfdf5' : 'transparent'}; word-break: break-word;">
                    ${newVal}
                </td>
            </tr>
        `;
    });

    container.innerHTML = `
        <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
            <table class="table table-bordered mb-0" style="font-size: 13px;">
                <thead style="background: #f1f5f9; color: #475569; font-size: 12px;">
                    <tr>
                        <th style="width: 28%;">ชื่อฟิลด์ (Attribute)</th>
                        <th style="width: 36%; color: #991b1b;"><i class="bi bi-dash-circle me-1"></i> ค่าเดิม (Old Value)</th>
                        <th style="width: 36%; color: #065f46;"><i class="bi bi-plus-circle me-1"></i> ค่าใหม่ (New Value)</th>
                    </tr>
                </thead>
                <tbody>
                    ${rowsHtml}
                </tbody>
            </table>
        </div>
    `;
}

function formatDiffValue(val) {
    if (val === null || val === undefined) {
        return '<span style="color: #94a3b8; font-style: italic;">null</span>';
    }
    if (typeof val === 'boolean') {
        return val ? '<span class="badge badge-success">true (ใช่)</span>' : '<span class="badge badge-secondary">false (ไม่ใช่)</span>';
    }
    if (typeof val === 'object') {
        return `<pre style="margin: 0; font-size: 11px; font-family: monospace; white-space: pre-wrap; background: rgba(0,0,0,0.03); padding: 6px; border-radius: 4px;">${JSON.stringify(val, null, 2)}</pre>`;
    }
    return String(val);
}
</script>
@endpush
