<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create it_hardware_audits table
        Schema::create('it_hardware_audits', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year')->index(); // e.g. 2568, 2569
            $table->foreignId('asset_id')->nullable()->constrained('it_assets')->nullOnDelete();
            $table->string('hostname', 100)->nullable()->index();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('mac_address', 50)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('device_type_code', 20)->nullable(); // PC, NB, etc.
            $table->string('cpu_model', 150)->nullable();
            $table->string('cpu_speed', 50)->nullable();
            $table->integer('ram_capacity')->nullable();
            $table->string('ram_type', 30)->nullable();
            $table->string('ram_bus', 30)->nullable();
            $table->string('ram_slots', 50)->nullable();
            $table->string('storage_type', 50)->nullable();
            $table->string('storage_capacity', 50)->nullable();
            $table->string('os_name', 100)->nullable();
            $table->string('os_license', 100)->nullable();
            $table->string('gpu_model', 150)->nullable();
            $table->string('monitor_size', 50)->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('specs_diff')->nullable();
            $table->string('client_agent_version', 30)->nullable()->default('1.0.0');
            $table->string('status', 20)->default('pending')->index(); // pending, approved, rejected
            $table->foreignId('reviewed_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });

        // 2. Add last_audited columns to it_assets
        Schema::table('it_assets', function (Blueprint $table) {
            $table->timestamp('last_audited_at')->nullable()->after('notes');
            $table->integer('last_audited_fiscal_year')->nullable()->index()->after('last_audited_at');
        });
    }

    public function down(): void
    {
        Schema::table('it_assets', function (Blueprint $table) {
            $table->dropColumn(['last_audited_at', 'last_audited_fiscal_year']);
        });

        Schema::dropIfExists('it_hardware_audits');
    }
};
