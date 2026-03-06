<?php

namespace App\Services;

use App\Models\AcademicPhase;
use App\Models\Course;
use App\Models\Student;

class AcademicStructureService
{
    public function __construct(private SemesterCalculationService $semesterCalculationService)
    {
    }

    public function totalSemesters(Course $course): int
    {
        return $this->semesterCalculationService->totalSemesters($course);
    }

    public function getSemesterPositionFromDate($onDate = null, int $semestersPerYear = 2): int
    {
        return $this->semesterCalculationService->getPhaseIndex();
    }

    public function deriveCurrentSemesterNumber(Student $student, ?int $semesterPosition = null, $onDate = null): int
    {
        return $this->semesterCalculationService->currentSemesterForStudent($student);
    }

    public function deriveSemesterNumberForYear(Course $course, int $currentYear, int $semesterPosition): int
    {
        $phaseIndex = $semesterPosition === 2 ? 2 : 1;
        $year = max(1, min($currentYear, (int) $course->duration_years));
        $semesterNumber = (($year - 1) * 2) + $phaseIndex;

        return min($semesterNumber, $this->totalSemesters($course));
    }

    public function activePhase(): AcademicPhase
    {
        return $this->semesterCalculationService->getActivePhase();
    }
}
