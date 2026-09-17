@extends('layouts.app')

@section('title', 'ยื่นคำขอยืมอุปกรณ์คอมพิวเตอร์และพัสดุไอที')
@section('page_title', 'ยื่นคำขอยืมอุปกรณ์คอมพิวเตอร์และพัสดุไอที')
@section('page_subtitle', 'เลือกอุปกรณ์จากคลังคอมพิวเตอร์และครุภัณฑ์ IT โรงพยาบาลทุ่งหัวช้าง')

@section('content')
<div class="content-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div class="header-left" style="display: flex; align-items: center; gap: 14px;">
        <div class="header-icon" style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
            <i class="bi bi-file-earmark-plus"></i>
        </div>
        <div>
            <h1 class="header-title" style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">แบบฟอร์มขอยืมอุปกรณ์ / ครุภัณฑ์คอมพิวเตอร์</h1>
            <p class="header-subtitle" style="font-size: 13px; color: #64748b; margin: 3px 0 0;">
                เลือกอุปกรณ์จากคลังคอมพิวเตอร์และครุภัณฑ์ IT โรงพยาบาลทุ่งหัวช้าง
            </p>
        </div>
    </div>
    <div class="header-right">
        <a href="{{ route('asset-borrows.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-arrow-left"></i> ย้อนกลับหน้ารายการยืม-คืน
        </a>
    </div>
</div>

