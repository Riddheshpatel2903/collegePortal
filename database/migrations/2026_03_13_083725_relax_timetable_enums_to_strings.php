<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('type')->default('Theory')->change();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('type')->default('lecture')->change();
        });

        Schema::table('timetable', function (Blueprint $table) {
            $table->string('day')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->enum('type', ['lecture', 'lab'])->default('lecture')->change();
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->enum('type', ['lecture', 'lab'])->default('lecture')->change();
        });

        Schema::table('timetable', function (Blueprint $table) {
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])->change();
        });
    }
};
