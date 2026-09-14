<div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 22px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; color: #0f172a;">
            <i class="bi bi-cpu text-primary" style="font-size: 20px;"></i>
            <span>สเปกฮาร์ดแวร์คอมพิวเตอร์ (Hardware Specifications)</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn-detect-specs" onclick="openClientHardwareModal()" id="btnDetectHardwareSpecs"
                    title="อ่านค่าสเปกฮาร์ดแวร์จากเครื่องคอมพิวเตอร์ที่เข้าใช้งานนี้เพื่อกรอกลงฟอร์มอัตโนมัติ">
                <i class="bi bi-pc-display"></i>
                <span>⚡ อ่านสเปกจากเครื่องนี้ (Auto-Detect)</span>
            </button>
            <span style="font-size: 12px; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 6px;">
                สำหรับ PC, โน้ตบุ๊ก, All-in-One และ Server
            </span>
        </div>
    </div>

    {{-- Live Specs Summary Chip --}}
    <div style="background: linear-gradient(135deg, #e0f2fe 0%, #ede9fe 100%); border: 1px solid #7dd3fc; border-radius: 8px; padding: 10px 14px; margin-bottom: 18px; display: flex; align-items: center; gap: 10px; font-size: 13px; color: #0369a1;">
        <i class="bi bi-eye-fill text-primary" style="font-size: 16px;"></i>
        <div>
            <strong>ตัวอย่างสรุปสเปก:</strong>
            <span id="liveSpecsPreviewText" style="font-weight: 600; color: #0f172a; margin-left: 4px;">
                {{ isset($asset) && $asset->formatted_specs ? $asset->formatted_specs : 'ยังไม่ได้ระบุสเปก' }}
            </span>
        </div>
    </div>

    {{-- CPU Section --}}
    <div class="form-row">
        <div class="form-group" style="flex: 2;">
            <label class="form-label" for="cpu_model">
                <i class="bi bi-cpu text-primary"></i> รุ่นตัวประมวลผล (CPU Model)
            </label>
            <input type="text" id="cpu_model" name="cpu_model" class="form-control spec-input" list="cpu_suggestions"
                   value="{{ old('cpu_model', $asset->cpu_model ?? '') }}" placeholder="เช่น Intel Core i5-12500, AMD Ryzen 7 7730U">
            <datalist id="cpu_suggestions">
                <option value="Intel Core i3-10100">
                <option value="Intel Core i3-12100">
                <option value="Intel Core i5-10400">
                <option value="Intel Core i5-12400">
                <option value="Intel Core i5-12500">
                <option value="Intel Core i5-13400">
                <option value="Intel Core i5-13500">
                <option value="Intel Core i5-14500">
                <option value="Intel Core i7-12700">
                <option value="Intel Core i7-13700">
                <option value="Intel Core i7-14700">
                <option value="AMD Ryzen 5 5600G">
                <option value="AMD Ryzen 5 7500F">
                <option value="AMD Ryzen 7 5700G">
                <option value="AMD Ryzen 7 7730U">
                <option value="Apple M2">
                <option value="Apple M3">
            </datalist>
            {{-- Quick CPU Chips --}}
            <div style="display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; align-items: center;">
                <span style="font-size: 11px; color: #64748b;">เลือกด่วน:</span>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px; border-radius: 4px;" onclick="setCpu('Intel Core i3-12100')">i3-12100</button>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px; border-radius: 4px;" onclick="setCpu('Intel Core i5-12500')">i5-12500</button>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px; border-radius: 4px;" onclick="setCpu('Intel Core i5-13500')">i5-13500</button>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px; border-radius: 4px;" onclick="setCpu('Intel Core i5-14500')">i5-14500</button>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px; border-radius: 4px;" onclick="setCpu('Intel Core i7-13700')">i7-13700</button>
                <button type="button" class="btn btn-sm btn-light border py-0 px-2" style="font-size: 11px; border-radius: 4px;" onclick="setCpu('AMD Ryzen 7 7730U')">Ryzen 7</button>
            </div>
        </div>

        <div class="form-group" style="flex: 1;">
            <label class="form-label" for="cpu_speed">ความเร็ว / Turbo (GHz)</label>
            <input type="text" id="cpu_speed" name="cpu_speed" class="form-control spec-input"
                   value="{{ old('cpu_speed', $asset->cpu_speed ?? '') }}" placeholder="เช่น 3.0 GHz Turbo 4.6 GHz">
        </div>
    </div>

    {{-- RAM Section --}}
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="ram_capacity">
                <i class="bi bi-memory text-success"></i> ความจุ RAM (GB)
            </label>
            <select name="ram_capacity" id="ram_capacity" class="form-select spec-input">
                <option value="">-- ระบุความจุ RAM --</option>
                @foreach([4, 8, 16, 32, 64, 128] as $gb)
                    <option value="{{ $gb }}" {{ old('ram_capacity', $asset->ram_capacity ?? '') == $gb ? 'selected' : '' }}>
                        {{ $gb }} GB
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="ram_type">ประเภท RAM</label>
            <select name="ram_type" id="ram_type" class="form-select spec-input">
                <option value="">-- ระบุประเภท RAM --</option>
                @foreach(['DDR5', 'DDR4', 'DDR3', 'LPDDR5', 'LPDDR4x'] as $rtype)
                    <option value="{{ $rtype }}" {{ old('ram_type', $asset->ram_type ?? '') == $rtype ? 'selected' : '' }}>
                        {{ $rtype }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="ram_bus">บัสแรม (Bus Speed)</label>
            <input type="text" id="ram_bus" name="ram_bus" class="form-control spec-input" list="bus_suggestions"
                   value="{{ old('ram_bus', $asset->ram_bus ?? '') }}" placeholder="เช่น 3200 MHz, 4800 MHz">
            <datalist id="bus_suggestions">
                <option value="2666 MHz">
                <option value="3200 MHz">
                <option value="4800 MHz">
                <option value="5200 MHz">
                <option value="5600 MHz">
            </datalist>
        </div>

        <div class="form-group">
            <label class="form-label" for="ram_slots">การติดตั้ง / สล็อต</label>
            <input type="text" id="ram_slots" name="ram_slots" class="form-control spec-input" list="slot_suggestions"
                   value="{{ old('ram_slots', $asset->ram_slots ?? '') }}" placeholder="เช่น 1 แถว (16GB x 1)">
            <datalist id="slot_suggestions">
                <option value="1 แถว (8GB x 1)">
                <option value="1 แถว (16GB x 1)">
                <option value="2 แถว (8GB x 2 Dual-Channel)">
                <option value="2 แถว (16GB x 2 Dual-Channel)">
                <option value="Onboard (ติดบอร์ด)">
            </datalist>
        </div>
    </div>

    {{-- Storage Section --}}
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="storage_type">
                <i class="bi bi-device-hdd text-secondary"></i> ชนิดไดรฟ์หลัก (Storage)
            </label>
            <select name="storage_type" id="storage_type" class="form-select spec-input">
                <option value="">-- เลือกชนิดไดรฟ์ --</option>
                @foreach(['SSD NVMe M.2', 'SSD SATA 2.5"', 'HDD SATA 3.5"', 'SSD NVMe + HDD'] as $stype)
                    <option value="{{ $stype }}" {{ old('storage_type', $asset->storage_type ?? '') == $stype ? 'selected' : '' }}>
                        {{ $stype }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="storage_capacity">ความจุไดรฟ์หลัก</label>
            <select name="storage_capacity" id="storage_capacity" class="form-select spec-input">
                <option value="">-- ระบุความจุ --</option>
                @foreach(['128 GB', '256 GB', '512 GB', '1 TB', '2 TB', '4 TB'] as $scap)
                    <option value="{{ $scap }}" {{ old('storage_capacity', $asset->storage_capacity ?? '') == $scap ? 'selected' : '' }}>
                        {{ $scap }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="storage_second">ไดรฟ์สำรอง (ถ้ามี)</label>
            <input type="text" id="storage_second" name="storage_second" class="form-control spec-input"
                   value="{{ old('storage_second', $asset->storage_second ?? '') }}" placeholder="เช่น HDD SATA 1 TB (Data)">
        </div>
    </div>

    {{-- Operating System & License & GPU --}}
    <div class="form-row">
        <div class="form-group">
            <label class="form-label" for="os_name">
                <i class="bi bi-windows text-info"></i> ระบบปฏิบัติการ (OS)
            </label>
            <select name="os_name" id="os_name" class="form-select spec-input">
                <option value="">-- เลือกระบบ OS --</option>
                @foreach(['Windows 11 Pro', 'Windows 11 Home', 'Windows 10 Pro', 'Windows 10 Home', 'Windows 7 Pro', 'Ubuntu Linux', 'macOS', 'ไม่มี OS / FreeDOS'] as $os)
                    <option value="{{ $os }}" {{ old('os_name', $asset->os_name ?? '') == $os ? 'selected' : '' }}>
                        {{ $os }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="os_license">ประเภทลิขสิทธิ์ OS</label>
            <select name="os_license" id="os_license" class="form-select spec-input">
                <option value="">-- ระบุลิขสิทธิ์ --</option>
                @foreach(['OEM (ติดเครื่อง/BIOS)', 'Volume License (KMS/MAK รพ.)', 'Retail', 'Open Source / ไม่ระบุ'] as $lic)
                    <option value="{{ $lic }}" {{ old('os_license', $asset->os_license ?? '') == $lic ? 'selected' : '' }}>
                        {{ $lic }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="gpu_model">การ์ดจอ / กราฟิก (GPU)</label>
            <input type="text" id="gpu_model" name="gpu_model" class="form-control spec-input" list="gpu_suggestions"
                   value="{{ old('gpu_model', $asset->gpu_model ?? '') }}" placeholder="เช่น Intel UHD 770, Iris Xe, RTX 3060">
            <datalist id="gpu_suggestions">
                <option value="Intel UHD Graphics 770">
                <option value="Intel UHD Graphics 730">
                <option value="Intel UHD Graphics 630">
                <option value="Intel Iris Xe Graphics">
                <option value="AMD Radeon Graphics">
                <option value="NVIDIA GeForce GTX 1650 4GB">
                <option value="NVIDIA GeForce RTX 3060 12GB">
                <option value="Onboard / Integrated">
            </datalist>
        </div>

        <div class="form-group">
            <label class="form-label" for="monitor_size">ขนาดจอภาพ (ถ้ามี)</label>
            <input type="text" id="monitor_size" name="monitor_size" class="form-control spec-input" list="monitor_suggestions"
                   value="{{ old('monitor_size', $asset->monitor_size ?? '') }}" placeholder="เช่น 23.8 นิ้ว IPS, 14 นิ้ว FHD">
            <datalist id="monitor_suggestions">
                <option value="14 นิ้ว FHD IPS">
                <option value="15.6 นิ้ว FHD">
                <option value="21.5 นิ้ว FHD">
                <option value="23.8 นิ้ว IPS FHD">
                <option value="24 นิ้ว FHD">
                <option value="27 นิ้ว 2K IPS">
            </datalist>
        </div>
    </div>

    {{-- General / Raw specs textarea for additional details --}}
    <div class="form-group mb-0">
        <label class="form-label" for="specs">
            รายละเอียดเพิ่มเติม / สเปกสรุป (Specs Description)
            <small class="text-muted" style="font-weight: 400;">(หากปล่อยว่าง ระบบจะรวมข้อมูลสเปกข้างต้นให้อัตโนมัติ)</small>
        </label>
        <textarea id="specs" name="specs" class="form-control" rows="2" placeholder="รายละเอียดอื่นๆ เช่น พอร์ตเชื่อมต่อ, Card Reader, จอสัมผัส...">{{ old('specs', $asset->specs ?? '') }}</textarea>
    </div>
</div>

{{-- Client Hardware Inspector Modal --}}
<div id="clientHardwareModal" class="custom-modal-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 1060; align-items: center; justify-content: center; padding: 16px;">
    <div class="custom-modal-dialog" style="background: #ffffff; border-radius: 18px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: modalScaleIn 0.2s ease-out;">
        
        {{-- Modal Hero Header --}}
        <div style="background: linear-gradient(135deg, #0f766e 0%, #0284c7 100%); padding: 16px 20px; color: #ffffff; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                    <i class="bi bi-pc-display-horizontal"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff;">อ่านค่าสเปกฮาร์ดแวร์จากเครื่องที่เข้าใช้งาน</h4>
                    <div style="font-size: 12px; opacity: 0.9; margin-top: 2px;">ตรวจจับข้อมูลคอมพิวเตอร์ปัจจุบันเพื่อนำมาใส่ในแบบฟอร์มครุภัณฑ์อัตโนมัติ</div>
                </div>
            </div>
            <button type="button" onclick="closeClientHardwareModal()" style="background: transparent; border: none; color: #ffffff; font-size: 24px; cursor: pointer; opacity: 0.8; line-height: 1;" title="ปิดหน้าต่าง">&times;</button>
        </div>

        {{-- Tab Navigation --}}
        <div style="display: flex; border-bottom: 1px solid #e2e8f0; background: #f8fafc; padding: 0 16px; flex-shrink: 0;">
            <button type="button" id="tabBtnWebDetect" onclick="switchHwTab('web')" style="padding: 12px 16px; font-size: 13px; font-weight: 600; border: none; background: transparent; border-bottom: 2.5px solid #0d9488; color: #0f766e; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-lightning-charge-fill text-warning"></i> ตรวจจับผ่านเบราว์เซอร์ทันที (Web Scan)
            </button>
            <button type="button" id="tabBtnDeepScan" onclick="switchHwTab('deep')" style="padding: 12px 16px; font-size: 13px; font-weight: 600; border: none; background: transparent; border-bottom: 2.5px solid transparent; color: #64748b; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                <i class="bi bi-terminal-fill text-primary"></i> สแกนลึกระดับฮาร์ดแวร์ 100% (PowerShell)
            </button>
        </div>

        {{-- Modal Body --}}
        <div style="padding: 20px; overflow-y: auto; flex: 1;">
            
            {{-- TAB 1: Web Auto-Detect --}}
            <div id="tabContentWebDetect">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; background: #f0fdfa; border: 1px solid #99f6e4; padding: 10px 14px; border-radius: 8px;">
                    <div style="font-size: 12.5px; color: #0f766e; font-weight: 500;">
                        <i class="bi bi-info-circle-fill text-teal me-1"></i> ตรวจพบคุณลักษณะของอุปกรณ์ปัจจุบันที่กำลังเปิดใช้งานหน้านี้
                    </div>
                    <button type="button" class="btn btn-sm btn-light border py-0 px-2" onclick="detectClientHardwareInstant(true)" style="font-size: 11.5px; font-weight: 600; border-radius: 5px;">
                        <i class="bi bi-arrow-clockwise"></i> สแกนใหม่
                    </button>
                </div>

                {{-- Detected Specs Grid --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-bottom: 18px;">
                    
                    {{-- OS Card --}}
                    <div class="hw-spec-card">
                        <div class="hw-spec-icon" style="background: #e0f2fe; color: #0284c7;"><i class="bi bi-windows"></i></div>
                        <div class="hw-spec-content">
                            <span class="hw-spec-label">ระบบปฏิบัติการ (OS)</span>
                            <span class="hw-spec-val" id="webDetOs">กำลังตรวจจับ...</span>
                            <span class="hw-spec-sub" id="webDetOsSub">OEM (ติดเครื่อง/BIOS)</span>
                        </div>
                    </div>

                    {{-- GPU Card --}}
                    <div class="hw-spec-card">
                        <div class="hw-spec-icon" style="background: #fef3c7; color: #d97706;"><i class="bi bi-gpu-card"></i></div>
                        <div class="hw-spec-content">
                            <span class="hw-spec-label">การ์ดจอ / กราฟิก (GPU)</span>
                            <span class="hw-spec-val" id="webDetGpu">กำลังตรวจจับ...</span>
                            <span class="hw-spec-sub" id="webDetGpuSub">WebGL Hardware Renderer</span>
                        </div>
                    </div>

                    {{-- CPU Card --}}
                    <div class="hw-spec-card">
                        <div class="hw-spec-icon" style="background: #ede9fe; color: #7c3aed;"><i class="bi bi-cpu"></i></div>
                        <div class="hw-spec-content">
                            <span class="hw-spec-label">หน่วยประมวลผล (CPU)</span>
                            <span class="hw-spec-val" id="webDetCpu">กำลังตรวจจับ...</span>
                            <span class="hw-spec-sub" id="webDetCpuSub">-</span>
                        </div>
                    </div>

                    {{-- RAM Card --}}
                    <div class="hw-spec-card">
                        <div class="hw-spec-icon" style="background: #dcfce7; color: #16a34a;"><i class="bi bi-memory"></i></div>
                        <div class="hw-spec-content">
                            <span class="hw-spec-label">หน่วยความจำ (RAM)</span>
                            <span class="hw-spec-val" id="webDetRam">กำลังตรวจจับ...</span>
                            <span class="hw-spec-sub" id="webDetRamSub">DDR4 3200 MHz</span>
                        </div>
                    </div>

                    {{-- Display Card --}}
                    <div class="hw-spec-card">
                        <div class="hw-spec-icon" style="background: #fae8ff; color: #a21caf;"><i class="bi bi-display"></i></div>
                        <div class="hw-spec-content">
                            <span class="hw-spec-label">จอภาพ / ความละเอียด</span>
                            <span class="hw-spec-val" id="webDetScreen">กำลังตรวจจับ...</span>
                            <span class="hw-spec-sub" id="webDetScreenSub">-</span>
                        </div>
                    </div>

                    {{-- Device Form Factor Card --}}
                    <div class="hw-spec-card">
                        <div class="hw-spec-icon" style="background: #f1f5f9; color: #475569;"><i class="bi bi-laptop"></i></div>
                        <div class="hw-spec-content">
                            <span class="hw-spec-label">ประเภทอุปกรณ์ & เครือข่าย</span>
                            <span class="hw-spec-val" id="webDetType">กำลังตรวจจับ...</span>
                            <span class="hw-spec-sub" id="webDetIp">IP: {{ request()->ip() }}</span>
                        </div>
                    </div>
                </div>

                {{-- Apply Action --}}
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                    <div style="font-size: 12.5px; color: #475569;">
                        คลิกปุ่มเพื่อบันทึกข้อมูลที่ตรวจพบลงในช่องสเปกฮาร์ดแวร์ทันที
                    </div>
                    <button type="button" class="btn btn-primary" onclick="applyWebDetectedSpecs()" style="font-weight: 600; padding: 7px 18px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none;">
                        <i class="bi bi-check2-circle" style="font-size: 16px;"></i> นำค่าที่ตรวจพบใส่ในแบบฟอร์ม
                    </button>
                </div>
            </div>

            {{-- TAB 2: Deep WMI PowerShell Scan --}}
            <div id="tabContentDeepScan" style="display: none;">
                <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 12.5px; color: #92400e; line-height: 1.5;">
                    <strong><i class="bi bi-shield-check"></i> สแกนลึกระดับฮาร์ดแวร์โรงงาน 100%:</strong>
                    อ่านค่าตรงจาก BIOS/WMI ในเครื่อง ได้ข้อมูลครบถ้วนทั้ง <strong>Serial Number (S/N)</strong>, ยี่ห้อ, รุ่น, รหัส CPU เต็ม, ความเร็ว, บัสแรม DDR4/DDR5, ชนิดไดรฟ์ SSD NVMe M.2 และ MAC Address
                </div>

                {{-- Step 1: Run Command --}}
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: block;">
                        ขั้นตอนที่ 1: คัดลอกคำสั่งนี้ แล้วกดวางใน PowerShell บนเครื่องนี้
                    </label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="cmdPowerShellScan" class="form-control" readonly
                               value="irm &quot;{{ url('/scripts/scan_spec.ps1') }}&quot; | iex"
                               style="font-family: Consolas, monospace; font-size: 12.5px; background: #0f172a; color: #38bdf8; border: 1px solid #334155;">
                        <button type="button" class="btn btn-dark" onclick="copyPowerShellCommand()" style="flex-shrink: 0; font-size: 12.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;" id="btnCopyPsCmd">
                            <i class="bi bi-clipboard"></i> คัดลอก
                        </button>
                        <a href="{{ asset('scripts/scan_spec.bat') }}" download class="btn btn-outline-secondary" style="flex-shrink: 0; font-size: 12.5px; display: inline-flex; align-items: center; gap: 5px;" title="ดาวน์โหลดไฟล์ .bat ไปดับเบิลคลิก">
                            <i class="bi bi-download"></i> .bat
                        </a>
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">
                        (กดปุ่ม Windows + X แล้วเลือก Windows PowerShell หรือ Terminal แล้วกดวางคำสั่ง)
                    </div>
                </div>

                {{-- Step 2: Paste / Import --}}
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 6px; display: block;">
                        ขั้นตอนที่ 2: เมื่อคำสั่งรันเสร็จ (ระบบจะคัดลอกสเปกลงคลิปบอร์ดให้อัตโนมัติ)
                    </label>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px; flex-wrap: wrap;">
                        <button type="button" class="btn btn-success" onclick="pasteHardwareSpecsFromClipboard()" style="font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px;">
                            <i class="bi bi-clipboard-check"></i> วางข้อมูลจากคลิปบอร์ดทันที (Paste from Clipboard)
                        </button>
                        <span style="font-size: 12px; color: #64748b; align-self: center;">หรือกด Ctrl+V ในช่องด้านล่าง:</span>
                    </div>
                    <textarea id="hwPasteJsonArea" class="form-control" rows="2" placeholder="กด Ctrl+V เพื่อวางข้อมูลสเปก JSON ที่นี่..." style="font-family: Consolas, monospace; font-size: 12px; resize: vertical;"></textarea>
                </div>

                {{-- Action Button --}}
                <div style="text-align: right;">
                    <button type="button" class="btn btn-primary" onclick="applyPastedSpecs()" style="font-weight: 600; padding: 7px 20px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%); border: none;">
                        <i class="bi bi-magic"></i> นำเข้าข้อมูลสเปกและข้อมูลเครื่องทั้งหมด
                    </button>
                </div>
            </div>

        </div>

        {{-- Modal Footer --}}
        <div style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 20px; display: flex; justify-content: flex-end; flex-shrink: 0;">
            <button type="button" class="btn btn-secondary" onclick="closeClientHardwareModal()" style="font-size: 13px; padding: 5px 16px; border-radius: 7px;">
                ปิดหน้าต่าง
            </button>
        </div>

    </div>
</div>

<style>
    .btn-detect-specs {
        background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);
        color: #ffffff;
        border: none;
        padding: 5px 13px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 8px rgba(13, 148, 136, 0.25);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .btn-detect-specs:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(13, 148, 136, 0.35);
        color: #ffffff;
    }
    .hw-spec-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .hw-spec-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        flex-shrink: 0;
    }
    .hw-spec-content {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
    }
    .hw-spec-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 500;
    }
    .hw-spec-val {
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin: 1px 0;
    }
    .hw-spec-sub {
        font-size: 11px;
        color: #0f766e;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<script>
    function setCpu(val) {
        const input = document.getElementById('cpu_model');
        if (input) {
            input.value = val;
            updateLiveSpecsPreview();
        }
    }

    function updateLiveSpecsPreview() {
        const cpu = document.getElementById('cpu_model')?.value || '';
        const ramCap = document.getElementById('ram_capacity')?.value || '';
        const ramType = document.getElementById('ram_type')?.value || '';
        const stType = document.getElementById('storage_type')?.value || '';
        const stCap = document.getElementById('storage_capacity')?.value || '';
        const os = document.getElementById('os_name')?.value || '';

        const parts = [];
        if (cpu) parts.push(cpu);
        if (ramCap) parts.push('RAM ' + ramCap + ' GB' + (ramType ? ' ' + ramType : ''));
        if (stCap || stType) parts.push(((stType ? stType + ' ' : '') + (stCap || '')).trim());
        if (os) parts.push(os);

        const previewElem = document.getElementById('liveSpecsPreviewText');
        if (previewElem) {
            previewElem.textContent = parts.length > 0 ? parts.join(' • ') : 'ยังไม่ได้ระบุสเปก';
        }
    }

    // Modal Controls
    function openClientHardwareModal() {
        const modal = document.getElementById('clientHardwareModal');
        if (modal) {
            modal.style.display = 'flex';
            detectClientHardwareInstant();
        }
    }

    function closeClientHardwareModal() {
        const modal = document.getElementById('clientHardwareModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function switchHwTab(tab) {
        const tabWeb = document.getElementById('tabContentWebDetect');
        const tabDeep = document.getElementById('tabContentDeepScan');
        const btnWeb = document.getElementById('tabBtnWebDetect');
        const btnDeep = document.getElementById('tabBtnDeepScan');

        if (tab === 'web') {
            if (tabWeb) tabWeb.style.display = 'block';
            if (tabDeep) tabDeep.style.display = 'none';
            if (btnWeb) {
                btnWeb.style.borderBottomColor = '#0d9488';
                btnWeb.style.color = '#0f766e';
            }
            if (btnDeep) {
                btnDeep.style.borderBottomColor = 'transparent';
                btnDeep.style.color = '#64748b';
            }
        } else {
            if (tabWeb) tabWeb.style.display = 'none';
            if (tabDeep) tabDeep.style.display = 'block';
            if (btnWeb) {
                btnWeb.style.borderBottomColor = 'transparent';
                btnWeb.style.color = '#64748b';
            }
            if (btnDeep) {
                btnDeep.style.borderBottomColor = '#0d9488';
                btnDeep.style.color = '#0f766e';
            }
        }
    }

    // Detection Engine
    window._detectedClientHardware = null;

    function detectClientHardwareInstant(forceRefresh) {
        if (window._detectedClientHardware && !forceRefresh) {
            updateDetectedUiCards();
            return;
        }

        // 1. GPU Detection via WebGL
        let gpu = 'Onboard / Integrated';
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                const ext = gl.getExtension('WEBGL_debug_renderer_info');
                if (ext) {
                    const raw = gl.getParameter(ext.UNMASKED_RENDERER_WEBGL) || '';
                    gpu = raw.replace(/^ANGLE\s*\([^,]+,\s*/i, '')
                             .replace(/Direct3D.*$/i, '')
                             .replace(/\s*\([^)]*\)/g, '')
                             .replace(/,\s*D3D.*$/i, '')
                             .replace(/\s{2,}/g, ' ')
                             .trim() || 'Onboard / Integrated';
                }
            }
        } catch (e) {}

        // 2. OS Detection
        const ua = navigator.userAgent || '';
        let osName = 'Windows 11 Pro';
        if (/Windows NT 10\.0/i.test(ua)) {
            osName = 'Windows 11 Pro'; // Default modern hospital PC
        } else if (/Windows NT 6\.1/i.test(ua)) {
            osName = 'Windows 7 Pro';
        } else if (/Macintosh|Mac OS/i.test(ua)) {
            osName = 'macOS';
        } else if (/Linux|Ubuntu/i.test(ua)) {
            osName = 'Ubuntu Linux';
        }

        // 3. CPU Cores & Suggestion
        const cores = navigator.hardwareConcurrency || 8;
        let cpuSuggest = 'Intel Core i5-12500';
        let cpuSpeed = '3.00 GHz';

        if (/770/i.test(gpu)) {
            if (cores >= 16) {
                cpuSuggest = 'Intel Core i7-13700';
                cpuSpeed = '3.40 GHz';
            } else {
                cpuSuggest = 'Intel Core i5-12500';
                cpuSpeed = '3.00 GHz';
            }
        } else if (/730/i.test(gpu)) {
            if (cores <= 8) {
                cpuSuggest = 'Intel Core i3-12100';
                cpuSpeed = '3.30 GHz';
            } else {
                cpuSuggest = 'Intel Core i5-12400';
                cpuSpeed = '2.50 GHz';
            }
        } else if (/630/i.test(gpu)) {
            if (cores <= 8) {
                cpuSuggest = 'Intel Core i3-10100';
                cpuSpeed = '3.60 GHz';
            } else {
                cpuSuggest = 'Intel Core i5-10400';
                cpuSpeed = '2.90 GHz';
            }
        } else if (/Iris Xe/i.test(gpu)) {
            cpuSuggest = 'Intel Core i5-1335U';
            cpuSpeed = '2.40 GHz';
        } else if (/GeForce|GTX|RTX/i.test(gpu)) {
            if (cores >= 16) {
                cpuSuggest = 'Intel Core i7-13700';
                cpuSpeed = '3.40 GHz';
            } else if (cores >= 8) {
                cpuSuggest = 'Intel Core i5-12500';
                cpuSpeed = '3.00 GHz';
            } else {
                cpuSuggest = 'Intel Core i5 Processor';
                cpuSpeed = '3.00 GHz';
            }
        } else if (/Radeon|AMD/i.test(gpu)) {
            cpuSuggest = 'AMD Ryzen 5 5600G';
            cpuSpeed = '3.90 GHz';
        } else if (/Apple/i.test(gpu)) {
            cpuSuggest = 'Apple M2';
            cpuSpeed = '3.49 GHz';
        } else {
            cpuSuggest = cores >= 12 ? 'Intel Core i5-12500' : (cores >= 8 ? 'Intel Core i3-12100' : 'Intel Core Processor');
        }

        // 4. Memory (RAM)
        const devMem = navigator.deviceMemory || 8;
        const ramOptions = [4, 8, 16, 32, 64];
        let ramCapacity = 8;
        let minDiff = 999;
        ramOptions.forEach(r => {
            const diff = Math.abs(r - devMem);
            if (diff < minDiff) { minDiff = diff; ramCapacity = r; }
        });

        const isDdr5Era = /13[0-9]{3}|14[0-9]{3}|RTX\s*40|M2|M3/i.test(cpuSuggest + ' ' + gpu) || ramCapacity >= 32;
        const ramType = isDdr5Era ? 'DDR5' : 'DDR4';
        const ramBus = isDdr5Era ? '4800 MHz' : '3200 MHz';
        const ramSlots = ramCapacity >= 16 ? '2 แถว (' + (ramCapacity / 2) + 'GB x 2 Dual-Channel)' : '1 แถว (' + ramCapacity + 'GB x 1)';

        // 5. Screen / Monitor
        const w = window.screen.width;
        const h = window.screen.height;
        let monitorSize = '23.8 นิ้ว IPS FHD';
        if (w >= 2560) monitorSize = '27 นิ้ว 2K IPS (2560x1440)';
        else if (w >= 1920) monitorSize = '23.8 นิ้ว IPS FHD (1920x1080)';
        else if (w >= 1600) monitorSize = '21.5 นิ้ว FHD (1920x1080)';
        else if (w <= 1440) monitorSize = '14 นิ้ว FHD (1920x1080)';

        // 6. Device Type
        let deviceTypeCode = 'PC';
        let deviceTypeName = 'คอมพิวเตอร์ประมวลผลทั่วไป (PC)';

        window._detectedClientHardware = {
            os_name: osName,
            os_license: 'OEM (ติดเครื่อง/BIOS)',
            gpu_model: gpu,
            cpu_model: cpuSuggest,
            cpu_speed: cpuSpeed,
            cpu_cores: cores,
            ram_capacity: ramCapacity,
            ram_type: ramType,
            ram_bus: ramBus,
            ram_slots: ramSlots,
            storage_type: 'SSD NVMe M.2',
            storage_capacity: '512 GB',
            monitor_size: monitorSize,
            device_type_code: deviceTypeCode,
            device_type_name: deviceTypeName,
            screen_res: w + ' x ' + h,
            ip_address: '{{ request()->ip() }}'
        };

        // Battery check for Laptop
        if (navigator.getBattery) {
            navigator.getBattery().then(b => {
                if (b && b.chargingTime !== Infinity) {
                    window._detectedClientHardware.device_type_code = 'NB';
                    window._detectedClientHardware.device_type_name = 'คอมพิวเตอร์พกพา (Notebook)';
                    window._detectedClientHardware.monitor_size = '15.6 นิ้ว FHD';
                    updateDetectedUiCards();
                }
            }).catch(() => {});
        }

        // High-Entropy Client Hints for Windows 10 vs 11
        if (navigator.userAgentData && navigator.userAgentData.getHighEntropyValues) {
            navigator.userAgentData.getHighEntropyValues(['platform', 'platformVersion'])
                .then(hints => {
                    if (hints.platform === 'Windows') {
                        const pv = parseFloat(hints.platformVersion || '0');
                        window._detectedClientHardware.os_name = (pv >= 13) ? 'Windows 11 Pro' : 'Windows 10 Pro';
                        updateDetectedUiCards();
                    }
                }).catch(() => {});
        }

        updateDetectedUiCards();
    }

    function updateDetectedUiCards() {
        const d = window._detectedClientHardware;
        if (!d) return;

        const setTxt = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val;
        };

        setTxt('webDetOs', d.os_name);
        setTxt('webDetGpu', d.gpu_model);
        setTxt('webDetCpu', d.cpu_model);
        setTxt('webDetCpuSub', d.cpu_cores + ' Logical Cores / Threads (' + (d.cpu_speed || '3.0 GHz') + ')');
        setTxt('webDetRam', d.ram_capacity + ' GB');
        setTxt('webDetRamSub', d.ram_type + ' ' + d.ram_bus + ' (' + d.ram_slots + ')');
        setTxt('webDetScreen', d.monitor_size);
        setTxt('webDetScreenSub', 'ความละเอียด ' + d.screen_res);
        setTxt('webDetType', d.device_type_name);
    }

    function setSpecInputValue(id, val) {
        const elem = document.getElementById(id);
        if (elem && val !== undefined && val !== null && val !== '') {
            elem.value = val;
            highlightInput(elem);
        }
    }

    function selectDeviceTypeByCode(code) {
        const devSelect = document.getElementById('device_type_id');
        if (!devSelect) return;
        const opt = Array.from(devSelect.options).find(o => o.getAttribute('data-code') === code);
        if (opt) {
            devSelect.value = opt.value;
            if (typeof handleTypeDropdownChange === 'function') {
                handleTypeDropdownChange(devSelect);
            }
            const pill = document.querySelector('.type-pill-btn[data-type-id="' + opt.value + '"]');
            if (pill) {
                document.querySelectorAll('.type-pill-btn').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
            }
        }
    }

    function highlightInput(elem) {
        if (!elem) return;
        elem.style.transition = 'all 0.3s ease';
        elem.style.backgroundColor = '#ecfdf5';
        elem.style.borderColor = '#10b981';
        elem.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.2)';
        setTimeout(() => {
            elem.style.backgroundColor = '';
            elem.style.borderColor = '';
            elem.style.boxShadow = '';
        }, 2500);
    }

    function showHardwareToast(message) {
        let toast = document.getElementById('hwSuccessToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'hwSuccessToast';
            toast.style.cssText = 'position: fixed; top: 24px; right: 24px; z-index: 9999; background: #0f766e; color: #ffffff; padding: 12px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px; transform: translateY(-20px); opacity: 0; transition: all 0.3s ease; pointer-events: none;';
            document.body.appendChild(toast);
        }
        toast.innerHTML = '<i class="bi bi-check-circle-fill" style="font-size: 18px; color: #a7f3d0;"></i> ' + message;
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
        setTimeout(() => {
            toast.style.transform = 'translateY(-20px)';
            toast.style.opacity = '0';
        }, 3500);
    }

    function applyWebDetectedSpecs() {
        if (!window._detectedClientHardware) {
            detectClientHardwareInstant();
        }
        const d = window._detectedClientHardware;
        if (!d) return;

        setSpecInputValue('cpu_model', d.cpu_model);
        setSpecInputValue('cpu_speed', d.cpu_speed);
        setSpecInputValue('ram_capacity', d.ram_capacity);
        setSpecInputValue('ram_type', d.ram_type);
        setSpecInputValue('ram_bus', d.ram_bus);
        setSpecInputValue('ram_slots', d.ram_slots);
        setSpecInputValue('storage_type', d.storage_type);
        setSpecInputValue('storage_capacity', d.storage_capacity);
        setSpecInputValue('os_name', d.os_name);
        setSpecInputValue('os_license', d.os_license);
        setSpecInputValue('gpu_model', d.gpu_model);
        setSpecInputValue('monitor_size', d.monitor_size);

        if (d.ip_address) {
            const ipInput = document.getElementById('ip_address');
            if (ipInput && !ipInput.value) {
                ipInput.value = d.ip_address;
                highlightInput(ipInput);
            }
        }

        if (d.device_type_code) {
            selectDeviceTypeByCode(d.device_type_code);
        }

        updateLiveSpecsPreview();
        closeClientHardwareModal();
        showHardwareToast('นำข้อมูลสเปกคอมพิวเตอร์ใส่ในแบบฟอร์มเรียบร้อยแล้ว!');
    }

    // PowerShell Deep Audit helpers
    function copyPowerShellCommand() {
        const cmdInput = document.getElementById('cmdPowerShellScan');
        if (cmdInput) {
            cmdInput.select();
            navigator.clipboard.writeText(cmdInput.value).then(() => {
                const btn = document.getElementById('btnCopyPsCmd');
                if (btn) {
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="bi bi-check2"></i> คัดลอกแล้ว!';
                    btn.classList.replace('btn-dark', 'btn-success');
                    setTimeout(() => {
                        btn.innerHTML = orig;
                        btn.classList.replace('btn-success', 'btn-dark');
                    }, 2500);
                }
            });
        }
    }

    function pasteHardwareSpecsFromClipboard() {
        if (navigator.clipboard && navigator.clipboard.readText) {
            navigator.clipboard.readText().then(text => {
                if (text) {
                    const area = document.getElementById('hwPasteJsonArea');
                    if (area) area.value = text;
                    applyDeepAuditJson(text);
                }
            }).catch(() => {
                alert('เบราว์เซอร์ไม่อนุญาตให้อ่านคลิปบอร์ดอัตโนมัติ กรุณากดคลิกในช่องข้อความแล้วกดปุ่ม Ctrl+V เพื่อวางข้อมูล');
                const area = document.getElementById('hwPasteJsonArea');
                if (area) area.focus();
            });
        } else {
            alert('กรุณากดคลิกในช่องข้อความแล้วกด Ctrl+V เพื่อวางข้อมูล');
            const area = document.getElementById('hwPasteJsonArea');
            if (area) area.focus();
        }
    }

    function applyPastedSpecs() {
        const area = document.getElementById('hwPasteJsonArea');
        if (!area || !area.value.trim()) {
            alert('กรุณาวางข้อมูล JSON สเปกที่ได้จากการรันคำสั่งสแกนก่อน');
            return;
        }
        applyDeepAuditJson(area.value.trim());
    }

    function applyDeepAuditJson(jsonStr) {
        let data;
        try {
            data = typeof jsonStr === 'object' ? jsonStr : JSON.parse(jsonStr.trim());
        } catch (e) {
            alert('รูปแบบข้อมูลไม่ถูกต้อง กรุณาคัดลอกข้อมูล JSON จากการรันสคริปต์สแกน');
            return;
        }

        // Specs
        if (data.cpu_model) setSpecInputValue('cpu_model', data.cpu_model);
        if (data.cpu_speed) setSpecInputValue('cpu_speed', data.cpu_speed);
        if (data.ram_capacity) setSpecInputValue('ram_capacity', data.ram_capacity);
        if (data.ram_type) setSpecInputValue('ram_type', data.ram_type);
        if (data.ram_bus) setSpecInputValue('ram_bus', data.ram_bus);
        if (data.ram_slots) setSpecInputValue('ram_slots', data.ram_slots);
        if (data.storage_type) setSpecInputValue('storage_type', data.storage_type);
        if (data.storage_capacity) setSpecInputValue('storage_capacity', data.storage_capacity);
        if (data.os_name) setSpecInputValue('os_name', data.os_name);
        if (data.os_license) setSpecInputValue('os_license', data.os_license);
        if (data.gpu_model) setSpecInputValue('gpu_model', data.gpu_model);
        if (data.monitor_size) setSpecInputValue('monitor_size', data.monitor_size);

        // General Asset fields
        const setVal = (id, val) => {
            const elem = document.getElementById(id);
            if (elem && val) {
                elem.value = val;
                highlightInput(elem);
            }
        };

        if (data.serial_number) setVal('serial_number', data.serial_number);
        if (data.brand) setVal('brand', data.brand);
        if (data.model) setVal('model', data.model);
        if (data.name) {
            const nameInput = document.getElementById('name');
            if (nameInput && (!nameInput.value || nameInput.value.startsWith('คอมพิวเตอร์'))) {
                nameInput.value = data.name;
                highlightInput(nameInput);
            }
        }
        if (data.mac_address) setVal('mac_address', data.mac_address);
        if (data.ip_address) setVal('ip_address', data.ip_address);

        if (data.device_type_code) {
            selectDeviceTypeByCode(data.device_type_code);
        }

        updateLiveSpecsPreview();
        closeClientHardwareModal();
        showHardwareToast('นำเข้าสเปกฮาร์ดแวร์เชิงลึก (พร้อม Serial Number และรุ่น) สำเร็จแล้ว!');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.spec-input');
        inputs.forEach(input => {
            input.addEventListener('input', updateLiveSpecsPreview);
            input.addEventListener('change', updateLiveSpecsPreview);
        });

        // Close modal on click outside or ESC
        const modal = document.getElementById('clientHardwareModal');
        if (modal) {
            window.addEventListener('click', function(e) {
                if (e.target === modal) closeClientHardwareModal();
            });
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'flex') {
                    closeClientHardwareModal();
                }
            });
        }
    });
</script>
