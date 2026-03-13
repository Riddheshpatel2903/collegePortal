<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\TeacherSubjectAssignment;
use App\Models\TeacherAvailability;

$subjects = Subject::where('course_id', 1)->where('semester_number', 1)->get();
foreach($subjects as $s) {
    echo "Subject: {$s->name} ({$s->id})\n";
    $assignments = TeacherSubjectAssignment::where('subject_id', $s->id)->get();
    foreach($assignments as $a) {
        $t = $a->teacher;
        $u = $t->user;
        echo "  - Teacher: {$u->name} (ID: {$t->id})\n";
        $avail = TeacherAvailability::where('teacher_id', $t->id)->get();
        if ($avail->isEmpty()) {
            echo "    - Availability: OPEN\n";
        } else {
            echo "    - Availability: " . $avail->count() . " records\n";
            foreach($avail as $av) {
                echo "      - {$av->day_of_week}: {$av->start_time}-{$av->end_time}\n";
            }
        }
    }
}
