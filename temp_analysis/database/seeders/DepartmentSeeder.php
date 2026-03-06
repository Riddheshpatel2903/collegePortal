<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Computer Engineering' => 'Focuses on software engineering, AI, data, and systems.',
            'Mechanical Engineering' => 'Focuses on thermal, design, and manufacturing engineering.',
            'Civil Engineering' => 'Focuses on structural, transportation, and infrastructure engineering.',
            'Electrical Engineering' => 'Focuses on power systems, control, and electrical machines.',
            'IT Engineering' => 'Focuses on application development, cloud, and enterprise IT.',
        ];

        foreach ($departments as $name => $desc) {
            Department::updateOrCreate(['name' => $name], ['description' => $desc]);
        }
    }
}
