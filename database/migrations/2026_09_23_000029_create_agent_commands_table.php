<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_agent_commands', function (Blueprint $table) {
            $table->id();
            $table->string('command', 50)->default('scan')->index(); // scan, ping, update_agent
            $table->string('batch_id', 60)->nullable()->index(); // For grouping broadcast 'scan-all' jobs
            $table->string('target_type', 20)->default('single')->index(); // single, all
            $table->string('target_hardware_id', 100)->nullable()->index();
            $table->string('target_hostname', 100)->nullable()->index();
            $table->foreignId('target_audit_id')->nullable()->constrained('it_hardware_audits')->nullOnDelete();
            $table->foreignId('target_asset_id')->nullable()->constrained('it_assets')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index(); // pending, processing, completed, failed, cancelled
            $table->foreignId('requested_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('parameters')->nullable();
            $table->text('result_summary')->nullable();
            $table->timestamps();

            // Composite indexes for rapid polling lookup
            $table->index(['status', 'target_hardware_id']);
            $table->index(['status', 'target_hostname']);
            $table->index(['status', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_agent_commands');
    }
};
