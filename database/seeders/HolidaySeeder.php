<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) now()->year;

        $holidays = [
            ['New Year\'s Day', "{$year}-01-01"],
            ['Republic Day', "{$year}-01-26"],
            ['Mahashivratri', "{$year}-03-08"],
            ['Holi', "{$year}-03-26"],
            ['Good Friday', "{$year}-04-03"],
            ['Id-ul-Fitr', "{$year}-04-21"],
            ['Independence Day', "{$year}-08-15"],
            ['Janmashtami', "{$year}-08-26"],
            ['Ganesh Chaturthi', "{$year}-09-09"],
            ['Gandhi Jayanti', "{$year}-10-02"],
            ['Dussehra', "{$year}-10-20"],
            ['Diwali Break Start', "{$year}-11-01"],
            ['Diwali Break End', "{$year}-11-07"],
            ['Bhai Dooj', "{$year}-11-09"],
            ['Christmas', "{$year}-12-25"],
            ['Winter Vacation Start', "{$year}-12-26"],
            ['Winter Vacation End', "{$year}-12-31"],
        ];

        foreach ($holidays as [$name, $date]) {
            Holiday::updateOrCreate(
                ['date' => $date],
                [
                    'is_recurring' => false,
                    'description' => $name . ' holiday',
                    'name' => $name,
                ]
            );
        }
    }
}

