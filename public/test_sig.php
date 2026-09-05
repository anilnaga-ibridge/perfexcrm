<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');

$zipPath = storage_path('app/private/temp_uploads/company-policies.zip');
echo "Testing signature verification for: {$zipPath}\n";

try {
    $ok = resolve(\App\Plugin\Kernel\PackageManager::class)->verifySignature($zipPath);
    echo "RESULT: SUCCESS\n";
} catch (\Throwable $e) {
    echo "RESULT: FAILED - " . $e->getMessage() . "\n";
}
