@extends('layouts.app')

@section('title', 'สร้างใบแจ้งซ่อมใหม่')
@section('page-title', 'แบบฟอร์มแจ้งซ่อมคอมพิวเตอร์ & ระบบสารสนเทศ')
@section('breadcrumb', 'ระบบแจ้งซ่อม / สร้างใบแจ้งซ่อมใหม่')

@section('topbar-actions')
<a href="{{ route('repairs.index') }}" class="topbar-btn">
    <i class="bi bi-arrow-left"></i>
    <span>กลับหน้ารายการ</span>
</a>
@endsection

@section('content')
<div class="content-container" style="max-width: 1140px; margin: 0 auto; padding-bottom: 40px;">

    <!-- Main Card Container -->
    <div class="card" style="border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04); overflow: hidden;">
        
        <!-- Premium Form Header -->
        <div style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); padding: 28px 32px; color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 26px; color: white; border: 1px solid rgba(255, 255, 255, 0.25);">
                    <i class="bi bi-tools"></i>
                </div>
                <div>
                    <h1 style="font-size: 20px; font-weight: 700; margin: 0; letter-spacing: -0.2px; color: white;">
                        แบบฟอร์มแจ้งซ่อมคอมพิวเตอร์ & ระบบสารสนเทศ (Repair Request Form)
                    </h1>
                    <div style="font-size: 13.5px; opacity: 0.9; margin-top: 4px; font-weight: 300;">
                        กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง &bull; IT Service Desk & Maintenance
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <span style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-clock-history"></i> บริการรวดเร็ว 24 ชม.
                </span>
                <span style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-shield-check"></i> บันทึกเข้าระบบช่างทันที
                </span>
            </div>
        </div>

        <div class="card-body" style="padding: 32px;">
            <form action="{{ route('repairs.store') }}" method="POST" enctype="multipart/form-data" id="repairForm">
                @csrf

                <!-- Strip: ข้อมูลผู้แจ้งซ่อมและหน่วยงาน -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 24px; margin-bottom: 28px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700;">
                            {{ mb_substr(auth()->user()?->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">ผู้ยื่นแจ้งซ่อม</div>
                            <div style="font-size: 15px; font-weight: 700; color: #1e293b;">{{ auth()->user()?->name ?? 'เจ้าหน้าที่' }}</div>
                            <div style="font-size: 12.5px; color: #64748b;">ตำแหน่ง: {{ auth()->user()?->position ?? 'เจ้าหน้าที่ประจำแผนก' }}</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 13px; font-weight: 600; color: #475569;">
                                <i class="bi bi-building text-primary"></i> แผนกที่แจ้ง:
                            </span>
                            @if(auth()->check() && auth()->user()->isUser() && auth()->user()->department_id)
                                <input type="hidden" name="department_id" id="department_id" value="{{ auth()->user()->department_id }}">
                                <span style="font-size: 13.5px; font-weight: 600; color: #0f766e; background: #f0fdfa; border: 1px solid #99f6e4; padding: 7px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-hospital"></i> {{ auth()->user()->department->name ?? 'แผนกของท่าน' }} ({{ auth()->user()->department->building ?? 'รพ.ทุ่งหัวช้าง' }})
                                </span>
                            @else
                                <select name="department_id" id="department_id" class="form-select" style="min-width: 220px; font-size: 13.5px; font-weight: 500; border-radius: 8px;" required>
                                    <option value="">-- กรุณาเลือกแผนก --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ old('department_id', auth()->user()?->department_id) == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }} ({{ $dept->building }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Presets Template Section -->
                <div style="margin-bottom: 28px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 13.5px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 8px;">
                            <span style="width: 24px; height: 24px; border-radius: 6px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </span>
                            <span>เทมเพลตอาการเสียยอดนิยม (คลิกเลือกเพื่อให้ระบบช่วยพิมพ์อัตโนมัติ):</span>
                        </div>
                        <span style="font-size: 12px; color: #94a3b8;">สามารถกดเลือกเพื่อเป็นแนวทางแล้วแก้ไขข้อความได้</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                        <button type="button" class="preset-card" onclick="applyPreset('pc_power')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;">
                                <i class="bi bi-display"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">คอมเปิดไม่ติด / ดับ</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;">ไฟไม่เข้า / พัดลมไม่หมุน</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyPreset('printer_jam')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;">
                                <i class="bi bi-printer"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">เครื่องพิมพ์มีปัญหา</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;">พิมพ์ไม่ออก / กระดาษติด</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyPreset('network_down')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;">
                                <i class="bi bi-wifi"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">เครือข่าย / อินเทอร์เน็ต</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;">หลุดบ่อย / ออกเน็ตไม่ได้</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyPreset('hosxp_error')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;">
                                <i class="bi bi-hospital"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #0f172a;">โปรแกรม HosXP</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;">Error / หลุด / เข้าไม่ได้</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyPreset('er_critical')" style="background: #ffffff; border: 1.5px solid #fee2e2; border-radius: 12px; padding: 12px 14px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 10px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;">
                                <i class="bi bi-lightning-fill"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13px; color: #dc2626;">ด่วนที่สุด! ผู้ป่วย/ER</div>
                                <div style="font-size: 11.5px; color: #b91c1c; margin-top: 1px;">กระทบจุดบริการผู้ป่วย</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Section: รายละเอียดปัญหาและอาการเสีย -->
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);">
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-card-text text-primary" style="font-size: 18px;"></i>
                        <span>รายละเอียดปัญหาและระดับความเร่งด่วน</span>
                    </div>

                    <!-- Problem Title -->
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label class="form-label required" for="title" style="font-weight: 600; font-size: 13.5px;">
                            หัวข้อปัญหา / อาการเสียเบื้องต้น
                        </label>
                        <input type="text" id="title" name="title" class="form-control" value="{{ old('title') }}" placeholder="เช่น คอมพิวเตอร์เปิดไม่ติด ไฟไม่เข้า, เครื่องพิมพ์กระดาษติด, ออกอินเทอร์เน็ตไม่ได้" required autofocus style="font-size: 14px; padding: 10px 14px; border-radius: 8px;">
                        @error('title')
                            <div class="text-danger" style="font-size: 12.5px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Urgency Cards -->
                    <div class="form-group" style="margin-bottom: 22px;">
                        <label class="form-label required" style="font-weight: 600; font-size: 13.5px; margin-bottom: 10px; display: block;">
                            ระดับความเร่งด่วนของงานซ่อม
                        </label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                            <!-- Low -->
                            <label class="urgency-card {{ old('urgency') == 'low' ? 'active' : '' }}" id="card_low" style="border: 2px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #ffffff; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="low" id="urgency_low" {{ old('urgency') == 'low' ? 'checked' : '' }} onchange="selectUrgency('low')" style="accent-color: #64748b; transform: scale(1.15);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 14px; color: #475569; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-check-circle" style="color: #64748b;"></i> ปกติ (ทั่วไป)
                                    </div>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">ไม่กระทบงานหลัก รอตามรอบคิว</div>
                                </div>
                            </label>

                            <!-- Normal (Default) -->
                            <label class="urgency-card {{ old('urgency', 'normal') == 'normal' ? 'active' : '' }}" id="card_normal" style="border: 2px solid #7dd3fc; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #f0f9ff; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="normal" id="urgency_normal" {{ old('urgency', 'normal') == 'normal' ? 'checked' : '' }} onchange="selectUrgency('normal')" style="accent-color: #0284c7; transform: scale(1.15);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 14px; color: #0284c7; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-info-circle-fill"></i> ปานกลาง
                                    </div>
                                    <div style="font-size: 11.5px; color: #0369a1; margin-top: 2px;">รอได้ 1-2 วัน ไม่กระทบงานด่วน</div>
                                </div>
                            </label>

                            <!-- High -->
                            <label class="urgency-card {{ old('urgency') == 'high' ? 'active' : '' }}" id="card_high" style="border: 2px solid #fde68a; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #ffffff; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="high" id="urgency_high" {{ old('urgency') == 'high' ? 'checked' : '' }} onchange="selectUrgency('high')" style="accent-color: #d97706; transform: scale(1.15);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 14px; color: #d97706; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-exclamation-triangle-fill"></i> ด่วน
                                    </div>
                                    <div style="font-size: 11.5px; color: #92400e; margin-top: 2px;">ส่งผลต่องานประจำวัน เข้าตรวจในวันนี้</div>
                                </div>
                            </label>

                            <!-- Critical -->
                            <label class="urgency-card {{ old('urgency') == 'critical' ? 'active' : '' }}" id="card_critical" style="border: 2px solid #fca5a5; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #ffffff; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="critical" id="urgency_critical" {{ old('urgency') == 'critical' ? 'checked' : '' }} onchange="selectUrgency('critical')" style="accent-color: #dc2626; transform: scale(1.15);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 14px; color: #dc2626; display: flex; align-items: center; gap: 6px;">
                                        <i class="bi bi-lightning-fill"></i> ด่วนที่สุด (Critical)
                                    </div>
                                    <div style="font-size: 11.5px; color: #b91c1c; margin-top: 2px;">กระทบการรักษา/ผู้ป่วย ช่างต้องเข้าทันที</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Problem Description -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label required" for="description" style="font-weight: 600; font-size: 13.5px;">
                            รายละเอียดอาการเสีย / ข้อความ Error ที่พบ
                        </label>
                        <textarea id="description" name="description" class="form-control" rows="4" placeholder="โปรดระบุรายละเอียด เช่น เปิดสวิตช์แล้วพัดลมไม่หมุน, มีกลิ่นไหม้, หน้าจอสีฟ้า Error Code ..., เกิดขึ้นช่วงเวลาใด, ได้ทดสอบแก้ไขเบื้องต้นอย่างไรบ้าง..." required style="font-size: 14px; padding: 12px 14px; border-radius: 8px; line-height: 1.6;">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger" style="font-size: 12.5px; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Section: ข้อมูลอุปกรณ์/ครุภัณฑ์ที่ต้องการซ่อม -->
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);">
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-laptop text-primary" style="font-size: 18px;"></i>
                        <span>ข้อมูลอุปกรณ์หรือครุภัณฑ์ที่ต้องการส่งซ่อม</span>
                    </div>

                    <!-- Device Mode Selector (Segmented Cards) -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-bottom: 18px;">
                        <label id="mode_asset_label" style="border: 2px solid var(--primary); border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #f0fdfa; transition: all 0.2s;">
                            <input type="radio" name="device_type_mode" value="asset" id="mode_asset" checked onchange="toggleDeviceMode()" style="accent-color: var(--primary); transform: scale(1.15);">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: var(--primary);">
                                    <i class="bi bi-pc-display"></i> ครุภัณฑ์ในระบบ (มีรหัสครุภัณฑ์ รพ.)
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                    เลือกจากรายการครุภัณฑ์เพื่อเชื่อมโยงประวัติการซ่อมบำรุง
                                </div>
                            </div>
                        </label>

                        <label id="mode_other_label" style="border: 2px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: #ffffff; transition: all 0.2s;">
                            <input type="radio" name="device_type_mode" value="other" id="mode_other" onchange="toggleDeviceMode()" style="accent-color: var(--primary); transform: scale(1.15);">
                            <div>
                                <div style="font-weight: 700; font-size: 14px; color: #475569;">
                                    <i class="bi bi-hdd-network"></i> อุปกรณ์ทั่วไป / ไม่มีรหัสครุภัณฑ์
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                    เช่น สาย LAN, รางปลั๊กไฟ, คีย์บอร์ด, เมาส์, จุดเชื่อมต่อระบบ
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Asset Select Box -->
                    <div id="asset_select_box" class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="asset_id" style="font-weight: 600; font-size: 13.5px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-pc-display text-primary"></i> ค้นหาและเลือกครุภัณฑ์ในระบบ:
                            </span>
                            @if(auth()->check() && auth()->user()->isUser())
                                <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 500; font-size: 12px; padding: 4px 10px; border-radius: 20px;">
                                    <i class="bi bi-building-check"></i> แสดงเฉพาะแผนก{{ auth()->user()->department->name ?? 'ของท่าน' }}
                                </span>
                            @else
                                <span class="badge" style="background: #f0fdfa; color: #0f766e; border: 1px solid #99f6e4; font-weight: 500; font-size: 12px; padding: 4px 10px; border-radius: 20px;">
                                    <i class="bi bi-globe2"></i> แอดมิน/ช่าง: แสดงครุภัณฑ์ทุกแผนก ({{ $assets->count() }} เครื่อง)
                                </span>
                            @endif
                        </label>
                        <select name="asset_id" id="asset_id" class="form-select" onchange="syncDepartmentFromAsset(this)" style="font-size: 14px; padding: 10px 14px; border-radius: 8px;">
                            <option value="">-- ไม่ระบุครุภัณฑ์ หรือให้ช่างตรวจสอบหน้างาน --</option>
                            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isTechnician()))
                                {{-- Admin & Technician: See all assets from all departments, neatly grouped --}}
                                @php
                                    $groupedAssets = $assets->groupBy(function($a) {
                                        return $a->department ? $a->department->name . ' (' . ($a->department->building ?: 'รพ.') . ')' : 'ส่วนกลาง / ไม่ระบุแผนก';
                                    });
                                @endphp
                                @foreach($groupedAssets as $deptLabel => $deptAssets)
                                    <optgroup label="🏢 {{ $deptLabel }} ({{ $deptAssets->count() }} เครื่อง)">
                                        @foreach($deptAssets as $asset)
                                            <option value="{{ $asset->id }}" 
                                                    data-department-id="{{ $asset->department_id }}" 
                                                    {{ old('asset_id', request('asset_id')) == $asset->id ? 'selected' : '' }}>
                                                [{{ $asset->asset_code }}] {{ $asset->name }} ({{ $asset->brand }} {{ $asset->model }}) &bull; S/N: {{ $asset->serial_number ?: '-' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @else
                                {{-- Regular User: Only their own department assets --}}
                                @forelse($assets as $asset)
                                    <option value="{{ $asset->id }}" 
                                            data-department-id="{{ $asset->department_id }}" 
                                            {{ old('asset_id', request('asset_id')) == $asset->id ? 'selected' : '' }}>
                                        [{{ $asset->asset_code }}] {{ $asset->name }} ({{ $asset->brand }} {{ $asset->model }}) &bull; S/N: {{ $asset->serial_number ?: '-' }}
                                    </option>
                                @empty
                                    <option value="" disabled>-- ไม่พบรายการครุภัณฑ์ในแผนกของท่าน (สามารถเลือก 'อุปกรณ์ทั่วไป' ด้านบน) --</option>
                                @endforelse
                            @endif
                        </select>
                        @if(auth()->check() && auth()->user()->isUser())
                            <span class="form-text" style="font-size: 12px; color: #0d9488; margin-top: 6px; display: flex; align-items: center; gap: 5px;">
                                <i class="bi bi-shield-check"></i> ระบบแสดงเฉพาะรายการครุภัณฑ์ประจำ {{ auth()->user()->department->name ?? 'แผนกของท่าน' }} ({{ $assets->count() }} เครื่อง) เพื่อความสะดวกรวดเร็วในการแจ้งซ่อม
                            </span>
                        @else
                            <span class="form-text" style="font-size: 12px; color: #64748b; margin-top: 6px; display: block;">
                                <i class="bi bi-info-circle"></i> สิทธิ์ผู้ดูแลระบบ/ช่าง สามารถค้นหาและเลือกครุภัณฑ์ได้จากทุกแผนกในโรงพยาบาล (จัดกลุ่มตามแผนก)
                            </span>
                        @endif
                    </div>

                    <!-- Other Device Input Box -->
                    <div id="other_device_box" class="form-group" style="margin-bottom: 0; display: none;">
                        <label class="form-label" for="other_device_info" style="font-weight: 600; font-size: 13.5px;">
                            ระบุชนิดอุปกรณ์ จุดติดตั้ง หรือยี่ห้อ:
                        </label>
                        <input type="text" id="other_device_info" name="other_device_info" class="form-control" value="{{ old('other_device_info') }}" placeholder="เช่น สายสัญญาณ LAN หัวหลุด จุดห้องตรวจ 2, ปลั๊กไฟตู้ Rack แผนกฉุกเฉิน, เครื่องพิมพ์สติกเกอร์ยาสำรอง" style="font-size: 14px; padding: 10px 14px; border-radius: 8px;">
                    </div>
                </div>

                <!-- Section: สถานที่และข้อมูลการติดต่อ -->
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);">
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-geo-alt text-primary" style="font-size: 18px;"></i>
                        <span>จุดที่ตั้งอุปกรณ์และข้อมูลการติดต่อผู้แจ้ง</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" for="location_detail" style="font-weight: 600; font-size: 13.5px;">
                                จุดที่ตั้ง / ห้อง / ชั้น / โต๊ะทำงาน
                            </label>
                            <input type="text" id="location_detail" name="location_detail" class="form-control" value="{{ old('location_detail') }}" placeholder="เช่น โต๊ะคัดกรอง 1, ห้องตรวจโรค 3, ช่องจ่ายยา 2, ห้องพักแพทย์" style="font-size: 14px; padding: 10px 14px; border-radius: 8px;">
                            <span class="form-text" style="font-size: 12px; color: #64748b; margin-top: 4px; display: block;">ช่วยให้ช่างเดินทางไปหน้างานได้รวดเร็วและตรงจุด</span>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" for="requester_name" style="font-weight: 600; font-size: 13.5px;">
                                ชื่อ-นามสกุล ผู้แจ้งซ่อม
                            </label>
                            <input type="text" id="requester_name" name="requester_name" class="form-control" value="{{ old('requester_name', auth()->user()?->name) }}" required style="font-size: 14px; padding: 10px 14px; border-radius: 8px;">
                            @error('requester_name')
                                <div class="text-danger" style="font-size: 12.5px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label required" for="requester_phone" style="font-weight: 600; font-size: 13.5px;">
                                เบอร์โทรศัพท์ติดต่อ / เบอร์ภายใน รพ.
                            </label>
                            <input type="text" id="requester_phone" name="requester_phone" class="form-control" value="{{ old('requester_phone', auth()->user()?->phone) }}" placeholder="เช่น ต่อ 111 หรือ 081-xxxxxxx" required style="font-size: 14px; padding: 10px 14px; border-radius: 8px;">
                            @error('requester_phone')
                                <div class="text-danger" style="font-size: 12.5px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Section: แนบรูปภาพประกอบ -->
                <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 28px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);">
                    <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-image text-primary" style="font-size: 18px;"></i>
                        <span>แนบรูปภาพความเสียหายหรือภาพหน้าจอ Error (ถ้ามี)</span>
                    </div>

                    <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; text-align: center; background: #f8fafc; transition: all 0.2s;" id="dropzone_box">
                        <input type="file" id="attachment_image" name="attachment_image" accept="image/*" onchange="previewImage(this)" style="display: none;">
                        
                        <div id="upload_prompt">
                            <div style="width: 52px; height: 52px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px;">
                                <i class="bi bi-cloud-arrow-up"></i>
                            </div>
                            <div style="font-weight: 600; font-size: 14px; color: #1e293b;">
                                คลิกเพื่อเลือกรูปภาพความเสียหาย หรือถ่ายภาพจากมือถือ
                            </div>
                            <div style="font-size: 12.5px; color: #64748b; margin-top: 4px;">
                                รองรับไฟล์ภาพ JPG, PNG, GIF หรือ WebP (ขนาดสูงสุดไม่เกิน 5 MB)
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('attachment_image').click()" style="margin-top: 14px; border-radius: 6px;">
                                <i class="bi bi-folder2-open"></i> เลือกไฟล์รูปภาพ
                            </button>
                        </div>

                        <!-- Image Preview Box -->
                        <div id="image_preview_box" style="display: none; align-items: center; justify-content: center; flex-direction: column; gap: 12px;">
                            <img id="preview_img" src="#" alt="Preview" style="max-height: 220px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <div style="display: flex; gap: 8px;">
                                <span id="file_name_display" style="font-size: 13px; font-weight: 500; color: #334155;"></span>
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()" style="padding: 2px 8px; font-size: 12px; border-radius: 4px;">
                                    <i class="bi bi-trash"></i> ลบรูป
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; padding-top: 20px; border-top: 1px solid var(--border); flex-wrap: wrap;">
                    <a href="{{ route('repairs.index') }}" class="btn btn-secondary" style="padding: 10px 22px; font-size: 14px; font-weight: 500; border-radius: 8px;">
                        <i class="bi bi-x-circle"></i> ยกเลิก
                    </a>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 12px 32px; font-size: 15px; font-weight: 600; border-radius: 10px; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 14px var(--primary-glow);">
                            <i class="bi bi-send-fill"></i>
                            <span>ส่งใบแจ้งซ่อมเข้าระบบ</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Presets Definition
    const presets = {
        pc_power: {
            title: 'เครื่องคอมพิวเตอร์เปิดไม่ติด / ไฟไม่เข้า / ดับเอง',
            desc: 'กดปุ่มเปิดเครื่อง (Power) แล้วพัดลมไม่หมุน ไฟหน้าเครื่องไม่ติด ลองขยับปลั๊กไฟและสายไฟ AC ด้านหลังเครื่องแล้วยังไม่ทำงาน เครื่องไม่ตอบสนองใดๆ',
            urgency: 'normal'
        },
        printer_jam: {
            title: 'เครื่องพิมพ์กระดาษติด / พิมพ์ไม่ออก / ไฟกระพริบเตือน',
            desc: 'สั่งพิมพ์งานจากระบบแล้วเครื่องพิมพ์เงียบ ไม่ดึงกระดาษ มีไฟสีส้มกระพริบเตือน หรือขึ้นข้อความ Paper Jam หน้าจอเครื่องพิมพ์ ลองเปิดฝาตรวจสอบแล้วไม่พบเศษกระดาษ',
            urgency: 'normal'
        },
        network_down: {
            title: 'ไม่สามารถเชื่อมต่อระบบเครือข่าย / สัญญาณอินเทอร์เน็ตหลุด',
            desc: 'สัญลักษณ์เครือข่ายขึ้นเครื่องหมายตกใจสีเหลือง หรือกากบาทสีแดง ไม่สามารถเปิดเว็บเบราว์เซอร์หรือเชื่อมต่อฐานข้อมูล HosXP ภายในโรงพยาบาลได้',
            urgency: 'high'
        },
        hosxp_error: {
            title: 'โปรแกรม HosXP เกิดข้อผิดพลาด Error / ไม่สามารถบันทึกข้อมูลได้',
            desc: 'เปิดใช้งานโปรแกรม HosXP แล้วขึ้นข้อความแจ้งเตือน Error หรือค้างขณะกำลังกดบันทึกข้อมูลตรวจรักษา ลองปิดโปรแกรมแล้วเปิดใหม่ยังพบอาการเดิม',
            urgency: 'high'
        },
        er_critical: {
            title: 'ด่วนที่สุด! คอมพิวเตอร์จุดบริการผู้ป่วย/ห้องฉุกเฉินขัดข้อง',
            desc: 'เครื่องคอมพิวเตอร์จุดบริการผู้ป่วยหยุดทำงาน ไม่สามารถสั่งยาหรือพิมพ์ใบตรวจรักษาผู้ป่วยได้ กระทบการให้บริการผู้ป่วยโดยตรง ต้องการให้ช่างเข้าตรวจสอบทันที',
            urgency: 'critical'
        }
    };

    function applyPreset(key) {
        if (!presets[key]) return;
        const p = presets[key];

        document.getElementById('title').value = p.title;
        document.getElementById('description').value = p.desc;
        selectUrgency(p.urgency);

        // Highlight effect on form inputs
        const titleEl = document.getElementById('title');
        titleEl.focus();
        titleEl.style.transition = 'all 0.3s';
        titleEl.style.boxShadow = '0 0 0 3px rgba(13, 148, 136, 0.25)';
        setTimeout(() => {
            titleEl.style.boxShadow = '';
        }, 1200);
    }

    function selectUrgency(level) {
        const levels = ['low', 'normal', 'high', 'critical'];
        levels.forEach(l => {
            const card = document.getElementById('card_' + l);
            const radio = document.getElementById('urgency_' + l);
            if (l === level) {
                radio.checked = true;
                if (card) {
                    card.style.border = getUrgencyBorder(l);
                    card.style.background = getUrgencyBg(l);
                }
            } else {
                if (card) {
                    card.style.border = '2px solid #e2e8f0';
                    card.style.background = '#ffffff';
                }
            }
        });
    }

    function getUrgencyBorder(level) {
        switch(level) {
            case 'low': return '2px solid #94a3b8';
            case 'normal': return '2px solid #0284c7';
            case 'high': return '2px solid #d97706';
            case 'critical': return '2px solid #dc2626';
            default: return '2px solid var(--primary)';
        }
    }

    function getUrgencyBg(level) {
        switch(level) {
            case 'low': return '#f8fafc';
            case 'normal': return '#f0f9ff';
            case 'high': return '#fffbeb';
            case 'critical': return '#fff1f2';
            default: return '#ffffff';
        }
    }

    function toggleDeviceMode() {
        const isAsset = document.getElementById('mode_asset').checked;
        document.getElementById('asset_select_box').style.display = isAsset ? 'block' : 'none';
        document.getElementById('other_device_box').style.display = isAsset ? 'none' : 'block';

        const assetLabel = document.getElementById('mode_asset_label');
        const otherLabel = document.getElementById('mode_other_label');
        if (isAsset) {
            assetLabel.style.border = '2px solid var(--primary)';
            assetLabel.style.background = '#f0fdfa';
            otherLabel.style.border = '2px solid #e2e8f0';
            otherLabel.style.background = '#ffffff';
        } else {
            assetLabel.style.border = '2px solid #e2e8f0';
            assetLabel.style.background = '#ffffff';
            otherLabel.style.border = '2px solid var(--primary)';
            otherLabel.style.background = '#f0fdfa';
        }
    }

    function previewImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('preview_img').src = e.target.result;
                document.getElementById('file_name_display').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                document.getElementById('upload_prompt').style.display = 'none';
                document.getElementById('image_preview_box').style.display = 'flex';
            }

            reader.readAsDataURL(file);
        }
    }

    function removeImage() {
        document.getElementById('attachment_image').value = '';
        document.getElementById('preview_img').src = '#';
        document.getElementById('upload_prompt').style.display = 'block';
        document.getElementById('image_preview_box').style.display = 'none';
    }

    function syncDepartmentFromAsset(select) {
        if (!select || !select.value) return;
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) return;
        const deptId = selectedOption.getAttribute('data-department-id');
        const deptSelect = document.getElementById('department_id');
        if (deptId && deptSelect && deptSelect.tagName === 'SELECT' && !deptSelect.value) {
            deptSelect.value = deptId;
        }
    }
</script>
@endpush
