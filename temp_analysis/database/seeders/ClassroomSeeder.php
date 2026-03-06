<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = [
            ['name' => 'A-101', 'capacity' => 40],
            ['name' => 'A-102', 'capacity' => 40],
            ['name' => 'A-201', 'capacity' => 60],
            ['name' => 'B-101', 'capacity' => 45],
            ['name' => 'B-202', 'capacity' => 55],
            ['name' => 'C-301', 'capacity' => 70],
            ['name' => 'Lab-1', 'capacity' => 30],
            ['name' => 'Lab-2', 'capacity' => 35],
            ['name' => 'Seminar Hall', 'capacity' => 120],
        ];

        foreach ($classrooms as $room) {
            Classroom::updateOrCreate(
                ['name' => $room['name']],
                ['capacity' => $room['capacity']]
            );
        }
    }
}
