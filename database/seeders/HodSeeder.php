<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HodSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->orderBy('id')->get();
        if ($departments->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($departments) {
            foreach ($departments as $department) {
                $slug = Str::slug($department->name, '');
                $user = User::query()->updateOrCreate(
                    ['email' => "hod.{$slug}@college.edu"],
                    [
                        'name' => "Dr. {$department->name} HOD",
                        'password' => Hash::make('password'),
                        'role' => 'hod',
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]
                );

                $department->update(['hod_id' => $user->id]);
            }
        });
    }
}
