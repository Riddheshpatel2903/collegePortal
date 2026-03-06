<?php

namespace App\Services;

use App\Models\Result;
use App\Models\ResultSubject;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ResultService
{
    public function __construct(
        protected PromotionService $promotionService
    ) {
    }
    /**
     * Calculate SGPA for a semester
     */
    public function calculateSGPA(Result $result)
    {
        $resultSubjects = $result->resultSubjects;
        
        if ($resultSubjects->isEmpty()) {
            return 0;
        }

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($resultSubjects as $resultSubject) {
            $totalGradePoints += $resultSubject->grade_point * $resultSubject->credits;
            $totalCredits += $resultSubject->credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;
    }

    /**
     * Calculate CGPA for a student
     */
    public function calculateCGPA(Student $student)
    {
        $results = $student->results()
            ->where('result_status', '!=', 'pending')
            ->get();

        if ($results->isEmpty()) {
            return 0;
        }

        $totalSGPA = 0;
        $semesterCount = 0;

        foreach ($results as $result) {
            if ($result->sgpa > 0) {
                $totalSGPA += $result->sgpa;
                $semesterCount++;
            }
        }

        return $semesterCount > 0 ? round($totalSGPA / $semesterCount, 2) : 0;
    }

    /**
     * Submit result for a student
     */
    public function submitResult(Student $student, int $semesterNumber, array $subjectMarks)
    {
        return DB::transaction(function () use ($student, $semesterNumber, $subjectMarks) {
            // Create or get result
            $result = Result::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'semester_number' => $semesterNumber
                ],
                [
                    'course_id' => $student->course_id,
                    'academic_year' => $student->current_year,
                    'result_status' => 'pending',
                    'promoted' => false
                ]
            );

            $backlogCount = 0;
            $totalCredits = 0;

            // Process each subject
            foreach ($subjectMarks as $subjectId => $marks) {
                $subject = \App\Models\Subject::find($subjectId);
                if (!$subject) {
                    continue;
                }

                $resultSubject = ResultSubject::updateOrCreate(
                    [
                        'result_id' => $result->id,
                        'subject_id' => $subjectId,
                        'student_id' => $student->id
                    ],
                    [
                        'internal_marks' => $marks['internal'],
                        'external_marks' => $marks['external'],
                        'max_marks' => $marks['max_marks'] ?? 100,
                        'credits' => $subject->credits
                    ]
                );

                if ($resultSubject->is_backlog) {
                    $backlogCount++;
                } else {
                    $totalCredits += $resultSubject->credits;
                }
            }

            // Calculate SGPA
            $sgpa = $this->calculateSGPA($result);

            // Update result
            $result->update([
                'sgpa' => $sgpa,
                'backlog_subjects' => $backlogCount,
                'total_credits_earned' => $totalCredits,
                'result_status' => $backlogCount > 0 ? 'fail' : 'pass',
                'result_declared_date' => now()
            ]);

            // Calculate and update CGPA
            $cgpa = $this->calculateCGPA($student);
            $student->update([
                'cgpa' => $cgpa,
                'cpi' => $cgpa,
                'backlog_count' => $backlogCount,
                'academic_status' => $backlogCount > 0 ? 'backlog' : 'active',
            ]);

            // Auto promote if passed
            if ($backlogCount === 0) {
                $isYearEnd = ((int) $semesterNumber % max(1, (int) $student->course?->semesters_per_year)) === 0;
                if ($isYearEnd) {
                    $this->promotionService->promoteAtYearEnd($student, true);
                }
            } else {
                $isYearEnd = ((int) $semesterNumber % max(1, (int) $student->course?->semesters_per_year)) === 0;
                if ($isYearEnd) {
                    $this->promotionService->promoteAtYearEnd($student, false);
                }
            }

            return $result;
        });
    }

    public function getResultCard(Student $student, int $semesterNumber)
    {
        $result = Result::with('resultSubjects.subject')
            ->where('student_id', $student->id)
            ->where('semester_number', $semesterNumber)
            ->first();

        return $result;
    }

    public function lockResult(Result $result, int $lockedByUserId): Result
    {
        $status = ((int) $result->backlog_subjects > 0) ? 'fail' : 'pass';
        $result->update([
            'result_status' => $status,
            'result_declared_date' => now(),
            'locked_at' => now(),
            'locked_by' => $lockedByUserId,
        ]);

        return $result->fresh();
    }
}
