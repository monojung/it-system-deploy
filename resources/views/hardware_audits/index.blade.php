@extends('layouts.app')

@section('title', 'ติดตามสเปค & ตรวจนับครุภัณฑ์คอมพิวเตอร์ประจำปีงบประมาณ')

@section('content')
<div class="content-header">
    <div class="header-left">
        <div class="header-icon" style="background: linear-gradient(135deg, #0f766e 0%, #0284c7 100%); color: #ffffff;">
            <i class="bi bi-cpu-fill"></i>
        </div>
        <div>
            <h1 class="header-title">ติดตามสเปค & ตรวจนับคอมพิวเตอร์ประจำปีงบประมาณ</h1>
            <p class="header-subtitle">
                ศูนย์มอนิเตอร์และอนุมัติการอัปเดตสเปคฮาร์ดแวร์จาก Agent ประจำเครื่อง สำหรับสรุปรายงานประจำปีงบประมาณ พ.ศ. {{ $fiscalYear }}
            </p>
        </div>
    </div>
    <div class="header-right" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
        {{-- Fiscal Year Selector --}}
        <form method="GET" action="{{ route('hardware-audits.index') }}" style="display: flex; align-items: center; gap: 6px;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <label for="fiscal_year" style="font-size: 13px; font-weight: 600; color: #475569; white-space: nowrap;">ปีงบประมาณ:</label>
            <select name="fiscal_year" id="fiscal_year" class="form-select form-select-sm" onchange="this.form.submit()" style="font-weight: 700; width: 110px; border-color: #0f766e; color: #0f766e;">
                @foreach($availableFiscalYears as $fy)
                    <option value="{{ $fy }}" {{ $fiscalYear == $fy ? 'selected' : '' }}>พ.ศ. {{ $fy }}</option>
                @endforeach
            </select>
        </form>

        <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="display: inline-flex; align-items: center; gap: 5px;">
            <i class="bi bi-printer"></i> พิมพ์รายงานสรุป A4
        </a>
        <a href="{{ route('hardware-audits.export-csv', ['fiscal_year' => $fiscalYear]) }}" class="btn btn-sm btn-outline-success" style="display: inline-flex; align-items: center; gap: 5px;">
            <i class="bi bi-file-earmark-excel"></i> ส่งออก CSV
        </a>
    </div>
</div>

