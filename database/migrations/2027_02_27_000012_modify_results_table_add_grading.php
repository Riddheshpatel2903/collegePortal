<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('results');

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->decimal('sgpa', 4, 2)->default(0.00);
            $table->decimal('cgpa', 4, 2)->default(0.00);
            $table->integer('total_credits_earned')->default(0);
            $table->integer('backlog_subjects')->default(0);
            $table->enum('result_status', ['pass', 'fail', 'detained', 'pending'])->default('pending');
            $table->boolean('promoted')->default(false);
            $table->date('result_declared_date')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'semester_id'], 'unique_student_semester_result');
        });
    }

    public function down()
    {
        Schema::dropIfExists('results');
    }
};
