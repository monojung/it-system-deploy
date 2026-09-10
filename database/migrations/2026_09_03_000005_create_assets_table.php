<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique(); // รหัสครุภัณฑ์ เช่น 7440-001-0001/67
            $table->string('serial_number')->nullable()->index();
            $table->string('name'); // ชื่อรายการครุภัณฑ์
            $table->foreignId('device_type_id')->nullable()->constrained('it_device_types')->nullOnDelete();
            $table->string('brand')->nullable(); // Dell, HP, Brother ฯลฯ
            $table->string('model')->nullable();
            $table->text('specs')->nullable(); // CPU, RAM, SSD, OS ฯลฯ
            $table->string('ip_address', 45)->nullable();
            $table->string('mac_address', 50)->nullable();
            $table->foreignId('department_id')->nullable()->constrained('it_departments')->nullOnDelete();
            $table->string('location_detail')->nullable(); // ห้อง/จุดวาง
            $table->string('custodian_name')->nullable(); // ผู้รับผิดชอบ/ผู้ใช้งาน
            $table->string('status')->default('active'); // active, spare, repairing, broken, disposed
            $table->date('purchase_date')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->date('warranty_expire_date')->nullable();
            $table->string('budget_year', 10)->nullable(); // เช่น 2567, 2568
            $table->string('image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_assets');
    }
};
