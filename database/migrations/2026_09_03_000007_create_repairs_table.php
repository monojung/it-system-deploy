<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_repairs', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // REP-202609-0001
            $table->string('title'); // หัวข้อปัญหา / อาการเบื้องต้น
            $table->text('description'); // รายละเอียดอาการเสีย
            $table->foreignId('asset_id')->nullable()->constrained('it_assets')->nullOnDelete();
            $table->string('other_device_info')->nullable(); // อุปกรณ์อื่นๆ
            $table->foreignId('department_id')->constrained('it_departments')->cascadeOnDelete();
            $table->string('location_detail')->nullable(); // จุดที่ตั้ง
            $table->enum('urgency', ['low', 'normal', 'high', 'critical'])->default('normal'); // ปกติ, ด่วน, ด่วนมาก, ฉุกเฉิน
            $table->string('status')->default('pending'); // pending, in_progress, waiting_parts, completed, external, cancelled
            $table->foreignId('requester_id')->nullable()->constrained('it_users')->nullOnDelete();
            $table->string('requester_name'); // ชื่อผู้แจ้ง
            $table->string('requester_phone')->nullable(); // เบอร์ติดต่อ
            $table->foreignId('technician_id')->nullable()->constrained('it_users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('cause')->nullable(); // สาเหตุ
            $table->text('solution')->nullable(); // วิธีแก้
            $table->text('external_repair_detail')->nullable(); // ส่งซ่อมภายนอก
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->tinyInteger('satisfaction_score')->nullable(); // 1-5
            $table->text('satisfaction_comment')->nullable();
            $table->string('attachment_image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_repairs');
    }
};
