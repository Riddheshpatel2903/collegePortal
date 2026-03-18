<?php

namespace App\Http\Controllers\API;

use App\Models\Subject;

class SubjectApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Subject::class;
    }

    protected function withRelations(): array
    {
        return ['course', 'semester'];
    }
}
