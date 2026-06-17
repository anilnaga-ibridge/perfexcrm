<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Reminder;
use App\Models\ActivityLog;

class ReminderController extends Controller
{
    /**
     * List reminders for a client.
     */
    public function index(Request $request, $clientId)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $reminders = Reminder::where('client_id', $clientId)
            ->with([
                'remindTo:id,name,email',
                'creator:id,name',
            ])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($r) {
                return [
                    'id'          => $r->id,
                    'description' => $r->description,
                    'date'        => $r->date ? $r->date->format('Y-m-d') : null,
                    'send_email'  => $r->send_email,
                    'is_notified' => $r->is_notified,
                    'remind_to'   => $r->remind_to,
                    'remind_to_user' => $r->remindTo,
                    'created_by'  => $r->created_by,
                    'creator'     => $r->creator,
                    'created_at'  => $r->created_at,
                ];
            });

        return response()->json($reminders);
    }

    /**
     * Create a new reminder for a client.
     */
    public function store(Request $request, $clientId)
    {
        $client = Client::find($clientId);
        if (!$client) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $validated = $request->validate([
            'description' => 'required|string',
            'date'        => 'required|date',
            'remind_to'   => 'required|exists:users,id',
            'send_email'  => 'boolean',
        ]);

        $reminder = Reminder::create([
            'client_id'   => $clientId,
            'description' => $validated['description'],
            'date'        => $validated['date'],
            'remind_to'   => $validated['remind_to'],
            'send_email'  => $validated['send_email'] ?? false,
            'is_notified' => false,
            'created_by'  => $request->user()->id,
        ]);

        ActivityLog::log("Set reminder for customer #{$clientId} on {$validated['date']}");

        $reminder->load('remindTo:id,name,email', 'creator:id,name');

        return response()->json([
            'id'          => $reminder->id,
            'description' => $reminder->description,
            'date'        => $reminder->date->format('Y-m-d'),
            'send_email'  => $reminder->send_email,
            'is_notified' => $reminder->is_notified,
            'remind_to'   => $reminder->remind_to,
            'remind_to_user' => $reminder->remindTo,
            'created_by'  => $reminder->created_by,
            'creator'     => $reminder->creator,
        ], 201);
    }

    /**
     * Delete a reminder.
     */
    public function destroy(Request $request, $id)
    {
        $reminder = Reminder::find($id);
        if (!$reminder) {
            return response()->json(['message' => 'Reminder not found'], 404);
        }

        $user    = $request->user();
        $isAdmin = $user->role === 'admin';

        if (!$isAdmin && $reminder->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $clientId = $reminder->client_id;
        $reminder->delete();

        ActivityLog::log("Deleted reminder #{$id} for customer #{$clientId}");

        return response()->json(['message' => 'Reminder deleted successfully']);
    }
}
