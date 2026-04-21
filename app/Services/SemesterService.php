<?php

namespace App\Services;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use Carbon\Carbon;

class SemesterService
{
    /**
     * Auto-generate semesters for a course in an academic session
     */
    public function generateSemestersForCourse(Course $course, AcademicSession $session)
    {
        $totalSemesters = $course->total_semesters;
        $sessionStartDate = Carbon::parse($session->session_start_date);

        // Calculate semester duration in months
        $semesterDurationMonths = 12 / ($course->semesters_per_year ?: 2);

        $semesters = [];

        for ($i = 1; $i <= $totalSemesters; $i++) {
            $startDate = $sessionStartDate->copy()->addMonths(($i - 1) * $semesterDurationMonths);
            $endDate = $startDate->copy()->addMonths($semesterDurationMonths)->subDay();

            $semester = Semester::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'academic_session_id' => $session->id,
                    'semester_number' => $i,
                ],
                [
                    'name' => "Semester $i",
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $i == 1 ? 'active' : 'upcoming',
                    'is_current' => $i == 1,
                ]
            );

            $semesters[] = $semester;
        }

        return $semesters;
    }

    /**
     * Activate next semester
     */
    public function activateNextSemester(Semester $currentSemester)
    {
        $nextSemester = Semester::where('course_id', $currentSemester->course_id)
            ->where('academic_session_id', $currentSemester->academic_session_id)
            ->where('semester_number', $currentSemester->semester_number + 1)
            ->first();

        if ($nextSemester) {
            $currentSemester->update([
                'is_current' => false,
                'status' => 'completed',
            ]);

            $nextSemester->update([
                'is_current' => true,
                'status' => 'active',
            ]);

            return $nextSemester;
        }

        return null;
    }
}
