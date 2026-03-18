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
            'Electronics & Communication' => 'Focuses on communication systems, VLSI, and embedded design.',
            'Artificial Intelligence' => 'Focuses on machine learning, deep learning, and data-driven systems.',
            'Data Science' => 'Focuses on big data, analytics, and business intelligence.',
            'Management Studies' => 'MBA and related management programs.',
            'Computer Applications' => 'BCA and MCA programs focused on application development.',
        ];

        foreach ($departments as $name => $desc) {
            Department::updateOrCreate(['name' => $name], ['description' => $desc]);
        }
    }
}
