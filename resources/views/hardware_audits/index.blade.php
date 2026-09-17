@extends('layouts.app')

@section('title', 'ติดตามสเปค & ตรวจนับครุภัณฑ์คอมพิวเตอร์ประจำปีงบประมาณ')
@section('page_title', 'ติดตามสเปค & ตรวจนับคอมพิวเตอร์ประจำปีงบประมาณ')
@section('page_subtitle', 'ศูนย์มอนิเตอร์ฮาร์ดแวร์ ตรวจจับสเปคอัตโนมัติจาก Agent และประเมินมาตรฐาน ICT ภาครัฐ พ.ศ. ' . $fiscalYear)

@section('topbar-actions')
<div style="display: flex; gap: 8px; align-items: center;">
    <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" class="topbar-btn" title="พิมพ์รายงานสรุปประจำปีงบประมาณ A4">
        <i class="bi bi-printer text-primary"></i>
        <span>พิมพ์รายงาน A4</span>
    </a>
    <a href="{{ route('hardware-audits.export-csv', ['fiscal_year' => $fiscalYear]) }}" class="topbar-btn" title="ส่งออกไฟล์ข้อมูล Excel/CSV">
        <i class="bi bi-file-earmark-excel text-success"></i>
        <span>ส่งออก CSV</span>
    </a>
</div>
@endsection

