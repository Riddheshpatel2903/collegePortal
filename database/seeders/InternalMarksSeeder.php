<?php

namespace Database\Seeders;

use App\Models\AcademicPhase;
use App\Models\Result;
use App\Models\ResultSubject;
use App\Models\Semester;
use App\Models\SemesterSubject;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternalMarksSeeder extends Seeder
{
    public function run(): void
    {
        $phase = AcademicPhase::query()->where('is_active', true)->first();
        $phaseIndex = strcasecmp((string) ($phase?->phase_name ?? 'Odd'), 'Even') === 0 ? 2 : 1;

        $students = Student::query()->with('course')->get();
        if ($students->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($students, $phaseIndex) {
            foreach ($students as $student) {
                $semesterNumber = (($student->current_year - 1) * 2) + $phaseIndex;
                $semesterNumber = max(1, min(8, (int) $semesterNumber));

                $semester = Semester::query()
                    ->where('course_id', $student->course_id)
                    ->where('semester_number', $semesterNumber)
                    ->first();

                $result = Result::query()->firstOrCreate(
                    [
                        'student_id' => $student->id,
                        'semester_id' => $semester?->id,
                    ],
                    [
                        'course_id' => $student->course_id,
                        'academic_year' => (int) ceil($semesterNumber / 2),
                        'semester_number' => $semesterNumber,
                        'sgpa' => 0,
                        'cgpa' => 0,
                        'total_credits_earned' => 0,
                        'backlog_subjects' => 0,
                        'result_status' => 'pending',
                        'promoted' => false,
                    ]
                );

                $semesterSubjects = SemesterSubject::query()
                    ->where('semester_id', $semester?->id)
                    ->with('subject')
                    ->get();

                foreach ($semesterSubjects as $semesterSubject) {
                    $isFailCase = random_int(1, 100) <= 10;
                    $internal = $isFailCase ? random_int(0, 11) : random_int(12, 30);

                    ResultSubject::query()->updateOrCreate(
                        [
                            'result_id' => $result->id,
                            'semester_subject_id' => $semesterSubject->id,
                        ],
                        [
                            'subject_id' => $semesterSubject->subject_id,
                            'student_id' => $student->id,
                            'internal_marks' => $internal,
                            'external_marks' => 0,
                            'max_marks' => 30,
                            'credits' => $semesterSubject->credits ?: ($semesterSubject->subject?->credits ?? 3),
                        ]
                    );
                }
            }
        });
    }
}
