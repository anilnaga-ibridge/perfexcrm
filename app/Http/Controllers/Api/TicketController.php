<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\ActivityLog;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with('client:id,company', 'assignee:id,name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status'))   $query->where('status',   $request->input('status'));
        if ($request->filled('priority')) $query->where('priority', $request->input('priority'));

        $perPage = $request->input('per_page', 25);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $stats = [
            'total'       => Ticket::count(),
            'open'        => Ticket::where('status', 'Open')->count(),
            'in_progress' => Ticket::where('status', 'In Progress')->count(),
            'answered'    => Ticket::where('status', 'Answered')->count(),
            'on_hold'     => Ticket::where('status', 'On Hold')->count(),
            'closed'      => Ticket::where('status', 'Closed')->count(),
        ];

        return response()->json(['tickets' => $tickets, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'       => 'required|string|max:255',
            'client_id'     => 'nullable|exists:clients,id',
            'priority'      => 'required|string|in:Low,Medium,High,Urgent',
            'status'        => 'required|string|in:Open,In Progress,Answered,On Hold,Closed',
            'department_id' => 'nullable|integer',
            'message'       => 'required|string',
            'assigned_to'   => 'nullable|exists:users,id',
        ]);

        $ticket = Ticket::create($validated);
        ActivityLog::log("Created support ticket: {$ticket->subject}");

        return response()->json($ticket->load('client', 'assignee'), 201);
    }

    public function show($id)
    {
        $ticket = Ticket::with('client', 'assignee', 'replies.user')->find($id);
        if (!$ticket) return response()->json(['message' => 'Ticket not found'], 404);
        $ticket->increment('views_count');
        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) return response()->json(['message' => 'Ticket not found'], 404);

        $validated = $request->validate([
            'status'      => 'sometimes|string|in:Open,In Progress,Answered,On Hold,Closed',
            'priority'    => 'sometimes|string|in:Low,Medium,High,Urgent',
            'assigned_to' => 'nullable|exists:users,id',
            'subject'     => 'sometimes|string|max:255',
        ]);

        $ticket->update($validated);
        ActivityLog::log("Updated ticket #{$ticket->id}: {$ticket->subject}");

        return response()->json($ticket->load('client', 'assignee'));
    }

    public function destroy($id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) return response()->json(['message' => 'Ticket not found'], 404);
        $ticket->delete();
        return response()->json(['message' => 'Ticket deleted']);
    }

    public function addReply(Request $request, $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) return response()->json(['message' => 'Ticket not found'], 404);

        $validated = $request->validate([
            'message'        => 'required|string',
            'is_admin_reply' => 'boolean',
        ]);

        $reply = TicketReply::create([
            'ticket_id'      => $ticket->id,
            'message'        => $validated['message'],
            'user_id'        => auth()->id(),
            'name'           => auth()->user()?->name,
            'is_admin_reply' => $validated['is_admin_reply'] ?? true,
        ]);

        // Auto-update ticket status to answered
        $ticket->update(['status' => 'Answered']);
        ActivityLog::log("Replied to ticket #{$ticket->id}");

        return response()->json($reply->load('user'), 201);
    }
}
