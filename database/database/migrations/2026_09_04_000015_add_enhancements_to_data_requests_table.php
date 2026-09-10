<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_data_requests', function (Blueprint $table) {
            $table->string('sample_file')->nullable()->after('criteria_detail');
            $table->string('sample_filename')->nullable()->after('sample_file');
            $table->unsignedInteger('download_count')->default(0)->after('result_file');
            $table->foreignId('re_request_from_id')->nullable()->after('handler_id')->constrained('it_data_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('it_data_requests', function (Blueprint $table) {
            $table->dropForeign(['re_request_from_id']);
            $table->dropColumn(['sample_file', 'sample_filename', 'download_count', 're_request_from_id']);
        });
    }
};
