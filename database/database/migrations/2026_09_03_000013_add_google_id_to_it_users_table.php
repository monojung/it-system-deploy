<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('cid');
        });
    }

    public function down(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
