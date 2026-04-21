<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teacher_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_session_id')->constrained()->onDelete('cascade');
            $table->date('assigned_date')->default(now());
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // One teacher per subject per semester
            $table->unique(['teacher_id', 'semester_subject_id', 'semester_id'], 'unique_teacher_assignment');
        });
    }

    public function down()
    {
        Schema::dropIfExists('teacher_subject_assignments');
    }
};
