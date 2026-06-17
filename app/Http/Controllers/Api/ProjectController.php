<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ActivityLog;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('client:id,company', 'members:id,name');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%")
                  ->orWhere('tags', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%$s%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', 25);
        $projects = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Scope stats query to client_id if present
        $statsQuery = Project::query();
        if ($request->filled('client_id')) {
            $statsQuery->where('client_id', $request->client_id);
        }

        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'not_started' => (clone $statsQuery)->where('status', 'Not Started')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'on_hold'     => (clone $statsQuery)->where('status', 'On Hold')->count(),
            'cancelled'   => (clone $statsQuery)->where('status', 'Cancelled')->count(),
            'finished'    => (clone $statsQuery)->where('status', 'Finished')->count(),
        ];

        return response()->json(['projects' => $projects, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'client_id'           => 'nullable|exists:clients,id',
            'billing_type'        => 'nullable|string',
            'budget'              => 'nullable|numeric|min:0',
            'start_date'          => 'nullable|date',
            'deadline'            => 'nullable|date',
            'status'              => 'nullable|string',
            'progress_from_tasks' => 'nullable|boolean',
            'progress'            => 'nullable|integer|min:0|max:100',
            'estimated_hours'     => 'nullable|numeric|min:0',
            'send_created_email'  => 'nullable|boolean',
            'tags'                => 'nullable|string',
            'settings'            => 'nullable|array',
            'member_ids'          => 'nullable|array',
            'member_ids.*'        => 'exists:users,id',
        ]);

        $memberIds = $validated['member_ids'] ?? [];
        unset($validated['member_ids']);

        $project = Project::create($validated);
        if ($memberIds) {
            $project->members()->sync($memberIds);
        }

        ActivityLog::log("Created project: {$project->name}");

        return response()->json($project->load('client', 'members'), 201);
    }

    public function show($id)
    {
        $project = Project::with('client', 'members')->find($id);
        if (!$project) return response()->json(['message' => 'Not found'], 404);
        return response()->json($project);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);
        if (!$project) return response()->json(['message' => 'Not found'], 404);

        $validated = $request->validate([
            'name'                => 'sometimes|required|string|max:255',
            'description'         => 'nullable|string',
            'client_id'           => 'nullable|exists:clients,id',
            'billing_type'        => 'nullable|string',
            'budget'              => 'nullable|numeric|min:0',
            'start_date'          => 'nullable|date',
            'deadline'            => 'nullable|date',
            'status'              => 'nullable|string',
            'progress_from_tasks' => 'nullable|boolean',
            'progress'            => 'nullable|integer|min:0|max:100',
            'estimated_hours'     => 'nullable|numeric|min:0',
            'send_created_email'  => 'nullable|boolean',
            'tags'                => 'nullable|string',
            'settings'            => 'nullable|array',
            'member_ids'          => 'nullable|array',
        ]);

        $memberIds = $validated['member_ids'] ?? null;
        unset($validated['member_ids']);

        $project->update($validated);
        if ($memberIds !== null) {
            $project->members()->sync($memberIds);
        }

        ActivityLog::log("Updated project: {$project->name}");

        return response()->json($project->load('client', 'members'));
    }

    public function destroy($id)
    {
        $project = Project::find($id);
        if (!$project) return response()->json(['message' => 'Not found'], 404);
        $project->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
