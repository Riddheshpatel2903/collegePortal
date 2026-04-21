<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Student;
use App\Models\TeacherSubjectAssignment;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;

        $assignments = TeacherSubjectAssignment::with(['subject.course'])
            ->where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->get();

        $subjects = $assignments->map(function ($assignment) {
            $subject = $assignment->subject;

            return (object) [
                'id' => $assignment->subject_id,
                'name' => $subject?->name ?? 'Subject',
                'semester_id' => (int) ($subject?->semester_sequence ?? 1),
                'semester' => (object) ['name' => 'Semester '.((int) ($subject?->semester_sequence ?? 1))],
            ];
        })->values();

        $studentsBySemester = [];
        foreach ($assignments as $assignment) {
            $subject = $assignment->subject;
            $course = $subject?->course;
            if (! $subject || ! $course) {
                continue;
            }

            $semesterNumber = (int) $subject->semester_sequence;
            $academicYear = (int) ceil($semesterNumber / max(1, (int) $course->semesters_per_year));
            Student::with('user')
                ->where('course_id', $course->id)
                ->where('current_year', $academicYear)
                ->get()
                ->each(function ($s) use (&$studentsBySemester, $semesterNumber) {
                    $studentsBySemester[$semesterNumber][] = [
                        'id' => $s->id,
                        'name' => $s->user->name ?? 'N/A',
                        'roll' => $s->roll_number ?? '',
                    ];
                });
        }

        $studentsBySemesterJson = json_encode($studentsBySemester);

        return view('teacher.attendance.index', compact('subjects', 'studentsBySemesterJson'));
    }

    public function sessions(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $teacher = auth()->user()->teacher;
        $allowed = TeacherSubjectAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->where('is_active', true)
            ->exists();

        abort_unless($allowed, 403, 'Unauthorized subject access.');

        $sessions = AttendanceSession::where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->withCount([
                'attendances',
                'attendances as present_count' => fn ($q) => $q->where('status', 'present'),
            ])
            ->get()
            ->map(fn ($s) => [
                'type' => 'session',
                'id' => $s->id,
                'date' => $s->date?->format('Y-m-d'),
                'total' => $s->attendances_count,
                'present' => $s->present_count,
                'absent' => $s->attendances_count - $s->present_count,
            ])
            ->toArray();

        $holidays = \App\Models\Holiday::whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->get()
            ->map(fn ($h) => [
                'type' => 'holiday',
                'name' => $h->name,
                'date' => \Carbon\Carbon::parse($h->date)->format('Y-m-d'),
                'is_recurring' => $h->is_recurring,
            ])
            ->toArray();

        $merged = array_merge($sessions, $holidays);

        return response()->json($merged);
    }

    public function show($id)
    {
        $teacher = auth()->user()->teacher;
        $session = AttendanceSession::with('attendances.student.user')
            ->where('teacher_id', $teacher->id)
            ->findOrFail($id);

        $records = $session->attendances->map(fn ($r) => [
            'student_id' => $r->student_id,
            'name' => $r->student?->user?->name ?? 'N/A',
            'roll' => $r->student?->roll_number ?? '',
            'status' => $r->status,
        ]);

        return response()->json($records);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent',
        ]);

        $teacher = auth()->user()->teacher;
        $allowed = TeacherSubjectAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $request->subject_id)
            ->where('is_active', true)
            ->exists();

        abort_unless($allowed, 403, 'Unauthorized subject access.');

        $date = \Carbon\Carbon::parse($request->date)->startOfDay();
        $today = \Carbon\Carbon::today();
        $minDate = $today->copy()->subDays(3);

        if ($date->gt($today) || $date->lt($minDate)) {
            return response()->json(['error' => 'Attendance can only be marked for today and the previous 3 days.'], 422);
        }

        $subject = \App\Models\Subject::with('course')->findOrFail($request->subject_id);
        $semesterNumber = (int) $subject->semester_sequence;
        $academicYear = (int) ceil($semesterNumber / max(1, (int) ($subject->course?->semesters_per_year ?? 2)));

        $session = AttendanceSession::firstOrCreate(
            [
                'subject_id' => $request->subject_id,
                'date' => $request->date,
            ],
            [
                'semester_subject_id' => null,
                'teacher_id' => $teacher->id,
                'course_id' => $subject->course_id,
                'academic_year' => $academicYear,
                'semester_number' => $semesterNumber,
                'start_time' => '09:00:00',
                'is_completed' => true,
            ]
        );

        foreach ($request->attendance as $studentId => $status) {
            Attendance::updateOrCreate(
                [
                    'attendance_session_id' => $session->id,
                    'student_id' => $studentId,
                ],
                ['status' => $status]
            );
        }

        return response()->json(['success' => true, 'session_id' => $session->id]);
    }
}
