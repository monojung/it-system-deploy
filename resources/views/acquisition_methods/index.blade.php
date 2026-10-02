@extends('layouts.app')

@section('title', 'วิธีการได้มาของครุภัณฑ์')
@section('page_title', 'วิธีการได้มาของครุภัณฑ์ (Acquisition Methods)')
@section('page_subtitle', 'จัดการประเภทและวิธีการได้มา เช่น จัดซื้อจัดจ้าง, บริจาค, เช่าใช้ (Rental / Lease), รับโอน')

@section('topbar-actions')
<a href="{{ route('assets.index') }}" class="topbar-btn" title="กลับไปยังทะเบียนครุภัณฑ์ IT">
    <i class="bi bi-pc-display text-primary"></i>
    <span>ทะเบียนครุภัณฑ์</span>
</a>
<a href="{{ route('budget-sources.index') }}" class="topbar-btn" title="ไปจัดการแหล่งเงินงบประมาณ">
    <i class="bi bi-wallet2 text-indigo"></i>
    <span>แหล่งเงินงบประมาณ</span>
</a>
@endsection

@section('content')
<!-- Header Metrics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="bi bi-box-arrow-in-down-right"></i>
        </div>
        <div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 500;">วิธีการได้มาทั้งหมด</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ $acquisitionMethods->count() }} <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">วิธี</span></div>
        </div>
    </div>

    <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="bi bi-hdd-network"></i>
        </div>
        <div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 500;">ครุภัณฑ์ที่ระบุวิธีการได้มา</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ number_format($totalAssets) }} <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">เครื่อง</span></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Acquisition Methods List Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <i class="bi bi-list-check text-success" style="font-size: 20px;"></i>
                <span>รายชื่อวิธีการได้มาของครุภัณฑ์ ({{ $acquisitionMethods->count() }} รายการ)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
                        <tr>
                            <th style="width: 100px;">รหัสย่อ</th>
                            <th>ชื่อวิธีการได้มา</th>
                            <th>คำอธิบาย</th>
                            <th style="text-align: center; width: 90px;">สถานะ</th>
                            <th style="text-align: center; width: 110px;">จำนวนครุภัณฑ์</th>
                            <th style="text-align: center; width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($acquisitionMethods as $method)
                        <tr>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #059669; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11.5px; font-weight: 700;">
                                    {{ $method->code ?: '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 13.5px;">
                                    {{ $method->name }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12.5px; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $method->description ?: '-' }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($method->is_active)
                                    <span class="badge bg-success" style="font-size: 11px; font-weight: 600;">ใช้งาน</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 11px; font-weight: 600;">ปิดใช้งาน</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($method->assets_count > 0)
                                    <a href="{{ route('assets.index', ['acquisition_method_id' => $method->id]) }}" 
                                       class="badge" 
                                       title="คลิกเพื่อดูรายการครุภัณฑ์ที่ได้มาด้วยวิธีนี้"
                                       style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; font-size: 12px; font-weight: 700; text-decoration: none; padding: 4px 8px; border-radius: 6px;">
                                        <i class="bi bi-box-arrow-up-right me-1" style="font-size: 10px;"></i>
                                        {{ number_format($method->assets_count) }} เครื่อง
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-size: 11px;">0 เครื่อง</span>
                                @endif
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-secondary btn-sm" 
                                            onclick="editAcquisitionMethod('{{ $method->id }}', '{{ addslashes($method->name) }}', '{{ addslashes($method->code ?? '') }}', '{{ addslashes($method->description ?? '') }}', {{ $method->is_active ? 'true' : 'false' }})" 
                                            title="แก้ไข" 
                                            style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('acquisition-methods.destroy', $method) }}" method="POST" style="display: inline;" onsubmit="return confirmDeleteAcquisitionMethod(event, '{{ addslashes($method->name) }}', {{ $method->assets_count }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="ลบวิธีการนี้" style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
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
                                ไม่พบข้อมูลวิธีการได้มา
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add / Edit Acquisition Method Card -->
    <div>
        <div class="card" style="position: sticky; top: 85px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title" id="formCardTitle">
                    <i class="bi bi-plus-circle text-success"></i>
                    <span>เพิ่มวิธีการได้มาใหม่</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="btnResetForm" onclick="resetForm()" style="display: none; font-size: 12px; padding: 4px 10px;">
                    ยกเลิกแก้ไข
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('acquisition-methods.store') }}" method="POST" id="acquisitionForm">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">

                    <div class="form-group mb-3">
                        <label class="form-label required" for="method_name">ชื่อวิธีการได้มาของครุภัณฑ์</label>
                        <input type="text" id="method_name" name="name" class="form-control" placeholder="เช่น จัดซื้อจัดจ้าง, ได้รับบริจาค, เช่าใช้" required autofocus>
                        <span class="form-text" style="font-size: 11.5px; color: #64748b;">ระบุวิธีการได้มา เช่น จัดซื้อจัดจ้าง, บริจาค, เช่าใช้บริการ, รับโอนจากภายนอก</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="method_code">รหัสย่อ (Code)</label>
                        <input type="text" id="method_code" name="code" class="form-control" placeholder="เช่น PURCHASE, DONATION, RENTAL, TRANSFER" style="font-family: monospace; text-transform: uppercase;">
                        <span class="form-text" style="font-size: 11.5px; color: #64748b;">รหัสตัวอักษรภาษาอังกฤษสั้นๆ ใช้สำหรับระบบรายงาน</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="method_desc">คำอธิบายเพิ่มเติม</label>
                        <textarea id="method_desc" name="description" class="form-control" rows="3" placeholder="เช่น การจัดซื้อตาม พ.ร.บ. จัดซื้อจัดจ้างภาครัฐ หรือ ได้รับมอบจากผู้บริจาค"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="method_is_active" value="1" checked>
                        <label class="form-check-label" for="method_is_active" style="font-size: 13px; font-weight: 600;">
                            เปิดใช้งานในระบบ
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%; height: 42px; font-weight: 700; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;" id="btnSubmit">
                        <i class="bi bi-floppy-fill"></i> บันทึกวิธีการได้มา
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editAcquisitionMethod(id, name, code, description, isActive) {
        document.getElementById('acquisitionForm').action = "{{ url('/acquisition-methods') }}/" + id;
        document.getElementById('methodField').value = "PUT";
        document.getElementById('method_name').value = name;
        document.getElementById('method_code').value = code || '';
        document.getElementById('method_desc').value = description || '';
        document.getElementById('method_is_active').checked = Boolean(isActive);

        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-pencil-square text-warning"></i> <span>แก้ไขวิธีการได้มา</span>';
        document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกการแก้ไข';
        document.getElementById('btnResetForm').style.display = 'inline-flex';
        document.getElementById('method_name').focus();
    }

    function resetForm() {
        document.getElementById('acquisitionForm').action = "{{ route('acquisition-methods.store') }}";
        document.getElementById('methodField').value = "POST";
        document.getElementById('acquisitionForm').reset();
        document.getElementById('method_is_active').checked = true;

        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-plus-circle text-success"></i> <span>เพิ่มวิธีการได้มาใหม่</span>';
        document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกวิธีการได้มา';
        document.getElementById('btnResetForm').style.display = 'none';
    }

    function confirmDeleteAcquisitionMethod(event, name, assetsCount) {
        if (assetsCount > 0) {
            event.preventDefault();
            alert("ไม่สามารถลบวิธีการได้มา '" + name + "' ได้ เนื่องจากมีครุภัณฑ์ในระบบอ้างอิงอยู่ " + assetsCount + " รายการ\nกรุณาย้ายหรือเปลี่ยนวิธีการได้มาของครุภัณฑ์ก่อนดำเนินการ");
            return false;
        }
        return confirm("คุณต้องการลบวิธีการได้มา '" + name + "' ใช่หรือไม่?");
    }
</script>
@endpush
