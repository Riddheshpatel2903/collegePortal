<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Principal (Admin user)
            PrincipalSeeder::class,

            // 2. Departments
            DepartmentSeeder::class,

            // 3. HODs (1 per department)
            HodSeeder::class,

            // 4. Courses (under departments)
            CourseSeeder::class,

            // 5. Teachers (under department)
            TeacherSeeder::class,

            // 6. Subjects (per course per year)
            SubjectSeeder::class,

            // 7. Teacher-Subject Assignments
            TeacherSubjectAssignmentSeeder::class,

            // 8. Students
            StudentSeeder::class,

            // 9. Fee Structures
            FeeStructureSeeder::class,

            // 10. Student Fees
            StudentFeeSeeder::class,

            // 11. Internal Marks
            InternalMarksSeeder::class,

            // 12. Timetable
            TimetableSeeder::class,

            // 13. Leave Requests
            LeaveSeeder::class,

            // 14. Result Data (sample GTU import simulation)
            ResultSeeder::class,
        ]);
    }
}

