<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_repair_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_id')->constrained('it_repairs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('it_users')->nullOnDelete();
            $table->string('action'); // created, status_changed, assigned, part_used, resolved, cancelled
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_repair_logs');
    }
};
