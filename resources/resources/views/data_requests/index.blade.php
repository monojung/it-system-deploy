@extends('layouts.app')

@section('title', 'ระบบขอข้อมูลสารสนเทศทางการแพทย์และสถิติ')
@section('page-title', 'ขอข้อมูลสารสนเทศทางการแพทย์ & สถิติ')
@section('breadcrumb', 'ระบบขอข้อมูลสารสนเทศ')

@section('topbar-actions')
<a href="{{ route('data-requests.export', request()->query()) }}" class="topbar-btn">
    <i class="bi bi-file-earmark-excel"></i>
    <span>ส่งออก CSV</span>
</a>
<a href="{{ route('data-requests.create') }}" class="topbar-btn topbar-btn-primary">
    <i class="bi bi-plus-circle"></i>
    <span>ยื่นคำขอข้อมูลใหม่</span>
</a>
@endsection

@section('content')
<div class="content-container">
    <!-- Stat Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted);">คำขอทั้งหมด</div>
                    <div style="font-size: 26px; font-weight: 700; color: var(--text-main); margin-top: 4px;">{{ number_format($stats['total']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f0fdfa; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted);">รอพิจารณา</div>
                    <div style="font-size: 26px; font-weight: 700; color: #d97706; margin-top: 4px;">{{ number_format($stats['pending']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted);">กำลังประมวลผล</div>
                    <div style="font-size: 26px; font-weight: 700; color: #0284c7; margin-top: 4px;">{{ number_format($stats['in_progress']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f0f9ff; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 0; padding: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted);">สกัดข้อมูลเสร็จสิ้น</div>
                    <div style="font-size: 26px; font-weight: 700; color: #16a34a; margin-top: 4px;">{{ number_format($stats['completed']) }}</div>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-body" style="padding: 18px 24px;">
            <form action="{{ route('data-requests.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
                <div style="flex: 1; min-width: 240px;">
                    <label class="form-label" style="font-size: 12.5px; margin-bottom: 4px;">ค้นหาคำขอ</label>
                    <input type="text" name="search" class="form-control" placeholder="รหัสคำขอ, หัวข้อข้อมูล, เงื่อนไข..." value="{{ request('search') }}">
                </div>

                <div style="min-width: 160px;">
                    <label class="form-label" style="font-size: 12.5px; margin-bottom: 4px;">สถานะ</label>
                    <select name="status" class="form-select">
                        <option value="">-- ทุกสถานะ --</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอพิจารณา</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>อนุมัติแล้ว</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>กำลังประมวลผล</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>ปฏิเสธคำขอ</option>
                    </select>
                </div>

                <div style="min-width: 140px;">
                    <label class="form-label" style="font-size: 12.5px; margin-bottom: 4px;">ความเร่งด่วน</label>
                    <select name="urgency" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="normal" {{ request('urgency') === 'normal' ? 'selected' : '' }}>ปกติ</option>
                        <option value="urgent" {{ request('urgency') === 'urgent' ? 'selected' : '' }}>ด่วน</option>
                        <option value="very_urgent" {{ request('urgency') === 'very_urgent' ? 'selected' : '' }}>ด่วนที่สุด</option>
                    </select>
                </div>

                @if(!auth()->user()->isUser())
                <div style="min-width: 180px;">
                    <label class="form-label" style="font-size: 12.5px; margin-bottom: 4px;">แผนกผู้ขอ</label>
                    <select name="department_id" class="form-select">
                        <option value="">-- ทุกแผนก --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">


                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                    @if(request()->hasAny(['search', 'status', 'urgency', 'department_id']))
                        <a href="{{ route('data-requests.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> ล้าง
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div class="card-title" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-list-task text-primary"></i>
                <span>รายการคำขอข้อมูลสารสนเทศ</span>
                <span class="badge badge-secondary" style="font-size: 12px;">{{ $requests->total() }} รายการ</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 130px;">รหัสคำขอ</th>
                        <th>หัวข้อข้อมูลสารสนเทศที่ต้องการ</th>
                        <th style="width: 150px;">ผู้ยื่นคำขอ</th>
                        <th style="width: 140px;">วัตถุประสงค์</th>
                        <th style="width: 120px;">รูปแบบไฟล์</th>
                        <th style="width: 100px;">ความเร่งด่วน</th>
                        <th style="width: 110px;">สถานะ</th>
                        <th style="width: 120px; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                <a href="{{ route('data-requests.show', $req->id) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                    {{ $req->request_no }}
                                </a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                    <a href="{{ route('data-requests.show', $req->id) }}" style="color: inherit; text-decoration: none;">
                                        {{ $req->title }}
                                    </a>
                                    @if($req->sample_file)
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 10.5px; padding: 2px 6px;" title="มีไฟล์ตัวอย่างแนบมา">
                                            <i class="bi bi-paperclip"></i> ตัวอย่าง
                                        </span>
                                    @endif
                                    @if($req->re_request_from_id)
                                        <span class="badge" style="background: #f0fdf4; color: #166534; font-size: 10px; padding: 2px 5px;" title="สร้างจากการขอซ้ำ">
                                            <i class="bi bi-copy"></i> ขอซ้ำ
                                        </span>
                                    @endif
                                </div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    @if($req->data_start_date && $req->data_end_date)
                                        <i class="bi bi-calendar3"></i> ช่วงข้อมูล: {{ $req->data_start_date->format('d/m/Y') }} - {{ $req->data_end_date->format('d/m/Y') }}
                                    @elseif($req->data_start_date)
                                        <i class="bi bi-calendar3"></i> ตั้งแต่: {{ $req->data_start_date->format('d/m/Y') }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 500;">{{ $req->user->name ?? 'ไม่ระบุ' }}</div>
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ $req->department->name ?? 'ไม่ระบุแผนก' }}</div>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: #475569;">{{ $req->objective_label }}</span>
                            </td>
                            <td>
                                <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; padding: 2px 8px; border-radius: 4px; background: #f1f5f9;">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> {{ $req->file_format }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $req->urgency_badge }}">{{ $req->urgency_label }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $req->status_badge }}">{{ $req->status_label }}</span>
                                @if($req->status === 'completed' && $req->download_count > 0)
                                    <div style="font-size: 10.5px; color: #166534; margin-top: 2px;">
                                        <i class="bi bi-check2-all"></i> โหลดแล้ว ({{ $req->download_count }})
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <a href="{{ route('data-requests.show', $req->id) }}" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" title="ดูรายละเอียด">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('data-requests.create', ['clone_id' => $req->id]) }}" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px; color: #166534;" title="ขอซ้ำจากคำขอนี้">
                                        <i class="bi bi-copy"></i>
                                    </a>
                                    <a href="{{ route('data-requests.print', $req->id) }}" target="_blank" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;" title="พิมพ์ใบคำขอ">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    @if($req->status === 'completed' && $req->result_file)
                                        <a href="{{ route('data-requests.download', $req->id) }}" class="btn btn-primary" style="padding: 4px 8px; font-size: 12px;" title="ดาวน์โหลดไฟล์ข้อมูล">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                ไม่พบรายการคำขอข้อมูลสารสนเทศ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 16px 24px; border-top: 1px solid var(--border); background: #ffffff;">
            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
