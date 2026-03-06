<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicPhase;
use App\Repositories\TimetableRepository;
use App\Services\PortalAccessService;
use Illuminate\Support\Collection;

class ScheduleController extends Controller
{
    public function __construct(
        private TimetableRepository $repository,
        private PortalAccessService $accessService
    ) {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $phase = AcademicPhase::query()->where('is_active', true)->first();
        $semesterType = strcasecmp((string) ($phase?->phase_name ?? 'Odd'), 'Even') === 0 ? 'even' : 'odd';

        $schedules = $this->repository->timetableByTeacher((int) $teacher->id, $semesterType);

        [$timeSlots, $grid] = $this->buildGrid($schedules);

        return view('teacher.schedule.index', [
            'schedules' => $schedules,
            'timeSlots' => $timeSlots,
            'grid' => $grid,
            'days' => $this->accessService->workingDays(),
        ]);
    }

    private function buildGrid(Collection $schedules): array
    {
        $timeSlots = $this->accessService->timeSlots();
        $days = $this->accessService->workingDays();

        $grid = [];
        foreach ($days as $day) {
            $grid[$day] = [];
            foreach ($timeSlots->values() as $index => $timeSlot) {
                $grid[$day][$timeSlot] = $schedules->first(
                    fn ($slot) => $slot->day === $day
                        && (int) $slot->slot_number === ($index + 1)
                );
            }
        }

        return [$timeSlots, $grid];
    }
}
