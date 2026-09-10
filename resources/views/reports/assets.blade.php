@extends('layouts.app')

@section('title', 'รายงานสถานะครุภัณฑ์คอมพิวเตอร์')
@section('page_title', 'รายงานสถานะและมูลค่าครุภัณฑ์คอมพิวเตอร์')
@section('page_subtitle', 'กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง')

@section('content')
<!-- Filter Bar -->
<div class="card no-print" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 16px 24px;">
        <form action="{{ route('reports.assets') }}" method="GET" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                @if(auth()->user()->isUser())
                    <span style="font-size: 13.5px; background: #e0f2fe; color: #0369a1; padding: 6px 12px; border-radius: 6px; font-weight: 600;">
                        <i class="bi bi-building"></i> แผนก: {{ auth()->user()->department->name ?? 'แผนกของท่าน' }}
                    </span>
                @else
                    <label style="font-weight: 600; font-size: 13.5px;">แผนก:</label>
                    <select name="department_id" class="form-select" style="width: 200px;">
                        <option value="">-- ทุกแผนก --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <label style="font-weight: 600; font-size: 13.5px;">สถานะ:</label>
                <select name="status" class="form-select" style="width: 160px;">
                    <option value="">-- ทุกสถานะ --</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>ใช้งานปกติ</option>
                    <option value="spare" {{ $status == 'spare' ? 'selected' : '' }}>เครื่องสำรอง</option>
                    <option value="repairing" {{ $status == 'repairing' ? 'selected' : '' }}>กำลังส่งซ่อม</option>
                    <option value="broken" {{ $status == 'broken' ? 'selected' : '' }}>ชำรุดรอซ่อม</option>
                    <option value="disposed" {{ $status == 'disposed' ? 'selected' : '' }}>รอจำหน่าย</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> กรองข้อมูล
                </button>
            </div>

            <div style="display: flex; gap: 10px;">
                <a href="{{ route('reports.export', 'assets') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export CSV
                </a>
                <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> สั่งพิมพ์รายงาน
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Header -->
<div style="text-align: center; margin-bottom: 24px;">
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">รายงานสถานะและการกระจายตัวของครุภัณฑ์คอมพิวเตอร์</h2>
    <div style="font-size: 15px; color: var(--text-muted); font-weight: 500;">
        โรงพยาบาลทุ่งหัวช้าง @if(auth()->user()->isUser()) &bull; <strong>{{ auth()->user()->department->name ?? 'แผนกของท่าน' }}</strong> @endif &bull; ข้อมูล ณ วันที่ {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} น.
    </div>
</div>

<!-- Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center;">
        <div style="font-size: 12.5px; color: var(--text-muted);">ครุภัณฑ์ทั้งหมด</div>
        <div style="font-size: 26px; font-weight: 700; color: var(--text-main); margin-top: 4px;">{{ $totalCount }}</div>
        <div style="font-size: 11.5px; color: #64748b;">เครื่อง/รายการ</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #ecfdf5; border-color: #a7f3d0;">
        <div style="font-size: 12.5px; color: #065f46;">ใช้งานปกติ (Active)</div>
        <div style="font-size: 26px; font-weight: 700; color: #047857; margin-top: 4px;">{{ $statusCounts['active'] }}</div>
        <div style="font-size: 11.5px; color: #065f46;">
            {{ $totalCount > 0 ? round(($statusCounts['active'] / $totalCount) * 100, 1) : 0 }}% พร้อมใช้
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #fef2f2; border-color: #fecaca;">
        <div style="font-size: 12.5px; color: #991b1b;">ชำรุด / ส่งซ่อม</div>
        <div style="font-size: 26px; font-weight: 700; color: #b91c1c; margin-top: 4px;">{{ $statusCounts['repairing'] + $statusCounts['broken'] }}</div>
        <div style="font-size: 11.5px; color: #991b1b;">ต้องการการซ่อมบำรุง</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #f0fdfa; border-color: #99f6e4;">
        <div style="font-size: 12.5px; color: #0f766e;">มูลค่าจัดซื้อรวม</div>
        <div style="font-size: 26px; font-weight: 700; color: #0d9488; margin-top: 4px;">{{ number_format($totalValue, 2) }}</div>
        <div style="font-size: 11.5px; color: #0f766e;">บาท</div>
    </div>
</div>

<!-- Asset Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="bi bi-table"></i>
            <span>รายการครุภัณฑ์คอมพิวเตอร์ ({{ $assets->count() }} รายการ)</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>รหัสครุภัณฑ์</th>
                        <th>ชื่อรายการ / รุ่น</th>
                        <th>ประเภท</th>
                        <th>แผนก / จุดวาง</th>
                        <th>ผู้ครอบครอง</th>
                        <th>สถานะ</th>
                        <th style="text-align: right;">ราคาจัดซื้อ (บาท)</th>
                        <th>ปีงบประมาณ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                            {{ $asset->asset_code }}
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $asset->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $asset->brand }} {{ $asset->model }}</div>
                        </td>
                        <td>{{ $asset->deviceType?->name }}</td>
                        <td>
                            <div>{{ $asset->department?->name }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $asset->location_detail }}</div>
                        </td>
                        <td>{{ $asset->custodian_name ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $asset->status_badge }}">
                                {{ $asset->status_label }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            {{ $asset->price ? number_format($asset->price, 2) : '-' }}
                        </td>
                        <td>{{ $asset->budget_year ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            ไม่พบข้อมูลครุภัณฑ์ตามเงื่อนไข
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
