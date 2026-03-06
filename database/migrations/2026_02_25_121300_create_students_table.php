<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_semester_id')
                ->nullable()
                ->constrained('semesters')
                ->nullOnDelete();

            $table->string('roll_number')->unique();
            $table->integer('admission_year');

            $table->enum('academic_status', [
                'active',
                'backlog',
                'promoted',
                'graduated',
                'detained'
            ])->default('active');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
