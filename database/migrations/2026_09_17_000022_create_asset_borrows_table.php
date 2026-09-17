<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_asset_borrows', function (Blueprint $table) {
            $table->id();
            $table->string('borrow_no')->unique(); // e.g. BR-256909-0001
            $table->foreignId('user_id')->nullable()->constrained('it_users')->nullOnDelete();
            $table->string('borrower_name'); // ชื่อ-สกุลผู้ขอยืม
            $table->foreignId('department_id')->nullable()->constrained('it_departments')->nullOnDelete();
            $table->string('borrower_department')->nullable(); // ชื่อกลุ่มงาน/แผนก
            $table->string('borrower_position')->nullable(); // ตำแหน่งผู้ขอยืม
            $table->string('contact_phone', 50)->nullable();
            $table->foreignId('asset_id')->constrained('it_assets')->cascadeOnDelete();
            $table->text('purpose'); // วัตถุประสงค์การยืมใช้งาน
            $table->string('location_used')->nullable(); // สถานที่นำไปใช้งาน
            $table->date('borrow_date'); // วันที่ต้องการยืม/รับของ
            $table->date('expected_return_date'); // กำหนดส่งคืน
            $table->date('actual_return_date')->nullable(); // วันที่ส่งคืนจริง
            $table->text('accessories')->nullable(); // อุปกรณ์เสริมที่ยืม (JSON หรือ comma-separated)
            $table->string('status')->default('pending')->index(); // pending, approved, borrowed, returned, rejected, cancelled
            $table->text('notes')->nullable(); // หมายเหตุเพิ่มเติมจากผู้ขอยืม
            $table->text('rejection_reason')->nullable(); // เหตุผลกรณีไม่อนุมัติ
            
            // Handover / Approval Workflow
            $table->foreignId('approved_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('dispatched_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->dateTime('dispatched_at')->nullable();
            $table->string('dispatch_condition')->nullable(); // สภาพอุปกรณ์ก่อนส่งมอบ
            
            // Return Workflow
            $table->foreignId('received_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->string('return_condition')->nullable(); // สภาพเมื่อรับคืน: 'ปกติ สมบูรณ์', 'ชำรุดเสียหาย', 'อุปกรณ์ไม่ครบ'
            $table->text('return_notes')->nullable(); // บันทึกข้อสังเกตการรับคืน

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_asset_borrows');
    }
};
