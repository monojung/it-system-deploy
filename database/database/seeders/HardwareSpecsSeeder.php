<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\DeviceType;
use App\Models\Department;

class HardwareSpecsSeeder extends Seeder
{
    public function run(): void
    {
        $pcType = DeviceType::where('code', 'PC')->first() ?? DeviceType::first();
        $nbType = DeviceType::where('code', 'NB')->first() ?? $pcType;
        $aioType = DeviceType::where('code', 'AIO')->first() ?? $pcType;
        $srvType = DeviceType::where('code', 'SRV')->first() ?? $pcType;

        $opd = Department::where('code', 'OPD')->first();
        $er = Department::where('code', 'ER')->first();
        $phar = Department::where('code', 'PHAR')->first();
        $lab = Department::where('code', 'LAB')->first();
        $it = Department::where('code', 'IT')->first();
        $adm = Department::where('code', 'ADM')->first();
        $ipd = Department::where('code', 'IPD')->first();
        $fin = Department::where('code', 'FIN')->first();

        // Update existing assets cleanly
        $updates = [
            1 => [
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'ram_bus' => '3200 MHz',
                'ram_slots' => '2 แถว (8GB x 2 Dual-Channel)',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 770',
                'monitor_size' => '23.8 นิ้ว IPS',
            ],
            2 => [
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'ram_bus' => '3200 MHz',
                'ram_slots' => '2 แถว (8GB x 2 Dual-Channel)',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 770',
                'monitor_size' => '23.8 นิ้ว IPS',
            ],
            4 => [
                'storage_type' => 'SSD SATA 2.5"',
                'storage_capacity' => '256 GB',
                'ram_bus' => '2666 MHz',
                'ram_slots' => '1 แถว (8GB x 1)',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 630',
                'monitor_size' => '21.5 นิ้ว',
            ],
            7 => [
                'storage_type' => 'HDD SATA 3.5"',
                'storage_capacity' => '1 TB',
                'ram_bus' => '2133 MHz',
                'ram_slots' => '1 แถว (8GB x 1)',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel HD Graphics 530',
                'monitor_size' => '19.5 นิ้ว',
            ],
            8 => [
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'ram_type' => 'DDR5',
                'ram_bus' => '4800 MHz',
                'ram_slots' => '1 แถว (16GB x 1)',
                'os_name' => 'Windows 11 Pro',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'AMD Radeon 680M',
                'monitor_size' => '14 นิ้ว FHD IPS',
            ],
        ];

        foreach ($updates as $id => $data) {
            Asset::where('id', $id)->update($data);
        }

        // Additional sample computers showcasing DDR4 and DDR5, Windows 11 & Windows 10
        $newAssets = [
            [
                'asset_code' => '7440-001-0010/67',
                'serial_number' => 'DL-OPT-7020-01',
                'name' => 'คอมพิวเตอร์ห้องจ่ายยา ผู้ป่วยนอก (DDR5)',
                'device_type_id' => $pcType->id,
                'brand' => 'Dell',
                'model' => 'OptiPlex 7020 Plus Tower',
                'cpu_model' => 'Intel Core i5-14500',
                'cpu_speed' => '2.6 GHz Turbo 5.0 GHz',
                'ram_capacity' => 16,
                'ram_type' => 'DDR5',
                'ram_bus' => '5600 MHz',
                'ram_slots' => '1 แถว (16GB x 1)',
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'os_name' => 'Windows 11 Pro',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 770',
                'monitor_size' => '24 นิ้ว FHD IPS',
                'specs' => 'Intel Core i5-14500, RAM 16GB DDR5 5600MHz, SSD 512GB NVMe, Windows 11 Pro',
                'ip_address' => '192.168.2.75',
                'mac_address' => '00:14:22:88:99:AA',
                'department_id' => $phar?->id,
                'location_detail' => 'ห้องจ่ายยา ช่องบริการ 1',
                'custodian_name' => 'ภญ.พิมพ์ใจ รักยา',
                'status' => 'active',
                'purchase_date' => '2024-04-10',
                'price' => 28900.00,
                'warranty_expire_date' => '2027-04-10',
                'budget_year' => '2567',
            ],
            [
                'asset_code' => '7440-001-0011/67',
                'serial_number' => 'HP-ELITE-800-01',
                'name' => 'คอมพิวเตอร์สถานีตรวจเลือด ห้อง LAB (DDR5)',
                'device_type_id' => $pcType->id,
                'brand' => 'HP',
                'model' => 'EliteDesk 800 G9 SFF',
                'cpu_model' => 'Intel Core i7-13700',
                'cpu_speed' => '2.1 GHz Turbo 5.2 GHz',
                'ram_capacity' => 32,
                'ram_type' => 'DDR5',
                'ram_bus' => '4800 MHz',
                'ram_slots' => '2 แถว (16GB x 2 Dual-Channel)',
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '1 TB',
                'os_name' => 'Windows 11 Pro',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 770',
                'monitor_size' => '27 นิ้ว 2K IPS',
                'specs' => 'Intel Core i7-13700, RAM 32GB DDR5, SSD 1TB NVMe, Windows 11 Pro',
                'ip_address' => '192.168.2.80',
                'mac_address' => '3C:52:82:11:33:55',
                'department_id' => $lab?->id,
                'location_detail' => 'ห้องวิเคราะห์โลหิตวิทยา ชั้น 1',
                'custodian_name' => 'ทนพ.ธนวัฒน์ ตรวจเร็ว',
                'status' => 'active',
                'purchase_date' => '2024-05-15',
                'price' => 35500.00,
                'warranty_expire_date' => '2027-05-15',
                'budget_year' => '2567',
            ],
            [
                'asset_code' => '7440-006-0002/66',
                'serial_number' => 'LN-L14-GEN4-01',
                'name' => 'โน้ตบุ๊กงานระบบสารสนเทศ (DDR4)',
                'device_type_id' => $nbType->id,
                'brand' => 'Lenovo',
                'model' => 'ThinkPad L14 Gen 4',
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
                'specs' => 'Intel Core i5-1335U, RAM 16GB DDR4, SSD 512GB NVMe, Windows 11 Pro',
                'ip_address' => '192.168.2.105',
                'mac_address' => '48:2A:E3:44:55:66',
                'department_id' => $it?->id,
                'location_detail' => 'ห้องปฏิบัติการ IT ชั้น 2',
                'custodian_name' => 'นายช่าง IT ประจำการ',
                'status' => 'active',
                'purchase_date' => '2023-12-01',
                'price' => 26900.00,
                'warranty_expire_date' => '2026-12-01',
                'budget_year' => '2567',
            ],
            [
                'asset_code' => '7440-002-0005/65',
                'serial_number' => 'AC-AIO-C24-01',
                'name' => 'คอมพิวเตอร์ All-in-One ประชาสัมพันธ์ (DDR4)',
                'device_type_id' => $aioType->id,
                'brand' => 'Acer',
                'model' => 'Aspire C24-1700',
                'cpu_model' => 'Intel Core i3-1215U',
                'cpu_speed' => '1.2 GHz Turbo 4.4 GHz',
                'ram_capacity' => 8,
                'ram_type' => 'DDR4',
                'ram_bus' => '3200 MHz',
                'ram_slots' => '1 แถว (8GB x 1)',
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'os_name' => 'Windows 10 Pro',
                'os_license' => 'Volume License (KMS/MAK)',
                'gpu_model' => 'Intel UHD Graphics',
                'monitor_size' => '23.8 นิ้ว FHD',
                'specs' => 'Intel Core i3-1215U, RAM 8GB DDR4, SSD 512GB NVMe, Windows 10 Pro',
                'ip_address' => '192.168.2.92',
                'mac_address' => '94:C6:91:22:33:44',
                'department_id' => $adm?->id,
                'location_detail' => 'เคาน์เตอร์ประชาสัมพันธ์ อาคารผู้ป่วยนอก',
                'custodian_name' => 'เจ้าหน้าที่ประชาสัมพันธ์',
                'status' => 'active',
                'purchase_date' => '2022-10-18',
                'price' => 18500.00,
                'warranty_expire_date' => '2025-10-18',
                'budget_year' => '2566',
            ],
            [
                'asset_code' => '7440-001-0015/67',
                'serial_number' => 'DL-OPT-3000-01',
                'name' => 'คอมพิวเตอร์เคาน์เตอร์พยาบาล IPD (DDR5)',
                'device_type_id' => $pcType->id,
                'brand' => 'Dell',
                'model' => 'OptiPlex 3000 Micro',
                'cpu_model' => 'Intel Core i5-13500T',
                'cpu_speed' => '1.6 GHz Turbo 4.6 GHz',
                'ram_capacity' => 16,
                'ram_type' => 'DDR5',
                'ram_bus' => '4800 MHz',
                'ram_slots' => '1 แถว (16GB x 1)',
                'storage_type' => 'SSD NVMe M.2',
                'storage_capacity' => '512 GB',
                'os_name' => 'Windows 11 Pro',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 770',
                'monitor_size' => '21.5 นิ้ว FHD',
                'specs' => 'Intel Core i5-13500T, RAM 16GB DDR5, SSD 512GB, Windows 11 Pro',
                'ip_address' => '192.168.2.115',
                'mac_address' => '74:86:7A:12:34:56',
                'department_id' => $ipd?->id,
                'location_detail' => 'เคาน์เตอร์พยาบาล หอผู้ป่วยใน ชั้น 2',
                'custodian_name' => 'พว.หัวหน้าเวร IPD',
                'status' => 'active',
                'purchase_date' => '2024-03-20',
                'price' => 22500.00,
                'warranty_expire_date' => '2027-03-20',
                'budget_year' => '2567',
            ],
            [
                'asset_code' => '7440-001-0020/66',
                'serial_number' => 'AS-EXP-D500-01',
                'name' => 'คอมพิวเตอร์ฝ่ายการเงินและบัญชี (DDR4)',
                'device_type_id' => $pcType->id,
                'brand' => 'Asus',
                'model' => 'ExpertCenter D500 SFF',
                'cpu_model' => 'Intel Core i5-12400',
                'cpu_speed' => '2.5 GHz Turbo 4.4 GHz',
                'ram_capacity' => 8,
                'ram_type' => 'DDR4',
                'ram_bus' => '3200 MHz',
                'ram_slots' => '1 แถว (8GB x 1)',
                'storage_type' => 'SSD SATA 2.5"',
                'storage_capacity' => '512 GB',
                'os_name' => 'Windows 10 Pro',
                'os_license' => 'OEM (ติดเครื่อง/BIOS)',
                'gpu_model' => 'Intel UHD Graphics 730',
                'monitor_size' => '21.5 นิ้ว',
                'specs' => 'Intel Core i5-12400, RAM 8GB DDR4, SSD 512GB, Windows 10 Pro',
                'ip_address' => '192.168.2.122',
                'mac_address' => '50:EB:71:33:44:55',
                'department_id' => $fin?->id,
                'location_detail' => 'ห้องการเงิน ชั้น 1 อาคารอำนวยการ',
                'custodian_name' => 'น.ส.ดวงตา รับเงิน',
                'status' => 'active',
                'purchase_date' => '2023-08-14',
                'price' => 21900.00,
                'warranty_expire_date' => '2026-08-14',
                'budget_year' => '2566',
            ],
        ];

        foreach ($newAssets as $item) {
            Asset::firstOrCreate(['asset_code' => $item['asset_code']], $item);
        }
    }
}
