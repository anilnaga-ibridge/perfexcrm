<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\ActivityLog;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with('client:id,company');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('subject', 'like', "%$s%")
                  ->orWhere('description', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%$s%"));
            });
        }

        if ($request->filled('status')) $query->where('status', $request->status);

        $perPage = $request->input('per_page', 25);
        $contracts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $stats = [
            'total'       => Contract::count(),
            'active'      => Contract::whereIn('status', ['In Progress', 'Active'])->count(),
            'not_started' => Contract::where('status', 'Not Started')->count(),
            'finished'    => Contract::where('status', 'Finished')->count(),
            'total_value' => Contract::sum('value'),
        ];

        return response()->json(['contracts' => $contracts, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'          => 'required|string|max:255',
            'client_id'        => 'nullable|exists:clients,id',
            'contract_type_id' => 'nullable|integer',
            'value'            => 'nullable|numeric|min:0',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date',
            'status'           => 'nullable|string',
            'description'      => 'nullable|string',
            'signed'           => 'nullable|boolean',
        ]);

        $contract = Contract::create($validated);
        ActivityLog::log("Created contract: {$contract->subject}");

        return response()->json($contract->load('client'), 201);
    }

    public function show($id)
    {
        $contract = Contract::with('client')->find($id);
        if (!$contract) return response()->json(['message' => 'Not found'], 404);
        return response()->json($contract);
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::find($id);
        if (!$contract) return response()->json(['message' => 'Not found'], 404);

        $validated = $request->validate([
            'subject'    => 'sometimes|string|max:255',
            'status'     => 'sometimes|string',
            'value'      => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'signed'     => 'nullable|boolean',
        ]);

        $contract->update($validated);
        ActivityLog::log("Updated contract: {$contract->subject}");

        return response()->json($contract->load('client'));
    }

    public function destroy($id)
    {
        $contract = Contract::find($id);
        if (!$contract) return response()->json(['message' => 'Not found'], 404);
        $contract->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
