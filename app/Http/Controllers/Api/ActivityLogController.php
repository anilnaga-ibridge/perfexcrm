<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 25);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'logs'  => $logs,
            'total' => ActivityLog::count(),
        ]);
    }
}
