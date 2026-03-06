<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = \Illuminate\Http\Request::create('/admin/users/create', 'GET');

// We need to bypass the session middleware issues if we just run it directly.
// A better way is using artisan commands or just looking at the log file.
