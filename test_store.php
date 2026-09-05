<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->instance('request', Illuminate\Http\Request::create('/'));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$uploadedFile = new Illuminate\Http\UploadedFile(
    base_path('packages/hello-world-1.0.0.zip'),
    'hello-world-1.0.0.zip',
    'application/zip',
    null,
    true
);

$req = Illuminate\Http\Request::create('/api/plugins', 'POST', [], [], ['module_file' => $uploadedFile]);
$req->setUserResolver(fn() => App\Models\User::first());

$controller = new App\Http\Controllers\Api\ModuleController();
$response = $controller->store($req);

echo "STATUS: " . $response->getStatusCode() . "\n";
echo "RESPONSE:\n" . $response->getContent() . "\n";
