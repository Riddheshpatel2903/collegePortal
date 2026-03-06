<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_subject_assignments')) {
            return;
        }

        $rows = DB::table('teacher_subject_assignments')
            ->select('id', 'subject_id')
            ->whereNotNull('subject_id')
            ->orderBy('subject_id')
            ->orderByDesc('is_active')
            ->orderByDesc('assigned_date')
            ->orderByDesc('id')
            ->get();

        $keepBySubject = [];
        $deleteIds = [];
        foreach ($rows as $row) {
            $subjectId = (int) $row->subject_id;
            if (!isset($keepBySubject[$subjectId])) {
                $keepBySubject[$subjectId] = (int) $row->id;
                continue;
            }
            $deleteIds[] = (int) $row->id;
        }

        if (!empty($deleteIds)) {
            DB::table('teacher_subject_assignments')->whereIn('id', $deleteIds)->delete();
        }

        if (!$this->indexExists('teacher_subject_assignments', 'tsa_subject_unique')) {
            Schema::table('teacher_subject_assignments', function (Blueprint $table) {
                $table->unique('subject_id', 'tsa_subject_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('teacher_subject_assignments')) {
            return;
        }

        if ($this->indexExists('teacher_subject_assignments', 'tsa_subject_unique')) {
            Schema::table('teacher_subject_assignments', function (Blueprint $table) {
                $table->dropUnique('tsa_subject_unique');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($indexes as $index) {
                if (($index->name ?? null) === $indexName) {
                    return true;
                }
            }
            return false;
        }

        if ($driver === 'mysql') {
            $rows = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
            return !empty($rows);
        }

        return false;
    }
};
