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
            $table->string('code')->nullable()->after('course_id');
            $table->unsignedTinyInteger('lecture_hours')->default(0)->after('name');
            $table->unsignedTinyInteger('tutorial_hours')->default(0)->after('lecture_hours');
            $table->unsignedTinyInteger('practical_hours')->default(0)->after('tutorial_hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            //
        });
    }
};