@section('content')
<div class="content-container">

    <!-- PAGE HEADER BANNER -->
    <div class="card" style="background: linear-gradient(135deg, #0f766e 0%, #0369a1 100%); border: none; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 10px 25px -5px rgba(15, 118, 110, 0.35); color: #ffffff; overflow: hidden; position: relative;">
        <!-- Background Pattern Deco -->
        <div style="position: absolute; right: -20px; top: -30px; width: 220px; height: 220px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%); pointer-events: none;"></div>
        <div style="position: absolute; right: 140px; bottom: -40px; width: 160px; height: 160px; border-radius: 50%; background: radial-gradient(circle, rgba(56,189,248,0.15) 0%, transparent 70%); pointer-events: none;"></div>

        <div style="padding: 24px 28px; position: relative; z-index: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <h1 style="font-size: 22px; font-weight: 800; margin: 0; letter-spacing: -0.3px; color: #ffffff;">ติดตามสเปค & ตรวจนับคอมพิวเตอร์ประจำปีงบประมาณ</h1>
                            <span style="background: rgba(255,255,255,0.22); border: 1px solid rgba(255,255,255,0.3); font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 999px;">
                                พ.ศ. {{ $fiscalYear }}
                            </span>
                        </div>
                        <p style="font-size: 13.5px; opacity: 0.92; margin: 5px 0 0 0; font-weight: 400;">
                            ศูนย์มอนิเตอร์ฮาร์ดแวร์อัตโนมัติ ตรวจสอบความถูกต้องของสเปคเครื่องจริงจาก Agent ประจำเครื่อง และประเมินความสอดคล้องตามเกณฑ์กระทรวงดีอี (MDES)
                        </p>
                    </div>
                </div>

                <!-- Fiscal Year Form Selector -->
                <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 12px; padding: 8px 14px; display: flex; align-items: center; gap: 10px;">
                    <form method="GET" action="{{ route('hardware-audits.index') }}" id="fiscalYearForm" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        <i class="bi bi-calendar-check" style="font-size: 16px; opacity: 0.9;"></i>
                        <label for="fiscal_year" style="font-size: 13px; font-weight: 600; white-space: nowrap; margin: 0;">ปีงบประมาณ:</label>
                        <select name="fiscal_year" id="fiscal_year" class="form-select form-select-sm" onchange="document.getElementById('fiscalYearForm').submit()" 
                                style="font-weight: 700; width: 120px; border-radius: 8px; border: none; background: #ffffff; color: #0f766e; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            @foreach($availableFiscalYears as $fy)
                                <option value="{{ $fy }}" {{ $fiscalYear == $fy ? 'selected' : '' }}>พ.ศ. {{ $fy }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 TOP KPI STATISTICS CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        
        <!-- CARD 1: Total Fleet -->
        <div class="card stat-card" style="border-radius: 14px; border: 1px solid #e2e8f0; padding: 18px; margin-bottom: 0; background: #ffffff; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 12.5px; font-weight: 600; color: #64748b;">เป้าหมายสำรวจทั้งหมด</div>
                    <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px; line-height: 1;">
                        {{ number_format($totalFleet) }} <span style="font-size: 13px; font-weight: 500; color: #64748b;">เครื่อง</span>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-pc-display"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 10px; display: flex; align-items: center; gap: 4px;">
                <i class="bi bi-building me-1"></i> คอมพิวเตอร์สถานะปกติใน รพ.
            </div>
        </div>

        <!-- CARD 2: Audited This FY -->
        <div class="card stat-card" style="border-radius: 14px; border: 1px solid #bbf7d0; padding: 18px; margin-bottom: 0; background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 12.5px; font-weight: 600; color: #166534;">ตรวจนับ/อัปเดตสเปคแล้ว</div>
                    <div style="font-size: 26px; font-weight: 800; color: #15803d; margin-top: 4px; line-height: 1;">
                        {{ number_format($auditedCount) }} 
                        <span style="font-size: 13px; font-weight: 700; color: #166534;">({{ $auditedPercentage }}%)</span>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
            <div style="margin-top: 10px;">
                <div class="progress" style="height: 6px; background: #d1fae5; border-radius: 99px; overflow: hidden;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $auditedPercentage) }}%; border-radius: 99px;"></div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Pending Submissions -->
        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending', 'status' => 'pending']) }}" style="text-decoration: none; color: inherit;">
            <div class="card stat-card" style="border-radius: 14px; border: 1px solid {{ $pendingCount > 0 ? '#fde047' : '#e2e8f0' }}; padding: 18px; margin-bottom: 0; background: {{ $pendingCount > 0 ? 'linear-gradient(180deg, #ffffff 0%, #fefce8 100%)' : '#ffffff' }}; box-shadow: var(--shadow-sm); cursor: pointer; transition: transform 0.2s ease;">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 12.5px; font-weight: 600; color: #854d0e;">รอแอดมินกดอนุมัติ</div>
                        <div style="font-size: 26px; font-weight: 800; color: #ca8a04; margin-top: 4px; line-height: 1;">
                            {{ number_format($pendingCount) }} <span style="font-size: 13px; font-weight: 500; color: #854d0e;">รายการ</span>
                        </div>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef9c3; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div style="font-size: 11.5px; color: #a16207; margin-top: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <span>สแกนเข้ามาแล้ว รอยืนยัน</span>
                    <i class="bi bi-arrow-right-short" style="font-size: 16px;"></i>
                </div>
            </div>
        </a>

        <!-- CARD 4: Remaining Not Audited -->
        <div class="card stat-card" style="border-radius: 14px; border: 1px solid #e2e8f0; padding: 18px; margin-bottom: 0; background: #ffffff; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 12.5px; font-weight: 600; color: #64748b;">คงเหลือยังไม่ได้สำรวจ</div>
                    <div style="font-size: 26px; font-weight: 800; color: #334155; margin-top: 4px; line-height: 1;">
                        {{ number_format($notAuditedCount) }} <span style="font-size: 13px; font-weight: 500; color: #64748b;">เครื่อง</span>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 10px; display: flex; align-items: center; gap: 4px;">
                <i class="bi bi-tools me-1"></i> ต้องส่ง Agent หรือเดินสำรวจ
            </div>
        </div>

        <!-- CARD 5: MDES Upgrade Warnings -->
        <div class="card stat-card" style="border-radius: 14px; border: 1px solid #fecaca; padding: 18px; margin-bottom: 0; background: linear-gradient(180deg, #ffffff 0%, #fff5f5 100%); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 12.5px; font-weight: 600; color: #991b1b;">เตือนสเปคต่ำกว่าเกณฑ์ ICT</div>
                    <div style="font-size: 26px; font-weight: 800; color: #dc2626; margin-top: 4px; line-height: 1;">
                        {{ number_format($mdesWarningCount) }} <span style="font-size: 13px; font-weight: 500; color: #991b1b;">เครื่อง</span>
                    </div>
                </div>
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #ef4444; margin-top: 10px; display: flex; align-items: center; gap: 4px;">
                <i class="bi bi-info-circle me-1"></i> RAM &lt; 8GB / ไดรฟ์ HDD / Win 7
            </div>
        </div>
    </div>

    <!-- MAIN INTERACTIVE SECTION WITH MODERN TABS -->
    <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        
        <!-- MODERN TAB BAR -->
        <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc; overflow-x: auto; padding: 6px 12px 0 12px; gap: 6px;">
            {{-- TAB 1: PENDING QUEUE --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending']) }}" 
               class="tab-nav-btn {{ $tab === 'pending' ? 'active' : '' }}"
               style="padding: 12px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-radius: 10px 10px 0 0; border: 1px solid {{ $tab === 'pending' ? '#e2e8f0' : 'transparent' }}; border-bottom: {{ $tab === 'pending' ? '2px solid #ffffff' : 'none' }}; margin-bottom: -1px; background: {{ $tab === 'pending' ? '#ffffff' : 'transparent' }}; color: {{ $tab === 'pending' ? '#0f766e' : '#64748b' }}; transition: all 0.15s;">
                <i class="bi bi-inbox-fill" style="color: {{ $tab === 'pending' ? '#0f766e' : '#94a3b8' }};"></i>
                <span>คิวรอตรวจสอบและอนุมัติ</span>
                @if($pendingCount > 0)
                    <span style="background: #f59e0b; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 99px; font-weight: 700;">{{ $pendingCount }}</span>
                @endif
            </a>

            {{-- TAB 2: DEPARTMENT TRACKER --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'departments']) }}" 
               class="tab-nav-btn {{ $tab === 'departments' ? 'active' : '' }}"
               style="padding: 12px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-radius: 10px 10px 0 0; border: 1px solid {{ $tab === 'departments' ? '#e2e8f0' : 'transparent' }}; border-bottom: {{ $tab === 'departments' ? '2px solid #ffffff' : 'none' }}; margin-bottom: -1px; background: {{ $tab === 'departments' ? '#ffffff' : 'transparent' }}; color: {{ $tab === 'departments' ? '#0f766e' : '#64748b' }}; transition: all 0.15s;">
                <i class="bi bi-diagram-3-fill" style="color: {{ $tab === 'departments' ? '#0f766e' : '#94a3b8' }};"></i>
                <span>ติดตามความคืบหน้ารายแผนก</span>
                <span class="badge bg-light text-dark border" style="font-size: 11px;">{{ count($departments) }} แผนก</span>
            </a>

            {{-- TAB 3: ANNUAL REPORT & CHARTS --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'report']) }}" 
               class="tab-nav-btn {{ $tab === 'report' ? 'active' : '' }}"
               style="padding: 12px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-radius: 10px 10px 0 0; border: 1px solid {{ $tab === 'report' ? '#e2e8f0' : 'transparent' }}; border-bottom: {{ $tab === 'report' ? '2px solid #ffffff' : 'none' }}; margin-bottom: -1px; background: {{ $tab === 'report' ? '#ffffff' : 'transparent' }}; color: {{ $tab === 'report' ? '#0f766e' : '#64748b' }}; transition: all 0.15s;">
                <i class="bi bi-bar-chart-line-fill" style="color: {{ $tab === 'report' ? '#0f766e' : '#94a3b8' }};"></i>
                <span>รายงานสถิติ & วิเคราะห์สเปค (Charts)</span>
            </a>

            {{-- TAB 4: AGENT DEPLOYMENT HUB --}}
            <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'deploy']) }}" 
               class="tab-nav-btn {{ $tab === 'deploy' ? 'active' : '' }}"
               style="padding: 12px 18px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-radius: 10px 10px 0 0; border: 1px solid {{ $tab === 'deploy' ? '#e2e8f0' : 'transparent' }}; border-bottom: {{ $tab === 'deploy' ? '2px solid #ffffff' : 'none' }}; margin-bottom: -1px; background: {{ $tab === 'deploy' ? '#ffffff' : 'transparent' }}; color: {{ $tab === 'deploy' ? '#0f766e' : '#64748b' }}; transition: all 0.15s;">
                <i class="bi bi-terminal-fill" style="color: {{ $tab === 'deploy' ? '#0f766e' : '#94a3b8' }};"></i>
                <span>ตัวติดตั้ง Agent & การกระจายสคริปต์</span>
            </a>
        </div>

        <!-- TAB CONTENT 1: PENDING QUEUE -->
        @if($tab === 'pending')
        <div style="padding: 22px;">
            
            <!-- Filter & Batch Action Toolbar -->
            <form method="GET" action="{{ route('hardware-audits.index') }}" style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                <input type="hidden" name="fiscal_year" value="{{ $fiscalYear }}">
                <input type="hidden" name="tab" value="pending">
                
                <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                    <div style="position: relative; width: 280px;">
                        <i class="bi bi-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 13px;"></i>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา Hostname, S/N, IP หรือ CPU..." 
                               value="{{ request('search') }}" style="padding-left: 32px; border-radius: 8px; height: 36px;">
                    </div>

                    <select name="status" class="form-select form-select-sm" style="width: 150px; border-radius: 8px; height: 36px;">
                        <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>⏳ รอตรวจสอบ ({{ $pendingCount }})</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✓ อนุมัติแล้ว ({{ $approvedCount }})</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>✕ ปฏิเสธแล้ว ({{ $rejectedCount }})</option>
                        <option value="" {{ request('status') === '' ? 'selected' : '' }}>📋 ทุกสถานะ</option>
                    </select>

                    <button type="submit" class="btn btn-primary btn-sm" style="height: 36px; padding: 0 16px; border-radius: 8px;">
                        <i class="bi bi-funnel"></i> คัดกรอง
                    </button>

                    @if(request('search') || (request('status') && request('status') !== 'pending'))
                        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending']) }}" class="btn btn-outline-secondary btn-sm" style="height: 36px; border-radius: 8px; display: inline-flex; align-items: center;">
                            <i class="bi bi-x-circle me-1"></i> ล้างตัวกรอง
                        </a>
                    @endif
                </div>

                {{-- Batch Approval Trigger --}}
                @if(request('status', 'pending') === 'pending' && $audits->count() > 0)
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" class="btn btn-success btn-sm" onclick="submitBatchApproval()" style="height: 36px; padding: 0 16px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);">
                        <i class="bi bi-check2-all" style="font-size: 16px;"></i> 
                        <span>อนุมัติรายการที่เลือกเป็นกลุ่ม (Batch)</span>
                    </button>
                </div>
                @endif
            </form>

            <!-- Table of Submissions -->
            <form id="batchApproveForm" method="POST" action="{{ route('hardware-audits.batch-approve') }}">
                @csrf
                <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
                            <tr>
                                <th style="width: 40px; text-align: center;">
                                    <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)" style="cursor: pointer;">
                                </th>
                                <th style="width: 115px;">วันที่ส่งผลสแกน</th>
                                <th>ชื่อเครื่อง (Hostname / Network)</th>
                                <th style="width: 175px;">ยี่ห้อ / รุ่น (Brand & Model)</th>
                                <th style="width: 140px;">Serial Number</th>
                                <th>ครุภัณฑ์ที่จับคู่ (Matched Asset)</th>
                                <th>สเปคที่ตรวจพบจาก Agent (Hardware Telemetry)</th>
                                <th style="text-align: center; width: 110px;">การเปลี่ยนแปลง</th>
                                <th style="text-align: center; width: 95px;">สถานะ</th>
                                <th style="text-align: right; width: 230px;">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audits as $audit)
                            @php
                                $isUnderMdes = $audit->isUnderMdesStandard();
                            @endphp
                            <tr style="{{ $audit->status === 'pending' ? 'background: #ffffff;' : 'background: #fbfcfe;' }}">
                                <td style="text-align: center;">
                                    @if($audit->status === 'pending')
                                        <input type="checkbox" name="audit_ids[]" value="{{ $audit->id }}" class="audit-select-chk" style="cursor: pointer;">
                                    @else
                                        <i class="bi bi-dash text-muted"></i>
                                    @endif
                                </td>
                                <td style="color: #64748b; font-size: 12px; white-space: nowrap;">
                                    <div style="font-weight: 600; color: #334155;">{{ $audit->created_at ? $audit->created_at->format('d/m/Y') : '-' }}</div>
                                    <div style="font-size: 11px; color: #94a3b8;">{{ $audit->created_at ? $audit->created_at->format('H:i น.') : '' }}</div>
                                </td>
                                <td>
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
                                <td>
                                    @if($audit->brand || $audit->model)
                                        <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 11px; font-weight: 700;">
                                                <i class="bi bi-laptop me-1"></i>{{ $audit->brand ?: 'คอมพิวเตอร์' }}
                                            </span>
                                            <span style="font-size: 12px; font-weight: 600; color: #1e293b;">{{ $audit->model }}</span>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; margin-top: 3px;">
                                            ประเภท: <strong style="color: #0f766e;">{{ strtoupper($audit->device_type_code ?? 'PC') }}</strong>
                                        </div>
                                    @elseif($audit->asset && ($audit->asset->brand || $audit->asset->model))
                                        <div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                                            <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 11px;">
                                                {{ $audit->asset->brand }}
                                            </span>
                                            <span style="font-size: 12px; font-weight: 600;">{{ $audit->asset->model }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted" style="font-size: 12px;">(ไม่ระบุยี่ห้อ/รุ่น)</span>
                                    @endif
                                </td>

                                <td>
                                    <code style="font-size: 11.5px; color: #0f766e; background: #f0fdfa; border: 1px solid #ccfbf1; padding: 3px 6px; border-radius: 6px; font-weight: 700; display: inline-block;">
                                        {{ $audit->serial_number ?: '(ไม่พบ S/N)' }}
                                    </code>
                                </td>

                                <td>
                                    @if($audit->asset)
                                        <div>
                                            <a href="{{ route('assets.show', $audit->asset_id) }}" target="_blank" style="font-weight: 700; color: #0f766e; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                <span>{{ $audit->asset->asset_code }}</span>
                                                <i class="bi bi-box-arrow-up-right" style="font-size: 10px;"></i>
                                            </a>
                                        </div>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $audit->asset->department?->name ?? 'ไม่ระบุแผนก' }}
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 11px;">
                                            <i class="bi bi-asterisk me-1"></i>เครื่องใหม่ / ยังไม่จับคู่
                                        </span>
                                    @endif
                                </td>

                                {{-- Scanned Specs Formatted --}}
                                <td>
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
                                    </div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-windows text-info"></i> <span>{{ $audit->os_name }}</span>
                                    </div>
                                </td>

                                {{-- Diff Status --}}
                                <td style="text-align: center;">
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

                                <td style="text-align: center;">
                                    {!! $audit->status_badge !!}
                                </td>

                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 5px; align-items: center;">
                                        
                                        <!-- BUTTON 1: Open Agent Inspector Modal -->
                                        <button type="button" class="btn btn-sm" onclick="openAgentInspectionModal({{ $audit->id }})" 
                                                title="เปิดดูผลการตรวจเช็คสเปคฮาร์ดแวร์เชิงลึก และผลตรวจเกณฑ์มาตรฐาน ICT" 
                                                style="background: #0284c7; color: #ffffff; font-size: 11.5px; padding: 5px 10px; border-radius: 7px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 4px rgba(2,132,199,0.2);">
                                            <i class="bi bi-cpu-fill"></i>
                                            <span>ตรวจเช็ค Agent</span>
                                        </button>

                                        <!-- BUTTON 2: Side-by-Side Diff Comparison Modal -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="openAuditDiffModal({{ json_encode($audit) }}, {{ json_encode($audit->asset) }})" 
                                                title="เปรียบเทียบสเปคเดิม vs สเปคใหม่ (Diff)" style="font-size: 11.5px; padding: 5px 8px; border-radius: 7px;">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>

                                        <!-- ACTION BUTTONS: Approve / Reject (Only for pending) -->
                                        @if($audit->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" onclick="approveSingleAudit({{ $audit->id }}, '{{ $audit->hostname }}')" 
                                                    title="อนุมัติและอัปเดตสเปคลงครุภัณฑ์ทันที" style="font-size: 11.5px; padding: 5px 9px; border-radius: 7px;">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectSingleAudit({{ $audit->id }}, '{{ $audit->hostname }}')" 
                                                    title="ปฏิเสธรายการนี้" style="font-size: 11.5px; padding: 5px 9px; border-radius: 7px;">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
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

        <!-- TAB CONTENT 2: DEPARTMENT TRACKER -->
        @if($tab === 'departments')
        <div style="padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">
                        <i class="bi bi-diagram-3-fill text-primary me-1"></i> ติดตามความคืบหน้าการตรวจนับแยกตามแผนก/หน่วยงาน
                    </h3>
                    <p style="font-size: 12.5px; color: #64748b; margin: 3px 0 0 0;">
                        ปีงบประมาณ พ.ศ. {{ $fiscalYear }} &bull; เป้าหมายการตรวจนับครุภัณฑ์คอมพิวเตอร์ให้ครบ 100% ทั่วทั้งโรงพยาบาล
                    </p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <input type="text" id="deptFilterInput" onkeyup="filterDeptCards()" placeholder="ค้นหาชื่อแผนก..." class="form-control form-control-sm" style="width: 220px; border-radius: 8px;">
                </div>
            </div>

            <div id="deptCardsGrid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px;">
                @foreach($departments as $dept)
                <div class="dept-card" data-name="{{ strtolower($dept->name) }}" style="background: #ffffff; border: 1px solid {{ $dept->percentage == 100 ? '#86efac' : ($dept->percentage > 0 ? '#cbd5e1' : '#e2e8f0') }}; border-radius: 14px; padding: 18px; box-shadow: var(--shadow-sm); transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: {{ $dept->percentage == 100 ? '#dcfce7' : '#e0f2fe' }}; color: {{ $dept->percentage == 100 ? '#16a34a' : '#0284c7' }}; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                <i class="bi {{ $dept->percentage == 100 ? 'bi-check-circle-fill' : 'bi-building' }}"></i>
                            </div>
                            <div>
                                <strong style="color: #0f172a; font-size: 14px; display: block;">{{ $dept->name }}</strong>
                                <span style="font-size: 11.5px; color: #64748b;">รหัสแผนก: THC-D{{ $dept->id }}</span>
                            </div>
                        </div>
                        <span style="font-size: 13px; font-weight: 800; color: {{ $dept->percentage == 100 ? '#16a34a' : ($dept->percentage > 0 ? '#0284c7' : '#94a3b8') }}; background: {{ $dept->percentage == 100 ? '#f0fdf4' : '#f8fafc' }}; border: 1px solid {{ $dept->percentage == 100 ? '#bbf7d0' : '#e2e8f0' }}; padding: 3px 9px; border-radius: 8px;">
                            {{ $dept->percentage }}%
                        </span>
                    </div>
                    
                    <div class="progress mb-2" style="height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden;">
                        <div class="progress-bar {{ $dept->percentage == 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $dept->percentage }}%; border-radius: 99px;"></div>
                    </div>

                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 10px; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                        <span>สำรวจแล้ว: <strong style="color: #166534;">{{ $dept->audited_assets }}</strong> / {{ $dept->total_assets }} เครื่อง</span>
                        <span>คงเหลือ: <strong style="color: {{ $dept->pending_assets > 0 ? '#dc2626' : '#166534' }};">{{ $dept->pending_assets }}</strong> เครื่อง</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- TAB CONTENT 3: ANNUAL REPORT ANALYTICS & CHARTS -->
        @if($tab === 'report')
        <div style="padding: 22px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: #0f172a;">สรุปภาพรวมและวิเคราะห์สเปคคอมพิวเตอร์ประจำปีงบประมาณ พ.ศ. {{ $fiscalYear }}</h3>
                    <div style="font-size: 13px; color: #64748b; margin-top: 2px;">
                        ข้อมูลวิเคราะห์เชิงลึกสำหรับประกอบการวางแผนจัดซื้อ จัดหาทดแทน และปรับปรุงฮาร์ดแวร์ โรงพยาบาลทุ่งหัวช้าง
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px;">
                        <i class="bi bi-printer"></i> พิมพ์รายงานสรุป A4 ฉบับทางการ
                    </a>
                </div>
            </div>

            <!-- Executive Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; border-left: 4px solid #0d9488; box-shadow: var(--shadow-sm);">
                    <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">ความคืบหน้าการตรวจนับภาพรวม</div>
                    <div style="font-size: 22px; font-weight: 800; color: #0f766e; margin-top: 4px;">{{ $auditedCount }} / {{ $totalFleet }} เครื่อง ({{ $auditedPercentage }}%)</div>
                    <div style="font-size: 11.5px; color: #94a3b8; margin-top: 4px;">เสร็จสิ้นตามเกณฑ์ประจำปี</div>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; border-left: 4px solid #ef4444; box-shadow: var(--shadow-sm);">
                    <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">เครื่องที่ควรเสนอของบปรับปรุง (MDES)</div>
                    <div style="font-size: 22px; font-weight: 800; color: #dc2626; margin-top: 4px;">{{ $mdesWarningCount }} เครื่อง</div>
                    <div style="font-size: 11.5px; color: #ef4444; margin-top: 4px;">RAM &lt; 8GB / ใช้ฮาร์ดดิสก์จานหมุน</div>
                </div>

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; border-left: 4px solid #f59e0b; box-shadow: var(--shadow-sm);">
                    <div style="font-size: 12.5px; color: #64748b; font-weight: 600;">อายุการใช้งานเกิน 5 ปี (ควรจำหน่าย/ทดแทน)</div>
                    <div style="font-size: 22px; font-weight: 800; color: #d97706; margin-top: 4px;">{{ $agedOver5YearsCount }} เครื่อง</div>
                    <div style="font-size: 11.5px; color: #b45309; margin-top: 4px;">อิงตามวันที่จัดซื้อในระบบคลัง</div>
                </div>
            </div>

            <!-- CHARTS GRID -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; margin-bottom: 24px;">
                
                {{-- Chart 1: RAM Distribution --}}
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
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
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
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
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-shadow: var(--shadow-sm);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
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

                <!-- 3 Steps Visual Flow -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 20px 0 24px 0;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #0f766e; color: #ffffff; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto;">1</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">ดาวน์โหลดหรือสั่งรัน</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">เลือกไฟล์ .bat หรือคำสั่ง PowerShell One-Liner</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #0284c7; color: #ffffff; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto;">2</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">สแกนสเปคอัตโนมัติ</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">อ่าน CPU, RAM, SSD, OS, S/N ใน 3 วินาที</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #10b981; color: #ffffff; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px auto;">3</div>
                        <div style="font-weight: 700; font-size: 13px; color: #0f172a;">แอดมินกดอนุมัติ</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">ข้อมูลเข้าคิวรอแอดมินยืนยันอัปเดตลงคลัง</div>
                    </div>
                </div>

                <!-- PowerShell Network Rollout Box -->
                <div style="background: #0f172a; border-radius: 14px; padding: 20px; color: #f8fafc; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(15,23,42,0.15);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <span style="font-size: 13px; font-weight: 700; color: #38bdf8; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-terminal-fill"></i> คำสั่ง PowerShell One-Liner (รันได้ทันทีบนเครื่องลูกข่าย):
                        </span>
                        <button type="button" class="btn btn-sm btn-primary" onclick="copyNetworkCommand()" style="font-size: 12px; padding: 4px 12px; border-radius: 6px;">
                            <i class="bi bi-clipboard me-1"></i> คัดลอกคำสั่ง
                        </button>
                    </div>
                    <div style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 12px 14px;">
                        <code id="networkCmdText" style="font-family: Consolas, monospace; font-size: 13px; color: #38bdf8; display: block; word-break: break-all;">
                            irm "{{ url('/agent/thc_audit_agent.ps1') }}" | iex
                        </code>
                    </div>
                    <div style="font-size: 11.5px; color: #94a3b8; margin-top: 8px;">
                        * เปิด PowerShell (Run as administrator) แล้ววางคำสั่งด้านบน กด Enter ระบบจะตรวจนับและส่งข้อมูลเข้าคิวเซิร์ฟเวอร์อัตโนมัติ
                    </div>
                </div>

                <!-- Installer Packages Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    
                    {{-- Package 1: Permanent Installer --}}
                    <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 14px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #166534; display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <i class="bi bi-hdd-rack-fill"></i> 1. ตัวติดตั้ง Agent ฝังประจำเครื่อง (แนะนำ)
                            </div>
                            <p style="font-size: 12.5px; color: #374151; line-height: 1.5; margin-bottom: 14px;">
                                ติดตั้งไฟล์สคริปต์ไว้ใน <code>C:\ProgramData\THC-IT-Agent</code>, สร้าง Task และวางปุ่มไอคอนทางลัดไว้บน Desktop สะดวกสำหรับเจ้าหน้าที่ประจำแผนกกดส่งสเปคเครื่อง
                            </p>
                        </div>
                        <a href="{{ asset('agent/install_thc_agent.bat') }}" download class="btn btn-success btn-sm" style="font-weight: 700; width: 100%; padding: 9px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="bi bi-download"></i> ดาวน์โหลด install_thc_agent.bat
                        </a>
                    </div>

                    {{-- Package 2: Portable USB Runner --}}
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 14px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: var(--shadow-sm);">
                        <div>
                            <div style="font-weight: 700; font-size: 15px; color: #334155; display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <i class="bi bi-usb-drive-fill"></i> 2. ตัวส่งสเปคแบบพกพา (ไม่ต้องติดตั้ง)
                            </div>
                            <p style="font-size: 12.5px; color: #64748b; line-height: 1.5; margin-bottom: 14px;">
                                คัดลอกใส่ใน Flash Drive ดับเบิลคลิกเพื่อรันสแกนและส่งผลเข้าคิวเซิร์ฟเวอร์ทันที เหมาะสำหรับช่างไอทีที่เดินตรวจนับตามตึก/แผนกต่างๆ
                            </p>
                        </div>
                        <a href="{{ asset('agent/run_manual_audit.bat') }}" download class="btn btn-outline-secondary btn-sm" style="font-weight: 700; width: 100%; padding: 9px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="bi bi-download"></i> ดาวน์โหลด run_manual_audit.bat
                        </a>
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

<style>
@keyframes modalScaleIn {
    from { transform: scale(0.96); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md) !important;
}
.tab-nav-btn:hover {
    color: #0f766e !important;
}
</style>

<script>
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
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a; margin-bottom: 6px;">
                        คำสั่งเรียกตรวจสเปคซ้ำจากเครื่องลูกข่าย (PowerShell One-Liner):
                    </div>
                    <p style="font-size: 12.5px; color: #64748b; margin-bottom: 12px;">
                        หากต้องการให้เครื่อง <strong>${audit.hostname}</strong> สแกนและส่งผลตรวจสอบเข้ามาใหม่อีกครั้ง สามารถคัดลอกคำสั่งด้านล่างไปรันใน PowerShell (Run as Administrator) บนเครื่องนั้นได้ทันที:
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
    // Side-by-Side Spec Diff Modal (เปรียบเทียบสเปคเดิม vs สเปคใหม่)
    // -------------------------------------------------------------
    function openAuditDiffModal(audit, asset) {
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

        body.innerHTML = `
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="font-size: 15px; color: #0f172a;">${audit.hostname || 'ไม่ระบุชื่อเครื่อง'}</strong>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        Serial Number: <code>${audit.serial_number || '-'}</code> &bull; 
                        ${asset ? 'รหัสครุภัณฑ์: <strong>' + asset.asset_code + '</strong>' : '<span class="text-warning">ไม่พบครุภัณฑ์ที่จับคู่</span>'}
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
            approveBtn.style.display = 'inline-flex';
            approveBtn.onclick = function() {
                closeAuditDiffModal();
                approveSingleAudit(audit.id, audit.hostname);
            };
        } else {
            approveBtn.style.display = 'none';
        }

        modal.style.display = 'flex';
    }

    function closeAuditDiffModal() {
        document.getElementById('auditDiffModal').style.display = 'none';
    }

    // Copy network command button
    function copyNetworkCommand() {
        const text = document.getElementById('networkCmdText').textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            if (window.Swal) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'คัดลอกคำสั่งเรียบร้อยแล้ว', showConfirmButton: false, timer: 1500 });
            } else {
                alert('คัดลอกคำสั่งเรียบร้อยแล้ว');
            }
        });
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
</script>
@endsection
