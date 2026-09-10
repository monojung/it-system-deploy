<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->string('google_email')->nullable()->after('google_id');
            $table->boolean('mfa_enabled')->default(false)->after('google_email');
            $table->boolean('mfa_enforced')->default(false)->after('mfa_enabled');
            $table->string('mfa_secret')->nullable()->after('mfa_enforced');
            $table->timestamp('mfa_enrolled_at')->nullable()->after('mfa_secret');
        });
    }

    public function down(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->dropColumn([
                'google_email',
                'mfa_enabled',
                'mfa_enforced',
                'mfa_secret',
                'mfa_enrolled_at',
            ]);
        });
    }
};
