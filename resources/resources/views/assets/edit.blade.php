@extends('layouts.app')

@section('title', 'แก้ไขครุภัณฑ์ ' . $asset->asset_code)
@section('page-title', 'แก้ไขข้อมูลครุภัณฑ์ IT')
@section('breadcrumb', 'ระบบพัสดุและคลัง / ทะเบียนครุภัณฑ์ IT / แก้ไขข้อมูล ' . $asset->asset_code)

@section('topbar-actions')
<a href="{{ route('assets.show', $asset) }}" class="topbar-btn" title="กลับไปหน้ารายละเอียด">
    <i class="bi bi-arrow-left"></i>
    <span>กลับหน้ารายละเอียด</span>
</a>
@endsection

@push('styles')
<style>
    .form-section-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid var(--border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        margin-bottom: 22px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .form-section-card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        border-color: #cbd5e1;
    }

    .form-section-header {
        padding: 16px 22px;
        background: linear-gradient(to right, #f8fafc, #f1f5f9);
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }

    .form-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }

    .form-section-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }

    .form-section-body {
        padding: 22px;
    }

    /* Type Selector Chips */
    .type-pill-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        font-size: 12.5px;
        font-weight: 500;
        color: #334155;
        cursor: pointer;
        transition: all 0.18s ease;
        user-select: none;
    }

    .type-pill-btn:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        transform: translateY(-1px);
    }

    .type-pill-btn.active {
        background: #0d9488;
        border-color: #0d9488;
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.25);
    }

    /* Status Radio Cards */
    .status-card-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
        gap: 10px;
    }

    .status-radio-card {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }

    .status-radio-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .status-radio-card input {
        display: none;
    }

    .status-radio-card.active-status {
        border-color: #0d9488;
        background: #f0fdfa;
        box-shadow: 0 2px 10px rgba(13, 148, 136, 0.15);
    }

    .status-radio-card.active-status.status-spare {
        border-color: #0284c7;
        background: #f0f9ff;
    }

    .status-radio-card.active-status.status-repairing {
        border-color: #f59e0b;
        background: #fffbeb;
    }

    .status-radio-card.active-status.status-broken {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .status-radio-card.active-status.status-disposed {
        border-color: #64748b;
        background: #f1f5f9;
    }

    /* Quick Preset Buttons */
    .quick-tag {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 4px;
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s;
    }

    .quick-tag:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Image Dropzone */
    .image-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s;
    }

    .image-dropzone:hover {
        border-color: #0d9488;
        background: #f0fdfa;
    }
</style>
@endpush

