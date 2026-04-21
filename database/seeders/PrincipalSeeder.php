<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PrincipalSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'principal@college.edu'],
            [
                'name' => 'Dr. Principal',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
    }
}
