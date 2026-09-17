@extends('layouts.app')

@section('title', 'แก้ไขคำขอยืมอุปกรณ์ไอที - ' . $borrow->borrow_no)
@section('page_title', 'แก้ไขคำขอยืมอุปกรณ์ไอที')
@section('page_subtitle', 'รหัสคำขอ: ' . $borrow->borrow_no)

@section('content')
<div class="content-container" style="max-width: 960px; margin: 0 auto;">

    <!-- Top Action Nav -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('asset-borrows.show', $borrow->id) }}" class="btn btn-outline-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
            <i class="bi bi-arrow-left"></i> ย้อนกลับหน้ารายละเอียดคำขอ
        </a>
    </div>

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

    <form action="{{ route('asset-borrows.update', $borrow->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- CARD 1: อุปกรณ์ที่ต้องการยืม -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-pc-display text-primary"></i>
                    <span>1. เลือกอุปกรณ์ที่ต้องการยืมจากคลัง (IT Asset Selection)</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="mb-3">
                    <label for="asset_id" style="font-size: 13.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">
                        เลือกอุปกรณ์ในคลัง <span style="color: #ef4444;">*</span>
                    </label>
                    <select name="asset_id" id="asset_id" class="form-select" required>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}" {{ old('asset_id', $borrow->asset_id) == $asset->id ? 'selected' : '' }}>
                                [{{ $asset->status_label }}] {{ $asset->name }} - {{ $asset->brand }} {{ $asset->model }} (รหัส: {{ $asset->asset_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- CARD 2: ข้อมูลผู้ขอยืม & แผนก -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-person-badge text-info"></i>
                    <span>2. ข้อมูลผู้ขอยืมและหน่วยงาน</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="borrower_name" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            ชื่อ-สกุล ผู้ขอยืม <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" name="borrower_name" id="borrower_name" class="form-control" 
                               value="{{ old('borrower_name', $borrow->borrower_name) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="department_id" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            กลุ่มงาน / แผนกที่ขอยืม <span style="color: #ef4444;">*</span>
                        </label>
                        <select name="department_id" id="department_id" class="form-select" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $borrow->department_id) == $dept->id ? 'selected' : '' }}>
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
                               value="{{ old('contact_phone', $borrow->contact_phone) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: กำหนดเวลาและวัตถุประสงค์ -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-calendar-event text-warning"></i>
                    <span>3. กำหนดเวลาและวัตถุประสงค์การนำไปใช้งาน</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="borrow_date" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            วันที่ต้องการยืม / วันที่รับมอบ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="borrow_date" id="borrow_date" class="form-control" 
                               value="{{ old('borrow_date', $borrow->borrow_date ? $borrow->borrow_date->format('Y-m-d') : '') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label for="expected_return_date" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                            กำหนดวันที่ส่งคืนอุปกรณ์ <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="date" name="expected_return_date" id="expected_return_date" class="form-control" 
                               value="{{ old('expected_return_date', $borrow->expected_return_date ? $borrow->expected_return_date->format('Y-m-d') : '') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        วัตถุประสงค์การขอยืมใช้งาน <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea name="purpose" id="purpose" rows="2" class="form-control" required>{{ old('purpose', $borrow->purpose) }}</textarea>
                </div>

                <div class="mb-0">
                    <label for="location_used" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        สถานที่นำไปใช้งาน
                    </label>
                    <input type="text" name="location_used" id="location_used" class="form-control" 
                           value="{{ old('location_used', $borrow->location_used) }}">
                </div>
            </div>
        </div>

        <!-- CARD 4: อุปกรณ์เสริม -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-bottom: 1px solid #e2e8f0; padding: 16px 22px;">
                <div style="font-weight: 700; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-box-seam text-purple"></i>
                    <span>4. อุปกรณ์เสริมและอุปกรณ์ต่อพ่วงที่ต้องการยืม</span>
                </div>
            </div>

            <div class="card-body" style="padding: 22px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; margin-bottom: 16px;">
                    @foreach($presetAccessories as $acc)
                        <label style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff; cursor: pointer;">
                            <input type="checkbox" name="accessories[]" value="{{ $acc }}" 
                                   {{ in_array($acc, $currentAcc) ? 'checked' : '' }}
                                   style="width: 17px; height: 17px; accent-color: #0d9488;">
                            <span style="font-size: 13px; color: #1e293b; font-weight: 500;">{{ $acc }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CARD 5: หมายเหตุ -->
        <div class="card" style="border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: var(--shadow-sm); overflow: hidden;">
            <div class="card-body" style="padding: 22px;">
                <div class="mb-0">
                    <label for="notes" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: block;">
                        หมายเหตุหรือข้อความถึงเจ้าหน้าที่ไอที
                    </label>
                    <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $borrow->notes) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-bottom: 40px;">
            <a href="{{ route('asset-borrows.show', $borrow->id) }}" class="btn btn-light" style="padding: 10px 24px; font-weight: 600;">
                ยกเลิก
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 10px 28px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle-fill"></i> บันทึกการแก้ไข
            </button>
        </div>
    </form>

</div>
@endsection
