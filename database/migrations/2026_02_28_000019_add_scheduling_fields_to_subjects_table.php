<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('subjects', 'weekly_hours')) {
                $table->unsignedTinyInteger('weekly_hours')->default(4)->after('credits');
            }
            if (! Schema::hasColumn('subjects', 'is_lab')) {
                $table->boolean('is_lab')->default(false)->after('weekly_hours');
            }
            if (! Schema::hasColumn('subjects', 'lab_block_hours')) {
                $table->unsignedTinyInteger('lab_block_hours')->nullable()->after('is_lab');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('subjects', 'weekly_hours')) {
                $drops[] = 'weekly_hours';
            }
            if (Schema::hasColumn('subjects', 'is_lab')) {
                $drops[] = 'is_lab';
            }
            if (Schema::hasColumn('subjects', 'lab_block_hours')) {
                $drops[] = 'lab_block_hours';
            }
            if (! empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
