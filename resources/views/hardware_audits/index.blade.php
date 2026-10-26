@extends('layouts.app')

@section('title', 'ติดตามสเปค & ตรวจนับครุภัณฑ์คอมพิวเตอร์ประจำปีงบประมาณ')
@section('page_title', 'ติดตามสเปค & ตรวจนับคอมพิวเตอร์ประจำปีงบประมาณ')
@section('page_subtitle', 'ศูนย์มอนิเตอร์ฮาร์ดแวร์ ตรวจจับสเปคอัตโนมัติจาก Agent และประเมินมาตรฐาน ICT ภาครัฐ พ.ศ. ' . $fiscalYear)

@section('topbar-actions')
<div class="ha-topbar-actions">
    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'map3d']) }}" 
       class="topbar-btn ha-topbar-btn-map" 
       title="เปิดแผนผัง 3 มิติแสดงตำแหน่งคอมพิวเตอร์และสถานะการตรวจนับแต่ละแผนก (3D Department Map Diagram)">
        <i class="bi bi-box-fill text-warning"></i>
        <span class="d-none d-sm-inline">แผนผัง 3D แต่ละแผนก</span>
        <span class="badge" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; font-size: 10px; padding: 2px 6px; border-radius: 99px;">3D</span>
    </a>
    <button type="button" class="topbar-btn ha-topbar-btn-scan" onclick="triggerScanAllConfirm()" 
            title="ส่งคำสั่งไปยัง Agent ทุกเครื่องให้ทำการสแกนสเปคใหม่เดี๋ยวนี้ (Remote Scan All)">
        <i class="bi bi-broadcast-pin text-info"></i>
        <span class="d-none d-sm-inline">สั่งสแกนทุกเครื่อง</span>
        <span class="badge d-none d-md-inline" style="background: rgba(255,255,255,0.25); color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 99px;">
            {{ $agentInstalledCount ?? 0 }}
        </span>
    </button>
    <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" class="topbar-btn" title="พิมพ์รายงานสรุปประจำปีงบประมาณ A4">
        <i class="bi bi-printer text-primary"></i>
        <span class="d-none d-sm-inline">พิมพ์รายงาน A4</span>
    </a>
    <a href="{{ route('hardware-audits.export-csv', ['fiscal_year' => $fiscalYear]) }}" class="topbar-btn" title="ส่งออกไฟล์ข้อมูล Excel/CSV">
        <i class="bi bi-file-earmark-excel text-success"></i>
        <span class="d-none d-sm-inline">ส่งออก CSV</span>
    </a>
</div>
@endsection

