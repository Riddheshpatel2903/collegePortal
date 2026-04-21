<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('departments', 'hod_id')) {
                $table->foreignId('hod_id')->nullable()->after('name')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('teacher_subject_assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('teacher_subject_assignments', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->after('teacher_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('notices', function (Blueprint $table) {
            if (! Schema::hasColumn('notices', 'posted_by')) {
                $table->foreignId('posted_by')->nullable()->after('content')->constrained('users')->nullOnDelete();
            }
        });

        if (Schema::hasTable('teacher_subject_assignments') && Schema::hasTable('semester_subjects')) {
            if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
                DB::statement(
                    'UPDATE teacher_subject_assignments 
                     SET subject_id = (
                         SELECT subject_id 
                         FROM semester_subjects 
                         WHERE semester_subjects.id = teacher_subject_assignments.semester_subject_id
                     )
                     WHERE subject_id IS NULL'
                );
            } else {
                DB::statement(
                    'UPDATE teacher_subject_assignments tsa
                     INNER JOIN semester_subjects ss ON ss.id = tsa.semester_subject_id
                     SET tsa.subject_id = ss.subject_id
                     WHERE tsa.subject_id IS NULL'
                );
            }
        }

        Schema::table('fee_structures', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_structures', 'semester_sequence')) {
                $table->integer('semester_sequence')->nullable()->after('course_id');
            }
        });

        if (Schema::hasTable('fee_structures')) {
            DB::statement('UPDATE fee_structures SET semester_sequence = semester_number WHERE semester_sequence IS NULL');
        }

        Schema::table('semesters', function (Blueprint $table) {
            if (! Schema::hasColumn('semesters', 'sequence')) {
                $table->integer('sequence')->nullable()->after('academic_session_id');
            }
            if (! Schema::hasColumn('semesters', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('is_current');
            }
        });

        if (Schema::hasTable('semesters')) {
            DB::statement('UPDATE semesters SET sequence = semester_number WHERE sequence IS NULL');
            DB::statement("UPDATE semesters SET is_active = CASE WHEN status = 'active' THEN 1 ELSE 0 END");
        }

        if (! Schema::hasTable('attendance_records')) {
            Schema::create('attendance_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attendance_session_id')->constrained()->onDelete('cascade');
                $table->foreignId('student_id')->constrained()->onDelete('cascade');
                $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
                $table->text('remarks')->nullable();
                $table->timestamps();
                $table->unique(['attendance_session_id', 'student_id'], 'unique_attendance_records');
            });

            if (Schema::hasTable('attendances')) {
                DB::statement(
                    'INSERT INTO attendance_records (attendance_session_id, student_id, status, remarks, created_at, updated_at)
                     SELECT attendance_session_id, student_id, status, remarks, created_at, updated_at FROM attendances'
                );
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_records')) {
            Schema::drop('attendance_records');
        }

        Schema::table('semesters', function (Blueprint $table) {
            if (Schema::hasColumn('semesters', 'sequence')) {
                $table->dropColumn('sequence');
            }
            if (Schema::hasColumn('semesters', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            if (Schema::hasColumn('fee_structures', 'semester_sequence')) {
                $table->dropColumn('semester_sequence');
            }
        });

        Schema::table('teacher_subject_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_subject_assignments', 'subject_id')) {
                $table->dropForeign(['subject_id']);
                $table->dropColumn('subject_id');
            }
        });

        Schema::table('notices', function (Blueprint $table) {
            if (Schema::hasColumn('notices', 'posted_by')) {
                $table->dropForeign(['posted_by']);
                $table->dropColumn('posted_by');
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'hod_id')) {
                $table->dropForeign(['hod_id']);
                $table->dropColumn('hod_id');
            }
            if (Schema::hasColumn('departments', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
