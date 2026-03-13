<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Subject;

foreach(Course::where('id', 1)->get() as $c) {
    echo "Course: {$c->name}\n";
    foreach(range(1, 1) as $y) {
        $sems = [$y*2-1, $y*2];
        $subjects = Subject::where('course_id', $c->id)->whereIn('semester_number', $sems)->get();
        $load = $subjects->sum(fn($s) => $s->totalWeeklySlots());
        echo "  Year {$y}: {$load} hours\n";
        foreach($subjects as $s) {
            echo "    - {$s->name} ({$s->id}): L:{$s->lecture_hours} T:{$s->tutorial_hours} P:{$s->practical_hours} Total:{$s->totalWeeklySlots()}\n";
        }
    }
}
