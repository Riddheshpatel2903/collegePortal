<?php

namespace App\Http\Controllers\API;

use App\Models\FeeStructure;

class FeeApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return FeeStructure::class;
    }

    protected function withRelations(): array
    {
        return ['course', 'department'];
    }
}
