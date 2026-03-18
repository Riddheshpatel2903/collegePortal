<?php

namespace App\Http\Controllers\API;

use App\Models\Semester;

class SemesterApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Semester::class;
    }

    protected function withRelations(): array
    {
        return ['course'];
    }
}