@section('content')
<div class="content-container">

    <!-- PAGE HEADER BANNER (RESPONSIVE) -->
    <div class="card ha-hero-banner">
        <!-- Background Pattern Deco -->
        <div class="ha-hero-blob-1"></div>
        <div class="ha-hero-blob-2"></div>

        <div class="ha-hero-content">
            <div class="ha-hero-inner">
                <div class="ha-hero-title-area">
                    <div class="ha-hero-icon">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div class="ha-hero-text">
                        <div class="ha-hero-title-row">
                            <h1 class="ha-hero-title">ติดตามสเปค & ตรวจนับคอมพิวเตอร์ประจำปี</h1>
                            <span class="ha-hero-fy-badge">
                                พ.ศ. {{ $fiscalYear }}
                            </span>
                        </div>
                        <p class="ha-hero-desc">
                            ศูนย์มอนิเตอร์ฮาร์ดแวร์อัตโนมัติ ตรวจสอบความถูกต้องของสเปคเครื่องจริงจาก Agent ประจำเครื่อง และประเมินความสอดคล้องตามเกณฑ์กระทรวงดีอี (MDES)
                        </p>
                    </div>
                </div>

                <!-- Right Action Bar: Scan All Agent Button, Download Standalone Agent & Fiscal Year Form Selector -->
                <div class="ha-hero-actions">
                    <!-- 3D Map Hero Button -->
                    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'map3d']) }}" 
                       class="btn btn-sm ha-hero-btn-map"
                       title="เปิดแผนผัง 3 มิติแสดงสถานะคอมพิวเตอร์ประจำแต่ละแผนก (3D Department Map Diagram)">
                        <i class="bi bi-box-fill text-warning"></i>
                        <span>แผนผัง 3D แผนก</span>
                        <span class="badge bg-warning text-dark">3D</span>
                    </a>

                    <!-- Download Standalone Agent .exe Hero Button -->
                    <a href="{{ url('/agent/THC_IT_Agent.exe') }}" download class="btn btn-sm ha-hero-btn-download"
                       title="ดาวน์โหลดโปรแกรม THC IT Agent (Standalone .exe 68 KB พร้อม Setup Wizard ติดตั้งง่าย มี System Tray Icon)">
                        <i class="bi bi-download text-primary"></i>
                        <span>โหลด Agent (.exe)</span>
                        <span class="badge bg-success text-white">v2.11.0</span>
                    </a>

                    <!-- Pull All Agent Telemetry Hero Button (On-Demand) -->
                    <button type="button" class="btn btn-sm ha-hero-btn-scan" onclick="triggerScanAllConfirm()" 
                            title="ส่งสัญญาณ Remote Scan ไปยัง Agent ทุกเครื่องเพื่อดึงสเปกฮาร์ดแวร์ล่าสุด">
                        <i class="bi bi-cloud-arrow-down-fill text-warning"></i>
                        <span>ดึงข้อมูลทุกเครื่อง</span>
                        <span class="ha-hero-count-pill">
                            {{ $agentInstalledCount ?? 0 }} เครื่อง
                        </span>
                    </button>

                    <!-- Fiscal Year Form Selector -->
                    <div class="ha-hero-fy-box">
                        <form method="GET" action="{{ route('hardware-audits.index') }}" id="fiscalYearForm">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <i class="bi bi-calendar-check text-white opacity-75"></i>
                            <label for="fiscal_year">ปีงบ:</label>
                            <select name="fiscal_year" id="fiscal_year" class="form-select form-select-sm" onchange="document.getElementById('fiscalYearForm').submit()">
                                @foreach($availableFiscalYears as $fy)
                                    <option value="{{ $fy }}" {{ $fiscalYear == $fy ? 'selected' : '' }}>พ.ศ. {{ $fy }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 TOP KPI STATISTICS CARDS (RESPONSIVE GRID) -->
    <div class="ha-stat-grid">
        
        <!-- CARD 1: Total Fleet -->
        <div class="card stat-card ha-stat-card" style="border: 1px solid #e2e8f0; background: #ffffff;">
            <div class="ha-stat-inner">
                <div class="ha-stat-info">
                    <div class="ha-stat-label" style="color: #64748b;">เป้าหมายสำรวจทั้งหมด</div>
                    <div class="ha-stat-value" style="color: #0f172a;">
                        {{ number_format($totalFleet) }} <span class="ha-stat-unit">เครื่อง</span>
                    </div>
                </div>
                <div class="ha-stat-icon" style="background: #e0f2fe; color: #0284c7;">
                    <i class="bi bi-pc-display"></i>
                </div>
            </div>
            <div class="ha-stat-footer" style="color: #94a3b8;">
                <i class="bi bi-building me-1"></i> คอมพิวเตอร์สถานะปกติใน รพ.
            </div>
        </div>

        <!-- CARD 2: Audited This FY -->
        <div class="card stat-card ha-stat-card" style="border: 1px solid #bbf7d0; background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);">
            <div class="ha-stat-inner">
                <div class="ha-stat-info">
                    <div class="ha-stat-label" style="color: #166534;">ตรวจนับ/อัปเดตสเปคแล้ว</div>
                    <div class="ha-stat-value" style="color: #15803d;">
                        {{ number_format($auditedCount) }} 
                        <span class="ha-stat-percentage" style="color: #166534;">({{ $auditedPercentage }}%)</span>
                    </div>
                </div>
                <div class="ha-stat-icon" style="background: #dcfce7; color: #16a34a;">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
            <div class="ha-stat-progress-wrap">
                <div class="progress" style="height: 6px; background: #d1fae5; border-radius: 99px; overflow: hidden;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $auditedPercentage) }}%; border-radius: 99px;"></div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Pending Submissions -->
        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending', 'status' => 'pending']) }}" style="text-decoration: none; color: inherit; display: block;">
            <div class="card stat-card ha-stat-card" style="border: 1px solid {{ $pendingCount > 0 ? '#fde047' : '#e2e8f0' }}; background: {{ $pendingCount > 0 ? 'linear-gradient(180deg, #ffffff 0%, #fefce8 100%)' : '#ffffff' }}; cursor: pointer;">
                <div class="ha-stat-inner">
                    <div class="ha-stat-info">
                        <div class="ha-stat-label" style="color: #854d0e;">รอแอดมินกดอนุมัติ</div>
                        <div class="ha-stat-value" style="color: #ca8a04;">
                            {{ number_format($pendingCount) }} <span class="ha-stat-unit" style="color: #854d0e;">รายการ</span>
                        </div>
                    </div>
                    <div class="ha-stat-icon" style="background: #fef9c3; color: #ca8a04;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="ha-stat-footer" style="color: #a16207; display: flex; align-items: center; justify-content: space-between;">
                    <span>สแกนเข้ามาแล้ว รอยืนยัน</span>
                    <i class="bi bi-arrow-right-short" style="font-size: 16px;"></i>
                </div>
            </div>
        </a>

        <!-- CARD 4: Remaining Not Audited -->
        <div class="card stat-card ha-stat-card" style="border: 1px solid #e2e8f0; background: #ffffff;">
            <div class="ha-stat-inner">
                <div class="ha-stat-info">
                    <div class="ha-stat-label" style="color: #64748b;">คงเหลือยังไม่ได้สำรวจ</div>
                    <div class="ha-stat-value" style="color: #334155;">
                        {{ number_format($notAuditedCount) }} <span class="ha-stat-unit">เครื่อง</span>
                    </div>
                </div>
                <div class="ha-stat-icon" style="background: #f1f5f9; color: #475569;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div class="ha-stat-footer" style="color: #94a3b8;">
                <i class="bi bi-tools me-1"></i> ต้องส่ง Agent หรือเดินสำรวจ
            </div>
        </div>

        <!-- CARD 5: MDES Upgrade Warnings -->
        <div class="card stat-card ha-stat-card ha-stat-card-warning" style="border: 1px solid #fecaca; background: linear-gradient(180deg, #ffffff 0%, #fff5f5 100%);">
            <div class="ha-stat-inner">
                <div class="ha-stat-info">
                    <div class="ha-stat-label" style="color: #991b1b;">เตือนสเปคต่ำกว่าเกณฑ์ ICT</div>
                    <div class="ha-stat-value" style="color: #dc2626;">
                        {{ number_format($mdesWarningCount) }} <span class="ha-stat-unit" style="color: #991b1b;">เครื่อง</span>
                    </div>
                </div>
                <div class="ha-stat-icon" style="background: #fee2e2; color: #dc2626;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
            <div class="ha-stat-footer" style="color: #ef4444;">
                <i class="bi bi-info-circle me-1"></i> RAM &lt; 8GB / ไดรฟ์ HDD / Win 7
            </div>
        </div>
    </div>

    <!-- HARDWARE AUDIT COMMAND & TOOLKIT HUB (ศูนย์รวมเครื่องมือไอทีครบวงจร) -->
    <div class="card ha-toolkit-card">
        <div class="ha-toolkit-header">
            <div class="ha-toolkit-title-group">
                <div class="ha-toolkit-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <h3 class="ha-toolkit-heading">
                        ศูนย์รวมเครื่องมือ & คำสั่งระบบติดตามสเปคคอมพิวเตอร์ (IT Command Center)
                    </h3>
                    <p class="ha-toolkit-sub">
                        รวมเครื่องมือสั่งการระยะไกล ตรวจจับสเปคฮาร์ดแวร์ แผนผัง 3 มิติ และการส่งออกข้อมูลสำหรับฝ่ายไอที
                    </p>
                </div>
            </div>
            <div class="ha-toolkit-header-badges">
                <span class="ha-badge-pill bg-light border text-dark">
                    <i class="bi bi-hdd-network text-primary me-1"></i> รวม <strong>{{ $totalFleet }}</strong> เครื่อง
                </span>
                <span class="ha-badge-pill bg-success-subtle border border-success-subtle text-success">
                    <i class="bi bi-circle-fill text-success me-1" style="font-size: 8px;"></i> ออนไลน์ <strong id="toolkitOnlineBadge">{{ $agentOnlineCount }}</strong> เครื่อง
                </span>
            </div>
        </div>

        <div class="ha-toolkit-body">
            <!-- Group 1: Remote Operations -->
            <div class="ha-tool-group">
                <div class="ha-tool-group-label">
                    <i class="bi bi-broadcast-pin text-info me-1"></i> คำสั่งระยะไกล (Remote Commands)
                </div>
                <div class="ha-tool-group-buttons">
                    <button type="button" class="btn btn-sm ha-tool-btn ha-tool-btn-primary" onclick="triggerScanAllConfirm()" 
                            title="ส่งสัญญาณ Remote Scan ไปยัง Agent ทุกเครื่องเพื่อดึงสเปกฮาร์ดแวร์ล่าสุด">
                        <i class="bi bi-cloud-arrow-down-fill text-warning"></i>
                        <span>สั่งสแกนทุกเครื่อง</span>
                        <span class="ha-tool-pill">{{ $agentInstalledCount ?? 0 }}</span>
                    </button>

                    <button type="button" class="btn btn-sm ha-tool-btn ha-tool-btn-success" onclick="triggerAllOnlineFleetScan()"
                            title="ส่งคำสั่งดึงข้อมูลเฉพาะเครื่องที่กำลังออนไลน์ขณะนี้">
                        <i class="bi bi-broadcast text-success"></i>
                        <span>ดึงเครื่องออนไลน์</span>
                        <span class="ha-tool-pill bg-success text-white">{{ $agentOnlineCount ?? 0 }}</span>
                    </button>

                    @if(($agentOutdatedCount ?? 0) > 0)
                    <button type="button" class="btn btn-sm ha-tool-btn ha-tool-btn-warning" onclick="triggerUpdateAllOutdatedFleet()"
                            title="ส่งคำสั่งอัปเดต Agent เครื่องเก่าทั้งหมดเป็นเวอร์ชัน v2.11.0 อัตโนมัติ">
                        <i class="bi bi-arrow-repeat text-white"></i>
                        <span>อัปเดตเครื่องเก่า</span>
                        <span class="ha-tool-pill bg-warning text-dark">{{ $agentOutdatedCount }}</span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Group 2: Visualization & 3D Diagram -->
            <div class="ha-tool-group">
                <div class="ha-tool-group-label">
                    <i class="bi bi-box-fill text-warning me-1"></i> แผนผัง & ผังองค์กร (Diagrams)
                </div>
                <div class="ha-tool-group-buttons">
                    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'map3d']) }}" 
                       class="btn btn-sm ha-tool-btn ha-tool-btn-map {{ $tab === 'map3d' ? 'active' : '' }}"
                       title="เปิดแผนผัง 3 มิติแสดงตำแหน่งคอมพิวเตอร์และสถานะการตรวจนับแต่ละแผนก (3D Department Map Diagram)">
                        <i class="bi bi-box-fill text-warning"></i>
                        <span>แผนผัง 3D แต่ละแผนก</span>
                        <span class="ha-tool-pill bg-warning text-dark">3D</span>
                    </a>

                    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'departments']) }}" 
                       class="btn btn-sm ha-tool-btn ha-tool-btn-secondary {{ $tab === 'departments' ? 'active' : '' }}"
                       title="ติดตามความคืบหน้าการสำรวจแยกตามแผนก/หน่วยงาน">
                        <i class="bi bi-diagram-3-fill text-primary"></i>
                        <span>ติดตามรายแผนก</span>
                        <span class="ha-tool-pill">{{ count($departments) }}</span>
                    </a>
                </div>
            </div>

            <!-- Group 3: Downloads, Reports & Integrations -->
            <div class="ha-tool-group">
                <div class="ha-tool-group-label">
                    <i class="bi bi-printer-fill text-secondary me-1"></i> รายงาน & ติดตั้ง (Reports & Deploy)
                </div>
                <div class="ha-tool-group-buttons">
                    <a href="{{ url('/agent/THC_IT_Agent.exe') }}" download class="btn btn-sm ha-tool-btn ha-tool-btn-download"
                       title="ดาวน์โหลดโปรแกรม Standalone Agent v2.12.0 มี GUI Dashboard ในตัว">
                        <i class="bi bi-download text-primary"></i>
                        <span>โหลด Agent (.exe)</span>
                        <span class="ha-tool-pill bg-primary text-white">v2.12.0</span>
                    </a>

                    <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" 
                       class="btn btn-sm ha-tool-btn ha-tool-btn-secondary" title="พิมพ์รายงานสรุปผลการสำรวจประจำปี A4">
                        <i class="bi bi-printer text-primary"></i>
                        <span>พิมพ์รายงาน A4</span>
                    </a>

                    <a href="{{ route('hardware-audits.export-csv', ['fiscal_year' => $fiscalYear]) }}" 
                       class="btn btn-sm ha-tool-btn ha-tool-btn-secondary" title="ส่งออกข้อมูลผลการตรวจนับเป็นไฟล์ Excel/CSV">
                        <i class="bi bi-file-earmark-excel text-success"></i>
                        <span>ส่งออก CSV</span>
                    </a>

                    <a href="{{ url('/api/hardware-audit/submit') }}" target="_blank" 
                       class="btn btn-sm ha-tool-btn ha-tool-btn-api" title="ตรวจสอบสถานะ Telemetry API & Webhook Service">
                        <i class="bi bi-activity text-info"></i>
                        <span>สถานะ API</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN INTERACTIVE SECTION WITH MODERN TABS -->
    <div class="card ha-main-card" style="border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        
        <!-- MODERN SEGMENTED TABS BAR -->
        <div class="ha-tabs-bar" role="tablist" aria-label="หมวดหมู่การตรวจสอบอุปกรณ์">
            {{-- TAB 1: PENDING QUEUE --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending']) }}" 
               class="tab-nav-btn {{ $tab === 'pending' ? 'active' : '' }}"
               title="รายการรอตรวจสอบและอนุมัติ">
                <i class="bi bi-inbox-fill"></i>
                <span class="tab-label">รอตรวจสอบ</span>
                @if($pendingCount > 0)
                    <span class="ha-tab-badge badge-pending">{{ $pendingCount }}</span>
                @endif
            </a>

            {{-- TAB 2: AGENT FLEET --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'agents']) }}" 
               class="tab-nav-btn {{ $tab === 'agents' ? 'active' : '' }}"
               title="เครื่องคอมพิวเตอร์ที่ติดตั้ง Agent">
                <i class="bi bi-display-fill"></i>
                <span class="tab-label">เครื่อง Agent</span>
                <span class="ha-tab-badge {{ $agentOnlineCount > 0 ? 'badge-agent' : 'badge-slate' }}">
                    <span id="tabAgentOnlineCount">{{ $agentOnlineCount }}</span>/{{ $agentTotalCount }}
                </span>
            </a>

            {{-- TAB 3: 3D DEPARTMENT MAP DIAGRAM --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'map3d']) }}" 
               class="tab-nav-btn tab-map3d {{ $tab === 'map3d' ? 'active' : '' }}"
               title="แผนผัง 3D แสดงตำแหน่งคอมพิวเตอร์ในแต่ละแผนก">
                <i class="bi bi-box-fill"></i>
                <span class="tab-label">ผัง 3D แผนก</span>
                <span class="ha-tab-badge badge-3d">3D</span>
            </a>

            {{-- TAB 4: DEPARTMENT TRACKER --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'departments']) }}" 
               class="tab-nav-btn {{ $tab === 'departments' ? 'active' : '' }}"
               title="ติดตามความคืบหน้ารายแผนก">
                <i class="bi bi-diagram-3-fill"></i>
                <span class="tab-label">สรุปรายแผนก</span>
                <span class="ha-tab-badge badge-count">{{ count($departments) }}</span>
            </a>

            {{-- TAB 5: ANNUAL REPORT & CHARTS --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'report']) }}" 
               class="tab-nav-btn {{ $tab === 'report' ? 'active' : '' }}"
               title="รายงานสถิติและการวิเคราะห์สเปค">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span class="tab-label">สถิติ & กราฟ</span>
            </a>

            {{-- TAB 6: AGENT DEPLOYMENT HUB --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'deploy']) }}" 
               class="tab-nav-btn {{ $tab === 'deploy' ? 'active' : '' }}"
               title="ตัวติดตั้ง Agent และคำแนะนำการกระจายสคริปต์">
                <i class="bi bi-terminal-fill"></i>
                <span class="tab-label">ติดตั้ง Agent</span>
                <span class="ha-tab-badge badge-version">v2.12.0</span>
            </a>
        </div>

        <!-- TAB CONTENT 1: PENDING QUEUE (RESPONSIVE) -->
        @if($tab === 'pending')
        <div class="ha-tab-pane" style="padding: 22px;">
            
            <!-- Filter & Batch Action Toolbar -->
            <div class="ha-filter-toolbar">
                <form method="GET" action="{{ route('hardware-audits.index') }}" class="ha-filter-form">
                    <input type="hidden" name="fiscal_year" value="{{ $fiscalYear }}">
                    <input type="hidden" name="tab" value="pending">
                    
                    <div class="ha-filter-inputs">
                        <div class="ha-search-box">
                            <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา Hostname, S/N, IP, CPU..." 
                                   value="{{ request('search') }}" style="padding-left: 32px; border-radius: 8px; height: 38px; font-size: 13px;">
                        </div>

                        <select name="status" class="form-select form-select-sm ha-status-select" style="border-radius: 8px; height: 38px; font-size: 13px;">
                            <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>⏳ รอตรวจสอบ ({{ $pendingCount }})</option>
                            <option value="unlinked" {{ request('status') === 'unlinked' ? 'selected' : '' }}>🆕 เครื่องใหม่ยังไม่ผูก ({{ $unlinkedCount }})</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✓ อนุมัติแล้ว ({{ $approvedCount }})</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>✕ ปฏิเสธแล้ว ({{ $rejectedCount }})</option>
                            <option value="" {{ request('status') === '' ? 'selected' : '' }}>📋 ทุกสถานะ</option>
                        </select>

                        <div class="ha-filter-btn-group">
                            <button type="submit" class="btn btn-primary btn-sm" style="height: 38px; padding: 0 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <i class="bi bi-funnel"></i> คัดกรอง
                            </button>

                            @if(request('search') || (request('status') && request('status') !== 'pending'))
                                <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending']) }}" class="btn btn-outline-secondary btn-sm" style="height: 38px; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="bi bi-x-circle"></i> ล้าง
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Batch Actions Trigger --}}
                    @if(request('status', 'pending') === 'pending' && $audits->count() > 0)
                    <div class="ha-batch-actions">
                        <button type="button" class="btn btn-success btn-sm" onclick="submitBatchApproval()" style="height: 38px; padding: 0 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                            <i class="bi bi-check2-all" style="font-size: 15px;"></i> 
                            <span>อนุมัติที่เลือก (Batch)</span>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="submitBatchDelete()" style="height: 38px; padding: 0 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: #ffffff;">
                            <i class="bi bi-trash" style="font-size: 14px;"></i> 
                            <span>ลบที่เลือก</span>
                        </button>
                    </div>
                    @endif
                </form>
            </div>

            <!-- Table of Submissions -->
            <form id="batchApproveForm" method="POST" action="{{ route('hardware-audits.batch-approve') }}">
                @csrf
                <div class="table-responsive ha-responsive-table-wrapper" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    <table class="table table-hover align-middle mb-0 ha-responsive-table ha-pending-table" style="font-size: 13px;">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
                            <tr>
                                <th style="width: 38px; text-align: center;">
                                    <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)" style="cursor: pointer;">
                                </th>
                                <th style="min-width: 135px;">สถานะ Agent / รับข้อมูล</th>
                                <th style="min-width: 140px;">ชื่อเครื่อง (Hostname / IP)</th>
                                <th style="width: 140px;">ยี่ห้อ / รุ่น</th>
                                <th style="width: 140px;">HardwareID / S/N</th>
                                <th style="width: 165px;">ครุภัณฑ์ที่จับคู่</th>
                                <th style="min-width: 180px;">สเปคจาก Agent</th>
                                <th style="text-align: center; width: 95px;">เปลี่ยนแปลง</th>
                                <th style="text-align: center; width: 85px;">สถานะ</th>
                                <th style="text-align: right; width: 310px; min-width: 310px;">ดึงข้อมูล / จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audits as $audit)
                            @php
                                $isUnderMdes = $audit->isUnderMdesStandard();
                            @endphp
                            <tr class="ha-audit-row" style="{{ $audit->status === 'pending' ? 'background: #ffffff;' : 'background: #fbfcfe;' }}">
                                <td class="ha-col-select" style="text-align: center;">
                                    @if($audit->status === 'pending')
                                        <input type="checkbox" name="audit_ids[]" value="{{ $audit->id }}" class="audit-select-chk" style="cursor: pointer;">
                                    @else
                                        <i class="bi bi-dash text-muted"></i>
                                    @endif
                                </td>
                                <td class="ha-col-telemetry" style="color: #64748b; font-size: 12px; white-space: nowrap;" id="agent-status-cell-{{ $audit->id }}">
                                    <div id="telemetry-badge-{{ $audit->id }}">
                                        {!! $audit->agent_telemetry_badge !!}
                                    </div>
                                    <div style="font-weight: 600; color: #475569; font-size: 11px; margin-top: 3px;" id="telemetry-time-{{ $audit->id }}">
                                        <i class="bi bi-clock me-1 text-muted"></i>{{ $audit->updated_at ? $audit->updated_at->format('d/m/Y H:i น.') : '-' }}
                                    </div>
                                </td>
                                <td class="ha-col-hostname">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $audit->status === 'approved' ? '#10b981' : ($audit->status === 'rejected' ? '#94a3b8' : '#f59e0b') }}; display: inline-block;"></span>
                                        <strong style="color: #0f172a; font-size: 13.5px; font-family: monospace;">{{ $audit->hostname ?: '-' }}</strong>
                                    </div>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                                        <i class="bi bi-hdd-network me-1"></i>IP: <code style="color: #0284c7; background: #f0f9ff; padding: 1px 4px; border-radius: 4px;">{{ $audit->ip_address ?: '-' }}</code>
                                    </div>
                                    @if($audit->mac_address)
                                        <div style="font-size: 10.5px; color: #94a3b8; margin-top: 1px;">
                                            MAC: <code style="color: #64748b;">{{ $audit->mac_address }}</code>
                                        </div>
                                    @endif
                                </td>

                                {{-- Brand & Model Column --}}
                                <td class="ha-col-model">
                                    @if($audit->brand || $audit->model)
                                        <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px; font-weight: 700;">
                                                {{ $audit->brand ?: 'ไม่ระบุยี่ห้อ' }}
                                            </span>
                                            <span style="font-weight: 600; color: #334155; font-size: 12.5px;">{{ $audit->model }}</span>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                            <span class="badge bg-light text-secondary border" style="font-size: 10px;">{{ $audit->device_type_code ?? 'PC' }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 12px;">(ไม่ระบุยี่ห้อ/รุ่น)</span>
                                    @endif
                                </td>

                                {{-- HardwareID & Serial Number Column --}}
                                <td class="ha-col-hwid">
                                    @if($audit->hardware_id)
                                        <div style="margin-bottom: 3px;">
                                            <span class="badge" style="background: #fdf4ff; color: #a21caf; border: 1px solid #f0abfc; font-size: 10px; font-weight: 700; font-family: monospace;" title="HardwareID: {{ $audit->hardware_id }}">
                                                <i class="bi bi-fingerprint me-1"></i>HWID: {{ Str::limit($audit->hardware_id, 13) }}
                                            </span>
                                        </div>
                                    @endif
                                    <code style="font-size: 11px; color: #0f766e; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 2px 6px; border-radius: 6px; font-weight: 700; display: inline-block;">
                                        {{ $audit->serial_number ?: '(ไม่พบ S/N)' }}
                                    </code>
                                </td>

                                {{-- Matched Asset Column --}}
                                <td class="ha-col-asset">
                                    @if($audit->asset)
                                        <div>
                                            <a href="{{ route('assets.show', $audit->asset_id) }}" target="_blank" style="font-weight: 700; color: #0f766e; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                <span>{{ $audit->asset->asset_code }}</span>
                                                <i class="bi bi-box-arrow-up-right" style="font-size: 10px;"></i>
                                            </a>
                                            @if($audit->hardware_id && $audit->asset->hardware_id === $audit->hardware_id)
                                                <span class="badge" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 9.5px; font-weight: 600; padding: 1px 5px; border-radius: 4px;" title="จับคู่แม่นยำด้วย HardwareID">
                                                    <i class="bi bi-link-45deg"></i> เชื่อมด้วย HWID
                                                </span>
                                            @endif
                                            @if($audit->asset->status === 'borrowed')
                                                <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 9.5px; font-weight: 600; padding: 1px 5px; border-radius: 4px;" title="อุปกรณ์นี้กำลังถูกยืมใช้งาน">
                                                    <i class="bi bi-arrow-left-right"></i> กำลังถูกยืม
                                                </span>
                                            @endif
                                        </div>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $audit->asset->department?->name ?? 'ไม่ระบุแผนก' }}
                                        </div>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                            <span class="badge" style="background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px;">
                                                <i class="bi bi-sparkles me-1"></i>🆕 ตรวจพบเครื่องใหม่
                                            </span>
                                            <button type="button" class="btn btn-sm" onclick="openCreateAssetModal({{ json_encode($audit) }})"
                                                    style="font-size: 11px; padding: 2px 8px; border-radius: 6px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; font-weight: 600; border: none; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(13,148,136,0.2);">
                                                <i class="bi bi-plus-circle-fill"></i> เพิ่มเป็นครุภัณฑ์ใหม่
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                {{-- Scanned Specs Formatted --}}
                                <td class="ha-col-specs">
                                    <div style="font-weight: 700; color: #0f172a; font-size: 12.5px; display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                        <i class="bi bi-cpu text-primary"></i>
                                        <span>{{ $audit->cpu_model ?: '-' }}</span>
                                        @if($audit->cpu_speed)
                                            <span class="badge" style="background: #f0fdfa; color: #0d9488; border: 1px solid #ccfbf1; font-size: 10px; font-weight: 600;">{{ $audit->cpu_speed }}</span>
                                        @endif
                                    </div>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px;">
                                        <span class="badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 11px; font-weight: 600;" title="{{ $audit->ram_slots }}">
                                            RAM {{ $audit->ram_capacity }}GB {{ $audit->ram_type }} {{ $audit->ram_bus ? "({$audit->ram_bus})" : '' }}
                                        </span>
                                        <span class="badge" style="background: {{ stripos($audit->storage_type, 'SSD') !== false ? '#dcfce7' : '#fef3c7' }}; color: {{ stripos($audit->storage_type, 'SSD') !== false ? '#15803d' : '#b45309' }}; border: 1px solid {{ stripos($audit->storage_type, 'SSD') !== false ? '#bbf7d0' : '#fde68a' }}; font-size: 11px; font-weight: 600;">
                                            {{ $audit->storage_type }} {{ $audit->storage_capacity }}
                                        </span>
                                        @if($audit->storage_second)
                                            <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 10px; font-weight: 600;" title="ไดรฟ์เสริม: {{ $audit->storage_second }}">
                                                <i class="bi bi-hdd me-1"></i>+ {{ $audit->storage_second }}
                                            </span>
                                        @endif
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-windows text-info"></i> <span>{{ $audit->os_name }}</span>
                                    </div>
                                </td>

                                {{-- Diff Status --}}
                                <td class="ha-col-diff" style="text-align: center;">
                                    @if($audit->hasDiff())
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 11px; font-weight: 600;">
                                            <i class="bi bi-arrow-left-right me-1"></i> {{ $audit->diff_count }} จุดเปลี่ยน
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px;">ตรงกับคลัง</span>
                                    @endif

                                    @if($isUnderMdes)
                                        <div style="margin-top: 4px;">
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 10px;" title="สเปคต่ำกว่าเกณฑ์ ICT (RAM < 8GB หรือไดรฟ์ HDD)">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> ต่ำกว่าเกณฑ์
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <td class="ha-col-status" style="text-align: center;">
                                    {!! $audit->status_badge !!}
                                </td>

                                <td class="ha-col-actions" style="text-align: right;">
                                    <div style="display: flex; gap: 4px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                                        
                                        <!-- ACTION BUTTONS: Approve / Reject / Link (Only for pending) -->
                                        @if($audit->status === 'pending')
                                            @if(!$audit->asset_id)
                                                <button type="button" class="btn btn-sm" onclick="openLinkAssetModal({{ json_encode($audit) }})" 
                                                        title="ค้นหาและเชื่อมโยงผลตรวจสเปคเข้ากับครุภัณฑ์เดิมที่มีในระบบ (1:1 เครื่อง โดยแอดมินเท่านั้น)" 
                                                        style="background: #0d9488; color: #ffffff; font-size: 11.5px; padding: 4px 8px; border-radius: 7px; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 1px 3px rgba(13,148,136,0.25); flex-shrink: 0;">
                                                    <i class="bi bi-link-45deg"></i>
                                                    <span>ผูกครุภัณฑ์ (1:1)</span>
                                                </button>
                                                <button type="button" class="btn btn-sm" onclick="openCreateAssetModal({{ json_encode($audit) }})" 
                                                        title="ลงทะเบียนและเพิ่มเป็นครุภัณฑ์ใหม่ลงระบบคลังทันที" 
                                                        style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%); color: #ffffff; font-size: 11.5px; padding: 4px 8px; border-radius: 7px; font-weight: 700; border: none; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 1px 3px rgba(5,150,105,0.25); flex-shrink: 0;">
                                                    <i class="bi bi-plus-circle-fill"></i>
                                                    <span>+ ครุภัณฑ์</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-success" onclick="approveSingleAudit({{ $audit->id }}, '{{ addslashes($audit->hostname ?: '-') }}')" 
                                                        title="อนุมัติและอัปเดตสเปคลงครุภัณฑ์ทันที" style="font-size: 11.5px; padding: 4px 9px; border-radius: 7px; font-weight: 700; display: inline-flex; align-items: center; gap: 3px; flex-shrink: 0; box-shadow: 0 1px 3px rgba(16,185,129,0.3);">
                                                    <i class="bi bi-check-lg"></i>
                                                    <span>อนุมัติ</span>
                                                </button>
                                                <button type="button" class="btn btn-sm" onclick="unlinkSingleAudit({{ $audit->id }}, '{{ addslashes($audit->hostname ?: '-') }}')"
                                                        title="ปลดการผูกครุภัณฑ์ เพื่อผูกใหม่ 1:1" 
                                                        style="background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; font-size: 11px; padding: 3px 6px; border-radius: 6px; font-weight: 600; display: inline-flex; align-items: center; gap: 2px;">
                                                    <i class="bi bi-link-45deg"></i> ปลดผูก
                                                </button>
                                            @endif
                                        @endif

                                        <!-- BUTTON 1: Open Agent Inspector Modal -->
                                        <button type="button" class="btn btn-sm" onclick="openAgentInspectionModal({{ $audit->id }})" 
                                                title="เปิดดูผลการตรวจเช็คสเปคฮาร์ดแวร์เชิงลึก และผลตรวจเกณฑ์มาตรฐาน ICT" 
                                                style="background: #0284c7; color: #ffffff; font-size: 11.5px; padding: 4px 8px; border-radius: 7px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; box-shadow: 0 1px 3px rgba(2,132,199,0.25); border: none; flex-shrink: 0;">
                                            <i class="bi bi-cpu-fill"></i>
                                            <span>สเปค</span>
                                        </button>

                                        <!-- BUTTON 0: Pull Telemetry from this machine (On-Demand Icon) -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-scan-{{ $audit->id }}"
                                                onclick="triggerSingleScan({{ $audit->id }}, '{{ addslashes($audit->hostname ?: '-') }}')"
                                                title="ดึงข้อมูลสเปคล่าสุดจาก Agent บนเครื่องนี้เดี๋ยวนี้"
                                                style="width: 29px; height: 29px; padding: 0; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f0f9ff; border-color: #7dd3fc; color: #0284c7;">
                                            <i class="bi bi-cloud-arrow-down-fill" style="font-size: 13px;"></i>
                                        </button>

                                        <!-- BUTTON 2: Side-by-Side Diff Comparison Modal (Icon) -->
                                        @if($audit->asset_id)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openAuditDiffModal({{ json_encode($audit) }}, {{ json_encode($audit->asset) }})" 
                                                title="เปรียบเทียบสเปคเดิม vs สเปคใหม่ (Diff)" style="width: 29px; height: 29px; padding: 0; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="bi bi-arrow-left-right" style="font-size: 12px;"></i>
                                        </button>
                                        @if($audit->status !== 'pending')
                                        <button type="button" class="btn btn-sm" onclick="unlinkSingleAudit({{ $audit->id }}, '{{ addslashes($audit->hostname ?: '-') }}')" 
                                                title="ปลดการผูกครุภัณฑ์ของเครื่องนี้ เพื่อผูกใหม่ 1:1" 
                                                style="width: 29px; height: 29px; padding: 0; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fff1f2; border: 1px solid #fecdd3; color: #e11d48;">
                                            <i class="bi bi-slash-circle" style="font-size: 12px;"></i>
                                        </button>
                                        @endif
                                        @endif

                                        <!-- ACTION BUTTON: Reject (Icon) -->
                                        @if($audit->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="rejectSingleAudit({{ $audit->id }}, '{{ addslashes($audit->hostname ?: '-') }}')" 
                                                title="ปฏิเสธรายการนี้" style="width: 29px; height: 29px; padding: 0; border-radius: 7px; border-color: #fcd34d; color: #b45309; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="bi bi-x-lg" style="font-size: 12px;"></i>
                                        </button>
                                        @endif

                                        <!-- ACTION BUTTON: Delete (Icon with Confirmation) -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSingleAudit({{ $audit->id }}, '{{ addslashes($audit->hostname ?: ($audit->asset ? $audit->asset->name : 'เครื่อง ID #' . $audit->id)) }}')" 
                                                title="ลบเฉพาะข้อมูลสเปกที่ได้รับนี้ (ไม่กระทบครุภัณฑ์ในคลัง)" style="width: 29px; height: 29px; padding: 0; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; border-color: #fca5a5;">
                                            <i class="bi bi-trash" style="font-size: 13px;"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 50px 20px; color: #94a3b8;">
                                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 12px auto; color: #94a3b8;">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <div style="font-weight: 700; font-size: 15px; color: #475569;">ไม่พบรายการส่งผลสแกนในเงื่อนไขนี้</div>
                                    <div style="font-size: 12.5px; color: #94a3b8; margin-top: 4px;">
                                        สามารถติดตั้ง Agent หรือสั่งรันคำสั่ง PowerShell จากเครื่องลูกข่าย เพื่อส่งผลสแกนเข้ามาที่คิวได้ทันที
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="mt-3" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 12.5px; color: #64748b;">
                    แสดงผล {{ $audits->firstItem() ?? 0 }} - {{ $audits->lastItem() ?? 0 }} จากทั้งหมด {{ $audits->total() }} รายการ
                </div>
                <div>
                    {{ $audits->links() }}
                </div>
            </div>
        </div>
        @endif

        <!-- TAB CONTENT: AGENT FLEET (RESPONSIVE) -->
        @if($tab === 'agents')
        <div class="ha-tab-pane" style="padding: 22px;">
            <!-- Header Summary & Quick Controls -->
            <div class="ha-fleet-header">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-display-fill text-primary"></i> 
                        <span>รายการเครื่องที่ติดตั้ง Agent (Agent Fleet)</span>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 8px;">
                            {{ $agentTotalCount }} เครื่อง
                        </span>
                    </h3>
                    <p style="font-size: 12.5px; color: #64748b; margin: 4px 0 0 0;">
                        มอนิเตอร์สถานะการออนไลน์ และสามารถเลือกเครื่องที่ต้องการเพื่อสั่งดึงข้อมูลสเปกปัจจุบันเข้าสู่ระบบได้ทันที
                    </p>
                </div>

                <!-- Live Status & Version Indicators -->
                <div class="ha-fleet-indicators">
                    <div class="ha-indicator-pill" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 2px rgba(16,185,129,0.3);"></span>
                        <span>ออนไลน์:</span>
                        <strong id="fleetOnlineCountDisplay">{{ $agentOnlineCount }}</strong>
                    </div>
                    <div class="ha-indicator-pill" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569;">
                        <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #94a3b8;"></span>
                        <span>ออฟไลน์:</span>
                        <strong>{{ $agentOfflineCount }}</strong>
                    </div>
                    <div class="ha-indicator-pill" style="background: #f0fdf4; border: 1px solid #86efac; color: #166534;">
                        <i class="bi bi-shield-check text-success"></i>
                        <span>v2.11.0 ล่าสุด:</span>
                        <strong>{{ $agentLatestCount ?? 0 }}</strong>
                    </div>
                    @if(($agentOutdatedCount ?? 0) > 0)
                    <div class="ha-indicator-pill" style="background: #fffbeb; border: 1px solid #fcd34d; color: #b45309; font-weight: 700;">
                        <i class="bi bi-arrow-repeat text-warning"></i>
                        <span>เวอร์ชันเก่า:</span>
                        <strong>{{ $agentOutdatedCount }}</strong> เครื่อง
                    </div>
                    @endif
                </div>
            </div>

            <!-- Toolbar: Search, Filter, Multi-select Action -->
            <div class="ha-fleet-toolbar">
                <div class="ha-fleet-toolbar-left">
                    <!-- Live Search Input -->
                    <div class="ha-fleet-search-box">
                        <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                        <input type="text" id="fleetSearchInput" onkeyup="filterFleetRows()" placeholder="ค้นหา Hostname, IP, MAC, ครุภัณฑ์..." 
                               class="form-control form-control-sm" style="padding-left: 32px; border-radius: 8px; height: 38px; font-size: 13px;">
                    </div>

                    <!-- Filter Buttons -->
                    <div class="btn-group btn-group-sm ha-fleet-filter-group" role="group">
                        <button type="button" class="btn btn-outline-secondary active" id="fleetFilterAll" onclick="setFleetFilter('all')">
                            ทั้งหมด ({{ $agentTotalCount }})
                        </button>
                        <button type="button" class="btn btn-outline-success" id="fleetFilterOnline" onclick="setFleetFilter('online')">
                            🟢 ออนไลน์ ({{ $agentOnlineCount }})
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="fleetFilterOffline" onclick="setFleetFilter('offline')">
                            ⚪ ออฟไลน์ ({{ $agentOfflineCount }})
                        </button>
                        @if(($agentOutdatedCount ?? 0) > 0)
                        <button type="button" class="btn btn-outline-warning" id="fleetFilterOutdated" onclick="setFleetFilter('outdated')" style="font-weight: 700; color: #b45309;">
                            🟠 ต้องอัปเดต ({{ $agentOutdatedCount }})
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Batch Actions for Fleet -->
                <div class="ha-fleet-actions">
                    @if(($agentOutdatedCount ?? 0) > 0)
                    <button type="button" onclick="triggerUpdateAllOutdatedFleet()" class="btn btn-warning btn-sm"
                            style="height: 38px; padding: 0 14px; border-radius: 8px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff; border: none; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);">
                        <i class="bi bi-cloud-arrow-up-fill" style="font-size: 15px;"></i>
                        <span>สั่งอัปเดตเครื่องเก่าทั้งหมด ({{ $agentOutdatedCount }})</span>
                    </button>
                    @endif

                    <button type="button" id="btnSelectedFleetScan" onclick="triggerSelectedFleetScan()" class="btn btn-primary btn-sm" disabled
                            style="height: 38px; padding: 0 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(15, 118, 110, 0.25);">
                        <i class="bi bi-cloud-arrow-down-fill" style="font-size: 15px;"></i>
                        <span>ดึงข้อมูลที่เลือก</span>
                        <span id="selectedFleetBadge" class="badge bg-white text-primary" style="font-size: 11px; padding: 2px 7px; border-radius: 99px;">0</span>
                    </button>

                    <button type="button" id="btnSelectedFleetUpdate" onclick="triggerSelectedFleetUpdate()" class="btn btn-outline-info btn-sm" disabled
                            style="height: 38px; padding: 0 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: #ffffff;">
                        <i class="bi bi-arrow-repeat" style="font-size: 15px;"></i>
                        <span>อัปเดต Agent</span>
                        <span id="selectedFleetUpdateBadge" class="badge bg-info text-white" style="font-size: 11px; padding: 2px 7px; border-radius: 99px;">0</span>
                    </button>

                    <button type="button" id="btnSelectedFleetDelete" onclick="deleteSelectedFleetMachines()" class="btn btn-outline-danger btn-sm" disabled
                            style="height: 38px; padding: 0 12px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: #ffffff;">
                        <i class="bi bi-trash3-fill" style="font-size: 15px;"></i>
                        <span>ลบเครื่อง</span>
                        <span id="selectedFleetDeleteBadge" class="badge bg-danger text-white" style="font-size: 11px; padding: 2px 7px; border-radius: 99px;">0</span>
                    </button>

                    @if($agentOnlineCount > 0)
                    <button type="button" onclick="triggerAllOnlineFleetScan()" class="btn btn-outline-success btn-sm"
                            style="height: 38px; padding: 0 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; background: #ffffff;">
                        <i class="bi bi-broadcast" style="font-size: 15px;"></i>
                        <span class="d-none d-lg-inline">ดึงข้อมูลเครื่องออนไลน์ทั้งหมด ({{ $agentOnlineCount }})</span>
                        <span class="d-inline d-lg-none">ดึงออนไลน์ ({{ $agentOnlineCount }})</span>
                    </button>
                    @endif

                    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'agents']) }}" class="btn btn-outline-secondary btn-sm" title="รีเฟรชสถานะ" style="height: 38px; padding: 0 12px; border-radius: 8px; display: inline-flex; align-items: center;">
                        <i class="bi bi-arrow-clockwise" style="font-size: 15px;"></i>
                    </a>
                </div>
            </div>

            <!-- Fleet Table (Responsive Card Transform) -->
            @if(count($agentFleet) > 0)
            <div class="table-responsive ha-responsive-table-wrapper" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff;">
                <table class="table table-hover align-middle mb-0 ha-responsive-table ha-fleet-table" id="fleetTable" style="font-size: 13px;">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
                        <tr>
                            <th style="width: 38px; text-align: center;">
                                <input type="checkbox" id="fleetSelectAll" onchange="toggleFleetSelectAll(this)" style="cursor: pointer;" title="เลือกทั้งหมด">
                            </th>
                            <th style="width: 140px;">สถานะการเชื่อมต่อ</th>
                            <th style="min-width: 190px;">ชื่อเครื่อง & เครือข่าย</th>
                            <th style="min-width: 180px;">ครุภัณฑ์ & แผนก</th>
                            <th style="min-width: 230px;">สเปกปัจจุบันที่ตรวจพบ</th>
                            <th style="width: 130px; text-align: right;">จัดการ / ดึงสเปก</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentFleet as $agent)
                        @php
                            $isFleetLatest = (!empty($agent['client_version']) && strpos($agent['client_version'], '2.11.0') === 0);
                        @endphp
                        <tr class="fleet-row" data-online="{{ $agent['is_online'] ? 'true' : 'false' }}" data-outdated="{{ $isFleetLatest ? 'false' : 'true' }}" data-version="{{ $agent['client_version'] ?? '' }}" data-search="{{ strtolower($agent['hostname'] . ' ' . $agent['ip_address'] . ' ' . $agent['mac_address'] . ' ' . $agent['hardware_id'] . ' ' . ($agent['asset_code'] ?? '') . ' ' . ($agent['asset_name'] ?? '') . ' ' . ($agent['department_name'] ?? '') . ' ' . ($agent['cpu_model'] ?? '') . ' ' . ($agent['client_version'] ?? '')) }}">
                            <!-- Checkbox -->
                            <td class="ha-col-select" style="text-align: center;">
                                <input type="checkbox" class="fleet-item-chk" 
                                       value="{{ $agent['key'] }}"
                                       data-hostname="{{ $agent['hostname'] }}"
                                       data-hwid="{{ $agent['hardware_id'] }}"
                                       data-audit-id="{{ $agent['audit_id'] ?? '' }}"
                                       data-asset-id="{{ $agent['asset_id'] ?? '' }}"
                                       data-online="{{ $agent['is_online'] ? 'true' : 'false' }}"
                                       onchange="updateFleetSelectionCount()"
                                       style="cursor: pointer;">
                            </td>

                            <!-- Connection Status & Version -->
                            <td class="ha-col-status">
                                <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap;">
                                    @if($agent['is_online'])
                                        <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 11px; padding: 3px 7px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #16a34a; box-shadow: 0 0 0 2px rgba(22,163,74,0.3);"></span>
                                            <span>ออนไลน์</span>
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-size: 11px; padding: 3px 7px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;">
                                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #94a3b8;"></span>
                                            <span>ออฟไลน์</span>
                                        </span>
                                    @endif

                                    @if($isFleetLatest)
                                        <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 10px; padding: 2px 5px; border-radius: 4px;">
                                            v{{ $agent['client_version'] }} (ล่าสุด)
                                        </span>
                                    @else
                                        <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fcd34d; font-size: 10px; padding: 2px 5px; border-radius: 4px;" title="เวอร์ชันเก่า ต้องอัปเดตเป็น v2.11.0">
                                            v{{ $agent['client_version'] }} (เก่า)
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 3px;">
                                    <i class="bi bi-clock me-1"></i>{{ $agent['last_seen'] }}
                                </div>
                            </td>

                            <!-- Hostname & Network -->
                            <td class="ha-col-hostname">
                                <div style="font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-display text-primary" style="font-size: 14px;"></i>
                                    <span>{{ $agent['hostname'] }}</span>
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                    IP: <code>{{ $agent['ip_address'] ?: '-' }}</code>
                                    @if(!empty($agent['mac_address']))
                                        &bull; MAC: <code>{{ $agent['mac_address'] }}</code>
                                    @endif
                                </div>
                                @if(!empty($agent['hardware_id']))
                                <div style="font-size: 10.5px; color: #94a3b8; margin-top: 1px;">
                                    HWID: <code>{{ Str::limit($agent['hardware_id'], 14) }}</code>
                                </div>
                                @endif
                            </td>

                            <!-- Matched Asset & Dept -->
                            <td class="ha-col-asset">
                                @if(!empty($agent['asset_code']))
                                    <div style="font-weight: 600; color: #0369a1; display: flex; align-items: center; gap: 5px;">
                                        <i class="bi bi-upc-scan"></i>
                                        <span>{{ $agent['asset_code'] }}</span>
                                    </div>
                                    <div style="font-size: 12px; color: #334155; margin-top: 1px;">
                                        {{ $agent['asset_name'] ?: 'คอมพิวเตอร์' }}
                                    </div>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                        <i class="bi bi-geo-alt me-1"></i> {{ $agent['department_name'] }}
                                    </div>
                                @else
                                    <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 11px; padding: 3px 8px; border-radius: 6px;">
                                        <i class="bi bi-exclamation-circle me-1"></i> ยังไม่ผูกครุภัณฑ์
                                    </span>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                                        {{ $agent['department_name'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- Specs -->
                            <td class="ha-col-specs">
                                <div style="font-weight: 600; color: #1e293b; font-size: 12.5px;">
                                    <i class="bi bi-cpu text-info me-1"></i> {{ Str::limit($agent['cpu_model'], 38) }}
                                </div>
                                <div style="font-size: 11.5px; color: #475569; margin-top: 2px;">
                                    <span>RAM: <b>{{ $agent['ram_capacity'] ? $agent['ram_capacity'] . ' GB' : '-' }}</b></span> &bull; 
                                    <span>Disk: <b>{{ $agent['storage_capacity'] ?: '-' }} ({{ $agent['storage_type'] ?: 'Storage' }})</b></span>
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 1px;">
                                    <i class="bi bi-windows text-primary me-1"></i> {{ $agent['os_name'] }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="ha-col-actions" style="text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 5px; align-items: center; flex-wrap: wrap;">
                                    <button type="button" class="btn btn-outline-primary btn-sm" 
                                            onclick="triggerSingleFleetScan('{{ addslashes($agent['hostname']) }}', '{{ addslashes($agent['hardware_id']) }}', '{{ $agent['audit_id'] ?? '' }}', '{{ $agent['asset_id'] ?? '' }}')"
                                            title="ส่งคำสั่งดึงข้อมูลสเปกของเครื่องนี้เดี๋ยวนี้"
                                            style="border-radius: 8px; padding: 4px 10px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="bi bi-cloud-arrow-down-fill"></i>
                                        <span>ดึงสเปก</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm"
                                            onclick="triggerSingleFleetUpdate('{{ addslashes($agent['hostname']) }}', '{{ addslashes($agent['hardware_id']) }}')"
                                            title="ส่งคำสั่งอัปเดต Agent เครื่องนี้เป็นเวอร์ชันล่าสุด"
                                            style="border-radius: 8px; padding: 4px 8px; font-size: 11.5px;">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    @if(!empty($agent['audit_id']))
                                        <button type="button" class="btn btn-light btn-sm text-secondary border" 
                                                onclick="openAgentInspectionModal({{ $agent['audit_id'] }})"
                                                title="ดูข้อมูลสเปก / ผลการตรวจนับ" style="border-radius: 8px; padding: 4px 8px; font-size: 11.5px;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="openDeleteMachineModal('{{ addslashes($agent['hostname']) }}', '{{ addslashes($agent['hardware_id']) }}', '{{ addslashes($agent['ip_address'] ?? '') }}', {{ $agent['is_online'] ? 'true' : 'false' }}, '{{ $agent['audit_id'] ?? '' }}')"
                                            title="ลบเครื่องที่ติดตั้ง Agent นี้ออกจากระบบ"
                                            style="border-radius: 8px; padding: 4px 8px; font-size: 11.5px;">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <!-- Empty State -->
            <div style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 16px; padding: 40px 20px; text-align: center;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 16px;">
                    <i class="bi bi-hdd-network"></i>
                </div>
                <h4 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 6px;">ยังไม่มีเครื่องที่ติดตั้ง Agent ส่งข้อมูลเข้ามา</h4>
                <p style="font-size: 13px; color: #64748b; max-width: 480px; margin: 0 auto 18px auto;">
                    ท่านสามารถดาวน์โหลดโปรแกรม THC IT Agent หรือสคริปต์สำรวจอัตโนมัติ เพื่อนำไปติดตั้งบนเครื่องคอมพิวเตอร์ลูกข่ายในโรงพยาบาลได้ทันที
                </p>
                <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'deploy']) }}" class="btn btn-primary btn-sm" style="border-radius: 10px; padding: 8px 18px; font-weight: 600;">
                    <i class="bi bi-download me-1"></i> ไปที่หน้าตัวติดตั้ง Agent & การกระจายสคริปต์
                </a>
            </div>
            @endif
        </div>
        @endif

        <!-- TAB CONTENT 3: 3D DEPARTMENT MAP DIAGRAM (แผนผัง 3D แต่ละแผนก) -->
        @if($tab === 'map3d')
        <div class="ha-tab-pane ha-map3d-pane" style="padding: 20px;">
            <!-- 3D DIAGRAM CONTROLS & NAVIGATION HEADER -->
            <div class="ha-map3d-header">
                <div class="ha-map3d-header-left">
                    <div class="ha-map3d-badge-live">
                        <span class="ha-live-beacon"></span>
                        <span>ระบบแผนผัง 3 มิติประจำแผนก (3D Interactive Department Map Diagram)</span>
                    </div>
                    <h2 class="ha-map3d-title">
                        <i class="bi bi-box-fill text-warning me-2"></i> แผนผังจำลอง 3 มิติ ตรวจติดตามคอมพิวเตอร์รายแผนก
                    </h2>
                    <p class="ha-map3d-subtitle">
                        โรงพยาบาลทุ่งหัวช้าง &bull; จำลองผังอาคาร ห้องปฏิบัติงาน และตำแหน่งเครื่องคอมพิวเตอร์ พร้อมสถานะการตรวจนับสเปกและ Agent ออนไลน์
                    </p>
                </div>
                <div class="ha-map3d-header-right">
                    <!-- Switch to Department List View -->
                    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'departments']) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-list-ul me-1"></i> ดูตารางสรุปรายแผนก
                    </a>
                    <!-- Quick Scan Department Button -->
                    <button type="button" class="btn btn-sm btn-primary" onclick="THC_3D.scanCurrentView()" style="border-radius: 8px; font-weight: 700; background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%); border: none; box-shadow: 0 2px 6px rgba(15, 118, 110, 0.3);">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> สั่งดึงสเปกอาคารที่กำลังดู (<span id="btnHeaderScanCount">0</span>)
                    </button>
                </div>
            </div>

            <!-- 3D TOOLBAR & FILTER DECK -->
            <div class="ha-map3d-toolbar">
                <div class="ha-map3d-toolbar-row">
                    <!-- Building Selector Buttons -->
                    <div class="ha-map3d-bld-group" id="map3dBuildingSelector">
                        <button type="button" class="btn btn-sm ha-bld-btn active" data-bld="all" onclick="THC_3D.setBuilding('all')">
                            <i class="bi bi-buildings-fill"></i> ทุกอาคาร
                        </button>
                        <button type="button" class="btn btn-sm ha-bld-btn" data-bld="opd" onclick="THC_3D.setBuilding('opd')">
                            <i class="bi bi-hospital-fill text-info"></i> อาคารผู้ป่วยนอก (OPD)
                        </button>
                        <button type="button" class="btn btn-sm ha-bld-btn" data-bld="ipd" onclick="THC_3D.setBuilding('ipd')">
                            <i class="bi bi-heart-pulse-fill text-danger"></i> อาคารผู้ป่วยใน (IPD)
                        </button>
                        <button type="button" class="btn btn-sm ha-bld-btn" data-bld="admin" onclick="THC_3D.setBuilding('admin')">
                            <i class="bi bi-bank2 text-warning"></i> อาคารอำนวยการ
                        </button>
                        <button type="button" class="btn btn-sm ha-bld-btn" data-bld="rehab" onclick="THC_3D.setBuilding('rehab')">
                            <i class="bi bi-activity text-success"></i> อาคารฟื้นฟู
                        </button>
                        <button type="button" class="btn btn-sm ha-bld-btn" data-bld="other" onclick="THC_3D.setBuilding('other')">
                            <i class="bi bi-grid-1x2-fill text-secondary"></i> อื่นๆ / ส่วนกลาง
                        </button>
                    </div>

                    <!-- Floor Selector -->
                    <div class="ha-map3d-floor-group" id="map3dFloorSelector">
                        <span class="ha-floor-label">ชั้น:</span>
                        <button type="button" class="btn btn-sm ha-floor-btn active" data-floor="all" onclick="THC_3D.setFloor('all')">ทุกชั้น</button>
                        <button type="button" class="btn btn-sm ha-floor-btn" data-floor="1" onclick="THC_3D.setFloor(1)">ชั้น 1</button>
                        <button type="button" class="btn btn-sm ha-floor-btn" data-floor="2" onclick="THC_3D.setFloor(2)">ชั้น 2</button>
                    </div>
                </div>

                <div class="ha-map3d-toolbar-row ha-map3d-controls-row">
                    <!-- Search Input -->
                    <div class="ha-map3d-search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="map3dSearchInput" placeholder="ค้นหา Hostname, IP, เลขครุภัณฑ์, ผู้ครอบครอง..." oninput="THC_3D.searchDevices(this.value)">
                        <button type="button" class="ha-search-clear" onclick="THC_3D.clearSearch()" title="ล้างการค้นหา">&times;</button>
                    </div>

                    <!-- Status Filter Pills -->
                    <div class="ha-map3d-status-filters">
                        <button type="button" class="ha-status-filter-btn active" data-status="all" onclick="THC_3D.setStatusFilter('all')">
                            ทั้งหมด (<span id="countFilterAll">0</span>)
                        </button>
                        <button type="button" class="ha-status-filter-btn ha-filter-audited" data-status="audited" onclick="THC_3D.setStatusFilter('audited')">
                            🟢 ตรวจแล้ว (<span id="countFilterAudited">0</span>)
                        </button>
                        <button type="button" class="ha-status-filter-btn ha-filter-online" data-status="online" onclick="THC_3D.setStatusFilter('online')">
                            🔵 ออนไลน์ (<span id="countFilterOnline">0</span>)
                        </button>
                        <button type="button" class="ha-status-filter-btn ha-filter-pending" data-status="pending" onclick="THC_3D.setStatusFilter('pending')">
                            🟡 รอตรวจ/ยังไม่ตรวจ (<span id="countFilterPending">0</span>)
                        </button>
                        <button type="button" class="ha-status-filter-btn ha-filter-warning" data-status="warning" onclick="THC_3D.setStatusFilter('warning')">
                            ⚠️ ตกเกณฑ์ ICT (<span id="countFilterWarning">0</span>)
                        </button>
                    </div>

                    <!-- 3D Camera Controls Toolbar -->
                    <div class="ha-map3d-camera-deck">
                        <!-- Projection Modes -->
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-dark active" id="btnModeIso" onclick="THC_3D.setViewMode('iso')" title="มุมมองไอโซเมตริก 3 มิติ">
                                <i class="bi bi-box"></i> 3D Iso
                            </button>
                            <button type="button" class="btn btn-dark" id="btnModePerspective" onclick="THC_3D.setViewMode('perspective')" title="มุมมองเฉียง 3 มิติ (หมุนรอบทิศ)">
                                <i class="bi bi-compass"></i> 3D Persp
                            </button>
                            <button type="button" class="btn btn-dark" id="btnModeTopdown" onclick="THC_3D.setViewMode('topdown')" title="มุมมองผังแบนราบ 2 มิติ (Top-Down)">
                                <i class="bi bi-layers"></i> 2D Plan
                            </button>
                        </div>

                        <!-- Rotation -->
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-dark" onclick="THC_3D.rotateLeft()" title="หมุนซ้าย 45°">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>
                            <button type="button" class="btn btn-dark" onclick="THC_3D.rotateRight()" title="หมุนขวา 45°">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>

                        <!-- Zoom -->
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-dark" onclick="THC_3D.zoomIn()" title="ซูมเข้า">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                            <button type="button" class="btn btn-dark" onclick="THC_3D.zoomOut()" title="ซูมออก">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <button type="button" class="btn btn-dark" onclick="THC_3D.resetView()" title="รีเซ็ตมุมมองเดิม">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>

                        <!-- Design Mode Toggle (ออกแบบไว้รอ) -->
                        <button type="button" class="btn btn-sm btn-outline-warning ha-btn-design-mode" id="btnToggleDesignMode" onclick="THC_3D.toggleDesignMode()" title="เปิดโหมดปรับแต่งจัดวางตำแหน่งห้องและอุปกรณ์ (ออกแบบไว้รอสำหรับผังจริง)">
                            <i class="bi bi-pencil-square"></i>
                            <span class="d-none d-xl-inline">โหมดจัดวางผัง</span>
                        </button>

                        <!-- Fullscreen Toggle -->
                        <button type="button" class="btn btn-sm btn-dark" onclick="THC_3D.toggleFullscreen()" title="ขยายเต็มหน้าจอ">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </div>
                </div>

                <!-- Design Mode Helper Banner (Visible only in Design Mode) -->
                <div id="map3dDesignModeBanner" class="ha-design-mode-banner" style="display: none;">
                    <div class="ha-design-banner-left">
                        <i class="bi bi-pencil-fill text-warning me-2"></i>
                        <strong>กำลังเปิดใช้งานโหมดออกแบบจัดวางตำแหน่งผัง (Design & Layout Mode)</strong>
                        <span class="ms-2 opacity-75 d-none d-md-inline">&bull; ท่านสามารถลากย้ายตำแหน่งห้อง (Drag Room) เพื่อจัดเรียงแผนผังอาคารให้ตรงกับสถานที่จริง</span>
                    </div>
                    <div class="ha-design-banner-right">
                        <button type="button" class="btn btn-sm btn-success" onclick="THC_3D.saveLayout()" style="border-radius: 6px; font-weight: 700;">
                            <i class="bi bi-check2-circle me-1"></i> บันทึกตำแหน่งผัง
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="THC_3D.resetLayoutDefaults()" style="border-radius: 6px;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> คืนค่าเริ่มต้น
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="THC_3D.toggleDesignMode()" style="border-radius: 6px;">
                            ปิดโหมด
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3D VIEWPORT CANVAS STAGE -->
            <div class="ha-map3d-viewport-wrapper" id="map3dViewportWrapper">
                
                <!-- HUD Top-Left: Selected Building / Room Overview Card -->
                <div class="ha-hud-card ha-hud-top-left" id="hudTopLeft">
                    <div class="ha-hud-title" id="hudBuildingTitle">อาคารผู้ป่วยนอก (OPD)</div>
                    <div class="ha-hud-sub" id="hudBuildingSub">แสดงทุกแผนกในอาคาร</div>
                    <div class="ha-hud-stats-row">
                        <div class="ha-hud-stat">
                            <span class="ha-hud-stat-val" id="hudStatDepts">0</span>
                            <span class="ha-hud-stat-lbl">แผนก</span>
                        </div>
                        <div class="ha-hud-stat">
                            <span class="ha-hud-stat-val" id="hudStatDevices">0</span>
                            <span class="ha-hud-stat-lbl">เครื่อง</span>
                        </div>
                        <div class="ha-hud-stat">
                            <span class="ha-hud-stat-val text-success" id="hudStatAudited">0%</span>
                            <span class="ha-hud-stat-lbl">ตรวจแล้ว</span>
                        </div>
                        <div class="ha-hud-stat">
                            <span class="ha-hud-stat-val text-info" id="hudStatOnline">0</span>
                            <span class="ha-hud-stat-lbl">ออนไลน์</span>
                        </div>
                    </div>
                </div>

                <!-- HUD Top-Right: Quick Department Action Trigger -->
                <div class="ha-hud-card ha-hud-top-right" id="hudTopRight">
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">คำสั่งด่วนในผัง 3D</div>
                    <div style="display: flex; gap: 6px; margin-top: 6px;">
                        <button type="button" class="btn btn-sm btn-outline-info ha-hud-btn" onclick="THC_3D.scanCurrentView()" title="ส่งคำสั่งสแกนดึงสเปกเครื่องทั้งหมดในมุมมองนี้">
                            <i class="bi bi-cloud-arrow-down-fill"></i> สแกนทั้งหมด (<span id="hudScanCount">0</span>)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning ha-hud-btn" onclick="THC_3D.updateCurrentView()" title="สั่งอัปเดต Agent เครื่องเก่าในมุมมองนี้">
                            <i class="bi bi-arrow-repeat"></i> อัปเดต Agent
                        </button>
                    </div>
                </div>

                <!-- HUD Bottom-Left: Color & Symbol Legend -->
                <div class="ha-hud-card ha-hud-bottom-left">
                    <div style="font-size: 11px; font-weight: 700; color: #94a3b8; margin-bottom: 5px;">สัญลักษณ์สถานะในผัง 3D:</div>
                    <div class="ha-legend-grid">
                        <div class="ha-legend-item"><span class="ha-legend-dot bg-success"></span> ตรวจนับแล้ว</div>
                        <div class="ha-legend-item"><span class="ha-legend-dot bg-info ha-dot-pulse"></span> Agent ออนไลน์</div>
                        <div class="ha-legend-item"><span class="ha-legend-dot bg-warning"></span> รออนุมัติ/ยังไม่ตรวจ</div>
                        <div class="ha-legend-item"><span class="ha-legend-dot bg-danger"></span> ยังไม่สำรวจ</div>
                        <div class="ha-legend-item"><span class="ha-legend-dot bg-orange">⚠️</span> ตกเกณฑ์ ICT</div>
                    </div>
                </div>

                <!-- HUD Bottom-Right: View Coordinates & Hint -->
                <div class="ha-hud-card ha-hud-bottom-right">
                    <div style="font-size: 11px; color: #94a3b8;">
                        <i class="bi bi-mouse me-1"></i> ลากเมาส์หมุนมุมมอง &bull; Scroll เพื่อซูม &bull; ดับเบิลคลิกเพื่อเข้าห้อง
                    </div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 2px;" id="hudCameraInfo">
                        มุมหมุน: 45° &bull; มุมเอียง: 60° &bull; ซูม: 100%
                    </div>
                </div>

                <!-- 3D INTERACTIVE SVG / CANVAS STAGE -->
                <div class="ha-map3d-stage" id="map3dStage">
                    <div class="ha-map3d-world" id="map3dWorld">
                        <!-- Holographic Floor Grid -->
                        <div class="ha-map3d-grid-layer" id="map3dGridLayer"></div>
                        <!-- Departments & Rooms Container -->
                        <div class="ha-map3d-rooms-container" id="map3dRoomsContainer"></div>
                        <!-- Radar / Beacon Effect Overlay -->
                        <div class="ha-map3d-beacon-layer" id="map3dBeaconLayer"></div>
                    </div>
                </div>

                <!-- 3D FLOATING TOOLTIP FOR HOVER -->
                <div id="map3dTooltip" class="ha-map3d-tooltip" style="display: none;"></div>

                <!-- 3D SIDE DRAWER (DEVICE & DEPARTMENT INSPECTOR) -->
                <div id="map3dDrawer" class="ha-map3d-drawer" style="display: none;">
                    <div class="ha-drawer-header">
                        <div class="ha-drawer-title-row">
                            <span class="ha-drawer-badge" id="drawerBadge">คอมพิวเตอร์</span>
                            <button type="button" class="ha-drawer-close" onclick="THC_3D.closeDrawer()">&times;</button>
                        </div>
                        <h3 class="ha-drawer-hostname" id="drawerHostname">-</h3>
                        <div class="ha-drawer-ip" id="drawerIp">-</div>
                    </div>
                    <div class="ha-drawer-body" id="drawerBody">
                        <!-- Dynamic specs & actions injected by JS -->
                    </div>
                    <div class="ha-drawer-footer" id="drawerFooter">
                        <!-- Dynamic action buttons injected by JS -->
                    </div>
                </div>

            </div>
        </div>
        @endif

        <!-- TAB CONTENT 4: DEPARTMENT TRACKER -->
        @if($tab === 'departments')
        <div class="ha-tab-pane" style="padding: 22px;">
            <div class="ha-section-header">
                <div>
                    <h3 class="ha-section-title">
                        <i class="bi bi-diagram-3-fill text-primary me-1"></i> ติดตามความคืบหน้าการตรวจนับแยกตามแผนก/หน่วยงาน
                    </h3>
                    <p class="ha-section-desc">
                        ปีงบประมาณ พ.ศ. {{ $fiscalYear }} &bull; เป้าหมายการตรวจนับครุภัณฑ์คอมพิวเตอร์ให้ครบ 100% ทั่วทั้งโรงพยาบาล
                    </p>
                </div>
                <div class="ha-section-search">
                    <input type="text" id="deptFilterInput" onkeyup="filterDeptCards()" placeholder="ค้นหาชื่อแผนก..." class="form-control form-control-sm" style="width: 100%; min-width: 200px; max-width: 260px; border-radius: 8px;">
                </div>
            </div>

            <div id="deptCardsGrid" class="ha-dept-grid">
                @foreach($departments as $dept)
                <div class="dept-card" data-name="{{ strtolower($dept->name) }}" style="background: #ffffff; border: 1px solid {{ $dept->percentage == 100 ? '#86efac' : ($dept->percentage > 0 ? '#cbd5e1' : '#e2e8f0') }}; border-radius: 14px; padding: 18px; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: {{ $dept->percentage == 100 ? '#dcfce7' : '#e0f2fe' }}; color: {{ $dept->percentage == 100 ? '#16a34a' : '#0284c7' }}; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                <i class="bi {{ $dept->percentage == 100 ? 'bi-check-circle-fill' : 'bi-building' }}"></i>
                            </div>
                            <div>
                                <strong style="color: #0f172a; font-size: 14px; display: block;">{{ $dept->name }}</strong>
                                <span style="font-size: 11.5px; color: #64748b;">รหัสแผนก: THC-D{{ $dept->id }}</span>
                            </div>
                        </div>
                        <span style="font-size: 13px; font-weight: 800; color: {{ $dept->percentage == 100 ? '#16a34a' : ($dept->percentage > 0 ? '#0284c7' : '#94a3b8') }}; background: {{ $dept->percentage == 100 ? '#f0fdf4' : '#f8fafc' }}; border: 1px solid {{ $dept->percentage == 100 ? '#bbf7d0' : '#e2e8f0' }}; padding: 3px 9px; border-radius: 8px; flex-shrink: 0;">
                            {{ $dept->percentage }}%
                        </span>
                    </div>
                    
                    <div class="progress mb-2" style="height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden;">
                        <div class="progress-bar {{ $dept->percentage == 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $dept->percentage }}%; border-radius: 99px;"></div>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 10px; border-top: 1px dashed #e2e8f0; padding-top: 8px; flex-wrap: wrap; gap: 4px;">
                        <span>สำรวจแล้ว: <strong style="color: #166534;">{{ $dept->audited_assets }}</strong> / {{ $dept->total_assets }} เครื่อง</span>
                        <span>คงเหลือ: <strong style="color: {{ $dept->pending_assets > 0 ? '#dc2626' : '#166534' }};">{{ $dept->pending_assets }}</strong> เครื่อง</span>
                    </div>

                    <!-- 3D Map View Shortcut Button -->
                    <div style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #f1f5f9; display: flex; justify-content: flex-end;">
                        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'map3d']) }}#dept-{{ $dept->id }}" 
                           class="btn btn-sm btn-outline-warning" 
                           onclick="localStorage.setItem('thc_3d_target_dept', {{ $dept->id }})"
                           title="เปิดดูตำแหน่งเครื่องและสถานะของแผนกนี้บนแผนผัง 3D"
                           style="border-radius: 6px; font-size: 11.5px; padding: 3px 9px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; color: #b45309; border-color: #fcd34d; background: #fffbeb;">
                            <i class="bi bi-box-fill text-warning"></i> ดูบนแผนผัง 3D
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- TAB CONTENT 4: ANNUAL REPORT ANALYTICS & CHARTS -->
        @if($tab === 'report')
        <div class="ha-tab-pane" style="padding: 22px;">
            <div class="ha-section-header">
                <div>
                    <h3 class="ha-section-title">สรุปภาพรวมและวิเคราะห์สเปคคอมพิวเตอร์ประจำปีงบประมาณ พ.ศ. {{ $fiscalYear }}</h3>
                    <div class="ha-section-desc">
                        ข้อมูลวิเคราะห์เชิงลึกสำหรับประกอบการวางแผนจัดซื้อ จัดหาทดแทน และปรับปรุงฮาร์ดแวร์ โรงพยาบาลทุ่งหัวช้าง
                    </div>
                </div>
                <div class="ha-section-actions">
                    <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; box-shadow: 0 2px 6px rgba(15, 118, 110, 0.25);">
                        <i class="bi bi-printer"></i>
                        <span>พิมพ์รายงานสรุป A4 ฉบับทางการ</span>
                    </a>
                </div>
            </div>

            <!-- Executive Summary Cards -->
            <div class="ha-report-summary-grid">
                <div class="ha-report-summary-card" style="border-left: 4px solid #0d9488;">
                    <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">ความคืบหน้าการตรวจนับภาพรวม</div>
                    <div style="font-size: 22px; font-weight: 800; color: #0f766e; margin-top: 4px;">{{ $auditedCount }} / {{ $totalFleet }} เครื่อง ({{ $auditedPercentage }}%)</div>
                    <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">เสร็จสิ้นตามเกณฑ์ประจำปี</div>
                </div>

                <div class="ha-report-summary-card" style="border-left: 4px solid #ef4444;">
                    <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">เครื่องที่ควรเสนอของบปรับปรุง (MDES)</div>
                    <div style="font-size: 22px; font-weight: 800; color: #dc2626; margin-top: 4px;">{{ $mdesWarningCount }} เครื่อง</div>
                    <div style="font-size: 11.5px; color: #ef4444; margin-top: 4px;">RAM &lt; 8GB / ใช้ฮาร์ดดิสก์จานหมุน</div>
                </div>

                <div class="ha-report-summary-card" style="border-left: 4px solid #f59e0b;">
                    <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">อายุการใช้งานเกิน 5 ปี (ควรจำหน่าย/ทดแทน)</div>
                    <div style="font-size: 22px; font-weight: 800; color: #d97706; margin-top: 4px;">{{ $agedOver5YearsCount }} เครื่อง</div>
                    <div style="font-size: 11.5px; color: #b45309; margin-top: 4px;">อิงตามวันที่จัดซื้อในระบบคลัง</div>
                </div>
            </div>

            <!-- CHARTS GRID -->
            <div class="ha-charts-grid">
                
                {{-- Chart 1: RAM Distribution --}}
                <div class="ha-chart-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 8px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-memory text-primary"></i> สัดส่วนขนาด RAM ประจำเครื่อง
                        </h4>
                        <span class="badge bg-light text-dark border" style="font-size: 11px;">มาตรฐาน &ge; 8 GB</span>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="ramChart"></canvas>
                    </div>
                    <div style="margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 12px; color: #64748b;">
                        @foreach($ramDistribution as $r)
                            <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                                <span>RAM {{ $r->ram_capacity }} GB {{ $r->ram_capacity < 8 ? '⚠️ (ต่ำกว่าเกณฑ์)' : '✓' }}</span>
                                <strong>{{ $r->total }} เครื่อง</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Chart 2: OS Distribution --}}
                <div class="ha-chart-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 8px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-windows text-info"></i> สัดส่วนระบบปฏิบัติการ (OS)
                        </h4>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="osChart"></canvas>
                    </div>
                    <div style="margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 12px; color: #64748b;">
                        @foreach($osDistribution as $os)
                            <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                                <span>{{ $os->os_name }}</span>
                                <strong>{{ $os->total }} เครื่อง</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Chart 3: Storage Type Distribution --}}
                <div class="ha-chart-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 8px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-hdd-fill text-warning"></i> สัดส่วนประเภท Storage (SSD vs HDD)
                        </h4>
                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">SSD แนะนำ</span>
                    </div>
                    <div style="height: 220px; position: relative;">
                        <canvas id="storageChart"></canvas>
                    </div>
                    <div style="margin-top: 14px; border-top: 1px solid #f1f5f9; padding-top: 10px; font-size: 12px; color: #64748b;">
                        @foreach($storageDistribution as $st)
                            <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                                <span>{{ $st->storage_type }}</span>
                                <strong>{{ $st->total }} เครื่อง</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
        @endif

        <!-- TAB CONTENT 4: AGENT DEPLOYMENT HUB -->
        @if($tab === 'deploy')
        <div style="padding: 22px;">
            <div style="max-width: 900px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-terminal-fill"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">
                            ศูนย์ดาวน์โหลดและกระจายสคริปต์ Audit Agent ประจำเครื่อง
                        </h3>
                        <p style="font-size: 13px; color: #64748b; margin: 2px 0 0 0;">
                            โปรแกรม Agent สำหรับตรวจอ่านสเปคฮาร์ดแวร์จริงผ่าน WMI/BIOS รวดเร็ว แม่นยำ ไม่ต้องกรอกมือ
                        </p>
                    </div>
                </div>

                <!-- Security Compliance & MISP Whitelist Verification Banner -->
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f0fdfa 100%); border: 1.5px solid #86efac; border-radius: 16px; padding: 20px 24px; margin: 18px 0 24px 0; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #16a34a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 3px 8px rgba(22, 163, 74, 0.3);">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 16px; font-weight: 800; color: #14532d; margin: 0; display: flex; align-items: center; gap: 8px;">
                                    <span>THC IT Agent v2.11.0 (Hardened Enterprise Standalone)</span>
                                    <span class="badge" style="background: #22c55e; color: #ffffff; font-size: 11px; padding: 3px 8px; border-radius: 6px;">ปลอดภัย 100%</span>
                                </h4>
                                <p style="font-size: 12.5px; color: #166534; margin: 2px 0 0 0;">
                                    ปรับแต่ง Heartbeat 60s พร้อมสุ่มเวลา (Jitter) ป้องกันการจัดกลุ่มเป็น C2/Botnet Beacon รองรับ Hot-Swap In-Process
                                </p>
                            </div>
                        </div>
                        <a href="{{ url('/agent/manifest.json') }}" target="_blank" class="btn btn-sm btn-outline-success" style="border-radius: 8px; font-weight: 600; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; background: #ffffff;">
                            <i class="bi bi-filetype-json"></i> ตรวจสอบ Manifest JSON
                        </a>
                    </div>

                    <!-- Hash & Security Attribute Table -->
                    <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 16px; font-size: 12px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <strong style="color: #0f172a; min-width: 90px;"><i class="bi bi-key-fill text-success"></i> SHA-256 Hash:</strong>
                                <code id="agentSha256Code" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 3px 8px; border-radius: 6px; font-size: 11.5px; color: #047857; font-weight: 700; word-break: break-all;">1857427CA8E8F3FDB5B1805229B091E10357BA452CA217D1B22F611F7A8C179C</code>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border" onclick="copyInstallerCmd('1857427CA8E8F3FDB5B1805229B091E10357BA452CA217D1B22F611F7A8C179C', 'คัดลอก SHA-256 เรียบร้อยแล้ว')" style="font-size: 11px; padding: 2px 8px; border-radius: 6px;">
                                <i class="bi bi-clipboard me-1"></i> คัดลอก Hash
                            </button>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; border-top: 1px dashed #e2e8f0; padding-top: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="color: #0f172a; min-width: 90px;"><i class="bi bi-fingerprint text-primary"></i> MD5 Hash:</strong>
                                <code style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 2px 6px; border-radius: 5px; font-size: 11px; color: #475569;">773E736CAE85383298F0070CFA7D27F3</code>
                            </div>
                            <div style="display: flex; gap: 14px; color: #64748b; font-size: 11.5px;">
                                <span><i class="bi bi-clock-history me-1"></i> Heartbeat: <strong>60s (±10s Jitter)</strong></span>
                                <span><i class="bi bi-file-earmark-binary me-1"></i> ขนาด: <strong>68 KB (.NET 4.0 Standalone)</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Official Installation Method: Setup Wizard -->
                <div style="margin-bottom: 24px;">
                    <div class="ha-wizard-card">
                        <div class="ha-official-badge">
                            ★ ช่องทางการติดตั้งทางการ (Official Setup Wizard)
                        </div>
                        <div class="ha-wizard-inner">
                            <div class="ha-wizard-icon">
                                <i class="bi bi-window-desktop"></i>
                            </div>
                            <div class="ha-wizard-body">
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <h4 style="font-size: 18px; font-weight: 800; color: #15803d; margin: 0;">
                                        ติดตั้งผ่านตัวช่วย Setup Wizard ในตัวโปรแกรม (THC_IT_Agent.exe)
                                    </h4>
                                    <span class="badge" style="background: #16a34a; color: #ffffff; font-size: 11.5px; padding: 4px 9px; border-radius: 6px;">v2.11.0 Standalone</span>
                                </div>
                                <p style="font-size: 13.5px; color: #334155; line-height: 1.6; margin: 8px 0 18px 0;">
                                    โปรแกรมขนาดเล็ก (68 KB) ไม่ต้องติดตั้ง Dependency เพิ่มเติม พร้อมตัวช่วยติดตั้ง <strong>Setup Wizard ในตัว</strong> เพียงเปิดโปรแกรมขึ้นมาแล้วคลิกปุ่มติดตั้งเพียงคลิกเดียว ระบบจะทำการตั้งค่าและรันใน System Tray ทันที
                                </p>

                                <!-- Big Download Button -->
                                <div class="ha-wizard-download-row">
                                    <a href="{{ url('/agent/THC_IT_Agent.exe') }}" download class="btn btn-success ha-wizard-btn-download">
                                        <i class="bi bi-download" style="font-size: 18px;"></i> 
                                        <span>ดาวน์โหลด THC_IT_Agent.exe (68 KB)</span>
                                    </a>
                                    <code class="ha-wizard-silent-cmd" title="คำสั่งรันติดตั้งแบบเงียบสำหรับผู้ดูแลระบบ">
                                        THC_IT_Agent.exe /install /silent
                                    </code>
                                </div>

                                <!-- 3 Step Visual Guide -->
                                <div class="ha-wizard-steps">
                                    <div class="ha-wizard-step-item">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                            <span style="width: 24px; height: 24px; border-radius: 50%; background: #16a34a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0;">1</span>
                                            <strong style="font-size: 13px; color: #0f172a;">ดาวน์โหลดและเปิดไฟล์</strong>
                                        </div>
                                        <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.5;">
                                            วางไฟล์ <code>THC_IT_Agent.exe</code> บนเครื่องลูกข่ายแล้วดับเบิลคลิกเปิดไฟล์
                                        </p>
                                    </div>
                                    <div class="ha-wizard-step-item">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                            <span style="width: 24px; height: 24px; border-radius: 50%; background: #16a34a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0;">2</span>
                                            <strong style="font-size: 13px; color: #0f172a;">กดปุ่มใน Setup Wizard</strong>
                                        </div>
                                        <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.5;">
                                            หน้าต่างตัวช่วยติดตั้งจะแสดงผล ให้คลิกปุ่ม <strong>"🚀 ติดตั้งลงในเครื่องทันที"</strong>
                                        </p>
                                    </div>
                                    <div class="ha-wizard-step-item">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                            <span style="width: 24px; height: 24px; border-radius: 50%; background: #16a34a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0;">3</span>
                                            <strong style="font-size: 13px; color: #0f172a;">พร้อมใช้งานทันที</strong>
                                        </div>
                                        <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.5;">
                                            โปรแกรมจะถูกติดตั้งที่ <code>C:\ProgramData\THC-IT-Agent</code> และเริ่มทำงานใน System Tray อัตโนมัติ
                                        </p>
                                    </div>
                                </div>

                                <!-- Features & Instructions Accordion/Box -->
                                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px 16px; font-size: 12.5px; color: #166534; display: flex; flex-direction: column; gap: 6px;">
                                    <div>
                                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i>
                                        <strong>โหมดพกพา (Portable Mode):</strong> หากต้องการสำรวจสเปกครั้งเดียวโดยไม่ติดตั้งลงเครื่อง ให้กดปุ่ม <em>"⚡ รันสำรวจสเปกทันที (ไม่ติดตั้ง)"</em> ในหน้าต่าง Setup Wizard
                                    </div>
                                    <div style="border-top: 1px dashed #86efac; padding-top: 6px;">
                                        <i class="bi bi-trash3-fill text-danger me-1"></i>
                                        <strong>การถอนการติดตั้งอย่างหมดจด (Clean Uninstall):</strong> ถอนการติดตั้งผ่าน Windows <strong>Settings > Apps > Installed apps</strong> (ค้นหา <code>THC IT Agent</code> แล้วกด <code>Uninstall</code>) หรือดับเบิลคลิกเปิดตัวโปรแกรมแล้วเลือกปุ่ม <em>"🗑️ ถอนการติดตั้ง"</em> หรือรันคำสั่ง <code>THC_IT_Agent.exe /uninstall</code>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- Central Remote Update Management Card --}}
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 22px; margin-top: 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.04);">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                                <i class="bi bi-broadcast"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 15.5px; font-weight: 800; color: #0f172a; margin: 0;">
                                    สั่งอัปเดตจากส่วนกลาง (Central Remote Update)
                                </h4>
                                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">
                                    ฝ่ายไอทีสามารถสั่งอัปเดตเครื่องลูกข่ายทั้งหมดได้โดยไม่ต้องไปหน้าเครื่อง ผ่านระบบ Atomic Binary Hot-Swap
                                </p>
                            </div>
                        </div>
                        <span class="badge" style="background: #ecfdf5; color: #059669; font-weight: 700; font-size: 11.5px; padding: 5px 12px; border-radius: 8px; border: 1px solid #a7f3d0;">
                            <i class="bi bi-shield-check me-1"></i> In-Process Atomic Hot-Swap
                        </span>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
                        <div>
                            <div style="font-weight: 700; font-size: 13.5px; color: #1e293b; margin-bottom: 4px;">
                                สถานะเวอร์ชันของเครื่องในโรงพยาบาล:
                            </div>
                            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; font-size: 12.5px;">
                                <span class="badge" style="background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; padding: 4px 9px;">
                                    🟢 เวอร์ชันล่าสุด: {{ $agentLatestCount }} เครื่อง
                                </span>
                                @if($agentOutdatedCount > 0)
                                <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 4px 9px; font-weight: 700;">
                                    🟠 เวอร์ชันเก่า (ต้องอัปเดต): {{ $agentOutdatedCount }} เครื่อง
                                </span>
                                @else
                                <span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #86efac; padding: 4px 9px;">
                                    ✓ ทุกเครื่องเป็นเวอร์ชันล่าสุดแล้ว
                                </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'agents']) }}" class="btn btn-primary" style="font-weight: 700; font-size: 13px; border-radius: 10px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>ไปที่หน้า Agent Fleet เพื่อสั่งอัปเดต</span>
                            @if($agentOutdatedCount > 0)
                            <span class="badge bg-warning text-dark" style="font-size: 11px; padding: 2px 6px;">{{ $agentOutdatedCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>

                    {{-- Official System Documentation & PDF Manuals (v2.11.0) --}}
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 22px; margin-top: 16px; box-shadow: 0 4px 14px rgba(0,0,0,0.04);">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </div>
                                <div>
                                    <h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0;">
                                        คู่มือการใช้งานระบบฉบับทางการ (Official System Documentation - PDF)
                                    </h4>
                                    <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">
                                        เอกสารคู่มือการใช้งานฉบับสมบูรณ์พร้อมภาพประกอบและแผนผังเชิงเทคนิค (เวอร์ชัน v{{ $latestAgentVersion ?? '2.11.0' }} Production)
                                    </p>
                                </div>
                            </div>
                            <a href="{{ url('/manuals') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px; font-weight: 600;">
                                <i class="bi bi-box-arrow-up-right me-1"></i> ไปที่ศูนย์คู่มือทั้งหมด
                            </a>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px;">
                            {{-- User Manual --}}
                            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div style="font-size: 13.5px; font-weight: 700; color: #0369a1; margin-bottom: 4px;">
                                        <i class="bi bi-person-circle me-1"></i> คู่มือสำหรับผู้ใช้งานทั่วไป
                                    </div>
                                    <div style="font-size: 11.5px; color: #0284c7; margin-bottom: 12px;">
                                        การแจ้งซ่อม, ติดตามสถานะ, ตรวจรับงาน (1.8 MB)
                                    </div>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ asset('docs/User_Manual_IT_System_THC.pdf') }}" download class="btn btn-primary btn-sm flex-grow-1" style="font-size: 12px; font-weight: 700; border-radius: 8px; padding: 6px 10px;">
                                        <i class="bi bi-download me-1"></i> ดาวน์โหลด PDF
                                    </a>
                                    <a href="{{ asset('docs/User_Manual_IT_System_THC.pdf') }}" target="_blank" class="btn btn-outline-primary btn-sm" style="font-size: 12px; border-radius: 8px; padding: 6px 10px;" title="เปิดอ่าน">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- Admin Manual --}}
                            <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div style="font-size: 13.5px; font-weight: 700; color: #3730a3; margin-bottom: 4px;">
                                        <i class="bi bi-shield-lock-fill me-1"></i> คู่มือแอดมินและช่างไอที
                                    </div>
                                    <div style="font-size: 11.5px; color: #4338ca; margin-bottom: 12px;">
                                        Helpdesk, IT Assets, Agent Fleet, Remote Commands (2.2 MB)
                                    </div>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ asset('docs/Admin_Manual_IT_System_THC.pdf') }}" download class="btn btn-dark btn-sm flex-grow-1" style="background: #312e81; border-color: #312e81; font-size: 12px; font-weight: 700; border-radius: 8px; padding: 6px 10px;">
                                        <i class="bi bi-download me-1"></i> ดาวน์โหลด PDF
                                    </a>
                                    <a href="{{ asset('docs/Admin_Manual_IT_System_THC.pdf') }}" target="_blank" class="btn btn-outline-dark btn-sm" style="font-size: 12px; border-radius: 8px; padding: 6px 10px;" title="เปิดอ่าน">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>

                            {{-- Developer Manual --}}
                            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                                        <i class="bi bi-code-slash me-1"></i> คู่มือสำหรับผู้พัฒนา
                                    </div>
                                    <div style="font-size: 11.5px; color: #475569; margin-bottom: 12px;">
                                        Architecture, DB Schema, C# Agent, CI/CD (2.3 MB)
                                    </div>
                                </div>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ asset('docs/Developer_Manual_IT_System_THC.pdf') }}" download class="btn btn-secondary btn-sm flex-grow-1" style="background: #334155; border-color: #334155; font-size: 12px; font-weight: 700; border-radius: 8px; padding: 6px 10px;">
                                        <i class="bi bi-download me-1"></i> ดาวน์โหลด PDF
                                    </a>
                                    <a href="{{ asset('docs/Developer_Manual_IT_System_THC.pdf') }}" target="_blank" class="btn btn-outline-secondary btn-sm" style="font-size: 12px; border-radius: 8px; padding: 6px 10px;" title="เปิดอ่าน">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: AGENT TELEMETRY & FULL INSPECTION MODAL                          -->
