<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\ActivityLog;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 25);
        $announcements = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'announcements' => $announcements,
            'total'         => Announcement::count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'         => 'required|string|max:255',
            'message'         => 'required|string',
            'show_to_staff'   => 'required|boolean',
            'show_to_clients' => 'required|boolean',
        ]);

        $announcement = Announcement::create($validated);
        ActivityLog::log("Created announcement: {$announcement->subject}");

        return response()->json($announcement, 201);
    }

    public function show($id)
    {
        $announcement = Announcement::find($id);
        if (!$announcement) return response()->json(['message' => 'Announcement not found'], 404);
        return response()->json($announcement);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::find($id);
        if (!$announcement) return response()->json(['message' => 'Announcement not found'], 404);

        $validated = $request->validate([
            'subject'         => 'sometimes|string|max:255',
            'message'         => 'sometimes|string',
            'show_to_staff'   => 'sometimes|boolean',
            'show_to_clients' => 'sometimes|boolean',
        ]);

        $announcement->update($validated);
        ActivityLog::log("Updated announcement: {$announcement->subject}");

        return response()->json($announcement);
    }

    public function destroy($id)
    {
        $announcement = Announcement::find($id);
        if (!$announcement) return response()->json(['message' => 'Announcement not found'], 404);

        $subject = $announcement->subject;
        $announcement->delete();
        ActivityLog::log("Deleted announcement: {$subject}");

        return response()->json(['message' => 'Announcement deleted']);
    }
}
