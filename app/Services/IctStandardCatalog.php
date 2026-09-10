<?php

namespace App\Services;

class IctStandardCatalog
{
    /**
     * Get all official ICT standard items under MDES guidelines
     * (เกณฑ์ราคากลางและคุณลักษณะพื้นฐานการจัดหาอุปกรณ์และระบบคอมพิวเตอร์ กระทรวงดิจิทัลเพื่อเศรษฐกิจและสังคม)
     */
    public static function all(): array
    {
        return [
            // 1. คอมพิวเตอร์ตั้งโต๊ะ (PC)
            [
                'code' => 'PC-PROC-2',
                'name' => 'เครื่องคอมพิวเตอร์ สำหรับงานประมวลผล แบบที่ 2 (จอแสดงผลขนาดไม่น้อยกว่า 21.5 นิ้ว)',
                'short_name' => 'PC สำหรับงานประมวลผล แบบที่ 2',
                'category' => 'คอมพิวเตอร์ตั้งโต๊ะ (PC)',
                'device_type_code' => 'PC',
                'standard_price' => 27000.00,
                'description' => 'หน่วยประมวลผลไม่น้อยกว่า 6 แกนหลัก (6 Cores), RAM ไม่น้อยกว่า 16 GB, SSD M.2 ไม่น้อยกว่า 512 GB, จอ 21.5-24 นิ้ว',
                'is_popular' => true,
                'specs' => [
                    'cpu_model' => 'Intel Core i5-12500',
                    'cpu_speed' => '3.0 GHz Turbo 4.6 GHz',
                    'ram_capacity' => 16,
                    'ram_type' => 'DDR5',
                    'ram_bus' => '4800 MHz',
                    'ram_slots' => '1 แถว (16GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '512 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel UHD Graphics 770',
                    'monitor_size' => '23.8 นิ้ว IPS FHD',
                ],
            ],
            [
                'code' => 'PC-PROC-1',
                'name' => 'เครื่องคอมพิวเตอร์ สำหรับงานประมวลผล แบบที่ 1 (จอแสดงผลขนาดไม่น้อยกว่า 19.5 นิ้ว)',
                'short_name' => 'PC สำหรับงานประมวลผล แบบที่ 1',
                'category' => 'คอมพิวเตอร์ตั้งโต๊ะ (PC)',
                'device_type_code' => 'PC',
                'standard_price' => 22000.00,
                'description' => 'หน่วยประมวลผลไม่น้อยกว่า 6 แกนหลัก, RAM ไม่น้อยกว่า 8 GB, SSD ไม่น้อยกว่า 256 GB, จอไม่น้อยกว่า 19.5 นิ้ว',
                'is_popular' => false,
                'specs' => [
                    'cpu_model' => 'Intel Core i5-12400',
                    'cpu_speed' => '2.5 GHz Turbo 4.4 GHz',
                    'ram_capacity' => 8,
                    'ram_type' => 'DDR4',
                    'ram_bus' => '3200 MHz',
                    'ram_slots' => '1 แถว (8GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '256 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel UHD Graphics 730',
                    'monitor_size' => '21.5 นิ้ว FHD',
                ],
            ],
            [
                'code' => 'PC-OFFICE',
                'name' => 'เครื่องคอมพิวเตอร์ สำหรับงานสำนักงาน (จอแสดงผลขนาดไม่น้อยกว่า 19.5 นิ้ว)',
                'short_name' => 'PC สำหรับงานสำนักงานทั่วไป',
                'category' => 'คอมพิวเตอร์ตั้งโต๊ะ (PC)',
                'device_type_code' => 'PC',
                'standard_price' => 17000.00,
                'description' => 'หน่วยประมวลผลไม่น้อยกว่า 4 แกนหลัก (4 Cores), RAM ไม่น้อยกว่า 8 GB, SSD ไม่น้อยกว่า 256 GB, จอไม่น้อยกว่า 19.5 นิ้ว',
                'is_popular' => true,
                'specs' => [
                    'cpu_model' => 'Intel Core i3-12100',
                    'cpu_speed' => '3.3 GHz Turbo 4.3 GHz',
                    'ram_capacity' => 8,
                    'ram_type' => 'DDR4',
                    'ram_bus' => '3200 MHz',
                    'ram_slots' => '1 แถว (8GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '256 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel UHD Graphics 730',
                    'monitor_size' => '21.5 นิ้ว FHD',
                ],
            ],

            // 2. คอมพิวเตอร์พกพา (Notebook)
            [
                'code' => 'NB-PROC',
                'name' => 'เครื่องคอมพิวเตอร์โน้ตบุ๊ก สำหรับงานประมวลผล',
                'short_name' => 'โน้ตบุ๊ก สำหรับงานประมวลผล',
                'category' => 'คอมพิวเตอร์พกพา (Notebook)',
                'device_type_code' => 'NB',
                'standard_price' => 26000.00,
                'description' => 'หน่วยประมวลผลไม่น้อยกว่า 6 แกนหลัก, RAM ไม่น้อยกว่า 16 GB, SSD ไม่น้อยกว่า 512 GB, จอภาพ 14-15.6 นิ้ว',
                'is_popular' => true,
                'specs' => [
                    'cpu_model' => 'Intel Core i5-1335U',
                    'cpu_speed' => '1.3 GHz Turbo 4.6 GHz',
                    'ram_capacity' => 16,
                    'ram_type' => 'DDR4',
                    'ram_bus' => '3200 MHz',
                    'ram_slots' => '1 แถว (16GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '512 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel Iris Xe Graphics',
                    'monitor_size' => '14 นิ้ว FHD IPS',
                ],
            ],
            [
                'code' => 'NB-OFFICE',
                'name' => 'เครื่องคอมพิวเตอร์โน้ตบุ๊ก สำหรับงานสำนักงาน',
                'short_name' => 'โน้ตบุ๊ก สำหรับงานสำนักงาน',
                'category' => 'คอมพิวเตอร์พกพา (Notebook)',
                'device_type_code' => 'NB',
                'standard_price' => 21000.00,
                'description' => 'หน่วยประมวลผลไม่น้อยกว่า 4 แกนหลัก, RAM ไม่น้อยกว่า 8 GB, SSD ไม่น้อยกว่า 256 GB, จอภาพ 14-15.6 นิ้ว',
                'is_popular' => true,
                'specs' => [
                    'cpu_model' => 'Intel Core i3-1215U',
                    'cpu_speed' => '1.2 GHz Turbo 4.4 GHz',
                    'ram_capacity' => 8,
                    'ram_type' => 'DDR4',
                    'ram_bus' => '3200 MHz',
                    'ram_slots' => '1 แถว (8GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '256 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel UHD Graphics',
                    'monitor_size' => '14 นิ้ว FHD',
                ],
            ],
            [
                'code' => 'TABLET',
                'name' => 'เครื่องคอมพิวเตอร์แท็บเล็ต (Tablet)',
                'short_name' => 'คอมพิวเตอร์แท็บเล็ต',
                'category' => 'คอมพิวเตอร์พกพา (Notebook)',
                'device_type_code' => 'NB',
                'standard_price' => 15000.00,
                'description' => 'หน้าจอขนาดไม่น้อยกว่า 10.2 นิ้ว, หน่วยความจำไม่น้อยกว่า 64 GB, รองรับ Wi-Fi',
                'is_popular' => false,
                'specs' => [
                    'cpu_model' => 'ARM Octa-Core Processor',
                    'cpu_speed' => '2.0 GHz',
                    'ram_capacity' => 4,
                    'ram_type' => 'LPDDR4x',
                    'storage_type' => 'eMMC / UFS',
                    'storage_capacity' => '64 GB',
                    'os_name' => 'iPadOS / Android',
                    'monitor_size' => '10.9 นิ้ว',
                ],
            ],

            // 3. คอมพิวเตอร์ All-in-One (AIO)
            [
                'code' => 'AIO-PROC',
                'name' => 'เครื่องคอมพิวเตอร์ All-in-One สำหรับงานประมวลผล',
                'short_name' => 'All-in-One สำหรับงานประมวลผล',
                'category' => 'คอมพิวเตอร์ All-in-One (AIO)',
                'device_type_code' => 'AIO',
                'standard_price' => 23000.00,
                'description' => 'รวมตัวเครื่องและจอภาพในชุดเดียวกัน ขนาดจอไม่น้อยกว่า 21.5 นิ้ว, CPU ไม่น้อยกว่า 6 Cores, RAM ไม่น้อยกว่า 8 GB, SSD ไม่น้อยกว่า 256 GB',
                'is_popular' => true,
                'specs' => [
                    'cpu_model' => 'Intel Core i5-13400',
                    'cpu_speed' => '2.5 GHz Turbo 4.6 GHz',
                    'ram_capacity' => 8,
                    'ram_type' => 'DDR4',
                    'ram_bus' => '3200 MHz',
                    'ram_slots' => '1 แถว (8GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '512 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel UHD Graphics 730',
                    'monitor_size' => '23.8 นิ้ว FHD IPS',
                ],
            ],
            [
                'code' => 'AIO-OFFICE',
                'name' => 'เครื่องคอมพิวเตอร์ All-in-One สำหรับงานสำนักงาน',
                'short_name' => 'All-in-One สำหรับงานสำนักงาน',
                'category' => 'คอมพิวเตอร์ All-in-One (AIO)',
                'device_type_code' => 'AIO',
                'standard_price' => 18000.00,
                'description' => 'รวมตัวเครื่องและจอภาพ ขนาดไม่น้อยกว่า 21.5 นิ้ว, CPU ไม่น้อยกว่า 4 Cores, RAM 8 GB, SSD 256 GB',
                'is_popular' => false,
                'specs' => [
                    'cpu_model' => 'Intel Core i3-12100',
                    'cpu_speed' => '3.3 GHz Turbo 4.3 GHz',
                    'ram_capacity' => 8,
                    'ram_type' => 'DDR4',
                    'ram_bus' => '3200 MHz',
                    'ram_slots' => '1 แถว (8GB x 1)',
                    'storage_type' => 'SSD NVMe M.2',
                    'storage_capacity' => '256 GB',
                    'os_name' => 'Windows 11 Pro',
                    'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                    'gpu_model' => 'Intel UHD Graphics',
                    'monitor_size' => '21.5 นิ้ว FHD',
                ],
            ],

            // 4. เครื่องแม่ข่าย (Server)
            [
                'code' => 'SRV-TYPE-1',
                'name' => 'เครื่องคอมพิวเตอร์แม่ข่าย แบบที่ 1 (Tower/Rack 1 Socket)',
                'short_name' => 'เครื่องแม่ข่าย Server แบบที่ 1',
                'category' => 'เครื่องแม่ข่าย (Server)',
                'device_type_code' => 'SRV',
                'standard_price' => 75000.00,
                'description' => 'CPU ไม่น้อยกว่า 8 Cores, RAM ECC ไม่น้อยกว่า 16 GB, ฮาร์ดดิสก์แบบ Enterprise SAS/SATA/SSD, แหล่งจ่ายไฟแบบ Redundant',
                'is_popular' => false,
                'specs' => [
                    'cpu_model' => 'Intel Xeon Silver 4410Y',
                    'cpu_speed' => '2.0 GHz Turbo 3.9 GHz',
                    'ram_capacity' => 32,
                    'ram_type' => 'DDR5',
                    'ram_bus' => '4800 MHz',
                    'ram_slots' => '2 แถว (16GB x 2 ECC)',
                    'storage_type' => 'SSD NVMe + HDD',
                    'storage_capacity' => '960 GB Enterprise SSD',
                    'storage_second' => '2 TB SAS 10K Enterprise (RAID 1)',
                    'os_name' => 'Ubuntu Linux Server / Windows Server',
                ],
            ],
            [
                'code' => 'SRV-TYPE-2',
                'name' => 'เครื่องคอมพิวเตอร์แม่ข่าย แบบที่ 2 (Rack 2 Sockets)',
                'short_name' => 'เครื่องแม่ข่าย Server แบบที่ 2',
                'category' => 'เครื่องแม่ข่าย (Server)',
                'device_type_code' => 'SRV',
                'standard_price' => 145000.00,
                'description' => 'รองรับ CPU ไม่น้อยกว่า 2 หน่วย, RAM ECC ไม่น้อยกว่า 32 GB, ระบบ Hot-plug Drive, Redundant Power Supply',
                'is_popular' => false,
                'specs' => [
                    'cpu_model' => 'Dual Intel Xeon Gold 5416S (16C/32T)',
                    'cpu_speed' => '2.0 GHz Turbo 4.0 GHz',
                    'ram_capacity' => 64,
                    'ram_type' => 'DDR5',
                    'ram_bus' => '4800 MHz',
                    'ram_slots' => '2 แถว (32GB x 2 ECC)',
                    'storage_type' => 'SSD NVMe Enterprise',
                    'storage_capacity' => '1.92 TB NVMe SSD (RAID 1)',
                    'storage_second' => '4 TB Enterprise SAS 12G (RAID 5)',
                    'os_name' => 'Ubuntu Linux / Proxmox VE / Windows Server',
                ],
            ],

            // 5. เครื่องพิมพ์ (Printers)
            [
                'code' => 'PRN-LASER-MONO',
                'name' => 'เครื่องพิมพ์เลเซอร์ หรือ LED ขาวดำ (ความเร็วไม่น้อยกว่า 18 หน้า/นาที)',
                'short_name' => 'เครื่องพิมพ์เลเซอร์ ขาวดำ ทั่วไป',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'PRN',
                'standard_price' => 3000.00,
                'description' => 'ความละเอียดไม่น้อยกว่า 600x600 dpi, พิมพ์ขาวดำไม่น้อยกว่า 18 ppm, ถาดกระดาษไม่น้อยกว่า 150 แผ่น',
                'is_popular' => false,
                'specs' => [],
            ],
            [
                'code' => 'PRN-LASER-NET',
                'name' => 'เครื่องพิมพ์เลเซอร์ หรือ LED ขาวดำ ชนิด Network (ความเร็วไม่น้อยกว่า 30 หน้า/นาที)',
                'short_name' => 'เครื่องพิมพ์เลเซอร์ ขาวดำ ชนิด Network',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'PRN',
                'standard_price' => 8500.00,
                'description' => 'พิมพ์ขาวดำไม่น้อยกว่า 30 ppm, ความละเอียด 1200x1200 dpi, มีพอร์ต LAN (10/100/1000) และพิมพ์หน้าหลังอัตโนมัติ (Duplex)',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'PRN-LASER-COLOR',
                'name' => 'เครื่องพิมพ์เลเซอร์ หรือ LED สี ชนิด Network (ความเร็วไม่น้อยกว่า 20 หน้า/นาที)',
                'short_name' => 'เครื่องพิมพ์เลเซอร์ สี ชนิด Network',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'PRN',
                'standard_price' => 11000.00,
                'description' => 'พิมพ์สีและขาวดำไม่น้อยกว่า 20 ppm, ความละเอียด 600x600 dpi, มีพอร์ต LAN Network และรองรับ Duplex',
                'is_popular' => false,
                'specs' => [],
            ],
            [
                'code' => 'PRN-MFP-MONO',
                'name' => 'เครื่องพิมพ์มัลติฟังก์ชันเลเซอร์ หรือ LED ขาวดำ',
                'short_name' => 'เครื่องพิมพ์มัลติฟังก์ชันเลเซอร์ ขาวดำ',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'PRN',
                'standard_price' => 7000.00,
                'description' => 'ฟังก์ชัน Print / Copy / Scan ในเครื่องเดียว, ความเร็วไม่น้อยกว่า 20 ppm, สแกนสีความละเอียด 1200x1200 dpi',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'PRN-MFP-COLOR',
                'name' => 'เครื่องพิมพ์มัลติฟังก์ชันเลเซอร์ หรือ LED สี',
                'short_name' => 'เครื่องพิมพ์มัลติฟังก์ชันเลเซอร์ สี',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'PRN',
                'standard_price' => 15000.00,
                'description' => 'ฟังก์ชัน Print / Copy / Scan สี, ความเร็วไม่น้อยกว่า 18 ppm, เชื่อมต่อผ่าน LAN และ Wi-Fi ได้',
                'is_popular' => false,
                'specs' => [],
            ],
            [
                'code' => 'PRN-INK-TANK',
                'name' => 'เครื่องพิมพ์มัลติฟังก์ชันแบบฉีดหมึกพร้อมติดตั้งถังหมึกแท้ (Ink Tank Multifunction)',
                'short_name' => 'เครื่องพิมพ์มัลติฟังก์ชัน Ink Tank แท้',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'PRN',
                'standard_price' => 5000.00,
                'description' => 'พิมพ์ ขาวดำ/สี, มีระบบถังหมึกแท้จากโรงงาน (Ink Tank), สแกนและถ่ายเอกสารในตัว, รองรับ Wi-Fi',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'PRN-DOT-SHORT',
                'name' => 'เครื่องพิมพ์ด็อทเมตริกซ์ แบบแคร่สั้น (Dot Matrix Printer 24-pin)',
                'short_name' => 'เครื่องพิมพ์หัวเข็ม แบบแคร่สั้น (Dot Matrix)',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'DOT',
                'standard_price' => 9500.00,
                'description' => 'หัวพิมพ์ 24 เข็ม, ความกว้างพิมพ์ 80 คอลัมน์ (แคร่สั้น), รองรับพิมพ์กระดาษต่อเนื่อง 1 ต้นฉบับ + 3 สำเนา สำหรับพิมพ์ใบเสร็จ/การเงิน',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'PRN-DOT-LONG',
                'name' => 'เครื่องพิมพ์ด็อทเมตริกซ์ แบบแคร่ยาว (Dot Matrix Printer 24-pin)',
                'short_name' => 'เครื่องพิมพ์หัวเข็ม แบบแคร่ยาว (Dot Matrix)',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'DOT',
                'standard_price' => 22000.00,
                'description' => 'หัวพิมพ์ 24 เข็ม, ความกว้างพิมพ์ 136 คอลัมน์ (แคร่ยาว), รองรับพิมพ์รายงานระบบบัญชีและการเงิน',
                'is_popular' => false,
                'specs' => [],
            ],
            [
                'code' => 'PRN-LABEL-BARCODE',
                'name' => 'เครื่องพิมพ์บาร์โค้ดและฉลากยา (Barcode / Thermal Label Printer)',
                'short_name' => 'เครื่องพิมพ์สติกเกอร์บาร์โค้ด/ฉลากยา',
                'category' => 'เครื่องพิมพ์ (Printers)',
                'device_type_code' => 'THM',
                'standard_price' => 12000.00,
                'description' => 'ระบบความร้อนโดยตรง (Direct Thermal) หรือ Thermal Transfer ความละเอียดไม่น้อยกว่า 203 dpi สำหรับติดซองยาและหลอดแล็บ',
                'is_popular' => true,
                'specs' => [],
            ],

            // 6. เครื่องสแกนเนอร์ (Scanners)
            [
                'code' => 'SCN-DOC-ADF',
                'name' => 'เครื่องสแกนเนอร์สำหรับงานเอกสาร (Document Scanner แบบ Flatbed/ADF)',
                'short_name' => 'สแกนเนอร์เอกสาร (Flatbed / ADF)',
                'category' => 'เครื่องสแกนเนอร์ (Scanners)',
                'device_type_code' => 'SCN',
                'standard_price' => 16000.00,
                'description' => 'มีถาดป้อนกระดาษอัตโนมัติ (ADF) ไม่น้อยกว่า 50 แผ่น, ความเร็วสแกนหน้าหลังไม่น้อยกว่า 25 ppm / 50 ipm สำหรับงานเวชระเบียน',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'SCN-SMART-CARD',
                'name' => 'เครื่องอ่านบัตรประชาชนอเนกประสงค์ (Smart Card Reader)',
                'short_name' => 'เครื่องอ่านบัตรประชาชน (Smart Card Reader)',
                'category' => 'เครื่องสแกนเนอร์ (Scanners)',
                'device_type_code' => 'SCN',
                'standard_price' => 500.00,
                'description' => 'เชื่อมต่อผ่านพอร์ต USB รองรับมาตรฐาน ISO 7816, PC/SC สำหรับตรวจสอบสิทธิบัตรทองและลงทะเบียนผู้ป่วย OPD',
                'is_popular' => true,
                'specs' => [],
            ],

            // 7. เครื่องสำรองไฟฟ้า (UPS)
            [
                'code' => 'UPS-800VA',
                'name' => 'เครื่องสำรองไฟฟ้า ขนาด 800 VA (UPS)',
                'short_name' => 'UPS ขนาด 800 VA (สำหรับคอมฯ 1 ชุด)',
                'category' => 'เครื่องสำรองไฟฟ้า (UPS)',
                'device_type_code' => 'UPS',
                'standard_price' => 2500.00,
                'description' => 'ขนาดกำลังไฟไม่น้อยกว่า 800 VA / 480 W, ระบบ Line Interactive พร้อมเครื่องปรับแรงดันไฟฟ้าอัตโนมัติ (AVR)',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'UPS-1000VA',
                'name' => 'เครื่องสำรองไฟฟ้า ขนาด 1,000 VA (UPS)',
                'short_name' => 'UPS ขนาด 1,000 VA (สำหรับงานจุดสำคัญ)',
                'category' => 'เครื่องสำรองไฟฟ้า (UPS)',
                'device_type_code' => 'UPS',
                'standard_price' => 6000.00,
                'description' => 'ขนาดกำลังไฟไม่น้อยกว่า 1,000 VA / 600 W, ระบบ True Online หรือ Line Interactive สำรองไฟจุดพยาบาล/ห้องผ่าตัด',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'UPS-2000VA',
                'name' => 'เครื่องสำรองไฟฟ้า ขนาด 2,000 VA (UPS)',
                'short_name' => 'UPS ขนาด 2,000 VA (สำหรับตู้ Rack/Server)',
                'category' => 'เครื่องสำรองไฟฟ้า (UPS)',
                'device_type_code' => 'UPS',
                'standard_price' => 17000.00,
                'description' => 'ขนาดกำลังไฟไม่น้อยกว่า 2,000 VA / 1,600 W หรือแบบ True Online Double Conversion สำหรับห้องแม่ข่ายและตู้ Switch Hub',
                'is_popular' => false,
                'specs' => [],
            ],

            // 8. อุปกรณ์เครือข่าย (Network Equipment)
            [
                'code' => 'NET-AP-WIFI',
                'name' => 'อุปกรณ์กระจายสัญญาณแบบไร้สาย (Wireless Access Point)',
                'short_name' => 'อุปกรณ์กระจายสัญญาณไร้สาย (Access Point)',
                'category' => 'อุปกรณ์เครือข่าย (Network)',
                'device_type_code' => 'NET',
                'standard_price' => 6000.00,
                'description' => 'รองรับมาตรฐาน Wi-Fi 6 (802.11ax), Dual-band 2.4/5 GHz, จ่ายไฟผ่านสาย LAN (PoE), รองรับการจัดการรวมศูนย์',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'NET-SW-24-L2',
                'name' => 'อุปกรณ์สวิตช์เครือข่าย ขนาด 24 ช่อง แบบที่ 1 (Network Switch Layer 2 Gigabit)',
                'short_name' => 'สวิตช์เครือข่าย 24 พอร์ต L2 Gigabit',
                'category' => 'อุปกรณ์เครือข่าย (Network)',
                'device_type_code' => 'NET',
                'standard_price' => 9000.00,
                'description' => 'พอร์ต 10/100/1000 Mbps ไม่น้อยกว่า 24 พอร์ต, พอร์ต SFP ไม่น้อยกว่า 2 พอร์ต, รองรับ VLAN และ QoS',
                'is_popular' => true,
                'specs' => [],
            ],
            [
                'code' => 'NET-SW-24-POE',
                'name' => 'อุปกรณ์สวิตช์เครือข่าย ขนาด 24 ช่อง แบบที่ 2 (Network Switch Layer 2 PoE)',
                'short_name' => 'สวิตช์เครือข่าย 24 พอร์ต L2 PoE (จ่ายไฟ)',
                'category' => 'อุปกรณ์เครือข่าย (Network)',
                'device_type_code' => 'NET',
                'standard_price' => 18000.00,
                'description' => 'พอร์ต 10/100/1000 Mbps 24 พอร์ตพร้อมรองรับจ่ายไฟ PoE (802.3af/at) กำลังไฟรวมไม่น้อยกว่า 370W สำหรับกล้องและ Access Point',
                'is_popular' => false,
                'specs' => [],
            ],

            // 9. จอแสดงผล (Monitors)
            [
                'code' => 'MON-21-5',
                'name' => 'จอแสดงภาพ ขนาดไม่น้อยกว่า 21.5 นิ้ว (Monitor 21.5")',
                'short_name' => 'จอแสดงภาพ 21.5 นิ้ว FHD',
                'category' => 'จอแสดงผล (Monitors)',
                'device_type_code' => 'PC',
                'standard_price' => 3800.00,
                'description' => 'ความละเอียดไม่น้อยกว่า 1920x1080 (FHD), พอร์ตเชื่อมต่อ HDMI และ VGA/DisplayPort',
                'is_popular' => false,
                'specs' => [
                    'monitor_size' => '21.5 นิ้ว FHD',
                ],
            ],
            [
                'code' => 'MON-23-8',
                'name' => 'จอแสดงภาพ ขนาดไม่น้อยกว่า 23.8 นิ้ว (Monitor 23.8" - 24")',
                'short_name' => 'จอแสดงภาพ 23.8 นิ้ว IPS FHD',
                'category' => 'จอแสดงผล (Monitors)',
                'device_type_code' => 'PC',
                'standard_price' => 4500.00,
                'description' => 'ความละเอียดไม่น้อยกว่า 1920x1080 (FHD), พาเนล IPS มุมมองกว้าง ถนอมสายตา, พอร์ต HDMI / DisplayPort',
                'is_popular' => true,
                'specs' => [
                    'monitor_size' => '23.8 นิ้ว IPS FHD',
                ],
            ],
        ];
    }

    /**
     * Get grouped list by category
     */
    public static function grouped(): array
    {
        $grouped = [];
        foreach (self::all() as $item) {
            $cat = $item['category'];
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $item;
        }
        return $grouped;
    }

    /**
     * Get popular quick items
     */
    public static function popular(): array
    {
        return array_filter(self::all(), fn($i) => !empty($i['is_popular']));
    }

    /**
     * Find item by standard code
     */
    public static function findByCode(string $code): ?array
    {
        foreach (self::all() as $item) {
            if (strcasecmp($item['code'], $code) === 0) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Find item by full or partial name
     */
    public static function findByName(string $name): ?array
    {
        $clean = mb_strtolower(trim($name));
        foreach (self::all() as $item) {
            if (mb_stripos($item['name'], $clean) !== false || mb_stripos($clean, mb_strtolower($item['name'])) !== false) {
                return $item;
            }
        }
        return null;
    }
}
