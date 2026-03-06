<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->enum('session_type', ['lecture', 'practical', 'tutorial'])->default('lecture');
            $table->text('topic')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            
            $table->index(['semester_subject_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
