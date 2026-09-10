@extends('layouts.app')

@section('title', 'แก้ไขพัสดุ ' . $sparePart->name)
@section('page_title', 'แก้ไขข้อมูลพัสดุ: ' . $sparePart->name)
@section('page_subtitle', 'รหัสพัสดุ: ' . $sparePart->part_code)

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-pencil-square text-primary" style="font-size: 20px;"></i>
                <span>แก้ไขข้อมูลพัสดุ ({{ $sparePart->part_code }})</span>
            </div>
            <a href="{{ route('spare-parts.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('spare-parts.update', $sparePart) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="part_code">รหัสพัสดุ (Part Code)</label>
                        <input type="text" id="part_code" name="part_code" class="form-control" value="{{ old('part_code', $sparePart->part_code) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category">หมวดหมู่พัสดุ</label>
                        <input type="text" id="category" name="category" class="form-control" value="{{ old('category', $sparePart->category) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="name">ชื่อรายการพัสดุ / อะไหล่</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $sparePart->name) }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="unit">หน่วยนับ</label>
                        <input type="text" id="unit" name="unit" class="form-control" value="{{ old('unit', $sparePart->unit) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="unit_price">ราคาต่อหน่วย (บาท)</label>
                        <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" value="{{ old('unit_price', $sparePart->unit_price) }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="stock_quantity">จำนวนคงเหลือในคลัง</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $sparePart->stock_quantity) }}" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="minimum_quantity">จุดเตือนขั้นต่ำ</label>
                        <input type="number" id="minimum_quantity" name="minimum_quantity" class="form-control" value="{{ old('minimum_quantity', $sparePart->minimum_quantity) }}" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="location">สถานที่จัดเก็บ</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location', $sparePart->location) }}">
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">หมายเหตุเพิ่มเติม</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes', $sparePart->notes) }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                    <a href="{{ route('spare-parts.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-floppy-fill"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
