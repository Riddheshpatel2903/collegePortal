<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('students', 'gtu_enrollment_no')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('gtu_enrollment_no')->nullable()->after('roll_number');
            });
        }

        $selectColumns = ['id', 'gtu_enrollment_no', 'roll_number'];
        if (Schema::hasColumn('students', 'registration_number')) {
            $selectColumns[] = 'registration_number';
        }

        DB::table('students')
            ->select($selectColumns)
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    if (!empty($row->gtu_enrollment_no)) {
                        continue;
                    }

                    $registrationNumber = property_exists($row, 'registration_number') ? $row->registration_number : null;
                    $base = $registrationNumber ?: ($row->roll_number ?: ('GTU' . str_pad((string) $row->id, 6, '0', STR_PAD_LEFT)));
                    $candidate = strtoupper(preg_replace('/\s+/', '', (string) $base));
                    $suffix = 0;
                    while (
                        DB::table('students')
                            ->where('gtu_enrollment_no', $candidate . ($suffix ? "-{$suffix}" : ''))
                            ->where('id', '!=', $row->id)
                            ->exists()
                    ) {
                        $suffix++;
                    }
                    $value = $candidate . ($suffix ? "-{$suffix}" : '');

                    DB::table('students')->where('id', $row->id)->update(['gtu_enrollment_no' => $value]);
                }
            }, 'id');

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE students MODIFY gtu_enrollment_no VARCHAR(255) NOT NULL');
        }

        if (!$this->indexExists('students', 'students_gtu_enrollment_no_unique')) {
            Schema::table('students', function (Blueprint $table) {
                $table->unique('gtu_enrollment_no', 'students_gtu_enrollment_no_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('students', 'gtu_enrollment_no')) {
            return;
        }

        if ($this->indexExists('students', 'students_gtu_enrollment_no_unique')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropUnique('students_gtu_enrollment_no_unique');
            });
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('gtu_enrollment_no');
        });
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