<div class="content-container" style="max-width: 960px; margin: 0 auto;">

    @if ($errors->any())
    <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px;">
        <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
            <i class="bi bi-exclamation-circle-fill"></i> โปรดตรวจสอบข้อมูลต่อไปนี้:
        </div>
        <ul style="margin: 0; padding-left: 20px; font-size: 13.5px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('asset-borrows.store') }}" method="POST" id="borrowForm">
        @csrf

        <!-- CARD 1: เลือกอุปกรณ์จากคลังครุภัณฑ์ IT -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #ccfbf1; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-pc-display"></i>
                    </div>
                    <span>1. เลือกอุปกรณ์ที่ต้องการยืมจากคลัง (IT Asset Selection)</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="mb-3">
                    <label for="asset_id" style="font-size: 13.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        เลือกอุปกรณ์ในคลัง <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="asset_id" id="asset_id" class="form-select" required onchange="handleAssetChange(this)">
                        <option value="">-- กรุณาเลือกอุปกรณ์คอมพิวเตอร์ / พัสดุ IT --</option>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}" 
                                    data-name="{{ $asset->name }}"
                                    data-code="{{ $asset->asset_code }}"
                                    data-brand="{{ $asset->brand }}"
                                    data-model="{{ $asset->model }}"
                                    data-serial="{{ $asset->serial_number }}"
                                    data-specs="{{ $asset->formatted_specs }}"
                                    data-status="{{ $asset->status_label }}"
                                    data-dept="{{ $asset->department?->name ?? 'คลังส่วนกลาง' }}"
                                    {{ old('asset_id', $selectedAssetId) == $asset->id ? 'selected' : '' }}>
                                [{{ $asset->status_label }}] {{ $asset->name }} - {{ $asset->brand }} {{ $asset->model }} (รหัส: {{ $asset->asset_code }})
                            </option>
                        @endforeach
                    </select>
                    <div style="font-size: 12px; color: #64748b; margin-top: 5px;">
                        * แสดงเฉพาะอุปกรณ์ที่มีสถานะพร้อมใช้งานหรือเป็นเครื่องสำรองในคลัง และไม่ได้อยู่ระหว่างถูกยืม
                    </div>
                </div>

                <!-- Selected Asset Live Preview Card -->
                <div id="assetPreviewBox" style="display: none; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; margin-top: 14px;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; color: #0d9488;">อุปกรณ์ที่เลือกยืม:</div>
                            <div id="previewName" style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 2px;">-</div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-top: 6px;">
                                <span class="badge bg-primary" id="previewCode">-</span>
                                <span class="badge bg-light text-dark border" id="previewBrandModel">-</span>
                                <span class="badge bg-info text-dark" id="previewSerial">-</span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" id="previewStatus" style="font-size: 12px;">พร้อมใช้งาน</span>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;" id="previewDept">-</div>
                        </div>
                    </div>
                    <div style="font-size: 12px; color: #475569; margin-top: 10px; border-top: 1px dashed #cbd5e1; padding-top: 8px;">
                        <strong>สเปคเบื้องต้น:</strong> <span id="previewSpecs">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: ข้อมูลผู้ขอยืม & แผนก -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <span>2. ข้อมูลผู้ขอยืมและหน่วยงาน (Requester Information)</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="borrower_name" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            ชื่อ-สกุล ผู้ขอยืม <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="borrower_name" id="borrower_name" class="form-control" 
                               value="{{ old('borrower_name', $user ? $user->name : '') }}" required placeholder="เช่น นพ.สมชาย ใจดี / พว.สุดา สุขสันต์">
                    </div>

                    <div class="col-md-4">
                        <label for="department_id" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            กลุ่มงาน / แผนกที่ขอยืม <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="department_id" id="department_id" class="form-select" required>
                            <option value="">-- เลือกกลุ่มงาน/แผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $user ? $user->department_id : '') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="contact_phone" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            เบอร์โทรศัพท์ติดต่อ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="contact_phone" id="contact_phone" class="form-control" 
                               value="{{ old('contact_phone', $user ? $user->phone : '') }}" required placeholder="เช่น 081-234-5678 / เบอร์ภายใน">
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: กำหนดเวลาและวัตถุประสงค์การยืม -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <span>3. กำหนดเวลาและวัตถุประสงค์การนำไปใช้งาน (Schedule & Purpose)</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="borrow_date" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            วันที่ต้องการยืม / วันที่รับมอบ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="borrow_date" id="borrow_date" class="form-control" 
                               value="{{ old('borrow_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="expected_return_date" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            กำหนดวันที่ส่งคืนอุปกรณ์ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="expected_return_date" id="expected_return_date" class="form-control" 
                               value="{{ old('expected_return_date', date('Y-m-d', strtotime('+1 day'))) }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        วัตถุประสงค์การขอยืมใช้งาน <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="purpose" id="purpose" rows="2" class="form-control" required placeholder="ระบุเหตุผล เช่น ใช้สำหรับการประชุมคณะกรรมการบริหาร รพ. / ออกหน่วยแพทย์เคลื่อนที่ อ.ทุ่งหัวช้าง / อบรมระบบคอมพิวเตอร์">{{ old('purpose') }}</textarea>
                </div>

                <div class="mb-0">
                    <label for="location_used" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        สถานที่นำไปใช้งาน
                    </label>
                    <input type="text" name="location_used" id="location_used" class="form-control" 
                           value="{{ old('location_used') }}" placeholder="เช่น ห้องประชุม 1 อาคารอำนวยการ / รพ.สต.บ้านทุ่งหัวช้าง">
                </div>
            </div>
        </div>

        <!-- CARD 4: อุปกรณ์เสริมที่ขอยืมไปด้วย (Accessories) -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <div style="width: 28px; height: 28px; border-radius: 8px; background: #f3e8ff; color: #7e22ce; display: flex; align-items: center; justify-content: center; font-size: 15px;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <span>4. อุปกรณ์เสริมและอุปกรณ์ต่อพ่วงที่ต้องการยืม (Accessories Checklist)</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <p style="font-size: 13px; color: #64748b; margin-bottom: 14px;">
                    กรุณาเลือกอุปกรณ์เสริมที่ต้องการรับไปด้วย เพื่อให้เจ้าหน้าที่จัดเตรียมและบันทึกในใบยืม-คืน:
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; margin-bottom: 16px;">
                    @foreach($presetAccessories as $acc)
                        <label style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff; cursor: pointer; transition: all 0.15s;" 
                               onmouseover="this.style.borderColor='#0d9488'; this.style.background='#f0fdfa';" 
                               onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='#ffffff';">
                            <input type="checkbox" name="accessories[]" value="{{ $acc }}" 
                                   {{ is_array(old('accessories')) && in_array($acc, old('accessories')) ? 'checked' : '' }}
                                   style="width: 17px; height: 17px; accent-color: #0d9488;">
                            <span style="font-size: 13px; color: #1e293b; font-weight: 500;">{{ $acc }}</span>
                        </label>
                    @endforeach
                </div>

                <div>
                    <label for="accessories_other" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        อุปกรณ์เสริมอื่นๆ เพิ่มเติม (ถ้ามี)
                    </label>
                    <input type="text" name="accessories_other" id="accessories_other" class="form-control" 
                           value="{{ old('accessories_other') }}" placeholder="ระบุอุปกรณ์อื่นๆ เพิ่มเติม เช่น สายต่อขยายเสียง 3.5mm, ตัวชี้เลเซอร์">
                </div>
            </div>
        </div>

        <!-- CARD 5: หมายเหตุและข้อตกลง -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-body" style="padding: 22px;">
                <div class="mb-3">
                    <label for="notes" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        หมายเหตุหรือข้อความถึงเจ้าหน้าที่ไอที
                    </label>
                    <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="ข้อความเพิ่มเติม เช่น ขอให้ช่วยลงโปรแกรม Zoom หรือเตรียมไฟล์">{{ old('notes') }}</textarea>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0;">
                        <input type="checkbox" required style="width: 18px; height: 18px; accent-color: #0d9488; margin-top: 2px;">
                        <span style="font-size: 12.5px; color: #475569; line-height: 1.5;">
                            ข้าพเจ้าขอรับรองว่าข้อความข้างต้นเป็นความจริง และยินยอมดูแลรักษาอุปกรณ์ที่ยืมให้อยู่ในสภาพเรียบร้อย ปลอดภัย พร้อมส่งคืนตามกำหนดเวลา หากเกิดความเสียหายหรือสูญหาย ข้าพเจ้ายินดีรับผิดชอบตามระเบียบของทางราชการ
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-bottom: 40px;">
            <a href="{{ route('asset-borrows.index') }}" class="btn btn-light" style="padding: 10px 24px; font-weight: 600;">
                ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle-fill"></i> บันทึกและยื่นคำขอยืมอุปกรณ์
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
function handleAssetChange(select) {
    const box = document.getElementById('assetPreviewBox');
    const selectedOption = select.options[select.selectedIndex];

    if (!selectedOption || !selectedOption.value) {
        box.style.display = 'none';
        return;
    }

    document.getElementById('previewName').textContent = selectedOption.getAttribute('data-name') || '-';
    document.getElementById('previewCode').textContent = selectedOption.getAttribute('data-code') || '-';
    document.getElementById('previewBrandModel').textContent = (selectedOption.getAttribute('data-brand') || '') + ' ' + (selectedOption.getAttribute('data-model') || '');
    document.getElementById('previewSerial').textContent = 'S/N: ' + (selectedOption.getAttribute('data-serial') || 'ไม่ระบุ');
    document.getElementById('previewStatus').textContent = selectedOption.getAttribute('data-status') || 'พร้อมใช้งาน';
    document.getElementById('previewDept').textContent = 'ประจำที่: ' + (selectedOption.getAttribute('data-dept') || '-');
    document.getElementById('previewSpecs').textContent = selectedOption.getAttribute('data-specs') || '-';

    box.style.display = 'block';
}

// Trigger initial preview if asset_id is already selected
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('asset_id');
    if (select && select.value) {
        handleAssetChange(select);
    }
});
</script>
@endpush
@endsection
