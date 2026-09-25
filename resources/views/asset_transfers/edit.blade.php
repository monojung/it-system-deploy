@extends('layouts.app')

@section('title', 'แก้ไขข้อมูลการย้ายครุภัณฑ์ ' . $assetTransfer->transfer_no)
@section('page_title', 'แก้ไขข้อมูลการย้ายครุภัณฑ์: ' . $assetTransfer->transfer_no)
@section('page_subtitle', 'ปรับปรุงรายละเอียดจุดติดตั้งปลายทางและเหตุผลการย้าย')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('asset-transfers.show', $assetTransfer) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> กลับหน้ารายละเอียดการย้าย
    </a>
</div>

<div class="content-container">
    <div class="card" style="max-width: 800px; margin: 0 auto; border-top: 4px solid #6366f1;">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-pencil-square text-primary"></i>
                <span>แบบฟอร์มแก้ไขข้อมูลการย้ายครุภัณฑ์</span>
            </div>
            <span style="font-family: monospace; font-weight: 700; color: #6366f1;">
                {{ $assetTransfer->transfer_no }}
            </span>
        </div>
        <div class="card-body">
            <form action="{{ route('asset-transfers.update', $assetTransfer) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Asset Summary --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px;">
                    <div style="font-size: 11.5px; color: #64748b; text-transform: uppercase; font-weight: 700;">ครุภัณฑ์:</div>
                    <div style="font-size: 15px; font-weight: 800; color: #0f172a; margin-top: 2px;">
                        {{ $assetTransfer->asset?->asset_code }} - {{ $assetTransfer->asset?->name }}
                    </div>
                    <div style="font-size: 12.5px; color: #475569; margin-top: 4px;">
                        จุดติดตั้งเดิม: {{ $assetTransfer->fromDepartment?->name ?? $assetTransfer->from_department_name }} ({{ $assetTransfer->from_location_detail ?: 'ไม่ระบุห้อง' }})
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700;">ประเภทการย้าย <span class="text-danger">*</span></label>
                        @if(auth()->user()->isUser())
                            <select name="transfer_type" class="form-select" required>
                                <option value="relocation" {{ old('transfer_type', $assetTransfer->transfer_type) === 'relocation' ? 'selected' : '' }}>ย้ายจุดติดตั้ง / ห้อง (ภายในกลุ่มงานเดิม)</option>
                                <option value="temporary_move" {{ old('transfer_type', $assetTransfer->transfer_type) === 'temporary_move' ? 'selected' : '' }}>ย้ายใช้งานชั่วคราว (ภายในกลุ่มงานเดิม)</option>
                            </select>
                        @else
                            <select name="transfer_type" class="form-select" required>
                                <option value="relocation" {{ old('transfer_type', $assetTransfer->transfer_type) === 'relocation' ? 'selected' : '' }}>ย้ายจุดติดตั้ง/ห้อง</option>
                                <option value="department_transfer" {{ old('transfer_type', $assetTransfer->transfer_type) === 'department_transfer' ? 'selected' : '' }}>โอนย้ายหน่วยงาน/ผู้ครอบครอง</option>
                                <option value="temporary_move" {{ old('transfer_type', $assetTransfer->transfer_type) === 'temporary_move' ? 'selected' : '' }}>ย้ายใช้งานชั่วคราว</option>
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700;">วันที่ดำเนินการย้าย <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', $assetTransfer->transfer_date?->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700;">หน่วยงาน/กลุ่มงานปลายทาง <span class="text-danger">*</span></label>
                        @if(auth()->user()->isUser())
                            <input type="hidden" name="to_department_id" value="{{ auth()->user()->department_id }}">
                            <div class="form-control" style="background: #f8fafc; border: 1.5px solid #cbd5e1; display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; border-radius: 8px;">
                                <span style="font-weight: 700; color: #1e293b;">
                                    <i class="bi bi-building-check text-primary me-1"></i> {{ auth()->user()->department?->name ?? 'กลุ่มงานเดิม' }}
                                </span>
                                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-size: 11.5px; font-weight: 600; padding: 4px 8px; border-radius: 6px;">
                                    <i class="bi bi-lock-fill"></i> ย้ายภายในกลุ่มงานเดิม
                                </span>
                            </div>
                        @else
                            <select name="to_department_id" class="form-select" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('to_department_id', $assetTransfer->to_department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700;">จุดติดตั้งใหม่ / ห้อง / เลขโต๊ะ <span class="text-danger">*</span></label>
                        <input type="text" name="to_location_detail" class="form-control" value="{{ old('to_location_detail', $assetTransfer->to_location_detail) }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-weight: 700;">ผู้ดูแล/ผู้ครอบครองใหม่</label>
                        <input type="text" name="to_custodian_name" class="form-control" value="{{ old('to_custodian_name', $assetTransfer->to_custodian_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">IP Address ใหม่</label>
                        <input type="text" name="to_ip_address" class="form-control" value="{{ old('to_ip_address', $assetTransfer->to_ip_address) }}">
                    </div>
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
                <div class="mb-3">
                    <label class="form-label">ช่าง IT ผู้รับผิดชอบ</label>
                    <select name="technician_id" class="form-select">
                        <option value="">-- ยังไม่ระบุช่าง --</option>
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ old('technician_id', $assetTransfer->technician_id) == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="mb-3">
                    <label class="form-label" style="font-weight: 700;">เหตุผลความจำเป็นในการย้าย <span class="text-danger">*</span></label>
                    <textarea name="reason" rows="3" class="form-control" required>{{ old('reason', $assetTransfer->reason) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">หมายเหตุเพิ่มเติม</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes', $assetTransfer->notes) }}">
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; font-weight: 700;">
                        <i class="bi bi-save me-1"></i> บันทึกการแก้ไข
                    </button>
                    <a href="{{ route('asset-transfers.show', $assetTransfer) }}" class="btn btn-secondary" style="padding: 10px 20px;">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
