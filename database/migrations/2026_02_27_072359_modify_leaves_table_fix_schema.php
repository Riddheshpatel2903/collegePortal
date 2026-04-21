<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('leaves')) {
            return;
        }

        Schema::table('leaves', function (Blueprint $table) {
            if (! Schema::hasColumn('leaves', 'applied_at')) {
                $table->timestamp('applied_at')->nullable()->after('approved_at');
            }
            // Change leave_type to string to accommodate various names
            $table->string('leave_type')->default('casual')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('leaves')) {
            return;
        }

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('applied_at');
            // Reverting to enum might be complex, leaving as string for safety
        });
    }
};
