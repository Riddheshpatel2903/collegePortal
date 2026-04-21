<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AuditLog;
use App\Models\Result;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->select('id', 'role')->get();
        if ($users->isEmpty()) {
            return;
        }

        $studentIds = Student::query()->pluck('user_id')->all();
        $assignmentIds = Assignment::query()->pluck('id')->all();
        $scheduleIds = Schedule::query()->pluck('id')->all();
        $resultIds = Result::query()->pluck('id')->all();

        $actions = [
            ['login', 'GET', 'login', '/login'],
            ['dashboard.view', 'GET', 'dashboard', '/dashboard'],
            ['attendance.mark', 'POST', 'attendance.store', '/attendance'],
            ['assignment.upload', 'POST', 'assignments.submit', '/assignments/submit'],
            ['result.view', 'GET', 'results.show', '/results'],
            ['notice.view', 'GET', 'notices.index', '/notices'],
            ['timetable.view', 'GET', 'timetable.index', '/timetable'],
        ];

        $rows = [];
        $total = 50000;

        for ($i = 0; $i < $total; $i++) {
            $user = $users[random_int(0, $users->count() - 1)];
            [$action, $method, $route, $path] = $actions[array_rand($actions)];

            $meta = [];
            if ($action === 'assignment.upload' && $assignmentIds) {
                $meta['assignment_id'] = $assignmentIds[array_rand($assignmentIds)];
            } elseif ($action === 'attendance.mark' && $scheduleIds) {
                $meta['schedule_id'] = $scheduleIds[array_rand($scheduleIds)];
            } elseif ($action === 'result.view' && $resultIds) {
                $meta['result_id'] = $resultIds[array_rand($resultIds)];
            } elseif (in_array($action, ['dashboard.view', 'login'], true) && $studentIds) {
                $meta['student_user_id'] = $studentIds[array_rand($studentIds)];
            }

            $rows[] = [
                'user_id' => $user->id,
                'role' => $user->role,
                'action' => $action,
                'method' => $method,
                'route_name' => $route,
                'path' => $path,
                'status_code' => 200,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder-Fake-Agent',
                'meta' => $meta,
                'created_at' => now()->subDays(random_int(0, 60)),
                'updated_at' => now()->subDays(random_int(0, 60)),
            ];

            if (count($rows) >= 2000) {
                AuditLog::insert($rows);
                $rows = [];
            }
        }

        if ($rows) {
            AuditLog::insert($rows);
        }
    }
}