{{-- Top Statistics Cards --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 22px;">
    {{-- Card 1: Total Fleet --}}
    <div class="stat-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12.5px; font-weight: 600; color: #64748b;">คอมพิวเตอร์ทั้งหมด</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="bi bi-pc-display"></i>
            </div>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1;">{{ number_format($totalFleet) }} <span style="font-size: 13px; font-weight: 500; color: #64748b;">เครื่อง</span></div>
        <div style="font-size: 11.5px; color: #94a3b8; margin-top: 6px;">เป้าหมายสำรวจ รพ.ทุ่งหัวช้าง</div>
    </div>

    {{-- Card 2: Audited This FY --}}
    <div class="stat-card" style="background: #ffffff; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: linear-gradient(to bottom, #ffffff, #f0fdf4);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12.5px; font-weight: 600; color: #166534;">ตรวจนับ/อัปเดตสเปคแล้ว</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="bi bi-shield-check"></i>
            </div>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #15803d; line-height: 1;">
            {{ number_format($auditedCount) }} <span style="font-size: 13px; font-weight: 500; color: #166534;">({{ $auditedPercentage }}%)</span>
        </div>
        <div class="progress mt-2" style="height: 6px; background: #e2e8f0; border-radius: 3px;">
            <div class="progress-bar bg-success" style="width: {{ $auditedPercentage }}%;"></div>
        </div>
    </div>

    {{-- Card 3: Pending Review --}}
    <div class="stat-card" style="background: #ffffff; border: 1px solid {{ $pendingCount > 0 ? '#fde047' : '#e2e8f0' }}; border-radius: 12px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: {{ $pendingCount > 0 ? 'linear-gradient(to bottom, #ffffff, #fefce8)' : '#ffffff' }};">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12.5px; font-weight: 600; color: #854d0e;">รอแอดมินกดอนุมัติ</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #fef08a; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="bi bi-clock-history"></i>
            </div>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #a16207; line-height: 1;">{{ number_format($pendingCount) }} <span style="font-size: 13px; font-weight: 500; color: #854d0e;">รายการ</span></div>
        <div style="font-size: 11.5px; color: #713f12; margin-top: 6px;">ส่งสเปคมาแล้ว รอตรวจสอบ</div>
    </div>

    {{-- Card 4: Not Audited --}}
    <div class="stat-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12.5px; font-weight: 600; color: #64748b;">คงเหลือยังไม่ได้สำรวจ</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #334155; line-height: 1;">{{ number_format($notAuditedCount) }} <span style="font-size: 13px; font-weight: 500; color: #64748b;">เครื่อง</span></div>
        <div style="font-size: 11.5px; color: #94a3b8; margin-top: 6px;">ต้องเข้าดำเนินการตรวจนับ</div>
    </div>

    {{-- Card 5: Hardware Upgrade Warnings --}}
    <div class="stat-card" style="background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: linear-gradient(to bottom, #ffffff, #fff5f5);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <span style="font-size: 12.5px; font-weight: 600; color: #991b1b;">เตือนสเปคต่ำ/ควรปรับปรุง</span>
            <div style="width: 34px; height: 34px; border-radius: 8px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
        </div>
        <div style="font-size: 24px; font-weight: 800; color: #b91c1c; line-height: 1;">{{ number_format($mdesWarningCount) }} <span style="font-size: 13px; font-weight: 500; color: #991b1b;">เครื่อง</span></div>
        <div style="font-size: 11.5px; color: #ef4444; margin-top: 6px;">RAM &lt; 8GB / HDD / Win 7</div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.03); margin-bottom: 24px;">
    <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc; overflow-x: auto;">
        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending']) }}" 
           style="padding: 14px 20px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-bottom: 3px solid {{ $tab === 'pending' ? '#0f766e' : 'transparent' }}; color: {{ $tab === 'pending' ? '#0f766e' : '#64748b' }}; background: {{ $tab === 'pending' ? '#ffffff' : 'transparent' }};">
            <i class="bi bi-inbox-fill"></i>
            <span>คิวรอตรวจสอบและอนุมัติ</span>
            @if($pendingCount > 0)
                <span style="background: #f59e0b; color: #ffffff; font-size: 11px; padding: 2px 7px; border-radius: 10px; font-weight: 700;">{{ $pendingCount }}</span>
            @endif
        </a>

        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'departments']) }}" 
           style="padding: 14px 20px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-bottom: 3px solid {{ $tab === 'departments' ? '#0f766e' : 'transparent' }}; color: {{ $tab === 'departments' ? '#0f766e' : '#64748b' }}; background: {{ $tab === 'departments' ? '#ffffff' : 'transparent' }};">
            <i class="bi bi-diagram-3-fill"></i>
            <span>ติดตามความคืบหน้ารายแผนก</span>
        </a>

        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'report']) }}" 
           style="padding: 14px 20px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-bottom: 3px solid {{ $tab === 'report' ? '#0f766e' : 'transparent' }}; color: {{ $tab === 'report' ? '#0f766e' : '#64748b' }}; background: {{ $tab === 'report' ? '#ffffff' : 'transparent' }};">
            <i class="bi bi-bar-chart-line-fill"></i>
            <span>รายงานสรุปประจำปีงบประมาณ</span>
        </a>

        <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'deploy']) }}" 
           style="padding: 14px 20px; font-size: 13.5px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 8px; border-bottom: 3px solid {{ $tab === 'deploy' ? '#0f766e' : 'transparent' }}; color: {{ $tab === 'deploy' ? '#0f766e' : '#64748b' }}; background: {{ $tab === 'deploy' ? '#ffffff' : 'transparent' }};">
            <i class="bi bi-terminal-fill"></i>
            <span>ตัวติดตั้ง Agent & การกระจายสคริปต์</span>
        </a>
    </div>

    {{-- TAB 1: PENDING QUEUE --}}
    @if($tab === 'pending')
    <div style="padding: 20px;">
        {{-- Search & Filter Bar --}}
        <form method="GET" action="{{ route('hardware-audits.index') }}" style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <input type="hidden" name="fiscal_year" value="{{ $fiscalYear }}">
            <input type="hidden" name="tab" value="pending">
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหา Hostname, S/N, IP หรือ CPU..." 
                       value="{{ request('search') }}" style="width: 260px;">
                <select name="status" class="form-select form-select-sm" style="width: 140px;">
                    <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>รอตรวจสอบ</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธแล้ว</option>
                    <option value="" {{ request('status') === '' ? 'selected' : '' }}>ทุกสถานะ</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search"></i> ค้นหา</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('hardware-audits.index', ['fiscal_year' => $fiscalYear, 'tab' => 'pending']) }}" class="btn btn-sm btn-outline-secondary">ล้างค่า</a>
                @endif
            </div>

            {{-- Batch Actions --}}
            @if(request('status', 'pending') === 'pending' && $audits->count() > 0)
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-sm btn-success" onclick="submitBatchApproval()" style="font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-check2-all"></i> อนุมัติรายการที่เลือก
                </button>
            </div>
            @endif
        </form>

        <form id="batchApproveForm" method="POST" action="{{ route('hardware-audits.batch-approve') }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                        <tr>
                            <th style="width: 36px;">
                                <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                            </th>
                            <th>วันที่ส่งข้อมูล</th>
                            <th>เครื่องคอมพิวเตอร์ (Hostname)</th>
                            <th>Serial Number (S/N)</th>
                            <th>ครุภัณฑ์ที่จับคู่ (Matched Asset)</th>
                            <th>สเปคที่ตรวจพบ (Scanned Specs)</th>
                            <th>การเปลี่ยนแปลง (Diff)</th>
                            <th>สถานะ</th>
                            <th style="text-align: right; width: 140px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                        <tr>
                            <td>
                                @if($audit->status === 'pending')
                                    <input type="checkbox" name="audit_ids[]" value="{{ $audit->id }}" class="audit-select-chk">
                                @endif
                            </td>
                            <td style="color: #64748b; font-size: 12px; white-space: nowrap;">
                                {{ $audit->created_at ? $audit->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td>
                                <strong style="color: #0f172a;">{{ $audit->hostname ?: '-' }}</strong>
                                <div style="font-size: 11.5px; color: #64748b;">IP: {{ $audit->ip_address ?: '-' }}</div>
                            </td>
                            <td>
                                <code style="font-size: 12px; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px;">
                                    {{ $audit->serial_number ?: '(ไม่พบ S/N)' }}
                                </code>
                            </td>
                            <td>
                                @if($audit->asset)
                                    <a href="{{ route('assets.show', $audit->asset_id) }}" target="_blank" style="font-weight: 600; color: #0f766e; text-decoration: none;">
                                        {{ $audit->asset->asset_code }}
                                    </a>
                                    <div style="font-size: 11.5px; color: #64748b;">{{ $audit->asset->department?->name ?? 'ไม่ระบุแผนก' }}</div>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">เครื่องใหม่/ไม่พบในระบบ</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $audit->cpu_model ?: '-' }}</div>
                                <div style="font-size: 11.5px; color: #475569;">
                                    RAM {{ $audit->ram_capacity }}GB {{ $audit->ram_type }} • {{ $audit->storage_type }} {{ $audit->storage_capacity }}
                                </div>
                                <div style="font-size: 11px; color: #64748b;">{{ $audit->os_name }}</div>
                            </td>
                            <td>
                                @if($audit->hasDiff())
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 11.5px;">
                                        <i class="bi bi-arrow-left-right me-1"></i> มี {{ $audit->diff_count }} จุดเปลี่ยน
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 11px;">สเปคตรงเดิม</span>
                                @endif
                            </td>
                            <td>
                                {!! $audit->status_badge !!}
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="openAuditDiffModal({{ json_encode($audit) }}, {{ json_encode($audit->asset) }})" title="เปรียบเทียบสเปคละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if($audit->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success" onclick="approveSingleAudit({{ $audit->id }}, '{{ $audit->hostname }}')" title="อนุมัติอัปเดตลงครุภัณฑ์">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectSingleAudit({{ $audit->id }}, '{{ $audit->hostname }}')" title="ปฏิเสธ">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                ไม่พบรายการสแกนสเปคในคิวสำหรับปีงบประมาณนี้
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-3">
            {{ $audits->links() }}
        </div>
    </div>
    @endif

    {{-- TAB 2: DEPARTMENT TRACKER --}}
    @if($tab === 'departments')
    <div style="padding: 20px;">
        <div style="font-size: 13.5px; color: #475569; margin-bottom: 16px;">
            <i class="bi bi-info-circle text-primary me-1"></i> ติดตามความคืบหน้าการตรวจนับและอัปเดตสเปคคอมพิวเตอร์ประจำปีงบประมาณ {{ $fiscalYear }} แยกตามแผนก/หน่วยงาน
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 14px;">
            @foreach($departments as $dept)
            <div style="background: #ffffff; border: 1px solid {{ $dept->percentage == 100 ? '#bbf7d0' : '#e2e8f0' }}; border-radius: 10px; padding: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="color: #0f172a; font-size: 14px;">{{ $dept->name }}</strong>
                    <span style="font-size: 12px; font-weight: 700; color: {{ $dept->percentage == 100 ? '#16a34a' : ($dept->percentage > 0 ? '#0284c7' : '#94a3b8') }};">
                        {{ $dept->percentage }}%
                    </span>
                </div>
                
                <div class="progress mb-2" style="height: 7px; background: #e2e8f0; border-radius: 4px;">
                    <div class="progress-bar {{ $dept->percentage == 100 ? 'bg-success' : 'bg-primary' }}" style="width: {{ $dept->percentage }}%;"></div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b;">
                    <span>สำรวจแล้ว: <strong style="color: #166534;">{{ $dept->audited_assets }}</strong> / {{ $dept->total_assets }} เครื่อง</span>
                    <span>คงเหลือ: <strong style="color: {{ $dept->pending_assets > 0 ? '#dc2626' : '#166534' }};">{{ $dept->pending_assets }}</strong></span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- TAB 3: ANNUAL REPORT ANALYTICS --}}
    @if($tab === 'report')
    <div style="padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">สรุปภาพรวมสถานะและสเปคฮาร์ดแวร์ประจำปีงบประมาณ พ.ศ. {{ $fiscalYear }}</h3>
                <div style="font-size: 12.5px; color: #64748b;">ข้อมูลวิเคราะห์เพื่อการวางแผนงบประมาณปรับปรุงครุภัณฑ์คอมพิวเตอร์ รพ.ทุ่งหัวช้าง</div>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('hardware-audits.print-report', ['fiscal_year' => $fiscalYear]) }}" target="_blank" class="btn btn-primary btn-sm" style="font-weight: 600;">
                    <i class="bi bi-printer"></i> เปิดพิมพ์รายงาน A4 ฉบับทางการ
                </a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            {{-- RAM Breakdown --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <h4 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-memory text-primary"></i> สรุปขนาดหน่วยความจำ (RAM)
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($ramDistribution as $r)
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; padding: 4px 0; border-bottom: 1px solid #f1f5f9;">
                        <span>RAM {{ $r->ram_capacity }} GB {{ $r->ram_capacity < 8 ? '⚠️ (ต่ำกว่าเกณฑ์)' : '' }}</span>
                        <strong>{{ $r->total }} เครื่อง</strong>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- OS Breakdown --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <h4 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-windows text-info"></i> สรุประบบปฏิบัติการ (OS)
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($osDistribution as $os)
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; padding: 4px 0; border-bottom: 1px solid #f1f5f9;">
                        <span>{{ $os->os_name }}</span>
                        <strong>{{ $os->total }} เครื่อง</strong>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Storage Breakdown --}}
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px;">
                <h4 style="font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-hdd-fill text-warning"></i> สรุปประเภทพื้นที่จัดเก็บ (Storage)
                </h4>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    @foreach($storageDistribution as $st)
                    <div style="display: flex; justify-content: space-between; font-size: 12.5px; padding: 4px 0; border-bottom: 1px solid #f1f5f9;">
                        <span>{{ $st->storage_type }}</span>
                        <strong>{{ $st->total }} เครื่อง</strong>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 4: AGENT DEPLOYMENT HUB --}}
    @if($tab === 'deploy')
    <div style="padding: 20px;">
        <div style="max-width: 800px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">
                <i class="bi bi-box-arrow-down text-primary me-1"></i> ดาวน์โหลดและติดตั้ง Agent ประจำเครื่อง
            </h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 18px;">
                โปรแกรม Agent สำหรับฝังไว้ในเครื่องคอมพิวเตอร์ลูกข่ายของโรงพยาบาล เพื่ออ่านสเปคจริง (WMI/BIOS) และส่งเข้าสู่คิวตรวจสอบของเซิร์ฟเวอร์โดยที่แอดมินเป็นผู้กดอนุมัติอัปเดตเอง
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px;">
                {{-- Package 1: Permanent Installer --}}
                <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #166534; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                            <i class="bi bi-hdd-rack"></i> 1. ติดตั้ง Agent ฝังลงเครื่องถาวร (แนะนำ)
                        </div>
                        <div style="font-size: 12px; color: #374151; line-height: 1.4; margin-bottom: 12px;">
                            ฝังไฟล์ไว้ใน <code>C:\ProgramData\THC-IT-Agent</code>, สร้าง Task และวางปุ่มทางลัดไว้บน Desktop สำหรับส่งสเปคเครื่อง
                        </div>
                    </div>
                    <a href="{{ asset('agent/install_thc_agent.bat') }}" download class="btn btn-success btn-sm" style="font-weight: 600; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="bi bi-download"></i> ดาวน์โหลด install_thc_agent.bat
                    </a>
                </div>

                {{-- Package 2: Portable Runner --}}
                <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #334155; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                            <i class="bi bi-usb-drive"></i> 2. ตัวส่งสเปคแบบพกพา (ไม่ต้องติดตั้ง)
                        </div>
                        <div style="font-size: 12px; color: #64748b; line-height: 1.4; margin-bottom: 12px;">
                            ใส่ใน Flash Drive ดับเบิลคลิกเพื่อสแกนและส่งผลเข้าคิวทันที เหมาะสำหรับช่างที่เดินสำรวจตามจุด
                        </div>
                    </div>
                    <a href="{{ asset('agent/run_manual_audit.bat') }}" download class="btn btn-outline-secondary btn-sm" style="font-weight: 600; width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="bi bi-download"></i> ดาวน์โหลด run_manual_audit.bat
                    </a>
                </div>
            </div>

            {{-- PowerShell Network Rollout One-Liner --}}
            <div style="background: #0f172a; border-radius: 10px; padding: 16px; color: #f8fafc; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12.5px; font-weight: 700; color: #38bdf8;">
                        <i class="bi bi-terminal me-1"></i> คำสั่งสั่งรันผ่านเครือข่าย / PowerShell / GPO:
                    </span>
                    <button type="button" class="btn btn-xs btn-outline-light py-0 px-2" onclick="navigator.clipboard.writeText('irm &quot;{{ url('/agent/thc_audit_agent.ps1') }}&quot; | iex'); alert('คัดลอกคำสั่งแล้ว!');" style="font-size: 11px;">
                        <i class="bi bi-clipboard"></i> คัดลอก
                    </button>
                </div>
                <code style="font-family: Consolas, monospace; font-size: 12px; color: #e2e8f0; display: block; word-break: break-all;">
                    irm "{{ url('/agent/thc_audit_agent.ps1') }}" | iex
                </code>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Diff & Review Modal --}}
