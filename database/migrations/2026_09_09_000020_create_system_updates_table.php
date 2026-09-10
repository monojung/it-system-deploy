<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version')->nullable();
            $table->string('commit_hash', 50)->nullable();
            $table->string('previous_commit', 50)->nullable();
            $table->integer('commits_count')->default(0);
            $table->text('changelog')->nullable();
            $table->string('status', 30)->default('running'); // running, success, failed
            $table->string('backup_file')->nullable();
            $table->longText('output_log')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_seconds')->default(0);
            $table->foreignId('triggered_by')->nullable()->constrained('it_users')->nullOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_system_updates');
    }
};
