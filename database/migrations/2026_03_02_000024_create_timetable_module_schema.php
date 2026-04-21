<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            if (! Schema::hasColumn('teachers', 'max_lectures_per_day')) {
                $table->unsignedTinyInteger('max_lectures_per_day')->default(6)->after('department_id');
            }
        });

        Schema::table('subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('subjects', 'semester_number')) {
                $table->unsignedTinyInteger('semester_number')->nullable()->after('course_id');
            }
            if (! Schema::hasColumn('subjects', 'type')) {
                $table->enum('type', ['lecture', 'lab'])->default('lecture')->after('name');
            }
            if (! Schema::hasColumn('subjects', 'hours_per_week')) {
                $table->unsignedTinyInteger('hours_per_week')->default(4)->after('type');
            }
            if (! Schema::hasColumn('subjects', 'teacher_id')) {
                $table->foreignId('teacher_id')->nullable()->after('hours_per_week')->constrained('teachers')->nullOnDelete();
            }
            if (! Schema::hasColumn('subjects', 'lab_duration')) {
                $table->unsignedTinyInteger('lab_duration')->nullable()->after('teacher_id');
            }
        });

        if (Schema::hasColumn('subjects', 'semester_sequence') && Schema::hasColumn('subjects', 'semester_number')) {
            DB::statement('UPDATE subjects SET semester_number = COALESCE(semester_number, semester_sequence)');
        }
        if (Schema::hasColumn('subjects', 'is_lab') && Schema::hasColumn('subjects', 'type')) {
            DB::statement("UPDATE subjects SET type = CASE WHEN is_lab = 1 THEN 'lab' ELSE 'lecture' END WHERE type IS NOT NULL");
        }
        if (Schema::hasColumn('subjects', 'weekly_hours') && Schema::hasColumn('subjects', 'hours_per_week')) {
            DB::statement('UPDATE subjects SET hours_per_week = COALESCE(hours_per_week, weekly_hours, credits, 4)');
        }
        if (Schema::hasColumn('subjects', 'lab_block_hours') && Schema::hasColumn('subjects', 'lab_duration')) {
            DB::statement('UPDATE subjects SET lab_duration = COALESCE(lab_duration, lab_block_hours)');
        }

        Schema::table('classrooms', function (Blueprint $table) {
            if (! Schema::hasColumn('classrooms', 'type')) {
                $table->enum('type', ['lecture', 'lab'])->default('lecture')->after('name');
            }
            if (! Schema::hasColumn('classrooms', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('type')->constrained('courses')->nullOnDelete();
            }
            if (! Schema::hasColumn('classrooms', 'year_number')) {
                $table->unsignedTinyInteger('year_number')->nullable()->after('course_id');
            }
        });

        if (! Schema::hasTable('timetable')) {
            Schema::create('timetable', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->unsignedTinyInteger('year_number');
                $table->unsignedTinyInteger('semester_number');
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
                $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
                $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
                $table->unsignedTinyInteger('slot_number');
                $table->string('lab_block_id')->nullable();
                $table->timestamps();

                $table->index(['course_id', 'year_number', 'semester_number']);
                $table->index(['teacher_id', 'day', 'slot_number']);
                $table->index(['classroom_id', 'day', 'slot_number']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('timetable')) {
            Schema::drop('timetable');
        }
    }
};
