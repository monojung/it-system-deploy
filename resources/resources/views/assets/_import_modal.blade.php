{{-- resources/views/assets/_import_modal.blade.php --}}
@if(auth()->check() && auth()->user()->isAdmin())
<div id="importCsvModal" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-dialog" style="max-width: 680px;">
        {{-- Modal Header --}}
        <div class="modal-hero-header" style="background: linear-gradient(135deg, #0d9488 0%, #0284c7 100%);">
            <div class="modal-hero-left">
                <div class="modal-icon-badge" style="background: rgba(255, 255, 255, 0.25);">
                    <i class="bi bi-file-earmark-arrow-up-fill"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #ffffff;">นำเข้าข้อมูลครุภัณฑ์จากไฟล์ CSV (Import Assets)</h3>
                        <span style="background: rgba(255, 255, 255, 0.25); color: #ffffff; font-size: 11px; padding: 2px 8px; border-radius: 999px; font-weight: 600;">Admin Only</span>
                    </div>
                    <p style="font-size: 12.5px; margin: 3px 0 0 0; color: rgba(255,255,255,0.92);">
                        นำเข้าครุภัณฑ์เป็นชุด พร้อมรองรับสเปกฮาร์ดแวร์ CPU, RAM, Storage, OS และข้อมูลจัดซื้อ
                    </p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeImportModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body-content" style="max-height: 78vh; overflow-y: auto;">
            {{-- Template Download Card --}}
            <div style="background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%); border: 1px solid #99f6e4; border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: flex-start; gap: 12px; flex: 1; min-width: 260px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: #0d9488; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: #0f766e;">
                                ยังไม่มีไฟล์ข้อมูล? ดาวน์โหลดเทมเพลตตัวอย่าง
                            </div>
                            <div style="font-size: 12px; color: #334155; margin-top: 2px; line-height: 1.4;">
                                ไฟล์ <code>.csv</code> มีโครงสร้างพร้อม 4 ตัวอย่างจริง (PC DDR5, Notebook DDR4, All-in-One, Printer) รองรับภาษาไทยและเปิดด้วย Microsoft Excel ได้ทันที (UTF-8 BOM)
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('assets.import.template') }}" class="btn btn-sm btn-primary" style="white-space: nowrap; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; padding: 8px 14px; border-radius: 8px; box-shadow: 0 2px 6px rgba(13, 148, 136, 0.25);" download>
                        <i class="bi bi-download"></i>
                        <span>ดาวน์โหลดเทมเพลต CSV</span>
                    </a>
                </div>
            </div>

            {{-- Import Form --}}
            <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data" id="csvImportForm" onsubmit="handleImportSubmit(event)">
                @csrf

                {{-- Drag & Drop Zone --}}
                <div id="dropZone" style="border: 2px dashed #94a3b8; border-radius: 12px; padding: 28px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s ease; margin-bottom: 16px;"
                     onclick="document.getElementById('csv_file_input').click()"
                     ondragover="handleDragOver(event)"
                     ondragleave="handleDragLeave(event)"
                     ondrop="handleFileDrop(event)">
                    <input type="file" id="csv_file_input" name="csv_file" accept=".csv, text/csv, application/vnd.ms-excel" style="display: none;" onchange="handleFileSelected(event)" required>
                    
                    <div id="dropZonePrompt">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: inline-flex; align-items: center; justify-content: center; font-size: 26px; margin-bottom: 10px;">
                            <i class="bi bi-cloud-arrow-up-fill"></i>
                        </div>
                        <div style="font-size: 14.5px; font-weight: 600; color: #1e293b;">
                            คลิกเพื่อเลือกไฟล์ หรือลากไฟล์ .csv มาวางที่นี่
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                            รองรับไฟล์ CSV ตารางข้อมูลครุภัณฑ์ (ขนาดไฟล์ไม่เกิน 10 MB)
                        </div>
                    </div>

                    {{-- Selected File Info --}}
                    <div id="selectedFileInfo" style="display: none; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px; text-align: left;">
                        <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
                            <i class="bi bi-filetype-csv text-success" style="font-size: 26px; flex-shrink: 0;"></i>
                            <div style="overflow: hidden;">
                                <div id="selectedFileName" style="font-size: 13.5px; font-weight: 600; color: #0f172a; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">-</div>
                                <div id="selectedFileSize" style="font-size: 11.5px; color: #64748b;">-</div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light border text-danger" style="padding: 4px 8px; font-size: 12px;" onclick="clearSelectedFile(event)" title="ยกเลิกไฟล์นี้">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>

                {{-- Live CSV Quick Preview (First 3 rows parsed in browser) --}}
                <div id="csvPreviewBox" style="display: none; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <span style="font-size: 12px; font-weight: 600; color: #475569;">
                            <i class="bi bi-eye-fill text-primary"></i> ตัวอย่างข้อมูลจากไฟล์ (3 แถวแรก):
                        </span>
                        <span id="csvPreviewRowCount" style="font-size: 11px; color: #64748b; background: #e2e8f0; padding: 1px 7px; border-radius: 999px;">0 แถว</span>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow-x: auto; background: #ffffff; max-height: 140px;">
                        <table id="csvPreviewTable" style="width: 100%; border-collapse: collapse; font-size: 11.5px;">
                            <thead style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1; position: sticky; top: 0;">
                                <tr id="csvPreviewTheadRow"></tr>
                            </thead>
                            <tbody id="csvPreviewTbody"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Overwrite Option --}}
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 12px 14px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="overwrite" id="overwrite_checkbox" value="1" style="width: 17px; height: 17px; margin-top: 2px; accent-color: #d97706;">
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #92400e;">
                                เขียนทับ/อัปเดตข้อมูลเดิม (Overwrite) หากพบหมายเลขครุภัณฑ์ซ้ำ
                            </div>
                            <div style="font-size: 11.5px; color: #78350f; margin-top: 2px; line-height: 1.35;">
                                หากไม่ติ๊กเลือก: ระบบจะข้ามแถวที่มีหมายเลขครุภัณฑ์ (Asset Code) ซ้ำกับในฐานข้อมูล และนำเข้าเฉพาะรายการใหม่
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Column Guidelines Accordion --}}
                <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
                    <button type="button" onclick="toggleColumnGuide()" style="width: 100%; padding: 10px 14px; background: #f8fafc; border: none; text-align: left; display: flex; align-items: center; justify-content: space-between; font-size: 12.5px; font-weight: 600; color: #475569; cursor: pointer;">
                        <span><i class="bi bi-info-circle text-primary me-1"></i> ดูข้อกำหนดและชื่อคอลัมน์ที่รองรับ (ทั้งไทยและอังกฤษ)</span>
                        <i id="colGuideChevron" class="bi bi-chevron-down" style="transition: transform 0.2s;"></i>
                    </button>
                    <div id="colGuideBody" style="display: none; padding: 12px 14px; background: #ffffff; border-top: 1px solid #e2e8f0; font-size: 12px; color: #334155; line-height: 1.5;">
                        <p style="margin-bottom: 8px;"><strong>คอลัมน์จำเป็น:</strong></p>
                        <ul style="padding-left: 18px; margin-bottom: 10px;">
                            <li><code>asset_code</code> หรือ <code>รหัสครุภัณฑ์</code> <span class="text-danger">*จำเป็น</span></li>
                            <li><code>name</code> หรือ <code>ชื่อรายการครุภัณฑ์</code> <span class="text-danger">*จำเป็น</span></li>
                            <li><code>device_type</code> หรือ <code>ประเภทอุปกรณ์</code> (เช่น คอมพิวเตอร์ตั้งโต๊ะ, โน้ตบุ๊ก, เครื่องพิมพ์)</li>
                        </ul>
                        <p style="margin-bottom: 8px;"><strong>คอลัมน์สเปกฮาร์ดแวร์คอมพิวเตอร์:</strong></p>
                        <ul style="padding-left: 18px; margin-bottom: 10px;">
                            <li><code>cpu_model</code> หรือ <code>CPU</code> (เช่น Intel Core i5-12500, Ryzen 7)</li>
                            <li><code>ram_capacity</code> หรือ <code>RAM (GB)</code> (ตัวเลข เช่น 8, 16, 32)</li>
                            <li><code>ram_type</code> หรือ <code>ชนิด RAM</code> (DDR4, DDR5)</li>
                            <li><code>storage_type</code> หรือ <code>Storage Type</code> (SSD NVMe M.2, SSD SATA)</li>
                            <li><code>storage_capacity</code> หรือ <code>ความจุ Storage</code> (512 GB, 1 TB)</li>
                            <li><code>os_name</code> หรือ <code>OS</code> (Windows 11 Pro, Windows 10 Pro)</li>
                        </ul>
                        <p style="margin-bottom: 8px;"><strong>คอลัมน์ทั่วไปและสถานที่:</strong></p>
                        <ul style="padding-left: 18px; margin-bottom: 0;">
                            <li><code>department</code> หรือ <code>แผนก</code>, <code>location_detail</code> หรือ <code>จุดที่ตั้ง</code></li>
                            <li><code>custodian_name</code> หรือ <code>ผู้ดูแล</code>, <code>status</code> หรือ <code>สถานะ</code> (active, spare, repairing, broken, disposed)</li>
                            <li><code>price</code> หรือ <code>ราคา</code>, <code>purchase_date</code> หรือ <code>วันที่ซื้อ</code>, <code>warranty_expire_date</code> หรือ <code>วันหมดประกัน</code></li>
                        </ul>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
                    <button type="button" class="btn btn-secondary" onclick="closeImportModal()">
                        ยกเลิก
                    </button>
                    <button type="submit" id="btnSubmitImport" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                        <i class="bi bi-cloud-arrow-up-fill"></i>
                        <span id="btnSubmitImportText">เริ่มนำเข้าข้อมูล</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openImportModal() {
        const modal = document.getElementById('importCsvModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }

    function closeImportModal() {
        const modal = document.getElementById('importCsvModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function toggleColumnGuide() {
        const body = document.getElementById('colGuideBody');
        const chevron = document.getElementById('colGuideChevron');
        if (body.style.display === 'none' || body.style.display === '') {
            body.style.display = 'block';
            chevron.style.transform = 'rotate(180deg)';
        } else {
            body.style.display = 'none';
            chevron.style.transform = 'rotate(0deg)';
        }
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            dropZone.style.borderColor = '#0d9488';
            dropZone.style.background = '#f0fdfa';
        }
    }

    function handleDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            dropZone.style.borderColor = '#94a3b8';
            dropZone.style.background = '#f8fafc';
        }
    }

    function handleFileDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        handleDragLeave(e);
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            const input = document.getElementById('csv_file_input');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            processFile(file);
        }
    }

    function handleFileSelected(e) {
        if (e.target.files && e.target.files.length > 0) {
            processFile(e.target.files[0]);
        }
    }

    function processFile(file) {
        if (!file) return;

        // Display file info
        document.getElementById('dropZonePrompt').style.display = 'none';
        const fileInfo = document.getElementById('selectedFileInfo');
        fileInfo.style.display = 'flex';
        document.getElementById('selectedFileName').textContent = file.name;
        document.getElementById('selectedFileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';

        // Parse first few rows for quick preview
        const reader = new FileReader();
        reader.onload = function(evt) {
            const text = evt.target.result;
            parseAndPreviewCsv(text);
        };
        reader.readAsText(file);
    }

    function clearSelectedFile(e) {
        if (e) {
            e.stopPropagation();
        }
        const input = document.getElementById('csv_file_input');
        if (input) input.value = '';
        document.getElementById('dropZonePrompt').style.display = 'block';
        document.getElementById('selectedFileInfo').style.display = 'none';
        document.getElementById('csvPreviewBox').style.display = 'none';
    }

    function parseAndPreviewCsv(csvText) {
        if (!csvText) return;

        // Split lines
        const lines = csvText.split(/\r\n|\n/).filter(line => line.trim().length > 0);
        if (lines.length === 0) return;

        // Detect delimiter: , or ;
        const firstLine = lines[0];
        const delimiter = (firstLine.includes(';') && !firstLine.includes(',')) ? ';' : ',';

        // Parse headers
        const parseRow = (rowStr) => {
            // Basic CSV parser regex
            const pattern = new RegExp(
                "(\\" + delimiter + "|\\r?\\n|\\r|^)" +
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +
                "([^\"\\" + delimiter + "\\r\\n]*))",
                "gi"
            );
            const matches = [];
            let match;
            while ((match = pattern.exec(rowStr)) !== null) {
                let val = match[2] ? match[2].replace(/""/g, '"') : match[3];
                matches.push(val ? val.trim() : '');
            }
            return matches;
        };

        const headers = parseRow(firstLine);
        const theadRow = document.getElementById('csvPreviewTheadRow');
        theadRow.innerHTML = '';
        headers.slice(0, 6).forEach(h => {
            const th = document.createElement('th');
            th.style.padding = '6px 10px';
            th.style.textAlign = 'left';
            th.style.color = '#334155';
            th.textContent = h;
            theadRow.appendChild(th);
        });

        const tbody = document.getElementById('csvPreviewTbody');
        tbody.innerHTML = '';
        const previewRows = lines.slice(1, 4);
        previewRows.forEach(line => {
            const rowData = parseRow(line);
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid #f1f5f9';
            rowData.slice(0, 6).forEach(cell => {
                const td = document.createElement('td');
                td.style.padding = '5px 10px';
                td.style.color = '#475569';
                td.textContent = cell || '-';
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });

        document.getElementById('csvPreviewRowCount').textContent = (lines.length - 1) + ' แถวข้อมูล';
        document.getElementById('csvPreviewBox').style.display = 'block';
    }

    function handleImportSubmit(e) {
        const btn = document.getElementById('btnSubmitImport');
        const text = document.getElementById('btnSubmitImportText');
        if (btn && text) {
            btn.disabled = true;
            text.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> กำลังนำเข้าข้อมูล...';
        }
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('importCsvModal');
        if (e.target === modal) {
            closeImportModal();
        }
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImportModal();
        }
    });
</script>
@endif
