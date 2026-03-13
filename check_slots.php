<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\PortalAccessService;
echo "Slots Per Day: " . app(PortalAccessService::class)->slotsPerDay(1, 'odd') . "\n";
