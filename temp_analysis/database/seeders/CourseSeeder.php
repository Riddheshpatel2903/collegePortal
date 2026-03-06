<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Department;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::query()->updateOrCreate(
            ['name' => '2025-26'],
            [
                'start_year' => 2025,
                'end_year' => 2026,
                'session_start_date' => '2025-06-15',
                'session_end_date' => '2026-05-31',
                'is_current' => true,
                'status' => 'active',
            ]
        );

        $courseMap = [
            'Computer Engineering' => ['B.E. Computer Engineering', 'B.E. AI & Data Science'],
            'Mechanical Engineering' => ['B.E. Mechanical Engineering'],
            'Civil Engineering' => ['B.E. Civil Engineering', 'B.E. Infrastructure Engineering'],
            'Electrical Engineering' => ['B.E. Electrical Engineering'],
            'IT Engineering' => ['B.E. Information Technology', 'B.E. Software Engineering'],
        ];

        DB::transaction(function () use ($courseMap, $session) {
            foreach ($courseMap as $departmentName => $courseNames) {
                $department = Department::query()->where('name', $departmentName)->first();
                if (!$department) {
                    continue;
                }

                foreach ($courseNames as $courseName) {
                    $course = Course::query()->updateOrCreate(
                        ['department_id' => $department->id, 'name' => $courseName],
                        [
                            'duration_years' => 4,
                            'semesters_per_year' => 2,
                            'is_active' => true,
                        ]
                    );

                    for ($semesterNo = 1; $semesterNo <= 8; $semesterNo++) {
                        $year = (int) ceil($semesterNo / 2);
                        $isOdd = ($semesterNo % 2) === 1;
                        $start = $isOdd
                            ? now()->setDate($session->start_year + $year - 1, 6, 15)
                            : now()->setDate($session->start_year + $year - 1, 12, 1);
                        $end = $isOdd
                            ? now()->setDate($session->start_year + $year - 1, 11, 30)
                            : now()->setDate($session->start_year + $year, 5, 15);

                        Semester::query()->updateOrCreate(
                            [
                                'course_id' => $course->id,
                                'academic_session_id' => $session->id,
                                'semester_number' => $semesterNo,
                            ],
                            [
                                'name' => "Semester {$semesterNo}",
                                'start_date' => $start->toDateString(),
                                'end_date' => $end->toDateString(),
                                'is_current' => false,
                                'status' => 'upcoming',
                            ]
                        );
                    }
                }
            }
        });
    }
}

