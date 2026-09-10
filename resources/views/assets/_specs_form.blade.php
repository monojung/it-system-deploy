<div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 22px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 15px; color: #0f172a;">
            <i class="bi bi-cpu text-primary" style="font-size: 20px;"></i>
            <span>สเปกฮาร์ดแวร์คอมพิวเตอร์ (Hardware Specifications)</span>
        </div>
        <span style="font-size: 12px; color: #64748b; background: #e2e8f0; padding: 2px 8px; border-radius: 6px;">
            สำหรับ PC, โน้ตบุ๊ก, All-in-One และ Server
        </span>
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

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.spec-input');
        inputs.forEach(input => {
            input.addEventListener('input', updateLiveSpecsPreview);
            input.addEventListener('change', updateLiveSpecsPreview);
        });
    });
</script>
