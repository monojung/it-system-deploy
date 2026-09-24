<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('approval_status')->default('approved')->after('role'); // approved, pending, rejected
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('it_users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('approved_by');
            $table->boolean('onboarding_completed')->default(true)->after('rejection_reason');
        });

        // Ensure all existing accounts are marked approved and onboarding completed
        DB::table('it_users')->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'onboarding_completed' => true,
        ]);

        // Split existing 'name' into first_name and last_name for existing accounts if available
        $users = DB::table('it_users')->get(['id', 'name']);
        foreach ($users as $u) {
            $parts = explode(' ', trim($u->name), 2);
            $fname = $parts[0] ?? $u->name;
            $lname = $parts[1] ?? '';
            DB::table('it_users')->where('id', $u->id)->update([
                'first_name' => $fname,
                'last_name' => $lname,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('it_users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'first_name',
                'last_name',
                'approval_status',
                'approved_at',
                'approved_by',
                'rejection_reason',
                'onboarding_completed',
            ]);
        });
    }
};
