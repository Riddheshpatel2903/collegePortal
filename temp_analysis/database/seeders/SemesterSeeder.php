<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            $semesterCount = $course->duration_years * 2;
            for ($i = 1; $i <= $semesterCount; $i++) {
                Semester::updateOrCreate([
                    'course_id' => $course->id,
                    'name' => "Semester $i",
                ], [
                    'year' => ceil($i / 2)
                ]);
            }
        }
    }
}