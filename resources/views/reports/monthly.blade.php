@extends('layouts.app')

@section('title', 'รายงานสรุปประจำเดือน')
@section('page_title', 'รายงานสรุปงานซ่อมบำรุงและพัสดุประจำเดือน')
@section('page_subtitle', 'ประจำเดือน ' . $monthName)

@section('content')
<!-- Filter Bar -->
<div class="card no-print" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 16px 24px;">
        <form action="{{ route('reports.monthly') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <label style="font-weight: 600; font-size: 13.5px;">เลือกเดือน / ปี:</label>
                <select name="month" class="form-select" style="width: 140px;">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->locale('th')->isoFormat('MMMM') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="form-select" style="width: 120px;">
                    @for($y = \Carbon\Carbon::now()->year - 2; $y <= \Carbon\Carbon::now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y + 543 }}
                        </option>
                    @endfor
                </select>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> แสดงรายงาน
                </button>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="{{ route('reports.export', 'repairs') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export CSV
                </a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> สั่งพิมพ์รายงาน
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Report Header for Print -->
<div style="text-align: center; margin-bottom: 24px;">
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">รายงานสรุปงานซ่อมบำรุงและพัสดุอุปกรณ์ IT ประจำเดือน</h2>
    <div style="font-size: 15px; color: var(--text-muted); font-weight: 500;">
        กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง &bull; ประจำเดือน {{ $monthName }}
    </div>
</div>