<div id="auditDiffModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 1070; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 16px; width: 100%; max-width: 650px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="background: linear-gradient(135deg, #0f766e 0%, #0284c7 100%); padding: 16px 20px; color: #ffffff; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-cpu"></i> ตรวจสอบและเปรียบเทียบสเปคเครื่อง
            </div>
            <button type="button" onclick="closeAuditDiffModal()" style="background: transparent; border: none; color: #ffffff; font-size: 22px; cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 20px; overflow-y: auto; flex: 1;" id="auditDiffModalBody">
            {{-- Content loaded dynamically via JS --}}
        </div>

        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeAuditDiffModal()">ปิดหน้าต่าง</button>
            <button type="button" class="btn btn-success btn-sm" id="modalApproveBtn" style="font-weight: 600;">
                <i class="bi bi-check-lg"></i> อนุมัติและบันทึกลงครุภัณฑ์
            </button>
        </div>
    </div>
</div>

<form id="singleApproveForm" method="POST" style="display: none;">
    @csrf
</form>

<form id="singleRejectForm" method="POST" style="display: none;">
    @csrf
</form>

<script>
    function toggleSelectAll(master) {
        const chks = document.querySelectorAll('.audit-select-chk');
        chks.forEach(c => c.checked = master.checked);
    }

    function submitBatchApproval() {
        const checked = document.querySelectorAll('.audit-select-chk:checked');
        if (checked.length === 0) {
            alert('กรุณาเลือกรายการที่ต้องการอนุมัติอย่างน้อย 1 รายการ');
            return;
        }
        if (confirm('คุณต้องการอนุมัติและอัปเดตสเปคลงครุภัณฑ์จำนวน ' + checked.length + ' รายการใช่หรือไม่?')) {
            document.getElementById('batchApproveForm').submit();
        }
    }

    function approveSingleAudit(id, hostname) {
        if (confirm('ยืนยันอนุมัติและอัปเดตสเปคของเครื่อง "' + hostname + '" ลงฐานข้อมูลครุภัณฑ์?')) {
            const form = document.getElementById('singleApproveForm');
            form.action = '{{ url("/hardware-audits") }}/' + id + '/approve';
            form.submit();
        }
    }

    function rejectSingleAudit(id, hostname) {
        if (confirm('ต้องการปฏิเสธรายการสแกนสเปคของเครื่อง "' + hostname + '" ใช่หรือไม่?')) {
            const form = document.getElementById('singleRejectForm');
            form.action = '{{ url("/hardware-audits") }}/' + id + '/reject';
            form.submit();
        }
    }

    function openAuditDiffModal(audit, asset) {
        const modal = document.getElementById('auditDiffModal');
        const body = document.getElementById('auditDiffModalBody');
        const approveBtn = document.getElementById('modalApproveBtn');

        let html = `
            <div style="margin-bottom: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">${audit.hostname || '-'}</div>
                <div style="font-size: 12px; color: #64748b;">Serial Number: <strong>${audit.serial_number || 'ไม่พบ'}</strong> • IP: ${audit.ip_address || '-'}</div>
                <div style="font-size: 12px; color: #0f766e; margin-top: 4px;">ครุภัณฑ์ที่จับคู่: <strong>${asset ? asset.asset_code + ' (' + asset.name + ')' : 'ยังไม่ได้จับคู่กับครุภัณฑ์'}</strong></div>
            </div>
            
            <h5 style="font-size: 13px; font-weight: 700; margin-bottom: 8px; color: #334155;">รายละเอียดสเปคที่อ่านได้จากเครื่องจริง:</h5>
            <table class="table table-sm table-bordered" style="font-size: 12.5px; margin-bottom: 16px;">
                <tbody>
                    <tr><td style="width: 140px; background: #f8fafc; font-weight: 600;">CPU</td><td>${audit.cpu_model || '-'} (${audit.cpu_speed || '-'})</td></tr>
                    <tr><td style="background: #f8fafc; font-weight: 600;">RAM</td><td>${audit.ram_capacity || '-'} GB ${audit.ram_type || ''} (${audit.ram_bus || ''}) [${audit.ram_slots || ''}]</td></tr>
                    <tr><td style="background: #f8fafc; font-weight: 600;">Storage</td><td>${audit.storage_type || ''} ${audit.storage_capacity || ''}</td></tr>
                    <tr><td style="background: #f8fafc; font-weight: 600;">OS</td><td>${audit.os_name || '-'} (${audit.os_license || '-'})</td></tr>
                    <tr><td style="background: #f8fafc; font-weight: 600;">GPU</td><td>${audit.gpu_model || '-'}</td></tr>
                    <tr><td style="background: #f8fafc; font-weight: 600;">MAC Address</td><td><code>${audit.mac_address || '-'}</code></td></tr>
                </tbody>
            </table>
        `;

        if (audit.specs_diff && Object.keys(audit.specs_diff).length > 0) {
            html += `
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px; margin-bottom: 12px;">
                    <div style="font-weight: 700; font-size: 12.5px; color: #92400e; margin-bottom: 6px;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> พบจุดที่เปลี่ยนแปลงเมื่อเทียบกับข้อมูลเดิม:
                    </div>
                    <div style="font-size: 12px; color: #78350f;">
            `;
            for (const [k, d] of Object.entries(audit.specs_diff)) {
                html += `<div>• <strong>${d.label}:</strong> เดิม <code>${d.old}</code> ➔ ใหม่ <strong style="color: #16a34a;">${d.new}</strong></div>`;
            }
            html += `</div></div>`;
        }

        body.innerHTML = html;

        if (audit.status === 'pending') {
            approveBtn.style.display = 'inline-flex';
            approveBtn.onclick = function() {
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
</script>
@endsection
