<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission($permission)) {
            // Log 403 denial for security audit trail
            $userName = $user ? "User ID {$user->id} ({$user->email})" : 'Unauthenticated Guest';
            $logMessage = "PERMISSION DENIED [403]: {$userName} requested {$permission} on {$request->method()} {$request->fullUrl()}";
            
            Log::warning($logMessage);

            if ($user) {
                try {
                    ActivityLog::log("Denied access to {$permission} on {$request->path()}");
                } catch (\Throwable $e) {
                    // Ignore audit log write exceptions to preserve security boundary
                }
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Missing required permission: ' . $permission,
                ], 403);
            }

            abort(403, 'Unauthorized. Missing required permission: ' . $permission);
        }

        return $next($request);
    }
}
