<?php

namespace App\Observers;

use App\Models\Course;
class CourseObserver
{
    // Year-based mode: semester values are derived dynamically; no semester master generation.
    public function created(Course $course): void {}
    public function updated(Course $course): void {}
}
