<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_data_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 30)->unique();
            $table->foreignId('user_id')->constrained('it_users')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('it_departments')->nullOnDelete();
            $table->string('title');
            $table->string('objective_type', 50)->default('ha_quality'); // research, ha_quality, executive, external, other
            $table->text('objective_detail')->nullable();
            $table->date('data_start_date')->nullable();
            $table->date('data_end_date')->nullable();
            $table->text('criteria_detail');
            $table->string('file_format', 30)->default('excel'); // excel, csv, pdf, text
            $table->string('urgency', 20)->default('normal'); // normal, urgent, very_urgent
            $table->boolean('pdpa_consent')->default(true);
            $table->string('status', 30)->default('pending'); // pending, approved, in_progress, completed, rejected
            $table->foreignId('handler_id')->nullable()->constrained('it_users')->nullOnDelete();
            $table->text('sql_query')->nullable();
            $table->string('result_file')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_data_requests');
    }
};
