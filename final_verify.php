<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AutoTimetableService;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    // 1. Setup
    $course = Course::create(['name' => 'Demo Section Support', 'duration_years' => 4, 'department_id' => 1]);
    
    // 2. 3 Sections, 18 hrs each + 2 hrs common = 56 hrs total
    $subjectsData = [
        ['name' => 'Math-A', 'hours_per_week' => 6], ['name' => 'Phys-A', 'hours_per_week' => 6], ['name' => 'Chem-A', 'hours_per_week' => 6],
        ['name' => 'Math-B', 'hours_per_week' => 6], ['name' => 'Phys-B', 'hours_per_week' => 6], ['name' => 'Chem-B', 'hours_per_week' => 6],
        ['name' => 'Math-C', 'hours_per_week' => 6], ['name' => 'Phys-C', 'hours_per_week' => 6], ['name' => 'Chem-C', 'hours_per_week' => 6],
        ['name' => 'Common Seminar', 'hours_per_week' => 2],
    ];
    
    $teachers = [];
    for ($i = 0; $i < 10; $i++) {
        $user = \App\Models\User::create(['name' => "T-$i", 'email' => "t$i-" . uniqid() . "@example.com", 'password' => 'pass', 'role' => 'teacher']);
        $teachers[] = Teacher::create(['user_id' => $user->id, 'department_id' => 1, 'max_lectures_per_day' => 6, 'qualification' => 'PhD']);
    }

    foreach ($subjectsData as $index => $s) {
        $sub = Subject::create([
            'name' => $s['name'],
            'type' => 'lab', // use lab to utilize room pool
            'hours_per_week' => $s['hours_per_week'],
            'course_id' => $course->id,
            'semester_number' => 1,
            'semester_sequence' => 1,
            'is_lab' => true,
            'lab_duration' => 1
        ]);
        
        \App\Models\TeacherSubjectAssignment::create([
            'teacher_id' => $teachers[$index]->id,
            'subject_id' => $sub->id,
            'is_active' => true,
            'assigned_date' => now()
        ]);
    }
    
    for ($i = 0; $i < 5; $i++) {
        Classroom::create(['name' => "Room-$i", 'type' => 'lab', 'capacity' => 60]);
    }

    $lectureRoom = Classroom::create([
        'name' => "Fixed Lecture", 
        'type' => 'lecture', 
        'capacity' => 60,
        'course_id' => $course->id,
        'year_number' => 1
    ]);

    // 3. Run
    $service = app(AutoTimetableService::class);
    echo "Starting section-aware generation test (56 hours needed in 30-hour week)...\n";
    $result = $service->generate([
        'course_id' => $course->id,
        'semester_type' => 'odd',
        'selected_years' => [1]
    ]);
    echo "SUCCESS: Placed " . $result['generated_count'] . " slots!\n";

} catch (\Exception $e) {
    echo "FAIL: " . $e->getMessage() . "\n";
} finally {
    DB::rollBack();
}
