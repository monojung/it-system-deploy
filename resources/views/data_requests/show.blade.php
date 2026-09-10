@extends('layouts.app')

@section('title', 'คำขอข้อมูล: ' . $dataRequest->request_no)
@section('page-title', 'รายละเอียดคำขอข้อมูลสารสนเทศ')
@section('breadcrumb', 'ขอข้อมูลสารสนเทศ / ' . $dataRequest->request_no)

@section('topbar-actions')
<a href="{{ route('data-requests.index') }}" class="topbar-btn">
    <i class="bi bi-arrow-left"></i>
    <span>กลับหน้ารายการ</span>
</a>

<a href="{{ route('data-requests.print', $dataRequest->id) }}" target="_blank" class="topbar-btn" style="background: #0f766e; color: #fff;">
    <i class="bi bi-printer"></i>
    <span>พิมพ์ใบคำขอ (Print)</span>
</a>

<a href="{{ route('data-requests.create', ['clone_id' => $dataRequest->id]) }}" class="topbar-btn" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0;">
    <i class="bi bi-copy"></i>
    <span>ขอซ้ำจากคำขอนี้ (Re-request)</span>
</a>

@if(($dataRequest->user_id === auth()->id() && $dataRequest->status === 'pending') || auth()->user()->isAdmin())
    <a href="{{ route('data-requests.edit', $dataRequest->id) }}" class="topbar-btn" style="background: #f1f5f9; color: #334155;">
        <i class="bi bi-pencil-square"></i>
        <span>แก้ไขคำขอ</span>
    </a>

    <form action="{{ route('data-requests.destroy', $dataRequest->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('คุณต้องการยกเลิกคำขอข้อมูลสารสนเทศนี้ใช่หรือไม่?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="topbar-btn" style="background: #fef2f2; color: #dc2626; border: 1px solid #fca5a5;">
            <i class="bi bi-trash"></i>
            <span>ยกเลิกคำขอ</span>
        </button>
    </form>
@endif
@endsection

