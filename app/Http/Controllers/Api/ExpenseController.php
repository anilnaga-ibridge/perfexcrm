<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ActivityLog;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('client:id,company');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('note', 'like', "%$s%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%$s%"));
            });
        }

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);

        $perPage = $request->input('per_page', 25);
        $expenses = $query->orderBy('date', 'desc')->paginate($perPage);

        $stats = [
            'total'    => Expense::count(),
            'billed'   => Expense::where('status', 'billed')->sum('amount'),
            'unbilled' => Expense::where('status', 'unbilled')->sum('amount'),
            'total_amount' => Expense::sum('amount'),
        ];

        return response()->json(['expenses' => $expenses, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'date'         => 'required|date',
            'category_id'  => 'nullable|integer',
            'client_id'    => 'nullable|exists:clients,id',
            'payment_mode' => 'nullable|string',
            'status'       => 'nullable|string|in:billed,unbilled',
            'reference'    => 'nullable|string|max:100',
            'note'         => 'nullable|string',
            'billable'     => 'nullable|boolean',
        ]);

        $expense = Expense::create($validated);
        ActivityLog::log("Recorded expense: {$expense->name} (\${$expense->amount})");

        return response()->json($expense->load('client'), 201);
    }

    public function show($id)
    {
        $expense = Expense::with('client')->find($id);
        if (!$expense) return response()->json(['message' => 'Not found'], 404);
        return response()->json($expense);
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);
        if (!$expense) return response()->json(['message' => 'Not found'], 404);

        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'amount'       => 'sometimes|numeric|min:0',
            'date'         => 'sometimes|date',
            'status'       => 'sometimes|string|in:billed,unbilled',
            'payment_mode' => 'sometimes|string',
            'note'         => 'nullable|string',
        ]);

        $expense->update($validated);
        ActivityLog::log("Updated expense: {$expense->name}");

        return response()->json($expense->load('client'));
    }

    public function destroy($id)
    {
        $expense = Expense::find($id);
        if (!$expense) return response()->json(['message' => 'Not found'], 404);
        $expense->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
