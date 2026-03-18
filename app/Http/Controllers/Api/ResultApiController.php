<?php

namespace App\Http\Controllers\API;

use App\Models\Result;

class ResultApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Result::class;
    }

    protected function withRelations(): array
    {
        return ['student', 'resultSubjects', 'course', 'semester'];
    }
}
