@extends('layouts.app')

@section('title', 'รายละเอียดการย้ายครุภัณฑ์ ' . $assetTransfer->transfer_no)
@section('page_title', 'บันทึกการย้ายครุภัณฑ์: ' . $assetTransfer->transfer_no)
@section('page_subtitle', $assetTransfer->transfer_type_label . ' • ' . ($assetTransfer->asset?->name ?? '-'))

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <a href="{{ route('asset-transfers.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> รายการย้ายครุภัณฑ์
        </a>
        <span style="font-family: monospace; font-size: 15px; font-weight: 800; color: #6366f1;">
            {{ $assetTransfer->transfer_no }}
        </span>
        {!! $assetTransfer->transfer_type_badge !!}
        {!! $assetTransfer->status_badge !!}
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('asset-transfers.print', $assetTransfer) }}" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 5px;">
            <i class="bi bi-printer"></i>
            <span>พิมพ์ใบย้าย/ส่งมอบ</span>
        </a>

        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
            @if($assetTransfer->status === 'pending')
            <form action="{{ route('asset-transfers.status', $assetTransfer) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันรับเรื่องและเริ่มดำเนินการย้ายอุปกรณ์?');">
                @csrf
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="btn btn-info btn-sm text-white" style="display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-tools"></i>
                    <span>รับเรื่อง/เริ่มดำเนินการย้าย</span>
                </button>
            </form>
            @endif

            @if(in_array($assetTransfer->status, ['pending', 'in_progress']))
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#completeTransferModal" style="display: inline-flex; align-items: center; gap: 5px;">
                <i class="bi bi-check2-circle"></i>
                <span>บันทึกผลการย้ายเสร็จสิ้น</span>
            </button>

            <form action="{{ route('asset-transfers.status', $assetTransfer) }}" method="POST" style="display: inline;" onsubmit="return confirm('ต้องการยกเลิกคำขอย้ายนี้ใช่หรือไม่?');">
                @csrf
                <input type="hidden" name="status" value="cancelled">
                <button type="submit" class="btn btn-outline-danger btn-sm" style="display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-x-circle"></i>
                    <span>ยกเลิกคำขอ</span>
                </button>
            </form>
            @endif

            @if(auth()->user()->isAdmin())
            <form action="{{ route('asset-transfers.destroy', $assetTransfer) }}" method="POST" style="display: inline;" onsubmit="return confirm('คุณแน่ใจว่าต้องการลบประวัติการย้ายนี้อย่างถาวร?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-secondary btn-sm" title="ลบรายการ">
                    <i class="bi bi-trash3"></i>
                </button>
            </form>
            @endif
        @endif
    </div>
</div>

