@extends('layouts.app')

@section('title', 'ระบบขอยืม-คืนอุปกรณ์ไอที สำหรับโรงพยาบาล')
@section('page_title', 'ระบบขอยืม-คืนอุปกรณ์ไอที')
@section('page_subtitle', 'บริหารจัดการยืม-คืนเครื่องคอมพิวเตอร์ โน้ตบุ๊ก โปรเจคเตอร์ และอุปกรณ์สารสนเทศ โรงพยาบาลทุ่งหัวช้าง')

@section('topbar-actions')
<a href="{{ route('asset-borrows.create') }}" class="topbar-btn topbar-btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
    <i class="bi bi-plus-circle"></i>
    <span>ยื่นคำขอยืมอุปกรณ์ใหม่</span>
</a>
@endsection

@section('content')
<div class="content-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div class="header-left" style="display: flex; align-items: center; gap: 14px;">
        <div class="header-icon" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
            <i class="bi bi-arrow-left-right"></i>
        </div>
        <div>
            <h1 class="header-title" style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">ระบบขอยืม-คืนอุปกรณ์และครุภัณฑ์ IT</h1>
            <p class="header-subtitle" style="font-size: 13px; color: #64748b; margin: 3px 0 0;">
                บริหารจัดการยืม-คืนเครื่องคอมพิวเตอร์ โน้ตบุ๊ก โปรเจคเตอร์ และอุปกรณ์สารสนเทศ โรงพยาบาลทุ่งหัวช้าง
            </p>
        </div>
    </div>
    <div class="header-right">
        <a href="{{ route('asset-borrows.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px; font-weight: 600;">
            <i class="bi bi-plus-circle"></i>
            <span>ยื่นคำขอยืมอุปกรณ์ใหม่</span>
        </a>
    </div>
</div>