@section('content')
<div class="content-container" style="max-width: 1060px; margin: 0 auto;">
    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="bi bi-check-circle-fill" style="font-size: 18px;"></i>
            <span>{{ session('success') }}</span>
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

    @if($dataRequest->reRequestFrom)
        <div class="alert alert-info" style="margin-bottom: 16px; font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
            <i class="bi bi-info-circle-fill text-primary"></i>
            <span>คำขอนี้คัดลอกมาจากคำขอเดิม: <a href="{{ route('data-requests.show', $dataRequest->reRequestFrom->id) }}" style="font-weight: 600; text-decoration: underline;">{{ $dataRequest->reRequestFrom->request_no }}</a> ({{ $dataRequest->reRequestFrom->title }})</span>
        </div>
    @endif

    <!-- Top Status Banner & Progress Tracker -->
    <div class="card" style="margin-bottom: 24px; border-left: 5px solid var(--primary);">
        <div class="card-body" style="padding: 20px 24px;">
            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px; flex-wrap: wrap;">
                        <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0;">{{ $dataRequest->request_no }}</h2>
                        <span class="badge {{ $dataRequest->status_badge }}" style="font-size: 13px; padding: 4px 12px;">{{ $dataRequest->status_label }}</span>
                        <span class="badge {{ $dataRequest->urgency_badge }}" style="font-size: 13px; padding: 4px 10px;">ความเร่งด่วน: {{ $dataRequest->urgency_label }}</span>
                        @if($dataRequest->status === 'completed' && $dataRequest->download_count > 0)
                            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 12px; padding: 4px 10px;">
                                <i class="bi bi-cloud-arrow-down"></i> ดาวน์โหลดแล้ว {{ $dataRequest->download_count }} ครั้ง
                            </span>
                        @endif
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted);">
                        ยื่นคำขอเมื่อ {{ $dataRequest->created_at->format('d/m/Y H:i') }} น. ({{ $dataRequest->created_at->diffForHumans() }})
                    </div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('data-requests.print', $dataRequest->id) }}" target="_blank" class="btn btn-secondary">
                        <i class="bi bi-printer"></i> พิมพ์ใบคำขอ A4
                    </a>

                    <a href="{{ route('data-requests.create', ['clone_id' => $dataRequest->id]) }}" class="btn btn-secondary" style="background: #f0fdf4; color: #166534; border-color: #bbf7d0;">
                        <i class="bi bi-copy"></i> ขอซ้ำ
                    </a>

                    @if($dataRequest->status === 'completed' && $dataRequest->result_file)
                        <a href="{{ route('data-requests.download', $dataRequest->id) }}" class="btn btn-primary" style="padding: 10px 20px; font-size: 14px;">
                            <i class="bi bi-cloud-arrow-down-fill" style="font-size: 18px;"></i>
                            <span>ดาวน์โหลดไฟล์ผลลัพธ์</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Visual Progress Tracker (Timeline) -->
            @php
                $statusOrder = [
                    'pending' => 1,
                    'approved' => 2,
                    'in_progress' => 3,
                    'completed' => 4,
                    'rejected' => -1,
                ];
                $currentStep = $statusOrder[$dataRequest->status] ?? 1;
            @endphp

            @if($dataRequest->status === 'rejected')
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; color: #991b1b;">
                    <i class="bi bi-x-circle-fill" style="font-size: 24px; color: #ef4444;"></i>
                    <div>
                        <strong>คำขอนี้ถูกปฏิเสธ:</strong> {{ $dataRequest->admin_notes ?: 'ไม่มีการระบุเหตุผล' }}
                    </div>
                </div>
            @else
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 14px; position: relative;">
                    <!-- Step 1 -->
                    <div style="text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; background: #16a34a; color: white;">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div style="font-size: 12.5px; font-weight: 600; color: #16a34a;">1. ยื่นคำขอแล้ว</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $dataRequest->created_at->format('d/m/Y') }}</div>
                    </div>

                    <!-- Step 2 -->
                    <div style="text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; background: {{ $currentStep >= 2 ? '#0284c7' : '#e2e8f0' }}; color: {{ $currentStep >= 2 ? 'white' : '#64748b' }};">
                            @if($currentStep >= 2) <i class="bi bi-check-lg"></i> @else 2 @endif
                        </div>
                        <div style="font-size: 12.5px; font-weight: 600; color: {{ $currentStep >= 2 ? '#0284c7' : '#64748b' }};">2. IT รับเรื่อง</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $currentStep >= 2 ? 'ผ่านการตรวจสอบ' : 'รอเจ้าหน้าที่รับเรื่อง' }}</div>
                    </div>

                    <!-- Step 3 -->
                    <div style="text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; background: {{ $currentStep >= 3 ? '#0d9488' : '#e2e8f0' }}; color: {{ $currentStep >= 3 ? 'white' : '#64748b' }};">
                            @if($currentStep >= 4) <i class="bi bi-check-lg"></i> @elseif($currentStep == 3) <i class="bi bi-gear spin"></i> @else 3 @endif
                        </div>
                        <div style="font-size: 12.5px; font-weight: 600; color: {{ $currentStep >= 3 ? '#0d9488' : '#64748b' }};">3. สกัดข้อมูล HosXP</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $currentStep >= 3 ? 'กำลังดึง/ตรวจสอบ' : 'รอการประมวลผล' }}</div>
                    </div>

                    <!-- Step 4 -->
                    <div style="text-align: center;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; margin: 0 auto 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; background: {{ $currentStep >= 4 ? '#16a34a' : '#e2e8f0' }}; color: {{ $currentStep >= 4 ? 'white' : '#64748b' }};">
                            @if($currentStep >= 4) <i class="bi bi-cloud-arrow-down-fill"></i> @else 4 @endif
                        </div>
                        <div style="font-size: 12.5px; font-weight: 600; color: {{ $currentStep >= 4 ? '#16a34a' : '#64748b' }};">4. พร้อมส่งมอบ</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $currentStep >= 4 ? 'ดาวน์โหลดได้ทันที' : 'รอส่งมอบไฟล์' }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        <!-- Left Column: รายละเอียดคำขอ & ไฟล์แนบ -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-info-circle text-primary"></i>
                        <span>รายละเอียดข้อมูลที่ขอ</span>
                    </div>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12.5px; color: var(--text-muted);">หัวข้อข้อมูล</div>
                        <div style="font-size: 16px; font-weight: 600; color: var(--text-main); margin-top: 2px;">{{ $dataRequest->title }}</div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div>
                            <div style="font-size: 12.5px; color: var(--text-muted);">วัตถุประสงค์</div>
                            <div style="font-size: 14px; font-weight: 500; color: #334155; margin-top: 2px;">{{ $dataRequest->objective_label }}</div>
                            @if($dataRequest->objective_detail)
                                <div style="font-size: 12px; color: var(--text-muted);">({{ $dataRequest->objective_detail }})</div>
                            @endif
                        </div>

                        <div>
                            <div style="font-size: 12.5px; color: var(--text-muted);">รูปแบบไฟล์ที่ต้องการ</div>
                            <div style="font-size: 14px; font-weight: 600; color: #0284c7; text-transform: uppercase; margin-top: 2px;">
                                <i class="bi bi-file-earmark-spreadsheet"></i> {{ $dataRequest->file_format }}
                            </div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12.5px; color: var(--text-muted);">ช่วงเวลาข้อมูล (Data Date Range)</div>
                        <div style="font-size: 14px; font-weight: 600; color: #0d9488; margin-top: 2px;">
                            @if($dataRequest->data_start_date && $dataRequest->data_end_date)
                                {{ $dataRequest->data_start_date->format('d/m/Y') }} ถึง {{ $dataRequest->data_end_date->format('d/m/Y') }}
                            @elseif($dataRequest->data_start_date)
                                ตั้งแต่วันที่ {{ $dataRequest->data_start_date->format('d/m/Y') }} เป็นต้นไป
                            @else
                                ไม่ได้ระบุช่วงเวลา
                            @endif
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 4px;">เงื่อนไข ตัวแปร หรือฟิลด์ที่ต้องการ</div>
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 12px 14px; font-size: 13.5px; line-height: 1.6; white-space: pre-line; color: #1e293b;">{{ $dataRequest->criteria_detail }}</div>
                    </div>

                    <!-- Sample File (ถ้าผู้ขอแนบมา) -->
                    @if($dataRequest->sample_file)
                        <div style="margin-bottom: 16px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px 14px;">
                            <div style="font-size: 12.5px; font-weight: 600; color: #0369a1; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                <i class="bi bi-paperclip"></i>
                                <span>ไฟล์ตัวอย่างแบบฟอร์มที่ผู้ขอแนบมา:</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                <span style="font-size: 13px; color: #334155;">{{ $dataRequest->sample_filename ?: basename($dataRequest->sample_file) }}</span>
                                <a href="{{ route('data-requests.download-sample', $dataRequest->id) }}" class="btn btn-sm btn-primary" style="font-size: 12px; padding: 4px 12px;">
                                    <i class="bi bi-download"></i> ดาวน์โหลดดูตัวอย่าง
                                </a>
                            </div>
                        </div>
                    @endif

                    <div style="border-top: 1px solid var(--border); padding-top: 12px; font-size: 12px; color: #166534; display: flex; align-items: center; gap: 6px;">
                        <i class="bi bi-shield-check" style="font-size: 16px;"></i>
                        <span>ยินยอมตามเงื่อนไขการรักษาความลับข้อมูลผู้ป่วยตาม พ.ร.บ. คุ้มครองข้อมูลส่วนบุคคล (PDPA) เรียบร้อยแล้ว</span>
                    </div>
                </div>
            </div>

            <!-- Requester Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-person text-primary"></i>
                        <span>ข้อมูลผู้ยื่นคำขอ</span>
                    </div>
                </div>
                <div class="card-body">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                            {{ mb_substr($dataRequest->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 14.5px; color: var(--text-main);">{{ $dataRequest->user->name }}</div>
                            <div style="font-size: 12.5px; color: var(--text-muted);">
                                {{ $dataRequest->user->position ?? 'เจ้าหน้าที่' }} &bull; {{ $dataRequest->department->name ?? 'ไม่ระบุแผนก' }}
                            </div>
                        </div>
                    </div>
                    <div style="font-size: 13px; color: #475569; display: flex; flex-direction: column; gap: 6px;">
                        <div><i class="bi bi-telephone text-muted"></i> <strong>เบอร์โทร:</strong> {{ $dataRequest->user->phone ?? 'ไม่ระบุ' }}</div>
                        <div><i class="bi bi-envelope text-muted"></i> <strong>อีเมล:</strong> {{ $dataRequest->user->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: สถานะ, ผลลัพธ์ & เครื่องมือ IT -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Result File Card -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="bi bi-file-earmark-check text-success"></i>
                        <span>สถานะและไฟล์ผลลัพธ์ข้อมูล</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($dataRequest->status === 'completed')
                        <div style="background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 10px; padding: 18px; margin-bottom: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; color: #166534; font-weight: 700; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-check-circle-fill" style="font-size: 22px;"></i>
                                <span>สกัดข้อมูลและปิดงานเรียบร้อยแล้ว</span>
                            </div>
                            <div style="font-size: 13px; color: #14532d; margin-bottom: 14px;">
                                ดำเนินการโดย: <strong>{{ $dataRequest->handler->name ?? 'เจ้าหน้าที่ไอที' }}</strong>
                                @if($dataRequest->completed_at)
                                    เมื่อ {{ $dataRequest->completed_at->format('d/m/Y H:i') }} น.
                                @endif
                            </div>

                            @if($dataRequest->result_file)
                                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                                    <a href="{{ route('data-requests.download', $dataRequest->id) }}" class="btn btn-primary" style="padding: 10px 20px; font-size: 14px;">
                                        <i class="bi bi-cloud-arrow-down-fill"></i> ดาวน์โหลดไฟล์ข้อมูล ({{ strtoupper($dataRequest->file_format) }})
                                    </a>
                                    @if($dataRequest->download_count > 0)
                                        <span style="font-size: 12px; color: #166534;">
                                            <i class="bi bi-eye"></i> ดาวน์โหลดแล้ว {{ $dataRequest->download_count }} ครั้ง
                                        </span>
                                    @endif
                                </div>
                            @else
                                <div style="font-size: 13px; color: var(--text-muted);">ส่งมอบข้อมูลผ่านช่องทางอื่นเรียบร้อยแล้ว</div>
                            @endif
                        </div>
                    @elseif($dataRequest->status === 'rejected')
                        <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 10px; padding: 18px; margin-bottom: 18px;">
                            <div style="display: flex; align-items: center; gap: 10px; color: #991b1b; font-weight: 700; font-size: 15px; margin-bottom: 6px;">
                                <i class="bi bi-x-circle-fill" style="font-size: 22px;"></i>
                                <span>คำขอถูกปฏิเสธ</span>
                            </div>
                            <div style="font-size: 13px; color: #7f1d1d;">
                                <strong>เหตุผล:</strong> {{ $dataRequest->admin_notes ?: 'ไม่มีการระบุเหตุผล' }}
                            </div>
                        </div>
                    @else
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 16px; margin-bottom: 18px; font-size: 13px; color: #1e40af;">
                            <i class="bi bi-hourglass-split"></i>
                            กำลังอยู่ในกระบวนการตรวจสอบและประมวลผลข้อมูลจากแม่ข่าย HosXP
                        </div>
                    @endif

                    <!-- แสดง SQL Query ที่ใช้ (ถ้ามี) -->
                    @if($dataRequest->sql_query)
                        <div style="margin-top: 16px;">
                            <div style="font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-code-slash text-primary"></i>
                                    <span>คำสั่ง SQL Query ที่ใช้ดึงข้อมูล HosXP</span>
                                </span>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="navigator.clipboard.writeText(document.getElementById('displaySql').innerText); alert('คัดลอกคำสั่ง SQL แล้ว');" style="font-size: 11px; padding: 2px 8px;">
                                    <i class="bi bi-clipboard"></i> คัดลอก
                                </button>
                            </div>
                            <pre id="displaySql" style="background: #1e293b; color: #38bdf8; padding: 14px; border-radius: 8px; font-size: 12px; font-family: monospace; overflow-x: auto; max-height: 250px;">{{ $dataRequest->sql_query }}</pre>
                        </div>
                    @endif

                    @if($dataRequest->admin_notes && $dataRequest->status !== 'rejected')
                        <div style="margin-top: 14px;">
                            <div style="font-size: 12px; color: var(--text-muted);">หมายเหตุจากเจ้าหน้าที่:</div>
                            <div style="font-size: 13px; color: #334155; margin-top: 2px;">{{ $dataRequest->admin_notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- IT Actions Panel (Only for Technician and Admin) -->
            @if(!auth()->user()->isUser())
                <div class="card" style="border: 1.5px solid #0d9488;">
                    <div class="card-header" style="background: #f0fdfa;">
                        <div class="card-title" style="color: #0f766e;">
                            <i class="bi bi-sliders text-teal"></i>
                            <span>การจัดการสำหรับเจ้าหน้าที่สุขภาพดิจิทัล (IT Action)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Action 1: Change Status -->
                        @if($dataRequest->status !== 'completed')
                            <form action="{{ route('data-requests.status', $dataRequest->id) }}" method="POST" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px dashed var(--border);">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label required">ปรับสถานะคำขอ</label>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        <button type="submit" name="status" value="approved" class="btn btn-secondary" style="font-size: 13px; padding: 6px 14px; {{ $dataRequest->status === 'approved' ? 'background: #0284c7; color: white;' : '' }}">
                                            <i class="bi bi-check2"></i> อนุมัติคำขอ
                                        </button>
                                        <button type="submit" name="status" value="in_progress" class="btn btn-secondary" style="font-size: 13px; padding: 6px 14px; {{ $dataRequest->status === 'in_progress' ? 'background: #0d9488; color: white;' : '' }}">
                                            <i class="bi bi-gear"></i> รับงาน / กำลังสกัดข้อมูล
                                        </button>
                                        <button type="button" class="btn btn-secondary" style="font-size: 13px; padding: 6px 14px; color: #dc2626;" onclick="document.getElementById('rejectBox').style.display = 'block'">
                                            <i class="bi bi-x-circle"></i> ปฏิเสธคำขอ
                                        </button>
                                    </div>
                                </div>

                                <div id="rejectBox" style="display: none; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px; margin-top: 10px;">
                                    <label class="form-label required" style="font-size: 12px; color: #991b1b;">เหตุผลในการปฏิเสธคำขอ</label>
                                    <input type="text" name="admin_notes" class="form-control" placeholder="ระบุเหตุผล เช่น ข้อมูลขัดต่อระเบียบ PDPA หรือไม่มีข้อมูลในระบบ">
                                    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px;">
                                        <button type="button" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;" onclick="document.getElementById('rejectBox').style.display = 'none'">ยกเลิก</button>
                                        <button type="submit" name="status" value="rejected" class="btn btn-danger" style="padding: 4px 12px; font-size: 12px;">ยืนยันปฏิเสธ</button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        <!-- Action 2: HosXP Live Query with Presets -->
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                <div style="font-weight: 700; font-size: 13.5px; color: #0f766e; display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-terminal-fill"></i>
                                    <span>เครื่องมือสกัดข้อมูล HosXP อัตโนมัติ (Live Query)</span>
                                </div>
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">MySQL HosXP PDO</span>
                            </div>

                            <!-- Preset Query Selector -->
                            @if(isset($sqlPresets) && count($sqlPresets) > 0)
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label class="form-label" style="font-size: 12px; color: #475569;">
                                    <i class="bi bi-lightning-charge-fill text-warning"></i> เลือกคำสั่ง SQL สำเร็จรูป (Query Preset):
                                </label>
                                <select id="sqlPresetSelect" class="form-select form-select-sm" onchange="insertSqlPreset(this.value)">
                                    <option value="">-- เลือกเทมเพลตคำสั่ง SQL (ระบบจะแทนค่าช่วงวันที่ให้อัตโนมัติ) --</option>
                                    @foreach($sqlPresets as $idx => $preset)
                                        <option value="{{ $idx }}">{{ $preset['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="form-group" style="margin-bottom: 12px;">
                                <textarea id="it_sql_query" class="form-control" rows="6" style="font-family: monospace; font-size: 12px; background: #1e293b; color: #38bdf8;" placeholder="พิมพ์คำสั่ง SQL เช่น SELECT hn, fname, lname FROM ...">{{ $dataRequest->sql_query ?: "SELECT e.hn, p.pname, p.fname, p.lname, e.vstdate\nFROM er_regist e\nJOIN patient p ON e.hn = p.hn\nWHERE e.vstdate >= CURDATE() - INTERVAL 7 DAY\nLIMIT 20;" }}</textarea>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                                <button type="button" id="btnTestQuery" class="btn btn-secondary" style="font-size: 12.5px; padding: 6px 14px;">
                                    <i class="bi bi-play-circle-fill" style="color: #0284c7;"></i> ทดสอบรัน Query (Live Preview)
                                </button>

                                <form action="{{ route('data-requests.generate-file', $dataRequest->id) }}" method="POST" id="formGenerateCsv" onsubmit="return confirm('ระบบจะรัน SQL นี้และสร้างไฟล์ CSV แนบส่งมอบให้ผู้ขอทันที ยืนยันการดำเนินการหรือไม่?');">
                                    @csrf
                                    <input type="hidden" name="sql_query" id="csv_sql_query">
                                    <button type="submit" class="btn btn-primary" style="font-size: 12.5px; padding: 6px 14px; background: #059669;" onclick="document.getElementById('csv_sql_query').value = document.getElementById('it_sql_query').value;">
                                        <i class="bi bi-file-earmark-arrow-down-fill"></i> สร้างไฟล์ CSV อัตโนมัติ & ปิดงาน
                                    </button>
                                </form>
                            </div>

                            <!-- Query Result Preview Container -->
                            <div id="queryPreviewContainer" style="display: none; margin-top: 14px;">
                                <div id="queryPreviewStatus" style="font-size: 12px; font-weight: 600; margin-bottom: 6px;"></div>
                                <div style="max-height: 240px; overflow: auto; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                                    <table class="table" id="queryPreviewTable" style="margin: 0; font-size: 11.5px;">
                                        <thead id="queryPreviewHead" style="position: sticky; top: 0; background: #f1f5f9;"></thead>
                                        <tbody id="queryPreviewBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Action 3: Complete and upload file manually -->
                        <form action="{{ route('data-requests.complete', $dataRequest->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div style="font-weight: 600; font-size: 13.5px; color: #334155; margin-bottom: 12px;">
                                <i class="bi bi-upload"></i> หรืออัปโหลดไฟล์ผลลัพธ์ด้วยตนเอง
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="result_file">แนบไฟล์ผลลัพธ์ข้อมูล (Excel, CSV, PDF, Zip สูงสุด 20MB)</label>
                                <input type="file" id="result_file" name="result_file" class="form-control" accept=".xlsx,.xls,.csv,.pdf,.zip,.txt">
                                @if($dataRequest->result_file)
                                    <span class="form-text" style="color: #0d9488;">ปัจจุบันมีไฟล์: {{ basename($dataRequest->result_file) }} (อัปโหลดใหม่เพื่อแทนที่)</span>
                                @endif
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="admin_notes">ข้อความหรือคำแนะนำเพิ่มเติมถึงผู้ขอ</label>
                                <input type="text" id="admin_notes" name="admin_notes" class="form-control" value="{{ old('admin_notes', $dataRequest->admin_notes) }}" placeholder="เช่น ดึงข้อมูลตามเกณฑ์เรียบร้อยแล้ว">
                            </div>

                            <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                                <button type="submit" class="btn btn-secondary" style="padding: 9px 20px;">
                                    <i class="bi bi-send-check"></i> บันทึกผลลัพธ์และปิดงาน (Manual Complete)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
@if(isset($sqlPresets))
const sqlPresetsList = @json($sqlPresets);
const reqStartDate = "{{ $dataRequest->data_start_date ? $dataRequest->data_start_date->format('Y-m-d') : date('Y-01-01') }}";
const reqEndDate = "{{ $dataRequest->data_end_date ? $dataRequest->data_end_date->format('Y-m-d') : date('Y-m-d') }}";

function insertSqlPreset(index) {
    if (index === "" || !sqlPresetsList[index]) return;
    let rawSql = sqlPresetsList[index].sql;
    // Replace placeholders with request dates
    let formattedSql = rawSql.replaceAll(':start_date', reqStartDate).replaceAll(':end_date', reqEndDate);
    document.getElementById('it_sql_query').value = formattedSql;
}
@endif

document.addEventListener('DOMContentLoaded', function() {
    const btnTest = document.getElementById('btnTestQuery');
    if (!btnTest) return;

    btnTest.addEventListener('click', function() {
        const sql = document.getElementById('it_sql_query').value.trim();
        if (!sql) {
            alert('กรุณาระบุคำสั่ง SQL ก่อนทดสอบรัน');
            return;
        }

        const previewContainer = document.getElementById('queryPreviewContainer');
        const previewStatus = document.getElementById('queryPreviewStatus');
        const thead = document.getElementById('queryPreviewHead');
        const tbody = document.getElementById('queryPreviewBody');

        previewContainer.style.display = 'block';
        previewStatus.innerHTML = '<span style="color: #0284c7;"><i class="bi bi-arrow-repeat spin"></i> กำลังเชื่อมต่อ HosXP และรัน Query...</span>';
        thead.innerHTML = '';
        tbody.innerHTML = '';

        fetch("{{ route('data-requests.execute-query', $dataRequest->id) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ sql_query: sql })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                previewStatus.innerHTML = `<span style="color: #dc2626;"><i class="bi bi-exclamation-octagon"></i> ${data.message || 'เกิดข้อผิดพลาดในการรัน SQL'}</span>`;
                return;
            }

            previewStatus.innerHTML = `<span style="color: #16a34a;"><i class="bi bi-check2-circle"></i> รันสำเร็จ! แสดงตัวอย่าง ${data.total_rows} แถว (ความเร็ว ${data.latency_ms} ms)</span>`;

            if (data.columns && data.columns.length > 0) {
                let headerHtml = '<tr>';
                data.columns.forEach(col => {
                    headerHtml += `<th style="padding: 6px 10px; border-bottom: 2px solid #cbd5e1; background: #f8fafc;">${col}</th>`;
                });
                headerHtml += '</tr>';
                thead.innerHTML = headerHtml;

                let bodyHtml = '';
                data.rows.forEach(row => {
                    bodyHtml += '<tr>';
                    data.columns.forEach(col => {
                        bodyHtml += `<td style="padding: 5px 10px; border-bottom: 1px solid #e2e8f0;">${row[col] !== null ? row[col] : '<em style="color:#94a3b8;">NULL</em>'}</td>`;
                    });
                    bodyHtml += '</tr>';
                });
                tbody.innerHTML = bodyHtml;
            } else {
                tbody.innerHTML = '<tr><td style="padding: 10px; text-align: center; color: #64748b;">ไม่พบข้อมูลตามเงื่อนไขที่ระบุ</td></tr>';
            }
        })
        .catch(err => {
            previewStatus.innerHTML = `<span style="color: #dc2626;"><i class="bi bi-exclamation-octagon"></i> เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์</span>`;
        });
    });
});
</script>
@endsection
