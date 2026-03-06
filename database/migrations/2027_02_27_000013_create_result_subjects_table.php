<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('result_subjects');
        
        Schema::create('result_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->decimal('internal_marks', 5, 2)->default(0);
            $table->decimal('external_marks', 5, 2)->default(0);
            $table->decimal('total_marks', 5, 2)->virtualAs('internal_marks + external_marks');
            $table->decimal('max_marks', 5, 2)->default(100);
            $table->decimal('percentage', 5, 2)->virtualAs('(internal_marks + external_marks) / max_marks * 100');
            $table->string('grade', 2)->nullable(); // A+, A, B+, etc.
            $table->decimal('grade_point', 4, 2)->default(0); // 10.00, 9.00, etc.
            $table->integer('credits')->default(3);
            $table->boolean('is_backlog')->default(false);
            $table->enum('subject_status', ['pass', 'fail', 'absent'])->default('pass');
            $table->timestamps();
            
            $table->unique(['result_id', 'semester_subject_id'], 'unique_result_subject');
        });
    }

    public function down()
    {
        Schema::dropIfExists('result_subjects');
    }
};
