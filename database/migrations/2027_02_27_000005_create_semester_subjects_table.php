<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('semester_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->integer('credits')->default(3);
            $table->enum('subject_type', ['theory', 'practical', 'elective', 'core'])->default('core');
            $table->boolean('is_mandatory')->default(true);
            $table->integer('total_classes')->default(60); // For attendance calculation
            $table->timestamps();

            $table->unique(['semester_id', 'subject_id'], 'unique_semester_subject');
        });
    }

    public function down()
    {
        Schema::dropIfExists('semester_subjects');
    }
};
