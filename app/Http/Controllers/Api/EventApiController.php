<?php

namespace App\Http\Controllers\API;

use App\Models\Event;

class EventApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Event::class;
    }

    protected function withRelations(): array
    {
        return ['createdBy'];
    }
}