<!-- ========================================================================= -->
<div id="agentInspectionModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); z-index: 1080; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 860px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; animation: modalScaleIn 0.2s ease-out;">
        
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%); padding: 18px 24px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(255,255,255,0.2); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-cpu-fill"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 16px; line-height: 1.2;">ผลการตรวจเช็คข้อมูลเครื่องจริงจาก Client Agent</div>
                    <div style="font-size: 12px; opacity: 0.9; margin-top: 2px;">Hardware Telemetry, Spec Diff, MDES Compliance & Rescan Hub</div>
                </div>
            </div>
            <button type="button" onclick="closeAgentInspectionModal()" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 34px; height: 34px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">&times;</button>
        </div>

        <!-- Modal Body Content Container -->
        <div style="padding: 22px; overflow-y: auto; flex: 1;" id="agentInspectionModalContent">
            {{-- Loaded dynamically via AJAX --}}
        </div>

        <!-- Modal Footer -->
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div id="modalInspectionFooterLeft" style="font-size: 12px; color: #64748b;"></div>
            <div id="modalInspectionFooterRight" style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAgentInspectionModal()">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: SIDE-BY-SIDE SPEC DIFF COMPARISON MODAL                          -->
<!-- ========================================================================= -->
<div id="auditDiffModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); z-index: 1080; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 760px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); padding: 16px 22px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-arrow-left-right"></i>
                <span>เปรียบเทียบสเปคเดิมในคลัง vs สเปคใหม่ที่สแกนพบ</span>
            </div>
            <button type="button" onclick="closeAuditDiffModal()" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <div style="padding: 22px; overflow-y: auto; flex: 1;" id="auditDiffModalBody">
            {{-- Content loaded dynamically via JS --}}
        </div>

        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeAuditDiffModal()">ปิดหน้าต่าง</button>
            <button type="button" class="btn btn-success btn-sm" id="modalApproveBtn" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-check-lg"></i> อนุมัติและบันทึกลงครุภัณฑ์
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2.5: LINK UNMATCHED HARDWARE AUDIT TO EXISTING ASSET              -->
<!-- ========================================================================= -->
<div id="linkAssetModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); z-index: 1080; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 860px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
        <!-- Modal Header -->
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 16px 22px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-link-45deg" style="font-size: 20px;"></i>
                <span>เชื่อมโยงคอมพิวเตอร์เข้ากับครุภัณฑ์ในระบบ (Link Hardware Audit to Existing Asset)</span>
            </div>
            <button type="button" onclick="closeLinkAssetModal()" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <!-- Modal Body Form -->
        <form id="formLinkAsset" onsubmit="submitLinkAsset(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
            <input type="hidden" id="linkAssetAuditId" name="audit_id">
            <input type="hidden" id="linkAssetTargetId" name="asset_id">

            <div style="padding: 20px; overflow-y: auto; flex: 1;">
                <!-- Telemetry Source Machine Card -->
                <div style="background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; flex-wrap: wrap; gap: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bi bi-cpu-fill me-1"></i> คอมพิวเตอร์ที่ตรวจพบจาก Agent (ต้นทาง)
                        </span>
                        <span class="badge" id="linkAssetHardwareIdBadge" style="background: #fdf4ff; color: #a21caf; border: 1px solid #f0abfc; font-size: 11px; font-family: monospace; font-weight: 700;">
                            HWID: -
                        </span>
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;" id="linkAssetHostInfo">
                        -
                    </div>
                    <div id="linkAssetSpecsSummary" style="font-size: 12.5px; color: #334155; margin-top: 4px; line-height: 1.4;">
                        กำลังโหลดข้อมูลสเปค...
                    </div>
                </div>

                <!-- Step 1: Live Search Asset in Inventory -->
                <div style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: #0f172a;">
                        <i class="bi bi-search me-1 text-primary"></i> ค้นหาครุภัณฑ์ในระบบ เพื่อเชื่อมโยง:
                    </label>
                    <div style="position: relative;">
                        <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"></i>
                        <input type="text" id="linkAssetSearchInput" class="form-control" 
                               placeholder="พิมพ์หมายเลขครุภัณฑ์ (เช่น 7440-...), ชื่อเครื่อง, แผนก, S/N, หรือชื่อผู้ดูแล..." 
                               oninput="debounceSearchAssets()"
                               style="padding-left: 38px; border-radius: 10px; height: 42px; font-size: 13.5px; border: 1.5px solid #cbd5e1;">
                        <button type="button" id="linkAssetClearSearch" onclick="clearLinkSearch()" 
                                style="display: none; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: 16px; cursor: pointer;">&times;</button>
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 5px;">
                        <i class="bi bi-info-circle me-1"></i> ระบบจะค้นหาครุภัณฑ์สถานะปกติในคลัง สามารถคลิกเลือกเครื่องที่ต้องการได้จากรายการผลลัพธ์ด้านล่าง
                    </div>
                </div>

                <!-- Search Results Container -->
                <div id="linkAssetResultsContainer" style="max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px; margin-bottom: 16px; background: #f8fafc; min-height: 60px;">
                    <div id="linkAssetResultsEmpty" style="text-align: center; color: #94a3b8; padding: 18px; font-size: 12.5px;">
                        พิมพ์คำค้นหาข้างต้นเพื่อค้นหาครุภัณฑ์ที่ต้องการเชื่อมโยง
                    </div>
                </div>

                <!-- Step 2: Selected Asset Details & Sync Options (Initially Hidden) -->
                <div id="linkAssetSelectedCard" style="display: none; background: #eff6ff; border: 1.5px solid #93c5fd; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <div style="font-weight: 700; font-size: 13px; color: #1e40af; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="bi bi-check-circle-fill me-1 text-primary"></i> ครุภัณฑ์ที่เลือกเชื่อมโยง:</span>
                        <span id="selectedAssetBadge" class="badge bg-primary" style="font-size: 12px;">-</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 12.5px; margin-bottom: 14px;">
                        <div>
                            <span class="text-muted">ชื่อรายการ:</span> <strong id="selectedAssetName" class="text-dark">-</strong>
                        </div>
                        <div>
                            <span class="text-muted">แผนก:</span> <strong id="selectedAssetDept" class="text-dark">-</strong>
                        </div>
                        <div>
                            <span class="text-muted">ผู้ครอบครอง:</span> <strong id="selectedAssetCustodian" class="text-dark">-</strong>
                        </div>
                        <div>
                            <span class="text-muted">สเปคเดิม:</span> <span id="selectedAssetSpecs" class="text-secondary">-</span>
                        </div>
                    </div>

                    <!-- Sync Options Checkboxes -->
                    <div style="border-top: 1px solid #bfdbfe; padding-top: 12px;">
                        <div style="font-weight: 700; font-size: 12px; color: #1e40af; margin-bottom: 8px;">
                            ตัวเลือกการอัปเดตข้อมูลลงครุภัณฑ์:
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 8px; font-size: 12.5px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="sync_hwid" id="chkSyncHwid" value="1" checked class="form-check-input" style="cursor: pointer;">
                                <span>อัปเดต <strong>HardwareID (UUID)</strong> ของเครื่องนี้</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="sync_sn" id="chkSyncSn" value="1" checked class="form-check-input" style="cursor: pointer;">
                                <span>อัปเดต <strong>Serial Number</strong> จาก BIOS</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                <input type="checkbox" name="sync_specs" id="chkSyncSpecs" value="1" checked class="form-check-input" style="cursor: pointer;">
                                <span>อัปเดต <strong>สเปคจริง (CPU, RAM, SSD/HDD, OS)</strong></span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                <input type="checkbox" checked disabled class="form-check-input">
                                <span>บันทึกสถานะตรวจนับปีงบฯ <strong>{{ $fiscalYear }}</strong> ทันที</span>
                            </label>
                        </div>
                    </div>

                    <!-- Optional Note -->
                    <div style="margin-top: 12px;">
                        <label class="form-label" style="font-size: 11.5px; font-weight: 600; color: #1e40af; margin-bottom: 3px;">หมายเหตุเพิ่มเติม (ถ้ามี):</label>
                        <input type="text" id="linkAssetNotes" name="notes" class="form-control form-control-sm" placeholder="เช่น เครื่องประจำห้องจ่ายยาชั้น 1">
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeLinkAssetModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitLinkAsset" disabled style="font-weight: 700; padding: 6px 20px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); border: none;">
                    <i class="bi bi-link-45deg me-1"></i> ยืนยันเชื่อมโยงและอัปเดตสเปค
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DELETE AGENT MACHINE FROM SYSTEM                                   -->
<!-- ========================================================================= -->
<div id="deleteMachineModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); z-index: 1080; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 580px; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden; animation: modalScaleIn 0.2s ease-out;">
        <div style="background: linear-gradient(135deg, #e11d48 0%, #b91c1c 100%); padding: 16px 22px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-trash3-fill"></i>
                <span>ลบเครื่องที่ติดตั้ง Agent ออกจากระบบ</span>
            </div>
            <button type="button" onclick="closeDeleteMachineModal()" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <div style="padding: 22px; overflow-y: auto;">
            <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 15px; color: #9f1239; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-display"></i>
                        <span id="delMachineHostname">-</span>
                    </div>
                    <span id="delMachineOnlineBadge" class="badge" style="font-size: 11px;">-</span>
                </div>
                <div style="font-size: 12px; color: #475569; display: flex; flex-direction: column; gap: 3px;">
                    <div>IP Address: <code id="delMachineIp">-</code></div>
                    <div>Hardware ID: <code id="delMachineHwid" style="word-break: break-all;">-</code></div>
                </div>
            </div>

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin-bottom: 18px;">
                <div style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 8px;">
                    <i class="bi bi-shield-exclamation text-warning me-1"></i> สิ่งที่ระบบจะดำเนินการ:
                </div>
                <ul style="font-size: 12.5px; color: #475569; margin: 0; padding-left: 20px; line-height: 1.6;">
                    <li>ลบประวัติสแกนฮาร์ดแวร์ล่าสุดของเครื่องนี้ออกจากระบบ</li>
                    <li>ยกเลิกคำสั่งค้างท่อทั้งหมดของเครื่องนี้</li>
                    <li>ล้างสถานะออนไลน์ออกจาก Cache และ Registry ทันที</li>
                    <li><strong class="text-success">ไม่มีผลกระทบต่อข้อมูลครุภัณฑ์ในระบบคลังพัสดุ (ปลอดภัย)</strong></li>
                </ul>
            </div>

            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 16px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0;">
                    <input type="checkbox" id="chkRemoteUninstall" class="form-check-input" checked style="cursor: pointer; margin-top: 3px;">
                    <div>
                        <strong style="font-size: 13px; color: #991b1b; display: block;">
                            ส่งคำสั่งถอนการติดตั้งไปยัง Agent ทันที (Remote Uninstall)
                        </strong>
                        <span style="font-size: 11.5px; color: #7f1d1d;">
                            หากเครื่องยังออนไลน์อยู่ โปรแกรม Agent บนเครื่องลูกข่ายจะหยุดทำงาน ลบการตั้งค่า Startup และถอนตัวเองออกอัตโนมัติ
                        </span>
                    </div>
                </label>
            </div>
        </div>

        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeDeleteMachineModal()">ยกเลิก</button>
            <button type="button" class="btn btn-danger btn-sm" id="btnConfirmDeleteMachine" onclick="confirmDeleteMachineSubmit()" style="font-weight: 700; padding: 6px 18px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);">
                <i class="bi bi-trash3-fill me-1"></i> ยืนยันลบเครื่องนี้
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: REGISTER NEW ASSET DIRECTLY FROM HARDWARE AUDIT                  -->
<!-- ========================================================================= -->
<div id="createAssetModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(5px); z-index: 1080; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 820px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.3); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); padding: 16px 22px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 800; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-plus-circle-fill"></i>
                <span>ลงทะเบียนครุภัณฑ์ใหม่จากผลสแกนฮาร์ดแวร์ (New Asset Registration)</span>
            </div>
            <button type="button" onclick="closeCreateAssetModal()" style="background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 32px; height: 32px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center;">&times;</button>
        </div>

        <form id="formRegisterAssetFromAudit" onsubmit="submitCreateAssetFromAudit(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
            <input type="hidden" id="newAssetAuditId" name="audit_id">
            <input type="hidden" id="newAssetHardwareId" name="hardware_id">
            
            <div style="padding: 22px; overflow-y: auto; flex: 1;">
                {{-- Discovered Telemetry Specs Summary Banner --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; flex-wrap: wrap; gap: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #0f766e; text-transform: uppercase; letter-spacing: 0.5px;">
                            <i class="bi bi-cpu-fill me-1"></i> สเปคที่อ่านได้จริงจากเครื่อง (Hardware Telemetry)
                        </span>
                        <span class="badge" id="newAssetHardwareIdBadge" style="background: #fdf4ff; color: #a21caf; border: 1px solid #f0abfc; font-size: 11px; font-family: monospace; font-weight: 700;">
                            HWID: -
                        </span>
                    </div>
                    <div id="newAssetSpecsSummaryText" style="font-size: 13px; color: #1e293b; line-height: 1.5; font-weight: 500;">
                        กำลังโหลดข้อมูลสเปค...
                    </div>
                </div>

                {{-- Form Fields --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">หมายเลขครุภัณฑ์ (Asset Code) <span class="text-danger">*</span></label>
                        <input type="text" id="newAssetCode" name="asset_code" class="form-control" required placeholder="เช่น 7440-001-0001/{{ substr((string)$fiscalYear, -2) }}">
                        <span class="form-text" style="font-size: 11px; color: #64748b;">รหัสหมายเลขครุภัณฑ์ตามทะเบียนพัสดุ</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">ประเภทอุปกรณ์ (Device Type) <span class="text-danger">*</span></label>
                        <select id="newAssetDeviceTypeId" name="device_type_id" class="form-select" required>
                            @foreach($deviceTypes ?? [] as $dt)
                                <option value="{{ $dt->id }}" data-code="{{ $dt->code }}">{{ $dt->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">ชื่อรายการครุภัณฑ์ (Asset Name) <span class="text-danger">*</span></label>
                        <input type="text" id="newAssetName" name="name" class="form-control" required placeholder="เช่น เครื่องคอมพิวเตอร์ สำหรับงานประมวลผล แบบที่ 2">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">ยี่ห้อ (Brand)</label>
                        <input type="text" id="newAssetBrand" name="brand" class="form-control" placeholder="เช่น Dell, HP">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">รุ่น (Model)</label>
                        <input type="text" id="newAssetModel" name="model" class="form-control" placeholder="เช่น ProDesk 400 G7">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">Serial Number (S/N)</label>
                        <input type="text" id="newAssetSerial" name="serial_number" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">แผนก / หน่วยงานที่ใช้งาน</label>
                        <select id="newAssetDepartmentId" name="department_id" class="form-select">
                            <option value="">-- ไม่ระบุแผนก --</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">จุดวาง / ห้องทำงาน</label>
                        <input type="text" id="newAssetLocation" name="location_detail" class="form-control" placeholder="เช่น โต๊ะทำงานชั้น 2, ห้องตรวจ 1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">ผู้ครอบครอง / ผู้ดูแล</label>
                        <input type="text" id="newAssetCustodian" name="custodian_name" class="form-control" placeholder="เช่น ชื่อเจ้าหน้าที่ผู้รับผิดชอบ">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">ปีงบประมาณ</label>
                        <input type="text" id="newAssetBudgetYear" name="budget_year" class="form-control" value="{{ $fiscalYear }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">ราคาจัดซื้อ (บาท)</label>
                        <input type="number" step="0.01" id="newAssetPrice" name="price" class="form-control" placeholder="24500.00">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">แหล่งเงินที่ใช้ซื้อ (Budget Source)</label>
                        <select id="newAssetBudgetSourceId" name="budget_source_id" class="form-select">
                            <option value="">-- ไม่ระบุแหล่งเงิน --</option>
                            @foreach($budgetSources ?? [] as $bs)
                                <option value="{{ $bs->id }}">{{ $bs->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">วิธีการได้มา (Acquisition Method)</label>
                        <select id="newAssetAcquisitionMethodId" name="acquisition_method_id" class="form-select">
                            <option value="">-- ไม่ระบุวิธีการได้มา --</option>
                            @foreach($acquisitionMethods ?? [] as $am)
                                <option value="{{ $am->id }}">{{ $am->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">สิทธิ์การครอบครอง / กรรมสิทธิ์</label>
                        <select id="newAssetOwnershipType" name="ownership_type" class="form-select">
                            <option value="owned" selected>เป็นของโรงพยาบาล (Owned)</option>
                            <option value="rented">เครื่องเช่า / เช่าใช้ (Rented / Leased)</option>
                            <option value="borrowed">ยืมใช้งาน (Borrowed)</option>
                            <option value="donated">รับบริจาค (Donated)</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-size: 12.5px; font-weight: 700; color: #334155;">หมายเหตุ</label>
                        <input type="text" id="newAssetNotes" name="notes" class="form-control" placeholder="หมายเหตุเพิ่มเติม">
                    </div>
                </div>
            </div>

            <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <a id="btnOpenFullAssetForm" href="#" target="_blank" style="font-size: 12.5px; color: #0284c7; font-weight: 600; text-decoration: none;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> หรือเปิดฟอร์มลงทะเบียนแบบเต็มรูปแบบ
                    </a>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeCreateAssetModal()">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitNewAsset" style="font-weight: 700; padding: 6px 18px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none;">
                        <i class="bi bi-plus-circle-fill me-1"></i> บันทึกและสร้างครุภัณฑ์ใหม่
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: REMOTE SCAN ALL AGENT MACHINES MONITORING HUB                     -->
<!-- ========================================================================= -->
<div id="remoteScanAllModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); z-index: 1080; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 760px; max-height: 92vh; display: flex; flex-direction: column; box-shadow: 0 25px 60px -15px rgba(0,0,0,0.35); overflow: hidden; animation: modalScaleIn 0.2s ease-out;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0284c7 0%, #0f766e 100%); padding: 18px 24px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.2); backdrop-filter: blur(6px); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 17px; line-height: 1.2;">ศูนย์ดึงข้อมูลฮาร์ดแวร์ระยะไกล (Remote Agent Telemetry Console)</div>
                    <div style="font-size: 12.5px; opacity: 0.9; margin-top: 3px;">
                        ระบบดึงข้อมูลแบบ On-Demand: ส่งสัญญาณเรียกดูสเปคจากเครื่องที่ติดตั้ง Agent และแสดงข้อมูลจริงที่ได้รับกลับมา
                    </div>
                </div>
            </div>
            <button type="button" onclick="closeRemoteScanAllModal()" style="background: rgba(255,255,255,0.18); border: none; color: #ffffff; width: 34px; height: 34px; border-radius: 8px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.18)'">&times;</button>
        </div>

        <!-- Body -->
        <div style="padding: 22px; overflow-y: auto; flex: 1;">
            
            <!-- On-Demand Principle Info Banner -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #166534;">
                <i class="bi bi-shield-check fs-5 text-success"></i>
                <div>
                    <strong>หลักการ On-Demand Telemetry:</strong> เครื่องลูกข่ายจะไม่ส่งข้อมูลใดๆ จนกว่าแอดมินจะกดดึงข้อมูล เมื่อส่งคำสั่งแล้ว เครื่องปลายทางจะตรวจนับสเปคจริงและส่งกลับมาแสดงผลที่นี่ทันที
                </div>
            </div>

            <!-- Progress Status Banner -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span id="scanStatusPulse" style="width: 10px; height: 10px; border-radius: 50%; background: #0284c7; display: inline-block; animation: pulseGlow 1.5s infinite;"></span>
                        <strong style="font-size: 14px; color: #0f172a;" id="scanStatusTitle">กำลังส่งคำสั่งดึงข้อมูลและรอเครื่องตอบสนอง...</strong>
                    </div>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px; font-family: monospace;" id="scanBatchBadge">Batch: -</span>
                </div>

                <!-- Progress Bar -->
                <div class="progress" style="height: 12px; background: #e2e8f0; border-radius: 99px; overflow: hidden; margin-bottom: 12px;">
                    <div id="scanProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%; transition: width 0.4s ease;"></div>
                </div>

                <!-- 3 Mini KPIs -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px;">
                        <div style="font-size: 11.5px; color: #64748b; font-weight: 600;">เป้าหมายทั้งหมด</div>
                        <div style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 2px;" id="scanStatTotal">0</div>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 10px; padding: 10px;">
                        <div style="font-size: 11.5px; color: #166534; font-weight: 600;">ได้รับข้อมูลจริงแล้ว</div>
                        <div style="font-size: 18px; font-weight: 800; color: #15803d; margin-top: 2px;" id="scanStatCompleted">0</div>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #fef08a; border-radius: 10px; padding: 10px;">
                        <div style="font-size: 11.5px; color: #854d0e; font-weight: 600;">รอดึงคำสั่ง / สแกน</div>
                        <div style="font-size: 18px; font-weight: 800; color: #ca8a04; margin-top: 2px;" id="scanStatPending">0</div>
                    </div>
                </div>
            </div>

            <!-- Target Machines List Table -->
            <div style="margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 13px; font-weight: 700; color: #334155;">
                    <i class="bi bi-pc-display me-1 text-primary"></i> รายชื่อเครื่องลูกข่ายที่ส่งคำสั่ง:
                </span>
                <span style="font-size: 11.5px; color: #94a3b8;">รีเฟรชสถานะอัตโนมัติทุก 2.5 วินาที</span>
            </div>

            <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; max-height: 290px; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; position: sticky; top: 0; z-index: 2;">
                        <tr>
                            <th style="padding: 10px 14px; width: 170px;">ชื่อเครื่อง (Hostname / IP)</th>
                            <th style="padding: 10px 14px; text-align: center; width: 135px;">สถานะการดึง</th>
                            <th style="padding: 10px 14px;">ข้อมูลจริงที่ได้รับ (Verified Telemetry)</th>
                            <th style="padding: 10px 14px; text-align: right; width: 120px;">เวลาที่ได้รับ</th>
                        </tr>
                    </thead>
                    <tbody id="scanTargetsTableBody">
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 25px; color: #94a3b8;">
                                กำลังโหลดข้อมูลเป้าหมาย...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 12px; color: #64748b;" id="scanModalFooterInfo">
                <i class="bi bi-info-circle me-1"></i>เครื่องลูกข่ายจะดึงคำสั่งทันทีผ่าน Agent Daemon หรือภายใน 5 นาทีผ่าน Watchdog
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeRemoteScanAllModal()">ปิดหน้าต่าง</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="finishAndReloadScan()" id="btnScanFinishReload" style="font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-arrow-clockwise"></i> รีเฟรชหน้ารายการ
                </button>
            </div>
        </div>

    </div>
</div>

<style>
/* ========================================================================= */
/* HARDWARE AUDITS RESPONSIVE DESIGN SYSTEM (ALL SCREEN SIZES)               */
/* Desktop (>=1200px), Laptop (992-1199px), Tablet (768-991px), Mobile (<768px) */
/* ========================================================================= */

@keyframes modalScaleIn {
    from { transform: scale(0.96); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
@keyframes pulseGlow {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(2, 132, 199, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
}

/* 1. TOPBAR ACTIONS */
.ha-topbar-actions {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ha-topbar-btn-map {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
    color: #f59e0b !important;
    border: 1px solid rgba(245, 158, 11, 0.4) !important;
    font-weight: 700 !important;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.2) !important;
}
.ha-topbar-btn-map:hover {
    background: linear-gradient(135deg, #273549 0%, #1a2234 100%) !important;
    color: #fbbf24 !important;
    border-color: #f59e0b !important;
    transform: translateY(-1px);
}
.ha-hero-btn-map {
    background: rgba(15, 23, 42, 0.45) !important;
    backdrop-filter: blur(8px) !important;
    border: 1px solid rgba(245, 158, 11, 0.5) !important;
    color: #fef08a !important;
    font-weight: 700 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 6px 14px !important;
    border-radius: 9px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2) !important;
    transition: all 0.2s ease !important;
}
.ha-hero-btn-map:hover {
    background: rgba(15, 23, 42, 0.7) !important;
    color: #ffffff !important;
    border-color: #f59e0b !important;
    transform: translateY(-1px);
}

/* 1.5 IT COMMAND & TOOLKIT HUB */
.ha-toolkit-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 22px;
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.ha-toolkit-card:hover {
    box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
}
.ha-toolkit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 16px 20px;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #edf2f7;
}
.ha-toolkit-title-group {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ha-toolkit-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.ha-toolkit-heading {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.2px;
}
.ha-toolkit-sub {
    font-size: 12px;
    color: #64748b;
    margin: 2px 0 0 0;
}
.ha-toolkit-header-badges {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ha-badge-pill {
    font-size: 11.5px;
    padding: 4px 10px;
    border-radius: 99px;
    font-weight: 600;
}
.ha-toolkit-body {
    padding: 16px 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    gap: 16px;
    background: #ffffff;
}
.ha-tool-group {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ha-tool-group-label {
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    display: flex;
    align-items: center;
}
.ha-tool-group-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.ha-tool-btn {
    height: 36px;
    padding: 0 12px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
    text-decoration: none !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #334155;
}
.ha-tool-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
}
.ha-tool-btn-primary {
    background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%) !important;
    color: #ffffff !important;
    border: none !important;
    box-shadow: 0 2px 6px rgba(15, 118, 110, 0.25) !important;
}
.ha-tool-btn-primary:hover {
    background: linear-gradient(135deg, #115e59 0%, #0284c7 100%) !important;
    color: #ffffff !important;
}
.ha-tool-btn-success {
    background: #f0fdf4 !important;
    border-color: #86efac !important;
    color: #166534 !important;
}
.ha-tool-btn-success:hover {
    background: #dcfce7 !important;
    border-color: #4ade80 !important;
}
.ha-tool-btn-warning {
    background: #fffbeb !important;
    border-color: #fcd34d !important;
    color: #b45309 !important;
}
.ha-tool-btn-map {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
    color: #fef08a !important;
    border: 1px solid rgba(245, 158, 11, 0.4) !important;
}
.ha-tool-btn-map:hover, .ha-tool-btn-map.active {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%) !important;
    border-color: #f59e0b !important;
    color: #fde047 !important;
}
.ha-tool-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 99px;
    background: #f1f5f9;
    color: #475569;
    margin-left: 2px;
}

/* 3D DEPARTMENT MAP DIAGRAM SYSTEM */
.ha-map3d-pane {
    background: #ffffff;
}
.ha-map3d-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-bottom: 16px;
}
.ha-map3d-badge-live {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 700;
    color: #0f766e;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    padding: 3px 10px;
    border-radius: 99px;
    margin-bottom: 4px;
}
.ha-live-beacon {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    animation: pulseBeacon 1.5s infinite;
}
@keyframes pulseBeacon {
    0% { transform: scale(0.9); opacity: 0.8; }
    50% { transform: scale(1.3); opacity: 1; }
    100% { transform: scale(0.9); opacity: 0.8; }
}
.ha-map3d-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.ha-map3d-subtitle {
    font-size: 12.5px;
    color: #64748b;
    margin: 2px 0 0 0;
}
.ha-map3d-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.ha-map3d-toolbar {
    background: #0f172a;
    border-radius: 14px 14px 0 0;
    padding: 14px 16px;
    border: 1px solid #1e293b;
    border-bottom: none;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ha-map3d-toolbar-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.ha-map3d-bld-group {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.ha-bld-btn {
    background: #1e293b;
    color: #cbd5e1;
    border: 1px solid #334155;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.15s ease;
}
.ha-bld-btn:hover, .ha-bld-btn.active {
    background: #0284c7;
    color: #ffffff;
    border-color: #38bdf8;
    box-shadow: 0 0 10px rgba(56, 189, 248, 0.35);
}
.ha-map3d-floor-group {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #1e293b;
    padding: 3px 6px;
    border-radius: 8px;
    border: 1px solid #334155;
}
.ha-floor-label {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
    margin-right: 4px;
}
.ha-floor-btn {
    background: transparent;
    color: #94a3b8;
    border: none;
    font-size: 11.5px;
    padding: 3px 8px;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.15s;
}
.ha-floor-btn:hover, .ha-floor-btn.active {
    background: #0f766e;
    color: #ffffff;
}
.ha-map3d-search-box {
    position: relative;
    flex: 1;
    min-width: 200px;
    max-width: 320px;
}
.ha-map3d-search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    font-size: 12px;
}
.ha-map3d-search-box input {
    width: 100%;
    height: 34px;
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 8px;
    padding-left: 28px;
    padding-right: 26px;
    color: #ffffff;
    font-size: 12.5px;
}
.ha-map3d-search-box input:focus {
    outline: none;
    border-color: #38bdf8;
    background: #0f172a;
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25);
}
.ha-search-clear {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 16px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.ha-map3d-status-filters {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.ha-status-filter-btn {
    background: #1e293b;
    border: 1px solid #334155;
    color: #94a3b8;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.15s;
}
.ha-status-filter-btn:hover, .ha-status-filter-btn.active {
    background: #334155;
    color: #ffffff;
    border-color: #64748b;
}
.ha-status-filter-btn.ha-filter-audited.active {
    background: #064e3b;
    border-color: #059669;
    color: #34d399;
}
.ha-status-filter-btn.ha-filter-online.active {
    background: #082f49;
    border-color: #0284c7;
    color: #38bdf8;
}
.ha-status-filter-btn.ha-filter-pending.active {
    background: #451a03;
    border-color: #d97706;
    color: #fbbf24;
}
.ha-status-filter-btn.ha-filter-warning.active {
    background: #450a0a;
    border-color: #dc2626;
    color: #f87171;
}
.ha-map3d-camera-deck {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.ha-map3d-camera-deck .btn-dark {
    background: #1e293b;
    border-color: #334155;
    color: #cbd5e1;
    font-size: 11.5px;
}
.ha-map3d-camera-deck .btn-dark:hover, .ha-map3d-camera-deck .btn-dark.active {
    background: #0284c7;
    border-color: #38bdf8;
    color: #ffffff;
}
.ha-btn-design-mode {
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 6px;
}
.ha-btn-design-mode.active {
    background: #f59e0b !important;
    color: #000000 !important;
    border-color: #f59e0b !important;
    box-shadow: 0 0 10px rgba(245, 158, 11, 0.5) !important;
}
.ha-design-mode-banner {
    background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
    border: 1px solid #f59e0b;
    border-radius: 8px;
    padding: 8px 12px;
    color: #ffffff;
    font-size: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    animation: pulseGlow 3s infinite;
}
.ha-map3d-viewport-wrapper {
    position: relative;
    height: 640px;
    background: radial-gradient(circle at 50% 35%, #0d1a38 0%, #060b18 100%);
    border: 1px solid #1e293b;
    border-radius: 0 0 14px 14px;
    overflow: hidden;
    user-select: none;
    box-shadow: inset 0 0 60px rgba(0, 0, 0, 0.6);
}
.ha-map3d-stage {
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    cursor: grab;
    display: flex;
    align-items: center;
    justify-content: center;
    perspective: 1200px;
}
.ha-map3d-stage:active {
    cursor: grabbing;
}
.ha-map3d-world {
    position: relative;
    width: 1400px;
    height: 1000px;
    transform-style: preserve-3d;
    transition: transform 0.15s cubic-bezier(0.1, 0.9, 0.2, 1);
}
.ha-map3d-grid-layer {
    position: absolute;
    width: 100%;
    height: 100%;
    background-image: 
        linear-gradient(to right, rgba(56, 189, 248, 0.08) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(56, 189, 248, 0.08) 1px, transparent 1px);
    background-size: 50px 50px;
    border: 1px solid rgba(56, 189, 248, 0.15);
    border-radius: 20px;
    box-shadow: 0 0 40px rgba(14, 165, 233, 0.08);
    pointer-events: none;
}
.ha-map3d-rooms-container {
    position: absolute;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
}
/* 3D Room Box */
.ha-3d-room {
    position: absolute;
    border-radius: 12px;
    background: rgba(15, 23, 42, 0.82);
    backdrop-filter: blur(8px);
    border: 2px solid rgba(56, 189, 248, 0.4);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), inset 0 0 15px rgba(56, 189, 248, 0.1);
    transition: all 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
    cursor: pointer;
    transform-style: preserve-3d;
    overflow: hidden;
}
.ha-3d-room:hover {
    border-color: #38bdf8;
    box-shadow: 0 14px 36px rgba(14, 165, 233, 0.35), inset 0 0 25px rgba(56, 189, 248, 0.25);
    transform: translateZ(12px);
}
.ha-3d-room.focused {
    border-color: #f59e0b;
    box-shadow: 0 16px 42px rgba(245, 158, 11, 0.45), inset 0 0 30px rgba(245, 158, 11, 0.2);
    transform: translateZ(20px);
}
.ha-3d-room-header {
    padding: 8px 12px;
    background: rgba(30, 41, 59, 0.85);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.ha-3d-room-title {
    font-size: 12px;
    font-weight: 800;
    color: #f1f5f9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70%;
}
.ha-3d-room-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 99px;
    background: #0284c7;
    color: #ffffff;
}
.ha-3d-room-body {
    position: relative;
    width: 100%;
    height: calc(100% - 34px);
    padding: 10px;
    background-image: radial-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px);
    background-size: 18px 18px;
}
/* 3D Workstation Node */
.ha-3d-device {
    position: absolute;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(30, 41, 59, 0.95);
    border: 1.5px solid #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 15px;
    color: #e2e8f0;
    transition: all 0.2s cubic-bezier(0.2, 0.8, 0.2, 1);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
    transform-style: preserve-3d;
}
.ha-3d-device:hover {
    transform: translateZ(16px) scale(1.25);
    border-color: #ffffff;
    box-shadow: 0 8px 20px rgba(255, 255, 255, 0.35);
    z-index: 50;
}
.ha-3d-device.status-audited {
    border-color: #10b981;
    color: #34d399;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
}
.ha-3d-device.status-online {
    border-color: #38bdf8;
    color: #38bdf8;
    box-shadow: 0 0 14px rgba(56, 189, 248, 0.7);
    animation: nodePulse 2s infinite;
}
.ha-3d-device.status-pending {
    border-color: #f59e0b;
    color: #fbbf24;
    box-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
}
.ha-3d-device.status-not-audited {
    border-color: #ef4444;
    color: #f87171;
}
.ha-3d-device.status-warning {
    border-color: #ea580c;
    color: #fb923c;
}
@keyframes nodePulse {
    0% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(56, 189, 248, 0); }
    100% { box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
}
/* HUD Cards */
.ha-hud-card {
    position: absolute;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 12px 14px;
    color: #ffffff;
    z-index: 20;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}
.ha-hud-top-left { top: 14px; left: 14px; max-width: 290px; }
.ha-hud-top-right { top: 14px; right: 14px; }
.ha-hud-bottom-left { bottom: 14px; left: 14px; }
.ha-hud-bottom-right { bottom: 14px; right: 14px; text-align: right; }
.ha-hud-title { font-size: 13.5px; font-weight: 800; color: #f8fafc; }
.ha-hud-sub { font-size: 11px; color: #94a3b8; margin-top: 1px; }
.ha-hud-stats-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    padding-top: 6px;
}
.ha-hud-stat { display: flex; flex-direction: column; }
.ha-hud-stat-val { font-size: 14px; font-weight: 800; }
.ha-hud-stat-lbl { font-size: 10px; color: #94a3b8; }
.ha-hud-btn {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(255, 255, 255, 0.2);
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 7px;
}
.ha-legend-grid {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    font-size: 11px;
    color: #cbd5e1;
}
.ha-legend-item { display: flex; align-items: center; gap: 4px; }
.ha-legend-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
/* Floating Tooltip */
.ha-map3d-tooltip {
    position: absolute;
    z-index: 100;
    pointer-events: none;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(12px);
    border: 1px solid #38bdf8;
    border-radius: 10px;
    padding: 10px 14px;
    color: #ffffff;
    font-size: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
    transform: translate(-50%, -120%);
    transition: opacity 0.15s ease;
    min-width: 220px;
}
/* Side Drawer */
.ha-map3d-drawer {
    position: absolute;
    top: 0;
    right: 0;
    width: 360px;
    max-width: 90vw;
    height: 100%;
    background: rgba(15, 23, 42, 0.96);
    backdrop-filter: blur(16px);
    border-left: 1px solid rgba(56, 189, 248, 0.3);
    z-index: 30;
    box-shadow: -10px 0 40px rgba(0, 0, 0, 0.6);
    display: flex;
    flex-direction: column;
    animation: slideInRight 0.25s cubic-bezier(0.1, 0.9, 0.2, 1);
}
@keyframes slideInRight {
    from { transform: translateX(100%); }
    to { transform: translateX(0); }
}
.ha-drawer-header {
    padding: 18px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(30, 41, 59, 0.5);
}
.ha-drawer-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.ha-drawer-badge {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 99px;
    background: #0284c7;
    color: #ffffff;
    text-transform: uppercase;
}
.ha-drawer-close {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 22px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}
.ha-drawer-close:hover { color: #ffffff; }
.ha-drawer-hostname { font-size: 17px; font-weight: 800; color: #ffffff; margin: 0; }
.ha-drawer-ip { font-size: 12px; color: #38bdf8; font-family: monospace; }
.ha-drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 20px;
    font-size: 12.5px;
    color: #cbd5e1;
}
.ha-drawer-spec-item {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
}
.ha-drawer-spec-lbl { color: #94a3b8; }
.ha-drawer-spec-val { color: #f8fafc; font-weight: 600; text-align: right; }
.ha-drawer-footer {
    padding: 14px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(15, 23, 42, 0.8);
    display: flex;
    flex-direction: column;
    gap: 8px;
}
@keyframes radarPing {
    0% { transform: scale(0.2); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}

/* 2. HERO BANNER */
.ha-hero-banner {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 45%, #047857 100%);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #ffffff;
    box-shadow: 0 10px 30px -5px rgba(6, 78, 59, 0.35);
    margin-bottom: 22px;
    position: relative;
    overflow: hidden;
}
.ha-hero-blob-1 {
    position: absolute;
    right: -40px;
    top: -40px;
    width: 260px;
    height: 260px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.ha-hero-blob-2 {
    position: absolute;
    left: 20%;
    bottom: -60px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.ha-hero-content {
    padding: 24px 28px;
    position: relative;
    z-index: 2;
}
.ha-hero-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
.ha-hero-title-area {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
    min-width: 280px;
}
.ha-hero-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #ffffff;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}
.ha-hero-text {
    flex: 1;
    min-width: 200px;
}
.ha-hero-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.ha-hero-title {
    margin: 0;
    font-size: 21px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.3px;
    line-height: 1.25;
}
.ha-hero-fy-badge {
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 99px;
    white-space: nowrap;
}
.ha-hero-desc {
    font-size: 13px;
    opacity: 0.9;
    margin: 4px 0 0 0;
    line-height: 1.5;
    max-width: 720px;
}
.ha-hero-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.ha-hero-btn-download {
    background: #ffffff !important;
    color: #065f46 !important;
    font-weight: 700 !important;
    border: none !important;
    border-radius: 10px !important;
    padding: 9px 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease !important;
    white-space: nowrap !important;
}
.ha-hero-btn-download:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2) !important;
}
.ha-hero-fy-box {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 12px;
    padding: 6px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ha-hero-fy-form {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

/* 3. 5 TOP KPI STATISTIC CARDS */
.ha-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}
.ha-stat-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 16px 18px;
    border: 1px solid #e2e8f0;
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.ha-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.ha-stat-card-total { border-left: 4px solid #0d9488; }
.ha-stat-card-audited { border-left: 4px solid #10b981; }
.ha-stat-card-pending { border-left: 4px solid #f59e0b; }
.ha-stat-card-online { border-left: 4px solid #06b6d4; }
.ha-stat-card-warning { border-left: 4px solid #ef4444; }

.ha-stat-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.ha-stat-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    line-height: 1.35;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.ha-stat-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.ha-stat-value {
    font-size: 25px;
    font-weight: 800;
    line-height: 1.1;
    margin-top: 2px;
}
.ha-stat-subtext {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 6px;
}

/* 4. MODERN SEGMENTED TABS BAR */
.ha-tabs-bar {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.ha-tabs-bar::-webkit-scrollbar {
    display: none;
}
.tab-nav-btn {
    flex: 1 1 0;
    min-width: 0;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border-radius: 10px;
    color: #475569;
    background: transparent;
    border: 1px solid transparent;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    min-height: 44px;
    user-select: none;
    box-sizing: border-box;
}
.tab-nav-btn i {
    font-size: 15px;
    color: #64748b;
    transition: all 0.18s ease;
    flex-shrink: 0;
}
.tab-nav-btn .tab-label {
    white-space: nowrap;
    letter-spacing: -0.1px;
}
.tab-nav-btn:hover {
    color: #0f172a;
    background: rgba(255, 255, 255, 0.65);
    border-color: rgba(226, 232, 240, 0.8);
}
.tab-nav-btn:hover i {
    color: #0f766e;
    transform: scale(1.08);
}
.tab-nav-btn.active {
    background: #ffffff !important;
    color: #0f766e !important;
    font-weight: 700 !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 2px 6px -1px rgba(15, 23, 42, 0.08), 0 1px 3px -1px rgba(15, 23, 42, 0.05);
}
.tab-nav-btn.active i {
    color: #0d9488 !important;
}
.tab-nav-btn.tab-map3d i {
    color: #f59e0b;
}
.tab-nav-btn.tab-map3d.active i {
    color: #d97706 !important;
}

/* TAB MICRO-BADGES */
.ha-tab-badge {
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 999px;
    line-height: 1.2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.2px;
    flex-shrink: 0;
    transition: transform 0.15s ease;
}
.badge-pending {
    background: #f59e0b;
    color: #ffffff;
    box-shadow: 0 1px 3px rgba(245, 158, 11, 0.35);
}
.badge-agent {
    background: #10b981;
    color: #ffffff;
    box-shadow: 0 1px 3px rgba(16, 185, 129, 0.3);
}
.badge-slate {
    background: #64748b;
    color: #ffffff;
}
.badge-3d {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #ffffff;
    font-size: 10px;
    padding: 2px 6px;
    box-shadow: 0 1px 3px rgba(217, 119, 6, 0.35);
}
.badge-count {
    background: #e2e8f0;
    color: #334155;
    border: 1px solid #cbd5e1;
    font-size: 10.5px;
}
.badge-version {
    background: #ccfbf1;
    color: #0f766e;
    border: 1px solid #99f6e4;
    font-size: 10.5px;
    font-weight: 600;
}

/* 5. TAB 1: PENDING QUEUE TOOLBAR */
.ha-filter-toolbar {
    margin-bottom: 18px;
}
.ha-filter-form {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.ha-filter-inputs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    flex: 1;
}
.ha-search-box {
    position: relative;
    flex: 1;
    min-width: 220px;
    max-width: 320px;
}
.ha-status-select {
    width: auto;
    min-width: 175px;
}
.ha-filter-btn-group {
    display: inline-flex;
    gap: 6px;
}
.ha-batch-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* 6. TAB 2: AGENT FLEET TOOLBAR */
.ha-fleet-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.ha-fleet-indicators {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}
.ha-indicator-pill {
    padding: 5px 12px;
    border-radius: 99px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.ha-fleet-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 18px;
}
.ha-fleet-toolbar-left {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    flex: 1;
}
.ha-fleet-search-box {
    position: relative;
    flex: 1;
    min-width: 220px;
    max-width: 320px;
}
.ha-fleet-filter-group {
    flex-shrink: 0;
}
.ha-fleet-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

/* 7. SECTION HEADERS FOR TAB 3, 4, 5 */
.ha-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.ha-section-title {
    font-size: 16.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.ha-section-desc {
    font-size: 12.5px;
    color: #64748b;
    margin: 3px 0 0 0;
}
.ha-section-search {
    flex-shrink: 0;
}
.ha-section-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* 8. TAB 3: DEPARTMENTS GRID */
.ha-dept-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

/* 9. TAB 4: REPORTS & CHARTS */
.ha-report-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.ha-report-summary-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    box-shadow: var(--shadow-sm);
}
.ha-charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}
.ha-chart-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* 10. TAB 5: SETUP WIZARD & DOCUMENTATION */
.ha-wizard-card {
    background: #ffffff;
    border: 2px solid #22c55e;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 10px 30px -4px rgba(34, 197, 94, 0.15);
    position: relative;
}
.ha-official-badge {
    position: absolute;
    right: 0;
    top: 0;
    background: #16a34a;
    color: #ffffff;
    font-size: 11.5px;
    font-weight: 800;
    padding: 5px 18px;
    border-radius: 0 0 0 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.ha-wizard-inner {
    display: flex;
    gap: 18px;
    align-items: flex-start;
    flex-wrap: wrap;
}
.ha-wizard-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: #ecfdf5;
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
}
.ha-wizard-body {
    flex: 1;
    min-width: 260px;
}
.ha-wizard-download-row {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 22px;
}
.ha-wizard-btn-download {
    font-weight: 800;
    font-size: 14px;
    padding: 12px 24px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.35);
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    border: none;
}
.ha-wizard-silent-cmd {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 12px;
    color: #475569;
    word-break: break-all;
}
.ha-wizard-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}
.ha-wizard-step-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px;
}

/* ========================================================================= */
/* RESPONSIVE MEDIA QUERIES                                                  */
/* ========================================================================= */

/* LAPTOP / SMALL DESKTOP (992px to 1199px) */
@media (max-width: 1199px) {
    .ha-stat-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* TABLET (768px to 991px) */
@media (max-width: 991px) {
    .ha-tabs-bar {
        padding: 6px 8px;
        gap: 6px;
    }
    .tab-nav-btn {
        flex: 0 0 auto !important;
        padding: 8px 14px !important;
        font-size: 13px !important;
        min-height: 40px !important;
    }
    .ha-stat-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .ha-stat-card:last-child {
        grid-column: span 2;
    }
    .custom-modal-dialog {
        width: 95% !important;
        max-width: 95% !important;
    }
}

/* MOBILE (<768px) - COMPLETE PHONE ADAPTATION */
@media (max-width: 768px) {
    .content-container {
        padding: 10px 10px 24px 10px !important;
    }

    /* Topbar */
    .ha-topbar-actions {
        gap: 6px;
    }
    .ha-topbar-actions .topbar-btn {
        padding: 6px 10px !important;
        font-size: 12px !important;
    }

    /* Hero Banner Mobile */
    .ha-hero-banner {
        border-radius: 16px;
        margin-bottom: 16px;
    }
    .ha-hero-content {
        padding: 16px 14px;
    }
    .ha-hero-inner {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }
    .ha-hero-title-area {
        width: 100%;
        gap: 12px;
    }
    .ha-hero-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
        border-radius: 12px;
    }
    .ha-hero-title {
        font-size: 17px;
    }
    .ha-hero-desc {
        font-size: 12px;
        margin-top: 3px;
    }
    .ha-hero-actions {
        width: 100%;
        flex-direction: column;
        gap: 8px;
    }
    .ha-hero-btn-download {
        width: 100%;
        justify-content: center;
        padding: 10px 14px !important;
        font-size: 13px !important;
    }
    .ha-hero-fy-box {
        width: 100%;
        justify-content: space-between;
    }
    .ha-hero-fy-form {
        flex: 1;
        justify-content: flex-end;
    }

    /* KPI Stat Grid Mobile */
    .ha-stat-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-bottom: 16px;
    }
    .ha-stat-card {
        padding: 12px 14px;
        border-radius: 12px;
    }
    .ha-stat-label {
        font-size: 11px;
    }
    .ha-stat-icon {
        width: 32px;
        height: 32px;
        font-size: 16px;
    }
    .ha-stat-value {
        font-size: 21px;
    }
    .ha-stat-subtext {
        font-size: 10.5px;
        margin-top: 4px;
    }

    /* Tabs Bar Mobile */
    .ha-tabs-bar {
        padding: 6px;
        gap: 5px;
    }
    .tab-nav-btn {
        flex: 0 0 auto !important;
        padding: 7px 11px !important;
        font-size: 12px !important;
        min-height: 38px !important;
        gap: 5px !important;
        border-radius: 8px !important;
    }
    .tab-nav-btn i {
        font-size: 13.5px !important;
    }
    .ha-tab-badge {
        font-size: 10px !important;
        padding: 1px 5px !important;
    }

    /* Tab Panes */
    .ha-tab-pane {
        padding: 14px 10px !important;
    }

    /* Tab 1 Toolbar Mobile */
    .ha-filter-inputs {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    .ha-search-box {
        max-width: 100%;
        min-width: 100%;
        width: 100%;
    }
    .ha-status-select {
        width: 100%;
        min-width: 100%;
    }
    .ha-filter-btn-group {
        width: 100%;
    }
    .ha-filter-btn-group > button,
    .ha-filter-btn-group > a {
        flex: 1;
        justify-content: center;
    }
    .ha-batch-actions {
        width: 100%;
        margin-top: 8px;
    }
    .ha-batch-actions > button {
        flex: 1;
        justify-content: center;
    }

    /* Tab 2 Fleet Toolbar Mobile */
    .ha-fleet-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .ha-fleet-indicators {
        width: 100%;
    }
    .ha-indicator-pill {
        flex: 1;
        justify-content: center;
        font-size: 11px;
        padding: 4px 8px;
    }
    .ha-fleet-toolbar-left {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }
    .ha-fleet-search-box {
        max-width: 100%;
        min-width: 100%;
        width: 100%;
    }
    .ha-fleet-filter-group {
        display: flex;
        width: 100%;
        overflow-x: auto;
    }
    .ha-fleet-filter-group > button {
        flex: 1;
        white-space: nowrap;
        font-size: 11.5px;
        padding: 6px 8px;
    }
    .ha-fleet-actions {
        width: 100%;
    }
    .ha-fleet-actions > button,
    .ha-fleet-actions > a {
        flex: 1;
        justify-content: center;
        font-size: 12px;
    }

    /* ===================================================================== */
    /* TABLE-TO-CARD MOBILE TRANSFORMATION                                    */
    /* Converts wide tables into readable, touch-friendly cards on mobile      */
    /* ===================================================================== */
    .ha-responsive-table-wrapper {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .ha-responsive-table {
        display: block !important;
        width: 100% !important;
        background: transparent !important;
        border: none !important;
    }
    .ha-responsive-table thead {
        display: none !important; /* Hide rigid table headers */
    }
    .ha-responsive-table tbody {
        display: block !important;
        width: 100% !important;
    }
    .ha-responsive-table tbody tr {
        display: block !important;
        background: #ffffff !important;
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 14px !important;
        padding: 14px 14px !important;
        margin-bottom: 12px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
        position: relative !important;
    }
    .ha-responsive-table tbody tr:hover {
        border-color: #cbd5e1 !important;
    }
    .ha-responsive-table tbody td {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-start !important;
        padding: 6px 0 !important;
        border: none !important;
        border-bottom: 1px dashed #f1f5f9 !important;
        font-size: 12.5px !important;
        text-align: right !important;
        gap: 10px !important;
        min-height: auto !important;
    }
    .ha-responsive-table tbody td::before {
        font-weight: 700 !important;
        color: #64748b !important;
        font-size: 11.5px !important;
        text-align: left !important;
        flex-shrink: 0 !important;
        margin-top: 2px !important;
    }

    /* Tab 1 (Pending) Column Labels */
    .ha-pending-table tbody td.ha-col-select {
        justify-content: flex-start !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding-bottom: 8px !important;
        margin-bottom: 4px !important;
    }
    .ha-pending-table tbody td.ha-col-select::before {
        content: "เลือกรายการนี้" !important;
        margin-left: 8px !important;
        order: 2 !important;
    }
    .ha-pending-table tbody td.ha-col-telemetry::before { content: "สถานะรับข้อมูล:"; }
    .ha-pending-table tbody td.ha-col-hostname::before { content: "ชื่อเครื่อง / IP:"; }
    .ha-pending-table tbody td.ha-col-model::before { content: "ยี่ห้อ / รุ่น:"; }
    .ha-pending-table tbody td.ha-col-hwid::before { content: "Hardware ID:"; }
    .ha-pending-table tbody td.ha-col-asset::before { content: "ครุภัณฑ์ & แผนก:"; }
    .ha-pending-table tbody td.ha-col-specs::before { content: "สเปคที่อ่านได้:"; }
    .ha-pending-table tbody td.ha-col-diff::before { content: "ผลเทียบสเปค:"; }
    .ha-pending-table tbody td.ha-col-status::before { content: "สถานะตรวจนับ:"; }
    .ha-pending-table tbody td.ha-col-actions {
        border-bottom: none !important;
        padding-top: 10px !important;
        margin-top: 4px !important;
        justify-content: flex-end !important;
        width: 100% !important;
    }
    .ha-pending-table tbody td.ha-col-actions::before {
        display: none !important;
    }
    .ha-pending-table tbody td.ha-col-actions > div {
        width: 100% !important;
        justify-content: flex-end !important;
    }

    /* Tab 2 (Fleet) Column Labels */
    .ha-fleet-table tbody td.ha-col-select {
        justify-content: flex-start !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding-bottom: 8px !important;
        margin-bottom: 4px !important;
    }
    .ha-fleet-table tbody td.ha-col-select::before {
        content: "เลือกเครื่องนี้" !important;
        margin-left: 8px !important;
        order: 2 !important;
    }
    .ha-fleet-table tbody td.ha-col-status::before { content: "สถานะการเชื่อมต่อ:"; }
    .ha-fleet-table tbody td.ha-col-hostname::before { content: "ชื่อเครื่อง & เครือข่าย:"; }
    .ha-fleet-table tbody td.ha-col-asset::before { content: "ครุภัณฑ์ & แผนก:"; }
    .ha-fleet-table tbody td.ha-col-specs::before { content: "สเปคปัจจุบัน:"; }
    .ha-fleet-table tbody td.ha-col-actions {
        border-bottom: none !important;
        padding-top: 10px !important;
        margin-top: 4px !important;
        justify-content: flex-end !important;
        width: 100% !important;
    }
    .ha-fleet-table tbody td.ha-col-actions::before {
        display: none !important;
    }
    .ha-fleet-table tbody td.ha-col-actions > div {
        width: 100% !important;
        justify-content: flex-end !important;
    }

    /* Tab 3 Department Grid Mobile */
    .ha-dept-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    /* Tab 4 Reports Mobile */
    .ha-report-summary-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    .ha-charts-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    /* Tab 5 Deployment Hub Mobile */
    .ha-wizard-card {
        padding: 16px;
        border-radius: 14px;
    }
    .ha-official-badge {
        position: static;
        display: inline-block;
        margin-bottom: 12px;
        border-radius: 8px;
        padding: 4px 10px;
        font-size: 11px;
    }
    .ha-wizard-inner {
        gap: 12px;
    }
    .ha-wizard-icon {
        width: 44px;
        height: 44px;
        font-size: 22px;
        border-radius: 12px;
    }
    .ha-wizard-body {
        min-width: 100%;
    }
    .ha-wizard-btn-download {
        width: 100%;
        justify-content: center;
        padding: 12px 16px !important;
        font-size: 13.5px !important;
    }
    .ha-wizard-silent-cmd {
        width: 100%;
        text-align: center;
    }
    .ha-wizard-steps {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    /* Custom Modals Mobile */
    .custom-modal-backdrop {
        padding: 8px !important;
    }
    .custom-modal-dialog {
        border-radius: 14px !important;
        max-height: calc(100dvh - 16px) !important;
        max-height: calc(100vh - 16px) !important;
        width: 100% !important;
        margin: 6px auto !important;
    }
    #agentInspectionModalContent,
    #auditDiffModalBody,
    #linkAssetResultsContainer {
        padding: 12px !important;
        -webkit-overflow-scrolling: touch;
    }
}
</style>

<script>
    // -------------------------------------------------------------
    // Remote Hardware Scan Engine (Scan All & Single Machine)
    // -------------------------------------------------------------
    let currentScanBatchId = null;
    let scanAllPollingInterval = null;

    function triggerScanAllConfirm() {
        const agentCount = {{ $agentInstalledCount ?? 0 }};
        Swal.fire({
            title: 'ดึงข้อมูลสเปคจากทุกเครื่องที่ติดตั้ง Agent?',
            html: `ระบบจะส่งสัญญาณคำสั่ง On-Demand ไปยังคอมพิวเตอร์ที่ติดตั้ง Agent ทั้งหมด <b>${agentCount}</b> เครื่อง เพื่อดึงข้อมูลสเปคฮาร์ดแวร์ล่าสุดกลับมาแสดงผลทันที`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="bi bi-cloud-arrow-down-fill me-1"></i> เริ่มดึงข้อมูลเดี๋ยวนี้',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                executeScanAll();
            }
        });
    }

    function executeScanAll() {
        const modal = document.getElementById('remoteScanAllModal');
        modal.style.display = 'flex';
        
        document.getElementById('scanStatusTitle').textContent = 'กำลังส่งคำสั่งดึงข้อมูลไปยังเครื่องลูกข่าย...';
        document.getElementById('scanBatchBadge').textContent = 'กำลังเริ่มต้น...';
        document.getElementById('scanProgressBar').style.width = '5%';
        document.getElementById('scanProgressBar').className = 'progress-bar progress-bar-striped progress-bar-animated bg-info';
        document.getElementById('scanStatTotal').textContent = '...';
        document.getElementById('scanStatCompleted').textContent = '0';
        document.getElementById('scanStatPending').textContent = '...';
        document.getElementById('scanTargetsTableBody').innerHTML = `
            <tr>
                <td colspan="4" style="text-align: center; padding: 25px; color: #64748b;">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    กำลังกระจายคำสั่งดึงข้อมูลไปยังคิวระบบ...
                </td>
            </tr>
        `;

        fetch(`{{ route('hardware-audits.trigger-scan-all') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                fiscal_year: {{ $fiscalYear }}
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถส่งคำสั่งดึงข้อมูลได้', 'error');
                closeRemoteScanAllModal();
                return;
            }

            currentScanBatchId = data.batch_id;
            document.getElementById('scanBatchBadge').textContent = `Batch: ${data.batch_id}`;
            document.getElementById('scanStatTotal').textContent = data.total_targets;
            document.getElementById('scanStatPending').textContent = data.total_targets;

            renderScanTargetsTable(data.targets || []);

            // Start live polling every 2.5 seconds
            if (scanAllPollingInterval) clearInterval(scanAllPollingInterval);
            scanAllPollingInterval = setInterval(pollScanAllStatus, 2500);

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 3500
            });
        })
        .catch(err => {
            Swal.fire('เกิดข้อผิดพลาด', err.message, 'error');
            closeRemoteScanAllModal();
        });
    }

    function renderScanTargetsTable(items) {
        const tbody = document.getElementById('scanTargetsTableBody');
        if (!items || items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; padding: 20px; color:#94a3b8;">ไม่พบรายการเครื่องเป้าหมาย</td></tr>`;
            return;
        }

        let html = '';
        items.forEach(item => {
            let statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>รอเครื่องรับคำสั่ง</span>';
            if (item.status === 'processing') {
                statusBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span>กำลังดึงข้อมูล...</span>';
            } else if (item.status === 'completed') {
                statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="bi bi-shield-check me-1"></i>ได้รับข้อมูลแล้ว</span>';
            } else if (item.status === 'failed') {
                statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="bi bi-x-circle me-1"></i>ล้มเหลว</span>';
            }

            let telHtml = '';
            if (item.telemetry) {
                telHtml = `
                    <div style="font-size: 12px; color: #0f172a;">
                        <div style="font-weight: 700; color: #0f766e;"><i class="bi bi-cpu text-primary me-1"></i>${item.telemetry.cpu || '-'}</div>
                        <div style="color: #0284c7; margin-top: 2px; font-size: 11px;">
                            <span>RAM ${item.telemetry.ram || '-'}</span> | 
                            <span>${item.telemetry.storage || '-'}</span>
                            ${item.telemetry.os ? ` | <span>${item.telemetry.os}</span>` : ''}
                        </div>
                    </div>
                `;
            } else if (item.brand_model || item.ip) {
                telHtml = `
                    <div style="color: #64748b; font-size: 11.5px;">
                        <div>${item.brand_model || '-'}</div>
                        ${item.ip ? `<div style="font-size: 11px; color: #0284c7;">IP: <code>${item.ip}</code></div>` : ''}
                    </div>
                `;
            } else {
                telHtml = `<span style="color: #94a3b8; font-style: italic; font-size: 11.5px;">(รอเครื่องตอบรับและส่งข้อมูล)</span>`;
            }

            html += `
                <tr>
                    <td style="padding: 10px 14px;">
                        <strong style="color: #0f172a; font-family: monospace;">${item.hostname || '-'}</strong>
                        ${item.hardware_id ? `<div style="font-size: 10.5px; color: #a21caf; font-family: monospace;">HWID: ${item.hardware_id.substring(0, 13)}...</div>` : ''}
                        ${item.ip ? `<div style="font-size: 11px; color: #64748b; margin-top: 1px;">IP: <code>${item.ip}</code></div>` : ''}
                    </td>
                    <td style="padding: 10px 14px; text-align: center;">
                        ${statusBadge}
                    </td>
                    <td style="padding: 10px 14px;">
                        ${telHtml}
                    </td>
                    <td style="padding: 10px 14px; text-align: right; color: #64748b; font-size: 11px;">
                        <div>${item.executed_at || '-'}</div>
                        ${item.telemetry?.audit_id ? `<button type="button" class="btn btn-sm btn-outline-primary mt-1" style="font-size: 10.5px; padding: 2px 8px; border-radius: 6px;" onclick="openAgentInspectionModal(${item.telemetry.audit_id})"><i class="bi bi-eye me-1"></i>ดูข้อมูล</button>` : ''}
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function pollScanAllStatus() {
        if (!currentScanBatchId) return;

        fetch(`{{ route('hardware-audits.command-status') }}?batch_id=${currentScanBatchId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;

            document.getElementById('scanStatTotal').textContent = data.total;
            document.getElementById('scanStatCompleted').textContent = data.completed;
            document.getElementById('scanStatPending').textContent = (data.pending + data.processing);

            const pct = data.percentage || 0;
            const pBar = document.getElementById('scanProgressBar');
            pBar.style.width = `${pct}%`;

            if (pct >= 100) {
                pBar.className = 'progress-bar bg-success';
                document.getElementById('scanStatusTitle').textContent = `ดึงข้อมูลครบทุกเครื่องแล้ว 100% (${data.completed}/${data.total} เครื่อง)`;
                document.getElementById('scanStatusPulse').style.background = '#10b981';
                document.getElementById('scanStatusPulse').style.animation = 'none';
                if (scanAllPollingInterval) {
                    clearInterval(scanAllPollingInterval);
                    scanAllPollingInterval = null;
                }
            } else {
                document.getElementById('scanStatusTitle').textContent = `กำลังดึงข้อมูล... ได้รับแล้ว ${data.completed}/${data.total} เครื่อง (${pct}%)`;
            }

            if (data.items) {
                renderScanTargetsTable(data.items);
            }
        })
        .catch(() => {});
    }

    function closeRemoteScanAllModal() {
        if (scanAllPollingInterval) {
            clearInterval(scanAllPollingInterval);
            scanAllPollingInterval = null;
        }
        document.getElementById('remoteScanAllModal').style.display = 'none';
    }

    function finishAndReloadScan() {
        closeRemoteScanAllModal();
        window.location.reload();
    }

    function triggerSingleScan(auditId, hostname) {
        const btn = document.getElementById(`btn-scan-${auditId}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" style="width:11px;height:11px;"></span> <span>ส่งคำสั่ง...</span>`;
        }

        const badgeEl = document.getElementById(`telemetry-badge-${auditId}`);
        if (badgeEl) {
            badgeEl.innerHTML = `<span class="badge" style="background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;"><span class="spinner-border spinner-border-sm" style="width: 10px; height: 10px;"></span> กำลังดึงข้อมูล...</span>`;
        }

        fetch(`{{ url('/hardware-audits') }}/${auditId}/trigger-scan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire('เกิดข้อผิดพลาด', data.message || 'ไม่สามารถส่งคำสั่งดึงข้อมูลได้', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<i class="bi bi-cloud-arrow-down-fill"></i> <span>ดึงข้อมูล</span>`;
                }
                return;
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `ส่งคำสั่งดึงข้อมูลไปยังเครื่อง ${hostname} แล้ว!`,
                text: 'ระบบทำงานแบบ On-Demand: เครื่องกำลังเริ่มสแกนและส่งผล...',
                showConfirmButton: false,
                timer: 4000
            });

            if (btn) {
                btn.className = 'btn btn-sm btn-info text-white';
                btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" style="width:11px;height:11px;"></span> <span>รอเครื่องส่งผล...</span>`;
            }

            // Poll for single command completion
            let singlePollTimer = null;
            let pollAttempts = 0;
            const cmdId = data.command_id;

            singlePollTimer = setInterval(() => {
                pollAttempts++;
                if (pollAttempts > 35) {
                    clearInterval(singlePollTimer);
                    if (btn) {
                        btn.disabled = false;
                        btn.className = 'btn btn-sm btn-outline-primary';
                        btn.innerHTML = `<i class="bi bi-cloud-arrow-down-fill"></i> <span>ดึงข้อมูลอีกครั้ง</span>`;
                    }
                    return;
                }

                fetch(`{{ route('hardware-audits.command-status') }}?command_id=${cmdId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(pollData => {
                    if (pollData.success && pollData.command && pollData.command.status === 'completed') {
                        clearInterval(singlePollTimer);
                        if (btn) {
                            btn.disabled = false;
                            btn.className = 'btn btn-sm btn-success text-white';
                            btn.innerHTML = `<i class="bi bi-shield-check"></i> <span>ได้รับข้อมูลแล้ว</span>`;
                        }

                        if (badgeEl) {
                            badgeEl.innerHTML = `<span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 11px; font-weight: 700; padding: 3px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;"><i class="bi bi-shield-check text-success"></i> ได้รับข้อมูลแล้ว</span>`;
                        }
                        const timeEl = document.getElementById(`telemetry-time-${auditId}`);
                        if (timeEl) {
                            timeEl.innerHTML = `<i class="bi bi-clock me-1 text-success"></i>เพิ่งได้รับเมื่อสักครู่`;
                        }

                        const tel = pollData.command.telemetry;
                        Swal.fire({
                            icon: 'success',
                            title: `ได้รับข้อมูลจริงจากเครื่อง ${hostname} แล้ว!`,
                            html: `
                                <div style="text-align: left; font-size: 13px; line-height: 1.7; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin-top: 10px;">
                                    <div style="color: #059669; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-check-circle-fill"></i> ได้รับข้อมูลจริงจากเครื่องปลายทางสำเร็จ
                                    </div>
                                    <div><strong>IP เครื่อง:</strong> <code>${tel?.ip || pollData.command.ip || '-'}</code></div>
                                    <div><strong>เวลาที่ได้รับ:</strong> ${tel?.received_at || pollData.command.executed_at || 'เมื่อสักครู่'}</div>
                                    <div><strong>ซีพียู (CPU):</strong> ${tel?.cpu || '-'}</div>
                                    <div><strong>หน่วยความจำ (RAM):</strong> ${tel?.ram || '-'}</div>
                                    <div><strong>พื้นที่เก็บข้อมูล:</strong> ${tel?.storage || '-'}</div>
                                    <div><strong>ระบบปฏิบัติการ:</strong> ${tel?.os || '-'}</div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 8px; border-top: 1px dashed #cbd5e1; padding-top: 6px;">
                                        <i class="bi bi-info-circle me-1"></i>ระบบ On-Demand: Agent ส่งข้อมูลกลับมาเฉพาะเมื่อแอดมินกดดึงข้อมูลเท่านั้น
                                    </div>
                                </div>
                            `,
                            confirmButtonText: 'ปิดหน้าต่าง',
                            confirmButtonColor: '#0f766e',
                        });
                    }
                })
                .catch(() => {});
            }, 2500);

        })
        .catch(err => {
            Swal.fire('เกิดข้อผิดพลาด', err.message, 'error');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i class="bi bi-cloud-arrow-down-fill"></i> <span>ดึงข้อมูล</span>`;
            }
        });
    }

    // -------------------------------------------------------------
    // Toggle Select All Checkbox for Batch Approvals
    // -------------------------------------------------------------
    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.audit-select-chk');
        checkboxes.forEach(cb => cb.checked = master.checked);
    }

    // -------------------------------------------------------------
    // Batch Approval Handler
    // -------------------------------------------------------------
    function submitBatchApproval() {
        const checked = document.querySelectorAll('.audit-select-chk:checked');
        if (checked.length === 0) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกรายการ',
                    text: 'กรุณาเลือกรายการเครื่องที่ต้องการอนุมัติอย่างน้อย 1 เครื่อง',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#0f766e'
                });
            } else {
                alert('กรุณาเลือกรายการเครื่องที่ต้องการอนุมัติอย่างน้อย 1 เครื่อง');
            }
            return;
        }

        if (window.Swal) {
            Swal.fire({
                title: `ยืนยันอนุมัติ ${checked.length} เครื่อง?`,
                text: 'ระบบจะทำการอัปเดตสเปคใหม่ล่าสุดลงในฐานข้อมูลครุภัณฑ์ทันที',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `ยืนยันอนุมัติ (${checked.length} รายการ)`,
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('batchApproveForm').submit();
                }
            });
        } else {
            if (confirm(`ยืนยันอนุมัติสเปคที่เลือก ${checked.length} รายการ?`)) {
                document.getElementById('batchApproveForm').submit();
            }
        }
    }

    // -------------------------------------------------------------
    // Single Audit Approval
    // -------------------------------------------------------------
    function approveSingleAudit(id, hostname) {
        if (window.Swal) {
            Swal.fire({
                title: 'ยืนยันอนุมัติสเปค?',
                text: `ต้องการอัปเดตสเปคเครื่อง "${hostname}" ลงในระบบครุภัณฑ์คอมพิวเตอร์ใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'อนุมัติสเปค',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeApproveForm(id);
                }
            });
        } else {
            if (confirm(`ยืนยันอนุมัติสเปคเครื่อง "${hostname}"?`)) {
                executeApproveForm(id);
            }
        }
    }

    function executeApproveForm(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/hardware-audits') }}/${id}/approve`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }

    // -------------------------------------------------------------
    // Single Audit Rejection
    // -------------------------------------------------------------
    function rejectSingleAudit(id, hostname) {
        if (window.Swal) {
            Swal.fire({
                title: 'ปฏิเสธผลการสแกนสเปค?',
                text: `ระบุเหตุผลที่ปฏิเสธผลตรวจสเปคของเครื่อง "${hostname}":`,
                input: 'text',
                inputPlaceholder: 'เช่น สเปคไม่ตรงกับตัวเครื่องจริง / ส่งซ้ำ',
                showCancelButton: true,
                confirmButtonText: 'ยืนยันปฏิเสธ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                inputValidator: (value) => {
                    if (!value) {
                        return 'กรุณาระบุเหตุผลการปฏิเสธ';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    executeRejectForm(id, result.value);
                }
            });
        } else {
            const reason = prompt(`ระบุเหตุผลที่ปฏิเสธผลตรวจสเปคเครื่อง "${hostname}":`);
            if (reason) {
                executeRejectForm(id, reason);
            }
        }
    }

    function executeRejectForm(id, reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/hardware-audits') }}/${id}/reject`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'notes';
        reasonInput.value = reason;
        form.appendChild(reasonInput);

        document.body.appendChild(form);
        form.submit();
    }

    // -------------------------------------------------------------
    // Single Audit Deletion (With Confirmation)
    // -------------------------------------------------------------
    function deleteSingleAudit(id, hostname) {
        if (window.Swal) {
            Swal.fire({
                title: 'ยืนยันลบข้อมูลผลตรวจสเปคนี้?',
                html: `คุณแน่ใจหรือไม่ว่าต้องการลบ<b>ข้อมูลผลการตรวจนับสเปกที่ได้รับ</b>ของเครื่อง <b>"${hostname}"</b>?<br><br><div style="text-align: left; background: #fef2f2; border: 1px solid #fee2e2; padding: 10px 14px; border-radius: 8px; font-size: 12px; color: #991b1b;"><i class="bi bi-info-circle-fill me-1"></i> <b>ผลของการลบ:</b> จะลบเฉพาะบันทึกผลสแกนสเปกในคิวรอบนี้เท่านั้น <u>จะไม่มีการลบหรือแตะต้องข้อมูลครุภัณฑ์ในคลัง</u> แต่อย่างใด</div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash me-1"></i> ยืนยันลบเฉพาะข้อมูลสเปกนี้',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    executeDeleteForm(id);
                }
            });
        } else {
            if (confirm(`ยืนยันลบข้อมูลผลตรวจสเปคของเครื่อง "${hostname}"? (ลบเฉพาะข้อมูลสเปกที่ได้รับ ไม่กระทบครุภัณฑ์ในคลัง)`)) {
                executeDeleteForm(id);
            }
        }
    }

    function executeDeleteForm(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('/hardware-audits') }}/${id}`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }

    // -------------------------------------------------------------
    // Batch Audit Deletion (With Confirmation)
    // -------------------------------------------------------------
    function submitBatchDelete() {
        const checked = document.querySelectorAll('.audit-select-chk:checked');
        if (checked.length === 0) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกรายการ',
                    text: 'กรุณาเลือกรายการผลตรวจนับที่ต้องการลบอย่างน้อย 1 รายการ',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#0f766e'
                });
            } else {
                alert('กรุณาเลือกรายการผลตรวจนับที่ต้องการลบอย่างน้อย 1 รายการ');
            }
            return;
        }

        if (window.Swal) {
            Swal.fire({
                title: `ยืนยันลบข้อมูลตรวจนับ ${checked.length} รายการ?`,
                html: `คุณแน่ใจหรือไม่ว่าต้องการลบ<b>เฉพาะข้อมูลผลการตรวจสเปก</b>ที่เลือกทั้งหมด <b>${checked.length} รายการ</b> ออกจากระบบ?<br><br><div style="text-align: left; background: #fef2f2; border: 1px solid #fee2e2; padding: 10px 14px; border-radius: 8px; font-size: 12px; color: #991b1b;"><i class="bi bi-info-circle-fill me-1"></i> <b>ผลของการลบ:</b> จะลบเฉพาะบันทึกผลสแกนสเปกในคิวรอบนี้เท่านั้น <u>ไม่กระทบต่อทะเบียนครุภัณฑ์และประวัติทรัพย์สินในระบบ</u></div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `<i class="bi bi-trash me-1"></i> ยืนยันลบ (${checked.length} รายการ)`,
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('batchApproveForm');
                    form.action = "{{ route('hardware-audits.batch-delete') }}";
                    form.submit();
                }
            });
        } else {
            if (confirm(`ยืนยันลบข้อมูลผลตรวจสเปคที่เลือก ${checked.length} รายการ ออกจากระบบ? (ไม่สามารถกู้คืนได้)`)) {
                const form = document.getElementById('batchApproveForm');
                form.action = "{{ route('hardware-audits.batch-delete') }}";
                form.submit();
            }
        }
    }

    // -------------------------------------------------------------
    // Agent Telemetry Inspector Modal
    // -------------------------------------------------------------
    function openAgentInspectionModal(id) {
        const modal = document.getElementById('agentInspectionModal');
        const content = document.getElementById('agentInspectionModalContent');
        const footerLeft = document.getElementById('modalInspectionFooterLeft');
        const footerRight = document.getElementById('modalInspectionFooterRight');
        
        modal.style.display = 'flex';
        content.innerHTML = `
            <div style="text-align: center; padding: 48px 20px;">
                <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem; border-width: 3px;"></div>
                <div style="margin-top: 14px; font-weight: 700; color: #0f172a; font-size: 15px;">กำลังเชื่อมต่อและดึงข้อมูลสเปคจริงจากเครื่องลูกข่าย...</div>
                <div style="font-size: 12.5px; color: #64748b; margin-top: 4px;">กำลังวิเคราะห์ผล Telemetry และตรวจสอบตามเกณฑ์กระทรวงดีอี (MDES)</div>
            </div>
        `;
        footerLeft.textContent = '';
        footerRight.innerHTML = `<button type="button" class="btn btn-secondary btn-sm" onclick="closeAgentInspectionModal()">ปิดหน้าต่าง</button>`;

        fetch(`{{ url('/hardware-audits') }}/${id}/inspect`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                content.innerHTML = `<div class="alert alert-danger">ไม่สามารถโหลดข้อมูลได้</div>`;
                return;
            }
            renderAgentInspectionModal(data);
        })
        .catch(err => {
            content.innerHTML = `<div class="alert alert-danger">เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ${err.message}</div>`;
        });
    }

    function renderAgentInspectionModal(data) {
        const audit = data.audit;
        const asset = data.asset;
        const mdesIssues = data.mdes_issues || [];
        const isStandard = data.is_mdes_standard;
        const rescanCmd = data.rescan_command;
        const rawJson = JSON.stringify(audit.raw_payload || audit, null, 2);

        const content = document.getElementById('agentInspectionModalContent');
        const footerLeft = document.getElementById('modalInspectionFooterLeft');
        const footerRight = document.getElementById('modalInspectionFooterRight');
        
        footerLeft.innerHTML = `<i class="bi bi-clock-history me-1"></i>ส่งข้อมูลเมื่อ: <strong>${audit.created_at || '-'}</strong> &bull; Agent v${audit.client_agent_version || '1.2.0'}`;
        
        if (audit.status === 'pending') {
            footerRight.innerHTML = `
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAgentInspectionModal()">ปิดหน้าต่าง</button>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="rejectSingleAudit(${audit.id}, '${audit.hostname}')">ปฏิเสธ</button>
                <button type="button" class="btn btn-success btn-sm" onclick="approveSingleAudit(${audit.id}, '${audit.hostname}')" style="font-weight: 700;">
                    <i class="bi bi-check-lg"></i> อนุมัติสเปคเครื่องนี้
                </button>
            `;
        } else {
            footerRight.innerHTML = `<button type="button" class="btn btn-secondary btn-sm" onclick="closeAgentInspectionModal()">ปิดหน้าต่าง</button>`;
        }

        let html = `
            <!-- Top Computer Identity Banner -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        <strong style="font-size: 17px; color: #0f172a;">${audit.hostname || 'ไม่ระบุชื่อเครื่อง'}</strong>
                        <span class="badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 11px;">
                            ${audit.brand || 'PC'} ${audit.model || ''}
                        </span>
                        <span class="badge bg-light text-dark border" style="font-size: 11px;">Agent v${audit.client_agent_version || '1.2.0'}</span>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 5px;">
                        Serial Number: <strong style="color: #0f172a;">${audit.serial_number || 'ไม่พบใน BIOS'}</strong> &bull; 
                        IP Address: <code>${audit.ip_address || '-'}</code> &bull; 
                        MAC: <code>${audit.mac_address || '-'}</code>
                    </div>
                </div>
                <div style="text-align: right;">
                    ${audit.status === 'approved' 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1" style="font-size: 13px;"><i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว</span>' 
                        : (audit.status === 'rejected' 
                            ? '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1" style="font-size: 13px;"><i class="bi bi-x-circle me-1"></i>ปฏิเสธ</span>' 
                            : '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1" style="font-size: 13px;"><i class="bi bi-clock-history me-1"></i>รออนุมัติ</span>')
                    }
                    <div style="font-size: 11.5px; color: #0f766e; margin-top: 4px; font-weight: 600;">
                        ${asset ? 'ผูกกับครุภัณฑ์: ' + asset.asset_code : 'เครื่องใหม่ (ยังไม่ผูกครุภัณฑ์)'}
                    </div>
                </div>
            </div>

            <!-- Tab Navigation inside Modal -->
            <ul class="nav nav-pills mb-3" style="gap: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <li class="nav-item">
                    <button class="nav-link active btn-sm" id="btn-tab-specs" onclick="switchModalTab('specs')" style="border-radius: 8px; font-weight: 600; font-size: 12.5px; padding: 6px 14px;">
                        <i class="bi bi-cpu me-1"></i> สเปคฮาร์ดแวร์ที่ตรวจพบ
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="btn-tab-compliance" onclick="switchModalTab('compliance')" style="border-radius: 8px; font-weight: 600; font-size: 12.5px; padding: 6px 14px;">
                        <i class="bi bi-shield-check me-1"></i> ตรวจเกณฑ์มาตรฐาน ICT
                        ${!isStandard ? '<span class="badge bg-danger ms-1">!</span>' : '<span class="badge bg-success ms-1">✓</span>'}
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="btn-tab-raw" onclick="switchModalTab('raw')" style="border-radius: 8px; font-weight: 600; font-size: 12.5px; padding: 6px 14px;">
                        <i class="bi bi-code-square me-1"></i> Raw JSON Payload
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link btn-sm" id="btn-tab-cmd" onclick="switchModalTab('cmd')" style="border-radius: 8px; font-weight: 600; font-size: 12.5px; padding: 6px 14px;">
                        <i class="bi bi-terminal me-1"></i> คำสั่งรันสแกนซ้ำ
                    </button>
                </li>
            </ul>

            <!-- TAB PANE 1: SPECS BREAKDOWN -->
            <div id="pane-tab-specs">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; margin-bottom: 16px;">
                    <!-- CPU Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 11px; font-weight: 700; color: #0284c7; text-transform: uppercase;">
                            <i class="bi bi-cpu me-1"></i> หน่วยประมวลผล (CPU)
                        </div>
                        <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-top: 4px;">${audit.cpu_model || '-'}</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">ความเร็ว: <strong>${audit.cpu_speed || '-'}</strong></div>
                    </div>

                    <!-- RAM Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 11px; font-weight: 700; color: #0d9488; text-transform: uppercase;">
                            <i class="bi bi-memory me-1"></i> หน่วยความจำหลัก (RAM)
                        </div>
                        <div style="font-weight: 700; font-size: 15px; color: #0f172a; margin-top: 4px;">
                            ${audit.ram_capacity || '-'} GB <span style="font-size: 12px; font-weight: 600; color: #0d9488;">${audit.ram_type || ''}</span>
                        </div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                            บัส: <strong>${audit.ram_bus || '-'}</strong> &bull; สล็อต: <strong>${audit.ram_slots || '-'}</strong>
                        </div>
                    </div>

                    <!-- Storage Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 11px; font-weight: 700; color: #d97706; text-transform: uppercase;">
                            <i class="bi bi-hdd-fill me-1"></i> ไดรฟ์จัดเก็บข้อมูล (Storage)
                        </div>
                        <div style="font-weight: 700; font-size: 14px; color: #0f172a; margin-top: 4px;">
                            ${audit.storage_capacity || '-'}
                        </div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                            ประเภท: <strong>${audit.storage_type || '-'}</strong>
                        </div>
                        ${audit.storage_second ? `<div style="font-size: 11.5px; color: #0d9488; margin-top: 4px; font-weight: 600;"><i class="bi bi-hdd me-1"></i>ไดรฟ์ที่ 2: ${audit.storage_second}</div>` : ''}
                    </div>

                    <!-- OS Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 11px; font-weight: 700; color: #6366f1; text-transform: uppercase;">
                            <i class="bi bi-windows me-1"></i> ระบบปฏิบัติการ (OS)
                        </div>
                        <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-top: 4px;">${audit.os_name || '-'}</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">ลิขสิทธิ์: <strong>${audit.os_license || '-'}</strong></div>
                    </div>

                    <!-- GPU Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 11px; font-weight: 700; color: #ec4899; text-transform: uppercase;">
                            <i class="bi bi-gpu-card me-1"></i> กราฟิกการ์ด (GPU)
                        </div>
                        <div style="font-weight: 600; font-size: 13px; color: #0f172a; margin-top: 4px;">${audit.gpu_model || 'Integrated / Onboard'}</div>
                    </div>

                    <!-- Screen Card -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="font-size: 11px; font-weight: 700; color: #14b8a6; text-transform: uppercase;">
                            <i class="bi bi-display me-1"></i> จอแสดงผล (Monitor)
                        </div>
                        <div style="font-weight: 600; font-size: 13px; color: #0f172a; margin-top: 4px;">${audit.monitor_size || '-'}</div>
                    </div>
                </div>
            </div>

            <!-- TAB PANE 2: ICT/MDES COMPLIANCE -->
            <div id="pane-tab-compliance" style="display: none;">
                <div style="padding: 18px; border-radius: 12px; background: ${isStandard ? '#f0fdf4' : '#fffbeb'}; border: 1px solid ${isStandard ? '#bbf7d0' : '#fde68a'}; margin-bottom: 16px;">
                    <div style="font-weight: 700; font-size: 15px; color: ${isStandard ? '#166534' : '#92400e'}; display: flex; align-items: center; gap: 8px;">
                        <i class="bi ${isStandard ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-warning'}" style="font-size: 18px;"></i>
                        <span>${isStandard ? 'เครื่องคอมพิวเตอร์ผ่านเกณฑ์มาตรฐานครุภัณฑ์ ICT ภาครัฐ' : 'พบข้อสังเกตสเปคควรปรับปรุงตามเกณฑ์มาตรฐาน ICT ภาครัฐ'}</span>
                    </div>
                    ${mdesIssues && mdesIssues.length > 0 ? `
                        <div style="font-size: 13px; color: #78350f; margin-top: 8px;">รายการข้อสังเกตที่ไม่สอดคล้องตามเกณฑ์กระทรวงดีอี:</div>
                        <ul style="margin: 6px 0 0 20px; font-size: 13px; color: #78350f;">
                            ${mdesIssues.map(i => `<li style="margin-bottom: 4px;">${i}</li>`).join('')}
                        </ul>
                    ` : `
                        <div style="font-size: 13px; color: #15803d; margin-top: 8px;">
                            ✓ RAM ตั้งแต่ 8GB ขึ้นไป, ติดตั้งไดรฟ์เก็บข้อมูลความเร็วสูงแบบ SSD, และใช้งานระบบปฏิบัติการเวอร์ชันที่ยังอยู่ในระยะสนับสนุนความปลอดภัย
                        </div>
                    `}
                </div>
            </div>

            <!-- TAB PANE 3: RAW JSON -->
            <div id="pane-tab-raw" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12.5px; color: #64748b;">โครงสร้าง JSON ที่ Client PowerShell Agent ส่งมายังเซิร์ฟเวอร์:</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyAgentJson()" style="font-size: 11.5px; padding: 3px 10px;">
                        <i class="bi bi-clipboard me-1"></i> คัดลอก JSON
                    </button>
                </div>
                <pre id="rawJsonBlock" style="background: #0f172a; color: #38bdf8; border-radius: 10px; padding: 16px; font-size: 12px; max-height: 280px; overflow-y: auto; font-family: Consolas, monospace; margin: 0;">${rawJson}</pre>
            </div>

            <!-- TAB PANE 4: RESCAN COMMAND -->
            <div id="pane-tab-cmd" style="display: none;">
                <!-- 1-Click Remote Scan Action Card -->
                <div style="background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); border: 1.5px solid #99f6e4; border-radius: 14px; padding: 18px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="font-weight: 800; font-size: 14.5px; color: #0f766e; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-broadcast-pin" style="font-size: 18px;"></i>
                                <span>สั่งสแกนเครื่องนี้ทันทีจากระบบ (1-Click Remote Scan)</span>
                            </div>
                            <div style="font-size: 12.5px; color: #334155; margin-top: 4px;">
                                ระบบจะส่งคำสั่งไปยัง Agent บนเครื่อง <strong>${audit.hostname}</strong> เพื่อทำการสแกนสเปคฮาร์ดแวร์ล่าสุดและส่งกลับเข้าสู่ระบบอัตโนมัติ
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="triggerSingleScan(${audit.id}, '${audit.hostname}'); closeAgentInspectionModal();" 
                                style="background: linear-gradient(135deg, #0284c7 0%, #0f766e 100%); color: #ffffff; font-weight: 700; padding: 8px 18px; border-radius: 8px; border: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(2,132,199,0.3);">
                            <i class="bi bi-play-circle-fill"></i>
                            <span>ส่งคำสั่งสแกนเดี๋ยวนี้</span>
                        </button>
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 6px;">
                        หรือคัดลอกคำสั่งสำหรับรันด้วยตนเอง (PowerShell One-Liner):
                    </div>
                    <p style="font-size: 12.5px; color: #64748b; margin-bottom: 12px;">
                        หากต้องการสั่งสแกนจากหน้าเครื่องโดยตรง สามารถคัดลอกคำสั่งด้านล่างไปรันใน PowerShell (Run as Administrator):
                    </p>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="rescanCmdInput" class="form-control form-control-sm" value="${rescanCmd}" readonly style="font-family: Consolas, monospace; background: #ffffff;">
                        <button type="button" class="btn btn-sm btn-primary" onclick="copyRescanCommand()" style="white-space: nowrap;">
                            <i class="bi bi-clipboard me-1"></i> คัดลอกคำสั่ง
                        </button>
                    </div>
                </div>
            </div>
        `;

        content.innerHTML = html;
    }

    function switchModalTab(tabName) {
        ['specs', 'compliance', 'raw', 'cmd'].forEach(t => {
            const btn = document.getElementById(`btn-tab-${t}`);
            const pane = document.getElementById(`pane-tab-${t}`);
            if (btn && pane) {
                if (t === tabName) {
                    btn.classList.add('active');
                    pane.style.display = 'block';
                } else {
                    btn.classList.remove('active');
                    pane.style.display = 'none';
                }
            }
        });
    }

    function closeAgentInspectionModal() {
        document.getElementById('agentInspectionModal').style.display = 'none';
    }

    function copyAgentJson() {
        const text = document.getElementById('rawJsonBlock').textContent;
        navigator.clipboard.writeText(text).then(() => {
            if (window.Swal) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'คัดลอก JSON แล้ว', showConfirmButton: false, timer: 1500 });
            } else {
                alert('คัดลอก JSON เรียบร้อยแล้ว');
            }
        });
    }

    function copyRescanCommand() {
        const text = document.getElementById('rescanCmdInput').value;
        navigator.clipboard.writeText(text).then(() => {
            if (window.Swal) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'คัดลอกคำสั่งแล้ว', showConfirmButton: false, timer: 1500 });
            } else {
                alert('คัดลอกคำสั่งเรียบร้อยแล้ว');
            }
        });
    }

    // -------------------------------------------------------------
    // Link Existing Asset Modal Handler
    // -------------------------------------------------------------
    let currentLinkAudit = null;
    let selectedTargetAsset = null;
    let searchAssetsTimer = null;

    function openLinkAssetModal(audit) {
        currentLinkAudit = audit;
        selectedTargetAsset = null;
        const modal = document.getElementById('linkAssetModal');
        
        document.getElementById('linkAssetAuditId').value = audit.id;
        document.getElementById('linkAssetTargetId').value = '';
        document.getElementById('linkAssetHardwareIdBadge').textContent = audit.hardware_id ? ('HWID: ' + audit.hardware_id) : 'HWID: ไม่พบข้อมูล';
        
        // Host Info
        document.getElementById('linkAssetHostInfo').innerHTML = `
            <span style="font-family: monospace;">${audit.hostname || 'ไม่ระบุชื่อเครื่อง'}</span>
            ${audit.brand ? ' &bull; ' + audit.brand : ''} ${audit.model ? audit.model : ''}
            ${audit.serial_number ? ' &bull; S/N: <code>' + audit.serial_number + '</code>' : ''}
            ${audit.ip_address ? ' &bull; IP: <code>' + audit.ip_address + '</code>' : ''}
        `;

        // Specs Summary
        const specs = [
            audit.cpu_model ? ('CPU ' + audit.cpu_model + (audit.cpu_speed ? ' (' + audit.cpu_speed + ')' : '')) : null,
            audit.ram_capacity ? ('RAM ' + audit.ram_capacity + 'GB ' + (audit.ram_type || '') + (audit.ram_bus ? ' (' + audit.ram_bus + ')' : '')) : null,
            audit.storage_capacity ? ((audit.storage_type || 'Storage') + ' ' + audit.storage_capacity) : null,
            audit.os_name ? ('OS ' + audit.os_name) : null,
        ].filter(Boolean).join(' • ');
        document.getElementById('linkAssetSpecsSummary').textContent = specs || 'อ่านสเปคจากเครื่องไม่สมบูรณ์';

        // Reset Search & Preview
        document.getElementById('linkAssetSearchInput').value = '';
        document.getElementById('linkAssetClearSearch').style.display = 'none';
        document.getElementById('linkAssetSelectedCard').style.display = 'none';
        document.getElementById('btnSubmitLinkAsset').disabled = true;

        // Prefill search with serial number or hostname for convenience
        const query = (audit.serial_number && audit.serial_number.length > 3) ? audit.serial_number : (audit.hostname || '');
        if (query) {
            document.getElementById('linkAssetSearchInput').value = query;
            document.getElementById('linkAssetClearSearch').style.display = 'block';
            fetchAssetSearchResults(query);
        } else {
            document.getElementById('linkAssetResultsContainer').innerHTML = `
                <div id="linkAssetResultsEmpty" style="text-align: center; color: #94a3b8; padding: 18px; font-size: 12.5px;">
                    พิมพ์คำค้นหาข้างต้นเพื่อค้นหาครุภัณฑ์ที่ต้องการเชื่อมโยง
                </div>
            `;
        }

        modal.style.display = 'flex';
    }

    function closeLinkAssetModal() {
        document.getElementById('linkAssetModal').style.display = 'none';
    }

    function clearLinkSearch() {
        document.getElementById('linkAssetSearchInput').value = '';
        document.getElementById('linkAssetClearSearch').style.display = 'none';
        document.getElementById('linkAssetResultsContainer').innerHTML = `
            <div id="linkAssetResultsEmpty" style="text-align: center; color: #94a3b8; padding: 18px; font-size: 12.5px;">
                พิมพ์คำค้นหาข้างต้นเพื่อค้นหาครุภัณฑ์ที่ต้องการเชื่อมโยง
            </div>
        `;
    }

    function debounceSearchAssets() {
        clearTimeout(searchAssetsTimer);
        const q = document.getElementById('linkAssetSearchInput').value.trim();
        document.getElementById('linkAssetClearSearch').style.display = q ? 'block' : 'none';
        if (!q) {
            clearLinkSearch();
            return;
        }
        searchAssetsTimer = setTimeout(() => {
            fetchAssetSearchResults(q);
        }, 300);
    }

    function fetchAssetSearchResults(query) {
        const container = document.getElementById('linkAssetResultsContainer');
        container.innerHTML = `
            <div style="text-align: center; padding: 16px; color: #0284c7; font-size: 13px;">
                <div class="spinner-border spinner-border-sm me-1" role="status"></div> กำลังค้นหาครุภัณฑ์ในระบบ...
            </div>
        `;

        fetch(`{{ url('/hardware-audits/search-assets') }}?q=` + encodeURIComponent(query), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.assets || data.assets.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: #94a3b8; padding: 18px; font-size: 12.5px;">
                        <i class="bi bi-exclamation-circle me-1"></i> ไม่พบครุภัณฑ์ที่ตรงกับคำค้นหา "${query}"
                    </div>
                `;
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 6px;">';
            data.assets.forEach(asset => {
                const isSelected = selectedTargetAsset && selectedTargetAsset.id === asset.id;
                html += `
                    <div class="asset-link-card" onclick='selectAssetForLinking(${JSON.stringify(asset).replace(/'/g, "&apos;")})' 
                         style="background: ${isSelected ? '#e0f2fe' : '#ffffff'}; border: 1.5px solid ${isSelected ? '#0284c7' : '#e2e8f0'}; border-radius: 8px; padding: 10px 14px; cursor: pointer; transition: all 0.15s ease; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="color: #0284c7; font-family: monospace; font-size: 13px;">${asset.asset_code}</strong>
                                <span style="font-weight: 600; color: #0f172a; font-size: 13px;">${asset.name}</span>
                                <span class="badge bg-light text-secondary border" style="font-size: 10.5px;">${asset.device_type_name}</span>
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 3px;">
                                <i class="bi bi-geo-alt me-1"></i>${asset.department_name} &bull; 
                                ผู้ครอบครอง: <strong>${asset.custodian_name}</strong> &bull; 
                                S/N: <code>${asset.serial_number || '-'}</code>
                            </div>
                            <div style="font-size: 11px; color: #0f766e; margin-top: 2px;">
                                สเปคเดิม: ${asset.specs}
                            </div>
                        </div>
                        <div>
                            <span class="btn btn-sm ${isSelected ? 'btn-primary' : 'btn-outline-primary'}" style="font-size: 11.5px; padding: 3px 10px; border-radius: 6px; font-weight: 600;">
                                ${isSelected ? '✓ เลือกแล้ว' : 'เลือกเครื่องนี้'}
                            </span>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = `<div class="alert alert-danger p-2 mb-0" style="font-size: 12px;">เกิดข้อผิดพลาด: ${err.message}</div>`;
        });
    }

    function selectAssetForLinking(asset) {
        selectedTargetAsset = asset;
        document.getElementById('linkAssetTargetId').value = asset.id;

        // Update selected card preview
        document.getElementById('selectedAssetBadge').textContent = asset.asset_code;
        document.getElementById('selectedAssetName').textContent = asset.name;
        document.getElementById('selectedAssetDept').textContent = asset.department_name;
        document.getElementById('selectedAssetCustodian').textContent = asset.custodian_name;
        document.getElementById('selectedAssetSpecs').textContent = asset.specs;

        document.getElementById('linkAssetSelectedCard').style.display = 'block';
        document.getElementById('btnSubmitLinkAsset').disabled = false;

        // Refresh result card styles
        const cards = document.querySelectorAll('.asset-link-card');
        cards.forEach(card => {
            if (card.innerHTML.includes(asset.asset_code)) {
                card.style.background = '#e0f2fe';
                card.style.borderColor = '#0284c7';
                const btn = card.querySelector('.btn');
                if (btn) {
                    btn.className = 'btn btn-sm btn-primary';
                    btn.textContent = '✓ เลือกแล้ว';
                }
            } else {
                card.style.background = '#ffffff';
                card.style.borderColor = '#e2e8f0';
                const btn = card.querySelector('.btn');
                if (btn) {
                    btn.className = 'btn btn-sm btn-outline-primary';
                    btn.textContent = 'เลือกเครื่องนี้';
                }
            }
        });
    }

    function submitLinkAsset(e) {
        e.preventDefault();
        if (!selectedTargetAsset) {
            if (window.Swal) Swal.fire({ icon: 'warning', title: 'กรุณาเลือกครุภัณฑ์', text: 'โปรดค้นหาและคลิกเลือกครุภัณฑ์ที่ต้องการเชื่อมโยง' });
            else alert('โปรดเลือกครุภัณฑ์');
            return;
        }

        const form = document.getElementById('formLinkAsset');
        const submitBtn = document.getElementById('btnSubmitLinkAsset');
        const auditId = document.getElementById('linkAssetAuditId').value;
        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึกการเชื่อมโยง...';

        fetch("{{ url('/hardware-audits') }}/" + auditId + "/link-asset", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-link-45deg me-1"></i> ยืนยันเชื่อมโยงและอัปเดตสเปค';

            if (data.success) {
                closeLinkAssetModal();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'เชื่อมโยงครุภัณฑ์สำเร็จ!',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0284c7'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                const err = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'เกิดข้อผิดพลาดในการบันทึก');
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'ไม่สามารถบันทึกได้', text: err });
                } else {
                    alert(err);
                }
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-link-45deg me-1"></i> ยืนยันเชื่อมโยงและอัปเดตสเปค';
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', text: err.message });
            } else {
                alert('เกิดข้อผิดพลาด: ' + err.message);
            }
        });
    }

    function unlinkSingleAudit(auditId, hostname) {
        const confirmMsg = `ต้องการยกเลิกการผูกครุภัณฑ์ของเครื่อง "${hostname}" หรือไม่?\n\n(สถานะจะกลับสู่รอเชื่อมโยง 1:1 โดยแอดมิน เพื่อความถูกต้องแม่นยำ)`;
        if (window.Swal) {
            Swal.fire({
                title: 'ปลดการผูกครุภัณฑ์?',
                text: `ต้องการยกเลิกการผูกครุภัณฑ์ของเครื่อง "${hostname}" หรือไม่? สถานะจะกลับสู่รอเชื่อมโยง 1:1 โดยแอดมิน`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-link-45deg me-1"></i> ยืนยันปลดการผูก',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    executeUnlink(auditId);
                }
            });
        } else {
            if (confirm(confirmMsg)) {
                executeUnlink(auditId);
            }
        }
    }

    function executeUnlink(auditId) {
        fetch("{{ url('/hardware-audits') }}/" + auditId + "/unlink-asset", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ปลดการเชื่อมโยงเรียบร้อย',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    alert(data.message);
                    location.reload();
                }
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการปลดการผูก');
            }
        })
        .catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });
    }

    // -------------------------------------------------------------
    // Register New Asset Modal Handler
    // -------------------------------------------------------------
    let currentNewAssetAudit = null;
    let currentDiffAudit = null;

    function openCreateAssetModal(audit) {
        currentNewAssetAudit = audit;
        const modal = document.getElementById('createAssetModal');
        
        document.getElementById('newAssetAuditId').value = audit.id;
        document.getElementById('newAssetHardwareId').value = audit.hardware_id || '';
        document.getElementById('newAssetHardwareIdBadge').textContent = audit.hardware_id ? ('HWID: ' + audit.hardware_id) : 'HWID: ไม่พบข้อมูล';
        
        // Build Specs Summary text
        const specs = [
            audit.cpu_model ? ('CPU ' + audit.cpu_model + (audit.cpu_speed ? ' (' + audit.cpu_speed + ')' : '')) : null,
            audit.ram_capacity ? ('RAM ' + audit.ram_capacity + 'GB ' + (audit.ram_type || '') + (audit.ram_bus ? ' (' + audit.ram_bus + ')' : '')) : null,
            audit.storage_capacity ? ((audit.storage_type || 'Storage') + ' ' + audit.storage_capacity) : null,
            audit.os_name ? ('OS ' + audit.os_name) : null,
            audit.ip_address ? ('IP ' + audit.ip_address) : null
        ].filter(Boolean).join(' • ');

        document.getElementById('newAssetSpecsSummaryText').textContent = specs || 'อ่านสเปคจากเครื่องไม่สมบูรณ์';

        // Auto-generate suggested asset code
        const fiscalYearShort = String({{ $fiscalYear }}).slice(-2);
        const randomNum = String(Math.floor(1000 + Math.random() * 9000));
        document.getElementById('newAssetCode').value = '7440-001-' + randomNum + '/' + fiscalYearShort;

        const brand = audit.brand || '';
        const model = audit.model || audit.hostname || '';
        document.getElementById('newAssetName').value = 'เครื่องคอมพิวเตอร์ ' + (brand ? brand + ' ' : '') + model;
        document.getElementById('newAssetBrand').value = brand;
        document.getElementById('newAssetModel').value = model;
        document.getElementById('newAssetSerial').value = audit.serial_number || '';
        document.getElementById('newAssetBudgetYear').value = audit.fiscal_year || '{{ $fiscalYear }}';

        // Match DeviceType dropdown
        const devTypeSelect = document.getElementById('newAssetDeviceTypeId');
        if (devTypeSelect) {
            const targetCode = (audit.device_type_code || 'PC').toUpperCase();
            for (let i = 0; i < devTypeSelect.options.length; i++) {
                const opt = devTypeSelect.options[i];
                if ((opt.getAttribute('data-code') || '').toUpperCase() === targetCode) {
                    devTypeSelect.selectedIndex = i;
                    break;
                }
            }
        }

        // Link for full registration form
        const fullFormBtn = document.getElementById('btnOpenFullAssetForm');
        if (fullFormBtn) {
            fullFormBtn.href = "{{ route('assets.create') }}?from_audit=" + audit.id;
        }

        modal.style.display = 'flex';
    }

    function closeCreateAssetModal() {
        document.getElementById('createAssetModal').style.display = 'none';
    }

    function submitCreateAssetFromAudit(e) {
        e.preventDefault();
        const form = document.getElementById('formRegisterAssetFromAudit');
        const submitBtn = document.getElementById('btnSubmitNewAsset');
        const auditId = document.getElementById('newAssetAuditId').value;
        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...';

        fetch("{{ url('/hardware-audits') }}/" + auditId + "/create-asset", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-plus-circle-fill me-1"></i> บันทึกและสร้างครุภัณฑ์ใหม่';

            if (data.success) {
                closeCreateAssetModal();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ลงทะเบียนครุภัณฑ์สำเร็จ!',
                        text: data.message,
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                const err = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'เกิดข้อผิดพลาดในการบันทึก');
                if (window.Swal) {
                    Swal.fire({ icon: 'error', title: 'ไม่สามารถบันทึกได้', text: err });
                } else {
                    alert(err);
                }
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-plus-circle-fill me-1"></i> บันทึกและสร้างครุภัณฑ์ใหม่';
            if (window.Swal) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', text: err.message });
            } else {
                alert('เกิดข้อผิดพลาด: ' + err.message);
            }
        });
    }

    // -------------------------------------------------------------
    // Side-by-Side Spec Diff Modal (เปรียบเทียบสเปคเดิม vs สเปคใหม่)
    // -------------------------------------------------------------
    function openAuditDiffModal(audit, asset) {
        currentDiffAudit = audit;
        const modal = document.getElementById('auditDiffModal');
        const body = document.getElementById('auditDiffModalBody');
        const approveBtn = document.getElementById('modalApproveBtn');

        const fields = [
            { label: 'CPU', key: 'cpu_model', icon: 'bi-cpu' },
            { label: 'ความเร็ว CPU', key: 'cpu_speed', icon: 'bi-speedometer2' },
            { label: 'RAM ขนาด (GB)', key: 'ram_capacity', icon: 'bi-memory' },
            { label: 'RAM ประเภท', key: 'ram_type', icon: 'bi-memory' },
            { label: 'RAM บัส (MHz)', key: 'ram_bus', icon: 'bi-memory' },
            { label: 'Storage ประเภท', key: 'storage_type', icon: 'bi-hdd' },
            { label: 'Storage ขนาด', key: 'storage_capacity', icon: 'bi-hdd' },
            { label: 'ระบบปฏิบัติการ (OS)', key: 'os_name', icon: 'bi-windows' },
            { label: 'ลิขสิทธิ์ OS', key: 'os_license', icon: 'bi-key' },
            { label: 'IP Address', key: 'ip_address', icon: 'bi-hdd-network' },
            { label: 'MAC Address', key: 'mac_address', icon: 'bi-ethernet' }
        ];

        let diffRows = '';
        fields.forEach(f => {
            const oldVal = asset ? (asset[f.key] || '-') : '-';
            const newVal = audit[f.key] || '-';
            const isDifferent = asset && String(oldVal).trim().toLowerCase() !== String(newVal).trim().toLowerCase() && newVal !== '-';

            diffRows += `
                <tr style="${isDifferent ? 'background: #fefce8;' : ''}">
                    <td style="font-weight: 600; color: #475569; width: 160px;">
                        <i class="bi ${f.icon} me-1 text-primary"></i> ${f.label}
                    </td>
                    <td style="width: 260px; color: ${isDifferent ? '#dc2626' : '#334155'};">
                        ${oldVal}
                    </td>
                    <td style="color: ${isDifferent ? '#16a34a; font-weight: 700;' : '#334155'};">
                        ${newVal}
                        ${isDifferent ? '<span class="badge bg-success-subtle text-success ms-1">อัปเดตใหม่</span>' : ''}
                    </td>
                </tr>
            `;
        });

        let newDeviceBanner = '';
        if (!asset) {
            newDeviceBanner = `
                <div style="background: #fff1f2; border: 1.5px solid #fecdd3; border-radius: 12px; padding: 14px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            <i class="bi bi-sparkles"></i>
                        </div>
                        <div>
                            <div style="font-weight: 700; color: #e11d48; font-size: 14px;">ตรวจพบเครื่องใหม่ (ยังไม่มีในระบบทะเบียนครุภัณฑ์)</div>
                            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                เครื่องนี้ยังไม่เคยผูกกับครุภัณฑ์ สามารถเชื่อมโยงกับครุภัณฑ์เดิม หรือเพิ่มเป็นครุภัณฑ์ใหม่ได้ทันที
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-sm btn-info text-white" onclick="closeAuditDiffModal(); openLinkAssetModal(currentDiffAudit);" 
                                style="font-weight: 700; padding: 6px 14px; border-radius: 8px;">
                            <i class="bi bi-link-45deg me-1"></i> เชื่อมโยงครุภัณฑ์เดิม
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="closeAuditDiffModal(); openCreateAssetModal(currentDiffAudit);" 
                                style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none; font-weight: 700; padding: 6px 14px; border-radius: 8px;">
                            <i class="bi bi-plus-circle-fill me-1"></i> เพิ่มเป็นครุภัณฑ์ใหม่
                        </button>
                    </div>
                </div>
            `;
        }

        body.innerHTML = `
            ${newDeviceBanner}
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong style="font-size: 15px; color: #0f172a;">${audit.hostname || 'ไม่ระบุชื่อเครื่อง'}</strong>
                    <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                        ${audit.hardware_id ? '<span class="badge me-1" style="background:#fdf4ff; color:#a21caf; border:1px solid #f0abfc; font-family:monospace;">HWID: ' + audit.hardware_id + '</span>' : ''}
                        Serial Number: <code>${audit.serial_number || '-'}</code> &bull; 
                        ${asset ? 'รหัสครุภัณฑ์: <strong>' + asset.asset_code + '</strong>' : '<span class="text-danger fw-bold">ไม่พบครุภัณฑ์ที่จับคู่</span>'}
                    </div>
                </div>
                <div>
                    ${audit.has_diff 
                        ? '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1">พบการเปลี่ยนแปลงสเปค</span>' 
                        : '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">สเปคตรงกับในระบบ</span>'}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 12.5px;">
                    <thead style="background: #f1f5f9; color: #334155;">
                        <tr>
                            <th>รายการฮาร์ดแวร์</th>
                            <th>สเปคเดิมในคลังครุภัณฑ์</th>
                            <th>สเปคใหม่ที่สแกนพบจาก Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${diffRows}
                    </tbody>
                </table>
            </div>
        `;

        if (audit.status === 'pending') {
            if (asset) {
                approveBtn.style.display = 'inline-flex';
                approveBtn.innerHTML = '<i class="bi bi-check-lg"></i> อนุมัติและบันทึกลงครุภัณฑ์';
                approveBtn.onclick = function() {
                    closeAuditDiffModal();
                    approveSingleAudit(audit.id, audit.hostname);
                };
            } else {
                approveBtn.style.display = 'inline-flex';
                approveBtn.innerHTML = '<i class="bi bi-plus-circle-fill"></i> เพิ่มเป็นครุภัณฑ์ใหม่';
                approveBtn.onclick = function() {
                    closeAuditDiffModal();
                    openCreateAssetModal(audit);
                };
            }
        } else {
            approveBtn.style.display = 'none';
        }

        modal.style.display = 'flex';
    }

    function closeAuditDiffModal() {
        document.getElementById('auditDiffModal').style.display = 'none';
    }

    // Filter department cards
    function filterDeptCards() {
        const filter = document.getElementById('deptFilterInput').value.toLowerCase();
        const cards = document.querySelectorAll('.dept-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            if (name.includes(filter)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // -------------------------------------------------------------
    // Initialize Chart.js on Tab 'report'
    // -------------------------------------------------------------
    @if($tab === 'report')
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') return;

        // 1. RAM Chart
        const ramCtx = document.getElementById('ramChart');
        if (ramCtx) {
            const ramData = @json($ramDistribution);
            new Chart(ramCtx, {
                type: 'doughnut',
                data: {
                    labels: ramData.map(d => `${d.ram_capacity} GB`),
                    datasets: [{
                        data: ramData.map(d => d.total),
                        backgroundColor: ['#0d9488', '#0284c7', '#38bdf8', '#f59e0b', '#ef4444', '#8b5cf6'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { family: 'Prompt', size: 11 } } }
                    },
                    cutout: '65%'
                }
            });
        }

        // 2. OS Chart
        const osCtx = document.getElementById('osChart');
        if (osCtx) {
            const osData = @json($osDistribution);
            new Chart(osCtx, {
                type: 'doughnut',
                data: {
                    labels: osData.map(d => d.os_name),
                    datasets: [{
                        data: osData.map(d => d.total),
                        backgroundColor: ['#0284c7', '#0d9488', '#6366f1', '#f59e0b', '#ef4444'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { family: 'Prompt', size: 11 } } }
                    },
                    cutout: '65%'
                }
            });
        }

        // 3. Storage Chart
        const storageCtx = document.getElementById('storageChart');
        if (storageCtx) {
            const stData = @json($storageDistribution);
            new Chart(storageCtx, {
                type: 'pie',
                data: {
                    labels: stData.map(d => d.storage_type),
                    datasets: [{
                        data: stData.map(d => d.total),
                        backgroundColor: ['#10b981', '#f59e0b', '#0284c7', '#64748b'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, font: { family: 'Prompt', size: 11 } } }
                    }
                }
            });
        }
    });
    @endif

    // -------------------------------------------------------------
    // Agent Fleet Selection & Batch Telemetry Pull
    // -------------------------------------------------------------
    function toggleFleetSelectAll(master) {
        const visibleRows = Array.from(document.querySelectorAll('.fleet-row')).filter(r => r.style.display !== 'none');
        visibleRows.forEach(row => {
            const chk = row.querySelector('.fleet-item-chk');
            if (chk) chk.checked = master.checked;
        });
        updateFleetSelectionCount();
    }

    function updateFleetSelectionCount() {
        const checked = document.querySelectorAll('.fleet-item-chk:checked');
        const badge = document.getElementById('selectedFleetBadge');
        const btn = document.getElementById('btnSelectedFleetScan');
        if (badge) badge.textContent = checked.length;
        if (btn) btn.disabled = checked.length === 0;

        const updateBadge = document.getElementById('selectedFleetUpdateBadge');
        const updateBtn = document.getElementById('btnSelectedFleetUpdate');
        if (updateBadge) updateBadge.textContent = checked.length;
        if (updateBtn) updateBtn.disabled = checked.length === 0;

        const deleteBadge = document.getElementById('selectedFleetDeleteBadge');
        const deleteBtn = document.getElementById('btnSelectedFleetDelete');
        if (deleteBadge) deleteBadge.textContent = checked.length;
        if (deleteBtn) deleteBtn.disabled = checked.length === 0;
    }

    let currentFleetFilter = 'all';

    function setFleetFilter(status) {
        currentFleetFilter = status;
        ['fleetFilterAll', 'fleetFilterOnline', 'fleetFilterOffline', 'fleetFilterOutdated'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.classList.remove('active');
        });
        if (status === 'all') document.getElementById('fleetFilterAll')?.classList.add('active');
        if (status === 'online') document.getElementById('fleetFilterOnline')?.classList.add('active');
        if (status === 'offline') document.getElementById('fleetFilterOffline')?.classList.add('active');
        if (status === 'outdated') document.getElementById('fleetFilterOutdated')?.classList.add('active');
        filterFleetRows();
    }

    function filterFleetRows() {
        const query = (document.getElementById('fleetSearchInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.fleet-row');

        rows.forEach(row => {
            const isOnline = row.getAttribute('data-online') === 'true';
            const isOutdated = row.getAttribute('data-outdated') === 'true';
            const searchData = row.getAttribute('data-search') || '';

            let matchesFilter = true;
            if (currentFleetFilter === 'online' && !isOnline) matchesFilter = false;
            if (currentFleetFilter === 'offline' && isOnline) matchesFilter = false;
            if (currentFleetFilter === 'outdated' && !isOutdated) matchesFilter = false;

            let matchesSearch = true;
            if (query && !searchData.includes(query)) matchesSearch = false;

            if (matchesFilter && matchesSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // uncheck select all if filtered
        const master = document.getElementById('fleetSelectAll');
        if (master) master.checked = false;
        updateFleetSelectionCount();
    }

    function triggerSelectedFleetScan() {
        const checked = document.querySelectorAll('.fleet-item-chk:checked');
        if (checked.length === 0) return;

        const targets = [];
        checked.forEach(chk => {
            targets.push({
                hostname: chk.dataset.hostname || '',
                hardware_id: chk.dataset.hwid || '',
                audit_id: chk.dataset.auditId || null,
                asset_id: chk.dataset.assetId || null
            });
        });

        const onlineCount = Array.from(checked).filter(c => c.dataset.online === 'true').length;
        const offlineCount = checked.length - onlineCount;

        let note = `จะส่งสัญญาณคำสั่งสแกนไปยังเครื่องที่เลือก <b>${checked.length} เครื่อง</b>`;
        if (offlineCount > 0) {
            note += `<br><small style="color: #64748b;">(ออนไลน์: ${onlineCount} เครื่อง, ออฟไลน์: ${offlineCount} เครื่อง - เครื่องออฟไลน์จะรับคำสั่งทันทีที่เปิดเครื่องหรือเชื่อมต่อเครือข่าย)</small>`;
        }

        if (window.Swal) {
            Swal.fire({
                title: `ดึงข้อมูลสเปก ${checked.length} เครื่อง?`,
                html: `${note}<br><br>เครื่องลูกข่ายจะเริ่มส่งสเปกฮาร์ดแวร์ล่าสุดเข้ามาในระบบโดยอัตโนมัติ`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-cloud-arrow-down-fill me-1"></i> ยืนยันสั่งดึงข้อมูล',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#0f766e',
                cancelButtonColor: '#64748b',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    executeFleetScanBatch(targets);
                }
            });
        } else {
            if (confirm(`ยืนยันสั่งดึงข้อมูลสเปก ${checked.length} เครื่อง?`)) {
                executeFleetScanBatch(targets);
            }
        }
    }

    function triggerAllOnlineFleetScan() {
        const onlineChks = document.querySelectorAll('.fleet-item-chk[data-online="true"]');
        if (onlineChks.length === 0) {
            alert('ขณะนี้ไม่มีเครื่องที่ออนไลน์อยู่ในระบบ');
            return;
        }

        const targets = [];
        onlineChks.forEach(chk => {
            targets.push({
                hostname: chk.dataset.hostname || '',
                hardware_id: chk.dataset.hwid || '',
                audit_id: chk.dataset.auditId || null,
                asset_id: chk.dataset.assetId || null
            });
        });

        if (window.Swal) {
            Swal.fire({
                title: `ดึงข้อมูลสเปกทุกเครื่องที่ออนไลน์ (${targets.length} เครื่อง)?`,
                html: `ระบบจะส่งคำสั่งไปยัง Agent ทั้งหมดที่กำลังเชื่อมต่ออยู่ในขณะนี้ เพื่อดึงข้อมูลสเปกสดเข้ามาสู่ระบบทันที`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-broadcast me-1"></i> สั่งดึงสเปกเดี๋ยวนี้',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                reverseButtons: true
            }).then((res) => {
                if (res.isConfirmed) {
                    executeFleetScanBatch(targets);
                }
            });
        } else {
            if (confirm(`สั่งดึงสเปกทุกเครื่องที่ออนไลน์ (${targets.length} เครื่อง)?`)) {
                executeFleetScanBatch(targets);
            }
        }
    }

    function triggerSingleFleetScan(hostname, hwid, auditId, assetId) {
        executeFleetScanBatch([{
            hostname: hostname,
            hardware_id: hwid,
            audit_id: auditId || null,
            asset_id: assetId || null
        }]);
    }

    // Single Machine Deletion State
    let pendingDeleteMachine = null;

    function openDeleteMachineModal(hostname, hwid, ip, isOnline, auditId) {
        pendingDeleteMachine = {
            hostname: hostname,
            hardware_id: hwid,
            ip_address: ip,
            is_online: !!isOnline,
            audit_id: auditId || null
        };

        document.getElementById('delMachineHostname').textContent = hostname || 'Unknown PC';
        document.getElementById('delMachineIp').textContent = ip || '-';
        document.getElementById('delMachineHwid').textContent = hwid || '-';

        const badge = document.getElementById('delMachineOnlineBadge');
        if (isOnline) {
            badge.style.background = '#dcfce7';
            badge.style.color = '#15803d';
            badge.textContent = '🟢 ออนไลน์';
            document.getElementById('chkRemoteUninstall').checked = true;
        } else {
            badge.style.background = '#f1f5f9';
            badge.style.color = '#64748b';
            badge.textContent = '⚪ ออฟไลน์';
            document.getElementById('chkRemoteUninstall').checked = false;
        }

        document.getElementById('deleteMachineModal').style.display = 'flex';
    }

    function closeDeleteMachineModal() {
        document.getElementById('deleteMachineModal').style.display = 'none';
        pendingDeleteMachine = null;
    }

    function confirmDeleteMachineSubmit() {
        if (!pendingDeleteMachine) return;
        const btn = document.getElementById('btnConfirmDeleteMachine');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังลบ...';

        const remoteUninstall = document.getElementById('chkRemoteUninstall').checked;

        fetch("{{ route('hardware-audits.delete-machine') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                hostname: pendingDeleteMachine.hostname,
                hardware_id: pendingDeleteMachine.hardware_id,
                ip_address: pendingDeleteMachine.ip_address,
                remote_uninstall: remoteUninstall ? 1 : 0,
                audit_id: pendingDeleteMachine.audit_id
            })
        })
        .then(res => res.json())
        .then(data => {
            closeDeleteMachineModal();
            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบเครื่องออกจากระบบสำเร็จ',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0f766e'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการลบเครื่อง');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-trash3-fill me-1"></i> ยืนยันลบเครื่องนี้';
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถลบเครื่องได้',
                    text: err.message,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(err.message);
            }
        });
    }

    // Batch Delete Selected Machines
    function deleteSelectedFleetMachines() {
        const checked = document.querySelectorAll('.fleet-item-chk:checked');
        if (checked.length === 0) return;

        const targets = Array.from(checked).map(chk => ({
            hostname: chk.getAttribute('data-hostname'),
            hardware_id: chk.getAttribute('data-hwid'),
            audit_id: chk.getAttribute('data-audit-id') || null,
            is_online: chk.getAttribute('data-online') === 'true'
        }));

        const count = targets.length;

        if (window.Swal) {
            Swal.fire({
                title: `ลบเครื่องที่เลือก (${count} เครื่อง) ออกจากระบบ?`,
                html: `
                    <div style="text-align: left; font-size: 13px; color: #475569;">
                        <p>ระบบจะล้างประวัติการสแกนฮาร์ดแวร์ล่าสุด และนำออกจากระบบ Agent Fleet</p>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; margin: 0;">
                                <input type="checkbox" id="swalBatchRemoteUninstall" checked class="form-check-input">
                                <span style="color: #991b1b; font-weight: 600;">ส่งคำสั่งถอนการติดตั้งไปยัง Agent ทุกเครื่อง (Remote Uninstall)</span>
                            </label>
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: `<i class="bi bi-trash3-fill me-1"></i> ยืนยันลบ ${count} เครื่อง`,
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                reverseButtons: true
            }).then(res => {
                if (res.isConfirmed) {
                    const chk = document.getElementById('swalBatchRemoteUninstall');
                    const remoteUninstall = chk ? chk.checked : true;
                    executeBatchDeleteFleet(targets, remoteUninstall);
                }
            });
        } else {
            if (confirm(`คุณต้องการลบเครื่องที่เลือก (${count} เครื่อง) ออกจากระบบหรือไม่?`)) {
                executeBatchDeleteFleet(targets, true);
            }
        }
    }

    function executeBatchDeleteFleet(targets, remoteUninstall) {
        if (window.Swal) {
            Swal.fire({
                title: 'กำลังลบเครื่องที่เลือก...',
                text: 'กรุณารอสักครู่ ระบบกำลังประมวลผล',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch("{{ route('hardware-audits.batch-delete-machines') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                targets: targets,
                remote_uninstall: remoteUninstall ? 1 : 0
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ลบเครื่องสำเร็จ',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0f766e'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(data.message);
                    window.location.reload();
                }
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการลบเครื่อง');
            }
        })
        .catch(err => {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถลบเครื่องได้',
                    text: err.message,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(err.message);
            }
        });
    }

    // Trigger Agent Update
    function triggerSingleFleetUpdate(hostname, hwid) {
        triggerFleetUpdateExecution([{
            hostname: hostname,
            hardware_id: hwid
        }]);
    }

    function triggerSelectedFleetUpdate() {
        const checked = document.querySelectorAll('.fleet-item-chk:checked');
        if (checked.length === 0) return;

        const targets = Array.from(checked).map(chk => ({
            hostname: chk.getAttribute('data-hostname'),
            hardware_id: chk.getAttribute('data-hwid')
        }));

        triggerFleetUpdateExecution(targets);
    }

    // Central Remote Update: Update All Outdated Machines in Fleet
    function triggerUpdateAllOutdatedFleet() {
        const outdatedRows = document.querySelectorAll('.fleet-row[data-outdated="true"]');
        if (outdatedRows.length === 0) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'info',
                    title: 'ข้อมูลเป็นปัจจุบันแล้ว',
                    text: 'ทุกเครื่องในระบบติดตั้ง Agent เวอร์ชันล่าสุด (v{{ $latestAgentVersion ?? '2.11.0' }}) เรียบร้อยแล้ว',
                    confirmButtonColor: '#0f766e'
                });
            } else {
                alert('ทุกเครื่องเป็นเวอร์ชันล่าสุดแล้ว');
            }
            return;
        }

        const targets = [];
        outdatedRows.forEach(row => {
            const chk = row.querySelector('.fleet-item-chk');
            if (chk) {
                const hn = chk.getAttribute('data-hostname');
                const hw = chk.getAttribute('data-hwid');
                if (hn || hw) {
                    targets.push({
                        hostname: hn,
                        hardware_id: hw
                    });
                }
            }
        });

        if (targets.length === 0) return;

        if (window.Swal) {
            Swal.fire({
                title: `สั่งอัปเดตเครื่องเวอร์ชันเก่าทั้งหมด (${targets.length} เครื่อง)?`,
                html: `
                    <div style="text-align: left; font-size: 13.5px; color: #475569; line-height: 1.6;">
                        <p>ระบบจะส่งสัญญาณคำสั่ง Remote Update ไปยัง <b>${targets.length} เครื่อง</b> ที่กำลังใช้งาน Agent เวอร์ชันเก่า</p>
                        <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 10px 14px; color: #166534; font-size: 12.5px;">
                            <i class="bi bi-shield-check text-success me-1"></i> เครื่องลูกข่ายจะดาวน์โหลดไบนารี <b>v{{ $latestAgentVersion ?? '2.11.0' }}</b>, ตรวจสอบ SHA-256 และทำการ Hot-Swap ให้อัตโนมัติทันที
                        </div>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> ยืนยันสั่งอัปเดตทั้งหมด',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#64748b'
            }).then(res => {
                if (res.isConfirmed) {
                    dispatchAgentUpdateApi(targets);
                }
            });
        } else {
            if (confirm(`สั่งอัปเดตเครื่องเวอร์ชันเก่าทั้งหมด (${targets.length} เครื่อง)?`)) {
                dispatchAgentUpdateApi(targets);
            }
        }
    }

    function triggerFleetUpdateExecution(targets) {
        if (!targets || targets.length === 0) return;

        if (window.Swal) {
            Swal.fire({
                title: `ส่งคำสั่งอัปเดต Agent (${targets.length} เครื่อง)?`,
                text: 'เครื่องเป้าหมายจะได้รับคำสั่งให้อัปเดตเป็นเวอร์ชันล่าสุดและรีสตาร์ท Agent ใหม่อัตโนมัติ',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-arrow-repeat me-1"></i> ยืนยันสั่งอัปเดต',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#64748b'
            }).then(res => {
                if (res.isConfirmed) {
                    dispatchAgentUpdateApi(targets);
                }
            });
        } else {
            if (confirm(`ส่งคำสั่งอัปเดต Agent (${targets.length} เครื่อง)?`)) {
                dispatchAgentUpdateApi(targets);
            }
        }
    }

    function dispatchAgentUpdateApi(targets) {
        if (window.Swal) {
            Swal.fire({
                title: 'กำลังส่งคำสั่งอัปเดต...',
                text: 'กรุณารอสักครู่',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch("{{ route('hardware-audits.trigger-update') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                targets: targets
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งคำสั่งอัปเดตสำเร็จ',
                        text: data.message,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0f766e'
                    });
                } else {
                    alert(data.message);
                }
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการส่งคำสั่งอัปเดต');
            }
        })
        .catch(err => {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ส่งคำสั่งอัปเดตไม่สำเร็จ',
                    text: err.message,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(err.message);
            }
        });
    }

    function executeFleetScanBatch(targets) {
        if (!targets || targets.length === 0) return;

        if (window.Swal) {
            Swal.fire({
                title: 'กำลังส่งคำสั่งดึงข้อมูล...',
                text: 'กรุณารอสักครู่ ระบบกำลังสื่อสารกับ Agent ประจำเครื่อง',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
        }

        fetch("{{ route('hardware-audits.trigger-scan-batch') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                fiscal_year: '{{ $fiscalYear }}',
                targets: targets
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'ส่งคำสั่งสำเร็จ!',
                        html: `${data.message}<br><small style="color: #64748b;">(ข้อมูลที่ส่งเข้ามาจะปรากฏในแท็บ 'คิวรอตรวจสอบและอนุมัติ')</small>`,
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0f766e'
                    }).then(() => {
                        // Success confirmation
                    });
                } else {
                    alert(data.message);
                }
            } else {
                throw new Error(data.message || 'เกิดข้อผิดพลาดในการส่งคำสั่ง');
            }
        })
        .catch(err => {
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถส่งคำสั่งได้',
                    text: err.message || 'การเชื่อมต่อขัดข้อง กรุณาลองใหม่อีกครั้ง',
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(err.message || 'การเชื่อมต่อขัดข้อง');
            }
        });
    }

    // Live Agent Online Status Background Poller (every 8 seconds)
    function pollLiveAgentOnlineStatus() {
        fetch("{{ route('hardware-audits.online-status') }}", {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && Array.isArray(data.agents)) {
                const countBadge = document.getElementById('liveOnlineAgentsCount');
                if (countBadge) {
                    countBadge.textContent = data.online_count;
                }
                const tabBadge = document.getElementById('tabAgentOnlineCount');
                if (tabBadge) {
                    tabBadge.textContent = data.online_count;
                }
                const fleetBadge = document.getElementById('fleetOnlineCountDisplay');
                if (fleetBadge) {
                    fleetBadge.textContent = data.online_count;
                }
            }
        })
        .catch(() => {});
    }

    /* =========================================================================
     * THC 3D INTERACTIVE DEPARTMENT MAP DIAGRAM ENGINE
     * Zero-dependency pure CSS3D + Canvas/SVG system for hospital intranet
     * ========================================================================= */
    window.map3dData = @json($map3dData ?? null);

    const THC_3D = {
        data: null,
        currentBuilding: 'all',
        currentFloor: 'all',
        currentStatusFilter: 'all',
        searchQuery: '',
        viewMode: 'iso', // 'iso', 'perspective', 'topdown'
        rotX: 58,
        rotZ: 42,
        zoom: 100,
        panX: 0,
        panY: 0,
        isMouseDown: false,
        startX: 0,
        startY: 0,
        dragTargetRoom: null,
        roomDragStartX: 0,
        roomDragStartY: 0,
        roomInitialLeft: 0,
        roomInitialTop: 0,
        designMode: false,
        customLayout: {},
        selectedDeviceId: null,

        init: function(data) {
            this.data = data;
            const stage = document.getElementById('map3dStage');
            if (!stage || !this.data) return;

            // Load saved layout from localStorage if exists
            try {
                const saved = localStorage.getItem('thc_floorplan_layout_custom');
                if (saved) {
                    this.customLayout = JSON.parse(saved);
                }
            } catch (e) {
                this.customLayout = {};
            }

            // Bind mouse & touch drag events
            this.bindEvents(stage);

            // Check if user came from department card with target dept
            let targetDeptId = null;
            try {
                targetDeptId = localStorage.getItem('thc_3d_target_dept');
                if (targetDeptId) {
                    localStorage.removeItem('thc_3d_target_dept');
                }
            } catch (e) {}

            if (!targetDeptId && window.location.hash && window.location.hash.startsWith('#dept-')) {
                targetDeptId = window.location.hash.replace('#dept-', '');
            }

            if (targetDeptId && this.data.departments) {
                const targetDept = this.data.departments.find(d => String(d.id) === String(targetDeptId));
                if (targetDept) {
                    this.currentBuilding = targetDept.building_id || 'all';
                    this.currentFloor = targetDept.floor || 'all';
                    this.syncBuildingButtons();
                    this.syncFloorButtons();
                }
            }

            this.render();

            if (targetDeptId) {
                setTimeout(() => {
                    this.focusDepartment(targetDeptId);
                }, 400);
            }
        },

        bindEvents: function(stage) {
            const world = document.getElementById('map3dWorld');
            if (!stage || !world) return;

            // Prevent default drag
            stage.addEventListener('dragstart', (e) => e.preventDefault());

            // Mouse down
            stage.addEventListener('mousedown', (e) => {
                if (e.target.closest('.ha-3d-device') || e.target.closest('.ha-drawer-close') || e.target.closest('button') || e.target.closest('input')) {
                    return;
                }

                // If in design mode and clicked a room
                if (this.designMode) {
                    const roomEl = e.target.closest('.ha-3d-room');
                    if (roomEl) {
                        this.dragTargetRoom = roomEl;
                        this.roomDragStartX = e.clientX;
                        this.roomDragStartY = e.clientY;
                        this.roomInitialLeft = parseFloat(roomEl.style.left) || 0;
                        this.roomInitialTop = parseFloat(roomEl.style.top) || 0;
                        e.stopPropagation();
                        return;
                    }
                }

                this.isMouseDown = true;
                this.startX = e.clientX;
                this.startY = e.clientY;
            });

            // Mouse move
            window.addEventListener('mousemove', (e) => {
                // Room dragging in design mode
                if (this.designMode && this.dragTargetRoom) {
                    const scaleFactor = Math.max(0.2, this.zoom / 100);
                    const deltaX = (e.clientX - this.roomDragStartX) / scaleFactor;
                    const deltaY = (e.clientY - this.roomDragStartY) / scaleFactor;

                    // Snap to 10px grid
                    const newLeft = Math.round((this.roomInitialLeft + deltaX) / 10) * 10;
                    const newTop = Math.round((this.roomInitialTop + deltaY) / 10) * 10;

                    this.dragTargetRoom.style.left = `${newLeft}px`;
                    this.dragTargetRoom.style.top = `${newTop}px`;

                    const roomId = this.dragTargetRoom.getAttribute('data-room-id');
                    if (roomId) {
                        this.customLayout[roomId] = { left: newLeft, top: newTop };
                    }
                    return;
                }

                if (!this.isMouseDown) return;

                const deltaX = e.clientX - this.startX;
                const deltaY = e.clientY - this.startY;
                this.startX = e.clientX;
                this.startY = e.clientY;

                if (e.shiftKey || e.button === 2 || this.viewMode === 'topdown' || this.viewMode === 'iso') {
                    // Pan
                    this.panX += deltaX;
                    this.panY += deltaY;
                } else {
                    // Orbit / Rotate
                    this.rotZ += deltaX * 0.45;
                    this.rotX = Math.max(15, Math.min(85, this.rotX - deltaY * 0.35));
                }

                this.updateCamera();
            });

            // Mouse up
            window.addEventListener('mouseup', () => {
                this.isMouseDown = false;
                this.dragTargetRoom = null;
            });

            // Wheel zoom
            stage.addEventListener('wheel', (e) => {
                e.preventDefault();
                const zoomFactor = e.deltaY < 0 ? 10 : -10;
                this.zoom = Math.max(35, Math.min(220, this.zoom + zoomFactor));
                this.updateCamera();
            }, { passive: false });

            // Touch events for mobile/tablet
            let touchStartX = 0;
            let touchStartY = 0;
            stage.addEventListener('touchstart', (e) => {
                if (e.touches.length === 1) {
                    touchStartX = e.touches[0].clientX;
                    touchStartY = e.touches[0].clientY;
                    this.isMouseDown = true;
                    this.startX = touchStartX;
                    this.startY = touchStartY;
                }
            }, { passive: true });

            stage.addEventListener('touchmove', (e) => {
                if (e.touches.length === 1 && this.isMouseDown) {
                    const deltaX = e.touches[0].clientX - this.startX;
                    const deltaY = e.touches[0].clientY - this.startY;
                    this.startX = e.touches[0].clientX;
                    this.startY = e.touches[0].clientY;

                    if (this.viewMode === 'topdown' || this.viewMode === 'iso') {
                        this.panX += deltaX;
                        this.panY += deltaY;
                    } else {
                        this.rotZ += deltaX * 0.5;
                        this.rotX = Math.max(15, Math.min(85, this.rotX - deltaY * 0.4));
                    }
                    this.updateCamera();
                }
            }, { passive: true });

            stage.addEventListener('touchend', () => {
                this.isMouseDown = false;
            });
        },

        updateCamera: function() {
            const world = document.getElementById('map3dWorld');
            if (!world) return;

            let rx = this.rotX;
            let rz = this.rotZ;

            if (this.viewMode === 'topdown') {
                rx = 0;
                rz = 0;
            } else if (this.viewMode === 'iso') {
                rx = 58;
                rz = 42;
            }

            const scale = this.zoom / 100;
            world.style.transform = `translate(-50%, -50%) translate3d(${this.panX}px, ${this.panY}px, 0px) rotateX(${rx}deg) rotateZ(${rz}deg) scale(${scale})`;

            const cameraInfo = document.getElementById('hudCameraInfo');
            if (cameraInfo) {
                cameraInfo.textContent = `มุมหมุน: ${Math.round(rz)}° • มุมเอียง: ${Math.round(rx)}° • ซูม: ${this.zoom}%`;
            }
        },

        setViewMode: function(mode) {
            this.viewMode = mode;
            ['btnModeIso', 'btnModePerspective', 'btnModeTopdown'].forEach(id => {
                const b = document.getElementById(id);
                if (b) b.classList.remove('active');
            });

            if (mode === 'iso') {
                const b = document.getElementById('btnModeIso');
                if (b) b.classList.add('active');
                this.rotX = 58;
                this.rotZ = 42;
            } else if (mode === 'perspective') {
                const b = document.getElementById('btnModePerspective');
                if (b) b.classList.add('active');
                this.rotX = 55;
                this.rotZ = 30;
            } else if (mode === 'topdown') {
                const b = document.getElementById('btnModeTopdown');
                if (b) b.classList.add('active');
                this.rotX = 0;
                this.rotZ = 0;
            }

            this.updateCamera();
        },

        rotateLeft: function() {
            this.viewMode = 'perspective';
            this.syncViewModeButtons();
            this.rotZ -= 45;
            this.updateCamera();
        },

        rotateRight: function() {
            this.viewMode = 'perspective';
            this.syncViewModeButtons();
            this.rotZ += 45;
            this.updateCamera();
        },

        zoomIn: function() {
            this.zoom = Math.min(220, this.zoom + 20);
            this.updateCamera();
        },

        zoomOut: function() {
            this.zoom = Math.max(35, this.zoom - 20);
            this.updateCamera();
        },

        resetView: function() {
            this.panX = 0;
            this.panY = 0;
            this.zoom = 100;
            this.setViewMode('iso');
        },

        syncViewModeButtons: function() {
            ['btnModeIso', 'btnModePerspective', 'btnModeTopdown'].forEach(id => {
                const b = document.getElementById(id);
                if (b) b.classList.remove('active');
            });
            const p = document.getElementById('btnModePerspective');
            if (p) p.classList.add('active');
        },

        setBuilding: function(bldId) {
            this.currentBuilding = bldId;
            this.syncBuildingButtons();
            this.render();
        },

        syncBuildingButtons: function() {
            const container = document.getElementById('map3dBuildingSelector');
            if (!container) return;
            const buttons = container.querySelectorAll('.ha-bld-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('data-bld') === this.currentBuilding) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        },

        setFloor: function(floor) {
            this.currentFloor = floor;
            this.syncFloorButtons();
            this.render();
        },

        syncFloorButtons: function() {
            const container = document.getElementById('map3dFloorSelector');
            if (!container) return;
            const buttons = container.querySelectorAll('.ha-floor-btn');
            buttons.forEach(btn => {
                const f = btn.getAttribute('data-floor');
                if (String(f) === String(this.currentFloor)) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        },

        setStatusFilter: function(status) {
            this.currentStatusFilter = status;
            const filterContainer = document.querySelector('.ha-map3d-status-filters');
            if (filterContainer) {
                const btns = filterContainer.querySelectorAll('.ha-status-filter-btn');
                btns.forEach(b => {
                    if (b.getAttribute('data-status') === status) {
                        b.classList.add('active');
                    } else {
                        b.classList.remove('active');
                    }
                });
            }
            this.applyStatusFilter();
        },

        applyStatusFilter: function() {
            const status = this.currentStatusFilter;
            const devices = document.querySelectorAll('.ha-3d-device');
            devices.forEach(dev => {
                let match = true;
                if (status === 'audited') {
                    match = dev.classList.contains('status-audited');
                } else if (status === 'online') {
                    match = dev.classList.contains('status-online');
                } else if (status === 'pending') {
                    match = dev.classList.contains('status-pending');
                } else if (status === 'warning') {
                    match = dev.classList.contains('status-warning');
                }

                if (match) {
                    dev.style.opacity = '1';
                    dev.style.filter = 'none';
                } else {
                    dev.style.opacity = '0.2';
                    dev.style.filter = 'grayscale(80%)';
                }
            });
        },

        searchDevices: function(val) {
            this.searchQuery = (val || '').trim().toLowerCase();
            const query = this.searchQuery;
            const devices = document.querySelectorAll('.ha-3d-device');
            let firstMatch = null;

            devices.forEach(dev => {
                const searchStr = (dev.getAttribute('data-search') || '').toLowerCase();
                if (!query || searchStr.includes(query)) {
                    dev.style.opacity = '1';
                    dev.style.filter = 'none';
                    if (query) {
                        dev.style.boxShadow = '0 0 16px #f59e0b';
                        dev.style.borderColor = '#fbbf24';
                        if (!firstMatch) firstMatch = dev;
                    } else {
                        dev.style.boxShadow = '';
                        dev.style.borderColor = '';
                    }
                } else {
                    dev.style.opacity = '0.15';
                    dev.style.filter = 'grayscale(100%)';
                    dev.style.boxShadow = '';
                    dev.style.borderColor = '';
                }
            });

            if (firstMatch) {
                const roomEl = firstMatch.closest('.ha-3d-room');
                if (roomEl) {
                    this.showBeacon(roomEl, firstMatch);
                }
            } else {
                const beaconLayer = document.getElementById('map3dBeaconLayer');
                if (beaconLayer) beaconLayer.innerHTML = '';
            }
        },

        clearSearch: function() {
            const inp = document.getElementById('map3dSearchInput');
            if (inp) inp.value = '';
            this.searchDevices('');
        },

        render: function() {
            const container = document.getElementById('map3dRoomsContainer');
            if (!container || !this.data || !this.data.departments) return;

            container.innerHTML = '';

            // Filter departments by building and floor
            const filteredDepts = this.data.departments.filter(dept => {
                if (this.currentBuilding !== 'all' && dept.building_id !== this.currentBuilding) {
                    return false;
                }
                if (this.currentFloor !== 'all' && String(dept.floor) !== String(this.currentFloor)) {
                    return false;
                }
                return true;
            });

            // Update HUD counters
            this.updateHUD(filteredDepts);

            // Compute layout for rooms
            const roomWidth = 240;
            const gapX = 32;
            const gapY = 32;
            const cols = 5;

            filteredDepts.forEach((dept, index) => {
                const col = index % cols;
                const row = Math.floor(index / cols);

                const devCount = (dept.devices || []).length;
                const rowsNeeded = Math.max(2, Math.ceil(devCount / 5));
                const roomHeight = Math.max(160, 36 + rowsNeeded * 42 + 16);

                let posX = 50 + col * (roomWidth + gapX);
                let posY = 60 + row * (roomHeight + gapY);

                // Check custom layout override
                if (this.customLayout[dept.id]) {
                    posX = this.customLayout[dept.id].left;
                    posY = this.customLayout[dept.id].top;
                }

                const roomEl = document.createElement('div');
                roomEl.className = 'ha-3d-room';
                roomEl.id = `map3dRoom_${dept.id}`;
                roomEl.setAttribute('data-room-id', dept.id);
                roomEl.style.left = `${posX}px`;
                roomEl.style.top = `${posY}px`;
                roomEl.style.width = `${roomWidth}px`;
                roomEl.style.height = `${roomHeight}px`;

                // Header
                const pctClass = dept.percentage === 100 ? 'bg-success' : (dept.percentage > 0 ? 'bg-primary' : 'bg-secondary');
                const floorBadge = dept.floor ? `<span style="font-size: 9.5px; opacity: 0.75; margin-left: 4px;">(ช.${dept.floor})</span>` : '';

                roomEl.innerHTML = `
                    <div class="ha-3d-room-header">
                        <div class="ha-3d-room-title" title="${dept.name}">
                            ${dept.name} ${floorBadge}
                        </div>
                        <span class="ha-3d-room-badge ${pctClass}">
                            ${dept.percentage}%
                        </span>
                    </div>
                    <div class="ha-3d-room-body" id="roomBody_${dept.id}"></div>
                `;

                container.appendChild(roomEl);

                // Render workstation nodes inside this room
                const bodyEl = roomEl.querySelector(`#roomBody_${dept.id}`);
                this.renderRoomDevices(bodyEl, dept);
            });

            this.updateCamera();
            this.applyStatusFilter();
        },

        renderRoomDevices: function(bodyEl, dept) {
            if (!bodyEl || !dept.devices) return;

            const devices = dept.devices;
            if (devices.length === 0) {
                bodyEl.innerHTML = `<div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 11px; color: #64748b; font-style: italic;"><i class="bi bi-inbox me-1"></i> ไม่มีคอมพิวเตอร์</div>`;
                return;
            }

            const devCols = 5;
            const nodeSize = 30;
            const nodeGap = 10;
            const padLeft = 14;
            const padTop = 10;

            devices.forEach((dev, idx) => {
                const c = idx % devCols;
                const r = Math.floor(idx / devCols);
                const x = padLeft + c * (nodeSize + nodeGap);
                const y = padTop + r * (nodeSize + nodeGap);

                const devEl = document.createElement('div');
                devEl.className = 'ha-3d-device';
                devEl.id = `devNode_${dev.id}`;
                devEl.setAttribute('data-dev-id', dev.id);
                devEl.style.left = `${x}px`;
                devEl.style.top = `${y}px`;

                // Status classes
                if (dev.is_audited) devEl.classList.add('status-audited');
                else devEl.classList.add('status-pending');

                if (dev.is_online) devEl.classList.add('status-online');
                if (dev.is_mdes_warning) devEl.classList.add('status-warning');

                // Icon
                let iconClass = 'bi-display';
                const dt = (dev.device_type || '').toUpperCase();
                if (dt === 'NB' || dt.includes('NOTEBOOK') || dt.includes('LAPTOP')) {
                    iconClass = 'bi-laptop';
                } else if (dt === 'SERVER') {
                    iconClass = 'bi-server';
                }
                devEl.innerHTML = `<i class="bi ${iconClass}"></i>`;

                // Search attribute
                const searchStr = `${dev.hostname} ${dev.ip_address} ${dev.asset_code} ${dev.custodian_name} ${dev.name} ${dept.name}`;
                devEl.setAttribute('data-search', searchStr);

                // Hover tooltip
                devEl.addEventListener('mouseenter', (e) => {
                    this.showTooltip(dev, dept, e);
                });
                devEl.addEventListener('mouseleave', () => {
                    this.hideTooltip();
                });

                // Click device: open side drawer
                devEl.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.openDrawer(dev, dept);
                });

                bodyEl.appendChild(devEl);
            });
        },

        updateHUD: function(filteredDepts) {
            let totalDevs = 0;
            let auditedDevs = 0;
            let onlineDevs = 0;
            let warningDevs = 0;

            filteredDepts.forEach(d => {
                totalDevs += (d.total_devices || 0);
                auditedDevs += (d.audited_devices || 0);
                onlineDevs += (d.online_devices || 0);
                (d.devices || []).forEach(item => {
                    if (item.is_mdes_warning) warningDevs++;
                });
            });

            const pct = totalDevs > 0 ? Math.round((auditedDevs / totalDevs) * 100) : 0;

            // HUD building title
            const bldObj = (this.data.buildings || []).find(b => b.id === this.currentBuilding);
            const bldTitleEl = document.getElementById('hudBuildingTitle');
            const bldSubEl = document.getElementById('hudBuildingSub');
            if (bldTitleEl) {
                bldTitleEl.textContent = bldObj ? bldObj.name : 'ภาพรวมทุกอาคาร';
            }
            if (bldSubEl) {
                const floorStr = this.currentFloor === 'all' ? 'ทุกชั้น' : `ชั้น ${this.currentFloor}`;
                bldSubEl.textContent = `โรงพยาบาลทุ่งหัวช้าง • ${floorStr}`;
            }

            // HUD Stats
            const statDepts = document.getElementById('hudStatDepts');
            const statDevs = document.getElementById('hudStatDevices');
            const statAudited = document.getElementById('hudStatAudited');
            const statOnline = document.getElementById('hudStatOnline');
            const hudScanCount = document.getElementById('hudScanCount');
            const btnHeaderScanCount = document.getElementById('btnHeaderScanCount');

            if (statDepts) statDepts.textContent = filteredDepts.length;
            if (statDevs) statDevs.textContent = totalDevs;
            if (statAudited) statAudited.textContent = `${pct}%`;
            if (statOnline) statOnline.textContent = onlineDevs;
            if (hudScanCount) hudScanCount.textContent = totalDevs;
            if (btnHeaderScanCount) btnHeaderScanCount.textContent = totalDevs;

            // Filter pills counts
            const cAll = document.getElementById('countFilterAll');
            const cAudited = document.getElementById('countFilterAudited');
            const cOnline = document.getElementById('countFilterOnline');
            const cPending = document.getElementById('countFilterPending');
            const cWarning = document.getElementById('countFilterWarning');

            if (cAll) cAll.textContent = totalDevs;
            if (cAudited) cAudited.textContent = auditedDevs;
            if (cOnline) cOnline.textContent = onlineDevs;
            if (cPending) cPending.textContent = Math.max(0, totalDevs - auditedDevs);
            if (cWarning) cWarning.textContent = warningDevs;
        },

        showTooltip: function(dev, dept, event) {
            const tt = document.getElementById('map3dTooltip');
            if (!tt) return;

            const auditBadge = dev.is_audited 
                ? '<span class="badge bg-success" style="font-size: 10px;">ตรวจแล้ว พ.ศ. {{ $fiscalYear }}</span>' 
                : '<span class="badge bg-warning text-dark" style="font-size: 10px;">รอตรวจ</span>';
            const onlineBadge = dev.is_online 
                ? '<span class="badge bg-info" style="font-size: 10px;">Agent ออนไลน์</span>' 
                : '<span class="badge bg-secondary" style="font-size: 10px;">ออฟไลน์</span>';
            const warningBadge = dev.is_mdes_warning 
                ? '<div style="margin-top: 4px; font-size: 10.5px; color: #fb923c;"><i class="bi bi-exclamation-triangle-fill"></i> อุปกรณ์นี้ตกเกณฑ์มาตรฐาน ICT</div>' 
                : '';

            tt.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <strong style="color: #38bdf8; font-size: 13px;">${dev.hostname || dev.name}</strong>
                    ${onlineBadge}
                </div>
                <div style="font-size: 11px; color: #94a3b8; margin-bottom: 6px;">
                    IP: <span style="color: #ffffff; font-family: monospace;">${dev.ip_address || '-'}</span> • แผนก: <span style="color: #cbd5e1;">${dept.name}</span>
                </div>
                <div style="font-size: 11px; color: #cbd5e1; border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 4px;">
                    <div>CPU: ${dev.cpu_model || '-'}</div>
                    <div>RAM: ${dev.ram_capacity || '-'} • Storage: ${dev.storage_type || '-'}</div>
                    <div>OS: ${dev.os_name || '-'}</div>
                </div>
                <div style="margin-top: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 10.5px; color: #94a3b8;">ผู้ครอง: ${dev.custodian_name || '-'}</span>
                    ${auditBadge}
                </div>
                ${warningBadge}
            `;

            tt.style.display = 'block';

            const wrapper = document.getElementById('map3dViewportWrapper');
            if (wrapper) {
                const rect = wrapper.getBoundingClientRect();
                const mouseX = event.clientX - rect.left;
                const mouseY = event.clientY - rect.top;
                tt.style.left = `${mouseX}px`;
                tt.style.top = `${mouseY}px`;
            }
        },

        hideTooltip: function() {
            const tt = document.getElementById('map3dTooltip');
            if (tt) tt.style.display = 'none';
        },

        openDrawer: function(dev, dept) {
            const drawer = document.getElementById('map3dDrawer');
            if (!drawer) return;

            this.selectedDeviceId = dev.id;

            const badge = document.getElementById('drawerBadge');
            const hostname = document.getElementById('drawerHostname');
            const ip = document.getElementById('drawerIp');
            const body = document.getElementById('drawerBody');
            const footer = document.getElementById('drawerFooter');

            if (badge) badge.textContent = dev.device_type || 'คอมพิวเตอร์';
            if (hostname) hostname.textContent = dev.hostname || dev.name || 'ไม่ระบุชื่อเครื่อง';
            if (ip) ip.textContent = `IP: ${dev.ip_address || '-'} • ครุภัณฑ์: ${dev.asset_code || '-'}`;

            const auditStateHtml = dev.is_audited 
                ? '<span class="badge bg-success" style="font-size: 11px;"><i class="bi bi-check-circle-fill me-1"></i> ตรวจนับสเปกแล้ว (ปีงบฯ {{ $fiscalYear }})</span>' 
                : '<span class="badge bg-warning text-dark" style="font-size: 11px;"><i class="bi bi-clock-history me-1"></i> ยังไม่ได้รับการตรวจนับ</span>';

            const onlineStateHtml = dev.is_online 
                ? '<span class="badge bg-info" style="font-size: 11px;"><i class="bi bi-broadcast me-1"></i> Agent กำลังทำงาน (ออนไลน์)</span>' 
                : '<span class="badge bg-secondary" style="font-size: 11px;"><i class="bi bi-moon me-1"></i> ปิดเครื่อง / ออฟไลน์</span>';

            const warningBox = dev.is_mdes_warning 
                ? `<div style="background: rgba(234, 88, 12, 0.15); border: 1px solid #ea580c; border-radius: 8px; padding: 10px; margin-bottom: 14px; font-size: 12px; color: #fdba74;">
                    <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i>
                    <strong>คำเตือนมาตรฐาน ICT:</strong> สเปกเครื่องนี้ต่ำกว่าเกณฑ์กระทรวง (RAM ต่ำกว่า 8GB หรือใช้ Hard Disk จานหมุน หรือ Windows 7)
                   </div>`
                : '';

            if (body) {
                body.innerHTML = `
                    ${warningBox}
                    <div style="margin-bottom: 14px; display: flex; gap: 6px; flex-wrap: wrap;">
                        ${auditStateHtml}
                        ${onlineStateHtml}
                    </div>

                    <div style="font-size: 12px; font-weight: 700; color: #38bdf8; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                        รายละเอียดสเปกฮาร์ดแวร์
                    </div>
                    <table style="width: 100%; font-size: 12px; color: #cbd5e1; margin-bottom: 16px;">
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8; width: 35%;">CPU:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #f8fafc;">${dev.cpu_model || '-'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">RAM:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #f8fafc;">${dev.ram_capacity || '-'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">Storage:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #f8fafc;">${dev.storage_type || '-'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">OS:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #f8fafc;">${dev.os_name || '-'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">Agent Ver:</td>
                            <td style="padding: 6px 0; font-family: monospace; color: #38bdf8;">${dev.client_version || 'v2.11.0'}</td>
                        </tr>
                    </table>

                    <div style="font-size: 12px; font-weight: 700; color: #38bdf8; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">
                        ข้อมูลการครอบครองและตำแหน่ง
                    </div>
                    <table style="width: 100%; font-size: 12px; color: #cbd5e1; margin-bottom: 16px;">
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8; width: 35%;">แผนก/หน่วยงาน:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #f8fafc;">${dept.name} (ชั้น ${dept.floor || 1})</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">ผู้ครอบครอง:</td>
                            <td style="padding: 6px 0; font-weight: 600; color: #f8fafc;">${dev.custodian_name || '-'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">จุดติดตั้ง:</td>
                            <td style="padding: 6px 0; color: #f8fafc;">${dev.location_detail || '-'}</td>
                        </tr>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 6px 0; color: #94a3b8;">เลขครุภัณฑ์:</td>
                            <td style="padding: 6px 0; font-family: monospace; color: #fbbf24;">${dev.asset_code || '-'}</td>
                        </tr>
                    </table>
                `;
            }

            if (footer) {
                footer.style.padding = '14px 20px';
                footer.style.borderTop = '1px solid rgba(255,255,255,0.08)';
                footer.style.background = 'rgba(30, 41, 59, 0.5)';
                footer.style.display = 'flex';
                footer.style.flexDirection = 'column';
                footer.style.gap = '8px';

                footer.innerHTML = `
                    <button type="button" class="btn btn-primary btn-sm" onclick="THC_3D.scanSingleDevice('${dev.hostname}', '${dev.hardware_id || ''}', ${dev.id})" style="border-radius: 8px; font-weight: 700; background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%); border: none;">
                        <i class="bi bi-cloud-arrow-down-fill me-1"></i> ส่งคำสั่งดึงสเปกเครื่องนี้ทันที
                    </button>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ url('/assets') }}/${dev.id}" target="_blank" class="btn btn-outline-light btn-sm w-100" style="border-radius: 8px; font-size: 11.5px;">
                            <i class="bi bi-pencil-square me-1"></i> เปิดดูข้อมูลครุภัณฑ์
                        </a>
                    </div>
                `;
            }

            drawer.style.display = 'flex';

            // Pulse node on stage
            const nodeEl = document.getElementById(`devNode_${dev.id}`);
            if (nodeEl) {
                nodeEl.style.boxShadow = '0 0 20px #38bdf8';
                setTimeout(() => {
                    nodeEl.style.boxShadow = '';
                }, 3000);
            }
        },

        closeDrawer: function() {
            const drawer = document.getElementById('map3dDrawer');
            if (drawer) drawer.style.display = 'none';
            this.selectedDeviceId = null;
        },

        scanSingleDevice: function(hostname, hwid, assetId) {
            if (!hostname && !hwid) {
                alert('เครื่องนี้ไม่มี Hostname หรือ Hardware ID จึงไม่สามารถส่งคำสั่งสแกนได้');
                return;
            }
            executeFleetScanBatch([{
                hostname: hostname,
                hardware_id: hwid,
                asset_id: assetId
            }]);
        },

        scanCurrentView: function() {
            // Collect all devices currently displayed
            const visibleRooms = document.querySelectorAll('.ha-3d-room');
            const targets = [];
            const seen = new Set();

            visibleRooms.forEach(room => {
                const devs = room.querySelectorAll('.ha-3d-device');
                devs.forEach(dev => {
                    if (dev.style.opacity === '0.2' || dev.style.opacity === '0.15') return; // filtered out
                    const devId = dev.getAttribute('data-dev-id');
                    // Find in data
                    this.data.departments.forEach(dept => {
                        (dept.devices || []).forEach(d => {
                            if (String(d.id) === String(devId) && (d.hostname || d.hardware_id)) {
                                const key = (d.hostname || '') + '_' + (d.hardware_id || '');
                                if (!seen.has(key)) {
                                    seen.add(key);
                                    targets.push({
                                        hostname: d.hostname,
                                        hardware_id: d.hardware_id,
                                        asset_id: d.id
                                    });
                                }
                            }
                        });
                    });
                });
            });

            if (targets.length === 0) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'ไม่พบคอมพิวเตอร์ในมุมมองนี้',
                        text: 'กรุณาเลือกอาคารหรือแผนกที่มีเครื่องคอมพิวเตอร์ติดตั้งอยู่',
                        confirmButtonColor: '#0f766e'
                    });
                } else {
                    alert('ไม่พบคอมพิวเตอร์ในมุมมองนี้');
                }
                return;
            }

            if (window.Swal) {
                Swal.fire({
                    title: `สั่งสแกน ${targets.length} เครื่องในมุมมองนี้?`,
                    text: 'ระบบจะส่งคำสั่งไปยัง Agent ของคอมพิวเตอร์ที่แสดงบนแผนผัง เพื่อดึงสเปกฮาร์ดแวร์ล่าสุด',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ยืนยันสั่งสแกน',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#0f766e'
                }).then(result => {
                    if (result.isConfirmed) {
                        executeFleetScanBatch(targets);
                    }
                });
            } else {
                if (confirm(`สั่งสแกน ${targets.length} เครื่องในมุมมองนี้?`)) {
                    executeFleetScanBatch(targets);
                }
            }
        },

        updateCurrentView: function() {
            triggerUpdateAllOutdatedFleet();
        },

        focusDepartment: function(deptId) {
            const roomEl = document.getElementById(`map3dRoom_${deptId}`);
            if (!roomEl) return;

            // Highlight room
            document.querySelectorAll('.ha-3d-room').forEach(r => r.classList.remove('focused'));
            roomEl.classList.add('focused');

            // Pan to center of this room
            const roomLeft = parseFloat(roomEl.style.left) || 0;
            const roomTop = parseFloat(roomEl.style.top) || 0;

            this.panX = 700 - (roomLeft + 120);
            this.panY = 500 - (roomTop + 80);
            this.zoom = 120;
            this.updateCamera();

            this.showBeacon(roomEl);
        },

        showBeacon: function(roomEl, targetNode) {
            const beaconLayer = document.getElementById('map3dBeaconLayer');
            if (!beaconLayer) return;

            const roomLeft = parseFloat(roomEl.style.left) || 0;
            const roomTop = parseFloat(roomEl.style.top) || 0;

            let bx = roomLeft + 120;
            let by = roomTop + 80;

            if (targetNode) {
                const nodeLeft = parseFloat(targetNode.style.left) || 0;
                const nodeTop = parseFloat(targetNode.style.top) || 0;
                bx = roomLeft + nodeLeft + 15;
                by = roomTop + nodeTop + 45;
            }

            beaconLayer.innerHTML = `
                <div style="position: absolute; left: ${bx}px; top: ${by}px; width: 0; height: 0; pointer-events: none; transform: translateZ(25px);">
                    <div style="position: absolute; width: 60px; height: 60px; margin-left: -30px; margin-top: -30px; border-radius: 50%; border: 3px solid #f59e0b; animation: radarPing 1.8s infinite; box-shadow: 0 0 15px #f59e0b;"></div>
                    <div style="position: absolute; width: 120px; height: 120px; margin-left: -60px; margin-top: -60px; border-radius: 50%; border: 2px solid rgba(245, 158, 11, 0.6); animation: radarPing 1.8s infinite 0.6s;"></div>
                </div>
            `;
        },

        toggleDesignMode: function() {
            this.designMode = !this.designMode;
            const banner = document.getElementById('map3dDesignModeBanner');
            const btn = document.getElementById('btnToggleDesignMode');

            if (this.designMode) {
                if (banner) banner.style.display = 'flex';
                if (btn) btn.classList.add('active');
                document.querySelectorAll('.ha-3d-room').forEach(r => {
                    r.style.cursor = 'move';
                    r.style.borderColor = '#f59e0b';
                });
            } else {
                if (banner) banner.style.display = 'none';
                if (btn) btn.classList.remove('active');
                document.querySelectorAll('.ha-3d-room').forEach(r => {
                    r.style.cursor = 'pointer';
                    r.style.borderColor = '';
                });
            }
        },

        saveLayout: function() {
            try {
                localStorage.setItem('thc_floorplan_layout_custom', JSON.stringify(this.customLayout));
                if (window.Swal) {
                    Swal.fire({
                        icon: 'success',
                        title: 'บันทึกตำแหน่งผังเรียบร้อยแล้ว',
                        text: 'ตำแหน่งจัดวางห้องและแผนกจำลอง 3 มิติถูกบันทึกไว้ในเบราว์เซอร์แล้ว',
                        confirmButtonColor: '#0f766e'
                    });
                } else {
                    alert('บันทึกตำแหน่งผังเรียบร้อยแล้ว');
                }
            } catch (e) {
                alert('เกิดข้อผิดพลาดในการบันทึกผัง');
            }
        },

        resetLayoutDefaults: function() {
            try {
                localStorage.removeItem('thc_floorplan_layout_custom');
                this.customLayout = {};
                this.render();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'info',
                        title: 'คืนค่าเริ่มต้นสำเร็จ',
                        text: 'รีเซ็ตผังกลับสู่ตำแหน่งมาตรฐานเรียบร้อย',
                        confirmButtonColor: '#0f766e'
                    });
                }
            } catch (e) {}
        },

        toggleFullscreen: function() {
            const wrapper = document.getElementById('map3dViewportWrapper');
            if (!wrapper) return;

            if (!document.fullscreenElement) {
                if (wrapper.requestFullscreen) {
                    wrapper.requestFullscreen();
                } else if (wrapper.webkitRequestFullscreen) {
                    wrapper.webkitRequestFullscreen();
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        pollLiveAgentOnlineStatus();
        setInterval(pollLiveAgentOnlineStatus, 8000);

        if (window.map3dData && document.getElementById('map3dStage')) {
            THC_3D.init(window.map3dData);
        }
    });
</script>
@endsection
