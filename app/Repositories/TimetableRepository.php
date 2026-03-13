<?php

namespace App\Repositories;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Models\TeacherSubjectAssignment;
use App\Models\Timetable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class TimetableRepository
{
    public function findCourseOrFail(int $courseId): Course
    {
        return Course::query()->with('department:id,name')->findOrFail($courseId);
    }

    public function departmentTeachers(int $departmentId, ?array $onlyTeacherIds = null): EloquentCollection
    {
        return Teacher::query()
            ->with('user:id,name')
            ->where('department_id', $departmentId)
            ->when(!empty($onlyTeacherIds), fn ($q) => $q->whereIn('id', $onlyTeacherIds))
            ->orderBy('id')
            ->get();
    }

    public function yearsForCourse(Course $course): Collection
    {
        return collect(range(1, max(1, (int) $course->duration_years)));
    }

    public function semesterNumbersByType(Course $course, string $semesterType): Collection
    {
        $maxSemester = max(1, (int) $course->duration_years * 2);
        $seed = strtolower($semesterType) === 'even' ? [2, 4, 6, 8] : [1, 3, 5, 7];

        return collect($seed)->filter(fn (int $num) => $num <= $maxSemester)->values();
    }

    public function semesterForYearByType(int $year, string $semesterType): int
    {
        return strtolower($semesterType) === 'even'
            ? ($year * 2)
            : (($year * 2) - 1);
    }

    public function courseSubjectsForSemesters(int $courseId, array $semesterNumbers): EloquentCollection
    {
        return Subject::query()
            ->where('course_id', $courseId)
            ->whereIn('semester_number', $semesterNumbers)
            ->orderBy('semester_number')
            ->orderBy('id')
            ->get();
    }

    public function teacherMapForSubjects(Collection $subjectIds): Collection
    {
        $directMap = Subject::query()
            ->whereIn('id', $subjectIds)
            ->whereNotNull('teacher_id')
            ->pluck('teacher_id', 'id')
            ->map(fn ($teacherId) => (int) $teacherId);

        $assignmentMap = TeacherSubjectAssignment::query()
            ->whereIn('subject_id', $subjectIds)
            ->where('is_active', true)
            ->orderByDesc('assigned_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy('subject_id')
            ->map(fn (Collection $rows) => (int) $rows->first()->teacher_id);

        return $assignmentMap->replace($directMap->all());
    }

    public function lectureClassroomForCourseYear(int $courseId, int $yearNumber): ?Classroom
    {
        return $this->exactLectureClassroomForCourseYear($courseId, $yearNumber);
    }

    public function exactLectureClassroomForCourseYear(int $courseId, int $yearNumber): ?Classroom
    {
        return Classroom::query()
            ->where('course_id', $courseId)
            ->where('year_number', $yearNumber)
            ->where('type', 'lecture')
            ->orderBy('id')
            ->first();
    }

    public function lectureClassroomsPool(int $courseId): EloquentCollection
    {
        $courseRooms = Classroom::query()
            ->where('type', 'lecture')
            ->where('course_id', $courseId)
            ->orderByRaw('CASE WHEN year_number IS NULL THEN 1 ELSE 0 END')
            ->orderBy('year_number')
            ->orderBy('id')
            ->get();

        $globalRooms = Classroom::query()
            ->where('type', 'lecture')
            ->whereNull('course_id')
            ->orderBy('id')
            ->get();

        return $courseRooms->merge($globalRooms)->unique('id')->values();
    }

    public function ensureFixedLectureClassrooms(int $courseId, array $years): void
    {
        $years = collect($years)->map(fn ($year) => (int) $year)->unique()->values();
        if ($years->isEmpty()) {
            return;
        }

        $alreadyMapped = Classroom::query()
            ->where('type', 'lecture')
            ->where('course_id', $courseId)
            ->whereIn('year_number', $years->all())
            ->pluck('year_number')
            ->map(fn ($year) => (int) $year)
            ->all();

        foreach ($years as $year) {
            if (in_array((int) $year, $alreadyMapped, true)) {
                continue;
            }

            $room = Classroom::query()
                ->where('type', 'lecture')
                ->whereNull('course_id')
                ->whereNull('year_number')
                ->orderBy('id')
                ->first();

            if (!$room) {
                $room = Classroom::query()
                    ->where('type', 'lecture')
                    ->where('course_id', $courseId)
                    ->whereNull('year_number')
                    ->orderBy('id')
                    ->first();
            }

            if (!$room) {
                continue;
            }

            $room->course_id = $courseId;
            $room->year_number = (int) $year;
            $room->save();
        }
    }

    public function ensureLabClassrooms(int $courseId, int $minimum = 2): void
    {
        $minimum = max(1, $minimum);
        $existing = $this->labClassrooms($courseId);
        $count = $existing->count();

        while ($count < $minimum) {
            $room = Classroom::query()
                ->where('type', 'lecture')
                ->whereNull('course_id')
                ->whereNull('year_number')
                ->orderBy('id')
                ->first();

            if ($room) {
                $room->type = 'lab';
                $room->course_id = $courseId;
                $room->year_number = null;
                $room->save();
                $count++;
                continue;
            }

            $name = "LAB-{$courseId}-" . str_pad((string) ($count + 1), 2, '0', STR_PAD_LEFT);
            Classroom::query()->create([
                'name' => $name,
                'type' => 'lab',
                'course_id' => $courseId,
                'year_number' => null,
                'capacity' => 60,
            ]);
            $count++;
        }
    }

    public function lectureRoomFreeSlotCount(int $roomId, string $semesterType): int
    {
        $count = 0;
        $access = app(\App\Services\PortalAccessService::class);
        $days = $access->workingDays();
        $slots = range(1, $access->slotsPerDay());

        foreach ($slots as $slot) {
            foreach ($days as $day) {
                if (!$this->hasRoomConflict($roomId, $day, [$slot], $semesterType)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function bestUnassignedLectureRoom(string $semesterType, array $excludeRoomIds = []): ?Classroom
    {
        $rooms = Classroom::query()
            ->where('type', 'lecture')
            ->whereNull('course_id')
            ->whereNull('year_number')
            ->when(!empty($excludeRoomIds), fn ($q) => $q->whereNotIn('id', $excludeRoomIds))
            ->orderBy('id')
            ->get();

        if ($rooms->isEmpty()) {
            return null;
        }

        return $rooms
            ->map(fn ($room) => [
                'room' => $room,
                'free' => $this->lectureRoomFreeSlotCount((int) $room->id, $semesterType),
            ])
            ->sortByDesc('free')
            ->first()['room'] ?? null;
    }

    public function assignLectureRoomToCourseYear(int $roomId, int $courseId, int $yearNumber): void
    {
        Classroom::query()
            ->where('course_id', $courseId)
            ->where('year_number', $yearNumber)
            ->where('type', 'lecture')
            ->update([
                'course_id' => null,
                'year_number' => null,
            ]);

        Classroom::query()
            ->where('id', $roomId)
            ->where('type', 'lecture')
            ->update([
                'course_id' => $courseId,
                'year_number' => $yearNumber,
            ]);
    }

    public function labClassrooms(int $courseId): EloquentCollection
    {
        $courseSpecific = Classroom::query()
            ->where('type', 'lab')
            ->where('course_id', $courseId)
            ->orderBy('id')
            ->get();

        if ($courseSpecific->isNotEmpty()) {
            return $courseSpecific;
        }

        return Classroom::query()
            ->where('type', 'lab')
            ->whereNull('course_id')
            ->orderBy('id')
            ->get();
    }

    public function timetableByCourseAndSemesterType(int $courseId, string $semesterType): EloquentCollection
    {
        return Timetable::query()
            ->with(['subject', 'teacher.user', 'classroom'])
            ->where('course_id', $courseId)
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->orderBy('year_number')
            ->orderBy('day')
            ->orderBy('slot_number')
            ->orderBy('id')
            ->get();
    }

    public function timetableByTeacher(int $teacherId, ?string $semesterType = null): EloquentCollection
    {
        return Timetable::query()
            ->with(['subject', 'classroom', 'course'])
            ->where('teacher_id', $teacherId)
            ->when($semesterType, fn ($q) => $q->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)]))
            ->orderBy('day')
            ->orderBy('slot_number')
            ->get();
    }

    public function timetableByCourseYear(int $courseId, int $yearNumber, ?string $semesterType = null): EloquentCollection
    {
        return Timetable::query()
            ->with(['subject', 'teacher.user', 'classroom'])
            ->where('course_id', $courseId)
            ->where('year_number', $yearNumber)
            ->when($semesterType, fn ($q) => $q->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)]))
            ->orderBy('day')
            ->orderBy('slot_number')
            ->get();
    }

    public function otherTimetableBySemesterType(string $semesterType, ?int $excludeCourseId = null): EloquentCollection
    {
        return Timetable::query()
            ->with('subject:id,name')
            ->when($excludeCourseId, fn ($q) => $q->where('timetable.course_id', '!=', $excludeCourseId))
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->get();
    }

    public function clearCourseSemesterTypeYears(int $courseId, string $semesterType, array $years): void
    {
        Timetable::query()
            ->where('course_id', $courseId)
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->whereIn('year_number', $years)
            ->delete();
    }

    public function bulkInsert(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        Timetable::query()->insert($rows);
    }

    public function findTimetableOrFail(int $id): Timetable
    {
        return Timetable::query()
            ->with(['subject', 'teacher.user', 'classroom', 'course'])
            ->findOrFail($id);
    }

    public function rowsByLabBlock(string $labBlockId): EloquentCollection
    {
        return Timetable::query()
            ->where('lab_block_id', $labBlockId)
            ->orderBy('slot_number')
            ->get();
    }

    public function subjectOptionsForSemester(int $courseId, int $semesterNumber): EloquentCollection
    {
        return Subject::query()
            ->where('course_id', $courseId)
            ->where('semester_number', $semesterNumber)
            ->orderBy('name')
            ->get();
    }

    public function saveTimetableRow(Timetable $row): Timetable
    {
        $row->save();
        return $row->refresh()->load(['subject', 'teacher.user', 'classroom']);
    }

    public function saveMany(EloquentCollection $rows): void
    {
        $rows->each->save();
    }

    public function hasTeacherConflict(
        int $teacherId,
        string $day,
        array $slotNumbers,
        string $semesterType,
        array $ignoreIds = []
    ): bool {
        return Timetable::query()
            ->where('teacher_id', $teacherId)
            ->where('day', $day)
            ->whereIn('slot_number', $slotNumbers)
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->when(!empty($ignoreIds), fn ($q) => $q->whereNotIn('id', $ignoreIds))
            ->exists();
    }

    public function hasClassConflict(
        int $courseId,
        int $yearNumber,
        string $day,
        array $slotNumbers,
        string $semesterType,
        array $ignoreIds = []
    ): bool {
        return Timetable::query()
            ->where('course_id', $courseId)
            ->where('year_number', $yearNumber)
            ->where('day', $day)
            ->whereIn('slot_number', $slotNumbers)
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->when(!empty($ignoreIds), fn ($q) => $q->whereNotIn('id', $ignoreIds))
            ->exists();
    }

    public function hasRoomConflict(
        int $classroomId,
        string $day,
        array $slotNumbers,
        string $semesterType,
        array $ignoreIds = []
    ): bool {
        return Timetable::query()
            ->where('classroom_id', $classroomId)
            ->where('day', $day)
            ->whereIn('slot_number', $slotNumbers)
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->when(!empty($ignoreIds), fn ($q) => $q->whereNotIn('id', $ignoreIds))
            ->exists();
    }

    public function teacherDayLoad(
        int $teacherId,
        string $day,
        string $semesterType,
        array $ignoreIds = []
    ): int {
        return (int) Timetable::query()
            ->where('teacher_id', $teacherId)
            ->where('day', $day)
            ->whereRaw('MOD(semester_number, 2) = ?', [$this->semesterParity($semesterType)])
            ->when(!empty($ignoreIds), fn ($q) => $q->whereNotIn('id', $ignoreIds))
            ->count();
    }

    public function teacherAvailabilities(array $teacherIds): EloquentCollection
    {
        if (empty($teacherIds)) {
            return new EloquentCollection();
        }

        return TeacherAvailability::query()
            ->whereIn('teacher_id', $teacherIds)
            ->get();
    }

    public function semesterParity(string $semesterType): int
    {
        return strtolower($semesterType) === 'even' ? 0 : 1;
    }
}
