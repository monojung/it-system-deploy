@extends('layouts.app')

@section('title', 'รายงานสรุปประจำไตรมาส')
@section('page_title', 'รายงานสรุปงานซ่อมและพัสดุประจำไตรมาส')
@section('page_subtitle', 'ปีงบประมาณ ' . ($fiscalYear + 543) . ' &bull; ' . $quarterLabel)

@section('content')
<!-- Filter Bar -->
<div class="card no-print" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 16px 24px;">
        <form action="{{ route('reports.quarterly') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <label style="font-weight: 600; font-size: 13.5px;">ปีงบประมาณ:</label>
                <select name="fiscal_year" class="form-select" style="width: 140px;">
                    @for($fy = \Carbon\Carbon::now()->year - 2; $fy <= \Carbon\Carbon::now()->year + 2; $fy++)
                        <option value="{{ $fy }}" {{ $fiscalYear == $fy ? 'selected' : '' }}>
                            {{ $fy + 543 }}
                        </option>
                    @endfor
                </select>

                <label style="font-weight: 600; font-size: 13.5px;">ไตรมาส:</label>
                <select name="quarter" class="form-select" style="width: 220px;">
                    <option value="1" {{ $quarter == 1 ? 'selected' : '' }}>ไตรมาสที่ 1 (ต.ค. - ธ.ค.)</option>
                    <option value="2" {{ $quarter == 2 ? 'selected' : '' }}>ไตรมาสที่ 2 (ม.ค. - มี.ค.)</option>
                    <option value="3" {{ $quarter == 3 ? 'selected' : '' }}>ไตรมาสที่ 3 (เม.ย. - มิ.ย.)</option>
                    <option value="4" {{ $quarter == 4 ? 'selected' : '' }}>ไตรมาสที่ 4 (ก.ค. - ก.ย.)</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> แสดงผลรายงาน
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
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">รายงานสรุปผลการดำเนินงานซ่อมบำรุงและพัสดุอุปกรณ์ IT ประจำไตรมาส</h2>
    <div style="font-size: 15px; color: var(--text-muted); font-weight: 500;">
        กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง &bull; ปีงบประมาณ {{ $fiscalYear + 543 }} &bull; {{ $quarterLabel }}
    </div>
</div>

<!-- Top Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 20px; text-align: center;">
        <div style="font-size: 13px; color: var(--text-muted);">งานแจ้งซ่อมในไตรมาสนี้</div>
        <div style="font-size: 28px; font-weight: 700; color: var(--text-main); margin-top: 4px;">{{ $totalRepairs }}</div>
        <div style="font-size: 12px; color: #64748b;">รายการทั้งหมด</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 20px; text-align: center; background: #f0fdf4; border-color: #bbf7d0;">
        <div style="font-size: 13px; color: #166534;">ซ่อมเสร็จสมบูรณ์</div>
        <div style="font-size: 28px; font-weight: 700; color: #15803d; margin-top: 4px;">{{ $completedCount }}</div>
        <div style="font-size: 12px; color: #166534;">รายการที่ปิดงานแล้ว</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 20px; text-align: center; background: #ecfeff; border-color: #a5f3fc;">
        <div style="font-size: 13px; color: #0e7490;">อัตราความสำเร็จ (SLA)</div>
        <div style="font-size: 28px; font-weight: 700; color: #0891b2; margin-top: 4px;">{{ $completionRate }}%</div>
        <div style="font-size: 12px; color: #0e7490;">งานเสร็จสิ้น / งานทั้งหมด</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 20px; text-align: center; background: #f0fdfa; border-color: #99f6e4;">
        <div style="font-size: 13px; color: #0f766e;">ค่าใช้จ่ายอะไหล่รวม</div>
        <div style="font-size: 28px; font-weight: 700; color: #0d9488; margin-top: 4px;">{{ number_format($totalCost, 2) }}</div>
        <div style="font-size: 12px; color: #0f766e;">บาท</div>
    </div>
</div>

