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

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
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

        // For Kanban board, we might want to load all tasks if requested, or paginate
        if ($request->boolean('all', false)) {
            $tasks = $query->orderBy('due_date', 'asc')->get();
        } else {
            $perPage = $request->input('per_page', 25);
            $tasks = $query->orderBy('due_date', 'asc')->paginate($perPage);
        }

        // Summary stats
        $stats = [
            'total'             => Task::count(),
            'not_started'       => Task::where('status', 'Not Started')->count(),
            'in_progress'       => Task::where('status', 'In Progress')->count(),
            'testing'           => Task::where('status', 'Testing')->count(),
            'awaiting_feedback' => Task::where('status', 'Awaiting Feedback')->count(),
            'complete'          => Task::where('status', 'Complete')->count(),
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
