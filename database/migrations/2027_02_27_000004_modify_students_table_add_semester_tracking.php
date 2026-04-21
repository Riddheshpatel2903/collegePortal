<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('students', 'academic_session_id')) {
                $table->foreignId('academic_session_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('students', 'current_semester_id')) {
                $table->foreignId('current_semester_id')->nullable()->after('academic_session_id')->constrained('semesters')->nullOnDelete();
            }
            if (! Schema::hasColumn('students', 'current_semester_number')) {
                $table->integer('current_semester_number')->default(1)->after('current_semester_id');
            }
            if (! Schema::hasColumn('students', 'registration_number')) {
                $table->string('registration_number')->nullable()->unique()->after('roll_number');
            }
            if (! Schema::hasColumn('students', 'cgpa')) {
                $table->decimal('cgpa', 3, 2)->default(0.00)->after('current_semester_number');
            }
            if (! Schema::hasColumn('students', 'backlog_count')) {
                $table->integer('backlog_count')->default(0)->after('cgpa');
            }
            if (! Schema::hasColumn('students', 'student_status')) {
                $table->enum('student_status', ['active', 'promoted', 'detained', 'graduated', 'dropped'])->default('active')->after('backlog_count');
            }
            if (! Schema::hasColumn('students', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('student_status');
            }
            if (! Schema::hasColumn('students', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('students', 'admission_date')) {
                $table->date('admission_date')->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('students', 'phone')) {
                $table->string('phone')->nullable()->after('admission_date');
            }
            if (! Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['academic_session_id']);
            $table->dropForeign(['current_semester_id']);
            $table->dropColumn([
                'academic_session_id',
                'current_semester_id',
                'current_semester_number',
                'cgpa',
                'backlog_count',
                'student_status',
                'admission_date',
            ]);
        });
    }
};
