@extends('layouts.app')

@section('title', 'ยื่นคำขอข้อมูลสารสนเทศทางการแพทย์ & HosXP')
@section('page-title', 'ยื่นคำขอข้อมูลสารสนเทศใหม่')
@section('breadcrumb', 'ขอข้อมูลสารสนเทศ / สร้างคำขอใหม่')

@section('topbar-actions')
<a href="{{ route('data-requests.index') }}" class="topbar-btn">
    <i class="bi bi-arrow-left"></i>
    <span>กลับหน้ารายการ</span>
</a>
@endsection

@section('content')
<div class="content-container" style="max-width: 1140px; margin: 0 auto; padding-bottom: 40px;">

    @if(isset($cloneRequest) && $cloneRequest)
    <div class="alert alert-info" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #0284c7; background: #f0f9ff; padding: 16px 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.08);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="bi bi-copy"></i>
            </div>
            <div>
                <strong style="color: #0369a1; font-size: 15px;">คัดลอกข้อมูลจากคำขอเดิม: {{ $cloneRequest->request_no }}</strong>
                <div style="font-size: 13px; color: #475569; margin-top: 2px;">
                    ระบบได้เติมหัวข้อ วัตถุประสงค์ และเงื่อนไขเดิมไว้ให้แล้ว สามารถปรับเปลี่ยนช่วงเวลาและเงื่อนไขเพิ่มเติมได้ทันที
                </div>
            </div>
        </div>
        <a href="{{ route('data-requests.create') }}" class="btn btn-secondary btn-sm" style="font-size: 12px; padding: 6px 12px;">
            <i class="bi bi-x"></i> ล้างแบบฟอร์ม
        </a>
    </div>
    @endif

    <!-- Main Card Container -->
    <div class="card" style="border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04); overflow: hidden;">
        
        <!-- Premium Form Header -->
        <div style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); padding: 28px 32px; color: white; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255, 255, 255, 0.18); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; font-size: 26px; color: white; border: 1px solid rgba(255, 255, 255, 0.25);">
                    <i class="bi bi-file-earmark-medical-fill"></i>
                </div>
                <div>
                    <h1 style="font-size: 20px; font-weight: 700; margin: 0; letter-spacing: -0.2px; color: white;">
                        แบบฟอร์มขอข้อมูลสารสนเทศทางการแพทย์ & สถิติ HosXP
                    </h1>
                    <div style="font-size: 13.5px; opacity: 0.9; margin-top: 4px; font-weight: 300;">
                        กลุ่มงานสุขภาพดิจิทัล โรงพยาบาลทุ่งหัวช้าง &bull; ระบบบริการสกัดข้อมูลอิเล็กทรอนิกส์
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <span style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-shield-check"></i> มาตรฐาน PDPA
                </span>
                <span style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.3); font-size: 12px; padding: 6px 14px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-database-check"></i> HosXP Live Link
                </span>
            </div>
        </div>

        <div class="card-body" style="padding: 32px;">
            <form action="{{ route('data-requests.store') }}" method="POST" enctype="multipart/form-data" id="dataRequestForm">
                @csrf

                @if(isset($cloneRequest) && $cloneRequest)
                <input type="hidden" name="re_request_from_id" value="{{ $cloneRequest->id }}">
                @endif

                <!-- Strip: ข้อมูลผู้ยื่นคำขอ -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 24px; margin-bottom: 28px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-weight: 600;">ผู้ยื่นคำขอสารสนเทศ</div>
                            <div style="font-size: 15px; font-weight: 700; color: #1e293b;">{{ $user->name }}</div>
                            <div style="font-size: 12.5px; color: #64748b;">ตำแหน่ง: {{ $user->position ?? 'เจ้าหน้าที่' }}</div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 13px; font-weight: 600; color: #475569;">
                            <i class="bi bi-building text-primary"></i> สังกัดหน่วยงาน/แผนก:
                        </span>
                        <select name="department_id" class="form-select" style="min-width: 200px; font-size: 13.5px; font-weight: 500; border-radius: 8px;">
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ ($cloneRequest ? $cloneRequest->department_id : $user->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Quick Presets Template Section -->
                <div style="margin-bottom: 32px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div style="font-size: 13.5px; font-weight: 700; color: #334155; display: flex; align-items: center; gap: 8px;">
                            <span style="width: 24px; height: 24px; border-radius: 6px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                <i class="bi bi-lightning-charge-fill"></i>
                            </span>
                            <span>เทมเพลตคำขอยอดนิยม (คลิกเลือกเพื่อให้ระบบช่วยพิมพ์อัตโนมัติ):</span>
                        </div>
                        <span style="font-size: 12px; color: #94a3b8;">สามารถกดเลือกเพื่อเป็นแนวทางแล้วแก้ไขข้อความได้</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px;">
                        <button type="button" class="preset-card" onclick="applyTemplate('top10')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">10 อันดับโรค OPD</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">สถิติรหัสโรค ICD-10 ผู้ป่วยนอก</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyTemplate('ncd')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                <i class="bi bi-heart-pulse-fill"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">คลินิกเบาหวาน-ความดัน</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">ผู้ป่วย NCD นัดตรวจและผลแล็บ</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyTemplate('refer')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">ผู้ป่วย Refer / ฉุกเฉิน</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">สถิติการส่งต่อโรงพยาบาลแม่ข่าย</div>
                            </div>
                        </button>

                        <button type="button" class="preset-card" onclick="applyTemplate('pttype')" style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; text-align: left; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 10px; background: #f3e8ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                <i class="bi bi-credit-card-2-front-fill"></i>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">สถิติตามสิทธิการรักษา</div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">จำแนกบัตรทอง / ประกันสังคม / ขรก.</div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Section 1: ข้อมูลและวัตถุประสงค์ -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 24px; margin-bottom: 28px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 18px;">
                        <span style="color: #0284c7;"><i class="bi bi-1-circle-fill"></i></span>
                        <span>ข้อมูลสารสนเทศที่ต้องการ & วัตถุประสงค์</span>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label required" for="title" style="font-size: 14px; font-weight: 600;">หัวข้อข้อมูลสารสนเทศที่ต้องการ (Title)</label>
                        <input type="text" id="title" name="title" class="form-control" style="font-size: 15px; padding: 12px 16px; border-radius: 10px;" value="{{ old('title', $cloneRequest?->title ?? '') }}" placeholder="เช่น สถิติผู้ป่วยนอกโรคเบาหวานและความดันโลหิตสูง ประจำไตรมาสที่ 3 ปีงบประมาณ 2567" required>
                        <span class="form-text" style="font-size: 12px; color: #64748b; margin-top: 4px;">ระบุชื่อรายงานหรือหัวข้อข้อมูลให้ชัดเจนและเข้าใจง่าย</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <div class="form-group">
                            <label class="form-label required" for="objective_type" style="font-size: 14px; font-weight: 600;">วัตถุประสงค์ในการนำข้อมูลไปใช้</label>
                            @php $objVal = old('objective_type', $cloneRequest?->objective_type ?? 'ha_quality'); @endphp
                            <select id="objective_type" name="objective_type" class="form-select" style="font-size: 14px; padding: 11px 14px; border-radius: 10px;" required>
                                <option value="ha_quality" {{ $objVal === 'ha_quality' ? 'selected' : '' }}>งานพัฒนาคุณภาพโรงพยาบาล (HA/QA)</option>
                                <option value="research" {{ $objVal === 'research' ? 'selected' : '' }}>งานวิจัย / นวัตกรรม / วิทยานิพนธ์</option>
                                <option value="executive" {{ $objVal === 'executive' ? 'selected' : '' }}>รายงานผู้บริหาร / การประชุมวางแผน</option>
                                <option value="external" {{ $objVal === 'external' ? 'selected' : '' }}>ตอบแบบสำรวจ สสจ. ลำพูน / สปสช. / สธ.</option>
                                <option value="other" {{ $objVal === 'other' ? 'selected' : '' }}>วัตถุประสงค์อื่นๆ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="objective_detail" style="font-size: 14px; font-weight: 600;">รายละเอียดวัตถุประสงค์เพิ่มเติม (ถ้ามี)</label>
                            <input type="text" id="objective_detail" name="objective_detail" class="form-control" style="font-size: 14px; padding: 11px 14px; border-radius: 10px;" value="{{ old('objective_detail', $cloneRequest?->objective_detail ?? '') }}" placeholder="เช่น ใช้ประกอบการทบทวนเวชระเบียนคลินิก NCD">
                        </div>
                    </div>
                </div>

                <!-- Section 2: ช่วงเวลาและเงื่อนไขข้อมูล -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 24px; margin-bottom: 28px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 18px;">
                        <span style="color: #0d9488;"><i class="bi bi-2-circle-fill"></i></span>
                        <span>ขอบเขตเวลาและตัวแปรข้อมูล (Timeframe & Criteria)</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label class="form-label" for="data_start_date" style="font-size: 14px; font-weight: 600;">
                                <i class="bi bi-calendar-event text-primary"></i> ช่วงเวลาข้อมูล: วันที่เริ่มต้น
                            </label>
                            <input type="date" id="data_start_date" name="data_start_date" class="form-control" style="font-size: 14px; padding: 11px 14px; border-radius: 10px;" value="{{ old('data_start_date', $cloneRequest?->data_start_date?->format('Y-m-d') ?? date('Y-01-01')) }}">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="data_end_date" style="font-size: 14px; font-weight: 600;">
                                <i class="bi bi-calendar-check text-primary"></i> ช่วงเวลาข้อมูล: วันที่สิ้นสุด
                            </label>
                            <input type="date" id="data_end_date" name="data_end_date" class="form-control" style="font-size: 14px; padding: 11px 14px; border-radius: 10px;" value="{{ old('data_end_date', $cloneRequest?->data_end_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="criteria_detail" style="font-size: 14px; font-weight: 600;">
                            เงื่อนไข ตัวแปร หรือฟิลด์คอลัมน์ที่ต้องการ (Data Criteria & Columns)
                        </label>
                        <textarea id="criteria_detail" name="criteria_detail" class="form-control" rows="6" style="font-size: 14px; line-height: 1.6; padding: 14px 16px; border-radius: 10px; font-family: inherit;" placeholder="ระบุรายละเอียด เช่น:
- กลุ่มประชากร: ผู้ป่วยอายุ 35 ปีขึ้นไป ที่มารับบริการแผนก OPD
- รหัสการวินิจฉัยโรค (ICD-10): E10-E14, I10-I15
- คอลัมน์ที่ต้องการ: HN, วันที่รับบริการ, เพศ, อายุ, การวินิจฉัยหลัก, สิทธิการรักษา, ค่ายา, ค่ารักษาพยาบาลรวม" required>{{ old('criteria_detail', $cloneRequest?->criteria_detail ?? '') }}</textarea>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                            <span class="form-text" style="font-size: 12px; color: #64748b;">ยิ่งระบุเงื่อนไขและคอลัมน์ชัดเจน เจ้าหน้าที่ไอทีจะสามารถเขียน SQL สกัดข้อมูลจาก HosXP ได้รวดเร็วยิ่งขึ้น</span>
                            <span style="font-size: 11.5px; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px;">รองรับคำสั่ง SQL มาตรฐาน</span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: รูปแบบไฟล์ ความเร่งด่วน & เอกสารแนบ -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 24px; margin-bottom: 28px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 18px;">
                        <span style="color: #6366f1;"><i class="bi bi-3-circle-fill"></i></span>
                        <span>รูปแบบไฟล์ ความเร่งด่วน & ไฟล์ตัวอย่าง (Delivery & Options)</span>
                    </div>

                    <!-- รูปแบบไฟล์ (Card Radios) -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-label required" style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">รูปแบบไฟล์ผลลัพธ์ที่ต้องการ</label>
                        @php $fmtVal = old('file_format', $cloneRequest?->file_format ?? 'excel'); @endphp
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px;">
                            <label class="format-card {{ $fmtVal === 'excel' ? 'selected' : '' }}" style="border: 2px solid {{ $fmtVal === 'excel' ? '#16a34a' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $fmtVal === 'excel' ? '#f0fdf4' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="file_format" value="excel" {{ $fmtVal === 'excel' ? 'checked' : '' }} style="display: none;" onchange="updateFormatSelect(this)">
                                <div style="font-size: 26px; color: #16a34a;"><i class="bi bi-file-earmark-excel-fill"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">Excel (.xlsx)</div>
                                    <div style="font-size: 11px; color: #64748b;">ตารางคำนวณ</div>
                                </div>
                            </label>

                            <label class="format-card {{ $fmtVal === 'csv' ? 'selected' : '' }}" style="border: 2px solid {{ $fmtVal === 'csv' ? '#0284c7' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $fmtVal === 'csv' ? '#f0f9ff' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="file_format" value="csv" {{ $fmtVal === 'csv' ? 'checked' : '' }} style="display: none;" onchange="updateFormatSelect(this)">
                                <div style="font-size: 26px; color: #0284c7;"><i class="bi bi-file-earmark-spreadsheet-fill"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">CSV File (.csv)</div>
                                    <div style="font-size: 11px; color: #64748b;">สกัดรวดเร็ว UTF-8</div>
                                </div>
                            </label>

                            <label class="format-card {{ $fmtVal === 'pdf' ? 'selected' : '' }}" style="border: 2px solid {{ $fmtVal === 'pdf' ? '#dc2626' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $fmtVal === 'pdf' ? '#fef2f2' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="file_format" value="pdf" {{ $fmtVal === 'pdf' ? 'checked' : '' }} style="display: none;" onchange="updateFormatSelect(this)">
                                <div style="font-size: 26px; color: #dc2626;"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">PDF Document</div>
                                    <div style="font-size: 11px; color: #64748b;">สำหรับพิมพ์เอกสาร</div>
                                </div>
                            </label>

                            <label class="format-card {{ $fmtVal === 'text' ? 'selected' : '' }}" style="border: 2px solid {{ $fmtVal === 'text' ? '#64748b' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 16px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $fmtVal === 'text' ? '#f8fafc' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="file_format" value="text" {{ $fmtVal === 'text' ? 'checked' : '' }} style="display: none;" onchange="updateFormatSelect(this)">
                                <div style="font-size: 26px; color: #64748b;"><i class="bi bi-file-earmark-text-fill"></i></div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13.5px; color: #0f172a;">Text (.txt)</div>
                                    <div style="font-size: 11px; color: #64748b;">ไฟล์ข้อความดิบ</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- ระดับความเร่งด่วน (Urgency Radios) -->
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label class="form-label required" style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">ระดับความเร่งด่วน (Priority & SLA)</label>
                        @php $urgVal = old('urgency', $cloneRequest?->urgency ?? 'normal'); @endphp

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px;">
                            <label class="urgency-card {{ $urgVal === 'normal' ? 'selected' : '' }}" style="border: 2px solid {{ $urgVal === 'normal' ? '#0284c7' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $urgVal === 'normal' ? '#f0f9ff' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="normal" {{ $urgVal === 'normal' ? 'checked' : '' }} style="display: none;" onchange="updateUrgencySelect(this)">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                    <i class="bi bi-calendar3"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: #0f172a;">ปกติ (ระยะเวลา 3-5 วัน)</div>
                                    <div style="font-size: 11.5px; color: #64748b;">งานสถิติ รายงานประจำ หรือวิจัย</div>
                                </div>
                            </label>

                            <label class="urgency-card {{ $urgVal === 'urgent' ? 'selected' : '' }}" style="border: 2px solid {{ $urgVal === 'urgent' ? '#f59e0b' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $urgVal === 'urgent' ? '#fffbeb' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="urgent" {{ $urgVal === 'urgent' ? 'checked' : '' }} style="display: none;" onchange="updateUrgencySelect(this)">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                    <i class="bi bi-clock-history"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: #0f172a;">ด่วน (ระยะเวลา 1-2 วัน)</div>
                                    <div style="font-size: 11.5px; color: #64748b;">งานประกอบการประชุม / รายงานเร่งด่วน</div>
                                </div>
                            </label>

                            <label class="urgency-card {{ $urgVal === 'very_urgent' ? 'selected' : '' }}" style="border: 2px solid {{ $urgVal === 'very_urgent' ? '#ef4444' : '#e2e8f0' }}; border-radius: 12px; padding: 14px 18px; cursor: pointer; display: flex; align-items: center; gap: 12px; background: {{ $urgVal === 'very_urgent' ? '#fef2f2' : '#ffffff' }}; transition: all 0.2s;">
                                <input type="radio" name="urgency" value="very_urgent" {{ $urgVal === 'very_urgent' ? 'checked' : '' }} style="display: none;" onchange="updateUrgencySelect(this)">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: #dc2626;">ด่วนที่สุด (ภายใน 24 ชม.)</div>
                                    <div style="font-size: 11.5px; color: #64748b;">กรณีมีผลกระทบเร่งด่วนต่อการบริการ</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- แนบไฟล์ตัวอย่างแบบฟอร์มรายงาน -->
                    <div style="background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px 24px; transition: border-color 0.2s;">
                        <div style="display: flex; align-items: flex-start; gap: 16px;">
                            <div style="width: 44px; height: 44px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                                <i class="bi bi-paperclip"></i>
                            </div>
                            <div style="flex: 1;">
                                <label class="form-label" for="sample_file" style="font-size: 14px; font-weight: 600; margin-bottom: 2px;">
                                    แนบไฟล์ตัวอย่างแบบฟอร์ม / ตารางที่ต้องการ (Optional Attachment)
                                </label>
                                <div style="font-size: 12.5px; color: #64748b; margin-bottom: 12px;">
                                    เช่น ไฟล์ Excel หัวตาราง, รูปภาพถ่ายแบบฟอร์ม, หรือเอกสารขออนุมัติจาก สสจ./สปสช. (ขนาดไม่เกิน 10MB)
                                </div>
                                <input type="file" id="sample_file" name="sample_file" class="form-control" style="font-size: 13.5px; border-radius: 8px; background: white;" accept=".xlsx,.xls,.pdf,.jpg,.jpeg,.png,.csv,.doc,.docx,.zip">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: ข้อตกลงความปลอดภัย & PDPA Consent -->
                <div style="margin: 32px 0 28px; padding: 20px 24px; border: 1.5px solid #86efac; background: #f0fdf4; border-radius: 14px; box-shadow: 0 2px 10px rgba(22, 163, 74, 0.05);">
                    <label style="cursor: pointer; display: flex; align-items: flex-start; gap: 14px; margin: 0;">
                        <input type="checkbox" name="pdpa_consent" value="1" {{ old('pdpa_consent', '1') ? 'checked' : '' }} style="width: 22px; height: 22px; margin-top: 3px; accent-color: #16a34a; flex-shrink: 0;" required>
                        <div style="font-size: 13.5px; color: #166534; line-height: 1.6;">
                            <strong style="font-size: 14px;">ข้อตกลงการใช้ข้อมูลและความยินยอมตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562 (PDPA):</strong><br>
                            ข้าพเจ้ารับรองว่าข้อมูลสารสนเทศทางการแพทย์และสถิติ HosXP ที่ได้รับจะถูกนำไปใช้เพื่อประโยชน์ในการปฏิบัติงานทางการแพทย์และการสาธารณสุขตามวัตถุประสงค์ที่ระบุไว้เท่านั้น และจะรักษาความลับของข้อมูลผู้ป่วยโดยไม่เปิดเผยแก่บุคคลภายนอกโดยมิได้รับอนุญาตตามกฎหมาย
                        </div>
                    </label>
                </div>

                <!-- Actions Footer -->
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 24px; flex-wrap: wrap; gap: 14px;">
                    <a href="{{ route('data-requests.index') }}" class="btn btn-secondary" style="padding: 11px 24px; font-size: 14px; border-radius: 10px;">
                        <i class="bi bi-arrow-left"></i> ยกเลิก
                    </a>

                    <div style="display: flex; gap: 12px;">
                        <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 15px; font-weight: 600; border-radius: 10px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35); display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-send-fill"></i>
                            <span>ยื่นคำขอข้อมูลสารสนเทศ</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.preset-card:hover {
    transform: translateY(-2px);
    border-color: #0284c7 !important;
    box-shadow: 0 6px 16px rgba(2, 132, 199, 0.12);
}
.format-card:hover, .urgency-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
</style>