<div class="content-container">

    {{-- Progression Stepper --}}
    <div class="card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; position: relative;">
            
            {{-- Step 1 --}}
            <div style="text-align: center; flex: 1; z-index: 2;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: #6366f1; color: #fff; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800; font-size: 16px; box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">ยื่นคำขอ/บันทึกการย้าย</div>
                <div style="font-size: 11.5px; color: #64748b;">{{ $assetTransfer->created_at->format('d/m/Y H:i') }} น.</div>
            </div>

            <div style="height: 3px; background: {{ in_array($assetTransfer->status, ['in_progress', 'completed']) ? '#6366f1' : '#e2e8f0' }}; flex: 1; margin: -20px 10px 0;"></div>

            {{-- Step 2 --}}
            <div style="text-align: center; flex: 1; z-index: 2;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: {{ in_array($assetTransfer->status, ['in_progress', 'completed']) ? '#0284c7' : '#e2e8f0' }}; color: {{ in_array($assetTransfer->status, ['in_progress', 'completed']) ? '#fff' : '#94a3b8' }}; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800; font-size: 16px;">
                    <i class="bi bi-tools"></i>
                </div>
                <div style="font-weight: 700; font-size: 13.5px; color: {{ in_array($assetTransfer->status, ['in_progress', 'completed']) ? '#0f172a' : '#94a3b8' }};">กำลังดำเนินการย้าย</div>
                <div style="font-size: 11.5px; color: #64748b;">
                    {{ $assetTransfer->technician ? $assetTransfer->technician->name : 'รอช่างเข้าดำเนินการ' }}
                </div>
            </div>

            <div style="height: 3px; background: {{ $assetTransfer->status === 'completed' ? '#10b981' : '#e2e8f0' }}; flex: 1; margin: -20px 10px 0;"></div>

            {{-- Step 3 --}}
            <div style="text-align: center; flex: 1; z-index: 2;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: {{ $assetTransfer->status === 'completed' ? '#10b981' : ($assetTransfer->status === 'cancelled' ? '#ef4444' : '#e2e8f0') }}; color: {{ in_array($assetTransfer->status, ['completed', 'cancelled']) ? '#fff' : '#94a3b8' }}; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; font-weight: 800; font-size: 16px;">
                    <i class="bi {{ $assetTransfer->status === 'cancelled' ? 'bi-x-lg' : 'bi-check2' }}"></i>
                </div>
                <div style="font-weight: 700; font-size: 13.5px; color: {{ $assetTransfer->status === 'completed' ? '#059669' : ($assetTransfer->status === 'cancelled' ? '#dc2626' : '#94a3b8') }};">
                    {{ $assetTransfer->status === 'cancelled' ? 'ยกเลิกคำขอ' : 'ย้ายและติดตั้งเสร็จสมบูรณ์' }}
                </div>
                <div style="font-size: 11.5px; color: #64748b;">
                    {{ $assetTransfer->completed_at ? $assetTransfer->completed_at->format('d/m/Y H:i') . ' น.' : '-' }}
                </div>
            </div>

        </div>
    </div>

    {{-- Side-by-side Visual Comparison: FROM vs TO --}}
    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px; margin-bottom: 24px; align-items: stretch;">
        
        {{-- Origin Card --}}
        <div class="card" style="margin-bottom: 0; border: 1.5px solid #cbd5e1; background: #f8fafc;">
            <div class="card-header" style="background: #f1f5f9; border-bottom: 1.5px solid #e2e8f0;">
                <div class="card-title" style="color: #475569;">
                    <i class="bi bi-geo-alt-fill text-danger"></i>
                    <span>จุดติดตั้งเดิม (From - Origin)</span>
                </div>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #64748b;">หน่วยงาน / แผนกเดิม:</div>
                    <div style="font-weight: 700; font-size: 15px; color: #1e293b;">
                        {{ $assetTransfer->fromDepartment ? $assetTransfer->fromDepartment->name : ($assetTransfer->from_department_name ?: 'ไม่ระบุ') }}
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #64748b;">จุดติดตั้งเดิม / ห้อง / โต๊ะ:</div>
                    <div style="font-weight: 600; font-size: 14px; color: #334155;">
                        {{ $assetTransfer->from_location_detail ?: 'ไม่ระบุรายละเอียดห้อง' }}
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #64748b;">ผู้ครอบครอง / ผู้ใช้งานเดิม:</div>
                    <div style="font-weight: 600; font-size: 14px; color: #334155;">
                        <i class="bi bi-person me-1 text-muted"></i>{{ $assetTransfer->from_custodian_name ?: 'ไม่ระบุ' }}
                    </div>
                </div>

                <div>
                    <div style="font-size: 12px; color: #64748b;">IP Address เดิม:</div>
                    <div style="font-weight: 600; font-size: 13.5px; color: #64748b; font-family: monospace;">
                        {{ $assetTransfer->from_ip_address ?: '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Directional Indicator --}}
        <div style="display: flex; align-items: center; justify-content: center;">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3);">
                <i class="bi bi-arrow-right"></i>
            </div>
        </div>

        {{-- Destination Card --}}
        <div class="card" style="margin-bottom: 0; border: 1.5px solid #86efac; background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);">
            <div class="card-header" style="background: #dcfce7; border-bottom: 1.5px solid #bbf7d0;">
                <div class="card-title" style="color: #166534;">
                    <i class="bi bi-geo-alt-fill text-success"></i>
                    <span>จุดติดตั้งใหม่ (To - Destination)</span>
                </div>
                @if($assetTransfer->status === 'completed')
                    <span class="badge badge-success" style="background: #15803d; color: #fff;">
                        <i class="bi bi-check-circle-fill me-1"></i>อัปเดตครุภัณฑ์แล้ว
                    </span>
                @endif
            </div>
            <div class="card-body">
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #166534; font-weight: 600;">หน่วยงาน / แผนกใหม่:</div>
                    <div style="font-weight: 800; font-size: 16px; color: #14532d;">
                        {{ $assetTransfer->toDepartment ? $assetTransfer->toDepartment->name : ($assetTransfer->to_department_name ?: 'ไม่ระบุ') }}
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #166534; font-weight: 600;">จุดติดตั้งใหม่ / ห้อง / โต๊ะ:</div>
                    <div style="font-weight: 700; font-size: 14.5px; color: #15803d;">
                        {{ $assetTransfer->to_location_detail ?: 'ไม่ระบุ' }}
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #166534; font-weight: 600;">ผู้ครอบครอง / ผู้ดูแลใหม่:</div>
                    <div style="font-weight: 700; font-size: 14px; color: #15803d;">
                        <i class="bi bi-person-check-fill me-1 text-success"></i>{{ $assetTransfer->to_custodian_name ?: 'ไม่ระบุ' }}
                    </div>
                </div>

                <div>
                    <div style="font-size: 12px; color: #166534; font-weight: 600;">IP Address ใหม่:</div>
                    <div style="font-weight: 700; font-size: 13.5px; color: #0284c7; font-family: monospace;">
                        {{ $assetTransfer->to_ip_address ?: ($assetTransfer->from_ip_address ?: 'DHCP / คงเดิม') }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Asset Information & Process Dossier --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        
        {{-- Asset Details --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-pc-display text-primary"></i>
                    <span>ข้อมูลครุภัณฑ์ที่ทำการย้าย</span>
                </div>
                @if($assetTransfer->asset)
                <a href="{{ route('assets.show', $assetTransfer->asset) }}" class="btn btn-secondary btn-sm" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> เปิดดูทะเบียนครุภัณฑ์
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($assetTransfer->asset)
                    <div style="font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                        {{ $assetTransfer->asset->asset_code }}
                    </div>
                    <div style="font-size: 14px; font-weight: 600; color: #475569; margin-bottom: 14px;">
                        {{ $assetTransfer->asset->name }} &bull; {{ $assetTransfer->asset->deviceType?->name }}
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12.5px; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                        <div>
                            <span class="text-muted">ยี่ห้อ / รุ่น:</span>
                            <div style="font-weight: 600; color: #1e293b;">{{ $assetTransfer->asset->brand }} {{ $assetTransfer->asset->model }}</div>
                        </div>
                        <div>
                            <span class="text-muted">Serial Number:</span>
                            <div style="font-weight: 600; color: #1e293b; font-family: monospace;">{{ $assetTransfer->asset->serial_number ?: '-' }}</div>
                        </div>
                        <div>
                            <span class="text-muted">สถานะครุภัณฑ์ปัจจุบัน:</span>
                            <div><span class="badge {{ $assetTransfer->asset->status_badge }}">{{ $assetTransfer->asset->status_label }}</span></div>
                        </div>
                        <div>
                            <span class="text-muted">ปีงบประมาณ:</span>
                            <div style="font-weight: 600; color: #1e293b;">{{ $assetTransfer->asset->budget_year ?: '-' }}</div>
                        </div>
                    </div>

                    @if($assetTransfer->asset->formatted_specs !== '-')
                    <div style="margin-top: 12px; font-size: 12px; color: #64748b; background: #f1f5f9; padding: 8px 12px; border-radius: 6px;">
                        <i class="bi bi-cpu me-1"></i>สเปค: {{ $assetTransfer->asset->formatted_specs }}
                    </div>
                    @endif
                @else
                    <div class="text-muted">ข้อมูลครุภัณฑ์ถูกลบออกจากระบบแล้ว</div>
                @endif
            </div>
        </div>

        {{-- Movement Details & Testing Results --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-clipboard2-check text-primary"></i>
                    <span>เหตุผลและผลการดำเนินงาน</span>
                </div>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #64748b;">เหตุผลความจำเป็นในการย้าย:</div>
                    <div style="font-size: 13.5px; color: #1e293b; font-weight: 500; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0; margin-top: 4px;">
                        {{ $assetTransfer->reason ?: '-' }}
                    </div>
                </div>

                @if($assetTransfer->notes)
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 12px; color: #64748b;">หมายเหตุเพิ่มเติม:</div>
                    <div style="font-size: 13px; color: #475569; margin-top: 2px;">
                        {{ $assetTransfer->notes }}
                    </div>
                </div>
                @endif

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 12.5px; margin-top: 16px; border-top: 1px dashed #e2e8f0; padding-top: 14px;">
                    <div>
                        <span class="text-muted">ผู้ทำรายการ / ยื่นคำขอ:</span>
                        <div style="font-weight: 600; color: #1e293b;">
                            <i class="bi bi-person me-1"></i>{{ $assetTransfer->user?->name ?: 'ระบบ' }}
                        </div>
                    </div>
                    <div>
                        <span class="text-muted">ช่าง IT ผู้ดำเนินการ:</span>
                        <div style="font-weight: 600; color: #0284c7;">
                            <i class="bi bi-wrench me-1"></i>{{ $assetTransfer->technician?->name ?: 'รอระบุช่าง' }}
                        </div>
                    </div>
                </div>

                @if($assetTransfer->status === 'completed')
                <div style="margin-top: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px;">
                    <div style="font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 4px;">
                        <i class="bi bi-check2-circle me-1"></i>ผลการทดสอบหลังติดตั้ง:
                    </div>
                    <div style="font-size: 13px; color: #14532d;">
                        {{ $assetTransfer->test_result ?: 'ตรวจสอบการเชื่อมต่อเครือข่ายและระบบสารสนเทศใช้งานได้ปกติ' }}
                    </div>

                    @if($assetTransfer->receiver_name)
                    <div style="font-size: 12px; color: #166534; margin-top: 8px; border-top: 1px dashed #bbf7d0; padding-top: 6px;">
                        ผู้รับมอบ ณ จุดติดตั้งใหม่: <strong>{{ $assetTransfer->receiver_name }}</strong>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

    </div>

</div>

{{-- Modal: Complete Transfer --}}
@if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
<div class="modal fade" id="completeTransferModal" tabindex="-1" aria-labelledby="completeTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; box-shadow: var(--shadow-lg);">
            <form action="{{ route('asset-transfers.status', $assetTransfer) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="completed">

                <div class="modal-header" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #fff; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title" id="completeTransferModalLabel" style="font-weight: 700; font-size: 17px;">
                        <i class="bi bi-check2-circle me-1"></i> บันทึกผลการย้ายและติดตั้งเสร็จสมบูรณ์
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="padding: 24px;">
                    <div class="alert alert-success" style="font-size: 13px; margin-bottom: 18px;">
                        <i class="bi bi-info-circle me-1"></i>
                        เมื่อบันทึกแล้ว ระบบจะอัปเดตหน่วยงานและจุดติดตั้งของครุภัณฑ์ <strong>{{ $assetTransfer->asset?->asset_code }}</strong> ให้ตรงกับจุดติดตั้งใหม่ทันที
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700;">ช่าง IT ผู้ดำเนินการ</label>
                        <select name="technician_id" class="form-select" required>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ ($assetTransfer->technician_id ?: auth()->id()) == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700;">ผลการทดสอบการใช้งานหลังติดตั้ง</label>
                        <textarea name="test_result" rows="2" class="form-control" required placeholder="เช่น เชื่อมต่อสาย LAN ใช้งานได้ปกติ, ทดสอบพิมพ์ได้, สามารถเข้าสู่ระบบ รพ. ได้สมบูรณ์">ตรวจสอบการเชื่อมต่อเครือข่ายและระบบสารสนเทศใช้งานได้ปกติ</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 700;">ชื่อผู้รับมอบ ณ จุดติดตั้งใหม่</label>
                        <input type="text" name="receiver_name" class="form-control" value="{{ $assetTransfer->to_custodian_name }}" placeholder="ชื่อ-นามสกุล เจ้าหน้าที่ผู้รับมอบ">
                    </div>

                    <div>
                        <label class="form-label">บันทึกเพิ่มเติมของช่าง</label>
                        <input type="text" name="notes" class="form-control" placeholder="ข้อสังเกตเพิ่มเติม (ถ้ามี)">
                    </div>
                </div>

                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success" style="padding: 8px 24px; font-weight: 700;">
                        <i class="bi bi-check2-circle me-1"></i> ยืนยันบันทึกสำเร็จ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
