<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = ($user->admin == 1 || $user->is_admin || optional($user->role)->slug === 'admin');

        $query = Todo::with(['staff', 'assignedToStaff', 'assignedByStaff']);

        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('staff_id', $user->id)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('assigned_by', $user->id);
            });
        }

        if ($request->has('staff_id') && $request->staff_id) {
            $query->where(function ($q) use ($request) {
                $q->where('staff_id', $request->staff_id)
                  ->orWhere('assigned_to', $request->staff_id);
            });
        }

        $todos = $query->orderBy('done')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($todos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'assigned_to' => 'nullable|integer',
            'priority'    => 'nullable|string|in:high,medium,low',
            'due_date'    => 'nullable|date',
        ]);

        $user = $request->user();
        $assignedTo = $validated['assigned_to'] ?? $user->id;
        $maxSort = Todo::where('staff_id', $assignedTo)->max('sort_order');

        $todo = Todo::create([
            'staff_id'    => $assignedTo,
            'assigned_to' => $assignedTo,
            'assigned_by' => $user->id,
            'description' => $validated['description'],
            'priority'    => $validated['priority'] ?? 'medium',
            'due_date'    => $validated['due_date'] ?? null,
            'done'        => false,
            'sort_order'  => ($maxSort ?? 0) + 1,
        ]);

        return response()->json($todo->load(['staff', 'assignedToStaff', 'assignedByStaff']), 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $isAdmin = ($user->admin == 1 || $user->is_admin || optional($user->role)->slug === 'admin');

        $query = Todo::query();
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('staff_id', $user->id)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('assigned_by', $user->id);
            });
        }

        $todo = $query->findOrFail($id);

        $validated = $request->validate([
            'description' => 'sometimes|string|max:1000',
            'assigned_to' => 'sometimes|nullable|integer',
            'priority'    => 'sometimes|string|in:high,medium,low',
            'due_date'    => 'sometimes|nullable|date',
            'done'        => 'sometimes|boolean',
            'sort_order'  => 'sometimes|integer',
        ]);

        if (array_key_exists('assigned_to', $validated) && $validated['assigned_to']) {
            $validated['staff_id'] = $validated['assigned_to'];
        }

        $todo->update($validated);

        return response()->json($todo->load(['staff', 'assignedToStaff', 'assignedByStaff']));
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $isAdmin = ($user->admin == 1 || $user->is_admin || optional($user->role)->slug === 'admin');

        $query = Todo::query();
        if (!$isAdmin) {
            $query->where(function ($q) use ($user) {
                $q->where('staff_id', $user->id)
                  ->orWhere('assigned_to', $user->id)
                  ->orWhere('assigned_by', $user->id);
            });
        }

        $todo = $query->findOrFail($id);
        $todo->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:todo_items,id',
        ]);

        foreach ($validated['order'] as $index => $todoId) {
            Todo::where('id', $todoId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