@section('content')
<div style="max-width: 960px; margin: 0 auto; padding-bottom: 60px;">

    {{-- Header Banner --}}
    <div style="margin-bottom: 22px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                <i class="bi bi-pencil-square"></i>
            </div>
            <div>
                <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">
                    แก้ไขข้อมูลครุภัณฑ์: <span style="color: #0284c7;">{{ $asset->asset_code }}</span>
                </h2>
                <p style="font-size: 13.5px; color: #64748b; margin: 0;">
                    {{ $asset->name }} &bull; ประจำหน่วยงาน {{ $asset->department->name ?? 'ไม่ระบุ' }}
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('assets.show', $asset) }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
            <a href="{{ route('assets.label', $asset) }}" class="btn btn-outline-primary btn-sm" target="_blank" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-qr-code"></i> พิมพ์สติกเกอร์
            </a>
        </div>
    </div>

    {{-- Edit Form --}}
    <form action="{{ route('assets.update', $asset) }}" method="POST" enctype="multipart/form-data" id="editAssetForm">
        @csrf
        @method('PUT')

        {{-- SECTION 1: ข้อมูลพื้นฐานและการจำแนกประเภท (Asset Identity) --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div class="form-section-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="bi bi-tag-fill"></i>
                    </div>
                    <span>1. ข้อมูลพื้นฐานและประเภทครุภัณฑ์ (Asset Identification)</span>
                </div>
                <span style="font-size: 12px; color: #0284c7; font-weight: 600; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">
                    ข้อมูลจำเป็น *
                </span>
            </div>

            <div class="form-section-body">
                {{-- Quick Device Type Chips --}}
                <div style="margin-bottom: 18px;">
                    <label class="form-label" style="font-size: 12.5px; color: #64748b; margin-bottom: 6px;">
                        เลือกประเภทอุปกรณ์ด่วน:
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($deviceTypes as $type)
                            @php
                                $icon = 'bi-device-hdd';
                                $lower = mb_strtolower($type->name);
                                if (str_contains($lower, 'ตั้งโต๊ะ') || str_contains($lower, 'pc')) $icon = 'bi-pc-display';
                                elseif (str_contains($lower, 'โน้ตบุ๊ก') || str_contains($lower, 'notebook') || str_contains($lower, 'laptop')) $icon = 'bi-laptop';
                                elseif (str_contains($lower, 'all-in-one') || str_contains($lower, 'aio')) $icon = 'bi-display';
                                elseif (str_contains($lower, 'server') || str_contains($lower, 'เซิร์ฟเวอร์')) $icon = 'bi-server';
                                elseif (str_contains($lower, 'พิมพ์') || str_contains($lower, 'printer')) $icon = 'bi-printer';
                                elseif (str_contains($lower, 'สแกนเนอร์') || str_contains($lower, 'scanner')) $icon = 'bi-scanner';
                                elseif (str_contains($lower, 'เครือข่าย') || str_contains($lower, 'switch') || str_contains($lower, 'router')) $icon = 'bi-hdd-network';
                                $isActiveType = (old('device_type_id', $asset->device_type_id) == $type->id);
                            @endphp
                            <button type="button" class="type-pill-btn {{ $isActiveType ? 'active' : '' }}"
                                    data-type-id="{{ $type->id }}"
                                    data-type-name="{{ $type->name }}"
                                    onclick="selectDeviceType('{{ $type->id }}', this)">
                                <i class="bi {{ $icon }}"></i>
                                <span>{{ $type->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1.2;">
                        <label class="form-label required" for="asset_code">หมายเลขครุภัณฑ์ (Asset Code)</label>
                        <input type="text" id="asset_code" name="asset_code" class="form-control"
                               value="{{ old('asset_code', $asset->asset_code) }}" required>
                        <span class="form-text">รหัสหมายเลขครุภัณฑ์ตามทะเบียนพัสดุ</span>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="serial_number">Serial Number (S/N)</label>
                        <input type="text" id="serial_number" name="serial_number" class="form-control"
                               value="{{ old('serial_number', $asset->serial_number) }}" placeholder="เช่น S/N บนตัวเครื่อง">
                    </div>

                    <div class="form-group" style="flex: 1.2;">
                        <label class="form-label required" for="device_type_id">ประเภทอุปกรณ์ (Device Type)</label>
                        <select name="device_type_id" id="device_type_id" class="form-select" required onchange="handleTypeDropdownChange(this)">
                            <option value="">-- กรุณาเลือกประเภท --</option>
                            @foreach($deviceTypes as $type)
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}" {{ old('device_type_id', $asset->device_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- ICT Standard Catalog Selector (เกณฑ์ราคากลางกระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม) --}}
                <div style="background: linear-gradient(135deg, #f8fafc 0%, #f0fdfa 100%); border: 1.5px solid #99f6e4; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 28px; height: 28px; border-radius: 7px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                <i class="bi bi-bookmark-check-fill"></i>
                            </div>
                            <div>
                                <strong style="font-size: 13.5px; color: #0f766e;">ปรับชื่อรายการตามเกณฑ์ราคากลางและคุณลักษณะพื้นฐานฯ</strong>
                                <span style="font-size: 11.5px; color: #64748b; margin-left: 4px;">(กระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม)</span>
                            </div>
                        </div>
                        <span id="ictStandardBadge" style="display: none; background: #ccfbf1; color: #0f766e; border: 1px solid #5eead4; font-size: 11.5px; font-weight: 600; padding: 3px 10px; border-radius: 999px;">
                            <i class="bi bi-patch-check-fill text-success me-1"></i> ตรงตามเกณฑ์มาตรฐานภาครัฐ
                        </span>
                    </div>

                    {{-- Grouped Select by Category --}}
                    <div style="margin-bottom: 10px;">
                        <select id="ict_standard_select" class="form-select" onchange="handleIctStandardSelect(this.value)" style="border-color: #5eead4; background-color: #ffffff; font-size: 13.5px; font-weight: 500;">
                            <option value="">-- คลิกเพื่อเลือกปรับชื่อตามเกณฑ์ราคากลาง ICT (มีสเปกและราคากลางแนะนำ) --</option>
                            @if(isset($ictStandardsGrouped))
                                @foreach($ictStandardsGrouped as $category => $items)
                                    <optgroup label="📁 {{ $category }}">
                                        @foreach($items as $item)
                                            <option value="{{ $item['code'] }}">
                                                {{ $item['name'] }} (ราคากลาง: {{ number_format($item['standard_price']) }} บาท)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Quick Popular Standard Pills --}}
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                        <span style="font-size: 11px; font-weight: 600; color: #0d9488;">เกณฑ์ยอดนิยม:</span>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('PC-PROC-2')">💻 PC ประมวลผล (แบบ 2 - 27,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('PC-OFFICE')">💻 PC สำนักงาน (17,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('NB-PROC')">💻 โน้ตบุ๊กประมวลผล (26,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('NB-OFFICE')">💻 โน้ตบุ๊ก สนง. (21,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('AIO-PROC')">🖥️ All-in-One (23,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('PRN-LASER-NET')">🖨️ เลเซอร์ Network (8,500 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('PRN-MFP-MONO')">🖨️ มัลติฟังก์ชันเลเซอร์ (7,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('PRN-INK-TANK')">🖨️ Ink Tank แท้ (5,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('PRN-LABEL-BARCODE')">🏷️ พิมพ์สติกเกอร์ยา (12,000 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('UPS-800VA')">🔋 UPS 800VA (2,500 บ.)</button>
                        <button type="button" class="quick-tag" onclick="selectIctByCode('SCN-SMART-CARD')">💳 อ่านบัตร ปชช. (500 บ.)</button>
                    </div>

                    {{-- Description & Specs Hint Box (Appears when item selected) --}}
                    <div id="ictInfoBox" style="display: none; margin-top: 10px; padding: 10px 14px; background: #ffffff; border: 1px solid #99f6e4; border-radius: 8px; font-size: 12px; color: #334155;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap;">
                            <div style="flex: 1; min-width: 240px;">
                                <strong id="ictInfoTitle" style="color: #0f766e; font-size: 12.5px;">-</strong>
                                <div id="ictInfoDesc" style="color: #64748b; margin-top: 2px; line-height: 1.4;">-</div>
                            </div>
                            <div style="text-align: right; flex-shrink: 0;">
                                <span style="font-size: 11px; color: #64748b;">ราคากลางมาตรฐาน:</span>
                                <div id="ictInfoPrice" style="font-size: 14.5px; font-weight: 700; color: #0284c7;">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1.5;">
                        <label class="form-label required" for="name">
                            ชื่อรายการครุภัณฑ์ (Asset Name)
                            <small class="text-muted" style="font-weight: 400;">(สามารถเลือกจากเกณฑ์ด้านบน หรือพิมพ์แก้ไขต่อท้ายได้)</small>
                        </label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name', $asset->name) }}" required>
                        <span class="form-text">ชื่อทางการสำหรับทะเบียนพัสดุและรายงานตามระเบียบราชการ</span>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="brand">ยี่ห้อ (Brand)</label>
                        <input type="text" id="brand" name="brand" class="form-control" list="brand_suggestions"
                               value="{{ old('brand', $asset->brand) }}" placeholder="เช่น Dell, HP, Lenovo, Brother">
                        <datalist id="brand_suggestions">
                            <option value="Dell">
                            <option value="HP">
                            <option value="Lenovo">
                            <option value="Acer">
                            <option value="Asus">
                            <option value="Brother">
                            <option value="Canon">
                            <option value="Epson">
                            <option value="Cisco">
                            <option value="Ruijie">
                            <option value="MikroTik">
                        </datalist>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="model">รุ่น (Model)</label>
                        <input type="text" id="model" name="model" class="form-control"
                               value="{{ old('model', $asset->model) }}" placeholder="เช่น OptiPlex 7010, ThinkCentre">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 2: คุณลักษณะเฉพาะทางฮาร์ดแวร์ & สเปกเครื่อง (Hardware Specifications) --}}
        <div class="form-section-card" id="hardwareSpecsSection">
            <div class="form-section-header" style="background: linear-gradient(to right, #f0fdfa, #f8fafc);">
                <div class="form-section-title">
                    <div class="form-section-icon" style="background: #ccfbf1; color: #0d9488;">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <span>2. คุณลักษณะและสเปกฮาร์ดแวร์ (Hardware Specifications)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 11.5px; color: #0f766e; background: #e6fffa; border: 1px solid #99f6e4; padding: 2px 8px; border-radius: 6px;">
                        สำหรับ PC, โน้ตบุ๊ก, All-in-One, Server
                    </span>
                </div>
            </div>

            <div class="form-section-body" style="padding-bottom: 12px;">
                @include('assets._specs_form', ['asset' => $asset])
            </div>
        </div>

        {{-- SECTION 3: สถานที่ติดตั้งและผู้ครอบครอง (Location & Custodian) --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div class="form-section-icon" style="background: #ede9fe; color: #7c3aed;">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <span>3. สถานที่ติดตั้งและผู้ครอบครอง (Location & Custodian)</span>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 1.2;">
                        <label class="form-label" for="department_id">
                            <i class="bi bi-building text-secondary"></i> แผนก / หน่วยงานที่รับผิดชอบ
                        </label>
                        <select name="department_id" id="department_id" class="form-select">
                            <option value="">-- ไม่ระบุแผนก --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $asset->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="flex: 1.2;">
                        <label class="form-label" for="location_detail">
                            <i class="bi bi-pin-map text-danger"></i> จุดที่ตั้ง / ห้องทำงาน
                        </label>
                        <input type="text" id="location_detail" name="location_detail" class="form-control"
                               value="{{ old('location_detail', $asset->location_detail) }}" placeholder="เช่น เคาน์เตอร์พยาบาล, โต๊ะทำงานชั้น 2">
                        <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; align-items: center;">
                            <span style="font-size: 11px; color: #64748b;">ตำแหน่งด่วน:</span>
                            <span class="quick-tag" onclick="setLocation('เคาน์เตอร์พยาบาล')">เคาน์เตอร์พยาบาล</span>
                            <span class="quick-tag" onclick="setLocation('ห้องตรวจแพทย์ 1')">ห้องตรวจแพทย์ 1</span>
                            <span class="quick-tag" onclick="setLocation('ห้องจ่ายยา / เภสัชกรรม')">ห้องจ่ายยา</span>
                            <span class="quick-tag" onclick="setLocation('ห้องการเงินและบัญชี')">ห้องการเงิน</span>
                            <span class="quick-tag" onclick="setLocation('ห้องเซิร์ฟเวอร์ / งาน IT')">งาน IT</span>
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="custodian_name">
                            <i class="bi bi-person-fill text-primary"></i> ชื่อผู้ครอบครอง / ผู้ดูแล
                        </label>
                        <input type="text" id="custodian_name" name="custodian_name" class="form-control"
                               value="{{ old('custodian_name', $asset->custodian_name) }}" placeholder="เช่น พว.สุดาพร ใจดี">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 4: การเชื่อมต่อเครือข่าย (Network Settings) --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div class="form-section-icon" style="background: #e0f2fe; color: #0369a1;">
                        <i class="bi bi-hdd-network-fill"></i>
                    </div>
                    <span>4. การเชื่อมต่อเครือข่าย (Network Settings)</span>
                </div>
                <span style="font-size: 12px; color: #64748b;">Subnet รพ.ทุ่งหัวช้าง 192.168.2.x</span>
            </div>

            <div class="form-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="ip_address">
                            <i class="bi bi-router text-primary"></i> IP Address
                        </label>
                        <input type="text" id="ip_address" name="ip_address" class="form-control"
                               value="{{ old('ip_address', $asset->ip_address) }}" placeholder="เช่น 192.168.2.55">
                        <span class="form-text">IP Address ภายในระบบ LAN โรงพยาบาล</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="mac_address">
                            <i class="bi bi-ethernet text-secondary"></i> MAC Address
                        </label>
                        <input type="text" id="mac_address" name="mac_address" class="form-control"
                               value="{{ old('mac_address', $asset->mac_address) }}" placeholder="เช่น 00:1A:2B:3C:4D:5E">
                        <span class="form-text">Hardware Address การ์ดแลน / Wi-Fi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 5: การจัดซื้อ, ประกัน & สถานะ (Procurement & Status) --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div class="form-section-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <span>5. ข้อมูลการจัดซื้อ ประกัน และสถานะครุภัณฑ์ (Procurement & Status)</span>
                </div>
            </div>

            <div class="form-section-body">
                {{-- Status Selection --}}
                <div style="margin-bottom: 20px;">
                    <label class="form-label required" style="margin-bottom: 8px;">สถานะครุภัณฑ์ (Asset Status)</label>
                    <div class="status-card-group">
                        @php $currentStatus = old('status', $asset->status); @endphp
                        <label class="status-radio-card {{ $currentStatus == 'active' ? 'active-status' : '' }}" onclick="selectStatusCard(this)">
                            <input type="radio" name="status" value="active" {{ $currentStatus == 'active' ? 'checked' : '' }} required>
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 20px;"></i>
                            <strong style="font-size: 13px; color: #065f46;">ใช้งานปกติ</strong>
                            <span style="font-size: 11px; color: #64748b;">Active</span>
                        </label>

                        <label class="status-radio-card status-spare {{ $currentStatus == 'spare' ? 'active-status' : '' }}" onclick="selectStatusCard(this)">
                            <input type="radio" name="status" value="spare" {{ $currentStatus == 'spare' ? 'checked' : '' }}>
                            <i class="bi bi-shield-check text-info" style="font-size: 20px;"></i>
                            <strong style="font-size: 13px; color: #0284c7;">เครื่องสำรอง</strong>
                            <span style="font-size: 11px; color: #64748b;">Spare</span>
                        </label>

                        <label class="status-radio-card status-repairing {{ $currentStatus == 'repairing' ? 'active-status' : '' }}" onclick="selectStatusCard(this)">
                            <input type="radio" name="status" value="repairing" {{ $currentStatus == 'repairing' ? 'checked' : '' }}>
                            <i class="bi bi-tools text-warning" style="font-size: 20px;"></i>
                            <strong style="font-size: 13px; color: #d97706;">ส่งซ่อม</strong>
                            <span style="font-size: 11px; color: #64748b;">In Repair</span>
                        </label>

                        <label class="status-radio-card status-broken {{ $currentStatus == 'broken' ? 'active-status' : '' }}" onclick="selectStatusCard(this)">
                            <input type="radio" name="status" value="broken" {{ $currentStatus == 'broken' ? 'checked' : '' }}>
                            <i class="bi bi-exclamation-octagon-fill text-danger" style="font-size: 20px;"></i>
                            <strong style="font-size: 13px; color: #dc2626;">ชำรุดรอซ่อม</strong>
                            <span style="font-size: 11px; color: #64748b;">Broken</span>
                        </label>

                        <label class="status-radio-card status-disposed {{ $currentStatus == 'disposed' ? 'active-status' : '' }}" onclick="selectStatusCard(this)">
                            <input type="radio" name="status" value="disposed" {{ $currentStatus == 'disposed' ? 'checked' : '' }}>
                            <i class="bi bi-archive-fill text-secondary" style="font-size: 20px;"></i>
                            <strong style="font-size: 13px; color: #475569;">รอแทงจำหน่าย</strong>
                            <span style="font-size: 11px; color: #64748b;">Disposed</span>
                        </label>
                    </div>
                </div>

                {{-- Financial & Dates --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="purchase_date">วันที่ตรวจรับ / ซื้อ</label>
                        <input type="date" id="purchase_date" name="purchase_date" class="form-control"
                               value="{{ old('purchase_date', $asset->purchase_date?->format('Y-m-d')) }}" onchange="handlePurchaseDateChange(this.value)">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="price">ราคาจัดซื้อ (บาท)</label>
                        <div style="position: relative;">
                            <input type="number" step="0.01" id="price" name="price" class="form-control"
                                   value="{{ old('price', $asset->price) }}" placeholder="เช่น 24500.00" style="padding-right: 42px;">
                            <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #94a3b8;">บาท</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="warranty_expire_date">วันหมดประกัน</label>
                        <input type="date" id="warranty_expire_date" name="warranty_expire_date" class="form-control"
                               value="{{ old('warranty_expire_date', $asset->warranty_expire_date?->format('Y-m-d')) }}">
                        <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; align-items: center;">
                            <span style="font-size: 11px; color: #64748b;">คำนวณ:</span>
                            <span class="quick-tag" onclick="addWarrantyYears(1)">+1 ปี</span>
                            <span class="quick-tag" onclick="addWarrantyYears(3)">+3 ปี</span>
                            <span class="quick-tag" onclick="addWarrantyYears(5)">+5 ปี</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="budget_year">ปีงบประมาณ</label>
                        <input type="text" id="budget_year" name="budget_year" class="form-control"
                               value="{{ old('budget_year', $asset->budget_year) }}" placeholder="เช่น 2567, 2568">
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 6: รูปถ่ายและหมายเหตุเพิ่มเติม (Photo & Notes) --}}
        <div class="form-section-card">
            <div class="form-section-header">
                <div class="form-section-title">
                    <div class="form-section-icon" style="background: #f1f5f9; color: #475569;">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                    <span>6. รูปถ่ายครุภัณฑ์และหมายเหตุ (Photo & Notes)</span>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" for="image">รูปถ่ายครุภัณฑ์ (Asset Image)</label>
                        <div class="image-dropzone" onclick="document.getElementById('image').click()">
                            <input type="file" id="image" name="image" class="form-control" accept="image/*" style="display: none;" onchange="previewAssetImage(event)">
                            <div id="imageUploadPrompt" style="{{ $asset->image ? 'display: none;' : '' }}">
                                <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 28px;"></i>
                                <div style="font-size: 13px; font-weight: 600; color: #1e293b; margin-top: 4px;">คลิกเพื่อเปลี่ยนรูปถ่าย</div>
                                <div style="font-size: 11.5px; color: #64748b;">รองรับไฟล์ JPG, PNG สูงสุด 3MB</div>
                            </div>
                            <div id="imagePreviewBox" style="display: {{ $asset->image ? 'flex' : 'none' }}; align-items: center; justify-content: center; gap: 12px; margin-top: 8px;">
                                <img id="imagePreviewImg" src="{{ $asset->image ? asset($asset->image) : '' }}" alt="รูปถ่ายครุภัณฑ์" style="max-height: 110px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                <button type="button" class="btn btn-sm btn-light border text-danger" onclick="removeAssetImage(event)" title="ลบรูป">
                                    <i class="bi bi-trash"></i> ลบรูป
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1.5;">
                        <label class="form-label" for="notes">หมายเหตุเพิ่มเติม (Notes)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="4" placeholder="บันทึกข้อมูลเพิ่มเติม เช่น ประวัติการโยกย้าย, รหัสพัสดุเดิม...">{{ old('notes', $asset->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Actions Bar --}}
        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 14px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
            <a href="{{ route('assets.show', $asset) }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="bi bi-x-circle"></i>
                <span>ยกเลิก</span>
            </a>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="submit" class="btn btn-primary btn-lg" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; font-weight: 600; box-shadow: 0 4px 14px rgba(13, 148, 136, 0.3);">
                    <i class="bi bi-floppy-fill"></i>
                    <span>บันทึกการแก้ไขข้อมูล</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Set Device Type from Pills
    function selectDeviceType(typeId, buttonElem) {
        const select = document.getElementById('device_type_id');
        if (select) {
            select.value = typeId;
            handleTypeDropdownChange(select);
        }

        // Update pills active class
        document.querySelectorAll('.type-pill-btn').forEach(btn => btn.classList.remove('active'));
        if (buttonElem) {
            buttonElem.classList.add('active');
        }
    }

    function handleTypeDropdownChange(selectElem) {
        const typeId = selectElem.value;
        const selectedText = selectElem.options[selectElem.selectedIndex]?.text?.toLowerCase() || '';

        // Sync pills active state
        document.querySelectorAll('.type-pill-btn').forEach(btn => {
            if (btn.getAttribute('data-type-id') === typeId) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Highlight hardware section if computer/notebook/server
        const hwSection = document.getElementById('hardwareSpecsSection');
        const isComputer = selectedText.includes('คอมพิวเตอร์') || 
                           selectedText.includes('โน้ตบุ๊ก') || 
                           selectedText.includes('notebook') || 
                           selectedText.includes('all-in-one') || 
                           selectedText.includes('server');

        if (hwSection) {
            if (isComputer) {
                hwSection.style.border = '2px solid #0d9488';
                hwSection.style.boxShadow = '0 6px 20px rgba(13, 148, 136, 0.12)';
            } else {
                hwSection.style.border = '1px solid var(--border)';
                hwSection.style.boxShadow = '0 2px 8px rgba(0,0,0,0.02)';
            }
        }
    }

    function setLocation(val) {
        const input = document.getElementById('location_detail');
        if (input) input.value = val;
    }

    // Status Radio Card Selector
    function selectStatusCard(cardElem) {
        document.querySelectorAll('.status-radio-card').forEach(c => c.classList.remove('active-status'));
        cardElem.classList.add('active-status');
        const radio = cardElem.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    }

    // Auto Warranty Calculation
    function addWarrantyYears(years) {
        const purchaseInput = document.getElementById('purchase_date');
        const warrantyInput = document.getElementById('warranty_expire_date');
        if (!purchaseInput || !purchaseInput.value) {
            alert('กรุณาระบุ วันที่ตรวจรับ/ซื้อ ก่อนคำนวณวันหมดประกัน');
            return;
        }

        const date = new Date(purchaseInput.value);
        date.setFullYear(date.getFullYear() + years);
        warrantyInput.value = date.toISOString().split('T')[0];
    }

    function handlePurchaseDateChange(val) {
        const budgetYearInput = document.getElementById('budget_year');
        if (val && budgetYearInput && (!budgetYearInput.value || budgetYearInput.value === '2568')) {
            const date = new Date(val);
            const gregorianYear = date.getFullYear();
            const month = date.getMonth() + 1;
            const budgetYear = (month >= 10 ? gregorianYear + 1 : gregorianYear) + 543;
            budgetYearInput.value = budgetYear;
        }
    }

    // Image Upload & Preview
    function previewAssetImage(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('imagePreviewImg').src = evt.target.result;
                document.getElementById('imageUploadPrompt').style.display = 'none';
                document.getElementById('imagePreviewBox').style.display = 'flex';
            };
            reader.readAsDataURL(file);
        }
    }

    function removeAssetImage(e) {
        if (e) e.stopPropagation();
        const input = document.getElementById('image');
        if (input) input.value = '';
        document.getElementById('imagePreviewImg').src = '';
        document.getElementById('imagePreviewBox').style.display = 'none';
        document.getElementById('imageUploadPrompt').style.display = 'block';
    }

    // ICT Standard Catalog Integration
    const ictCatalog = @json($ictStandardsAll ?? []);

    function selectIctByCode(code) {
        const select = document.getElementById('ict_standard_select');
        if (select) {
            select.value = code;
            handleIctStandardSelect(code);
        }
    }

    function handleIctStandardSelect(code) {
        if (!code) {
            document.getElementById('ictStandardBadge').style.display = 'none';
            document.getElementById('ictInfoBox').style.display = 'none';
            return;
        }

        const item = ictCatalog.find(i => i.code === code);
        if (!item) return;

        // 1. Update Name
        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.value = item.name;
        }

        // 2. Select Matching Device Type
        if (item.device_type_code) {
            const devSelect = document.getElementById('device_type_id');
            if (devSelect) {
                const opt = Array.from(devSelect.options).find(o => o.getAttribute('data-code') === item.device_type_code);
                if (opt) {
                    devSelect.value = opt.value;
                    handleTypeDropdownChange(devSelect);
                }
            }
        }

        // 3. Suggest Standard Price if empty
        const priceInput = document.getElementById('price');
        if (priceInput && !priceInput.value) {
            priceInput.value = parseFloat(item.standard_price).toFixed(2);
        }

        // 4. Fill Recommended Specs (if fields are currently empty)
        if (item.specs) {
            const fillIfEmpty = (id, val) => {
                const elem = document.getElementById(id);
                if (elem && !elem.value && val !== undefined && val !== null && val !== '') {
                    elem.value = val;
                }
            };

            fillIfEmpty('cpu_model', item.specs.cpu_model);
            fillIfEmpty('cpu_speed', item.specs.cpu_speed);
            fillIfEmpty('ram_capacity', item.specs.ram_capacity);
            fillIfEmpty('ram_type', item.specs.ram_type);
            fillIfEmpty('ram_bus', item.specs.ram_bus);
            fillIfEmpty('ram_slots', item.specs.ram_slots);
            fillIfEmpty('storage_type', item.specs.storage_type);
            fillIfEmpty('storage_capacity', item.specs.storage_capacity);
            fillIfEmpty('storage_second', item.specs.storage_second);
            fillIfEmpty('os_name', item.specs.os_name);
            fillIfEmpty('os_license', item.specs.os_license);
            fillIfEmpty('gpu_model', item.specs.gpu_model);
            fillIfEmpty('monitor_size', item.specs.monitor_size);

            if (typeof updateLiveSpecsPreview === 'function') {
                updateLiveSpecsPreview();
            }
        }

        // 5. Display ICT Info Box & Badge
        const badge = document.getElementById('ictStandardBadge');
        if (badge) {
            badge.style.display = 'inline-flex';
            badge.innerHTML = '<i class="bi bi-patch-check-fill text-success me-1"></i> เกณฑ์มาตรฐาน ICT (ราคากลาง ' + Number(item.standard_price).toLocaleString() + ' บาท)';
        }

        const infoBox = document.getElementById('ictInfoBox');
        if (infoBox) {
            infoBox.style.display = 'block';
            document.getElementById('ictInfoTitle').textContent = item.name;
            document.getElementById('ictInfoDesc').textContent = item.description || 'คุณลักษณะพื้นฐานตามเกณฑ์กระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม';
            document.getElementById('ictInfoPrice').textContent = Number(item.standard_price).toLocaleString() + ' บาท';
        }
    }

    // Initial check on load: if asset name matches any ICT standard, show badge
    document.addEventListener('DOMContentLoaded', function() {
        const currentName = document.getElementById('name')?.value || '';
        if (currentName) {
            const matched = ictCatalog.find(i => i.name.trim() === currentName.trim() || currentName.includes(i.short_name));
            if (matched) {
                const badge = document.getElementById('ictStandardBadge');
                if (badge) {
                    badge.style.display = 'inline-flex';
                    badge.innerHTML = '<i class="bi bi-patch-check-fill text-success me-1"></i> เกณฑ์มาตรฐาน ICT: ' + matched.short_name;
                }
                const select = document.getElementById('ict_standard_select');
                if (select) select.value = matched.code;
            }
        }
    });
</script>
@endpush
