<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('it_budget_sources') && !Schema::hasColumn('it_budget_sources', 'color')) {
            Schema::table('it_budget_sources', function (Blueprint $table) {
                $table->string('color', 20)->nullable()->default('#0284c7')->after('code');
            });
        }

        if (Schema::hasTable('it_acquisition_methods') && !Schema::hasColumn('it_acquisition_methods', 'color')) {
            Schema::table('it_acquisition_methods', function (Blueprint $table) {
                $table->string('color', 20)->nullable()->default('#0d9488')->after('code');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('it_budget_sources') && Schema::hasColumn('it_budget_sources', 'color')) {
            Schema::table('it_budget_sources', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }

        if (Schema::hasTable('it_acquisition_methods') && Schema::hasColumn('it_acquisition_methods', 'color')) {
            Schema::table('it_acquisition_methods', function (Blueprint $table) {
                $table->dropColumn('color');
            });
        }
    }
};
