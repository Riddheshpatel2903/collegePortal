<?php

namespace App\Observers;

use App\Models\Student;
use App\Services\FeeService;

class StudentObserver
{
    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    /**
     * Handle the Student "created" event.
     */
    public function created(Student $student)
    {
        // Year-based academic initialization
        if (! $student->current_year) {
            $student->update([
                'current_year' => 1,
                'academic_status' => 'active',
                'student_status' => 'active',
            ]);
        }

        // Auto-apply year fee on admission
        $this->feeService->applyYearFeeToStudent($student, (int) $student->current_year);
    }
}
