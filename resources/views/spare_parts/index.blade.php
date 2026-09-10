@extends('layouts.app')

@section('title', 'คลังอะไหล่และพัสดุ IT')
@section('page_title', 'คลังอะไหล่และพัสดุอุปกรณ์ IT')
@section('page_subtitle', 'บริหารจัดการสต็อกอะไหล่คอมพิวเตอร์ ตลับหมึกพิมพ์ และอุปกรณ์ต่อพ่วง')

@section('content')
<!-- Low Stock Warning Banner -->
@if($lowStockCount > 0)
<div class="card" style="border-left: 4px solid #ef4444; margin-bottom: 24px; background: #fff5f5;">
    <div class="card-body" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <i class="bi bi-exclamation-octagon-fill text-danger" style="font-size: 28px;"></i>
            <div>
                <div style="font-weight: 700; color: #991b1b; font-size: 15px;">
                    มีรายการพัสดุ/อะไหล่ต่ำกว่าเกณฑ์ขั้นต่ำ {{ $lowStockCount }} รายการ!
                </div>
                <div style="font-size: 13px; color: #b91c1c;">
                    กรุณาวางแผนจัดซื้อหรือจัดหาทดแทนเพื่อไม่ให้กระทบต่องานบริการผู้ป่วย
                </div>
            </div>
        </div>
        <a href="{{ route('spare-parts.index', ['low_stock' => 1]) }}" class="btn btn-danger btn-sm">
            แสดงเฉพาะรายการสต็อกต่ำ
        </a>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 14px;">
        <div class="card-title">
            <i class="bi bi-box-seam text-primary" style="font-size: 20px;"></i>
            <span>รายการพัสดุและอะไหล่ในคลัง ({{ $parts->total() }} รายการ)</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('reports.export', 'spare_parts') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Export CSV</span>
            </a>
            <a href="{{ route('spare-parts.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i>
                <span>เพิ่มพัสดุใหม่</span>
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div style="padding: 18px 24px; background: #f8fafc; border-bottom: 1px solid var(--border);">
        <form action="{{ route('spare-parts.index') }}" method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; align-items: end;">
                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">ค้นหาพัสดุ</label>
                    <input type="text" name="search" class="form-control" placeholder="รหัส, ชื่อพัสดุ, สถานที่จัดเก็บ" value="{{ request('search') }}">
                </div>

                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">หมวดหมู่</label>
                    <select name="category" class="form-select">
                        <option value="">-- ทุกหมวดหมู่ --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" style="font-size: 12px; margin-bottom: 4px;">สถานะสต็อก</label>
                    <select name="low_stock" class="form-select">
                        <option value="">-- ทั้งหมด --</option>
                        <option value="1" {{ request('low_stock') ? 'selected' : '' }}>⚠️ เฉพาะพัสดุใกล้หมด (สต็อกต่ำ)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="bi bi-funnel"></i> ค้นหา
                    </button>
                    <a href="{{ route('spare-parts.index') }}" class="btn btn-secondary" title="ล้างการค้นหา">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>รหัสพัสดุ</th>
                        <th>ชื่อรายการพัสดุ / อะไหล่</th>
                        <th>หมวดหมู่</th>
                        <th>สถานที่เก็บ</th>
                        <th style="text-align: center;">คงเหลือ</th>
                        <th style="text-align: center;">จุดเตือนขั้นต่ำ</th>
                        <th style="text-align: right;">ราคา/หน่วย (บาท)</th>
                        <th style="text-align: center;">ปรับสต็อก</th>
                        <th style="text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parts as $part)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: #0f766e;">
                            {{ $part->part_code }}
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);">
                                {{ $part->name }}
                            </div>
                            @if($part->notes)
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $part->notes }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-secondary" style="font-size: 12px;">
                                {{ $part->category ?? 'ทั่วไป' }}
                            </span>
                        </td>
                        <td style="font-size: 13px; color: #475569;">
                            <i class="bi bi-geo-alt"></i> {{ $part->location ?? '-' }}
                        </td>
                        <td style="text-align: center;">
                            @if($part->isLowStock())
                                <span class="badge badge-danger" style="font-size: 13px; font-weight: 700; padding: 4px 10px;">
                                    {{ $part->stock_quantity }} {{ $part->unit }}
                                </span>
                            @else
                                <span class="badge badge-success" style="font-size: 13px; font-weight: 600; padding: 4px 10px;">
                                    {{ $part->stock_quantity }} {{ $part->unit }}
                                </span>
                            @endif
                        </td>
                        <td style="text-align: center; color: var(--text-muted); font-size: 13px;">
                            {{ $part->minimum_quantity }} {{ $part->unit }}
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            {{ number_format($part->unit_price, 2) }}
                        </td>
                        <td style="text-align: center;">
                            <!-- Quick Stock Adjustment Button -->
                            <button type="button" class="btn btn-secondary btn-sm" onclick="openAdjustModal('{{ $part->id }}', '{{ addslashes($part->name) }}', '{{ $part->stock_quantity }}', '{{ $part->unit }}')" title="รับเข้า หรือ ปรับยอด">
                                <i class="bi bi-arrow-down-up"></i> ปรับยอด
                            </button>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="{{ route('spare-parts.edit', $part) }}" class="btn btn-secondary btn-sm" title="แก้ไขข้อมูล">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('spare-parts.destroy', $part) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันลบพัสดุ {{ $part->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="ลบ">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="bi bi-box" style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
                            <div>ไม่พบรายการพัสดุหรืออะไหล่</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 18px 24px; border-top: 1px solid var(--border);">
            {{ $parts->links() }}
        </div>
    </div>
</div>

<!-- Stock Adjust Modal -->
<div id="adjustModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 14px; width: 100%; max-width: 420px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0;">ปรับยอดสต็อกพัสดุ</h3>
            <button type="button" onclick="closeAdjustModal()" style="background: none; border: none; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form id="adjustForm" method="POST">
            @csrf
            <div style="margin-bottom: 12px; font-size: 14px; font-weight: 600; color: #0d9488;" id="modalPartName"></div>
            <div style="margin-bottom: 16px; font-size: 13px; color: var(--text-muted);">
                ยอดคงเหลือปัจจุบัน: <span id="modalCurrentStock" style="font-weight: 700; color: var(--text-main);"></span>
            </div>

            <div class="form-group">
                <label class="form-label required">ประเภทการปรับปรุง</label>
                <div style="display: flex; gap: 14px;">
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 13.5px;">
                        <input type="radio" name="action_type" value="add" checked>
                        <span>รับเข้าเพิ่ม (บวกเพิ่ม)</span>
                    </label>
                    <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 13.5px;">
                        <input type="radio" name="action_type" value="set">
                        <span>กำหนดจำนวนใหม่ (นับสต็อก)</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label required" for="adjust_quantity">จำนวน (<span id="modalUnit"></span>)</label>
                <input type="number" id="adjust_quantity" name="quantity" class="form-control" value="1" min="0" required>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="closeAdjustModal()">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึกสต็อก</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAdjustModal(id, name, currentStock, unit) {
        document.getElementById('adjustForm').action = "/spare-parts/" + id + "/adjust-stock";
        document.getElementById('modalPartName').innerText = name;
        document.getElementById('modalCurrentStock').innerText = currentStock + ' ' + unit;
        document.getElementById('modalUnit').innerText = unit;
        document.getElementById('adjust_quantity').value = 1;

        const modal = document.getElementById('adjustModal');
        modal.style.display = 'flex';
    }

    function closeAdjustModal() {
        document.getElementById('adjustModal').style.display = 'none';
    }
</script>
@endpush