<div class="content-container">

    <!-- Stat Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Card 1: Total --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">คำขอยืมทั้งหมด</div>
                    <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ number_format($totalCount) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 8px;">ประวัติการยืมในระบบ</div>
        </div>

        {{-- Card 2: Pending Approval --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid {{ $pendingCount > 0 ? '#fde047' : '#e2e8f0' }}; background: {{ $pendingCount > 0 ? 'linear-gradient(to bottom, #ffffff, #fefce8)' : '#ffffff' }}; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #854d0e; font-weight: 600;">รอเจ้าหน้าที่อนุมัติ</div>
                    <div style="font-size: 26px; font-weight: 800; color: #ca8a04; margin-top: 4px;">{{ number_format($pendingCount) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef9c3; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #a16207; margin-top: 8px;">รอตรวจความพร้อมอุปกรณ์</div>
        </div>

        {{-- Card 3: Active Borrowed --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid #ccfbf1; background: linear-gradient(to bottom, #ffffff, #f0fdfa); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #0f766e; font-weight: 600;">กำลังยืมใช้งานอยู่</div>
                    <div style="font-size: 26px; font-weight: 800; color: #0d9488; margin-top: 4px;">{{ number_format($activeBorrowedCount) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #ccfbf1; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #0f766e; margin-top: 8px;">ส่งมอบแล้ว รอนำส่งคืน</div>
        </div>

        {{-- Card 4: Overdue --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid {{ $overdueCount > 0 ? '#fecaca' : '#e2e8f0' }}; background: {{ $overdueCount > 0 ? 'linear-gradient(to bottom, #ffffff, #fff5f5)' : '#ffffff' }}; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #991b1b; font-weight: 600;">เกินกำหนดส่งคืน</div>
                    <div style="font-size: 26px; font-weight: 800; color: #dc2626; margin-top: 4px;">{{ number_format($overdueCount) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #b91c1c; margin-top: 8px;">ต้องติดตามทวงถาม</div>
        </div>

        {{-- Card 5: Returned this month --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid #bbf7d0; background: linear-gradient(to bottom, #ffffff, #f0fdf4); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #166534; font-weight: 600;">คืนสำเร็จในเดือนนี้</div>
                    <div style="font-size: 26px; font-weight: 800; color: #16a34a; margin-top: 4px;">{{ number_format($returnedThisMonthCount) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #15803d; margin-top: 8px;">ตรวจสอบสภาพเรียบร้อย</div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card" style="margin-bottom: 22px; border-radius: 14px; border: 1px solid #e2e8f0; box-shadow: var(--shadow-sm);">
        <div class="card-body" style="padding: 18px 22px;">
            <form action="{{ route('asset-borrows.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                <div style="flex: 1; min-width: 240px;">
                    <label style="font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px; display: block;">ค้นหาคำขอยืม</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="รหัสคำขอ, ชื่อผู้ยืม, ครุภัณฑ์, S/N..." value="{{ request('search') }}">
                </div>

                <div style="min-width: 170px;">
                    <label style="font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px; display: block;">สถานะคำขอ</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">ทุกสถานะ</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รออนุมัติ (Pending)</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว (Approved)</option>
                        <option value="borrowed" {{ request('status') === 'borrowed' ? 'selected' : '' }}>กำลังยืมใช้งาน (Borrowed)</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>เกินกำหนดส่งคืน (Overdue)</option>
                        <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>ส่งคืนแล้ว (Returned)</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธ (Rejected)</option>
                    </select>
                </div>

                <div style="min-width: 190px;">
                    <label style="font-size: 12.5px; font-weight: 600; color: #475569; margin-bottom: 5px; display: block;">แผนก / กลุ่มงาน</label>
                    <select name="department_id" class="form-select form-select-sm">
                        <option value="">ทุกกลุ่มงาน / แผนก</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 5px; height: 33px;">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                    @if(request()->hasAny(['search', 'status', 'department_id']))
                        <a href="{{ route('asset-borrows.index') }}" class="btn btn-sm btn-outline-secondary" style="height: 33px; display: inline-flex; align-items: center;">
                            ล้างค่า
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Borrow Requests -->
    <div class="card" style="border-radius: 14px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-sm);">
        <div class="card-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 16px 22px; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-list-ul" style="color: var(--primary);"></i>
                <span>รายการขอยืม-คืนอุปกรณ์ไอที (ทั้งหมด {{ number_format($borrows->total()) }} รายการ)</span>
            </div>
            <a href="{{ route('asset-borrows.create') }}" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 5px;">
                <i class="bi bi-plus-lg"></i> ยื่นคำขอใหม่
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                    <tr>
                        <th style="width: 140px;">รหัสคำขอ</th>
                        <th>อุปกรณ์ที่ยืม (IT Asset)</th>
                        <th>ผู้ขอยืม & แผนก</th>
                        <th>ระยะเวลายืม-คืน</th>
                        <th>วัตถุประสงค์ & อุปกรณ์เสริม</th>
                        <th style="text-align: center;">สถานะ</th>
                        <th style="text-align: right; width: 140px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrows as $borrow)
                    <tr style="{{ $borrow->is_overdue ? 'background: #fff5f5;' : '' }}">
                        {{-- 1. Borrow No & Date --}}
                        <td>
                            <a href="{{ route('asset-borrows.show', $borrow->id) }}" style="font-weight: 700; color: var(--primary); text-decoration: none; font-family: monospace; font-size: 13.5px;">
                                {{ $borrow->borrow_no }}
                            </a>
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">
                                ยื่นเมื่อ: {{ $borrow->created_at ? $borrow->created_at->format('d/m/Y H:i') : '-' }}
                            </div>
                        </td>

                        {{-- 2. Asset Info --}}
                        <td>
                            @if($borrow->asset)
                                <div style="font-weight: 700; color: #0f172a;">
                                    {{ $borrow->asset->name }}
                                </div>
                                <div style="font-size: 11.5px; color: #475569; display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                                    @if($borrow->asset->brand || $borrow->asset->model)
                                        <span class="badge bg-light text-dark border px-1 py-0" style="font-size: 10.5px;">
                                            {{ $borrow->asset->brand }} {{ $borrow->asset->model }}
                                        </span>
                                    @endif
                                    <code>{{ $borrow->asset->asset_code }}</code>
                                    @if($borrow->asset->serial_number)
                                        <span style="color: #94a3b8;">(S/N: {{ $borrow->asset->serial_number }})</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">(ไม่พบข้อมูลอุปกรณ์)</span>
                            @endif
                        </td>

                        {{-- 3. Requester & Dept --}}
                        <td>
                            <div style="font-weight: 600; color: #1e293b;">
                                <i class="bi bi-person-fill text-muted me-1"></i>{{ $borrow->borrower_name }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                {{ $borrow->department?->name ?? 'ไม่ระบุแผนก' }}
                                @if($borrow->contact_phone)
                                    &bull; โทร. {{ $borrow->contact_phone }}
                                @endif
                            </div>
                        </td>

                        {{-- 4. Borrow Timeline --}}
                        <td>
                            <div style="font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 4px;">
                                <span>{{ $borrow->borrow_date ? $borrow->borrow_date->format('d/m/Y') : '-' }}</span>
                                <i class="bi bi-arrow-right text-muted" style="font-size: 11px;"></i>
                                <span style="{{ $borrow->is_overdue ? 'color: #dc2626; font-weight: 700;' : '' }}">
                                    {{ $borrow->expected_return_date ? $borrow->expected_return_date->format('d/m/Y') : '-' }}
                                </span>
                            </div>
                            <div style="font-size: 11px; color: #64748b; margin-top: 2px;">
                                รวม {{ $borrow->duration_days }} วัน
                                @if($borrow->status === 'returned' && $borrow->actual_return_date)
                                    <span style="color: #16a34a;">(คืนแล้ว: {{ $borrow->actual_return_date->format('d/m/Y') }})</span>
                                @elseif($borrow->is_overdue)
                                    <span style="color: #dc2626; font-weight: 700;">(เกินกำหนด {{ abs(now()->diffInDays($borrow->expected_return_date)) }} วัน)</span>
                                @endif
                            </div>
                        </td>

                        {{-- 5. Purpose & Accessories --}}
                        <td style="max-width: 250px;">
                            <div style="font-weight: 500; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $borrow->purpose }}">
                                {{ $borrow->purpose }}
                            </div>
                            @if($borrow->location_used)
                                <div style="font-size: 11px; color: #64748b;">
                                    <i class="bi bi-geo-alt me-1"></i>สถานที่: {{ $borrow->location_used }}
                                </div>
                            @endif
                            @if(!empty($borrow->accessories))
                                <div style="font-size: 10.5px; color: #0284c7; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="อุปกรณ์เสริม: {{ $borrow->accessories_text }}">
                                    <i class="bi bi-box-seam me-1"></i>{{ $borrow->accessories_text }}
                                </div>
                            @endif
                        </td>

                        {{-- 6. Status --}}
                        <td style="text-align: center; white-space: nowrap;">
                            {!! $borrow->status_badge !!}
                        </td>

                        {{-- 7. Actions --}}
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 4px; align-items: center;">
                                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()) && $borrow->status === 'pending')
                                    <form action="{{ route('asset-borrows.approve', $borrow->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันอนุมัติคำขอยืมอุปกรณ์ {{ $borrow->borrow_no }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" style="padding: 4px 8px; font-size: 12px;" title="อนุมัติคำขอยืมทันที">
                                            <i class="bi bi-check-lg"></i> อนุมัติ
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('asset-borrows.show', $borrow->id) }}" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียดและดำเนินการ">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('asset-borrows.print', $borrow->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="พิมพ์ใบยืม-คืน A4">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @if(in_array($borrow->status, ['pending']) || (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician())))
                                    <a href="{{ route('asset-borrows.edit', $borrow->id) }}" class="btn btn-sm btn-outline-warning" title="แก้ไขคำขอ">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px 16px;">
                            <div style="width: 64px; height: 64px; border-radius: 50%; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 14px;">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <div style="font-weight: 700; font-size: 16px; color: #334155;">ไม่พบรายการขอยืมอุปกรณ์ไอที</div>
                            <p style="color: #94a3b8; font-size: 13px; margin: 4px 0 16px;">คุณยังไม่มีคำขอยืมอุปกรณ์ หรือไม่พบรายการที่ตรงกับเงื่อนไขการค้นหา</p>
                            <a href="{{ route('asset-borrows.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                                <i class="bi bi-plus-lg"></i> ยื่นคำขอยืมอุปกรณ์เครื่องแรก
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($borrows->hasPages())
        <div class="card-footer" style="background: #ffffff; border-top: 1px solid #e2e8f0; padding: 14px 22px;">
            {{ $borrows->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
