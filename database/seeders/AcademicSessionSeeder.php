<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        AcademicSession::updateOrCreate(['name' => '2025-2026'], [
            'start_year' => 2025,
            'end_year' => 2026,
            'session_start_date' => Carbon::create(2025, 7, 1),
            'session_end_date' => Carbon::create(2026, 6, 30),
            'is_current' => true,
            'status' => 'active',
        ]);

        AcademicSession::updateOrCreate(['name' => '2024-2025'], [
            'start_year' => 2024,
            'end_year' => 2025,
            'session_start_date' => Carbon::create(2024, 7, 1),
            'session_end_date' => Carbon::create(2025, 6, 30),
            'is_current' => false,
            'status' => 'completed',
        ]);

        AcademicSession::updateOrCreate(['name' => '2026-2027'], [
            'start_year' => 2026,
            'end_year' => 2027,
            'session_start_date' => Carbon::create(2026, 7, 1),
            'session_end_date' => Carbon::create(2027, 6, 30),
            'is_current' => false,
            'status' => 'upcoming',
        ]);
    }
}
