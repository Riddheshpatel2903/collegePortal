<?php

namespace Database\Seeders;

use App\Models\AcademicPhase;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::query()->with('department')->orderBy('id')->get();
        $session = AcademicSession::query()->where('is_current', true)->first();
        if ($courses->isEmpty() || ! $session) {
            return;
        }

        $phase = AcademicPhase::query()->where('is_active', true)->first();
        $phaseIndex = strcasecmp((string) ($phase?->phase_name ?? 'Odd'), 'Even') === 0 ? 2 : 1;

        DB::transaction(function () use ($courses, $session, $phaseIndex) {
            foreach ($courses as $course) {
                $studentCount = random_int(300, 500);
                $semesterMap = Semester::query()
                    ->where('course_id', $course->id)
                    ->where('academic_session_id', $session->id)
                    ->get()
                    ->keyBy('semester_number');

                for ($i = 1; $i <= $studentCount; $i++) {
                    $currentYear = random_int(1, 4);
                    $semesterNumber = (($currentYear - 1) * 2) + $phaseIndex;
                    $semesterNumber = max(1, min(8, $semesterNumber));
                    $semesterId = $semesterMap->get($semesterNumber)?->id;

                    $email = "student.{$course->id}.".str_pad((string) $i, 4, '0', STR_PAD_LEFT).'@college.edu';
                    $user = User::query()->updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => fake()->name(),
                            'password' => Hash::make('password'),
                            'role' => 'student',
                            'status' => 'active',
                            'email_verified_at' => now(),
                            'remember_token' => Str::random(10),
                        ]
                    );

                    $admissionYear = $session->start_year - ($currentYear - 1);
                    $gtuEnrollment = $this->generateEnrollmentNumber($course->id, $i);
                    $rollNumber = strtoupper($this->courseCode($course->name)).'-'.str_pad((string) $course->id, 2, '0', STR_PAD_LEFT).'-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);

                    Student::query()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'department_id' => $course->department_id,
                            'course_id' => $course->id,
                            'academic_session_id' => $session->id,
                            'current_semester_id' => $semesterId,
                            'current_semester_number' => $semesterNumber,
                            'current_year' => $currentYear,
                            'roll_number' => $rollNumber,
                            'gtu_enrollment_no' => $gtuEnrollment,
                            'registration_number' => 'REG'.$gtuEnrollment,
                            'admission_year' => $admissionYear,
                            'admission_date' => "{$admissionYear}-07-01",
                            'phone' => fake()->numerify('9#########'),
                            'address' => fake()->address(),
                            'gender' => fake()->randomElement(['male', 'female']),
                            'date_of_birth' => fake()->date('Y-m-d', '-17 years'),
                            'student_status' => 'active',
                            'academic_status' => 'active',
                            'is_active' => true,
                        ]
                    );

                }
            }
        });
    }

    private function generateEnrollmentNumber(int $courseId, int $serial): string
    {
        return '21'
            .'01201'
            .str_pad((string) ($courseId % 100), 2, '0', STR_PAD_LEFT)
            .str_pad((string) ($serial % 1000), 3, '0', STR_PAD_LEFT);
    }

    private function courseCode(string $courseName): string
    {
        $words = preg_split('/\s+/', strtoupper($courseName)) ?: [];
        $letters = '';
        foreach ($words as $word) {
            if (in_array($word, ['B.E.', 'BE', 'BTECH', 'B.TECH', 'OF', 'AND'], true)) {
                continue;
            }
            $letters .= substr($word, 0, 1);
            if (strlen($letters) >= 3) {
                break;
            }
        }

        return str_pad(substr($letters, 0, 3), 3, 'X');
    }
}
