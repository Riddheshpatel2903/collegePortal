<?php

namespace Database\Seeders;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Annual Cultural Fest',
                'description' => 'A week-long celebration of arts, music, and culture.',
                'event_date' => Carbon::now()->addDays(15)->setTime(10, 0),
                'location' => 'Main Auditorium',
            ],
            [
                'title' => 'Tech Symposium 2026',
                'description' => 'Showcasing the latest in technology and innovation.',
                'event_date' => Carbon::now()->addDays(25)->setTime(9, 0),
                'location' => 'Seminar Hall A',
            ],
            [
                'title' => 'Sports Meet',
                'description' => 'Inter-departmental sports competitions.',
                'event_date' => Carbon::now()->addDays(5)->setTime(8, 0),
                'location' => 'College Ground',
            ],
            [
                'title' => 'Guest Lecture on AI',
                'description' => 'Special session by industry experts on generative AI.',
                'event_date' => Carbon::now()->addDays(2)->setTime(14, 0),
                'location' => 'Main Hall',
            ],
            [
                'title' => 'Placement Drive',
                'description' => 'Recruitment drive for final year students.',
                'event_date' => Carbon::now()->addDays(20)->setTime(10, 0),
                'location' => 'Placement Cell',
            ],
        ];

        foreach ($events as $event) {
            Event::create($event);
        }
    }
}
