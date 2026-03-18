<?php

namespace App\Http\Controllers\API;

use App\Models\Attendance;

class AttendanceApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Attendance::class;
    }

    protected function withRelations(): array
    {
        return ['student', 'semesterSubject', 'attendanceSession'];
    }
}
