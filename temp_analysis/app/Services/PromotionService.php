<?php

namespace App\Services;

use App\Models\Student;

class PromotionService
{
    public function __construct(private SemesterCalculationService $semesterCalculationService)
    {
    }

    public function promoteAtYearEnd(Student $student, bool $passedAllSubjects): bool
    {
        $course = $student->course;
        if (!$course) {
            return false;
        }

        $activePhase = $this->semesterCalculationService->getActivePhase();
        if (strcasecmp($activePhase->phase_name, 'Even') !== 0) {
            return false;
        }

        if ($passedAllSubjects && (int) $student->current_year < (int) $course->duration_years) {
            $nextYear = (int) $student->current_year + 1;
            $student->update([
                'current_year' => $nextYear,
                'student_status' => 'promoted',
                'academic_status' => 'promoted',
            ]);

            app(FeeService::class)->applyYearFeeToStudent($student, $nextYear);
            return true;
        }

        if ($passedAllSubjects && (int) $student->current_year >= (int) $course->duration_years) {
            $student->update([
                'student_status' => 'graduated',
                'academic_status' => 'graduated',
            ]);

            return false;
        }

        $student->update([
            'student_status' => 'detained',
            'academic_status' => 'backlog',
            'backlog_count' => (int) ($student->backlog_count ?? 0) + 1,
        ]);

        return false;
    }
}
