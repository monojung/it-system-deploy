@extends('layouts.app')

@section('title', 'บันทึก / ขอย้ายจุดติดตั้งครุภัณฑ์ IT')
@section('page_title', 'บันทึก / ขอย้ายจุดติดตั้งครุภัณฑ์')
@section('page_subtitle', 'ระบุครุภัณฑ์ จุดติดตั้งเดิม และข้อมูลจุดติดตั้งใหม่เพื่อบันทึกประวัติและอัปเดตระบบ')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('asset-transfers.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> กลับหน้ารายการย้ายครุภัณฑ์
    </a>
</div>

<div class="content-container">
    <form action="{{ route('asset-transfers.store') }}" method="POST" id="transferForm">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
            
            {{-- Column 1: Select Asset & Origin Information --}}
            <div>
                <div class="card" style="margin-bottom: 24px; border-top: 4px solid #7c3aed;">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-pc-display text-primary" style="color: #7c3aed !important;"></i>
                            <span>1. เลือกครุภัณฑ์ที่ต้องการย้าย</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                ค้นหาและเลือกครุภัณฑ์ <span class="text-danger">*</span>
                            </label>
                            <select name="asset_id" id="asset_select" class="form-select @error('asset_id') is-invalid @enderror" required onchange="handleAssetChange(this)">
                                <option value="">-- กรุณาเลือกครุภัณฑ์คอมพิวเตอร์ / IT --</option>
                                @foreach($assets as $ast)
                                    <option value="{{ $ast->id }}" 
                                        data-code="{{ $ast->asset_code }}"
                                        data-name="{{ $ast->name }}"
                                        data-brand="{{ $ast->brand }} {{ $ast->model }}"
                                        data-sn="{{ $ast->serial_number }}"
                                        data-dept-id="{{ $ast->department_id }}"
                                        data-dept-name="{{ $ast->department?->name ?? 'ไม่ระบุ' }}"
                                        data-location="{{ $ast->location_detail }}"
                                        data-custodian="{{ $ast->custodian_name }}"
                                        data-ip="{{ $ast->ip_address }}"
                                        data-type="{{ $ast->deviceType?->name ?? 'อุปกรณ์' }}"
                                        {{ (old('asset_id', $selectedAsset?->id) == $ast->id) ? 'selected' : '' }}>
                                        [{{ $ast->asset_code }}] {{ $ast->name }} - {{ $ast->department?->name ?? 'ไม่ระบุแผนก' }} ({{ $ast->location_detail ?: 'ไม่ระบุห้อง' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('asset_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" style="font-size: 12px; color: #64748b;">
                                สามารถพิมพ์เพื่อค้นหารหัสครุภัณฑ์ หรือชื่อเครื่องได้
                            </div>
                        </div>

                        {{-- Visual Snapshot Card: Current Location --}}
                        <div id="origin_preview_card" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-top: 16px;">
                            <div style="font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                                <span><i class="bi bi-geo-alt-fill text-danger me-1"></i>ข้อมูลจุดติดตั้งปัจจุบัน (จุดเดิม)</span>
                                <span class="badge badge-secondary" id="preview_device_type">-</span>
                            </div>

                            <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 4px;" id="preview_asset_title">
                                กรุณาเลือกครุภัณฑ์ด้านบน
                            </div>
                            <div style="font-size: 12.5px; color: #64748b; margin-bottom: 14px;" id="preview_asset_sub">
                                ข้อมูลจุดติดตั้งจะปรากฏที่นี่อัตโนมัติ
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 12.5px; background: #ffffff; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <div>
                                    <div style="color: #94a3b8; font-size: 11px;">หน่วยงานปัจจุบัน:</div>
                                    <div style="font-weight: 700; color: #334155;" id="preview_dept">-</div>
                                </div>
                                <div>
                                    <div style="color: #94a3b8; font-size: 11px;">จุดติดตั้ง/ห้องเดิม:</div>
                                    <div style="font-weight: 700; color: #334155;" id="preview_loc">-</div>
                                </div>
                                <div>
                                    <div style="color: #94a3b8; font-size: 11px;">ผู้ครอบครอง/ผู้ดูแลเดิม:</div>
                                    <div style="font-weight: 700; color: #334155;" id="preview_custodian">-</div>
                                </div>
                                <div>
                                    <div style="color: #94a3b8; font-size: 11px;">IP Address เดิม:</div>
                                    <div style="font-weight: 700; color: #0284c7; font-family: monospace;" id="preview_ip">-</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Reason & Type Card --}}
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-journal-text text-primary" style="color: #7c3aed !important;"></i>
                            <span>2. รายละเอียดและเหตุผลความจำเป็น</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                ประเภทการย้าย <span class="text-danger">*</span>
                            </label>
                            <select name="transfer_type" class="form-select @error('transfer_type') is-invalid @enderror" required>
                                <option value="relocation" {{ old('transfer_type') === 'relocation' ? 'selected' : '' }}>
                                    ย้ายจุดติดตั้ง/ย้ายห้อง (ภายในหน่วยงานเดิม หรือข้ามห้อง)
                                </option>
                                <option value="department_transfer" {{ old('transfer_type') === 'department_transfer' ? 'selected' : '' }}>
                                    โอนย้ายหน่วยงาน / เปลี่ยนผู้ครอบครองอุปกรณ์
                                </option>
                                <option value="temporary_move" {{ old('transfer_type') === 'temporary_move' ? 'selected' : '' }}>
                                    ย้ายใช้งานชั่วคราว (เช่น จัดฝึกอบรม, ประชุม, นิทรรศการ)
                                </option>
                            </select>
                            @error('transfer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                วันที่ดำเนินการย้าย <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="transfer_date" class="form-control @error('transfer_date') is-invalid @enderror" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                            @error('transfer_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                เหตุผลความจำเป็นในการย้าย <span class="text-danger">*</span>
                            </label>
                            <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" placeholder="เช่น ย้ายโต๊ะทำงานพยาบาล, ปรับปรุงผังห้องตรวจ, สลับเครื่องทดแทนเนื่องจากสเปคสูงกว่า..." required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">หมายเหตุเพิ่มเติม (ถ้ามี)</label>
                            <input type="text" name="notes" class="form-control" placeholder="ข้อสังเกต หรือสิ่งที่ต้องเตรียม เช่น เดินสายแลนเพิ่ม" value="{{ old('notes') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 2: Destination Information & Immediate Action --}}
            <div>
                <div class="card" style="margin-bottom: 24px; border-top: 4px solid #10b981;">
                    <div class="card-header">
                        <div class="card-title">
                            <i class="bi bi-geo-alt-fill" style="color: #10b981;"></i>
                            <span>3. ข้อมูลจุดติดตั้งปลายทาง (จุดใหม่)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                หน่วยงาน/กลุ่มงานปลายทาง <span class="text-danger">*</span>
                            </label>
                            <select name="to_department_id" id="to_department_id" class="form-select @error('to_department_id') is-invalid @enderror" required>
                                <option value="">-- เลือกหน่วยงานปลายทาง --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('to_department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('to_department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                จุดติดตั้งใหม่ / ห้อง / อาคาร / เลขโต๊ะ <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="to_location_detail" class="form-control @error('to_location_detail') is-invalid @enderror" placeholder="เช่น ตึกอุบัติเหตุ ชั้น 1 เคาน์เตอร์พยาบาล โต๊ะ 2" value="{{ old('to_location_detail') }}" required>
                            @error('to_location_detail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" style="font-size: 12px; color: #64748b;">
                                ระบุตำแหน่งที่ตั้งให้ชัดเจนเพื่อให้ช่างสามารถเข้าติดตั้งหรือตรวจสอบได้ถูกต้อง
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700;">
                                ผู้ดูแล / ผู้ครอบครองเครื่องใหม่
                            </label>
                            <input type="text" name="to_custodian_name" id="to_custodian_name" class="form-control @error('to_custodian_name') is-invalid @enderror" placeholder="ชื่อ-นามสกุล ผู้ใช้งานประจำเครื่องใหม่" value="{{ old('to_custodian_name') }}">
                            @error('to_custodian_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                IP Address ใหม่ (ถ้ามี หรือปล่อยว่างหากใช้ DHCP)
                            </label>
                            <input type="text" name="to_ip_address" class="form-control @error('to_ip_address') is-invalid @enderror" placeholder="เช่น 192.168.1.150" value="{{ old('to_ip_address') }}">
                            @error('to_ip_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
                        <div class="mb-3">
                            <label class="form-label">มอบหมายช่าง IT ดำเนินการ</label>
                            <select name="technician_id" class="form-select">
                                <option value="">-- ยังไม่ระบุช่าง (มอบหมายภายหลัง) --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ (old('technician_id', auth()->id()) == $tech->id) ? 'selected' : '' }}>
                                        {{ $tech->name }} ({{ $tech->role_label }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Immediate Completion Box (For Admin & Technician) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
                <div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac;">
                    <div class="card-body">
                        <div class="form-check form-switch" style="font-size: 15px;">
                            <input class="form-check-input" type="checkbox" name="complete_immediately" id="complete_immediately" value="1" {{ old('complete_immediately') ? 'checked' : '' }} onchange="toggleImmediateFields(this)">
                            <label class="form-check-label" for="complete_immediately" style="font-weight: 700; color: #166534; cursor: pointer;">
                                ดำเนินการย้ายเสร็จสิ้นแล้วทันที (อัปเดตตำแหน่งครุภัณฑ์ทันที)
                            </label>
                        </div>
                        <div style="font-size: 12.5px; color: #15803d; margin-top: 4px; padding-left: 2.2rem;">
                            เปิดตัวเลือกนี้หากดำเนินการย้ายและติดตั้งอุปกรณ์หน้างานเสร็จเรียบร้อยแล้ว ระบบจะอัปเดตที่ตั้งครุภัณฑ์ในฐานข้อมูลให้ทันที
                        </div>

                        <div id="immediate_fields" style="margin-top: 16px; padding-top: 14px; border-top: 1px dashed #86efac; display: {{ old('complete_immediately') ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label" style="font-weight: 600; font-size: 13px; color: #14532d;">
                                    ผลการทดสอบหลังติดตั้งอุปกรณ์
                                </label>
                                <input type="text" name="test_result" class="form-control form-control-sm" placeholder="เช่น เครือข่าย LAN ใช้งานได้, เครื่องเปิดติด, ทดสอบพิมพ์ได้ปกติ" value="{{ old('test_result', 'ตรวจสอบการเชื่อมต่อเครือข่ายและระบบสารสนเทศใช้งานได้ปกติ') }}">
                            </div>

                            <div>
                                <label class="form-label" style="font-weight: 600; font-size: 13px; color: #14532d;">
                                    ผู้รับมอบ ณ จุดติดตั้งใหม่
                                </label>
                                <input type="text" name="receiver_name" id="receiver_name" class="form-control form-control-sm" placeholder="ชื่อ-นามสกุล ผู้รับมอบอุปกรณ์" value="{{ old('receiver_name') }}">
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Action Submit Buttons --}}
                <div style="margin-top: 24px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; font-weight: 700; font-size: 15px; background: linear-gradient(135deg, #7c3aed 0%, #6366f1 100%); border-color: #6366f1; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>บันทึกการย้ายครุภัณฑ์</span>
                    </button>
                    <a href="{{ route('asset-transfers.index') }}" class="btn btn-secondary" style="padding: 12px 20px;">
                        ยกเลิก
                    </a>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
function handleAssetChange(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
        document.getElementById('preview_device_type').textContent = '-';
        document.getElementById('preview_asset_title').textContent = 'กรุณาเลือกครุภัณฑ์ด้านบน';
        document.getElementById('preview_asset_sub').textContent = 'ข้อมูลจุดติดตั้งจะปรากฏที่นี่อัตโนมัติ';
        document.getElementById('preview_dept').textContent = '-';
        document.getElementById('preview_loc').textContent = '-';
        document.getElementById('preview_custodian').textContent = '-';
        document.getElementById('preview_ip').textContent = '-';
        return;
    }

    const code = selectedOption.dataset.code || '-';
    const name = selectedOption.dataset.name || '-';
    const brand = selectedOption.dataset.brand || '';
    const sn = selectedOption.dataset.sn ? 'S/N: ' + selectedOption.dataset.sn : '';
    const deptId = selectedOption.dataset.deptId || '';
    const deptName = selectedOption.dataset.deptName || 'ไม่ระบุ';
    const location = selectedOption.dataset.location || 'ไม่ระบุห้อง';
    const custodian = selectedOption.dataset.custodian || 'ไม่ระบุ';
    const ip = selectedOption.dataset.ip || 'DHCP / ไม่ระบุ';
    const type = selectedOption.dataset.type || 'อุปกรณ์';

    document.getElementById('preview_device_type').textContent = type;
    document.getElementById('preview_asset_title').textContent = code + ' - ' + name;
    document.getElementById('preview_asset_sub').textContent = (brand + ' ' + sn).trim();
    document.getElementById('preview_dept').textContent = deptName;
    document.getElementById('preview_loc').textContent = location;
    document.getElementById('preview_custodian').textContent = custodian;
    document.getElementById('preview_ip').textContent = ip;

    // Auto-fill destination department and custodian as default suggestion if empty
    const toDeptSelect = document.getElementById('to_department_id');
    if (toDeptSelect && !toDeptSelect.value && deptId) {
        toDeptSelect.value = deptId;
    }
    const toCustodianInput = document.getElementById('to_custodian_name');
    if (toCustodianInput && !toCustodianInput.value && custodian !== 'ไม่ระบุ') {
        toCustodianInput.value = custodian;
    }
    const receiverInput = document.getElementById('receiver_name');
    if (receiverInput && !receiverInput.value && custodian !== 'ไม่ระบุ') {
        receiverInput.value = custodian;
    }
}

function toggleImmediateFields(checkbox) {
    const fields = document.getElementById('immediate_fields');
    if (fields) {
        fields.style.display = checkbox.checked ? 'block' : 'none';
    }
}

// Initial trigger if asset is preselected
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('asset_select');
    if (select && select.value) {
        handleAssetChange(select);
    }
});
</script>
@endsection
