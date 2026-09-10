<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataRequest;
use App\Models\User;
use App\Models\Department;

class DataRequestSeeder extends Seeder
{
    public function run(): void
    {
        $userOpd = User::where('username', 'user_opd')->first();
        $userEr = User::where('username', 'user_er')->first();
        $chang = User::where('username', 'chang')->first() ?: User::where('role', 'technician')->first();

        if ($userOpd) {
            DataRequest::firstOrCreate(
                ['request_no' => 'REQ-202609-0001'],
                [
                    'user_id' => $userOpd->id,
                    'department_id' => $userOpd->department_id,
                    'title' => 'สถิติผู้ป่วยนอกโรคเรื้อรัง (เบาหวานและความดันโลหิตสูง) ประจำไตรมาส 3',
                    'objective_type' => 'ha_quality',
                    'objective_detail' => 'ใช้สำหรับทบทวนเวชระเบียนและเตรียมรับการประเมินคุณภาพ HA ทีมนำทางคลินิก (PCT)',
                    'data_start_date' => '2026-04-01',
                    'data_end_date' => '2026-06-30',
                    'criteria_detail' => "1. ผู้ป่วยที่มารับบริการแผนก OPD\n2. รหัสโรค ICD-10: E11 (เบาหวานชนิดที่ 2), I10 (ความดันโลหิตสูง)\n3. ต้องการฟิลด์: HN, วันที่รับบริการ, เพศ, อายุ, สิทธิการรักษา, ค่าระดับน้ำตาลสะสม HbA1c, ค่าความดัน BP",
                    'file_format' => 'excel',
                    'urgency' => 'normal',
                    'pdpa_consent' => true,
                    'status' => 'in_progress',
                    'handler_id' => $chang?->id,
                    'admin_notes' => 'รับเรื่องเรียบร้อย กำลังเขียน SQL query ดึงข้อมูลจาก HosXP ให้ครับ',
                ]
            );
        }

        if ($userEr) {
            DataRequest::firstOrCreate(
                ['request_no' => 'REQ-202609-0002'],
                [
                    'user_id' => $userEr->id,
                    'department_id' => $userEr->department_id,
                    'title' => 'สถิติผู้ป่วยอุบัติเหตุจราจร (Traffic Injury) ช่วงเทศกาลสงกรานต์',
                    'objective_type' => 'external',
                    'objective_detail' => 'ส่งรายงานสรุปสถิติอุบัติเหตุ 7 วันอันตราย ให้สำนักงานสาธารณสุขจังหวัดลำพูน (สสจ.)',
                    'data_start_date' => '2026-04-11',
                    'data_end_date' => '2026-04-17',
                    'criteria_detail' => "1. ผู้ป่วยที่เข้ารับการรักษาที่ห้องฉุกเฉิน (ER)\n2. สาเหตุการบาดเจ็บ V01-V89\n3. ต้องการฟิลด์: HN, วัน-เวลาเกิดเหตุ, ประเภทยานพาหนะ, การดื่มแอลกอฮอล์, การสวมหมวกนิรภัย, ผลลัพธ์การรักษา (กลับบ้าน/Admit/Refer)",
                    'file_format' => 'excel',
                    'urgency' => 'urgent',
                    'pdpa_consent' => true,
                    'status' => 'completed',
                    'handler_id' => $chang?->id,
                    'sql_query' => "SELECT e.hn, p.pname, p.fname, p.lname, e.vstdate, e.vsttime, \n       ai.accident_type_id, ai.alcohol_type_id, ai.helmet_type_id, e.er_emergency_type_id\nFROM er_regist e\nJOIN patient p ON e.hn = p.hn\nLEFT JOIN accident_inquiry ai ON e.vn = ai.vn\nWHERE e.vstdate BETWEEN '2026-04-11' AND '2026-04-17'\nORDER BY e.vstdate, e.vsttime;",
                    'admin_notes' => 'ประมวลผลข้อมูลและตรวจสอบความถูกต้องตามเกณฑ์ สสจ. เรียบร้อยแล้ว',
                    'completed_at' => now(),
                ]
            );
        }
    }
}
