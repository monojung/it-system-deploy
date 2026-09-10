<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\DeviceType;
use App\Models\User;
use App\Models\Asset;
use App\Models\SparePart;
use App\Models\Repair;
use App\Models\RepairLog;
use App\Models\RepairPart;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departments (แผนกในโรงพยาบาลทุ่งหัวช้าง)
        $departments = [
            ['name' => 'กลุ่มงานสุขภาพดิจิทัล (งานคอมพิวเตอร์และสารสนเทศ)', 'code' => 'IT', 'building' => 'อาคารอำนวยการ ชั้น 2', 'phone' => '101, 102'],
            ['name' => 'แผนกผู้ป่วยนอก (OPD)', 'code' => 'OPD', 'building' => 'อาคารผู้ป่วยนอก ชั้น 1', 'phone' => '111'],
            ['name' => 'กลุ่มงานอุบัติเหตุและฉุกเฉิน (ER)', 'code' => 'ER', 'building' => 'อาคารอุบัติเหตุ ชั้น 1', 'phone' => '1669, 109'],
            ['name' => 'กลุ่มงานเภสัชกรรมและคุ้มครองผู้บริโภค', 'code' => 'PHAR', 'building' => 'อาคารผู้ป่วยนอก ชั้น 1', 'phone' => '115'],
            ['name' => 'กลุ่มงานเทคนิคการแพทย์และชันสูตร (LAB)', 'code' => 'LAB', 'building' => 'อาคารผู้ป่วยนอก ชั้น 1', 'phone' => '118'],
            ['name' => 'กลุ่มงานรังสีวิทยา (X-Ray)', 'code' => 'XRAY', 'building' => 'อาคารผู้ป่วยนอก ชั้น 1', 'phone' => '119'],
            ['name' => 'หอผู้ป่วยใน (IPD)', 'code' => 'IPD', 'building' => 'อาคารผู้ป่วยใน ชั้น 2', 'phone' => '121'],
            ['name' => 'งานการพยาบาลผู้คลอดและผ่าตัด (LR/OR)', 'code' => 'LR', 'building' => 'อาคารผู้ป่วยใน ชั้น 1', 'phone' => '125'],
            ['name' => 'กลุ่มงานทันตกรรม', 'code' => 'DENT', 'building' => 'อาคารส่งเสริม ชั้น 2', 'phone' => '130'],
            ['name' => 'กลุ่มงานกายภาพบำบัด', 'code' => 'PT', 'building' => 'อาคารฟื้นฟู ชั้น 1', 'phone' => '133'],
            ['name' => 'กลุ่มงานบริหารทั่วไป (ธุรการ/สารบรรณ)', 'code' => 'ADM', 'building' => 'อาคารอำนวยการ ชั้น 1', 'phone' => '100'],
            ['name' => 'งานการเงินและบัญชี', 'code' => 'FIN', 'building' => 'อาคารอำนวยการ ชั้น 1', 'phone' => '105'],
            ['name' => 'กลุ่มงานประกันสุขภาพและเวชระเบียน', 'code' => 'INS', 'building' => 'อาคารผู้ป่วยนอก ชั้น 1', 'phone' => '110'],
        ];

        $deptMap = [];
        foreach ($departments as $dept) {
            $created = Department::firstOrCreate(['name' => $dept['name']], $dept);
            $deptMap[$dept['code']] = $created->id;
        }

        // 2. Users (ผู้ใช้งานระบบ)
        $users = [
            [
                'name' => 'ผู้ดูแลระบบ สารสนเทศ',
                'username' => 'admin',
                'email' => 'admin@thchospital.go.th',
                'password' => Hash::make('admin1234'),
                'role' => 'admin',
                'cid' => '1509900950465',
                'department_id' => $deptMap['IT'],
                'position' => 'นักวิชาการคอมพิวเตอร์ปฏิบัติการ',
                'phone' => '081-2345678 (ต่อ 101)',
            ],
            [
                'name' => 'นายช่าง IT ประจำการ',
                'username' => 'technician',
                'email' => 'tech@thchospital.go.th',
                'password' => Hash::make('tech1234'),
                'role' => 'technician',
                'department_id' => $deptMap['IT'],
                'position' => 'นายช่างเทคนิคคอมพิวเตอร์',
                'phone' => '089-8765432 (ต่อ 102)',
            ],
            [
                'name' => 'นายช่าง ช้างดิจิทัล',
                'username' => 'chang',
                'email' => 'chang@thchospital.go.th',
                'password' => Hash::make('chang11143'),
                'role' => 'technician',
                'cid' => '3959900316670',
                'department_id' => $deptMap['IT'],
                'position' => 'เจ้าหน้าที่ระบบเครือข่ายและสารสนเทศ',
                'phone' => '082-3456789 (ต่อ 102)',
            ],
            [
                'name' => 'พว.สุดาพร ใจดี (พยาบาล OPD)',
                'username' => 'user_opd',
                'email' => 'suda@thchospital.go.th',
                'password' => Hash::make('user1234'),
                'role' => 'user',
                'department_id' => $deptMap['OPD'],
                'position' => 'พยาบาลวิชาชีพชำนาญการ',
                'phone' => 'ต่อ 111',
            ],
            [
                'name' => 'นายสมชาย มุ่งมั่น (ER)',
                'username' => 'user_er',
                'email' => 'er_staff@thchospital.go.th',
                'password' => Hash::make('user1234'),
                'role' => 'user',
                'department_id' => $deptMap['ER'],
                'position' => 'เจ้าพนักงานเวชสถิติ',
                'phone' => 'ต่อ 109',
            ],
            [
                'name' => 'ภญ.พิมพ์ใจ รักยา (เภสัชกรรม)',
                'username' => 'user_phar',
                'email' => 'phar@thchospital.go.th',
                'password' => Hash::make('user1234'),
                'role' => 'user',
                'department_id' => $deptMap['PHAR'],
                'position' => 'เภสัชกรชำนาญการ',
                'phone' => 'ต่อ 115',
            ],
        ];

        foreach ($users as $userData) {
            $userData['email_verified_at'] = now();
            $userData['is_active'] = true;
            $user = User::updateOrCreate(['username' => $userData['username']], $userData);
            $userMap[$userData['username']] = $user->id;
        }

        // 3. Device Types (ประเภทอุปกรณ์)
        $deviceTypes = [
            ['name' => 'คอมพิวเตอร์ตั้งโต๊ะ (PC)', 'code' => 'PC', 'icon' => 'bi-pc-display', 'description' => 'คอมพิวเตอร์สำหรับงานประมวลผลทั่วไปและระบบ HosxP'],
            ['name' => 'คอมพิวเตอร์ All-in-One (AIO)', 'code' => 'AIO', 'icon' => 'bi-display', 'description' => 'เครื่องคอมพิวเตอร์รวมจอภาพ ประหยัดพื้นที่'],
            ['name' => 'คอมพิวเตอร์พกพา (Notebook)', 'code' => 'NB', 'icon' => 'bi-laptop', 'description' => 'โน้ตบุ๊กสำหรับงานเคลื่อนที่และแพทย์'],
            ['name' => 'เครื่องพิมพ์เลเซอร์/อิงค์เจ็ท (Laser/Inkjet)', 'code' => 'PRN', 'icon' => 'bi-printer', 'description' => 'เครื่องพิมพ์เอกสารทางราชการและรายงานผล'],
            ['name' => 'เครื่องพิมพ์สติกเกอร์ยา/ความร้อน (Thermal/Label)', 'code' => 'THM', 'icon' => 'bi-upc-scan', 'description' => 'เครื่องพิมพ์สติกเกอร์บาร์โค้ดติดซองยาและหลอดเลือด'],
            ['name' => 'เครื่องพิมพ์หัวเข็ม (Dot Matrix)', 'code' => 'DOT', 'icon' => 'bi-printer-fill', 'description' => 'เครื่องพิมพ์ใบเสร็จรับเงิน กระดาษต่อเนื่อง'],
            ['name' => 'เครื่องสำรองไฟฟ้า (UPS)', 'code' => 'UPS', 'icon' => 'bi-battery-charging', 'description' => 'เครื่องสำรองไฟฟ้าป้องกันคอมพิวเตอร์ดับกะทันหัน'],
            ['name' => 'อุปกรณ์กระจายสัญญาณเครือข่าย (Network Switch/AP)', 'code' => 'NET', 'icon' => 'bi-router', 'description' => 'Switch Hub, Access Point Wi-Fi, Router'],
            ['name' => 'เครื่องอ่านบัตรประชาชน/สแกนเนอร์ (Card Reader/Scanner)', 'code' => 'SCN', 'icon' => 'bi-credit-card-2-front', 'description' => 'Smart Card Reader และ Scanner เอกสาร'],
            ['name' => 'เครื่องแม่ข่าย (Server)', 'code' => 'SRV', 'icon' => 'bi-hdd-rack', 'description' => 'เซิร์ฟเวอร์ฐานข้อมูลและระบบสารสนเทศส่วนกลาง'],
        ];

        $typeMap = [];
        foreach ($deviceTypes as $type) {
            $dt = DeviceType::firstOrCreate(['code' => $type['code']], $type);
            $typeMap[$type['code']] = $dt->id;
        }

        // 4. Spare Parts (คลังอะไหล่และอุปกรณ์สิ้นเปลือง)
        $spareParts = [
            ['part_code' => 'SP-001', 'name' => 'ตลับหมึก Brother TN-B022 (สำหรับ HL-B2080DW)', 'category' => 'หมึกพิมพ์', 'unit' => 'ตลับ', 'stock_quantity' => 12, 'minimum_quantity' => 3, 'unit_price' => 590.00, 'location' => 'ตู้ A1 ชั้น 2'],
            ['part_code' => 'SP-002', 'name' => 'ตลับหมึก HP LaserJet 85A (CE285A)', 'category' => 'หมึกพิมพ์', 'unit' => 'ตลับ', 'stock_quantity' => 8, 'minimum_quantity' => 2, 'unit_price' => 750.00, 'location' => 'ตู้ A1 ชั้น 2'],
            ['part_code' => 'SP-003', 'name' => 'ผ้าหมึกพิมพ์ Epson LQ-310 Ribbon Cartridge', 'category' => 'หมึกพิมพ์', 'unit' => 'ตลับ', 'stock_quantity' => 15, 'minimum_quantity' => 4, 'unit_price' => 190.00, 'location' => 'ตู้ A2 ชั้น 2'],
            ['part_code' => 'SP-004', 'name' => 'กระดาษสติกเกอร์ความร้อน พิมพ์ฉลากยา 8x5 ซม.', 'category' => 'กระดาษ/สติกเกอร์', 'unit' => 'ม้วน', 'stock_quantity' => 45, 'minimum_quantity' => 10, 'unit_price' => 120.00, 'location' => 'ตู้ A3 ชั้น 2'],
            ['part_code' => 'SP-005', 'name' => 'SSD M.2 NVMe 500GB Kingston NV2', 'category' => 'อะไหล่คอมพิวเตอร์', 'unit' => 'ตัว', 'stock_quantity' => 6, 'minimum_quantity' => 2, 'unit_price' => 1390.00, 'location' => 'ตู้ B1 ชั้น 2'],
            ['part_code' => 'SP-006', 'name' => 'RAM DDR4 8GB 3200MHz Kingston Fury', 'category' => 'อะไหล่คอมพิวเตอร์', 'unit' => 'แผง', 'stock_quantity' => 5, 'minimum_quantity' => 2, 'unit_price' => 790.00, 'location' => 'ตู้ B1 ชั้น 2'],
            ['part_code' => 'SP-007', 'name' => 'เมาส์ USB Optical Logitech B100', 'category' => 'อุปกรณ์ต่อพ่วง', 'unit' => 'ตัว', 'stock_quantity' => 14, 'minimum_quantity' => 5, 'unit_price' => 160.00, 'location' => 'ตู้ B2 ชั้น 2'],
            ['part_code' => 'SP-008', 'name' => 'คีย์บอร์ด USB Logitech K120', 'category' => 'อุปกรณ์ต่อพ่วง', 'unit' => 'ตัว', 'stock_quantity' => 10, 'minimum_quantity' => 4, 'unit_price' => 280.00, 'location' => 'ตู้ B2 ชั้น 2'],
            ['part_code' => 'SP-009', 'name' => 'สายแลน CAT6 UTP Patch Cord 3 เมตร', 'category' => 'อุปกรณ์เครือข่าย', 'unit' => 'เส้น', 'stock_quantity' => 25, 'minimum_quantity' => 5, 'unit_price' => 65.00, 'location' => 'ตู้ C1 ชั้น 2'],
            ['part_code' => 'SP-010', 'name' => 'เครื่องอ่านบัตรสมาร์ทการ์ด USB Smart Card Reader', 'category' => 'อุปกรณ์ต่อพ่วง', 'unit' => 'เครื่อง', 'stock_quantity' => 4, 'minimum_quantity' => 2, 'unit_price' => 290.00, 'location' => 'ตู้ B3 ชั้น 2'],
            ['part_code' => 'SP-011', 'name' => 'Power Supply 550W DTECH / Delux', 'category' => 'อะไหล่คอมพิวเตอร์', 'unit' => 'ตัว', 'stock_quantity' => 3, 'minimum_quantity' => 2, 'unit_price' => 590.00, 'location' => 'ตู้ B4 ชั้น 2'],
            ['part_code' => 'SP-012', 'name' => 'แบตเตอรี่เครื่องสำรองไฟ 12V 7.2Ah (สำหรับ UPS)', 'category' => 'อะไหล่สำรองไฟ', 'unit' => 'ลูก', 'stock_quantity' => 7, 'minimum_quantity' => 2, 'unit_price' => 480.00, 'location' => 'ตู้ D1 ชั้น 1'],
        ];

        $spareMap = [];
        foreach ($spareParts as $part) {
            $createdPart = SparePart::firstOrCreate(['part_code' => $part['part_code']], $part);
            $spareMap[$part['part_code']] = $createdPart->id;
        }

        // 5. Assets (ครุภัณฑ์คอมพิวเตอร์)
        $assets = [
            [
                'asset_code' => '7440-001-0001/66',
                'serial_number' => 'DL-OPT-7010-01',
                'name' => 'คอมพิวเตอร์ประมวลผล จุดคัดกรอง OPD',
                'device_type_id' => $typeMap['PC'],
                'brand' => 'Dell',
                'model' => 'OptiPlex 7010',
                'specs' => 'Intel Core i5-12500, RAM 16GB, SSD 512GB NVMe, Windows 11 Pro',
                'ip_address' => '192.168.2.51',
                'mac_address' => '00:1A:2B:3C:4D:5E',
                'department_id' => $deptMap['OPD'],
                'location_detail' => 'โต๊ะคัดกรองพยาบาล 1',
                'custodian_name' => 'พว.สุดาพร ใจดี',
                'status' => 'active',
                'purchase_date' => '2023-11-15',
                'price' => 24500.00,
                'warranty_expire_date' => '2026-11-15',
                'budget_year' => '2567',
                'notes' => 'ใช้งานระบบบันทึกสัญญาณชีพ HosxP',
            ],
            [
                'asset_code' => '7440-001-0002/66',
                'serial_number' => 'DL-OPT-7010-02',
                'name' => 'คอมพิวเตอร์แพทย์ ห้องตรวจ 1',
                'device_type_id' => $typeMap['PC'],
                'brand' => 'Dell',
                'model' => 'OptiPlex 7010',
                'specs' => 'Intel Core i5-12500, RAM 16GB, SSD 512GB NVMe, Windows 11 Pro',
                'ip_address' => '192.168.2.52',
                'mac_address' => '00:1A:2B:3C:4D:5F',
                'department_id' => $deptMap['OPD'],
                'location_detail' => 'ห้องตรวจโรค 1',
                'custodian_name' => 'นพ.ประจำห้องตรวจ 1',
                'status' => 'active',
                'purchase_date' => '2023-11-15',
                'price' => 24500.00,
                'warranty_expire_date' => '2026-11-15',
                'budget_year' => '2567',
                'notes' => 'เครื่องประจำห้องตรวจแพทย์',
            ],
            [
                'asset_code' => '7440-002-0001/67',
                'serial_number' => 'BR-HL-B2080-01',
                'name' => 'เครื่องพิมพ์เลเซอร์ แผนกผู้ป่วยนอก',
                'device_type_id' => $typeMap['PRN'],
                'brand' => 'Brother',
                'model' => 'HL-B2080DW',
                'specs' => 'Laser Duplex Network 34 ppm',
                'ip_address' => '192.168.2.150',
                'mac_address' => '00:80:77:AA:BB:CC',
                'department_id' => $deptMap['OPD'],
                'location_detail' => 'เคาน์เตอร์พยาบาล OPD',
                'custodian_name' => 'พว.สุดาพร ใจดี',
                'status' => 'active',
                'purchase_date' => '2024-02-10',
                'price' => 5900.00,
                'warranty_expire_date' => '2027-02-10',
                'budget_year' => '2567',
                'notes' => 'พิมพ์ใบสั่งยาและใบรับรองแพทย์',
            ],
            [
                'asset_code' => '7440-001-0005/65',
                'serial_number' => 'HP-PD-400-01',
                'name' => 'คอมพิวเตอร์ประจำจุดรับผู้ป่วย ER',
                'device_type_id' => $typeMap['PC'],
                'brand' => 'HP',
                'model' => 'ProDesk 400 G6',
                'specs' => 'Intel Core i3-10100, RAM 8GB, SSD 256GB, Windows 10 Pro',
                'ip_address' => '192.168.2.60',
                'mac_address' => '10:7B:44:11:22:33',
                'department_id' => $deptMap['ER'],
                'location_detail' => 'เคาน์เตอร์เวชระเบียน ER',
                'custodian_name' => 'นายสมชาย มุ่งมั่น',
                'status' => 'repairing',
                'purchase_date' => '2022-08-20',
                'price' => 21000.00,
                'warranty_expire_date' => '2025-08-20',
                'budget_year' => '2565',
                'notes' => 'เปิดเครื่องไม่ติด อยู่ระหว่างตรวจสอบ Power Supply',
            ],
            [
                'asset_code' => '7440-004-0001/67',
                'serial_number' => 'XPR-XP420B-01',
                'name' => 'เครื่องพิมพ์สติกเกอร์ยา กลุ่มงานเภสัชกรรม',
                'device_type_id' => $typeMap['THM'],
                'brand' => 'Xprinter',
                'model' => 'XP-420B Thermal Label',
                'specs' => 'Thermal Direct Barcode Printer 152mm/s',
                'ip_address' => null,
                'mac_address' => null,
                'department_id' => $deptMap['PHAR'],
                'location_detail' => 'ห้องจ่ายยา ช่องบริการ 2',
                'custodian_name' => 'ภญ.พิมพ์ใจ รักยา',
                'status' => 'active',
                'purchase_date' => '2024-01-15',
                'price' => 3800.00,
                'warranty_expire_date' => '2025-01-15',
                'budget_year' => '2567',
                'notes' => 'พิมพ์ฉลากยารวดเร็ว',
            ],
            [
                'asset_code' => '7440-005-0001/66',
                'serial_number' => 'APC-BX1100-01',
                'name' => 'เครื่องสำรองไฟฟ้า ห้องปฏิบัติการ LAB',
                'device_type_id' => $typeMap['UPS'],
                'brand' => 'APC',
                'model' => 'Back-UPS 1100VA / 550W',
                'specs' => '1100VA, AVR, Universal sockets',
                'ip_address' => null,
                'mac_address' => null,
                'department_id' => $deptMap['LAB'],
                'location_detail' => 'โต๊ะเครื่องตรวจวิเคราะห์เลือด CBC',
                'custodian_name' => 'ทนพ.ธนากร วิเคราะห์ดี',
                'status' => 'active',
                'purchase_date' => '2023-05-12',
                'price' => 4500.00,
                'warranty_expire_date' => '2025-05-12',
                'budget_year' => '2566',
                'notes' => 'สำรองไฟให้เครื่องตรวจแล็บ',
            ],
            [
                'asset_code' => '7440-001-0008/64',
                'serial_number' => 'ACR-VER-4640',
                'name' => 'คอมพิวเตอร์ธุรการ ชั้น 1',
                'device_type_id' => $typeMap['PC'],
                'brand' => 'Acer',
                'model' => 'Veriton M4640G',
                'specs' => 'Core i5-6500, RAM 8GB, HDD 1TB, Windows 10',
                'ip_address' => '192.168.2.80',
                'mac_address' => '00:1E:67:88:99:00',
                'department_id' => $deptMap['ADM'],
                'location_detail' => 'ห้องบริหารทั่วไป',
                'custodian_name' => 'นางวรรณา รวดเร็ว',
                'status' => 'broken',
                'purchase_date' => '2021-03-01',
                'price' => 18900.00,
                'warranty_expire_date' => '2024-03-01',
                'budget_year' => '2564',
                'notes' => 'ฮาร์ดดิสก์มี Bad Sector ทำงานช้ามาก รอเปลี่ยน SSD',
            ],
            [
                'asset_code' => '7440-006-0001/67',
                'serial_number' => 'LN-TP-E14-01',
                'name' => 'โน้ตบุ๊กงานระบบสารสนเทศ',
                'device_type_id' => $typeMap['NB'],
                'brand' => 'Lenovo',
                'model' => 'ThinkPad E14 Gen 5',
                'specs' => 'AMD Ryzen 7 7730U, RAM 16GB, SSD 512GB, 14 Inch FHD',
                'ip_address' => '192.168.2.95',
                'mac_address' => 'C4:00:AD:12:34:56',
                'department_id' => $deptMap['IT'],
                'location_detail' => 'ห้องศูนย์คอมพิวเตอร์',
                'custodian_name' => 'นายช่าง IT ประจำการ',
                'status' => 'active',
                'purchase_date' => '2024-03-20',
                'price' => 28900.00,
                'warranty_expire_date' => '2027-03-20',
                'budget_year' => '2567',
                'notes' => 'เครื่องประจำกลุ่มงานสุขภาพดิจิทัล',
            ],
            [
                'asset_code' => '7440-007-0001/66',
                'serial_number' => 'CS-C2960X-24TS',
                'name' => 'Network Switch กลาง อาคารผู้ป่วยนอก',
                'device_type_id' => $typeMap['NET'],
                'brand' => 'Cisco',
                'model' => 'Catalyst 2960-X 24 GigE',
                'specs' => '24 Port Gigabit Switch + 4 SFP, Managed',
                'ip_address' => '192.168.2.2',
                'mac_address' => '00:27:0D:33:44:55',
                'department_id' => $deptMap['IT'],
                'location_detail' => 'ตู้ Rack อาคารผู้ป่วยนอก ชั้น 1',
                'custodian_name' => 'กลุ่มงานสุขภาพดิจิทัล',
                'status' => 'active',
                'purchase_date' => '2023-01-10',
                'price' => 42000.00,
                'warranty_expire_date' => '2026-01-10',
                'budget_year' => '2566',
                'notes' => 'สวิตช์หลักเชื่อมโยง OPD, ER, LAB, PHAR',
            ],
            [
                'asset_code' => '7440-008-0001/66',
                'serial_number' => 'EPS-LQ310-01',
                'name' => 'เครื่องพิมพ์ใบเสร็จรับเงิน งานการเงิน',
                'device_type_id' => $typeMap['DOT'],
                'brand' => 'Epson',
                'model' => 'LQ-310 Dot Matrix',
                'specs' => '24 Pin Dot Matrix 416 cps',
                'ip_address' => null,
                'mac_address' => null,
                'department_id' => $deptMap['FIN'],
                'location_detail' => 'เคาน์เตอร์การเงิน ช่อง 1',
                'custodian_name' => 'น.ส.ดวงพร การเงิน',
                'status' => 'active',
                'purchase_date' => '2023-07-15',
                'price' => 8200.00,
                'warranty_expire_date' => '2025-07-15',
                'budget_year' => '2566',
                'notes' => 'พิมพ์ใบเสร็จรับเงินผู้ป่วย',
            ],
        ];

        $assetMap = [];
        foreach ($assets as $assetData) {
            $asset = Asset::firstOrCreate(['asset_code' => $assetData['asset_code']], $assetData);
            $assetMap[$assetData['asset_code']] = $asset->id;
        }

        // 6. Repairs (รายการแจ้งซ่อมตัวอย่าง เพื่อให้ Dashboard และรายงานแสดงผลได้ทันที)
        $repairs = [
            [
                'ticket_number' => 'REP-202609-0001',
                'title' => 'เครื่องเปิดไม่ติด มีกลิ่นไหม้เล็กน้อย',
                'description' => 'เมื่อเช้าเปิดเครื่องแล้วไฟไม่เข้า พัดลมไม่หมุน ได้กลิ่นไหม้จางๆ บริเวณด้านหลังเครื่อง รบกวนช่างตรวจสอบด่วนครับ เพราะต้องใช้คีย์ข้อมูลคนไข้ฉุกเฉิน',
                'asset_id' => $assetMap['7440-001-0005/65'],
                'other_device_info' => null,
                'department_id' => $deptMap['ER'],
                'location_detail' => 'เคาน์เตอร์เวชระเบียน ER',
                'urgency' => 'critical',
                'status' => 'in_progress',
                'requester_id' => $userMap['user_er'],
                'requester_name' => 'นายสมชาย มุ่งมั่น',
                'requester_phone' => '088-1234567 (ต่อ 109)',
                'technician_id' => $userMap['technician'],
                'received_at' => Carbon::now()->subHours(3),
                'cause' => 'ตรวจเบื้องต้นพบ Power Supply ไหม้จากไฟกระชาก',
                'solution' => 'กำลังเบิก Power Supply 550W ใหม่จากคลังมาเปลี่ยนทดแทน',
                'total_cost' => 590.00,
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'ticket_number' => 'REP-202609-0002',
                'title' => 'เครื่องพิมพ์เลเซอร์ OPD พิมพ์แล้วกระดาษติดและหมึกซีด',
                'description' => 'เครื่องพิมพ์ Brother หลอดไฟแจ้ง Error กระดาษติดดึงไม่ออก และมีข้อความ Replace Toner บนหน้าจอ พิมพ์สลิปและใบสั่งยาไม่ออกครับ',
                'asset_id' => $assetMap['7440-002-0001/67'],
                'other_device_info' => null,
                'department_id' => $deptMap['OPD'],
                'location_detail' => 'เคาน์เตอร์พยาบาล OPD',
                'urgency' => 'high',
                'status' => 'completed',
                'requester_id' => $userMap['user_opd'],
                'requester_name' => 'พว.สุดาพร ใจดี',
                'requester_phone' => 'ต่อ 111',
                'technician_id' => $userMap['chang'],
                'received_at' => Carbon::now()->subDays(1)->setTime(9, 30),
                'completed_at' => Carbon::now()->subDays(1)->setTime(10, 45),
                'cause' => 'มีเศษกระดาษฉีกติดที่ชุด Feed ลูกยางดึงกระดาษ และตลับหมึกหมดอายุการใช้งาน',
                'solution' => 'เคลียร์เศษกระดาษ ทำความสะอาดชุดลูกยาง และเปลี่ยนตลับหมึก Brother TN-B022 ใหม่ 1 ตลับ ทดสอบพิมพ์งานคมชัดปกติ',
                'total_cost' => 590.00,
                'satisfaction_score' => 5,
                'satisfaction_comment' => 'ช่างช้างมาแก้ไขเร็วมาก งานไม่สะดุดเลย ขอบคุณมากค่ะ',
                'created_at' => Carbon::now()->subDays(1)->setTime(9, 0),
            ],
            [
                'ticket_number' => 'REP-202609-0003',
                'title' => 'คอมพิวเตอร์ธุรการ ค้างบ่อยมาก บูตเครื่องเข้า Windows ใช้เวลาเกิน 15 นาที',
                'description' => 'เปิดโปรแกรมแล้วขึ้น Not Responding ตลอดเวลา เซฟเอกสารช้ามาก บางครั้งจอฟ้า (Blue Screen)',
                'asset_id' => $assetMap['7440-001-0008/64'],
                'other_device_info' => null,
                'department_id' => $deptMap['ADM'],
                'location_detail' => 'ห้องบริหารทั่วไป ชั้น 1',
                'urgency' => 'normal',
                'status' => 'waiting_parts',
                'requester_id' => null,
                'requester_name' => 'นางวรรณา รวดเร็ว',
                'requester_phone' => 'ต่อ 100',
                'technician_id' => $userMap['technician'],
                'received_at' => Carbon::now()->subDays(2),
                'cause' => 'HDD 1TB เสื่อมสภาพ มี Bad Sector จำนวนมาก ทำให้ระบบ I/O ค้าง',
                'solution' => 'รอเบิกงบจัดซื้อ SSD M.2 หรือใช้อะไหล่ทดแทน พร้อมสำรองข้อมูลเดิม',
                'total_cost' => 0.00,
                'created_at' => Carbon::now()->subDays(2)->subHours(3),
            ],
            [
                'ticket_number' => 'REP-202609-0004',
                'title' => 'เครื่องอ่านบัตรประชาชน Smart Card อ่านช้าและหลุดบ่อย',
                'description' => 'เสียบสมาร์ทการ์ดแล้วโปรแกรม HosxP ไม่ดึงข้อมูลเลขบัตรประชาชน ต้องขยับสาย USB หลายครั้ง',
                'asset_id' => null,
                'other_device_info' => 'เครื่องอ่าน Smart Card สีดำ USB เชื่อมต่อโต๊ะเวชระเบียน',
                'department_id' => $deptMap['INS'],
                'location_detail' => 'โต๊ะลงทะเบียนบัตร ชั้น 1',
                'urgency' => 'high',
                'status' => 'pending',
                'requester_id' => null,
                'requester_name' => 'จนท.เวชระเบียน กุลธิดา',
                'requester_phone' => 'ต่อ 110',
                'technician_id' => null,
                'created_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'ticket_number' => 'REP-202608-0012',
                'title' => 'อินเทอร์เน็ตและเครือข่ายแล็บหลุด เข้า HosxP ไม่ได้',
                'description' => 'เครื่องคอมฯ ในห้องแล็บขึ้นสัญลักษณ์ตกใจสีเหลือง Network Unreachable เข้าดูผลเลือดจากระบบไม่ได้',
                'asset_id' => null,
                'other_device_info' => 'สาย LAN จุดเชื่อมต่อโต๊ะแล็บ',
                'department_id' => $deptMap['LAB'],
                'location_detail' => 'ห้องปฏิบัติการเทคนิคการแพทย์',
                'urgency' => 'critical',
                'status' => 'completed',
                'requester_id' => null,
                'requester_name' => 'ทนพ.ธนากร วิเคราะห์ดี',
                'requester_phone' => 'ต่อ 118',
                'technician_id' => $userMap['chang'],
                'received_at' => Carbon::now()->subWeeks(2),
                'completed_at' => Carbon::now()->subWeeks(2)->addHours(1),
                'cause' => 'หัวต่อ RJ45 หักหลวม และสายแลนชำรุดจากการถูกเหยียบ',
                'solution' => 'เปลี่ยนสาย LAN Patch Cord Cat6 ความยาว 3 เมตรเส้นใหม่และเดินร้อยท่อเก็บสายเรียบร้อย เชื่อมต่อเครือข่ายได้ความเร็ว 1Gbps',
                'total_cost' => 65.00,
                'satisfaction_score' => 5,
                'satisfaction_comment' => 'รวดเร็วมากครับ ผลเลือดออกได้ทันตามเวลา',
                'created_at' => Carbon::now()->subWeeks(2)->subMinutes(15),
            ],
        ];

        foreach ($repairs as $repData) {
            $created_at = $repData['created_at'];
            unset($repData['created_at']);
            $repair = Repair::firstOrCreate(['ticket_number' => $repData['ticket_number']], $repData);
            $repair->created_at = $created_at;
            $repair->save();

            // Create timeline log
            RepairLog::create([
                'repair_id' => $repair->id,
                'user_id' => $repair->requester_id ?? $userMap['admin'],
                'action' => 'created',
                'previous_status' => null,
                'new_status' => 'pending',
                'comment' => 'สร้างรายการแจ้งซ่อม: ' . $repair->title,
                'created_at' => $created_at,
            ]);

            if ($repair->status === 'completed') {
                RepairLog::create([
                    'repair_id' => $repair->id,
                    'user_id' => $repair->technician_id,
                    'action' => 'completed',
                    'previous_status' => 'in_progress',
                    'new_status' => 'completed',
                    'comment' => 'ปิดงานซ่อม: ' . $repair->solution,
                    'created_at' => $repair->completed_at ?? Carbon::now(),
                ]);

                // If it used part
                if ($repair->ticket_number === 'REP-202609-0002') {
                    RepairPart::create([
                        'repair_id' => $repair->id,
                        'spare_part_id' => $spareMap['SP-001'],
                        'quantity' => 1,
                        'unit_price' => 590.00,
                        'total_price' => 590.00,
                    ]);
                } elseif ($repair->ticket_number === 'REP-202608-0012') {
                    RepairPart::create([
                        'repair_id' => $repair->id,
                        'spare_part_id' => $spareMap['SP-009'],
                        'quantity' => 1,
                        'unit_price' => 65.00,
                        'total_price' => 65.00,
                    ]);
                }
            } elseif ($repair->status === 'in_progress') {
                RepairLog::create([
                    'repair_id' => $repair->id,
                    'user_id' => $repair->technician_id,
                    'action' => 'status_changed',
                    'previous_status' => 'pending',
                    'new_status' => 'in_progress',
                    'comment' => 'รับเรื่องและเข้าตรวจสภาพเครื่อง: ' . $repair->cause,
                    'created_at' => $repair->received_at ?? Carbon::now(),
                ]);
            }
        }

        $this->call([
            SettingSeeder::class,
            HardwareSpecsSeeder::class,
            DataRequestSeeder::class,
        ]);
    }
}
