<?php

namespace App\Http\Controllers\API;

use App\Models\Teacher;

class TeacherApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Teacher::class;
    }

    protected function withRelations(): array
    {
        return ['subjects', 'department', 'user'];
    }
}
