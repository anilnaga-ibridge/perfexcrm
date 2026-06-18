<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goal;
use App\Models\ActivityLog;

class GoalController extends Controller
{
    public function index(Request $request)
    {
        $query = Goal::query()->with('staff');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('subject', 'like', "%{$search}%");
        }

        $perPage = $request->input('per_page', 25);
        $goals = $query->orderBy('end_date', 'asc')->paginate($perPage);

        return response()->json([
            'goals' => $goals,
            'total' => Goal::count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'           => 'required|string|max:255',
            'goal_type'         => 'required|string|max:255',
            'staff_member'      => 'nullable|integer|exists:users,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date',
            'target_value'      => 'required|numeric|min:0',
            'current_value'     => 'nullable|numeric|min:0',
            'description'       => 'nullable|string',
            'notify_when_achieve' => 'boolean',
            'notify_when_fail'   => 'boolean',
        ]);

        $goal = Goal::create($validated);
        ActivityLog::log("Created business goal: {$goal->subject}");

        return response()->json($goal->load('staff'), 201);
    }

    public function update(Request $request, $id)
    {
        $goal = Goal::find($id);
        if (!$goal) return response()->json(['message' => 'Goal not found'], 404);

        $validated = $request->validate([
            'subject'           => 'sometimes|string|max:255',
            'goal_type'         => 'sometimes|string|max:255',
            'staff_member'      => 'nullable|integer|exists:users,id',
            'start_date'        => 'sometimes|date',
            'end_date'          => 'sometimes|date',
            'target_value'      => 'sometimes|numeric|min:0',
            'current_value'     => 'sometimes|numeric|min:0',
            'description'       => 'nullable|string',
            'notify_when_achieve' => 'boolean',
            'notify_when_fail'   => 'boolean',
        ]);

        $goal->update($validated);
        ActivityLog::log("Updated business goal: {$goal->subject}");

        return response()->json($goal->load('staff'));
    }

    public function destroy($id)
    {
        $goal = Goal::find($id);
        if (!$goal) return response()->json(['message' => 'Goal not found'], 404);

        $subject = $goal->subject;
        $goal->delete();
        ActivityLog::log("Deleted business goal: {$subject}");

        return response()->json(['message' => 'Goal deleted']);
    }
}
