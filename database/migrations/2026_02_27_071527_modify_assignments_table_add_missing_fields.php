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
        Schema::table('assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignments', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('subject_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('assignments', 'total_marks')) {
                $table->integer('total_marks')->default(100)->after('description');
            }
            if (! Schema::hasColumn('assignments', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('total_marks');
            }
            if (! Schema::hasColumn('assignments', 'status')) {
                $table->string('status')->default('published')->after('attachment_path');
            }
            if (! Schema::hasColumn('assignments', 'allow_late_submission')) {
                $table->boolean('allow_late_submission')->default(false)->after('status');
            }
            if (! Schema::hasColumn('assignments', 'late_until')) {
                $table->dateTime('late_until')->nullable()->after('allow_late_submission');
            }
            if (! Schema::hasColumn('assignments', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('late_until');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn([
                'course_id',
                'total_marks',
                'attachment_path',
                'status',
                'allow_late_submission',
                'late_until',
                'is_active',
            ]);
        });
    }
};
