<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CreditNote;
use App\Models\Client;

class CreditNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = CreditNote::with('client:id,company');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $creditNotes = $query->orderBy('date', 'desc')->paginate($perPage);

        // Summary stats
        $stats = [
            'total'         => CreditNote::count(),
            'open'          => CreditNote::where('status', 'Open')->count(),
            'closed'        => CreditNote::where('status', 'Closed')->count(),
            'void'          => CreditNote::where('status', 'Void')->count(),
            'total_amount'  => CreditNote::sum('amount'),
            'open_amount'   => CreditNote::where('status', 'Open')->sum('amount'),
        ];

        return response()->json([
            'credit_notes' => $creditNotes,
            'stats'        => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'number'     => 'nullable|string|unique:credit_notes,number',
            'amount'     => 'required|numeric|min:0',
            'date'       => 'required|date',
            'status'     => 'required|in:Open,Closed,Void',
            'reference'  => 'nullable|string',
            'admin_note' => 'nullable|string',
            'terms'      => 'nullable|string',
        ]);

        if (empty($validated['number'])) {
            $last = CreditNote::max('id') ?? 0;
            $validated['number'] = 'CN-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
        }

        $creditNote = CreditNote::create($validated);
        return response()->json($creditNote->load('client'), 201);
    }

    public function show($id)
    {
        $creditNote = CreditNote::with('client')->find($id);
        if (!$creditNote) return response()->json(['message' => 'Credit Note not found'], 404);
        return response()->json($creditNote);
    }

    public function update(Request $request, $id)
    {
        $creditNote = CreditNote::find($id);
        if (!$creditNote) return response()->json(['message' => 'Credit Note not found'], 404);

        $validated = $request->validate([
            'status'     => 'sometimes|in:Open,Closed,Void',
            'reference'  => 'nullable|string',
            'admin_note' => 'nullable|string',
            'terms'      => 'nullable|string',
            'amount'     => 'sometimes|numeric|min:0',
            'date'       => 'sometimes|date',
        ]);

        $creditNote->update($validated);
        return response()->json($creditNote->load('client'));
    }

    public function destroy($id)
    {
        $creditNote = CreditNote::find($id);
        if (!$creditNote) return response()->json(['message' => 'Credit Note not found'], 404);
        $creditNote->delete();
        return response()->json(['message' => 'Credit Note deleted']);
    }
}
