<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AutoTimetableService;

$service = app(AutoTimetableService::class);
try {
    $result = $service->generate([
        'course_id' => 1,
        'semester_type' => 'odd',
        'selected_years' => [1],
    ]);
    echo "Success: Generated " . $result['generated_count'] . " slots\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
