<?php

namespace App\Console\Commands\Timetable;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Console\Command;

class AuditTeachersCommand extends Command
{
    protected $signature = 'timetable:audit-teachers
                            {--course= : Filter by course ID}
                            {--unassigned : Show only subjects without teacher assignments}';

    protected $description = 'Audit teacher assignments for timetable subjects — flags unassigned or overloaded teachers.';

    public function handle(): int
    {
        $this->info('🔍 Auditing teacher assignments...');

        $subjectsQuery = Subject::query()->with(['course.department']);

        if ($courseId = $this->option('course')) {
            $subjectsQuery->where('course_id', (int) $courseId);
        }

        $subjects = $subjectsQuery->get();

        if ($subjects->isEmpty()) {
            $this->warn('No subjects found. Add subjects first via the Admin panel.');
            return self::FAILURE;
        }

        $assignedSubjectIds = TeacherSubjectAssignment::query()
            ->pluck('subject_id')
            ->map(fn($id) => (int) $id)
            ->unique();

        $unassigned = $subjects->filter(
            fn($s) => !$assignedSubjectIds->contains((int) $s->id)
        );

        $this->newLine();
        $this->table(
            ['Status', 'Subject', 'Course', 'Semester', 'Weekly Hours'],
            $subjects->map(function ($s) use ($assignedSubjectIds) {
                $assigned = $assignedSubjectIds->contains((int) $s->id);
                return [
                    $assigned ? '✅ Assigned' : '❌ Unassigned',
                    $s->name,
                    $s->course?->name ?? '—',
                    $s->semester_sequence ?? '—',
                    $s->weekly_hours ?? ($s->credits ?? '—'),
                ];
            })->when($this->option('unassigned'), fn($rows) => $rows->filter(fn($r) => str_starts_with($r[0], '❌')))
              ->values()
              ->toArray()
        );

        $this->newLine();
        $total    = $subjects->count();
        $assigned = $total - $unassigned->count();
        $this->info("Summary: {$assigned}/{$total} subjects have teacher assignments.");

        if ($unassigned->isNotEmpty()) {
            $this->warn("{$unassigned->count()} subject(s) need teacher assignment before generating a timetable.");
            return self::FAILURE;
        }

        $this->info('All subjects are assigned. You are ready to generate a timetable.');
        return self::SUCCESS;
    }
}
