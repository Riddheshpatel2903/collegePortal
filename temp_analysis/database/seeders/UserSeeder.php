<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin1@college.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Teachers
        $teachers = [
            ['Dr. Anil Sharma', 'anil@college.com'],
            ['Prof. Rakesh Mehta', 'rakesh@college.com'],
            ['Dr. Pooja Patel', 'pooja@college.com'],
        ];

        foreach ($teachers as $t) {
            User::firstOrCreate(
                ['email' => $t[1]],
                [
                    'name' => $t[0],
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                ]
            );
        }

        // Students
        $students = [
            'Amit Patel',
            'Neha Shah',
            'Rahul Verma',
            'Priya Desai',
            'Karan Joshi',
            'Sneha Iyer',
            'Vikas Malhotra',
            'Riya Kapoor',
            'Abhishek Gupta',
            'Ishani Rao',
            'Manish Pandey',
            'Ananya Singh',
            'Rohan Mehra',
            'Tanya Choudhary',
            'Siddharth Reddy',
            'Kriti Sanon',
            'Aakash Bansal',
            'Megha Agarwal',
        ];

        foreach ($students as $name) {
            $email = strtolower(str_replace(' ', '', $name)) . '@college.com';

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                ]
            );
        }
    }
}