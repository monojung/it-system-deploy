@extends('layouts.app')

@section('title', 'แก้ไขคำขอข้อมูลสารสนเทศ - ' . $dataRequest->request_no)
@section('page-title', 'แก้ไขคำขอข้อมูลสารสนเทศ')
@section('breadcrumb', 'ขอข้อมูลสารสนเทศ / แก้ไขคำขอ ' . $dataRequest->request_no)

@section('topbar-actions')
<a href="{{ route('data-requests.show', $dataRequest->id) }}" class="topbar-btn">
    <i class="bi bi-arrow-left"></i>
    <span>กลับหน้ารายละเอียด</span>
</a>
@endsection

@section('content')
<div class="content-container" style="max-width: 900px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
            <div class="card-title">
                <i class="bi bi-pencil-square text-primary"></i>
                <span>แก้ไขคำขอข้อมูลสารสนเทศ: {{ $dataRequest->request_no }}</span>
            </div>
            <span class="badge {{ $dataRequest->status_badge }}">
                {{ $dataRequest->status_label }}
            </span>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger" style="margin-bottom: 20px;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('data-requests.update', $dataRequest->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Section 1: ข้อมูลผู้ขอ -->
                <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 16px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #334155; margin-bottom: 8px;">
                        <i class="bi bi-person-circle text-primary"></i>
                        <span>ข้อมูลผู้ยื่นคำขอ</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13.5px;">
                        <div><strong>ผู้ขอ:</strong> {{ $dataRequest->user->name ?? $user->name }}</div>
                        <div><strong>ตำแหน่ง:</strong> {{ $dataRequest->user->position ?? 'เจ้าหน้าที่' }}</div>
                        <div>
                            <strong>แผนก/งาน:</strong>
                            <select name="department_id" class="form-select" style="display: inline-block; width: auto; font-size: 13px; padding: 4px 8px;">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $dataRequest->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: รายละเอียดข้อมูลที่ต้องการ -->
                <div class="form-group">
                    <label class="form-label required" for="title">หัวข้อข้อมูลสารสนเทศที่ต้องการ</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $dataRequest->title) }}" placeholder="เช่น สถิติผู้ป่วยนอกโรคเบาหวานและความดันโลหิตสูง ไตรมาสที่ 3" required>
                    <span class="form-text">ระบุชื่อรายงานหรือหัวข้อข้อมูลให้ชัดเจนและเข้าใจง่าย</span>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="objective_type">วัตถุประสงค์ในการนำข้อมูลไปใช้</label>
                        <select id="objective_type" name="objective_type" class="form-select" required>
                            <option value="ha_quality" {{ old('objective_type', $dataRequest->objective_type) === 'ha_quality' ? 'selected' : '' }}>งานพัฒนาคุณภาพโรงพยาบาล (HA/QA)</option>
                            <option value="research" {{ old('objective_type', $dataRequest->objective_type) === 'research' ? 'selected' : '' }}>งานวิจัย / นวัตกรรม / วิทยานิพนธ์</option>
                            <option value="executive" {{ old('objective_type', $dataRequest->objective_type) === 'executive' ? 'selected' : '' }}>รายงานผู้บริหาร / การประชุมวางแผน</option>
                            <option value="external" {{ old('objective_type', $dataRequest->objective_type) === 'external' ? 'selected' : '' }}>ตอบแบบสำรวจ สสจ. ลำพูน / สปสช. / สธ.</option>
                            <option value="other" {{ old('objective_type', $dataRequest->objective_type) === 'other' ? 'selected' : '' }}>วัตถุประสงค์อื่นๆ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="objective_detail">รายละเอียดวัตถุประสงค์เพิ่มเติม (ถ้ามี)</label>
                        <input type="text" id="objective_detail" name="objective_detail" class="form-control" value="{{ old('objective_detail', $dataRequest->objective_detail) }}" placeholder="เช่น ใช้ในการทบทวนเวชระเบียนคลินิก NCD">
                    </div>
                </div>

                <!-- Section 3: ช่วงเวลาข้อมูล -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="data_start_date">ช่วงเวลาข้อมูล: วันที่เริ่มต้น</label>
                        <input type="date" id="data_start_date" name="data_start_date" class="form-control" value="{{ old('data_start_date', $dataRequest->data_start_date ? $dataRequest->data_start_date->format('Y-m-d') : '') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="data_end_date">ช่วงเวลาข้อมูล: วันที่สิ้นสุด</label>
                        <input type="date" id="data_end_date" name="data_end_date" class="form-control" value="{{ old('data_end_date', $dataRequest->data_end_date ? $dataRequest->data_end_date->format('Y-m-d') : '') }}">
                    </div>
                </div>

                <!-- Section 4: เงื่อนไขและตัวแปรที่ต้องการ -->
                <div class="form-group">
                    <label class="form-label required" for="criteria_detail">เงื่อนไข ตัวแปร หรือฟิลด์ข้อมูลที่ต้องการ</label>
                    <textarea id="criteria_detail" name="criteria_detail" class="form-control" rows="5" placeholder="ระบุรายละเอียดเงื่อนไขและคอลัมน์ที่ต้องการ" required>{{ old('criteria_detail', $dataRequest->criteria_detail) }}</textarea>
                    <span class="form-text">ยิ่งระบุเงื่อนไขและคอลัมน์ชัดเจน เจ้าหน้าที่ไอทีจะสามารถสกัดข้อมูลจาก HosXP ได้รวดเร็วยิ่งขึ้น</span>
                </div>

                <!-- Section 5: รูปแบบไฟล์และความเร่งด่วน -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required" for="file_format">รูปแบบไฟล์ผลลัพธ์ที่ต้องการ</label>
                        <select id="file_format" name="file_format" class="form-select" required>
                            <option value="excel" {{ old('file_format', $dataRequest->file_format) === 'excel' ? 'selected' : '' }}>Microsoft Excel (.xlsx)</option>
                            <option value="csv" {{ old('file_format', $dataRequest->file_format) === 'csv' ? 'selected' : '' }}>CSV File (.csv)</option>
                            <option value="pdf" {{ old('file_format', $dataRequest->file_format) === 'pdf' ? 'selected' : '' }}>PDF Document (.pdf)</option>
                            <option value="text" {{ old('file_format', $dataRequest->file_format) === 'text' ? 'selected' : '' }}>Text File (.txt)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required" for="urgency">ระดับความเร่งด่วน</label>
                        <select id="urgency" name="urgency" class="form-select" required>
                            <option value="normal" {{ old('urgency', $dataRequest->urgency) === 'normal' ? 'selected' : '' }}>ปกติ (ระยะเวลา 3-5 วันทำการ)</option>
                            <option value="urgent" {{ old('urgency', $dataRequest->urgency) === 'urgent' ? 'selected' : '' }}>ด่วน (ระยะเวลา 1-2 วันทำการ)</option>
                            <option value="very_urgent" {{ old('urgency', $dataRequest->urgency) === 'very_urgent' ? 'selected' : '' }}>ด่วนที่สุด (ภายใน 24 ชม. - กรณีมีผลกระทบเร่งด่วน)</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <a href="{{ route('data-requests.show', $dataRequest->id) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i>
                        <span>ยกเลิกการแก้ไข</span>
                    </a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <i class="bi bi-check-circle"></i>
                        <span>บันทึกการแก้ไขคำขอ</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
