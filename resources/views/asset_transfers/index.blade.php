@extends('layouts.app')

@section('title', 'ระบบย้ายครุภัณฑ์และย้ายจุดติดตั้งอุปกรณ์ IT')
@section('page_title', 'ระบบย้ายครุภัณฑ์และย้ายจุดติดตั้งอุปกรณ์')
@section('page_subtitle', 'บันทึกประวัติการเคลื่อนย้าย โอนย้ายหน่วยงาน และเปลี่ยนจุดติดตั้งครุภัณฑ์คอมพิวเตอร์ โรงพยาบาลทุ่งหัวช้าง')

@section('topbar-actions')
<a href="{{ route('asset-transfers.create') }}" class="topbar-btn topbar-btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
    <i class="bi bi-arrows-move"></i>
    <span>บันทึก/ขอย้ายจุดติดตั้ง</span>
</a>
@endsection

@section('content')
<div class="content-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div class="header-left" style="display: flex; align-items: center; gap: 14px;">
        <div class="header-icon" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.25);">
            <i class="bi bi-arrows-move"></i>
        </div>
        <div>
            <h1 class="header-title" style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">ระบบย้ายครุภัณฑ์และย้ายจุดติดตั้งอุปกรณ์ IT</h1>
            <p class="header-subtitle" style="font-size: 13px; color: #64748b; margin: 3px 0 0;">
                บันทึกการเคลื่อนย้าย ย้ายห้อง ย้ายโต๊ะทำงาน โอนย้ายหน่วยงาน และอัปเดตตำแหน่งครุภัณฑ์อัตโนมัติ
            </p>
        </div>
    </div>
    <div class="header-right" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('asset-transfers.export', request()->query()) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-file-earmark-excel"></i>
            <span>ส่งออก CSV</span>
        </a>
        <a href="{{ route('asset-transfers.create') }}" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px; font-weight: 600; background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); border-color: #6366f1;">
            <i class="bi bi-plus-circle"></i>
            <span>บันทึก/ขอย้ายจุดติดตั้ง</span>
        </a>
    </div>
</div>

