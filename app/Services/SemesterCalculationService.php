<?php

namespace App\Services;

use App\Models\AcademicPhase;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

class SemesterCalculationService
{
    public function getActivePhase(): AcademicPhase
    {
        return AcademicPhase::query()->active()->first()
            ?? AcademicPhase::query()->firstOrCreate(['phase_name' => 'Odd'], ['is_active' => true]);
    }

    public function getPhaseIndex(): int
    {
        return $this->getActivePhase()->phase_index;
    }

    public function totalSemesters(Course $course): int
    {
        return max(1, (int) $course->duration_years * max(1, ((int) $course->semesters_per_year) ?: 2));
    }

    public function semesterNumberForYear(Course $course, int $currentYear): int
    {
        $year = max(1, min((int) $currentYear, (int) $course->duration_years));
        $semesterNumber = (($year - 1) * 2) + $this->getPhaseIndex();
        $maxSemester = $this->totalSemesters($course);

        return max(1, min($semesterNumber, $maxSemester));
    }

    public function yearFromSemester(Course $course, int $semesterNumber): int
    {
        $this->validateSemesterWithinCourse($course, $semesterNumber);
        return (int) ceil($semesterNumber / 2);
    }

    public function validateSemesterWithinCourse(Course $course, int $semesterNumber): void
    {
        $maxSemester = $this->totalSemesters($course);
        if ($semesterNumber < 1 || $semesterNumber > $maxSemester) {
            throw ValidationException::withMessages([
                'semester_number' => "Semester must be between 1 and {$maxSemester} for {$course->name}.",
            ]);
        }
    }

    public function availableSemesters(Course $course): array
    {
        return range(1, $this->totalSemesters($course));
    }

    public function currentSemesterForStudent(Student $student): int
    {
        $course = $student->course;
        if (!$course) {
            return 1;
        }

        return $this->semesterNumberForYear($course, (int) $student->current_year);
    }
}
