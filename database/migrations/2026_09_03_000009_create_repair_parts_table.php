<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_repair_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_id')->constrained('it_repairs')->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained('it_spare_parts')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_repair_parts');
    }
};
