<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('/'));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\Api\NotificationController();

// 1. Insert a test unread notification
$reqStore = Illuminate\Http\Request::create('/api/notifications', 'POST', [
    'description' => 'Test Notification ' . rand(100, 999),
]);
$reqStore->setUserResolver(fn() => App\Models\User::first());
$resStore = $controller->store($reqStore);
echo "STORE RESULT: " . $resStore->getContent() . "\n";

// 2. Fetch notifications
$reqIndex = Illuminate\Http\Request::create('/api/notifications', 'GET');
$reqIndex->setUserResolver(fn() => App\Models\User::first());
$resIndex = $controller->index($reqIndex);
echo "BEFORE MARK ALL READ:\n" . $resIndex->getContent() . "\n";

// 3. Mark all read
$reqMark = Illuminate\Http\Request::create('/api/notifications/mark-all-read', 'POST');
$reqMark->setUserResolver(fn() => App\Models\User::first());
$resMark = $controller->markAllRead($reqMark);
echo "MARK ALL READ RESULT:\n" . $resMark->getContent() . "\n";

// 4. Fetch notifications again
$resIndex2 = $controller->index($reqIndex);
echo "AFTER MARK ALL READ:\n" . $resIndex2->getContent() . "\n";
