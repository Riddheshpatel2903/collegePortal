<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (!Schema::hasColumn('leaves', 'current_stage')) {
                $table->enum('current_stage', ['hod_review', 'admin_review', 'closed'])->default('hod_review')->after('status');
            }
            if (!Schema::hasColumn('leaves', 'requested_by_role')) {
                $table->enum('requested_by_role', ['student', 'teacher', 'hod'])->nullable()->after('leave_type');
            }
            if (!Schema::hasColumn('leaves', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            if (Schema::hasColumn('leaves', 'current_stage')) {
                $table->dropColumn('current_stage');
            }
            if (Schema::hasColumn('leaves', 'requested_by_role')) {
                $table->dropColumn('requested_by_role');
            }
            if (Schema::hasColumn('leaves', 'applied_at')) {
                $table->dropColumn('applied_at');
            }
        });
    }
};
