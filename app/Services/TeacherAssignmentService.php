<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentService
{
    public function subjectsForCourseYear(Course $course, int $academicYear): Collection
    {
        $semestersPerYear = max(1, (int) $course->semesters_per_year);
        $from = (($academicYear - 1) * $semestersPerYear) + 1;
        $to = $academicYear * $semestersPerYear;

        return Subject::query()
            ->where('course_id', $course->id)
            ->whereBetween('semester_sequence', [$from, $to])
            ->orderBy('semester_sequence')
            ->orderBy('name')
            ->get();
    }

    public function saveAssignments(Course $course, int $academicYear, array $subjectTeacherMap, Collection $departmentTeachers): void
    {
        $subjects = $this->subjectsForCourseYear($course, $academicYear);
        $this->assertSemesterSubjectLimit($course, $academicYear, $subjects);
        $subjectIds = $subjects->pluck('id')->map(fn ($id) => (int) $id);
        $teacherIds = $departmentTeachers->pluck('id')->map(fn ($id) => (int) $id);

        foreach ($subjectIds as $subjectId) {
            $teacherId = (int) ($subjectTeacherMap[$subjectId] ?? 0);
            if (!$teacherId || !$teacherIds->contains($teacherId)) {
                $subjectName = $subjects->firstWhere('id', $subjectId)?->name ?? "ID {$subjectId}";
                throw ValidationException::withMessages([
                    "subject_teacher_map.{$subjectId}" => "Please select a valid department teacher for subject {$subjectName}.",
                ]);
            }
        }

        DB::transaction(function () use ($subjectIds, $subjectTeacherMap) {
            foreach ($subjectIds as $subjectId) {
                TeacherSubjectAssignment::updateOrCreate(
                    ['subject_id' => $subjectId],
                    [
                        'teacher_id' => (int) $subjectTeacherMap[$subjectId],
                        'semester_subject_id' => null,
                        'semester_id' => null,
                        'academic_session_id' => null,
                        'assigned_date' => now()->toDateString(),
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    public function semesterCountsForCourseYear(Course $course, int $academicYear): Collection
    {
        $subjects = $this->subjectsForCourseYear($course, $academicYear);
        return $subjects
            ->groupBy(fn ($subject) => (int) $subject->semester_sequence)
            ->map(fn (Collection $rows, int $semester) => [
                'semester' => $semester,
                'count' => $rows->count(),
            ])
            ->values();
    }

    private function assertSemesterSubjectLimit(Course $course, int $academicYear, Collection $subjects): void
    {
        $required = max(1, (int) config('timetable.subjects_per_semester', 8));
        $grouped = $subjects->groupBy(fn ($subject) => (int) $subject->semester_sequence);
        $semestersPerYear = max(1, (int) $course->semesters_per_year);
        $from = (($academicYear - 1) * $semestersPerYear) + 1;
        $to = $academicYear * $semestersPerYear;

        foreach (range($from, $to) as $semester) {
            $count = $grouped->get((int) $semester, collect())->count();
            if ($count !== $required) {
                throw ValidationException::withMessages([
                    'subjects' => "Semester {$semester} must contain exactly {$required} subjects. Current count: {$count}.",
                ]);
            }
        }
    }
}
