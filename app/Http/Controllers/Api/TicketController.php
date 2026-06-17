<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\ActivityLog;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with([
            'client:id,company',
            'assignee:id,name',
            'contact:id,firstname,lastname,email',
            'project:id,name',
            'department:id,name',
        ]);

        // Filter by customer (client_id)
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%")
                  ->orWhereHas('client', fn ($c) => $c->where('company', 'like', "%{$search}%"))
                  ->orWhereHas('contact', fn ($c) => $c->where('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status'))   $query->where('status',   $request->input('status'));
        if ($request->filled('priority')) $query->where('priority', $request->input('priority'));

        $perPage = $request->input('per_page', 25);
        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Stats scoped to client if applicable
        $statsQuery = Ticket::query();
        if ($request->filled('client_id')) {
            $statsQuery->where('client_id', $request->integer('client_id'));
        }
        $stats = [
            'total'       => (clone $statsQuery)->count(),
            'open'        => (clone $statsQuery)->where('status', 'Open')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'answered'    => (clone $statsQuery)->where('status', 'Answered')->count(),
            'on_hold'     => (clone $statsQuery)->where('status', 'On Hold')->count(),
            'closed'      => (clone $statsQuery)->where('status', 'Closed')->count(),
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
            'department_id' => 'nullable|exists:ticket_departments,id',
            'message'       => 'required|string',
            'assigned_to'   => 'nullable|exists:users,id',
            'tags'          => 'nullable|string|max:500',
            'service_id'    => 'nullable|integer',
            'contact_id'    => 'nullable|exists:contacts,id',
            'cc'            => 'nullable|string|max:500',
            'project_id'    => 'nullable|exists:projects,id',
        ]);

        $ticket = Ticket::create($validated);
        ActivityLog::log("Created support ticket: {$ticket->subject}");

        return response()->json($ticket->load('client', 'assignee', 'contact', 'project', 'department'), 201);
    }

    public function show($id)
    {
        $ticket = Ticket::with('client', 'assignee', 'contact', 'project', 'department', 'replies.user')->find($id);
        if (!$ticket) return response()->json(['message' => 'Ticket not found'], 404);
        $ticket->increment('views_count');
        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::find($id);
        if (!$ticket) return response()->json(['message' => 'Ticket not found'], 404);

        $validated = $request->validate([
            'subject'       => 'sometimes|string|max:255',
            'client_id'     => 'nullable|exists:clients,id',
            'status'        => 'sometimes|string|in:Open,In Progress,Answered,On Hold,Closed',
            'priority'      => 'sometimes|string|in:Low,Medium,High,Urgent',
            'assigned_to'   => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:ticket_departments,id',
            'tags'          => 'nullable|string|max:500',
            'service_id'    => 'nullable|integer',
            'contact_id'    => 'nullable|exists:contacts,id',
            'cc'            => 'nullable|string|max:500',
            'project_id'    => 'nullable|exists:projects,id',
            'message'       => 'nullable|string',
        ]);

        $ticket->update($validated);
        ActivityLog::log("Updated ticket #{$ticket->id}: {$ticket->subject}");

        return response()->json($ticket->load('client', 'assignee', 'contact', 'project', 'department'));
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

        $reply = \App\Models\TicketReply::create([
            'ticket_id'      => $ticket->id,
            'message'        => $validated['message'],
            'user_id'        => auth()->id(),
            'name'           => auth()->user()?->name,
            'is_admin_reply' => $validated['is_admin_reply'] ?? true,
        ]);

        $ticket->update([
            'status'        => 'Answered',
            'last_reply_at' => now(),
        ]);
        ActivityLog::log("Replied to ticket #{$ticket->id}");

        return response()->json($reply->load('user'), 201);
    }

    public function metadata()
    {
        return response()->json([
            'departments' => \DB::table('ticket_departments')->select('id', 'name')->get(),
            'staff'       => \App\Models\User::select('id', 'name', 'email')->get(),
            'clients'     => \App\Models\Client::select('id', 'company')->get(),
            'contacts'    => \App\Models\Contact::select('id', 'client_id', 'firstname', 'lastname', 'email')->get(),
            'projects'    => \App\Models\Project::select('id', 'client_id', 'name')->get(),
            'services'    => [
                ['id' => 1, 'name' => 'Web Design'],
                ['id' => 2, 'name' => 'Hosting'],
                ['id' => 3, 'name' => 'System Administration'],
                ['id' => 4, 'name' => 'SLA Support'],
                ['id' => 5, 'name' => 'Billing & Finance'],
            ]
        ]);
    }
}
