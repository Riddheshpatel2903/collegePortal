<?php

namespace App\Observers;

use App\Models\Semester;
use App\Services\FeeService;

class SemesterObserver
{
    public function updated(Semester $semester): void
    {
        $becameActive = $semester->wasChanged('status') && $semester->status === 'active';
        $isActivatedFlag = $semester->wasChanged('is_active') && (bool) $semester->is_active;

        if ($becameActive || $isActivatedFlag) {
            $yearNumber = (int) max(1, ceil(($semester->semester_number ?? 1) / max(1, $semester->course?->semesters_per_year ?? 2)));
            app(FeeService::class)->applyFeesToAllStudentsInYear((int) $semester->course_id, $yearNumber);
        }
    }
}
