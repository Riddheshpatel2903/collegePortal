<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PortalAccessService;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

$portal = app(PortalAccessService::class);
$workingDays = $portal->workingDays();

echo "--- Teacher Availability Audit ---\n";
$teachers = Teacher::with('user')->get();
foreach ($teachers as $teacher) {
    echo "Teacher: {$teacher->user->name} (ID: {$teacher->id})\n";
    $avail = DB::table('teacher_availabilities')->where('teacher_id', $teacher->id)->get();
    if ($avail->isEmpty()) {
        echo "  - No specific availability records (Fully Free)\n";
    } else {
        $totalSlots = 0;
        foreach ($workingDays as $day) {
            $dayAvail = $avail->where('day', $day);
            echo "  - {$day}: " . $dayAvail->count() . " records\n";
            $totalSlots += $dayAvail->count();
        }
        echo "  - Total available slots: {$totalSlots}\n";
    }
}

echo "\n--- Timetable Occupancy (Total entries) ---\n";
$count = DB::table('timetable')->count();
echo "Total timetable entries: {$count}\n";

$byCourse = DB::table('timetable')
    ->select('course_id', DB::raw('count(*) as count'))
    ->groupBy('course_id')
    ->get();
foreach ($byCourse as $row) {
    echo "Course ID {$row->course_id}: {$row->count} entries\n";
}
