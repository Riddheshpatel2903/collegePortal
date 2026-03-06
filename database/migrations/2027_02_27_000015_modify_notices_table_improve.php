<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('notices', function (Blueprint $table) {
            if (!Schema::hasColumn('notices', 'notice_for')) {
                $table->enum('notice_for', ['all', 'students', 'teachers', 'specific_course'])->default('all')->after('content');
            }
            if (!Schema::hasColumn('notices', 'course_id')) {
                $table->foreignId('course_id')->nullable()->after('notice_for')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('notices', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('course_id');
            }
            if (!Schema::hasColumn('notices', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('is_active');
            }
            if (!Schema::hasColumn('notices', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('priority');
            }
        });
    }

    public function down()
    {
        Schema::table('notices', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropColumn(['notice_for', 'course_id', 'is_active', 'priority', 'expiry_date']);
        });
    }
};
