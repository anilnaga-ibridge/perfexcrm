<?php

use Illuminate\Support\Facades\Route;

// Load the full CI compatibility layer (classes, Active Record, helpers, AdminController)
require_once base_path('app/Services/CICompatLayer.php');

// Plugin SSO (primary)
Route::get('/plugins/sso', [\App\Http\Controllers\Api\ModuleController::class, 'ssoLogin'])->name('plugins.sso');
// Legacy alias
Route::get('/modules/sso', [\App\Http\Controllers\Api\ModuleController::class, 'ssoLogin'])->name('modules.sso');

// SPA Direct Routes — serve the Vue app for all frontend-managed pages
// These MUST come before the legacy controller bridge catch-all
Route::get('/admin/login', function () { return view('welcome'); })->name('login');
Route::get('/admin/register', function () { return view('welcome'); });
Route::get('/admin/forgot-password', function () { return view('welcome'); });
Route::get('/admin/reset-password/{token}', function () { return view('welcome'); })->name('password.reset');
Route::get('/admin/modules', function () { return view('welcome'); });
// Dynamic Module Asset Streamer (serves CSS, JS, images, fonts directly from Modules/{alias}/...)
Route::get('/modules/{alias}/{file}', function (\Illuminate\Http\Request $request, string $alias, string $file) {
    $alt = str_contains($alias, '-') ? str_replace('-', '_', $alias) : str_replace('_', '-', $alias);

    $candidates = [
        base_path("Modules/{$alias}/{$file}"),
        base_path("Modules/{$alt}/{$file}"),
        base_path("Modules/{$alias}/assets/{$file}"),
        base_path("Modules/{$alt}/assets/{$file}"),
    ];

    foreach ($candidates as $filePath) {
        if (file_exists($filePath) && !is_dir($filePath)) {
            $lastModified = filemtime($filePath);
            $etag = '"' . md5($lastModified . filesize($filePath)) . '"';

            // Check client cache headers for 304 Not Modified
            $ifNoneMatch = $request->header('If-None-Match');
            $ifModifiedSince = $request->header('If-Modified-Since');

            if ($ifNoneMatch === $etag || ($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified)) {
                return response('', 304)->withHeaders([
                    'ETag' => $etag,
                    'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                    'Cache-Control' => 'public, max-age=86400, must-revalidate',
                ]);
            }

            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'css' => 'text/css; charset=utf-8',
                'js' => 'application/javascript; charset=utf-8',
                'json' => 'application/json; charset=utf-8',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'map' => 'application/json',
                'webp' => 'image/webp',
                'ico' => 'image/x-icon',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
            ];
            $contentType = $mimeTypes[$ext] ?? mime_content_type($filePath) ?? 'text/plain';
            $acceptEncoding = $request->header('Accept-Encoding', '');

            // Check for pre-compressed brotli or gzip versions
            if (str_contains($acceptEncoding, 'br') && file_exists($filePath . '.br')) {
                return response()->file($filePath . '.br', [
                    'Content-Type' => $contentType,
                    'Content-Encoding' => 'br',
                    'ETag' => $etag,
                    'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                    'Cache-Control' => 'public, max-age=86400, must-revalidate',
                ]);
            }

            if (str_contains($acceptEncoding, 'gzip') && file_exists($filePath . '.gz')) {
                return response()->file($filePath . '.gz', [
                    'Content-Type' => $contentType,
                    'Content-Encoding' => 'gzip',
                    'ETag' => $etag,
                    'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                    'Cache-Control' => 'public, max-age=86400, must-revalidate',
                ]);
            }

            return response()->file($filePath, [
                'Content-Type' => $contentType,
                'ETag' => $etag,
                'Last-Modified' => gmdate('D, d M Y H:i:s', $lastModified) . ' GMT',
                'Cache-Control' => 'public, max-age=86400, must-revalidate',
            ]);
        }
    }
    abort(404);
})->where('alias', '[a-zA-Z0-9_\-.]+')
  ->where('file', '.*');

// Plugin page bridge — serves legacy CI views inside the SSO iframe
Route::any('/plugins/{alias}/{page?}', [\App\Http\Controllers\Api\PluginBridgeController::class, 'renderPage'])
    ->where('alias', '[a-zA-Z0-9_\-.]+')
    ->where('page', '.*');

// Dynamic Legacy Controller Bridge — handles all module controller actions, AJAX, CRUD, delete, edit
Route::any('/admin/{controller}/{method?}/{params?}', [\App\Http\Controllers\Api\PluginBridgeController::class, 'executeApi'])
    ->where('controller', '^(?!setup|modules|module|dashboard|auth|api|plugins|welcome|index)[a-zA-Z0-9_-]+$')
    ->where('params', '.*');

// SPA Catch-all Route for native Vue pages — placed AFTER legacy controller bridge
Route::get('/admin/{any}', function () { return view('welcome'); })->where('any', '.*');

Route::fallback(function () {
    return view('welcome');
});
