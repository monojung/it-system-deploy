@extends('layouts.app')

@section('title', 'แผนกในโรงพยาบาล')
@section('page_title', 'โครงสร้างแผนกและหน่วยงาน')
@section('page_subtitle', 'แผนก / งาน / ฝ่าย ในโรงพยาบาลทุ่งหัวช้าง')

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Departments List Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="bi bi-diagram-3 text-primary" style="font-size: 20px;"></i>
                <span>รายชื่อแผนกในโรงพยาบาล ({{ $departments->count() }} แผนก)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>รหัส</th>
                            <th>ชื่อแผนก / หน่วยงาน</th>
                            <th>อาคาร / ที่ตั้ง</th>
                            <th>เบอร์ภายใน</th>
                            <th style="text-align: center;">บุคลากร</th>
                            <th style="text-align: center;">ครุภัณฑ์</th>
                            <th style="text-align: center;">งานซ่อม</th>
                            <th style="text-align: center;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $dept)
                        <tr>
                            <td style="font-family: monospace; font-weight: 700; color: #0284c7;">
                                {{ $dept->code ?? '-' }}
                            </td>
                            <td>
                                <div style="font-weight: 600; color: var(--text-main);">
                                    {{ $dept->name }}
                                </div>
                            </td>
                            <td style="font-size: 13px; color: #475569;">{{ $dept->building ?? '-' }}</td>
                            <td style="font-size: 13px; font-family: monospace;">{{ $dept->phone ?? '-' }}</td>
                            <td style="text-align: center;">
                                <span class="badge badge-secondary">{{ $dept->users_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-primary">{{ $dept->assets_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-warning">{{ $dept->repairs_count }}</span>
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="editDept('{{ $dept->id }}', '{{ addslashes($dept->name) }}', '{{ $dept->code }}', '{{ addslashes($dept->building) }}', '{{ $dept->phone }}')" title="แก้ไข">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('departments.destroy', $dept) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันลบแผนก {{ $dept->name }}?')">
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
                            <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                ไม่พบข้อมูลแผนก
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add / Edit Department Card -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title" id="formCardTitle">
                    <i class="bi bi-plus-circle text-primary"></i>
                    <span>เพิ่มแผนกใหม่</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="btnResetForm" onclick="resetForm()" style="display: none;">
                    ยกเลิกแก้ไข
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('departments.store') }}" method="POST" id="deptForm">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">

                    <div class="form-group">
                        <label class="form-label required" for="dept_name">ชื่อแผนก / หน่วยงาน</label>
                        <input type="text" id="dept_name" name="name" class="form-control" placeholder="เช่น แผนกผู้ป่วยนอก (OPD)" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dept_code">รหัสย่อ (Code)</label>
                        <input type="text" id="dept_code" name="code" class="form-control" placeholder="เช่น OPD, ER, LAB">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dept_building">อาคาร / ชั้น</label>
                        <input type="text" id="dept_building" name="building" class="form-control" placeholder="เช่น อาคารผู้ป่วยนอก ชั้น 1">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dept_phone">เบอร์โทรศัพท์ภายใน</label>
                        <input type="text" id="dept_phone" name="phone" class="form-control" placeholder="เช่น ต่อ 111">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;" id="btnSubmitDept">
                        <i class="bi bi-floppy-fill"></i> บันทึกข้อมูลแผนก
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editDept(id, name, code, building, phone) {
        document.getElementById('deptForm').action = "/departments/" + id;
        document.getElementById('methodField').value = "PUT";
        document.getElementById('dept_name').value = name;
        document.getElementById('dept_code').value = code || '';
        document.getElementById('dept_building').value = building || '';
        document.getElementById('dept_phone').value = phone || '';
        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-pencil-square text-warning"></i> <span>แก้ไขแผนก</span>';
        document.getElementById('btnSubmitDept').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกการแก้ไข';
        document.getElementById('btnResetForm').style.display = 'inline-flex';
        document.getElementById('dept_name').focus();
    }

    function resetForm() {
        document.getElementById('deptForm').action = "{{ route('departments.store') }}";
        document.getElementById('methodField').value = "POST";
        document.getElementById('deptForm').reset();
        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-plus-circle text-primary"></i> <span>เพิ่มแผนกใหม่</span>';
        document.getElementById('btnSubmitDept').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกข้อมูลแผนก';
        document.getElementById('btnResetForm').style.display = 'none';
    }
</script>
@endpush
