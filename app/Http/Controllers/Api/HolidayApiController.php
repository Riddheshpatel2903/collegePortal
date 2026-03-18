<?php

namespace App\Http\Controllers\API;

use App\Models\Holiday;

class HolidayApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Holiday::class;
    }

    protected function withRelations(): array
    {
        return [];
    }
}
