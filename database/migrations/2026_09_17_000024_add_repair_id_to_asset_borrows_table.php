<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_asset_borrows', function (Blueprint $table) {
            $table->foreignId('repair_id')
                ->nullable()
                ->after('asset_id')
                ->constrained('it_repairs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('it_asset_borrows', function (Blueprint $table) {
            $table->dropForeign(['repair_id']);
            $table->dropColumn('repair_id');
        });
    }
};