<script>
function applyTemplate(type) {
    const titleInput = document.getElementById('title');
    const objSelect = document.getElementById('objective_type');
    const objDetailInput = document.getElementById('objective_detail');
    const criteriaText = document.getElementById('criteria_detail');

    if (type === 'top10') {
        titleInput.value = 'รายงาน 10 อันดับโรคผู้ป่วยนอก (Top 10 OPD Morbidity)';
        objSelect.value = 'ha_quality';
        objDetailInput.value = 'เพื่อใช้ทบทวนแผนพัฒนาคุณภาพบริการและสถิติโรคประจำโรงพยาบาล';
        criteriaText.value = `- แผนก: ผู้ป่วยนอก (OPD) ทุกห้องตรวจ\n- ช่วงเวลา: ตามช่วงวันที่ระบุ\n- เงื่อนไข: นับจำนวนครั้งที่มารับบริการ (Visits) และจำนวนผู้ป่วยรายคน (Patients)\n- คอลัมน์ที่ต้องการ: รหัสการวินิจฉัยโรค (ICD-10), ชื่อโรคภาษาไทย, จำนวนครั้งที่มารับบริการ, จำนวนผู้ป่วยรายคน`;
        selectFormatRadio('excel');
    } else if (type === 'ncd') {
        titleInput.value = 'ข้อมูลผู้ป่วยคลินิกพิเศษโรคเบาหวานและความดันโลหิตสูง (NCD)';
        objSelect.value = 'ha_quality';
        objDetailInput.value = 'เพื่อติดตามการควบคุมระดับน้ำตาล ความดันโลหิต และภาวะแทรกซ้อน';
        criteriaText.value = `- กลุ่มโรค: เบาหวาน (E10-E14) และ ความดันโลหิตสูง (I10)\n- คลินิก: คลินิกโรคเรื้อรัง (NCD Clinic)\n- คอลัมน์ที่ต้องการ: HN, วันที่รับบริการ, ชื่อ-สกุล, อายุ, เลขบัตร ปชช., รหัสโรคหลัก, ค่าความดันโลหิต (BP), น้ำหนัก, สิทธิการรักษา, ยาที่ได้รับ`;
        selectFormatRadio('excel');
    } else if (type === 'refer') {
        titleInput.value = 'สถิติการส่งต่อผู้ป่วยนอกไปยังโรงพยาบาลแม่ข่าย (OPD Refer Out)';
        objSelect.value = 'executive';
        objDetailInput.value = 'เพื่อประกอบการวางแผนพัฒนาระบบบริการและวิเคราะห์สาเหตุการส่งต่อ';
        criteriaText.value = `- ประเภท: ผู้ป่วยส่งต่อภายนอกโรงพยาบาล (Refer Out)\n- คอลัมน์ที่ต้องการ: วันที่ส่งต่อ, HN, ชื่อ-สกุล, แผนกที่ส่ง, รหัสโรคหลัก (ICD-10), โรงพยาบาลปลายทาง, สาเหตุการส่งต่อ, พาหนะที่ใช้`;
        selectFormatRadio('excel');
    } else if (type === 'pttype') {
        titleInput.value = 'สรุปสถิติผู้ป่วยนอกจำแนกตามสิทธิการรักษาพยาบาล';
        objSelect.value = 'executive';
        objDetailInput.value = 'เพื่อใช้ในการวิเคราะห์ต้นทุนและสรุปรายรับค่ารักษาพยาบาล';
        criteriaText.value = `- แผนก: ผู้ป่วยนอก (OPD)\n- เงื่อนไข: สรุปยอดรวมจำนวนครั้งและจำนวนเงินตามกลุ่มสิทธิ (UC ในเขต/นอกเขต, ประกันสังคม, ข้าราชการ/จ่ายตรง, ชำระเงินเอง)\n- คอลัมน์ที่ต้องการ: รหัสสิทธิ, ชื่อสิทธิการรักษา, จำนวนครั้งรับบริการ, จำนวนผู้ป่วย, ค่ารักษาพยาบาลรวม`;
        selectFormatRadio('excel');
    }

    // Flash animation to indicate filled
    titleInput.style.backgroundColor = '#fef3c7';
    criteriaText.style.backgroundColor = '#fef3c7';
    setTimeout(() => {
        titleInput.style.backgroundColor = '';
        criteriaText.style.backgroundColor = '';
    }, 600);
}

