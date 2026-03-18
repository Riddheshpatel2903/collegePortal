<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('department_id');
            $table->index('current_semester_id');
        });

        Schema::table('teacher_subject_assignments', function (Blueprint $table) {
            $table->index('teacher_id');
            $table->index('semester_subject_id');
        });

        Schema::table('results', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('course_id');
            $table->index('semester_number');
        });

        Schema::table('result_subjects', function (Blueprint $table) {
            $table->index('result_id');
            $table->index('subject_id');
            $table->index('student_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('student_id');
            $table->index('attendance_session_id');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->index('course_id');
            $table->index('semester_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['current_semester_id']);
        });

        Schema::table('teacher_subject_assignments', function (Blueprint $table) {
            $table->dropIndex(['teacher_id']);
            $table->dropIndex(['semester_subject_id']);
        });

        Schema::table('results', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['course_id']);
            $table->dropIndex(['semester_number']);
        });

        Schema::table('result_subjects', function (Blueprint $table) {
            $table->dropIndex(['result_id']);
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
            $table->dropIndex(['attendance_session_id']);
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['course_id']);
            $table->dropIndex(['semester_id']);
        });
    }
};
