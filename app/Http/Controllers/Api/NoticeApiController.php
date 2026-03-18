<?php

namespace App\Http\Controllers\API;

use App\Models\Notice;

class NoticeApiController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Notice::class;
    }

    protected function withRelations(): array
    {
        return ['createdBy'];
    }
}
