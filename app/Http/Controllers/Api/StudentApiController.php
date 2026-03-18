<?php

namespace App\Http\Controllers\API;

use App\Models\Student;

class StudentApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Student::class;
    }

    protected function withRelations(): array
    {
        return ['course', 'semester', 'department', 'academicSession'];
    }
}
