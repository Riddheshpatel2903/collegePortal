<?php

namespace Database\Seeders;

use App\Models\Result;
use App\Models\SemesterSubject;
use Illuminate\Database\Seeder;

class ResultSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $results = Result::all();

        foreach ($results as $result) {
            $semesterSubjects = SemesterSubject::with('subject')
                ->where('semester_id', $result->semester_id)
                ->get();

            foreach ($semesterSubjects as $semesterSubject) {
                $subject = $semesterSubject->subject;
                if (! $subject) {
                    continue;
                }

                if (! $result->subjects()->where('subject_id', $subject->id)->exists()) {
                    $result->subjects()->create([
                        'subject_id' => $subject->id,
                        'semester_subject_id' => $semesterSubject->id,
                        'internal_marks' => rand(18, 30),
                        'final_marks' => rand(45, 70),
                    ]);
                }
            }

            if (method_exists($result, 'calculateTotals')) {
                $result->calculateTotals();
            }
        }
    }
}
