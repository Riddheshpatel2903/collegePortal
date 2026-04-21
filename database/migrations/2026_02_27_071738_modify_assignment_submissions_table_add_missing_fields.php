<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('assignment_submissions', 'submitted_at')) {
                $table->dateTime('submitted_at')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('assignment_submissions', 'status')) {
                $table->string('status')->default('pending')->after('submitted_at');
            }
            if (Schema::hasColumn('assignment_submissions', 'marks') && ! Schema::hasColumn('assignment_submissions', 'marks_obtained')) {
                $table->renameColumn('marks', 'marks_obtained');
            }
        });
    }

    public function down()
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['submitted_at', 'status']);
            if (Schema::hasColumn('assignment_submissions', 'marks_obtained')) {
                $table->renameColumn('marks_obtained', 'marks');
            }
        });
    }
};
