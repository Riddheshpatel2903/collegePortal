<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_session_id')->constrained()->onDelete('cascade');
            $table->integer('semester_number'); // 1, 2, 3, 4, etc.
            $table->string('name'); // "Semester 1", "Semester 2"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();

            // Unique semester per course per session
            $table->unique(['course_id', 'academic_session_id', 'semester_number'], 'unique_semester');
        });
    }

    public function down()
    {
        Schema::dropIfExists('semesters');
    }
};
