<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "2024-2025"
            $table->year('start_year');
            $table->year('end_year');
            $table->date('session_start_date');
            $table->date('session_end_date');
            $table->boolean('is_current')->default(false);
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();

            // Ensure only one current session
            // Note: unique filter with where clause is used if DB supports it.
            // For general compatibility, we will handle uniqueness in the model/service.
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_sessions');
    }
};
