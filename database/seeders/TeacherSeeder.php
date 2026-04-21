<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->orderBy('id')->get();
        if ($departments->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($departments) {
            foreach ($departments as $department) {
                $count = random_int(5, 8);
                for ($i = 1; $i <= $count; $i++) {
                    $slug = Str::slug($department->name, '');
                    $email = "teacher.{$slug}.{$i}@college.edu";
                    $user = User::query()->updateOrCreate(
                        ['email' => $email],
                        [
                            'name' => fake()->name(),
                            'password' => Hash::make('password'),
                            'role' => 'teacher',
                            'status' => 'active',
                            'email_verified_at' => now(),
                        ]
                    );

                    Teacher::query()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'department_id' => $department->id,
                            'qualification' => fake()->randomElement(['M.E.', 'M.Tech', 'PhD']),
                            'phone' => fake()->numerify('9#########'),
                        ]
                    );
                }
            }
        });
    }
}
