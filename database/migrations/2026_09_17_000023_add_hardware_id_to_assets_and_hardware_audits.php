<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add hardware_id to it_assets
        Schema::table('it_assets', function (Blueprint $table) {
            $table->string('hardware_id', 100)->nullable()->index()->after('serial_number');
        });

        // 2. Add hardware_id to it_hardware_audits
        Schema::table('it_hardware_audits', function (Blueprint $table) {
            $table->string('hardware_id', 100)->nullable()->index()->after('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('it_hardware_audits', function (Blueprint $table) {
            $table->dropColumn('hardware_id');
        });

        Schema::table('it_assets', function (Blueprint $table) {
            $table->dropColumn('hardware_id');
        });
    }
};
