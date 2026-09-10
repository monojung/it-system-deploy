<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0); // bytes
            $table->string('type')->default('manual'); // manual, auto, pre_restore
            $table->string('status')->default('completed'); // completed, failed
            $table->foreignId('created_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_backups');
    }
};
