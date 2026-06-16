<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\LeadSource;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        // Check if Kanban group response is requested
        if ($request->boolean('kanban')) {
            $statuses = LeadStatus::orderBy('order_num', 'asc')->get();
            $kanbanData = [];

            foreach ($statuses as $status) {
                $leads = Lead::where('status_id', $status->id)
                    ->with(['source', 'assigned'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
                    
                $kanbanData[] = [
                    'status_id' => $status->id,
                    'status_name' => $status->name,
                    'status_color' => $status->color,
                    'leads' => $leads
                ];
            }

            return response()->json($kanbanData);
        }

        $query = Lead::with(['status', 'source', 'assigned']);

        // Search filters
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phonenumber', 'like', "%{$search}%");
            });
        }

        // Dropdown filters
        if ($request->has('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }
        if ($request->has('source_id')) {
            $query->where('source_id', $request->input('source_id'));
        }
        if ($request->has('assigned_id')) {
            $query->where('assigned_id', $request->input('assigned_id'));
        }

        $perPage = $request->input('per_page', 10);
        $leads = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($leads);
    }

    /**
     * Store a newly created lead in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'phonenumber' => 'nullable|string|max:255',
            'lead_value' => 'nullable|numeric',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'status_id' => 'required|exists:lead_statuses,id',
            'source_id' => 'required|exists:lead_sources,id',
            'assigned_id' => 'nullable|exists:users,id',
        ]);

        $lead = Lead::create($validated);

        return response()->json($lead->load(['status', 'source']), 201);
    }

    /**
     * Display the specified lead.
     */
    public function show($id)
    {
        $lead = Lead::with(['status', 'source', 'assigned'])->find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        return response()->json($lead);
    }

    /**
     * Update the specified lead in database.
     */
    public function update(Request $request, $id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'phonenumber' => 'nullable|string|max:255',
            'lead_value' => 'nullable|numeric',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'status_id' => 'required|exists:lead_statuses,id',
            'source_id' => 'required|exists:lead_sources,id',
            'assigned_id' => 'nullable|exists:users,id',
        ]);

        $lead->update($validated);

        return response()->json($lead->load(['status', 'source']));
    }

    /**
     * Update status of lead (Kanban Drag and Drop support).
     */
    public function updateStatus(Request $request, $id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $validated = $request->validate([
            'status_id' => 'required|exists:lead_statuses,id'
        ]);

        $lead->update([
            'status_id' => $validated['status_id']
        ]);

        return response()->json(['message' => 'Status updated successfully', 'lead' => $lead]);
    }

    /**
     * Remove the specified lead from database.
     */
    public function destroy($id)
    {
        $lead = Lead::find($id);

        if (!$lead) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }
}