<div class="content-container">

    <!-- Metrics Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Card 1: Total --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid #e2e8f0; background: #ffffff; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">รายการย้ายทั้งหมด</div>
                    <div style="font-size: 26px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ number_format($metrics['total']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-arrows-move"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #94a3b8; margin-top: 8px;">ประวัติการย้ายในระบบ</div>
        </div>

        {{-- Card 2: Pending --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid {{ $metrics['pending'] > 0 ? '#fde047' : '#e2e8f0' }}; background: {{ $metrics['pending'] > 0 ? 'linear-gradient(to bottom, #ffffff, #fefce8)' : '#ffffff' }}; box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #854d0e; font-weight: 600;">รอช่างดำเนินการ</div>
                    <div style="font-size: 26px; font-weight: 800; color: #ca8a04; margin-top: 4px;">{{ number_format($metrics['pending']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef9c3; color: #ca8a04; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #a16207; margin-top: 8px;">รอเข้าดำเนินการหน้างาน</div>
        </div>

        {{-- Card 3: In Progress --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid #bae6fd; background: linear-gradient(to bottom, #ffffff, #f0f9ff); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #0369a1; font-weight: 600;">กำลังดำเนินการย้าย</div>
                    <div style="font-size: 26px; font-weight: 800; color: #0284c7; margin-top: 4px;">{{ number_format($metrics['in_progress']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-tools"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #0284c7; margin-top: 8px;">อยู่ระหว่างถอดประกอบ/ติดตั้ง</div>
        </div>

        {{-- Card 4: Completed --}}
        <div class="card" style="margin-bottom: 0; padding: 20px; border-radius: 14px; border: 1px solid #bbf7d0; background: linear-gradient(to bottom, #ffffff, #f0fdf4); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: #166534; font-weight: 600;">ย้ายสำเร็จเรียบร้อย</div>
                    <div style="font-size: 26px; font-weight: 800; color: #16a34a; margin-top: 4px;">{{ number_format($metrics['completed']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <div style="font-size: 11.5px; color: #16a34a; margin-top: 8px;">ติดตั้งและอัปเดตครุภัณฑ์แล้ว</div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card" style="margin-bottom: 24px; padding: 18px 20px;">
        <form method="GET" action="{{ route('asset-transfers.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto; gap: 14px; align-items: end;">
            <div>
                <label class="form-label" style="font-size: 12.5px; margin-bottom: 5px;">ค้นหาคำค้น / รหัสครุภัณฑ์</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="เลขที่ย้าย, รหัส, ซีเรียล, ผู้รับผิดชอบ..." value="{{ request('search') }}">
                </div>
            </div>

            <div>
                <label class="form-label" style="font-size: 12.5px; margin-bottom: 5px;">สถานะการดำเนินงาน</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- ทุกสถานะ --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอเจ้าหน้าที่ดำเนินการ</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการย้าย</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>ย้ายเสร็จสมบูรณ์</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 12.5px; margin-bottom: 5px;">ประเภทการย้าย</label>
                <select name="transfer_type" class="form-select form-select-sm">
                    <option value="">-- ทุกประเภท --</option>
                    <option value="relocation" {{ request('transfer_type') === 'relocation' ? 'selected' : '' }}>ย้ายจุดติดตั้ง/ห้อง</option>
                    <option value="department_transfer" {{ request('transfer_type') === 'department_transfer' ? 'selected' : '' }}>โอนย้ายหน่วยงาน/ผู้ครอบครอง</option>
                    <option value="temporary_move" {{ request('transfer_type') === 'temporary_move' ? 'selected' : '' }}>ย้ายใช้งานชั่วคราว</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="font-size: 12.5px; margin-bottom: 5px;">หน่วยงาน/แผนก</label>
                <select name="department_id" class="form-select form-select-sm">
                    <option value="">-- ทุกหน่วยงาน --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 16px;">
                    <i class="bi bi-search"></i> ค้นหา
                </button>
                @if(request()->hasAny(['search', 'status', 'transfer_type', 'department_id', 'date_from', 'date_to']))
                <a href="{{ route('asset-transfers.index') }}" class="btn btn-secondary btn-sm" title="ล้างตัวกรอง" style="padding: 8px 12px;">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transfers Table Card -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="table" style="margin: 0; vertical-align: middle;">
                <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <tr>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569;">เลขที่เอกสาร</th>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569;">ครุภัณฑ์ IT</th>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569; min-width: 220px;">การเคลื่อนย้าย (จุดเดิม &rarr; จุดใหม่)</th>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569;">ผู้รับผิดชอบใหม่</th>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569;">วันที่ย้าย</th>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569;">สถานะ</th>
                        <th style="padding: 14px 16px; font-size: 12.5px; font-weight: 700; color: #475569; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                        {{-- Transfer No & Type --}}
                        <td style="padding: 14px 16px;">
                            <a href="{{ route('asset-transfers.show', $transfer) }}" style="font-weight: 800; color: #6366f1; text-decoration: none; font-family: monospace; font-size: 13.5px;">
                                {{ $transfer->transfer_no }}
                            </a>
                            <div style="margin-top: 4px;">
                                {!! $transfer->transfer_type_badge !!}
                            </div>
                        </td>

                        {{-- Asset Details --}}
                        <td style="padding: 14px 16px;">
                            @if($transfer->asset)
                                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">
                                    <a href="{{ route('assets.show', $transfer->asset) }}" style="color: inherit; text-decoration: none;">
                                        {{ $transfer->asset->asset_code }}
                                    </a>
                                </div>
                                <div style="font-size: 12px; color: #475569;">
                                    {{ $transfer->asset->name }}
                                </div>
                                <div style="font-size: 11.5px; color: #94a3b8;">
                                    {{ $transfer->asset->brand }} {{ $transfer->asset->model }}
                                    @if($transfer->asset->serial_number) &bull; S/N: {{ $transfer->asset->serial_number }} @endif
                                </div>
                            @else
                                <span class="text-muted">ครุภัณฑ์ถูกลบแล้ว</span>
                            @endif
                        </td>

                        {{-- From -> To Movement --}}
                        <td style="padding: 14px 16px;">
                            <div style="display: flex; align-items: flex-start; gap: 8px;">
                                <div style="font-size: 12px; flex: 1;">
                                    <div style="color: #94a3b8; font-size: 10.5px; text-transform: uppercase; font-weight: 700;">จุดเดิม:</div>
                                    <div style="color: #475569; font-weight: 600;">
                                        {{ $transfer->fromDepartment ? $transfer->fromDepartment->name : ($transfer->from_department_name ?: 'ไม่ระบุ') }}
                                    </div>
                                    <div style="color: #64748b; font-size: 11.5px;">
                                        {{ $transfer->from_location_detail ?: '-' }}
                                    </div>
                                </div>

                                <div style="color: #6366f1; font-size: 16px; padding-top: 8px; flex-shrink: 0;">
                                    <i class="bi bi-arrow-right"></i>
                                </div>

                                <div style="font-size: 12px; flex: 1;">
                                    <div style="color: #6366f1; font-size: 10.5px; text-transform: uppercase; font-weight: 700;">จุดใหม่:</div>
                                    <div style="color: #0f172a; font-weight: 700;">
                                        {{ $transfer->toDepartment ? $transfer->toDepartment->name : ($transfer->to_department_name ?: 'ไม่ระบุ') }}
                                    </div>
                                    <div style="color: #334155; font-size: 11.5px; font-weight: 500;">
                                        {{ $transfer->to_location_detail ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Custodian --}}
                        <td style="padding: 14px 16px; font-size: 12.5px;">
                            <div style="font-weight: 600; color: #0f172a;">
                                <i class="bi bi-person text-muted me-1"></i>{{ $transfer->to_custodian_name ?: '-' }}
                            </div>
                            @if($transfer->to_ip_address)
                                <div style="font-size: 11px; color: #0284c7; font-family: monospace; margin-top: 2px;">
                                    IP: {{ $transfer->to_ip_address }}
                                </div>
                            @endif
                        </td>

                        {{-- Transfer Date --}}
                        <td style="padding: 14px 16px; font-size: 12.5px; color: #334155; white-space: nowrap;">
                            <div>{{ $transfer->transfer_date ? $transfer->transfer_date->format('d/m/Y') : '-' }}</div>
                            <div style="font-size: 11px; color: #94a3b8;">
                                โดย {{ $transfer->user?->name ?: 'ระบบ' }}
                            </div>
                        </td>

                        {{-- Status --}}
                        <td style="padding: 14px 16px; white-space: nowrap;">
                            {!! $transfer->status_badge !!}
                            @if($transfer->technician)
                                <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                    <i class="bi bi-wrench me-1"></i>{{ $transfer->technician->name }}
                                </div>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td style="padding: 14px 16px; text-align: center; white-space: nowrap;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <a href="{{ route('asset-transfers.show', $transfer) }}" class="btn btn-secondary btn-sm" title="ดูรายละเอียด">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('asset-transfers.print', $transfer) }}" target="_blank" class="btn btn-secondary btn-sm" title="พิมพ์ใบย้าย/ส่งมอบ">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 40px 20px;">
                            <i class="bi bi-arrows-move" style="font-size: 36px; color: #cbd5e1; display: block; margin-bottom: 10px;"></i>
                            <div style="font-size: 15px; font-weight: 600; color: #475569;">ยังไม่มีประวัติการย้ายจุดติดตั้งครุภัณฑ์</div>
                            <p style="font-size: 13px; color: #94a3b8; margin: 4px 0 16px;">
                                สามารถบันทึกการย้ายเครื่อง หรือยื่นคำขอย้ายจุดติดตั้งครุภัณฑ์ได้ทันที
                            </p>
                            <a href="{{ route('asset-transfers.create') }}" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); border-color: #6366f1;">
                                <i class="bi bi-plus-circle"></i> บันทึกการย้ายครุภัณฑ์รายการแรก
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0;">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
