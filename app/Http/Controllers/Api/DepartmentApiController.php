<?php

namespace App\Http\Controllers\API;

use App\Models\Department;

class DepartmentApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Department::class;
    }

    protected function withRelations(): array
    {
        return ['courses', 'teachers'];
    }
}
