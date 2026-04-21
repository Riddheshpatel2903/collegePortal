<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'duration_years')) {
                $table->integer('duration_years')->default(4)->after('name');
            }
            if (! Schema::hasColumn('courses', 'semesters_per_year')) {
                $table->integer('semesters_per_year')->default(2)->after('duration_years');
            }
            if (! Schema::hasColumn('courses', 'total_semesters')) {
                $table->integer('total_semesters')->virtualAs('duration_years * semesters_per_year');
            }
            if (! Schema::hasColumn('courses', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['duration_years', 'semesters_per_year', 'total_semesters', 'is_active']);
        });
    }
};
