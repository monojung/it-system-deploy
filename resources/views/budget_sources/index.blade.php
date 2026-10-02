@extends('layouts.app')

@section('title', 'แหล่งเงินงบประมาณ / เงินที่ใช้จัดซื้อ')
@section('page_title', 'แหล่งเงินงบประมาณ (Budget Sources)')
@section('page_subtitle', 'จัดการแหล่งเงินที่ใช้จัดซื้อครุภัณฑ์ เช่น เงิน UC, เงินบำรุง/เงินเก็บค่าบริการ, เงินงบประมาณแผ่นดิน')

@section('topbar-actions')
<a href="{{ route('assets.index') }}" class="topbar-btn" title="กลับไปยังทะเบียนครุภัณฑ์ IT">
    <i class="bi bi-pc-display text-primary"></i>
    <span>ทะเบียนครุภัณฑ์</span>
</a>
<a href="{{ route('acquisition-methods.index') }}" class="topbar-btn" title="ไปจัดการวิธีการได้มาของครุภัณฑ์">
    <i class="bi bi-box-arrow-in-down-right text-success"></i>
    <span>วิธีการได้มา</span>
</a>
@endsection

@section('content')
<!-- Header Metrics Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 500;">แหล่งเงินงบประมาณทั้งหมด</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ $budgetSources->count() }} <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">แหล่งเงิน</span></div>
        </div>
    </div>

    <div class="card" style="padding: 18px 20px; display: flex; align-items: center; gap: 16px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 24px;">
            <i class="bi bi-cpu-fill"></i>
        </div>
        <div>
            <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 500;">ครุภัณฑ์ที่ระบุแหล่งเงิน</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);">{{ number_format($totalAssets) }} <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">เครื่อง</span></div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Budget Sources List Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="card-title">
                <i class="bi bi-wallet2 text-primary" style="font-size: 20px;"></i>
                <span>รายชื่อแหล่งเงินงบประมาณ ({{ $budgetSources->count() }} แหล่งเงิน)</span>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="font-size: 13px;">
                    <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
                        <tr>
                            <th style="width: 90px;">รหัสย่อ</th>
                            <th>ชื่อแหล่งเงินงบประมาณ</th>
                            <th>คำอธิบาย</th>
                            <th style="text-align: center; width: 90px;">สถานะ</th>
                            <th style="text-align: center; width: 110px;">จำนวนครุภัณฑ์</th>
                            <th style="text-align: center; width: 100px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($budgetSources as $source)
                        <tr>
                            <td>
                                <span class="badge" style="background: #f1f5f9; color: #4338ca; border: 1px solid #cbd5e1; font-family: monospace; font-size: 11.5px; font-weight: 700;">
                                    {{ $source->code ?: '-' }}
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main); font-size: 13.5px;">
                                    {{ $source->name }}
                                </div>
                            </td>
                            <td>
                                <div style="font-size: 12.5px; color: var(--text-muted); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $source->description ?: '-' }}
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($source->is_active)
                                    <span class="badge bg-success" style="font-size: 11px; font-weight: 600;">ใช้งาน</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 11px; font-weight: 600;">ปิดใช้งาน</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                @if($source->assets_count > 0)
                                    <a href="{{ route('assets.index', ['budget_source_id' => $source->id]) }}" 
                                       class="badge" 
                                       title="คลิกเพื่อดูรายการครุภัณฑ์ที่ใช้แหล่งเงินนี้"
                                       style="background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; font-size: 12px; font-weight: 700; text-decoration: none; padding: 4px 8px; border-radius: 6px;">
                                        <i class="bi bi-box-arrow-up-right me-1" style="font-size: 10px;"></i>
                                        {{ number_format($source->assets_count) }} เครื่อง
                                    </a>
                                @else
                                    <span class="badge bg-light text-muted border" style="font-size: 11px;">0 เครื่อง</span>
                                @endif
                            </td>
                            <td style="text-align: center; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 4px;">
                                    <button type="button" class="btn btn-secondary btn-sm" 
                                            onclick="editBudgetSource('{{ $source->id }}', '{{ addslashes($source->name) }}', '{{ addslashes($source->code ?? '') }}', '{{ addslashes($source->description ?? '') }}', {{ $source->is_active ? 'true' : 'false' }})" 
                                            title="แก้ไข" 
                                            style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('budget-sources.destroy', $source) }}" method="POST" style="display: inline;" onsubmit="return confirmDeleteBudgetSource(event, '{{ addslashes($source->name) }}', {{ $source->assets_count }})">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="ลบแหล่งเงินนี้" style="padding: 4px 8px; font-size: 12px; border-radius: 6px;">
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
                                ไม่พบข้อมูลแหล่งเงินงบประมาณ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add / Edit Budget Source Card -->
    <div>
        <div class="card" style="position: sticky; top: 85px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title" id="formCardTitle">
                    <i class="bi bi-plus-circle text-primary"></i>
                    <span>เพิ่มแหล่งเงินงบประมาณใหม่</span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="btnResetForm" onclick="resetForm()" style="display: none; font-size: 12px; padding: 4px 10px;">
                    ยกเลิกแก้ไข
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('budget-sources.store') }}" method="POST" id="budgetSourceForm">
                    @csrf
                    <input type="hidden" name="_method" id="methodField" value="POST">

                    <div class="form-group mb-3">
                        <label class="form-label required" for="source_name">ชื่อแหล่งเงินงบประมาณ / เงินที่ใช้ซื้อ</label>
                        <input type="text" id="source_name" name="name" class="form-control" placeholder="เช่น เงิน UC, เงินเก็บค่าบริการ (เงินบำรุง)" required autofocus>
                        <span class="form-text" style="font-size: 11.5px; color: #64748b;">ระบุประเภทเงิน เช่น เงิน UC, เงินบำรุง, เงินงบประมาณแผ่นดิน, เงินบริจาค</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="source_code">รหัสย่อ (Code)</label>
                        <input type="text" id="source_code" name="code" class="form-control" placeholder="เช่น UC, REVENUE, GOV, DONATE" style="font-family: monospace; text-transform: uppercase;">
                        <span class="form-text" style="font-size: 11.5px; color: #64748b;">รหัสย่อภาษาอังกฤษสั้นๆ ใช้สำหรับระบบรายงานและออกเอกสาร</span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label" for="source_desc">คำอธิบายเพิ่มเติม</label>
                        <textarea id="source_desc" name="description" class="form-control" rows="3" placeholder="เช่น กองทุนหลักประกันสุขภาพถ้วนหน้า, รายได้เงินบำรุงโรงพยาบาล"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="source_is_active" value="1" checked>
                        <label class="form-check-label" for="source_is_active" style="font-size: 13px; font-weight: 600;">
                            เปิดใช้งานในระบบ
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; height: 42px; font-weight: 700; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;" id="btnSubmit">
                        <i class="bi bi-floppy-fill"></i> บันทึกแหล่งเงิน
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editBudgetSource(id, name, code, description, isActive) {
        document.getElementById('budgetSourceForm').action = "{{ url('/budget-sources') }}/" + id;
        document.getElementById('methodField').value = "PUT";
        document.getElementById('source_name').value = name;
        document.getElementById('source_code').value = code || '';
        document.getElementById('source_desc').value = description || '';
        document.getElementById('source_is_active').checked = Boolean(isActive);

        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-pencil-square text-warning"></i> <span>แก้ไขแหล่งเงินงบประมาณ</span>';
        document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกการแก้ไข';
        document.getElementById('btnResetForm').style.display = 'inline-flex';
        document.getElementById('source_name').focus();
    }

    function resetForm() {
        document.getElementById('budgetSourceForm').action = "{{ route('budget-sources.store') }}";
        document.getElementById('methodField').value = "POST";
        document.getElementById('budgetSourceForm').reset();
        document.getElementById('source_is_active').checked = true;

        document.getElementById('formCardTitle').innerHTML = '<i class="bi bi-plus-circle text-primary"></i> <span>เพิ่มแหล่งเงินงบประมาณใหม่</span>';
        document.getElementById('btnSubmit').innerHTML = '<i class="bi bi-floppy-fill"></i> บันทึกแหล่งเงิน';
        document.getElementById('btnResetForm').style.display = 'none';
    }

    function confirmDeleteBudgetSource(event, name, assetsCount) {
        if (assetsCount > 0) {
            event.preventDefault();
            alert("ไม่สามารถลบแหล่งเงิน '" + name + "' ได้ เนื่องจากมีครุภัณฑ์ในระบบอ้างอิงอยู่ " + assetsCount + " รายการ\nกรุณาย้ายหรือเปลี่ยนแหล่งเงินของครุภัณฑ์ก่อนดำเนินการ");
            return false;
        }
        return confirm("คุณต้องการลบแหล่งเงิน '" + name + "' ใช่หรือไม่?");
    }
</script>
@endpush
