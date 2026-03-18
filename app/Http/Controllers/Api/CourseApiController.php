<?php

namespace App\Http\Controllers\API;

use App\Models\Course;

class CourseApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Course::class;
    }

    protected function withRelations(): array
    {
        return ['department', 'semesters', 'subjects'];
    }
}
