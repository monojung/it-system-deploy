<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('password_setup_token', 100)->nullable()->index()->after('email_verified_at');
            $table->timestamp('password_setup_expires_at')->nullable()->after('password_setup_token');
        });

        // Mark existing active users as already verified so current accounts remain working seamlessly
        DB::table('it_users')->whereNull('email_verified_at')->update([
            'email_verified_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'password_setup_token',
                'password_setup_expires_at',
            ]);
        });
    }
};
