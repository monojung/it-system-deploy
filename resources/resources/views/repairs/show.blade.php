@extends('layouts.app')

@section('title', 'ใบแจ้งซ่อม ' . $repair->ticket_number)
@section('page_title', 'รายละเอียดใบแจ้งซ่อม ' . $repair->ticket_number)
@section('page_subtitle', 'สถานะปัจจุบัน: ' . $repair->status_label . ' | แผนก: ' . $repair->department?->name)

@section('content')
<!-- Action Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <a href="{{ route('repairs.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> รายการทั้งหมด
        </a>
        <span class="badge {{ $repair->urgency_badge }}" style="font-size: 13px; padding: 6px 14px;">
            <i class="bi bi-lightning-charge"></i> {{ $repair->urgency_label }}
        </span>
        <span class="badge {{ $repair->status_badge }}" style="font-size: 13px; padding: 6px 14px;">
            <i class="bi bi-circle-fill" style="font-size: 8px;"></i> {{ $repair->status_label }}
        </span>
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="{{ route('repairs.print', $repair) }}" target="_blank" class="btn btn-secondary">
            <i class="bi bi-printer"></i>
            <span>พิมพ์ใบงานซ่อม</span>
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->isTechnician() || ($repair->status === 'pending' && $repair->requester_id === auth()->id()))
        <a href="{{ route('repairs.edit', $repair) }}" class="btn btn-secondary">
            <i class="bi bi-pencil"></i>
            <span>แก้ไขข้อมูล</span>
        </a>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 3fr 2fr; gap: 24px;">
    <!-- Left Column: Details & Parts -->
    <div>
        <!-- Main Ticket Details -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span>ข้อมูลปัญหาและการแจ้งซ่อม</span>
                </div>
                <span style="font-size: 12.5px; color: var(--text-muted);">
                    แจ้งเมื่อ: {{ $repair->created_at->format('d/m/Y H:i') }} น.
                </span>
            </div>
            <div class="card-body">
                <h2 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 12px;">
                    {{ $repair->title }}
                </h2>

                <div style="background: #f8fafc; border-radius: 8px; padding: 16px; border: 1px solid var(--border); margin-bottom: 20px; font-size: 14.5px; line-height: 1.6; white-space: pre-line;">
                    {{ $repair->description }}
                </div>

                @if($repair->attachment_image)
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-muted);">รูปภาพประกอบปัญหา:</div>
                    <a href="{{ asset($repair->attachment_image) }}" target="_blank">
                        <img src="{{ asset($repair->attachment_image) }}" alt="ภาพปัญหา" style="max-width: 100%; max-height: 320px; border-radius: 8px; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                    </a>
                </div>
                @endif

                <!-- Contact & Location Info -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; border-top: 1px solid var(--border); padding-top: 18px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">แผนก / หน่วยงาน:</div>
                        <div style="font-weight: 600; font-size: 14px;">{{ $repair->department?->name }}</div>
                        <div style="font-size: 12px; color: #64748b;">จุดที่ตั้ง: {{ $repair->location_detail ?? 'ไม่ระบุ' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">ผู้แจ้งซ่อม:</div>
                        <div style="font-weight: 600; font-size: 14px;">{{ $repair->requester_name }}</div>
                        <div style="font-size: 12px; color: #64748b;">เบอร์ติดต่อ: {{ $repair->requester_phone }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linked Asset Info -->
        @if($repair->asset)
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-pc-display text-info"></i>
                    <span>ครุภัณฑ์คอมพิวเตอร์ที่เกี่ยวข้อง</span>
                </div>
                <a href="{{ route('assets.show', $repair->asset) }}" class="btn btn-secondary btn-sm">
                    <span>ดูประวัติเครื่อง</span>
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">รหัสครุภัณฑ์:</div>
                        <div style="font-weight: 700; color: #0284c7; font-size: 15px;">{{ $repair->asset->asset_code }}</div>
                        <div style="font-size: 12px; color: #64748b;">Serial No: {{ $repair->asset->serial_number ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">ชื่อรายการ / รุ่น:</div>
                        <div style="font-weight: 600; font-size: 14px;">{{ $repair->asset->name }}</div>
                        <div style="font-size: 12px; color: #64748b;">{{ $repair->asset->brand }} {{ $repair->asset->model }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">สเปกเครื่อง:</div>
                        <div style="font-size: 12.5px; color: #334155;">{{ $repair->asset->specs ?? 'ไม่ได้ระบุ' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted);">IP / MAC Address:</div>
                        <div style="font-size: 12.5px; font-family: monospace;">{{ $repair->asset->ip_address ?? '-' }}</div>
                        <div style="font-size: 11px; font-family: monospace; color: #64748b;">{{ $repair->asset->mac_address ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($repair->other_device_info)
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-hdd-network text-info"></i>
                    <span>อุปกรณ์ทั่วไป (ไม่มีรหัสครุภัณฑ์)</span>
                </div>
            </div>
            <div class="card-body">
                <div style="font-size: 14px; font-weight: 500;">{{ $repair->other_device_info }}</div>
            </div>
        </div>
        @endif

        <!-- Repair Resolution & Parts Used -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-wrench-adjustable text-success"></i>
                    <span>สรุปผลการซ่อมและรายการอะไหล่ที่ใช้</span>
                </div>
                <div style="font-weight: 700; color: #0f766e; font-size: 15px;">
                    ค่าใช้จ่ายรวม: {{ number_format($repair->total_cost, 2) }} บาท
                </div>
            </div>
            <div class="card-body">
                @if($repair->cause || $repair->solution || $repair->external_repair_detail)
                <div style="margin-bottom: 20px;">
                    @if($repair->cause)
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: 600; font-size: 13.5px; color: var(--text-main);">สาเหตุของปัญหา:</span>
                        <div style="padding: 10px 14px; background: #f8fafc; border-radius: 6px; font-size: 13.5px; margin-top: 4px;">
                            {{ $repair->cause }}
                        </div>
                    </div>
                    @endif

                    @if($repair->solution)
                    <div style="margin-bottom: 10px;">
                        <span style="font-weight: 600; font-size: 13.5px; color: #065f46;">วิธีการแก้ไข:</span>
                        <div style="padding: 10px 14px; background: #ecfdf5; border-radius: 6px; font-size: 13.5px; margin-top: 4px; border: 1px solid #a7f3d0; color: #065f46;">
                            {{ $repair->solution }}
                        </div>
                    </div>
                    @endif

                    @if($repair->external_repair_detail)
                    <div>
                        <span style="font-weight: 600; font-size: 13.5px; color: #6b21a8;">ข้อมูลการส่งซ่อมภายนอก:</span>
                        <div style="padding: 10px 14px; background: #f3e8ff; border-radius: 6px; font-size: 13.5px; margin-top: 4px; border: 1px solid #d8b4fe; color: #581c87;">
                            {{ $repair->external_repair_detail }}
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Parts Table -->
                <div style="font-weight: 600; font-size: 14px; margin-bottom: 10px;">
                    <i class="bi bi-box-seam"></i> รายการอะไหล่ / พัสดุที่เบิกใช้
                </div>

                @if($repair->parts->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัสพัสดุ</th>
                                <th>รายการ</th>
                                <th style="text-align: center;">จำนวน</th>
                                <th style="text-align: right;">ราคาต่อหน่วย (บาท)</th>
                                <th style="text-align: right;">รวม (บาท)</th>
                                @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
                                <th style="text-align: center;">ลบ</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repair->parts as $part)
                            <tr>
                                <td style="font-family: monospace; font-weight: 600;">{{ $part->sparePart?->part_code }}</td>
                                <td>{{ $part->sparePart?->name }}</td>
                                <td style="text-align: center;">{{ $part->quantity }} {{ $part->sparePart?->unit }}</td>
                                <td style="text-align: right;">{{ number_format($part->unit_price, 2) }}</td>
                                <td style="text-align: right; font-weight: 600;">{{ number_format($part->total_price, 2) }}</td>
                                @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
                                <td style="text-align: center;">
                                    <form action="{{ route('repairs.remove-part', [$repair, $part]) }}" method="POST" onsubmit="return confirm('ยืนยันยกเลิกการเบิกอะไหล่นี้และคืนสต็อก?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 700;">ยอดรวมค่าอะไหล่ทั้งสิ้น:</td>
                                <td style="text-align: right; font-weight: 700; color: #0d9488; font-size: 15px;">
                                    {{ number_format($repair->total_cost, 2) }} บาท
                                </td>
                                @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())<td></td>@endif
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div style="background: #f8fafc; border-radius: 8px; padding: 18px; text-align: center; color: var(--text-muted); font-size: 13px;">
                    ไม่มีการเบิกใช้อะไหล่ในงานซ่อมนี้ (ซ่อมแซมแก้ไขโดยไม่ใช้อะไหล่)
                </div>
                @endif
            </div>
        </div>

        <!-- Satisfaction Rating -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span>การประเมินความพึงพอใจการให้บริการ</span>
                </div>
            </div>
            <div class="card-body">
                @if($repair->satisfaction_score)
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="font-size: 28px; color: #f59e0b;">
                        @for($s = 1; $s <= 5; $s++)
                            <i class="bi bi-star{{ $s <= $repair->satisfaction_score ? '-fill' : '' }}"></i>
                        @endfor
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 16px;">
                            {{ $repair->satisfaction_score }} เต็ม 5 ดาว
                        </div>
                        @if($repair->satisfaction_comment)
                        <div style="font-size: 13.5px; color: #475569; margin-top: 2px;">
                            "{{ $repair->satisfaction_comment }}"
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <form action="{{ route('repairs.rate', $repair) }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 12px; font-size: 13.5px; color: #475569;">
                        กรุณาให้คะแนนความพึงพอใจหลังการให้บริการของช่างเทคนิค:
                    </div>
                    <div style="display: flex; gap: 14px; margin-bottom: 14px;">
                        @for($i = 5; $i >= 1; $i--)
                        <label style="cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px; background: #f8fafc; padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border);">
                            <input type="radio" name="satisfaction_score" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }}>
                            <span style="font-weight: 600; color: #f59e0b;">{{ $i }} ★</span>
                        </label>
                        @endfor
                    </div>
                    <div class="form-group">
                        <input type="text" name="satisfaction_comment" class="form-control" placeholder="ข้อเสนอแนะเพิ่มเติม (ถ้ามี)">
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>บันทึกผลการประเมิน</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Technician Actions & Timeline -->
    <div>
        <!-- SLA & Target Due Date Card -->
        <div class="card" style="border-left: 4px solid {{ $repair->is_overdue ? '#ef4444' : '#0d9488' }};">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">
                    <i class="bi bi-stopwatch text-primary"></i>
                    <span>กรอบเวลาและมาตรฐาน SLA</span>
                </div>
                @if($repair->is_overdue)
                    <span class="badge badge-danger animate-pulse">⚠️ เกินกำหนด SLA</span>
                @elseif(in_array($repair->status, ['completed']))
                    <span class="badge badge-success">✓ สำเร็จตาม SLA</span>
                @else
                    <span class="badge badge-success">✓ ภายในกรอบ SLA</span>
                @endif
            </div>
            <div class="card-body" style="padding: 16px 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">เป้าหมาย SLA ตามความเร่งด่วน:</div>
                        <div style="font-weight: 700; font-size: 15px; color: #0f172a;">
                            {{ $repair->sla_hours }} ชั่วโมง
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">กำหนดแล้วเสร็จ:</div>
                        <div style="font-weight: 600; font-size: 13px; color: {{ $repair->is_overdue ? '#dc2626' : '#0284c7' }};">
                            {{ $repair->due_date ? $repair->due_date->format('d/m/Y H:i') . ' น.' : '-' }}
                        </div>
                    </div>
                </div>

                @if($repair->remaining_hours !== null && !in_array($repair->status, ['completed', 'cancelled']))
                    <div style="background: {{ $repair->remaining_hours < 0 ? '#fee2e2' : '#f0fdf4' }}; border-radius: 8px; padding: 10px 14px; font-size: 12.5px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="color: {{ $repair->remaining_hours < 0 ? '#991b1b' : '#166534' }};">
                            {{ $repair->remaining_hours < 0 ? 'เกินกำหนดเป้าหมายมาแล้ว' : 'เวลาคงเหลือโดยประมาณ' }}
                        </span>
                        <strong style="font-size: 14px; color: {{ $repair->remaining_hours < 0 ? '#dc2626' : '#16a34a' }};">
                            {{ abs($repair->remaining_hours) }} ชม.
                        </strong>
                    </div>
                @elseif($repair->completed_at)
                    <div style="background: #f8fafc; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #475569;">
                        ซ่อมเสร็จเมื่อ: <strong>{{ $repair->completed_at->format('d/m/Y H:i') }} น.</strong>
                        @if($repair->is_overdue)
                            <span style="color: #dc2626; font-weight: 600;">(ใช้เวลาเกินเกณฑ์ SLA)</span>
                        @else
                            <span style="color: #16a34a; font-weight: 600;">(ผ่านเกณฑ์มาตรฐาน SLA)</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Technician Action Box -->
        @if(auth()->user()->isAdmin() || auth()->user()->isTechnician())
        <div class="card" style="border-top: 4px solid var(--primary);">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-gear-fill text-primary"></i>
                    <span>จัดการสถานะ & มอบหมายงานช่าง</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('repairs.update-status', $repair) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label required" for="status">เปลี่ยนสถานะงาน</label>
                        <select name="status" id="status" class="form-select" onchange="toggleResolutionFields()">
                            <option value="pending" {{ $repair->status == 'pending' ? 'selected' : '' }}>🟡 รอรับเรื่อง (Pending)</option>
                            <option value="in_progress" {{ $repair->status == 'in_progress' ? 'selected' : '' }}>🔵 รับเรื่อง / กำลังดำเนินการ (In Progress)</option>
                            <option value="waiting_parts" {{ $repair->status == 'waiting_parts' ? 'selected' : '' }}>🟠 รออะไหล่ (Waiting for Parts)</option>
                            <option value="completed" {{ $repair->status == 'completed' ? 'selected' : '' }}>🟢 ซ่อมเสร็จสิ้น (Completed)</option>
                            <option value="external" {{ $repair->status == 'external' ? 'selected' : '' }}>🟣 ส่งซ่อมภายนอก (External)</option>
                            <option value="cancelled" {{ $repair->status == 'cancelled' ? 'selected' : '' }}>⚪ ยกเลิกรายการ (Cancelled)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="technician_id">ช่างผู้รับผิดชอบ</label>
                        <select name="technician_id" id="technician_id" class="form-select">
                            <option value="">-- ไม่ระบุช่าง --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ old('technician_id', $repair->technician_id) == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }} ({{ $tech->role_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="cause_solution_box" style="{{ in_array($repair->status, ['completed', 'waiting_parts', 'in_progress']) ? '' : 'display: none;' }}">
                        <div class="form-group">
                            <label class="form-label" for="cause">สาเหตุของปัญหา</label>
                            <input type="text" id="cause" name="cause" class="form-control" value="{{ old('cause', $repair->cause) }}" placeholder="เช่น พาวเวอร์ซัพพลายไหม้, หมึกหมด, แรมหลวม">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="solution">วิธีการแก้ไขปัญหา</label>
                            <textarea id="solution" name="solution" class="form-control" rows="2" placeholder="เช่น เปลี่ยนตลับหมึกและทำความสะอาดลูกยาง...">{{ old('solution', $repair->solution) }}</textarea>
                        </div>
                    </div>

                    <div id="external_box" style="{{ $repair->status == 'external' ? '' : 'display: none;' }}">
                        <div class="form-group">
                            <label class="form-label" for="external_repair_detail">รายละเอียดร้าน/ศูนย์บริการภายนอก</label>
                            <input type="text" id="external_repair_detail" name="external_repair_detail" class="form-control" value="{{ old('external_repair_detail', $repair->external_repair_detail) }}" placeholder="เช่น ส่งศูนย์ Dell เคลมเมนบอร์ด">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="comment">บันทึกข้อความเพิ่มเติม (Log Note)</label>
                        <input type="text" id="comment" name="comment" class="form-control" placeholder="ข้อความบันทึกในการเปลี่ยนสถานะ">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-check2-circle"></i>
                        <span>อัปเดตสถานะงานซ่อม</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Add Spare Part Modal / Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-cart-plus text-primary"></i>
                    <span>เบิกอะไหล่จากคลัง</span>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('repairs.add-part', $repair) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label required" for="spare_part_id">เลือกรายการพัสดุ / อะไหล่</label>
                        <select name="spare_part_id" id="spare_part_id" class="form-select" required>
                            <option value="">-- เลือกอะไหล่ในคลัง --</option>
                            @foreach($spareParts as $sp)
                                <option value="{{ $sp->id }}">
                                    [{{ $sp->part_code }}] {{ $sp->name }} (คงเหลือ {{ $sp->stock_quantity }} {{ $sp->unit }} - {{ number_format($sp->unit_price, 2) }} ฿)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="quantity">จำนวนที่เบิก</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" value="1" min="1" required>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width: 100%;">
                        <i class="bi bi-box-arrow-down"></i>
                        <span>เบิกอะไหล่และตัดสต็อก</span>
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Timeline Logs -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="bi bi-clock-history text-secondary"></i>
                    <span>ประวัติการดำเนินงาน (Timeline)</span>
                </div>
            </div>
            <div class="card-body" style="padding: 16px 20px;">
                <div style="position: relative; padding-left: 20px; border-left: 2px solid #e2e8f0; margin-left: 10px;">
                    @foreach($repair->logs as $log)
                    <div style="margin-bottom: 20px; position: relative;">
                        <!-- Dot -->
                        <div style="position: absolute; left: -26px; top: 2px; width: 12px; height: 12px; border-radius: 50%; background: #0d9488; border: 2px solid white; box-shadow: 0 0 0 2px #ccfbf1;"></div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: baseline;">
                            <div style="font-weight: 600; font-size: 13.5px; color: var(--text-main);">
                                {{ $log->user?->name ?? 'ระบบ' }}
                            </div>
                            <div style="font-size: 11.5px; color: var(--text-muted);">
                                {{ $log->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        <div style="font-size: 13px; color: #475569; margin-top: 2px;">
                            {{ $log->comment }}
                        </div>

                        @if($log->new_status)
                        <div style="margin-top: 4px;">
                            <span class="badge badge-secondary" style="font-size: 11px;">
                                สถานะ: {{ $log->new_status }}
                            </span>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleResolutionFields() {
        const st = document.getElementById('status').value;
        const causeBox = document.getElementById('cause_solution_box');
        const extBox = document.getElementById('external_box');

        if (st === 'completed' || st === 'waiting_parts' || st === 'in_progress') {
            causeBox.style.display = 'block';
        } else {
            causeBox.style.display = 'none';
        }

        if (st === 'external') {
            extBox.style.display = 'block';
        } else {
            extBox.style.display = 'none';
        }
    }
</script>
@endpush
