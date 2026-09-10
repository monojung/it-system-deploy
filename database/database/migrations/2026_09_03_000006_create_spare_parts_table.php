<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_code')->unique(); // เช่น PART-001
            $table->string('name');
            $table->string('category')->nullable(); // อะไหล่คอมพิวเตอร์, หมึกพิมพ์, อุปกรณ์ต่อพ่วง, วัสดุเครือข่าย
            $table->string('unit')->default('ชิ้น'); // ชิ้น, เส้น, ตลับ, ม้วน
            $table->integer('stock_quantity')->default(0);
            $table->integer('minimum_quantity')->default(5);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('location')->nullable(); // ชั้น/ตู้
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_spare_parts');
    }
};
