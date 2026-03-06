<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\FeeStructure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeeStructureSeeder extends Seeder
{
    public function run(): void
    {
        $feesByYear = [
            1 => 75000,
            2 => 80000,
            3 => 85000,
            4 => 90000,
        ];

        $courses = Course::query()->orderBy('id')->get();
        if ($courses->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($courses, $feesByYear) {
            foreach ($courses as $course) {
                foreach ($feesByYear as $year => $amount) {
                    FeeStructure::query()->updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'year_number' => $year,
                            'fee_type' => 'tuition',
                        ],
                        [
                            'semester_number' => null,
                            'semester_sequence' => null,
                            'amount' => $amount,
                            'is_mandatory' => true,
                            'description' => "Annual tuition fee for year {$year}",
                            'is_active' => true,
                        ]
                    );
                }
            }
        });
    }
}

