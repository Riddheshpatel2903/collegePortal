<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Semester;
use App\Models\SemesterSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::query()->with('department')->get();
        if ($courses->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($courses) {
            foreach ($courses as $course) {
                $prefix = $this->departmentPrefix((string) $course->department?->name);
                $semesterMap = Semester::query()
                    ->where('course_id', $course->id)
                    ->get()
                    ->keyBy('semester_number');

                $yearSubjects = $this->yearWiseSubjects();
                foreach ($yearSubjects as $year => $subjectNames) {
                    foreach ($subjectNames as $index => $subjectName) {
                        $semesterNo = (($year - 1) * 2) + ($index < 3 ? 1 : 2);
                        $localNo = (($year - 1) * 6) + $index + 1;
                        $code = sprintf('%s%03d', $prefix, $localNo);

                        $subject = Subject::query()->updateOrCreate(
                            [
                                'course_id' => $course->id,
                                'semester_sequence' => $semesterNo,
                                'name' => "{$code} - {$subjectName}",
                            ],
                            [
                                'credits' => random_int(3, 4),
                                'weekly_hours' => random_int(3, 5),
                                'is_lab' => false,
                                'lab_block_hours' => null,
                            ]
                        );

                        $semester = $semesterMap->get($semesterNo);
                        if ($semester) {
                            SemesterSubject::query()->updateOrCreate(
                                [
                                    'semester_id' => $semester->id,
                                    'subject_id' => $subject->id,
                                ],
                                [
                                    'credits' => $subject->credits,
                                    'subject_type' => 'core',
                                    'is_mandatory' => true,
                                    'total_classes' => 60,
                                ]
                            );
                        }
                    }
                }
            }
        });
    }

    private function yearWiseSubjects(): array
    {
        return [
            1 => ['Engineering Mathematics I', 'Engineering Physics', 'Basic Electrical Engineering', 'Engineering Mathematics II', 'Engineering Chemistry', 'Programming Fundamentals'],
            2 => ['Data Structures', 'Digital Logic Design', 'Object Oriented Programming', 'Database Management Systems', 'Computer Organization', 'Discrete Mathematics'],
            3 => ['Operating Systems', 'Design and Analysis of Algorithms', 'Software Engineering', 'Computer Networks', 'Web Technology', 'Theory of Computation'],
            4 => ['Machine Learning', 'Cloud Computing', 'Cyber Security', 'Project Management', 'Major Project I', 'Major Project II'],
        ];
    }

    private function departmentPrefix(string $departmentName): string
    {
        return match ($departmentName) {
            'Computer Engineering' => 'CE',
            'Mechanical Engineering' => 'ME',
            'Civil Engineering' => 'CV',
            'Electrical Engineering' => 'EE',
            'IT Engineering' => 'IT',
            default => 'EN',
        };
    }
}

