@extends('layouts.app')

@section('title', 'รายการแจ้งซ่อม')
@section('page_title', 'ระบบแจ้งซ่อมและติดตามสถานะ')
@section('page_subtitle', 'ติดตามงานแจ้งซ่อมบำรุงอุปกรณ์คอมพิวเตอร์และระบบสารสนเทศ')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 14px;">
        <div class="card-title">
            <i class="bi bi-list-check text-primary" style="font-size: 20px;"></i>
            <span>รายการแจ้งซ่อมทั้งหมด ({{ $repairs->total() }} รายการ)</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('reports.export', 'repairs') }}" class="btn btn-secondary btn-sm" title="ส่งออกข้อมูลเป็น CSV">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('repairs.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i>
                <span>สร้างใบแจ้งซ่อม</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid var(--border);">
        <form action="{{ route('repairs.index') }}" method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; align-items: end;">
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ค้นหาคำ (Search)</label>
                    <input type="text" name="search" class="form-control" placeholder="เลขที่, อาการ, ผู้แจ้ง, รหัสครุภัณฑ์" value="{{ request('search') }}">
                </div>

                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">สถานะการซ่อม</label>
                    <select name="status" class="form-select">
                        <option value="">-- ทุกสถานะ --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>🟡 รอรับเรื่อง</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>🔵 กำลังดำเนินการ</option>
                        <option value="waiting_parts" {{ request('status') == 'waiting_parts' ? 'selected' : '' }}>🟠 รออะไหล่</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🟢 ซ่อมเสร็จสิ้น</option>
                        <option value="external" {{ request('status') == 'external' ? 'selected' : '' }}>🟣 ส่งซ่อมภายนอก</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>⚪ ยกเลิก</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">แผนกที่แจ้ง</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- ทุกแผนก --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ระดับความเร่งด่วน</label>
                    <select name="urgency" class="form-select">
                        <option value="">-- ทุกระดับ --</option>
                        <option value="low" {{ request('urgency') == 'low' ? 'selected' : '' }}>ปกติ (ทั่วไป)</option>
                        <option value="normal" {{ request('urgency') == 'normal' ? 'selected' : '' }}>ปานกลาง</option>
                        <option value="high" {{ request('urgency') == 'high' ? 'selected' : '' }}>ด่วน</option>
                        <option value="critical" {{ request('urgency') == 'critical' ? 'selected' : '' }}>ด่วนที่สุด (กระทบผู้ป่วย)</option>
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ช่างผู้รับผิดชอบ</label>
                    <select name="technician_id" class="form-select">
                        <option value="">-- ทุกคน --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ request('technician_id') == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">


                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="bi bi-funnel"></i> กรอง
                    </button>
                    <a href="{{ route('repairs.index') }}" class="btn btn-secondary" title="ล้างการค้นหา">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>เลขที่ใบแจ้งซ่อม</th>
                        <th>อาการเสีย / หัวข้อปัญหา</th>
                        <th>แผนก / จุดที่ตั้ง</th>
                        <th>ครุภัณฑ์ / อุปกรณ์</th>
                        <th>ความเร่งด่วน</th>
                        <th>สถานะ</th>
                        <th>ช่างผู้รับผิดชอบ</th>
                        <th>วันที่แจ้ง</th>
                        <th style="text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($repairs as $repair)
                    <tr>
                        <td>
                            <a href="{{ route('repairs.show', $repair) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                {{ $repair->ticket_number }}
                            </a>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 14px;">
                                <a href="{{ route('repairs.show', $repair) }}" style="color: inherit; text-decoration: none;">
                                    {{ $repair->title }}
                                </a>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                ผู้แจ้ง: {{ $repair->requester_name }} ({{ $repair->requester_phone }})
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;">{{ $repair->department?->name }}</div>
                            @if($repair->location_detail)
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $repair->location_detail }}</div>
                            @endif
                        </td>
                        <td>
                            @if($repair->asset)
                                <a href="{{ route('assets.show', $repair->asset) }}" style="font-weight: 500; color: #0284c7; text-decoration: none; font-size: 13px;">
                                    <i class="bi bi-pc"></i> {{ $repair->asset->asset_code }}
                                </a>
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ $repair->asset->name }}</div>
                            @elseif($repair->other_device_info)
                                <span style="font-size: 13px; color: #475569;">{{ $repair->other_device_info }}</span>
                            @else
                                <span style="color: #94a3b8; font-size: 12px;">ไม่ระบุ</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $repair->urgency_badge }}">
                                {{ $repair->urgency_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $repair->status_badge }}">
                                {{ $repair->status_label }}
                            </span>
                        </td>
                        <td>
                            @if($repair->technician)
                                <div style="font-size: 13px; font-weight: 500;">{{ $repair->technician->name }}</div>
                            @else
                                <span style="color: #f59e0b; font-size: 12.5px; font-weight: 500;">
                                    <i class="bi bi-clock"></i> รอรับเรื่อง
                                </span>
                            @endif
                        </td>
                        <td style="font-size: 12.5px; color: var(--text-muted); white-space: nowrap;">
                            {{ $repair->created_at->format('d/m/Y') }}
                            <div style="font-size: 11px;">{{ $repair->created_at->format('H:i') }} น.</div>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="{{ route('repairs.show', $repair) }}" class="btn btn-secondary btn-sm" title="ดูรายละเอียด">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('repairs.print', $repair) }}" target="_blank" class="btn btn-secondary btn-sm" title="พิมพ์ใบสั่งซ่อม">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="bi bi-inbox" style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
                            <div>ไม่พบรายการแจ้งซ่อมตามเงื่อนไขที่ระบุ</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 16px 24px; border-top: 1px solid var(--border); background: #ffffff;">
            {{ $repairs->links() }}
        </div>
    </div>
</div>
@endsection
