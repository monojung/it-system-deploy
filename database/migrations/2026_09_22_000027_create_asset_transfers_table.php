<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no')->unique(); // e.g. TR-256909-0001
            $table->foreignId('asset_id')->constrained('it_assets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('it_users')->nullOnDelete(); // ผู้ทำรายการ / ผู้ยื่นคำขอ
            $table->string('transfer_type')->default('relocation'); // relocation, department_transfer, temporary_move

            // ข้อมูลต้นทาง (Original / From)
            $table->foreignId('from_department_id')->nullable()->constrained('it_departments')->nullOnDelete();
            $table->string('from_department_name')->nullable();
            $table->string('from_location_detail')->nullable();
            $table->string('from_custodian_name')->nullable();
            $table->string('from_ip_address')->nullable();

            // ข้อมูลปลายทาง (Destination / To)
            $table->foreignId('to_department_id')->nullable()->constrained('it_departments')->nullOnDelete();
            $table->string('to_department_name')->nullable();
            $table->string('to_location_detail')->nullable();
            $table->string('to_custodian_name')->nullable();
            $table->string('to_ip_address')->nullable();

            // การดำเนินงานและสถานะ
            $table->date('transfer_date'); // วันที่ดำเนินการ
            $table->text('reason'); // เหตุผลความจำเป็นในการย้าย
            $table->string('status')->default('pending')->index(); // pending, in_progress, completed, cancelled
            $table->foreignId('technician_id')->nullable()->constrained('it_users')->nullOnDelete(); // ช่าง IT ผู้ดำเนินการ
            $table->dateTime('completed_at')->nullable(); // วันเวลาที่ย้ายเสร็จสิ้น
            $table->text('test_result')->nullable(); // ผลการทดสอบหลังติดตั้ง (เช่น เครือข่ายปกติ พิมพ์ได้ ใช้งานระบบ รพ. ได้)
            $table->string('receiver_name')->nullable(); // ผู้รับมอบ ณ จุดติดตั้งใหม่
            $table->text('notes')->nullable(); // บันทึกข้อความเพิ่มเติม
            $table->string('attachment')->nullable(); // รูปถ่ายหรือเอกสารแนบ

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_asset_transfers');
    }
};
