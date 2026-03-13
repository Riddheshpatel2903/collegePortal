<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PortalAccessService;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;

$portal = app(PortalAccessService::class);

echo "--- Timetable Settings ---\n";
echo "Slots per Day: " . $portal->slotsPerDay() . "\n";
echo "Working Days: " . implode(', ', $portal->workingDays()) . "\n";
echo "Slot Duration: " . $portal->slotDuration() . " mins\n";
echo "Total weekly slots available per class: " . (count($portal->workingDays()) * $portal->slotsPerDay()) . "\n\n";

$courses = Course::all();
foreach ($courses as $course) {
    echo "Course: {$course->name} (ID: {$course->id})\n";
    $subjects = Subject::where('course_id', $course->id)->get();
    foreach ($subjects as $subject) {
        $hours = $subject->weekly_hours ?? $subject->hours_per_week ?? 4;
        $type = !empty($subject->type) ? $subject->type : ($subject->is_lab ? 'Lab' : 'Theory');
        echo "  - {$subject->name} ({$type}): {$hours} hours/week\n";
        
        $assignments = TeacherSubjectAssignment::where('subject_id', $subject->id)->where('is_active', true)->get();
        foreach ($assignments as $a) {
            $t = Teacher::with('user')->find($a->teacher_id);
            echo "    - Teacher: " . ($t->user->name ?? 'Unknown') . " (ID: {$t->id})\n";
        }
    }
    echo "\n";
}
