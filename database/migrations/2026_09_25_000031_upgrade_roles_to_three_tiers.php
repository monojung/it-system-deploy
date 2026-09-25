<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Upgrade existing admin users (including username 'admin') to 'super_admin'
     * while preserving other roles.
     */
    public function up(): void
    {
        // 1. Upgrade primary admin or users with role 'admin' to 'super_admin'
        DB::table('it_users')
            ->where('username', 'admin')
            ->orWhere('role', 'admin')
            ->update(['role' => 'super_admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('it_users')
            ->where('role', 'super_admin')
            ->update(['role' => 'admin']);
    }
};