<!-- Two Columns Tables -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Department Breakdown -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-building"></i>
                <span>สถิติการแจ้งซ่อมแยกตามแผนกในไตรมาส</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>แผนก / งาน</th>
                        <th style="text-align: center;">จำนวนงาน</th>
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
                            ไม่มีข้อมูลในไตรมาสนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Device Type Breakdown -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-pc"></i>
                <span>สถิติการแจ้งซ่อมแยกตามประเภทอุปกรณ์</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>ประเภทอุปกรณ์</th>
                        <th style="text-align: center;">รหัส</th>
                        <th style="text-align: center;">จำนวนครั้งที่เสีย</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($typeStats as $type)
                    <tr>
                        <td style="font-weight: 500;">{{ $type->name }}</td>
                        <td style="text-align: center; font-family: monospace;">{{ $type->code }}</td>
                        <td style="text-align: center; font-weight: 600;">{{ $type->repairs_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                            ไม่มีข้อมูล
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Parts Consumed in Quarter -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-box-seam"></i>
            <span>สรุปการเบิกใช้อะไหล่และพัสดุสิ้นเปลืองในไตรมาสนี้</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>รหัสพัสดุ</th>
                    <th>รายการพัสดุ / อะไหล่</th>
                    <th style="text-align: center;">จำนวนที่เบิกใช้</th>
                    <th style="text-align: right;">มูลค่ารวม (บาท)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partsConsumed as $part)
                <tr>
                    <td style="font-family: monospace; font-weight: 600;">{{ $part->sparePart?->part_code }}</td>
                    <td style="font-weight: 500;">{{ $part->sparePart?->name }}</td>
                    <td style="text-align: center; font-weight: 600;">{{ $part->total_qty }} {{ $part->sparePart?->unit }}</td>
                    <td style="text-align: right; font-weight: 700; color: #0d9488;">{{ number_format($part->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 25px;">
                        ไม่มีการเบิกใช้อะไหล่ในไตรมาสนี้
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Data Requests Summary in Quarter -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="bi bi-database-fill-check text-primary"></i>
            <span>สถิติการขอข้อมูลสารสนเทศทางการแพทย์ในรอบไตรมาส ({{ $totalDrQuarter }} คำขอ)</span>
        </div>
        <div class="no-print">
            <a href="{{ route('reports.export', 'data_requests') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-file-earmark-excel"></i> Export ข้อมูลคำขอ
            </a>
        </div>
    </div>
    <div class="card-body" style="padding: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 14px; text-align: center;">
                <div style="font-size: 12.5px; color: var(--text-muted);">จำนวนคำขอทั้งหมด</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-top: 4px;">{{ $totalDrQuarter }}</div>
                <div style="font-size: 11px; color: #64748b;">รายการ</div>
            </div>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px; text-align: center;">
                <div style="font-size: 12.5px; color: #166534;">สกัดข้อมูลสำเร็จแล้ว</div>
                <div style="font-size: 24px; font-weight: 700; color: #15803d; margin-top: 4px;">{{ $completedDrQuarter }}</div>
                <div style="font-size: 11px; color: #166534;">
                    {{ $totalDrQuarter > 0 ? round(($completedDrQuarter / $totalDrQuarter) * 100, 1) : 0 }}% ส่งมอบสำเร็จ
                </div>
            </div>
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px; text-align: center;">
                <div style="font-size: 12.5px; color: #1e40af;">รอดำเนินการ / สกัด</div>
                <div style="font-size: 24px; font-weight: 700; color: #1d4ed8; margin-top: 4px;">{{ $totalDrQuarter - $completedDrQuarter }}</div>
                <div style="font-size: 11px; color: #1e40af;">รายการคงค้าง</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>เลขที่คำขอ</th>
                        <th>หัวข้อข้อมูลสารสนเทศ</th>
                        <th>แผนก</th>
                        <th>ผู้ขอ</th>
                        <th>วัตถุประสงค์</th>
                        <th>สถานะ</th>
                        <th>วันที่ขอ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quarterlyDataRequests as $dr)
                    <tr>
                        <td style="font-weight: 700; color: var(--primary);">{{ $dr->request_no }}</td>
                        <td>{{ $dr->title }}</td>
                        <td>{{ $dr->department?->name ?? 'ไม่ระบุ' }}</td>
                        <td>{{ $dr->user?->name ?? '-' }}</td>
                        <td><span style="font-size: 12px;">{{ $dr->objective_label }}</span></td>
                        <td><span class="badge {{ $dr->status_badge }}">{{ $dr->status_label }}</span></td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ $dr->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 25px;">
                            ไม่มีคำขอข้อมูลสารสนเทศในไตรมาสนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
