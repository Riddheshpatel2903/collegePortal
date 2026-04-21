<?php

namespace Database\Seeders;

use App\Models\AcademicPhase;
use App\Models\AcademicSession;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Department;
use App\Models\FeeStructure;
use App\Models\Leave;
use App\Models\Notice;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\SemesterSubject;
use App\Models\Student;
use App\Models\StudentFee;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\AutoTimetableService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DevelopmentDemoPortalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $admin = $this->seedAdmin();
            $session = $this->seedAcademicSession();
            $this->seedAcademicPhase();

            $ceDept = Department::query()->firstOrCreate(
                ['name' => 'Computer Engineering'],
                ['description' => 'Demo department for development seeding']
            );
            $meDept = Department::query()->firstOrCreate(
                ['name' => 'Mechanical Engineering'],
                ['description' => 'Demo department for development seeding']
            );

            $ceCourse = $this->upsertCourse($ceDept->id, 'Computer Engineering', 4);
            $meCourse = $this->upsertCourse($meDept->id, 'Mechanical Engineering', 3);

            $ceSemesters = $this->seedSemesters($ceCourse, $session, 4);
            $meSemesters = $this->seedSemesters($meCourse, $session, 3);

            $ceRooms = $this->seedClassrooms($ceCourse, 'CE', 4);
            $meRooms = $this->seedClassrooms($meCourse, 'ME', 3);

            $this->cleanupExcessDemoTeacherUsers('ce', 4);
            $this->cleanupExcessDemoTeacherUsers('me', 3);

            $ceTeachers = $this->seedTeachers($ceDept->id, 'ce', 3);
            $ceSupportTeachers = $this->seedTeachers($ceDept->id, 'ce', 1, 4, 'inactive');
            $meTeachers = $this->seedTeachers($meDept->id, 'me', 2);
            $meSupportTeachers = $this->seedTeachers($meDept->id, 'me', 1, 3, 'inactive');
            $ceSchedulingTeachers = $ceTeachers->concat($ceSupportTeachers)->values();
            $meSchedulingTeachers = $meTeachers->concat($meSupportTeachers)->values();

            $ceStudents = $this->seedStudents($ceCourse, $ceDept->id, $session->id, 'ce', 4, $ceSemesters);
            $meStudents = $this->seedStudents($meCourse, $meDept->id, $session->id, 'me', 3, $meSemesters);

            [$ceSubjects, $ceAssignments] = $this->seedSubjectsForCourse($ceCourse, $ceSemesters, $ceSchedulingTeachers, 'CE');
            [$meSubjects, $meAssignments] = $this->seedSubjectsForCourse($meCourse, $meSemesters, $meSchedulingTeachers, 'ME');

            $this->seedFees($ceCourse, $ceStudents, $ceSemesters, 85000, $admin->id);
            $this->seedFees($meCourse, $meStudents, $meSemesters, 78000, $admin->id);

            $this->seedNotices($admin->id, $ceCourse->id, $meCourse->id, $ceDept->id, $meDept->id);
            $this->seedLeaves($admin->id, $ceStudents->first(), $meStudents->first(), $ceTeachers->first(), $meTeachers->first());

            $this->generateTimetableAndSyncLegacySchedules($ceCourse, $ceSchedulingTeachers, $ceRooms['labs']);
            $this->generateTimetableAndSyncLegacySchedules($meCourse, $meSchedulingTeachers, $meRooms['labs']);

            if ($ceSubjects->isEmpty() || $meSubjects->isEmpty() || $ceAssignments->isEmpty() || $meAssignments->isEmpty()) {
                throw new RuntimeException('Demo subject/assignment seeding failed.');
            }
        });
    }

    private function seedAdmin(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'demo.admin@college.test'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }

    private function seedAcademicSession(): AcademicSession
    {
        AcademicSession::query()->where('is_current', true)->update(['is_current' => false]);

        return AcademicSession::query()->updateOrCreate(
            ['name' => '2026-2027'],
            [
                'start_year' => 2026,
                'end_year' => 2027,
                'session_start_date' => '2026-06-01',
                'session_end_date' => '2027-05-31',
                'is_current' => true,
                'status' => 'active',
            ]
        );
    }

    private function seedAcademicPhase(): void
    {
        AcademicPhase::query()->updateOrCreate(['phase_name' => 'Odd'], ['is_active' => true]);
        AcademicPhase::query()->updateOrCreate(['phase_name' => 'Even'], ['is_active' => false]);
    }

    private function upsertCourse(int $departmentId, string $name, int $durationYears): Course
    {
        return Course::query()->updateOrCreate(
            ['department_id' => $departmentId, 'name' => $name],
            [
                'duration_years' => $durationYears,
                'semesters_per_year' => 2,
                'is_active' => true,
            ]
        );
    }

    private function seedSemesters(Course $course, AcademicSession $session, int $durationYears): array
    {
        $map = [];
        $total = $durationYears * 2;
        for ($n = 1; $n <= $total; $n++) {
            $start = now()->startOfYear()->addMonths(($n - 1) * 2)->toDateString();
            $end = now()->startOfYear()->addMonths(($n * 2))->subDay()->toDateString();

            $semester = Semester::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'academic_session_id' => $session->id,
                    'semester_number' => $n,
                ],
                [
                    'name' => "Semester {$n}",
                    'start_date' => $start,
                    'end_date' => $end,
                    'is_current' => $n === 1,
                    'status' => 'active',
                ]
            );

            $map[$n] = $semester;
        }

        return $map;
    }

    private function seedClassrooms(Course $course, string $prefix, int $years): array
    {
        $lecture = [];
        for ($year = 1; $year <= $years; $year++) {
            $lecture[$year] = Classroom::query()->updateOrCreate(
                ['name' => "{$prefix}-Y{$year}-CR"],
                [
                    'capacity' => 60,
                    'type' => 'lecture',
                    'course_id' => $course->id,
                    'year_number' => $year,
                ]
            );
        }

        $labs = collect();
        for ($i = 1; $i <= 3; $i++) {
            $labs->push(Classroom::query()->updateOrCreate(
                ['name' => "{$prefix}-LAB-{$i}"],
                ['capacity' => 40, 'type' => 'lab', 'course_id' => $course->id, 'year_number' => null]
            ));
        }

        return ['lecture' => $lecture, 'labs' => $labs];
    }

    private function seedTeachers(int $departmentId, string $prefix, int $count, int $startIndex = 1, string $status = 'active')
    {
        $teachers = collect();
        for ($i = $startIndex; $i < $startIndex + $count; $i++) {
            $user = User::query()->updateOrCreate(
                ['email' => "{$prefix}.teacher{$i}@college.test"],
                [
                    'name' => strtoupper($prefix)." Teacher {$i}",
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'status' => $status,
                ]
            );

            $teacher = Teacher::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $departmentId,
                    'qualification' => 'M.Tech',
                    'phone' => '99999'.str_pad((string) $i, 5, '0', STR_PAD_LEFT),
                    'max_lectures_per_day' => 6,
                ]
            );

            $teachers->push($teacher);
        }

        return $teachers;
    }

    private function cleanupExcessDemoTeacherUsers(string $prefix, int $maxIndex): void
    {
        User::query()
            ->where('email', 'like', "{$prefix}.teacher%@college.test")
            ->get(['id', 'email'])
            ->each(function (User $user) use ($maxIndex) {
                if (! preg_match('/teacher(\d+)@college\.test$/', $user->email, $matches)) {
                    return;
                }

                if ((int) ($matches[1] ?? 0) > $maxIndex) {
                    $user->delete();
                }
            });
    }

    private function seedStudents(Course $course, int $departmentId, int $sessionId, string $prefix, int $years, array $semesters)
    {
        $students = collect();
        for ($year = 1; $year <= $years; $year++) {
            $semesterNumber = (($year - 1) * 2) + 1;
            $semester = $semesters[$semesterNumber];
            $user = User::query()->updateOrCreate(
                ['email' => "{$prefix}.student{$year}@college.test"],
                [
                    'name' => strtoupper($prefix)." Student Y{$year}",
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'status' => 'active',
                ]
            );

            $student = Student::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $departmentId,
                    'course_id' => $course->id,
                    'academic_session_id' => $sessionId,
                    'current_year' => $year,
                    'current_semester_id' => $semester->id,
                    'current_semester_number' => $semesterNumber,
                    'roll_number' => strtoupper($prefix).'-Y'.$year.'-001',
                    'registration_number' => strtoupper($prefix).'-REG-'.$year,
                    'gtu_enrollment_no' => strtoupper($prefix).'-ENR-'.$year,
                    'phone' => '88888'.str_pad((string) $year, 5, '0', STR_PAD_LEFT),
                    'admission_year' => now()->year,
                    'admission_date' => now()->toDateString(),
                    'date_of_birth' => now()->subYears(18 + $year)->toDateString(),
                    'gender' => 'male',
                    'address' => 'Demo Address',
                    'cgpa' => 0,
                    'cpi' => 0,
                    'backlog_count' => 0,
                    'student_status' => 'active',
                    'academic_status' => 'active',
                    'is_active' => true,
                ]
            );

            $students->push($student);
        }

        return $students;
    }

    private function seedSubjectsForCourse(Course $course, array $semesters, $teachers, string $prefix): array
    {
        $subjectTemplates = [
            ['Theory-A', 'lecture', 8, null],
            ['Theory-B', 'lecture', 8, null],
            ['Theory-C', 'lecture', 6, null],
            ['Lab-A', 'lab', 4, 2],
            ['Lab-B', 'lab', 4, 2],
        ];

        $subjects = collect();
        $assignments = collect();
        $teacherIndex = 0;

        for ($year = 1; $year <= (int) $course->duration_years; $year++) {
            $semesterNumber = (($year - 1) * 2) + 1;
            $semester = $semesters[$semesterNumber];

            foreach ($subjectTemplates as $idx => [$label, $type, $hours, $labDuration]) {
                $teacher = $teachers[$teacherIndex % $teachers->count()];
                $teacherIndex++;

                $subject = Subject::query()->updateOrCreate(
                    ['course_id' => $course->id, 'semester_number' => $semesterNumber, 'name' => "{$prefix}{$year} {$label}"],
                    [
                        'semester_sequence' => $semesterNumber,
                        'type' => $type,
                        'hours_per_week' => $hours,
                        'teacher_id' => $teacher->id,
                        'lab_duration' => $type === 'lab' ? $labDuration : null,
                        'weekly_hours' => $hours,
                        'is_lab' => $type === 'lab',
                        'lab_block_hours' => $type === 'lab' ? $labDuration : null,
                        'credits' => $hours,
                    ]
                );
                $subjects->push($subject);

                $semesterSubject = SemesterSubject::query()->updateOrCreate(
                    ['semester_id' => $semester->id, 'subject_id' => $subject->id],
                    [
                        'credits' => $hours,
                        'subject_type' => $type === 'lab' ? 'practical' : 'core',
                        'is_mandatory' => true,
                        'total_classes' => 60,
                    ]
                );

                $assignment = TeacherSubjectAssignment::query()->updateOrCreate(
                    ['teacher_id' => $teacher->id, 'subject_id' => $subject->id],
                    [
                        'semester_subject_id' => $semesterSubject->id,
                        'semester_id' => $semester->id,
                        'academic_session_id' => $semester->academic_session_id,
                        'assigned_date' => now()->toDateString(),
                        'is_active' => true,
                    ]
                );
                $assignments->push($assignment);
            }
        }

        return [$subjects, $assignments];
    }

    private function seedFees(Course $course, $students, array $semesters, int $baseAmount, int $collectorUserId): void
    {
        foreach ($students as $index => $student) {
            $semesterNumber = (($student->current_year - 1) * 2) + 1;
            $semester = $semesters[$semesterNumber];

            $feeStructure = FeeStructure::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'semester_number' => $semesterNumber,
                    'fee_type' => 'tuition',
                ],
                [
                    'year_number' => $student->current_year,
                    'semester_sequence' => $semesterNumber,
                    'amount' => $baseAmount,
                    'is_mandatory' => true,
                    'description' => 'Demo semester tuition fee',
                    'is_active' => true,
                ]
            );

            $paidAmount = $index % 2 === 0 ? $baseAmount : (int) ($baseAmount * 0.4);
            $status = $paidAmount >= $baseAmount ? 'paid' : 'pending';

            $studentFee = StudentFee::query()->updateOrCreate(
                ['student_id' => $student->id, 'fee_structure_id' => $feeStructure->id],
                [
                    'semester_id' => $semester->id,
                    'academic_year' => $student->current_year,
                    'total_amount' => $baseAmount,
                    'paid_amount' => $paidAmount,
                    'status' => $status,
                    'due_date' => now()->addDays(30)->toDateString(),
                    'last_payment_date' => $paidAmount > 0 ? now()->toDateString() : null,
                ]
            );

            if ($paidAmount > 0) {
                Payment::query()->updateOrCreate(
                    ['receipt_number' => 'DEMO-RCT-'.$course->id.'-'.$student->id],
                    [
                        'student_fee_id' => $studentFee->id,
                        'student_id' => $student->id,
                        'amount' => $paidAmount,
                        'payment_date' => now()->toDateString(),
                        'payment_mode' => 'upi',
                        'transaction_id' => 'DEMO-TXN-'.$student->id,
                        'remarks' => 'Demo payment',
                        'collected_by' => $collectorUserId,
                    ]
                );
            }
        }
    }

    private function seedNotices(int $postedBy, int $ceCourseId, int $meCourseId, int $ceDeptId, int $meDeptId): void
    {
        $notices = [
            ['Campus Orientation', 'Global orientation schedule for all students.', 'all', 'all', null, null],
            ['Faculty Meeting', 'All teachers must attend the monthly review.', 'teacher', 'teachers', null, null],
            ['CE Lab Upgrade', 'Computer Engineering lab maintenance this weekend.', 'student', 'specific_course', $ceCourseId, $ceDeptId],
            ['ME Workshop', 'Mechanical workshop safety guidelines updated.', 'student', 'specific_course', $meCourseId, $meDeptId],
            ['Holiday Notice', 'Institute will remain closed on upcoming festival date.', 'all', 'all', null, null],
        ];

        foreach ($notices as $index => [$title, $content, $targetRole, $noticeFor, $courseId, $departmentId]) {
            Notice::query()->updateOrCreate(
                ['title' => $title],
                [
                    'content' => $content,
                    'posted_by' => $postedBy,
                    'target_role' => $targetRole,
                    'notice_for' => $noticeFor,
                    'course_id' => $courseId,
                    'department_id' => $departmentId,
                    'priority' => $index % 2 === 0 ? 'medium' : 'high',
                    'is_active' => true,
                    'expiry_date' => now()->addDays(30)->toDateString(),
                    'created_at' => now()->subDays(5 - $index),
                    'updated_at' => now()->subDays(5 - $index),
                ]
            );
        }
    }

    private function seedLeaves(int $approverId, Student $studentA, Student $studentB, Teacher $teacherA, Teacher $teacherB): void
    {
        $rows = [
            [$studentA->getMorphClass(), $studentA->id, 'sick', 'Fever and recovery', 'pending', 'student', null],
            [$teacherA->getMorphClass(), $teacherA->id, 'casual', 'Personal work', 'pending', 'teacher', null],
            [$studentB->getMorphClass(), $studentB->id, 'emergency', 'Family emergency', 'approved', 'student', $approverId],
            [$teacherB->getMorphClass(), $teacherB->id, 'other', 'Travel request', 'rejected', 'teacher', $approverId],
        ];

        foreach ($rows as $index => [$type, $id, $leaveType, $reason, $status, $requestedByRole, $approvedBy]) {
            Leave::query()->updateOrCreate(
                ['leaveable_type' => $type, 'leaveable_id' => $id, 'reason' => $reason],
                [
                    'start_date' => now()->addDays($index + 1)->toDateString(),
                    'end_date' => now()->addDays($index + 2)->toDateString(),
                    'leave_type' => $leaveType,
                    'requested_by_role' => $requestedByRole,
                    'status' => $status,
                    'current_stage' => in_array($status, ['approved', 'rejected'], true) ? 'closed' : 'hod_review',
                    'approved_by' => $approvedBy,
                    'approval_remarks' => $status === 'approved' ? 'Approved for demo' : ($status === 'rejected' ? 'Rejected for demo' : null),
                    'approved_at' => in_array($status, ['approved', 'rejected'], true) ? now() : null,
                    'applied_at' => now()->subDays(2),
                ]
            );
        }
    }

    private function generateTimetableAndSyncLegacySchedules(Course $course, $teachers, $labRooms): void
    {
        $service = app(AutoTimetableService::class);
        $years = collect(range(1, (int) $course->duration_years));
        $result = $service->generate([
            'course_id' => $course->id,
            'semester_type' => 'odd',
            'selected_years' => $years->all(),
            'selected_teacher_ids' => $teachers->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'selected_classroom_ids' => $labRooms->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);

        if (($result['generated_count'] ?? 0) <= 0) {
            throw new RuntimeException("Timetable generation failed for {$course->name} demo course.");
        }

        $subjectIds = Subject::query()->where('course_id', $course->id)->pluck('id');
        if ($subjectIds->isNotEmpty()) {
            Schedule::query()->whereIn('subject_id', $subjectIds)->delete();
        }

        $oddSemesters = collect(range(1, $course->duration_years * 2))->filter(fn ($n) => $n % 2 === 1)->values();
        $semesterMap = Semester::query()
            ->where('course_id', $course->id)
            ->whereIn('semester_number', $oddSemesters)
            ->pluck('id', 'semester_number');

        $slotBlocks = config('timetable.slot_blocks', []);
        $timetableRows = Timetable::query()
            ->where('course_id', $course->id)
            ->whereIn('semester_number', $oddSemesters)
            ->whereIn('day', config('timetable.working_days', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']))
            ->get();

        foreach ($timetableRows as $row) {
            $slot = $slotBlocks[(int) $row->slot_number - 1] ?? null;
            if (! $slot) {
                continue;
            }
            Schedule::query()->create([
                'semester_id' => $semesterMap[(int) $row->semester_number] ?? null,
                'subject_id' => $row->subject_id,
                'teacher_id' => $row->teacher_id,
                'classroom_id' => $row->classroom_id,
                'day_of_week' => $row->day,
                'start_time' => $slot[0].':00',
                'end_time' => $slot[1].':00',
            ]);
        }
    }
}
