@extends('layouts.app')

@section('title', 'แก้ไขใบแจ้งซ่อม ' . $repair->ticket_number)
@section('page_title', 'แก้ไขข้อมูลใบแจ้งซ่อม ' . $repair->ticket_number)
@section('page_subtitle', 'แก้ไขรายละเอียดการแจ้งซ่อมและข้อมูลผู้แจ้ง')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-pencil-square text-primary"></i>
                <span>แก้ไขใบแจ้งซ่อม ({{ $repair->ticket_number }})</span>
            </div>
            <a href="{{ route('repairs.show', $repair) }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('repairs.update', $repair) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label required" for="title">หัวข้อปัญหา / อาการเสียเบื้องต้น</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $repair->title) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="urgency">ระดับความเร่งด่วน</label>
                    <select name="urgency" id="urgency" class="form-select">
                        <option value="low" {{ old('urgency', $repair->urgency) == 'low' ? 'selected' : '' }}>ปกติ (ทั่วไป)</option>
                        <option value="normal" {{ old('urgency', $repair->urgency) == 'normal' ? 'selected' : '' }}>ปานกลาง</option>
                        <option value="high" {{ old('urgency', $repair->urgency) == 'high' ? 'selected' : '' }}>ด่วน</option>
                        <option value="critical" {{ old('urgency', $repair->urgency) == 'critical' ? 'selected' : '' }}>ด่วนที่สุด (กระทบผู้ป่วย)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="description">รายละเอียดอาการเสีย</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required>{{ old('description', $repair->description) }}</textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="asset_id">ครุภัณฑ์คอมพิวเตอร์ที่เชื่อมโยง</label>
                        <select name="asset_id" id="asset_id" class="form-select">
                            <option value="">-- ไม่ระบุ --</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ old('asset_id', $repair->asset_id) == $asset->id ? 'selected' : '' }}>
                                    [{{ $asset->asset_code }}] {{ $asset->name }} ({{ $asset->brand }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="other_device_info">อุปกรณ์ทั่วไป (กรณีไม่มีรหัสครุภัณฑ์)</label>
                        <input type="text" id="other_device_info" name="other_device_info" class="form-control" value="{{ old('other_device_info', $repair->other_device_info) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="department_id">แผนกที่แจ้ง</label>
                        <select name="department_id" id="department_id" class="form-select" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $repair->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="location_detail">จุดที่ตั้ง / ห้อง / โต๊ะทำงาน</label>
                        <input type="text" id="location_detail" name="location_detail" class="form-control" value="{{ old('location_detail', $repair->location_detail) }}">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="requester_name">ชื่อผู้แจ้งซ่อม</label>
                        <input type="text" id="requester_name" name="requester_name" class="form-control" value="{{ old('requester_name', $repair->requester_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="requester_phone">เบอร์ติดต่อ</label>
                        <input type="text" id="requester_phone" name="requester_phone" class="form-control" value="{{ old('requester_phone', $repair->requester_phone) }}" required>
                    </div>
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->isTechnician() || $repair->status === 'external' || $repair->external_repair_detail)
                <div style="margin-top: 18px; padding-top: 18px; border-top: 1px dashed var(--border);">
                    <h3 style="font-size: 14.5px; font-weight: 700; color: #4b5563; margin-bottom: 12px;">
                        <i class="bi bi-tools"></i> บันทึกผลการตรวจสภาพและการส่งซ่อมภายนอก (สำหรับเจ้าหน้าที่ IT)
                    </h3>
                    <div class="form-group">
                        <label class="form-label" for="cause">สาเหตุของปัญหา / ผลการตรวจสภาพ</label>
                        <input type="text" id="cause" name="cause" class="form-control" value="{{ old('cause', $repair->cause) }}" placeholder="เช่น เมนบอร์ดช็อต ชิปประมวลผลเสีย เกินขีดความสามารถซ่อมเอง">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="solution">วิธีการแก้ไข / แผนดำเนินการ</label>
                        <textarea id="solution" name="solution" class="form-control" rows="2" placeholder="เช่น ส่งเคลมศูนย์บริการ หรือเปลี่ยนอะไหล่...">{{ old('solution', $repair->solution) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="external_repair_detail">ร้านค้า / ศูนย์บริการภายนอกที่ส่งซ่อม</label>
                        <input type="text" id="external_repair_detail" name="external_repair_detail" class="form-control" value="{{ old('external_repair_detail', $repair->external_repair_detail) }}" placeholder="เช่น ศูนย์บริการ Dell เชียงใหม่, ร้านแอดไวซ์ สาขาลำพูน">
                        <span class="form-text" style="color: #6b21a8;">ข้อมูลนี้จะถูกนำไปพิมพ์ลงในแบบเสนอขออนุมัติส่งซ่อมภายนอก (เสนอหัวหน้ารับทราบ และ ผอ. เซ็นอนุมัติ)</span>
                    </div>
                </div>
                @endif

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                    <a href="{{ route('repairs.show', $repair) }}" class="btn btn-secondary">
                        ยกเลิก
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy-fill"></i>
                        <span>บันทึกการแก้ไข</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
