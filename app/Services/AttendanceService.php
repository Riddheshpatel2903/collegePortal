<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\SemesterSubject;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Create attendance session
     */
    public function createSession(array $data)
    {
        return AttendanceSession::create($data);
    }

    /**
     * Mark attendance for students
     */
    public function markAttendance(AttendanceSession $session, array $studentAttendances)
    {
        return DB::transaction(function () use ($session, $studentAttendances) {
            $attendances = [];

            foreach ($studentAttendances as $studentId => $status) {
                $attendance = Attendance::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $status['status'],
                        'remarks' => $status['remarks'] ?? null,
                    ]
                );

                $attendances[] = $attendance;
            }

            $session->update(['is_completed' => true]);

            return $attendances;
        });
    }

    /**
     * Calculate attendance percentage for a student in a subject
     */
    public function calculateAttendancePercentage(Student $student, SemesterSubject $semesterSubject)
    {
        $totalSessions = AttendanceSession::where('semester_subject_id', $semesterSubject->id)
            ->where('is_completed', true)
            ->count();

        if ($totalSessions == 0) {
            return 0;
        }

        $attendedSessions = Attendance::where('student_id', $student->id)
            ->whereHas('attendanceSession', function ($query) use ($semesterSubject) {
                $query->where('semester_subject_id', $semesterSubject->id)
                    ->where('is_completed', true);
            })
            ->where('status', 'present')
            ->count();

        return round(($attendedSessions / $totalSessions) * 100, 2);
    }

    /**
     * Get attendance report for a student in current semester
     */
    public function getStudentAttendanceReport(Student $student)
    {
        $semesterSubjects = $student->currentSemester ? $student->currentSemester->semesterSubjects : collect();
        $report = [];

        foreach ($semesterSubjects as $semesterSubject) {
            $report[] = [
                'subject' => $semesterSubject->subject->name,
                'percentage' => $this->calculateAttendancePercentage($student, $semesterSubject),
                'total_classes' => AttendanceSession::where('semester_subject_id', $semesterSubject->id)
                    ->where('is_completed', true)
                    ->count(),
                'attended' => Attendance::where('student_id', $student->id)
                    ->whereHas('attendanceSession', function ($query) use ($semesterSubject) {
                        $query->where('semester_subject_id', $semesterSubject->id);
                    })
                    ->where('status', 'present')
                    ->count(),
            ];
        }

        return $report;
    }
}
