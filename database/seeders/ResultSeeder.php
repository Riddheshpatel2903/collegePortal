<?php

namespace Database\Seeders;

use App\Models\Result;
use App\Models\ResultSubject;
use App\Models\Semester;
use App\Models\SemesterSubject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResultSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('role', 'admin')->value('id');
        $students = Student::query()->with('course')->get();
        if ($students->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($students, $adminId) {
            foreach ($students as $student) {
                $maxSemester = max(1, min(8, (int) $student->current_year * 2));
                $cumulativeCredits = 0.0;
                $cumulativeGradePoints = 0.0;
                $studentBacklogs = 0;

                for ($semesterNo = 1; $semesterNo <= $maxSemester; $semesterNo++) {
                    $semester = Semester::query()
                        ->where('course_id', $student->course_id)
                        ->where('semester_number', $semesterNo)
                        ->first();

                    $semesterSubjects = SemesterSubject::query()
                        ->where('semester_id', $semester?->id)
                        ->with('subject')
                        ->get();

                    if ($semesterSubjects->isEmpty()) {
                        continue;
                    }

                    $result = Result::query()->updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'semester_id' => $semester?->id,
                        ],
                        [
                            'course_id' => $student->course_id,
                            'academic_year' => (int) ceil($semesterNo / 2),
                            'semester_number' => $semesterNo,
                            'sgpa' => 0,
                            'cgpa' => 0,
                            'total_credits_earned' => 0,
                            'backlog_subjects' => 0,
                            'result_status' => 'pending',
                            'promoted' => false,
                        ]
                    );

                    $semesterCredits = 0.0;
                    $semesterGradePoints = 0.0;
                    $semesterBacklogs = 0;
                    $earnedCredits = 0;

                    foreach ($semesterSubjects as $semesterSubject) {
                        $subject = $semesterSubject->subject;
                        if (!$subject) {
                            continue;
                        }

                        $credits = (int) ($semesterSubject->credits ?: $subject->credits ?: 3);
                        $internal = random_int(10, 30);
                        $external = random_int(35, 70);

                        if (random_int(1, 100) <= 10) {
                            $external = random_int(0, 25);
                        }

                        $resultSubject = ResultSubject::query()->updateOrCreate(
                            [
                                'result_id' => $result->id,
                                'semester_subject_id' => $semesterSubject->id,
                            ],
                            [
                                'subject_id' => $subject->id,
                                'student_id' => $student->id,
                                'internal_marks' => $internal,
                                'external_marks' => $external,
                                'max_marks' => 100,
                                'credits' => $credits,
                            ]
                        );

                        $semesterCredits += $credits;
                        $semesterGradePoints += ((float) $resultSubject->grade_point * $credits);
                        if ($resultSubject->is_backlog) {
                            $semesterBacklogs++;
                        } else {
                            $earnedCredits += $credits;
                        }
                    }

                    $spi = $semesterCredits > 0 ? round($semesterGradePoints / $semesterCredits, 2) : 0.0;
                    $cumulativeCredits += $semesterCredits;
                    $cumulativeGradePoints += $semesterGradePoints;
                    $cpi = $cumulativeCredits > 0 ? round($cumulativeGradePoints / $cumulativeCredits, 2) : 0.0;
                    $studentBacklogs += $semesterBacklogs;

                    $result->update([
                        'sgpa' => $spi,
                        'cgpa' => $cpi,
                        'total_credits_earned' => $earnedCredits,
                        'backlog_subjects' => $semesterBacklogs,
                        'result_status' => $semesterBacklogs > 0 ? 'fail' : 'pass',
                        'promoted' => $semesterBacklogs === 0,
                        'result_declared_date' => now()->subDays(random_int(1, 40))->toDateString(),
                        'locked_at' => now(),
                        'locked_by' => $adminId,
                    ]);
                }

                $student->update([
                    'cgpa' => $cumulativeCredits > 0 ? round($cumulativeGradePoints / $cumulativeCredits, 2) : 0.0,
                    'cpi' => $cumulativeCredits > 0 ? round($cumulativeGradePoints / $cumulativeCredits, 2) : 0.0,
                    'backlog_count' => $studentBacklogs,
                    'academic_status' => $studentBacklogs > 0
                        ? 'backlog'
                        : (($student->current_year >= 4) ? 'graduated' : 'active'),
                ]);
            }
        });
    }
}

