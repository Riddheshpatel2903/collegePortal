<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Classroom;

echo "--- Classroom Inventory ---\n";
$rooms = Classroom::all();
foreach ($rooms as $room) {
    echo "ID: {$room->id} | Name: {$room->name} | Type: {$room->type} | Course: {$room->course_id} | Year: {$room->year_number}\n";
}
