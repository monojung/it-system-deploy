@extends('layouts.app')

@section('title', 'ประเภทอุปกรณ์ครุภัณฑ์')
@section('page_title', 'ประเภทอุปกรณ์ครุภัณฑ์คอมพิวเตอร์')
@section('page_subtitle', 'จัดการหมวดหมู่และประเภทอุปกรณ์ (Device Types) ของครุภัณฑ์ IT ทั้งหมดในระบบ')

@section('topbar-actions')
<a href="{{ route('assets.index') }}" class="topbar-btn" title="กลับไปยังทะเบียนครุภัณฑ์ IT">
    <i class="bi bi-pc-display text-primary"></i>
    <span>ทะเบียนครุภัณฑ์</span>
</a>
@endsection

@section('content')
<!-- Header Metrics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="bi bi-hdd-network"></i>
        </div>
        <div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 500;">ประเภทอุปกรณ์ทั้งหมด</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ $deviceTypes->count() }} <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">ประเภท</span></div>
        </div>
    </div>

    <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="bi bi-cpu-fill"></i>
        </div>
        <div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 500;">ครุภัณฑ์ที่ผูกในระบบ</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ number_format($totalAssets) }} <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">เครื่อง</span></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Device Types List Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <i class="bi bi-list-ul text-primary" style="font-size: 20px;"></i>
                <span>รายชื่อประเภทอุปกรณ์ครุภัณฑ์ ({{ $deviceTypes->count() }} รายการ)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
                        <tr>
                            <th style="width: 50px; text-align: center;">ไอคอน</th>
                            <th style="width: 90px;">รหัสย่อ</th>
                            <th>ชื่อประเภทอุปกรณ์</th>
                            <th>คำอธิบาย</th>
                            <th style="text-align: center; width: 110px;">จำนวนครุภัณฑ์</th>
                            <th style="text-align: center; width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deviceTypes as $type)
                        <tr>
                            <td style="text-align: center;">
                                <div style="width: 36px; height: 36px; border-radius: 9px; background: #f0fdf4; color: #0d9488; border: 1px solid #ccfbf1; display: inline-flex; align-items: center; justify-content: center; font-size: 18px;">
                                    <i class="bi {{ $type->icon ?: 'bi-hdd-network' }}"></i>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #0284c7; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11.5px; font-weight: 700;">
                                    {{ $type->code ?: '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 13.5px;">
                                    {{ $type->name }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12.5px; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $type->description ?: '-' }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($type->assets_count > 0)
                                    <a href="{{ route('assets.index', ['device_type_id' => $type->id]) }}" 
                                       class="badge" 
                                       title="คลิกเพื่อดูรายการครุภัณฑ์ประเภทนี้ทั้งหมด"
                                       style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 12px; font-weight: 700; text-decoration: none; padding: 4px 8px; border-radius: 6px;">
                                        <i class="bi bi-box-arrow-up-right me-1" style="font-size: 10px;"></i>
                                        {{ number_format($type->assets_count) }} เครื่อง
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-size: 11px;">0 เครื่อง</span>
                                @endif
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-secondary btn-sm" 
                                            onclick="editDeviceType('{{ $type->id }}', '{{ addslashes($type->name) }}', '{{ addslashes($type->code ?? '') }}', '{{ addslashes($type->icon ?? '') }}', '{{ addslashes($type->description ?? '') }}')" 
                                            title="แก้ไข" 
                                            style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('device-types.destroy', $type) }}" method="POST" style="display: inline;" onsubmit="return confirmDeleteDeviceType(event, '{{ addslashes($type->name) }}', {{ $type->assets_count }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="ลบประเภทนี้" style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                ไม่พบข้อมูลประเภทอุปกรณ์
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add / Edit Device Type Card -->
    <div>
        <div class="card" style="position: sticky; top: 85px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title" id="formCardTitle">
                    <i class="bi bi-plus-circle text-primary"></i>
                    <span>เพิ่มประเภทอุปกรณ์ใหม่</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="btnResetForm" onclick="resetForm()" style="display: none; font-size: 12px; padding: 4px 10px;">
                    ยกเลิกแก้ไข
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('device-types.store') }}" method="POST" id="typeForm">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">

                    <div class="form-group mb-3">
                        <label class="form-label required" for="type_name">ชื่อประเภทอุปกรณ์</label>
                        <input type="text" id="type_name" name="name" class="form-control" placeholder="เช่น คอมพิวเตอร์ตั้งโต๊ะ (PC), เครื่องพิมพ์เลเซอร์" required autofocus>
                        <span class="form-text" style="font-size: 11.5px; color: #64748b;">ชื่อหมวดหมู่อุปกรณ์ที่เข้าใจง่ายสำหรับระบุในระบบ</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="type_code">รหัสย่อ (Code)</label>
                        <input type="text" id="type_code" name="code" class="form-control" placeholder="เช่น PC, NB, AIO, PRN, SCN, UPS" style="font-family: monospace; text-transform: uppercase;">
                        <span class="form-text" style="font-size: 11.5px; color: #64748b;">รหัสตัวอักษรภาษาอังกฤษสั้นๆ ใช้สำหรับจับคู่ระบบ Telemetry & Import</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="type_icon">
                            ไอคอน Bootstrap Icon
                            <span id="iconPreview" style="margin-left: 8px; color: #0d9488; font-size: 16px;">
                                <i class="bi bi-hdd-network"></i>
                            </span>
                        </label>
                        <input type="text" id="type_icon" name="icon" class="form-control" placeholder="เช่น bi-pc-display, bi-laptop" value="bi-hdd-network" oninput="updateIconPreview(this.value)">
                        
                        <!-- Quick Icon Picker -->
                        <div style="margin-top: 8px;">
                            <span style="font-size: 11px; color: #64748b; display: block; margin-bottom: 4px;">เลือกไอคอนด่วน:</span>
                            <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                @php
                                    $popularIcons = [
                                        'bi-pc-display' => 'PC',
                                        'bi-laptop' => 'Notebook',
                                        'bi-display' => 'All-in-One',
                                        'bi-printer' => 'Printer',
                                        'bi-printer-fill' => 'Dot Matrix',
                                        'bi-upc-scan' => 'Barcode',
                                        'bi-credit-card-2-front' => 'Card Reader',
                                        'bi-hdd-rack' => 'Server',
                                        'bi-router' => 'Network',
                                        'bi-battery-charging' => 'UPS',
                                        'bi-tablet' => 'Tablet',
                                        'bi-camera-video' => 'CCTV',
                                        'bi-projector' => 'Projector',
                                        'bi-tv' => 'TV',
                                        'bi-hdd-network' => 'Device'
                                    ];
                                @endphp
                                @foreach($popularIcons as $ic => $lbl)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary" 
                                            onclick="selectPresetIcon('{{ $ic }}')" 
                                            title="{{ $lbl }} ({{ $ic }})"
                                            style="padding: 3px 8px; font-size: 13px; border-radius: 6px;">
                                        <i class="bi {{ $ic }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="type_desc">คำอธิบายเพิ่มเติม</label>
                        <textarea id="type_desc" name="description" class="form-control" rows="3" placeholder="ระบุรายละเอียด เช่น มาตรฐานการจัดซื้อ, รูปแบบการใช้งาน"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-weight: 700; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;" id="btnSubmitType">
                        <i class="bi bi-floppy-fill"></i> บันทึกประเภทอุปกรณ์
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editDeviceType(id, name, code, icon, description) {
        document.getElementById('typeForm').action = "{{ url('/device-types') }}/" + id;
        document.getElementById('methodField').value = "PUT";
        document.getElementById('type_name').value = name;
        document.getElementById('type_code').value = code || '';
        document.getElementById('type_icon').value = icon || 'bi-hdd-network';
        document.getElementById('type_desc').value = description || '';
        updateIconPreview(icon || 'bi-hdd-network');

        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-pencil-square text-warning"></i> <span>แก้ไขประเภทอุปกรณ์</span>';
        document.getElementById('btnSubmitType').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกการแก้ไข';
        document.getElementById('btnResetForm').style.display = 'inline-flex';
        document.getElementById('type_name').focus();
    }

    function resetForm() {
        document.getElementById('typeForm').action = "{{ route('device-types.store') }}";
        document.getElementById('methodField').value = "POST";
        document.getElementById('typeForm').reset();
        document.getElementById('type_icon').value = 'bi-hdd-network';
        updateIconPreview('bi-hdd-network');

        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-plus-circle text-primary"></i> <span>เพิ่มประเภทอุปกรณ์ใหม่</span>';
        document.getElementById('btnSubmitType').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกประเภทอุปกรณ์';
        document.getElementById('btnResetForm').style.display = 'none';
    }

    function selectPresetIcon(iconClass) {
        document.getElementById('type_icon').value = iconClass;
        updateIconPreview(iconClass);
    }

    function updateIconPreview(iconClass) {
        const preview = document.getElementById('iconPreview');
        if (iconClass && iconClass.trim() !== '') {
            preview.innerHTML = `<i class="bi ${iconClass.trim()}"></i>`;
        } else {
            preview.innerHTML = `<i class="bi bi-hdd-network"></i>`;
        }
    }

    function confirmDeleteDeviceType(event, name, assetsCount) {
        if (assetsCount > 0) {
            event.preventDefault();
            if (window.Swal) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่สามารถลบได้',
                    text: `ประเภทอุปกรณ์ "${name}" มีครุภัณฑ์ผูกใช้งานอยู่จำนวน ${assetsCount} เครื่อง กรุณาย้ายหรือเปลี่ยนประเภทครุภัณฑ์ก่อนดำเนินการลบ`,
                    confirmButtonText: 'รับทราบ',
                    confirmButtonColor: '#0f766e'
                });
            } else {
                alert(`ไม่สามารถลบประเภทอุปกรณ์ "${name}" ได้ เนื่องจากมีครุภัณฑ์ผูกอยู่ ${assetsCount} เครื่อง`);
            }
            return false;
        }

        if (window.Swal) {
            event.preventDefault();
            Swal.fire({
                title: `ยืนยันลบประเภทอุปกรณ์?`,
                text: `คุณต้องการลบประเภท "${name}" ออกจากระบบใช่หรือไม่?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-trash me-1"></i> ยืนยันลบ',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });
            return false;
        }

        return confirm(`ยืนยันลบประเภทอุปกรณ์ "${name}"?`);
    }
</script>
@endpush
