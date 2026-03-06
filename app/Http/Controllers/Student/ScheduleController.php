<?php

namespace App\Http\Controllers\Student;

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
        $student = auth()->user()->student;
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $phase = AcademicPhase::query()->where('is_active', true)->first();
        $semesterType = strcasecmp((string) ($phase?->phase_name ?? 'Odd'), 'Even') === 0 ? 'even' : 'odd';

        $schedules = $this->repository->timetableByCourseYear((int) $student->course_id, (int) $student->current_year, $semesterType);

        [$timeSlots, $grid] = $this->buildGrid($schedules);

        return view('student.schedule.index', [
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
