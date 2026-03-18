<?php

namespace App\Services;

use App\Models\Timetable;

class TimetableService
{
    public function generate(array $params)
    {
        // Placeholder for timetable generation logic
        return Timetable::create($params);
    }

    public function updateEntry(Timetable $entry, array $data)
    {
        $entry->update($data);
        return $entry;
    }
}