<!-- Summary Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center;">
        <div style="font-size: 12.5px; color: var(--text-muted);">งานแจ้งซ่อมทั้งหมด</div>
        <div style="font-size: 26px; font-weight: 700; color: var(--text-main); margin-top: 4px;">{{ $totalRepairs }}</div>
        <div style="font-size: 11.5px; color: #64748b;">รายการ</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #f0fdf4; border-color: #bbf7d0;">
        <div style="font-size: 12.5px; color: #166534;">ซ่อมเสร็จสิ้น</div>
        <div style="font-size: 26px; font-weight: 700; color: #15803d; margin-top: 4px;">{{ $completedCount }}</div>
        <div style="font-size: 11.5px; color: #166534;">
            {{ $totalRepairs > 0 ? round(($completedCount / $totalRepairs) * 100, 1) : 0 }}% ของทั้งหมด
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #fffbeb; border-color: #fde68a;">
        <div style="font-size: 12.5px; color: #92400e;">กำลังดำเนินการ / รออะไหล่</div>
        <div style="font-size: 26px; font-weight: 700; color: #b45309; margin-top: 4px;">{{ $inProgressCount }}</div>
        <div style="font-size: 11.5px; color: #92400e;">งานคงค้าง</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #f0fdfa; border-color: #99f6e4;">
        <div style="font-size: 12.5px; color: #0f766e;">มูลค่าอะไหล่ที่เบิกใช้</div>
        <div style="font-size: 26px; font-weight: 700; color: #0d9488; margin-top: 4px;">{{ number_format($totalCost, 2) }}</div>
        <div style="font-size: 11.5px; color: #0f766e;">บาท</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Department Breakdown Table -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-building"></i>
                <span>สถิติการแจ้งซ่อมแยกตามแผนก</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>แผนก / หน่วยงาน</th>
                        <th style="text-align: center;">จำนวนงานซ่อม</th>
                        <th style="text-align: right;">สัดส่วน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deptStats as $dept)
                    <tr>
                        <td style="font-weight: 500;">{{ $dept->name }}</td>
                        <td style="text-align: center; font-weight: 600;">{{ $dept->repairs_count }}</td>
                        <td style="text-align: right; color: var(--text-muted);">
                            {{ $totalRepairs > 0 ? round(($dept->repairs_count / $totalRepairs) * 100, 1) : 0 }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                            ไม่มีรายการแจ้งซ่อมในเดือนนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Spare Parts Consumed Table -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-box-seam"></i>
                <span>สรุปการเบิกใช้อะไหล่และพัสดุ</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>รายการพัสดุ</th>
                        <th style="text-align: center;">จำนวนเบิก</th>
                        <th style="text-align: right;">มูลค่ารวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partsConsumed as $part)
                    <tr>
                        <td style="font-weight: 500;">{{ $part->sparePart?->name }}</td>
                        <td style="text-align: center;">{{ $part->total_qty }} {{ $part->sparePart?->unit }}</td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($part->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                            ไม่มีการเบิกใช้อะไหล่ในเดือนนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detailed Repairs List Table -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-list-ul"></i>
            <span>รายการงานซ่อมทั้งหมดในรอบเดือน ({{ $repairs->count() }} รายการ)</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>เลขที่ใบแจ้งซ่อม</th>
                        <th>หัวข้อปัญหา</th>
                        <th>แผนก</th>
                        <th>ผู้แจ้ง</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th>สถานะ</th>
                        <th style="text-align: right;">ค่าใช้จ่าย (บาท)</th>
                        <th>วันที่แจ้ง</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repairs as $repair)
                    <tr>
                        <td style="font-weight: 700; color: var(--primary);">{{ $repair->ticket_number }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $repair->title }}</div>
                            @if($repair->solution)
                            <div style="font-size: 11.5px; color: #065f46;">วิธีแก้: {{ $repair->solution }}</div>
                            @endif
                        </td>
                        <td>{{ $repair->department?->name }}</td>
                        <td>{{ $repair->requester_name }}</td>
                        <td>{{ $repair->technician?->name ?? 'ยังไม่ระบุ' }}</td>
                        <td>
                            <span class="badge {{ $repair->status_badge }}">
                                {{ $repair->status_label }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($repair->total_cost, 2) }}</td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $repair->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            ไม่มีงานแจ้งซ่อมในเดือนนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Section: Data Requests Summary for the Month -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="bi bi-database text-teal"></i>
            <span>สรุปการให้บริการข้อมูลสารสนเทศทางการแพทย์และสถิติ ({{ $totalDataRequests }} รายการ)</span>
        </div>
        <div class="no-print">
            <a href="{{ route('reports.export', 'data_requests') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export ข้อมูลคำขอ
            </a>
        </div>
    </div>
    <div class="card-body" style="padding: 16px 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px;">
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; text-align: center;">
                <div style="font-size: 12px; color: var(--text-muted);">คำขอข้อมูลทั้งหมด</div>
                <div style="font-size: 22px; font-weight: 700; color: var(--text-main); margin-top: 2px;">{{ $totalDataRequests }}</div>
            </div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 16px; text-align: center;">
                <div style="font-size: 12px; color: #166534;">สกัดข้อมูลสำเร็จ</div>
                <div style="font-size: 22px; font-weight: 700; color: #15803d; margin-top: 2px;">{{ $completedDataRequests }}</div>
            </div>
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; text-align: center;">
                <div style="font-size: 12px; color: #92400e;">รอพิจารณา / กำลังสกัด</div>
                <div style="font-size: 22px; font-weight: 700; color: #b45309; margin-top: 2px;">{{ $pendingDataRequests }}</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>เลขที่คำขอ</th>
                        <th>หัวข้อข้อมูลสารสนเทศ</th>
                        <th>แผนกผู้ขอ</th>
                        <th>วัตถุประสงค์</th>
                        <th>รูปแบบไฟล์</th>
                        <th>สถานะ</th>
                        <th>ผู้รับผิดชอบ (IT)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataRequests as $dr)
                    <tr>
                        <td style="font-weight: 700; color: var(--primary);">{{ $dr->request_no }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $dr->title }}</div>
                            @if($dr->data_start_date && $dr->data_end_date)
                                <div style="font-size: 11px; color: var(--text-muted);">ช่วงข้อมูล: {{ $dr->data_start_date->format('d/m/Y') }} - {{ $dr->data_end_date->format('d/m/Y') }}</div>
                            @endif
                        </td>
                        <td>{{ $dr->department?->name ?? 'ไม่ระบุ' }}</td>
                        <td><span style="font-size: 12px;">{{ $dr->objective_label }}</span></td>
                        <td><span style="text-transform: uppercase; font-size: 12px; font-weight: 600;">{{ $dr->file_format }}</span></td>
                        <td><span class="badge {{ $dr->status_badge }}">{{ $dr->status_label }}</span></td>
                        <td>{{ $dr->handler?->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            ไม่มีคำขอข้อมูลสารสนเทศในเดือนนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
