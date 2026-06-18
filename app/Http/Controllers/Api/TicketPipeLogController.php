<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketPipeLog;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TicketPipeLogController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketPipeLog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('from_name', 'like', "%{$search}%")
                  ->orWhere('from_email', 'like', "%{$search}%")
                  ->orWhere('to', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $perPage = $request->input('per_page', 25);
        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'logs' => $logs,
        ]);
    }

    public function destroy($id)
    {
        $log = TicketPipeLog::find($id);

        if (!$log) {
            return response()->json(['message' => 'Log not found'], 404);
        }

        $log->delete();

        return response()->json(['message' => 'Log entry deleted']);
    }

    public function clear()
    {
        TicketPipeLog::truncate();
        ActivityLog::log('Cleared all ticket pipe logs');

        return response()->json(['message' => 'All ticket pipe logs cleared']);
    }
}
