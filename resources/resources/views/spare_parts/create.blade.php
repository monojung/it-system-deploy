@extends('layouts.app')

@section('title', 'เพิ่มพัสดุและอะไหล่ใหม่')
@section('page_title', 'ลงทะเบียนพัสดุและอะไหล่ใหม่')
@section('page_subtitle', 'เพิ่มรายการอะไหล่คอมพิวเตอร์และวัสดุสิ้นเปลืองเข้าสู่คลัง IT')

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-box-seam text-primary" style="font-size: 20px;"></i>
                <span>แบบฟอร์มลงทะเบียนพัสดุ / อะไหล่</span>
            </div>
            <a href="{{ route('spare-parts.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('spare-parts.store') }}" method="POST">
                @csrf

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="part_code">รหัสพัสดุ (Part Code)</label>
                        <input type="text" id="part_code" name="part_code" class="form-control" value="{{ old('part_code') }}" placeholder="เช่น SP-013, PART-005" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category">หมวดหมู่พัสดุ</label>
                        <input type="text" id="category" name="category" class="form-control" value="{{ old('category') }}" placeholder="เช่น หมึกพิมพ์, อะไหล่คอมพิวเตอร์, อุปกรณ์ต่อพ่วง, อุปกรณ์เครือข่าย">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label required" for="name">ชื่อรายการพัสดุ / อะไหล่</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="เช่น ตลับหมึก HP 85A, RAM DDR4 16GB, เมาส์ USB..." required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="unit">หน่วยนับ</label>
                        <input type="text" id="unit" name="unit" class="form-control" value="{{ old('unit', 'ชิ้น') }}" placeholder="เช่น ชิ้น, ตลับ, ตัว, เส้น, กล่อง" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="unit_price">ราคาต่อหน่วย (บาท)</label>
                        <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" value="{{ old('unit_price', '0.00') }}" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="stock_quantity">จำนวนตั้งต้นในคลัง</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" min="0" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="minimum_quantity">จุดเตือนขั้นต่ำ (Minimum Stock Alert)</label>
                        <input type="number" id="minimum_quantity" name="minimum_quantity" class="form-control" value="{{ old('minimum_quantity', setting('default_min_stock', 3)) }}" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="location">สถานที่จัดเก็บ (ตู้ / ชั้นวาง)</label>
                    <input type="text" id="location" name="location" class="form-control" value="{{ old('location') }}" placeholder="เช่น ตู้ A1 ชั้น 2 ห้องศูนย์คอมพิวเตอร์">
                </div>

                <div class="form-group">
                    <label class="form-label" for="notes">หมายเหตุเพิ่มเติม</label>
                    <textarea id="notes" name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                    <a href="{{ route('spare-parts.index') }}" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-floppy-fill"></i> บันทึกพัสดุ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
