<?php

namespace App\Http\Controllers\API;

use App\Models\Assignment;

class AssignmentApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Assignment::class;
    }

    protected function withRelations(): array
    {
        return ['teacher', 'course', 'semesterSubject'];
    }
}
