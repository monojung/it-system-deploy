<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;
use App\Models\User;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Hospital Info
            ['key' => 'hospital_name_th', 'value' => 'โรงพยาบาลทุ่งหัวช้าง', 'group' => 'hospital', 'type' => 'text', 'description' => 'ชื่อโรงพยาบาลภาษาไทย'],
            ['key' => 'hospital_name_en', 'value' => 'Thung Hua Chang Hospital', 'group' => 'hospital', 'type' => 'text', 'description' => 'ชื่อโรงพยาบาลภาษาอังกฤษ'],
            ['key' => 'hospital_code', 'value' => '11143', 'group' => 'hospital', 'type' => 'text', 'description' => 'รหัสสถานพยาบาล 5 หลัก'],
            ['key' => 'department_name', 'value' => 'กลุ่มงานสุขภาพดิจิทัล', 'group' => 'hospital', 'type' => 'text', 'description' => 'ชื่อกลุ่มงานไอที/สารสนเทศ'],
            ['key' => 'it_head_name', 'value' => 'หัวหน้ากลุ่มงานสุขภาพดิจิทัล', 'group' => 'hospital', 'type' => 'text', 'description' => 'หัวหน้ากลุ่มงาน'],
            ['key' => 'director_name', 'value' => 'ผู้อำนวยการโรงพยาบาลทุ่งหัวช้าง', 'group' => 'hospital', 'type' => 'text', 'description' => 'ผู้อำนวยการโรงพยาบาล'],
            ['key' => 'hospital_phone', 'value' => '053-595055', 'group' => 'hospital', 'type' => 'text', 'description' => 'เบอร์โทรศัพท์กลาง'],
            ['key' => 'hospital_address', 'value' => 'ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160', 'group' => 'hospital', 'type' => 'text', 'description' => 'ที่อยู่โรงพยาบาล'],

            // Helpdesk & SLA
            ['key' => 'sla_low', 'value' => '48', 'group' => 'helpdesk', 'type' => 'integer', 'description' => 'SLA งานซ่อมระดับปกติทั่วไป (ชั่วโมง)'],
            ['key' => 'sla_normal', 'value' => '24', 'group' => 'helpdesk', 'type' => 'integer', 'description' => 'SLA งานซ่อมระดับปานกลาง (ชั่วโมง)'],
            ['key' => 'sla_high', 'value' => '4', 'group' => 'helpdesk', 'type' => 'integer', 'description' => 'SLA งานซ่อมระดับด่วน (ชั่วโมง)'],
            ['key' => 'sla_critical', 'value' => '1', 'group' => 'helpdesk', 'type' => 'integer', 'description' => 'SLA งานซ่อมด่วนที่สุดกระทบผู้ป่วย (ชั่วโมง)'],
            ['key' => 'default_min_stock', 'value' => '3', 'group' => 'helpdesk', 'type' => 'integer', 'description' => 'จุดเตือนสต็อกขั้นต่ำเริ่มต้น'],

            // Notifications
            ['key' => 'line_notify_token', 'value' => '', 'group' => 'notification', 'type' => 'text', 'description' => 'LINE Notify Token สำหรับแจ้งเตือนกลุ่มช่าง'],
            ['key' => 'line_notify_enabled', 'value' => '0', 'group' => 'notification', 'type' => 'boolean', 'description' => 'เปิดใช้งาน LINE Notify'],
            ['key' => 'notify_on_new_ticket', 'value' => '1', 'group' => 'notification', 'type' => 'boolean', 'description' => 'แจ้งเตือนเมื่อมีใบแจ้งซ่อมใหม่'],
            ['key' => 'notify_on_critical_only', 'value' => '0', 'group' => 'notification', 'type' => 'boolean', 'description' => 'แจ้งเตือนเฉพาะเคสด่วนและด่วนที่สุด'],

            // Security & Auth
            ['key' => 'allow_thai_id_login', 'value' => '1', 'group' => 'security', 'type' => 'boolean', 'description' => 'อนุญาตให้เข้าสู่ระบบด้วย ThaID / เลขบัตรประชาชน 13 หลัก'],
            ['key' => 'backup_auto_enabled', 'value' => '0', 'group' => 'security', 'type' => 'boolean', 'description' => 'เปิดใช้งานระบบสำรองฐานข้อมูลอัตโนมัติ (Auto-Backup)'],
            ['key' => 'backup_retention_days', 'value' => '30', 'group' => 'security', 'type' => 'integer', 'description' => 'ระยะเวลาเก็บไฟล์สำรองข้อมูล (วัน)'],
            ['key' => 'google_client_id', 'value' => '', 'group' => 'security', 'type' => 'text', 'description' => 'Google Client ID สำหรับ Google Sign-In'],
            ['key' => 'google_client_secret', 'value' => '', 'group' => 'security', 'type' => 'text', 'description' => 'Google Client Secret'],
            ['key' => 'thaid_client_id', 'value' => '', 'group' => 'security', 'type' => 'text', 'description' => 'ThaID Client ID (DOPA กรมการปกครอง)'],
            ['key' => 'thaid_client_secret', 'value' => '', 'group' => 'security', 'type' => 'text', 'description' => 'ThaID Client Secret'],

            // HosXP Connection
            ['key' => 'hosxp_db_host', 'value' => '192.168.2.10', 'group' => 'hosxp', 'type' => 'text', 'description' => 'HosXP Host IP'],
            ['key' => 'hosxp_db_port', 'value' => '3306', 'group' => 'hosxp', 'type' => 'text', 'description' => 'HosXP Port'],
            ['key' => 'hosxp_db_name', 'value' => 'c3thchospital', 'group' => 'hosxp', 'type' => 'text', 'description' => 'HosXP Database Name'],
            ['key' => 'hosxp_db_user', 'value' => 'chang', 'group' => 'hosxp', 'type' => 'text', 'description' => 'HosXP Username'],
            ['key' => 'hosxp_db_password', 'value' => 'chang11143', 'group' => 'hosxp', 'type' => 'text', 'description' => 'HosXP Password'],
        ];

        foreach ($settings as $st) {
            SystemSetting::updateOrCreate(['key' => $st['key']], $st);
        }

        // Update default users with Thai Citizen IDs
        $userCids = [
            'admin' => '1509900950465',
            'chang' => '3959900316670',
            'technician' => '3510400025027',
            'user_opd' => '1510100234561',
            'user_er' => '3501000123456',
            'user_phar' => '1509900112233',
        ];

        foreach ($userCids as $uname => $cid) {
            User::where('username', $uname)->update(['cid' => $cid]);
        }
    }
}
