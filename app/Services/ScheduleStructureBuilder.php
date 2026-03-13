<?php

namespace App\Services;

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
        
        // Add a buffer slot for breaks if needed or just return calculated
        // The requirement says "break slots if required"
        // Let's stick to the formula provided first.
        
        return [
            'slots_per_day' => max(4, $slotsPerDay), // Minimum 4 slots per day
            'working_days_count' => $workingDaysCount,
            'total_capacity' => $slotsPerDay * $workingDaysCount,
            'utilization' => $totalRequired > 0 ? ($totalRequired / ($slotsPerDay * $workingDaysCount)) : 0
        ];
    }
}
