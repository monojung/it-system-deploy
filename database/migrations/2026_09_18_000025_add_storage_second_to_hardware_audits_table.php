<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_hardware_audits', function (Blueprint $table) {
            $table->string('storage_second', 100)->nullable()->after('storage_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('it_hardware_audits', function (Blueprint $table) {
            $table->dropColumn('storage_second');
        });
    }
};
