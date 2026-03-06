<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'cpi')) {
            Schema::table('students', function (Blueprint $table) {
                $afterColumn = Schema::hasColumn('students', 'cgpa')
                    ? 'cgpa'
                    : (Schema::hasColumn('students', 'current_semester_number') ? 'current_semester_number' : 'admission_year');
                $table->decimal('cpi', 4, 2)->default(0)->after($afterColumn);
            });
        }

        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (!Schema::hasColumn('results', 'locked_at')) {
                    $table->timestamp('locked_at')->nullable()->after('result_declared_date');
                }
                if (!Schema::hasColumn('results', 'locked_by')) {
                    $table->foreignId('locked_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('results')) {
            Schema::table('results', function (Blueprint $table) {
                if (Schema::hasColumn('results', 'locked_by')) {
                    $table->dropConstrainedForeignId('locked_by');
                }
                if (Schema::hasColumn('results', 'locked_at')) {
                    $table->dropColumn('locked_at');
                }
            });
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'cpi')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('cpi');
            });
        }
    }
};

