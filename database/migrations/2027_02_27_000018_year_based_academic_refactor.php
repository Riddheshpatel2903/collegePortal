<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'sqlite') {
            // Transition legacy semester foreign keys to nullable so year-based records can be created.
            if (Schema::hasColumn('students', 'current_semester_id')) {
                DB::statement('ALTER TABLE students MODIFY current_semester_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('fee_structures', 'semester_number')) {
                DB::statement('ALTER TABLE fee_structures MODIFY semester_number INT NULL');
            }
            if (Schema::hasColumn('student_fees', 'semester_id')) {
                DB::statement('ALTER TABLE student_fees MODIFY semester_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('results', 'semester_id')) {
                DB::statement('ALTER TABLE results MODIFY semester_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('assignments', 'semester_id')) {
                DB::statement('ALTER TABLE assignments MODIFY semester_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('schedules', 'semester_id')) {
                DB::statement('ALTER TABLE schedules MODIFY semester_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('teacher_subject_assignments', 'semester_id')) {
                DB::statement('ALTER TABLE teacher_subject_assignments MODIFY semester_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('teacher_subject_assignments', 'semester_subject_id')) {
                DB::statement('ALTER TABLE teacher_subject_assignments MODIFY semester_subject_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('teacher_subject_assignments', 'academic_session_id')) {
                DB::statement('ALTER TABLE teacher_subject_assignments MODIFY academic_session_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('attendance_sessions', 'semester_subject_id')) {
                DB::statement('ALTER TABLE attendance_sessions MODIFY semester_subject_id BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('result_subjects', 'semester_subject_id')) {
                DB::statement('ALTER TABLE result_subjects MODIFY semester_subject_id BIGINT UNSIGNED NULL');
            }
        }

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'current_year')) {
                $table->unsignedTinyInteger('current_year')->default(1)->after('admission_year');
            }
        });

        if (Schema::hasColumn('students', 'current_semester_number') && $driver !== 'sqlite') {
            DB::statement('UPDATE students SET current_year = GREATEST(1, CEILING(current_semester_number / 2)) WHERE current_semester_number IS NOT NULL');
        }

        Schema::table('fee_structures', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_structures', 'year_number')) {
                $table->unsignedTinyInteger('year_number')->default(1)->after('course_id');
            }
        });

        if (Schema::hasColumn('fee_structures', 'semester_number') && $driver !== 'sqlite') {
            DB::statement('UPDATE fee_structures SET year_number = GREATEST(1, CEILING(semester_number / 2))');
        }

        Schema::table('student_fees', function (Blueprint $table) {
            if (! Schema::hasColumn('student_fees', 'academic_year')) {
                $table->unsignedTinyInteger('academic_year')->default(1)->after('student_id');
            }
        });

        $driver = DB::getDriverName();

        if (Schema::hasTable('semesters') && $driver !== 'sqlite') {
            DB::statement('
                UPDATE student_fees sf
                JOIN semesters sem ON sem.id = sf.semester_id
                SET sf.academic_year = GREATEST(1, CEILING(sem.semester_number / 2))
            ');
        }

        Schema::table('results', function (Blueprint $table) {
            if (! Schema::hasColumn('results', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('results', 'academic_year')) {
                $table->unsignedTinyInteger('academic_year')->default(1)->after('course_id');
            }
            if (! Schema::hasColumn('results', 'semester_number')) {
                $table->unsignedTinyInteger('semester_number')->default(1)->after('academic_year');
            }
        });

        if (Schema::hasTable('semesters') && $driver !== 'sqlite') {
            DB::statement('
                UPDATE results r
                JOIN students s ON s.id = r.student_id
                LEFT JOIN semesters sem ON sem.id = r.semester_id
                SET r.course_id = s.course_id,
                    r.semester_number = COALESCE(sem.semester_number, 1),
                    r.academic_year = GREATEST(1, CEILING(COALESCE(sem.semester_number, 1) / 2))
            ');
        }

        Schema::table('assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignments', 'academic_year')) {
                $table->unsignedTinyInteger('academic_year')->default(1)->after('course_id');
            }
            if (! Schema::hasColumn('assignments', 'semester_number')) {
                $table->unsignedTinyInteger('semester_number')->default(1)->after('academic_year');
            }
        });

        if (Schema::hasTable('semesters') && $driver !== 'sqlite') {
            DB::statement('
                UPDATE assignments a
                JOIN semesters sem ON sem.id = a.semester_id
                SET a.semester_number = sem.semester_number,
                    a.academic_year = GREATEST(1, CEILING(sem.semester_number / 2))
            ');
        }

        Schema::table('attendance_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_sessions', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('teacher_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('attendance_sessions', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('attendance_sessions', 'academic_year')) {
                $table->unsignedTinyInteger('academic_year')->default(1)->after('subject_id');
            }
            if (! Schema::hasColumn('attendance_sessions', 'semester_number')) {
                $table->unsignedTinyInteger('semester_number')->default(1)->after('academic_year');
            }
        });

        if (Schema::hasTable('semester_subjects') && Schema::hasTable('semesters') && $driver !== 'sqlite') {
            DB::statement('
                UPDATE attendance_sessions ats
                JOIN semester_subjects ss ON ss.id = ats.semester_subject_id
                JOIN semesters sem ON sem.id = ss.semester_id
                SET ats.subject_id = ss.subject_id,
                    ats.course_id = sem.course_id,
                    ats.semester_number = sem.semester_number,
                    ats.academic_year = GREATEST(1, CEILING(sem.semester_number / 2))
            ');
        }

        Schema::table('result_subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('result_subjects', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('result_id')->constrained()->nullOnDelete();
            }
        });

        if (Schema::hasTable('semester_subjects') && $driver !== 'sqlite') {
            DB::statement('
                UPDATE result_subjects rs
                JOIN semester_subjects ss ON ss.id = rs.semester_subject_id
                SET rs.subject_id = ss.subject_id
            ');
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive rollback for live data safety.
    }
};
