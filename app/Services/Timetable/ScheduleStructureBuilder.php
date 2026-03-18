<?php

namespace App\Services\Timetable;

use App\Services\PortalAccessService;
use Illuminate\Support\Collection;

class ScheduleStructureBuilder
{
    public function __construct(private PortalAccessService $accessService)
    {
    }

    /**
     * Calculate timetable structure based on curriculum analysis.
     */
    public function build(array $analysis, int $workingDaysCount = 0): array
    {
        if ($workingDaysCount === 0) {
            $workingDaysCount = count($this->accessService->workingDays());
        }
        $workingDaysCount = max(1, $workingDaysCount);

        $totalRequired = $analysis['total_required_slots'];

        // slots_per_day = ceil(total_required_slots_per_week / working_days)
        $slotsPerDay = (int) ceil($totalRequired / $workingDaysCount);

        return [
            'slots_per_day' => max(4, $slotsPerDay), // Minimum 4 slots per day
            'working_days_count' => $workingDaysCount,
            'total_capacity' => $slotsPerDay * $workingDaysCount,
            'utilization' => $totalRequired > 0 ? ($totalRequired / ($slotsPerDay * $workingDaysCount)) : 0
        ];
    }
}
