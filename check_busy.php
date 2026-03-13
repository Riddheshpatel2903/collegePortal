<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Timetable;

$tids = [1, 2, 3, 4, 78];
foreach($tids as $tid) {
    $rows = Timetable::where('teacher_id', $tid)->get();
    echo "Teacher ID {$tid}: " . $rows->count() . " slots total\n";
    foreach($rows->groupBy('day') as $day => $drows) {
        echo "  - {$day}: " . $drows->count() . " slots\n";
    }
}
