<?php

namespace App\Http\Controllers\API;

use App\Models\Timetable;

class TimetableApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Timetable::class;
    }

    protected function withRelations(): array
    {
        return ['course', 'classroom', 'teacher'];
    }
}