function selectFormatRadio(val) {
    const radio = document.querySelector(`input[name="file_format"][value="${val}"]`);
    if (radio) {
        radio.checked = true;
        updateFormatSelect(radio);
    }
}

function updateFormatSelect(el) {
    document.querySelectorAll('.format-card').forEach(card => {
        card.style.borderColor = '#e2e8f0';
        card.style.backgroundColor = '#ffffff';
    });
    const parent = el.closest('.format-card');
    if (parent) {
        const val = el.value;
        const colors = {
            excel: { border: '#16a34a', bg: '#f0fdf4' },
            csv: { border: '#0284c7', bg: '#f0f9ff' },
            pdf: { border: '#dc2626', bg: '#fef2f2' },
            text: { border: '#64748b', bg: '#f8fafc' },
        };
        const c = colors[val] || { border: '#0284c7', bg: '#f0f9ff' };
        parent.style.borderColor = c.border;
        parent.style.backgroundColor = c.bg;
    }
}

function updateUrgencySelect(el) {
    document.querySelectorAll('.urgency-card').forEach(card => {
        card.style.borderColor = '#e2e8f0';
        card.style.backgroundColor = '#ffffff';
    });
    const parent = el.closest('.urgency-card');
    if (parent) {
        const val = el.value;
        const colors = {
            normal: { border: '#0284c7', bg: '#f0f9ff' },
            urgent: { border: '#f59e0b', bg: '#fffbeb' },
            very_urgent: { border: '#ef4444', bg: '#fef2f2' },
        };
        const c = colors[val] || { border: '#0284c7', bg: '#f0f9ff' };
        parent.style.borderColor = c.border;
        parent.style.backgroundColor = c.bg;
    }
}
</script>
@endsection
