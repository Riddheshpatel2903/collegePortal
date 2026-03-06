<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Result;
use App\Models\Subject;

class ResultSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $results = Result::all();

        foreach ($results as $result) {

            $subjects = Subject::where('course_id', $result->student->course_id)
                ->where('semester_id', $result->semester_id)
                ->get();

            foreach ($subjects as $subject) {
                if (!$result->subjects()->where('subject_id', $subject->id)->exists()) {
                    $result->subjects()->create([
                        'subject_id' => $subject->id,
                        'internal_marks' => rand(18, 30),
                        'final_marks' => rand(45, 70),
                    ]);
                }
            }

            // 🔥 Force recalc after inserting subjects
            $result->calculateTotals();
        }
    }
}
