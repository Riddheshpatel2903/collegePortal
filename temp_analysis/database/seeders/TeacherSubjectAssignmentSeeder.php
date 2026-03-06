<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\SemesterSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSubjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $sessionId = AcademicSession::query()->where('is_current', true)->value('id');
        $semesterSubjects = SemesterSubject::query()
            ->with(['subject.course.department'])
            ->get();

        if ($semesterSubjects->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($semesterSubjects, $sessionId) {
            foreach ($semesterSubjects as $semesterSubject) {
                $departmentId = (int) ($semesterSubject->subject?->course?->department_id ?? 0);
                if (!$departmentId) {
                    continue;
                }

                $teacherId = Teacher::query()
                    ->where('department_id', $departmentId)
                    ->inRandomOrder()
                    ->value('id');

                if (!$teacherId) {
                    continue;
                }

                TeacherSubjectAssignment::query()->updateOrCreate(
                    ['subject_id' => $semesterSubject->subject_id],
                    [
                        'teacher_id' => $teacherId,
                        'semester_subject_id' => $semesterSubject->id,
                        'semester_id' => $semesterSubject->semester_id,
                        'academic_session_id' => $sessionId,
                        'assigned_date' => now()->toDateString(),
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}

