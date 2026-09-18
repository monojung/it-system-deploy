<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Widen columns in it_hardware_audits
        Schema::table('it_hardware_audits', function (Blueprint $table) {
            $table->string('monitor_size', 255)->nullable()->change();
            $table->string('cpu_model', 255)->nullable()->change();
            $table->string('model', 255)->nullable()->change();
            $table->string('brand', 255)->nullable()->change();
            $table->string('storage_second', 255)->nullable()->change();
            $table->string('os_license', 255)->nullable()->change();
            $table->string('gpu_model', 255)->nullable()->change();
            $table->string('ram_slots', 255)->nullable()->change();
        });

        // 2. Widen columns in it_assets
        Schema::table('it_assets', function (Blueprint $table) {
            $table->string('monitor_size', 255)->nullable()->change();
            $table->string('cpu_model', 255)->nullable()->change();
            $table->string('storage_second', 255)->nullable()->change();
            $table->string('os_license', 255)->nullable()->change();
            $table->string('gpu_model', 255)->nullable()->change();
            $table->string('ram_slots', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('it_hardware_audits', function (Blueprint $table) {
            $table->string('monitor_size', 50)->nullable()->change();
        });

        Schema::table('it_assets', function (Blueprint $table) {
            $table->string('monitor_size', 50)->nullable()->change();
        });
    }
};
