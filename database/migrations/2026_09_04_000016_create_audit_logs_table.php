<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('it_users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('module', 50)->index(); // auth, repairs, data_requests, assets, spare_parts, backups, users, departments, settings, hosxp, reports
            $table->string('action', 50)->index(); // login, logout, create, update, delete, status_change, download, restore, backup, execute_query, stock_adjust, export
            $table->string('description', 500);
            $table->nullableMorphs('auditable'); // auditable_type, auditable_id
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('url', 1000)->nullable();
            $table->string('method', 10)->nullable();
            $table->timestamps();

            // Composite indexes for fast query performance
            $table->index(['created_at', 'module']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_audit_logs');
    }
};
