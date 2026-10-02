@extends('layouts.app')

@section('title', 'รายงานสถานะและแหล่งงบประมาณครุภัณฑ์ IT')
@section('page_title', 'รายงานสถานะ มูลค่า และการกระจายตัวของครุภัณฑ์ IT')
@section('page_subtitle', 'กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง • สรุปแหล่งงบประมาณ วิธีการได้มา และระบบคุมสัญญาเครื่องพิมพ์เช่า')

@section('content')
<!-- Filter Bar -->
<div class="card no-print" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
    <div class="card-body" style="padding: 18px 24px;">
        <form action="{{ route('reports.assets') }}" method="GET">
            <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px;">
                <div style="display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
                    @if(auth()->user()->isUser())
                        <div>
                            <label style="font-weight: 600; font-size: 12.5px; color: var(--text-muted); display: block; margin-bottom: 4px;">แผนกของคุณ</label>
                            <span style="font-size: 13.5px; background: #e0f2fe; color: #0369a1; padding: 7px 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="bi bi-building"></i> {{ auth()->user()->department->name ?? 'แผนกของท่าน' }}
                            </span>
                        </div>
                    @else
                        <div>
                            <label style="font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">แผนก:</label>
                            <select name="department_id" class="form-select" style="width: 190px;">
                                <option value="">-- ทุกแผนก --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">สถานะการใช้งาน:</label>
                        <select name="status" class="form-select" style="width: 150px;">
                            <option value="">-- ทุกสถานะ --</option>
                            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>🟢 ใช้งานปกติ</option>
                            <option value="spare" {{ $status == 'spare' ? 'selected' : '' }}>🟡 เครื่องสำรอง</option>
                            <option value="repairing" {{ $status == 'repairing' ? 'selected' : '' }}>🔵 ส่งซ่อม</option>
                            <option value="broken" {{ $status == 'broken' ? 'selected' : '' }}>🔴 ชำรุดรอซ่อม</option>
                            <option value="disposed" {{ $status == 'disposed' ? 'selected' : '' }}>⚪ รอจำหน่าย</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">แหล่งเงินที่ใช้ซื้อ:</label>
                        <select name="budget_source_id" class="form-select" style="width: 170px;">
                            <option value="">-- ทุกแหล่งเงิน --</option>
                            @foreach($budgetSources as $bs)
                                <option value="{{ $bs->id }}" {{ ($budgetSourceId ?? '') == $bs->id ? 'selected' : '' }}>
                                    {{ $bs->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">วิธีการได้มา:</label>
                        <select name="acquisition_method_id" class="form-select" style="width: 170px;">
                            <option value="">-- ทุกวิธีการได้มา --</option>
                            @foreach($acquisitionMethods as $am)
                                <option value="{{ $am->id }}" {{ ($acquisitionMethodId ?? '') == $am->id ? 'selected' : '' }}>
                                    {{ $am->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">กรรมสิทธิ์:</label>
                        <select name="ownership_type" class="form-select" style="width: 150px;">
                            <option value="">-- ทุกประเภท --</option>
                            <option value="owned" {{ ($ownershipType ?? '') == 'owned' ? 'selected' : '' }}>🏢 เป็นเจ้าของ</option>
                            <option value="rented" {{ ($ownershipType ?? '') == 'rented' ? 'selected' : '' }}>📄 เช่าใช้ (Rental)</option>
                        </select>
                    </div>

                    <div>
                        <label style="font-weight: 600; font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 4px;">สัญญาเครื่องเช่า:</label>
                        <select name="rental_status" class="form-select" style="width: 160px;">
                            <option value="">-- ทุกสถานะสัญญา --</option>
                            <option value="active" {{ ($rentalStatus ?? '') == 'active' ? 'selected' : '' }}>🟢 ปกติในสัญญา</option>
                            <option value="expiring_soon" {{ ($rentalStatus ?? '') == 'expiring_soon' ? 'selected' : '' }}>⚠️ ใกล้หมดสัญญา (≤30วัน)</option>
                            <option value="expired" {{ ($rentalStatus ?? '') == 'expired' ? 'selected' : '' }}>🔴 หมดสัญญาแล้ว</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary" style="padding: 7px 16px;">
                            <i class="bi bi-search"></i> กรองข้อมูล
                        </button>
                        @if(request()->anyFilled(['department_id', 'status', 'budget_source_id', 'acquisition_method_id', 'ownership_type', 'rental_status']))
                            <a href="{{ route('reports.assets') }}" class="btn btn-light" style="border: 1px solid var(--border);" title="ล้างตัวกรอง">
                                <i class="bi bi-x-circle"></i> ล้างค่า
                            </a>
                        @endif
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('reports.export', ['type' => 'assets'] + request()->query()) }}" class="btn btn-secondary" style="font-weight: 600;">
                        <i class="bi bi-file-earmark-excel"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()" style="font-weight: 600;">
                        <i class="bi bi-printer"></i> สั่งพิมพ์รายงาน
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Report Header (Print & Screen) -->
<div style="text-align: center; margin-bottom: 24px;">
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 4px; color: #0f172a;">รายงานสถานะ มูลค่า และการกระจายตัวของครุภัณฑ์คอมพิวเตอร์และอุปกรณ์ IT</h2>
    <div style="font-size: 14px; color: var(--text-muted); font-weight: 500;">
        โรงพยาบาลทุ่งหัวช้าง @if(auth()->user()->isUser()) &bull; <strong>{{ auth()->user()->department->name ?? 'แผนกของท่าน' }}</strong> @endif &bull; ข้อมูล ณ วันที่ {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} น.
    </div>
</div>

<!-- Primary Metric Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; border-radius: 12px; border: 1px solid var(--border);">
        <div style="font-size: 12.5px; color: var(--text-muted); font-weight: 600;">ครุภัณฑ์และอุปกรณ์ทั้งหมด</div>
        <div style="font-size: 28px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ number_format($totalCount) }}</div>
        <div style="font-size: 11.5px; color: #64748b;">เครื่อง/รายการที่ดูแล</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px;">
        <div style="font-size: 12.5px; color: #065f46; font-weight: 600;">ใช้งานปกติ (Active)</div>
        <div style="font-size: 28px; font-weight: 800; color: #047857; margin-top: 4px;">{{ number_format($statusCounts['active']) }}</div>
        <div style="font-size: 11.5px; color: #065f46;">
            {{ $totalCount > 0 ? round(($statusCounts['active'] / $totalCount) * 100, 1) : 0 }}% ความพร้อมใช้งาน
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px;">
        <div style="font-size: 12.5px; color: #991b1b; font-weight: 600;">ชำรุด / ส่งซ่อม</div>
        <div style="font-size: 28px; font-weight: 800; color: #b91c1c; margin-top: 4px;">{{ number_format($statusCounts['repairing'] + $statusCounts['broken']) }}</div>
        <div style="font-size: 11.5px; color: #991b1b;">ต้องบำรุงรักษา / จัดสรรทดแทน</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 12px;">
        <div style="font-size: 12.5px; color: #0f766e; font-weight: 600;">มูลค่าจัดซื้อครุภัณฑ์รวม</div>
        <div style="font-size: 24px; font-weight: 800; color: #0d9488; margin-top: 4px;">{{ number_format($totalValue, 2) }}</div>
        <div style="font-size: 11.5px; color: #0f766e;">บาท (เฉพาะทรัพย์สิน รพ.)</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 18px; text-align: center; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 12px;">
        <div style="font-size: 12.5px; color: #1e40af; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 4px;">
            <i class="bi bi-printer-fill"></i> ครุภัณฑ์เช่า & ปริ้นเตอร์เช่า
        </div>
        <div style="font-size: 28px; font-weight: 800; color: #1d4ed8; margin-top: 4px;">
            {{ number_format($rentalStats['total']) }} <span style="font-size: 13px; font-weight: 600;">(ปริ้นเตอร์ {{ $rentalStats['printers'] }})</span>
        </div>
        <div style="font-size: 11.5px; color: #1e3a8a; font-weight: 600;">
            ค่าเช่า ฿{{ number_format($rentalStats['monthly_fee'], 2) }}/เดือน
            @if($rentalStats['expiring_soon'] > 0)
                <span class="badge" style="background: #f59e0b; color: white; font-size: 10.5px; margin-left: 4px;">⚠️ เตือน {{ $rentalStats['expiring_soon'] }}</span>
            @endif
        </div>
    </div>
</div>

<!-- Strategic Breakdown Analysis (3 Column Cards) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- Box 1: แหล่งเงินงบประมาณ -->
    <div class="card" style="margin-bottom: 0; border: 1px solid var(--border); border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-cash-coin" style="color: #059669; font-size: 17px;"></i>
                <span>จำแนกตามแหล่งเงินที่ใช้ซื้อ</span>
            </div>
            <a href="{{ route('budget-sources.index') }}" class="btn btn-sm btn-light no-print" style="font-size: 11px; padding: 2px 8px;" title="จัดการแหล่งเงิน">
                ตั้งค่า
            </a>
        </div>
        <div class="card-body" style="padding: 16px;">
            @php $hasBsData = false; @endphp
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($budgetSourceStats as $bs)
                    @if($bs->assets_count > 0)
                        @php $hasBsData = true; @endphp
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 4px;">
                                <span style="font-weight: 600; color: #1e293b;">
                                    <span class="badge" style="background: {{ $bs->color ?? '#0284c7' }}; color: white; margin-right: 6px; font-size: 10px;">{{ $bs->code }}</span>
                                    {{ $bs->name }}
                                </span>
                                <span style="font-weight: 700; color: #0f172a;">
                                    {{ number_format($bs->assets_count) }} เครื่อง
                                    <span style="font-weight: 400; color: var(--text-muted); font-size: 11.5px;">(฿{{ number_format($bs->total_value ?: 0) }})</span>
                                </span>
                            </div>
                            <div style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $totalCount > 0 ? min(100, round(($bs->assets_count / $totalCount) * 100)) : 0 }}%; background: {{ $bs->color ?? '#0284c7' }}; border-radius: 3px;"></div>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hasBsData)
                    <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                        ยังไม่มีข้อมูลการระบุแหล่งเงินงบประมาณในครุภัณฑ์
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Box 2: วิธีการได้มาของครุภัณฑ์ -->
    <div class="card" style="margin-bottom: 0; border: 1px solid var(--border); border-radius: 12px;">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; font-size: 14px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-box-seam" style="color: #0284c7; font-size: 17px;"></i>
                <span>จำแนกตามวิธีการได้มา</span>
            </div>
            <a href="{{ route('acquisition-methods.index') }}" class="btn btn-sm btn-light no-print" style="font-size: 11px; padding: 2px 8px;" title="จัดการวิธีการได้มา">
                ตั้งค่า
            </a>
        </div>
        <div class="card-body" style="padding: 16px;">
            @php $hasAmData = false; @endphp
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($acquisitionMethodStats as $am)
                    @if($am->assets_count > 0)
                        @php $hasAmData = true; @endphp
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 4px;">
                                <span style="font-weight: 600; color: #1e293b;">
                                    <span class="badge" style="background: {{ $am->color ?? '#0d9488' }}; color: white; margin-right: 6px; font-size: 10px;">{{ $am->code }}</span>
                                    {{ $am->name }}
                                </span>
                                <span style="font-weight: 700; color: #0f172a;">
                                    {{ number_format($am->assets_count) }} เครื่อง
                                    <span style="font-weight: 500; color: #64748b; font-size: 11.5px;">({{ $totalCount > 0 ? round(($am->assets_count / $totalCount) * 100, 1) : 0 }}%)</span>
                                </span>
                            </div>
                            <div style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: {{ $totalCount > 0 ? min(100, round(($am->assets_count / $totalCount) * 100)) : 0 }}%; background: {{ $am->color ?? '#0d9488' }}; border-radius: 3px;"></div>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hasAmData)
                    <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                        ยังไม่มีข้อมูลการระบุวิธีการได้มาในครุภัณฑ์
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Box 3: ระบบคุมเครื่องพิมพ์เช่า & สัญญาเช่า IT -->
    <div class="card" style="margin-bottom: 0; border: 1px solid #bfdbfe; border-radius: 12px; background: #f8fafc;">
        <div class="card-header" style="background: #eff6ff; border-bottom: 1px solid #bfdbfe; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between;">
            <div style="font-weight: 700; font-size: 14px; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-printer-fill" style="color: #2563eb; font-size: 17px;"></i>
                <span>สรุปการควบคุมเครื่องพิมพ์และเครื่องเช่า</span>
            </div>
            <span class="badge" style="background: #2563eb; color: white; font-size: 11px;">
                {{ $rentalStats['total'] }} เครื่องในระบบ
            </span>
        </div>
        <div class="card-body" style="padding: 16px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; text-align: center;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600;">เครื่องพิมพ์เช่า (Printers)</div>
                    <div style="font-size: 18px; font-weight: 800; color: #0284c7; margin-top: 2px;">{{ $rentalStats['printers'] }} เครื่อง</div>
                </div>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; text-align: center;">
                    <div style="font-size: 11px; color: #64748b; font-weight: 600;">ภาระค่าเช่ารายเดือน</div>
                    <div style="font-size: 18px; font-weight: 800; color: #059669; margin-top: 2px;">฿{{ number_format($rentalStats['monthly_fee'], 2) }}</div>
                </div>
            </div>

            <!-- Rental Status Overview -->
            <div style="background: white; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; font-size: 12.5px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="color: #047857; font-weight: 600;"><i class="bi bi-check-circle-fill"></i> สัญญาปกติ:</span>
                    <strong style="color: #047857;">{{ $rentalStats['total'] - $rentalStats['expiring_soon'] - $rentalStats['expired'] }} รายการ</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="color: #b45309; font-weight: 600;"><i class="bi bi-clock-history"></i> ใกล้หมดสัญญา (≤30 วัน):</span>
                    <strong style="color: #b45309;">{{ $rentalStats['expiring_soon'] }} รายการ</strong>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #b91c1c; font-weight: 600;"><i class="bi bi-exclamation-triangle-fill"></i> สิ้นสุดสัญญาแล้ว:</span>
                    <strong style="color: #b91c1c;">{{ $rentalStats['expired'] }} รายการ</strong>
                </div>
            </div>

            @if($rentalStats['expiring_soon'] > 0 || $rentalStats['expired'] > 0)
                <div style="margin-top: 10px; padding: 8px 12px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; font-size: 11.5px; color: #92400e;">
                    <i class="bi bi-info-circle-fill"></i> มีเครื่องเช่าที่ต้องประสานงานต่อสัญญาหรือส่งคืนบริษัท กรุณาตรวจสอบในตารางด้านล่าง
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Asset Table -->
<div class="card" style="border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
    <div class="card-header" style="background: #ffffff; border-bottom: 1px solid var(--border); padding: 16px 24px; display: flex; align-items: center; justify-content: space-between;">
        <div class="card-title" style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">
            <i class="bi bi-table text-primary"></i>
            <span>รายการครุภัณฑ์คอมพิวเตอร์และอุปกรณ์ไอที ({{ number_format($assets->count()) }} รายการ)</span>
        </div>
        <div class="no-print" style="font-size: 12.5px; color: var(--text-muted);">
            คลิกที่แถวหรือรหัสเพื่อดูรายละเอียดเชิงลึกและประวัติสัญญา
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" style="margin-bottom: 0;">
                <thead style="background: #f8fafc;">
                    <tr>
                        <th style="padding: 12px 16px;">รหัสครุภัณฑ์</th>
                        <th>ชื่อรายการ / รุ่น</th>
                        <th>ประเภท</th>
                        <th>แผนก / จุดวาง</th>
                        <th>กรรมสิทธิ์ / สัญญาเช่า</th>
                        <th>วิธีการได้มา / แหล่งเงิน</th>
                        <th>สถานะ</th>
                        <th style="text-align: right;">มูลค่า / ค่าเช่า</th>
                        <th>ปีงบฯ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr style="{{ $asset->is_rented ? 'background: rgba(240, 249, 255, 0.4);' : '' }}">
                        <td style="font-family: monospace; font-weight: 700; color: #0284c7; padding: 12px 16px;">
                            <a href="{{ route('assets.show', $asset) }}" style="text-decoration: none; color: inherit;" title="ดูข้อมูลครุภัณฑ์">
                                {{ $asset->asset_code }}
                            </a>
                            @if($asset->serial_number)
                                <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; font-weight: normal;">
                                    SN: {{ $asset->serial_number }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0f172a;">
                                <a href="{{ route('assets.show', $asset) }}" style="text-decoration: none; color: inherit;">
                                    {{ $asset->name }}
                                </a>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $asset->brand }} {{ $asset->model }}</div>
                        </td>
                        <td>
                            <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 11.5px; border: 1px solid #e2e8f0;">
                                {{ $asset->deviceType?->name ?? 'อุปกรณ์ทั่วไป' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $asset->department?->name ?? '-' }}</div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">{{ $asset->location_detail ?? '-' }}</div>
                        </td>
                        <td>
                            @if($asset->is_rented)
                                <span class="badge bg-info text-white" style="font-size: 11px; padding: 3px 8px;">
                                    <i class="bi bi-printer"></i> เครื่องเช่า
                                </span>
                                @if($asset->rental_contract_no)
                                    <div style="font-size: 11.5px; color: #0369a1; font-weight: 600; margin-top: 2px;">
                                        {{ $asset->rental_contract_no }}
                                    </div>
                                @endif
                                @if($asset->rental_vendor)
                                    <div style="font-size: 11px; color: #64748b;">
                                        {{ $asset->rental_vendor }}
                                    </div>
                                @endif
                                <div style="margin-top: 2px;">
                                    <span class="badge {{ $asset->rental_status_badge }}" style="font-size: 10px;">
                                        {{ $asset->rental_status_label }}
                                    </span>
                                </div>
                            @else
                                <span class="badge bg-secondary" style="font-size: 11px; padding: 3px 8px;">
                                    <i class="bi bi-building"></i> ทรัพย์สิน รพ.
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($asset->acquisitionMethod)
                                <div>
                                    <span class="badge" style="background: {{ $asset->acquisitionMethod->color ?? '#0d9488' }}; color: white; font-size: 10.5px;">
                                        {{ $asset->acquisitionMethod->name }}
                                    </span>
                                </div>
                            @endif
                            @if($asset->budgetSource)
                                <div style="margin-top: 3px;">
                                    <span class="badge" style="background: {{ $asset->budgetSource->color ?? '#0284c7' }}; color: white; font-size: 10.5px;">
                                        {{ $asset->budgetSource->name }}
                                    </span>
                                </div>
                            @endif
                            @if(!$asset->acquisitionMethod && !$asset->budgetSource)
                                <span style="color: var(--text-muted); font-size: 12px;">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $asset->status_badge }}" style="font-size: 11.5px;">
                                {{ $asset->status_label }}
                            </span>
                        </td>
                        <td style="text-align: right; font-weight: 600;">
                            @if($asset->is_rented)
                                <div style="color: #0284c7;">
                                    {{ $asset->rental_monthly_fee !== null ? '฿' . number_format($asset->rental_monthly_fee, 2) : '-' }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--text-muted); font-weight: normal;">ต่อเดือน</div>
                            @else
                                <div style="color: #0f172a;">
                                    {{ $asset->price ? '฿' . number_format($asset->price, 2) : '-' }}
                                </div>
                                <div style="font-size: 10.5px; color: var(--text-muted); font-weight: normal;">ราคาจัดซื้อ</div>
                            @endif
                        </td>
                        <td>{{ $asset->budget_year ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
                            <i class="bi bi-inbox" style="font-size: 32px; display: block; margin-bottom: 8px; color: #cbd5e1;"></i>
                            ไม่พบข้อมูลครุภัณฑ์ตามเงื่อนไขที่เลือก
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .topbar, .sidebar, .app-header {
        display: none !important;
    }
    body {
        background: white !important;
        color: black !important;
        font-size: 12px;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .table th, .table td {
        padding: 6px 8px !important;
        font-size: 11px !important;
    }
}
</style>
@endsection
