<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('assignee:id,name,email');

        // Filter by customer (client_id or related_to_type=Customer with related_to_id)
        if ($request->filled('client_id')) {
            $clientId = $request->integer('client_id');
            $query->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhere(function ($q2) use ($clientId) {
                      $q2->where('related_to_type', 'Customer')
                         ->where('related_to_id', $clientId);
                  });
            });
        }

        // Filter by "related_to" type (Customer / Projects / Invoices / etc.)
        if ($request->filled('related_to_type') && !$request->filled('client_id')) {
            $query->where('related_to_type', $request->input('related_to_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        if ($request->boolean('all', false)) {
            $tasks = $query->orderBy('due_date', 'asc')->get();
        } else {
            $perPage = $request->input('per_page', 25);
            $tasks = $query->orderBy('created_at', 'desc')->paginate($perPage);
        }

        // Summary stats (scoped to client if applicable)
        $statsQuery = Task::query();
        if ($request->filled('client_id')) {
            $clientId = $request->integer('client_id');
            $statsQuery->where(function ($q) use ($clientId) {
                $q->where('client_id', $clientId)
                  ->orWhere(function ($q2) use ($clientId) {
                      $q2->where('related_to_type', 'Customer')
                         ->where('related_to_id', $clientId);
                  });
            });
        }

        $stats = [
            'total'             => (clone $statsQuery)->count(),
            'not_started'       => (clone $statsQuery)->where('status', 'Not Started')->count(),
            'in_progress'       => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'testing'           => (clone $statsQuery)->where('status', 'Testing')->count(),
            'awaiting_feedback' => (clone $statsQuery)->where('status', 'Awaiting Feedback')->count(),
            'complete'          => (clone $statsQuery)->where('status', 'Complete')->count(),
        ];

        return response()->json([
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'priority'        => 'required|string|in:Low,Medium,High,Urgent',
            'status'          => 'required|string|in:Not Started,In Progress,Testing,Awaiting Feedback,Complete',
            'start_date'      => 'nullable|date',
            'due_date'        => 'nullable|date',
            'assigned_to'     => 'nullable|exists:users,id',
            'related_to_type' => 'nullable|string',
            'related_to_id'   => 'nullable|integer',
            'client_id'       => 'nullable|integer',
            'billable'        => 'nullable|boolean',
            'is_public'       => 'nullable|boolean',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'repeat_every'    => 'nullable|string|max:50',
            'tags'            => 'nullable|string|max:500',
            'assignees'       => 'nullable|array',
            'assignees.*'     => 'integer|exists:users,id',
            'followers'       => 'nullable|array',
            'followers.*'     => 'integer|exists:users,id',
        ]);

        $task = Task::create($validated);
        return response()->json($task->load('assignee'), 201);
    }

    public function show($id)
    {
        $task = Task::with('assignee')->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);

        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'description'     => 'nullable|string',
            'priority'        => 'sometimes|string|in:Low,Medium,High,Urgent',
            'status'          => 'sometimes|string|in:Not Started,In Progress,Testing,Awaiting Feedback,Complete',
            'start_date'      => 'nullable|date',
            'due_date'        => 'nullable|date',
            'assigned_to'     => 'nullable|exists:users,id',
            'related_to_type' => 'nullable|string',
            'related_to_id'   => 'nullable|integer',
            'client_id'       => 'nullable|integer',
            'billable'        => 'nullable|boolean',
            'is_public'       => 'nullable|boolean',
            'hourly_rate'     => 'nullable|numeric|min:0',
            'repeat_every'    => 'nullable|string|max:50',
            'tags'            => 'nullable|string|max:500',
            'assignees'       => 'nullable|array',
            'assignees.*'     => 'integer|exists:users,id',
            'followers'       => 'nullable|array',
            'followers.*'     => 'integer|exists:users,id',
        ]);

        $task->update($validated);
        return response()->json($task->load('assignee'));
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}
