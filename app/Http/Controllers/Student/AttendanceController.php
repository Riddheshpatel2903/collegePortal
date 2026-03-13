<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    public function index()
    {
        $student = Student::where('user_id', auth()->id())->first();

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $records = Attendance::query()
            ->with(['attendanceSession.subject'])
            ->where('student_id', $student->id)
            ->get();

        $totalCount = $records->count();
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $overallPercent = $totalCount > 0 ? round(($presentCount / $totalCount) * 100) : 0;

        $subjectWise = $records
            ->filter(fn ($record) => $record->attendanceSession?->subject)
            ->groupBy(fn ($record) => $record->attendanceSession->subject_id)
            ->map(function (Collection $group) {
                $total = $group->count();
                $present = $group->where('status', 'present')->count();
                $absent = $group->where('status', 'absent')->count();

                return [
                    'subject' => $group->first()->attendanceSession->subject,
                    'present' => $present,
                    'absent' => $absent,
                    'total' => $total,
                    'percent' => $total > 0 ? round(($present / $total) * 100) : 0,
                ];
            })
            ->values();

        $calendarData = [];
        foreach ($records as $record) {
            $date = optional($record->attendanceSession?->date)->format('Y-m-d');
            if (!$date) {
                continue;
            }
            $calendarData[$date][] = [
                'type' => 'attendance',
                'subject' => $record->attendanceSession?->subject?->name ?? 'N/A',
                'status' => $record->status,
            ];
        }

        $holidays = \App\Models\Holiday::all();
        foreach ($holidays as $holiday) {
            $date = \Carbon\Carbon::parse($holiday->date)->format('Y-m-d');
            $calendarData[$date][] = [
                'type' => 'holiday',
                'name' => $holiday->name,
                'description' => $holiday->description,
            ];
        }

        return view('student.attendance.index', compact(
            'totalCount',
            'presentCount',
            'absentCount',
            'overallPercent',
            'subjectWise',
            'calendarData'
        ))->with('calendarData', json_encode($calendarData));
    }
}
