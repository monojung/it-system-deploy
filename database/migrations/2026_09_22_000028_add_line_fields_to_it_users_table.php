<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->string('line_user_id')->nullable()->after('cid')->index();
            $table->boolean('notify_line_enabled')->default(true)->after('line_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->dropColumn(['line_user_id', 'notify_line_enabled']);
        });
    }
};
