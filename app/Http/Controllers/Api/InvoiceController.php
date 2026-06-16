<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Client;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client:id,company', 'project:id,name');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->input('client_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $invoices = $query->orderBy('date', 'desc')->paginate($perPage);

        // Summary stats
        $stats = [
            'total'         => Invoice::count(),
            'draft'         => Invoice::where('status', 'draft')->count(),
            'unpaid'        => Invoice::where('status', 'unpaid')->count(),
            'paid'          => Invoice::where('status', 'paid')->count(),
            'overdue'       => Invoice::where('status', 'overdue')->count(),
            'partially_paid'=> Invoice::where('status', 'partially_paid')->count(),
            'total_amount'  => Invoice::sum('total'),
            'paid_amount'   => Invoice::where('status', 'paid')->sum('total'),
            'unpaid_amount' => Invoice::whereIn('status', ['unpaid', 'overdue'])->sum('total'),
        ];

        return response()->json([
            'invoices' => $invoices,
            'stats'    => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_id'=> 'nullable|exists:projects,id',
            'number'    => 'nullable|string|unique:invoices,number',
            'status'    => 'required|in:draft,unpaid,paid,overdue,partially_paid,cancelled',
            'date'      => 'required|date',
            'duedate'   => 'required|date',
            'prevent_overdue_reminders' => 'nullable|boolean',
            'allowed_payment_modes' => 'nullable|string',
            'currency'  => 'nullable|string',
            'sale_agent'=> 'nullable|string',
            'recurring_type' => 'nullable|string',
            'discount_type'  => 'nullable|string',
            'admin_note'=> 'nullable|string',
            'qty_display_mode' => 'nullable|string',
            'client_note'      => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'subtotal'  => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_val'     => 'nullable|numeric|min:0',
            'tax'       => 'nullable|numeric|min:0',
            'adjustment'=> 'nullable|numeric',
            'total'     => 'required|numeric|min:0',
            'notes'     => 'nullable|string',
            'tags'      => 'nullable|string',
            
            // Addresses
            'billing_street'  => 'nullable|string',
            'billing_city'    => 'nullable|string',
            'billing_state'   => 'nullable|string',
            'billing_zip'     => 'nullable|string',
            'billing_country' => 'nullable|string',
            'shipping_street' => 'nullable|string',
            'shipping_city'   => 'nullable|string',
            'shipping_state'  => 'nullable|string',
            'shipping_zip'    => 'nullable|string',
            'shipping_country'=> 'nullable|string',

            // Items
            'items'          => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.long_description' => 'nullable|string',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.unit'       => 'nullable|string',
            'items.*.rate'       => 'required|numeric|min:0',
            'items.*.tax_rate'   => 'nullable|numeric|min:0|max:100',
        ]);

        // Auto-generate invoice number if not given
        if (empty($validated['number'])) {
            $last = Invoice::max('id') ?? 0;
            $validated['number'] = 'INV-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
        }

        $invoice = \DB::transaction(function() use ($validated) {
            $invoiceData = \Arr::except($validated, ['items']);
            $invoice = Invoice::create($invoiceData);

            foreach ($validated['items'] as $item) {
                $invoice->items()->create($item);
            }

            return $invoice;
        });

        return response()->json($invoice->load('client'), 201);
    }

    public function show($id)
    {
        $invoice = Invoice::with(['client', 'project:id,name', 'items', 'payments'])->find($id);
        if (!$invoice) return response()->json(['message' => 'Invoice not found'], 404);
        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) return response()->json(['message' => 'Invoice not found'], 404);

        $validated = $request->validate([
            'status'     => 'sometimes|in:draft,unpaid,paid,overdue,partially_paid,cancelled',
            'project_id' => 'nullable|exists:projects,id',
            'duedate'    => 'sometimes|date',
            'date'       => 'sometimes|date',
            'prevent_overdue_reminders' => 'nullable|boolean',
            'allowed_payment_modes' => 'nullable|string',
            'currency'  => 'nullable|string',
            'sale_agent'=> 'nullable|string',
            'recurring_type' => 'nullable|string',
            'discount_type'  => 'nullable|string',
            'admin_note'=> 'nullable|string',
            'qty_display_mode' => 'nullable|string',
            'client_note'      => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'subtotal'  => 'sometimes|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_val'     => 'nullable|numeric|min:0',
            'tax'       => 'nullable|numeric|min:0',
            'adjustment'=> 'nullable|numeric',
            'total'     => 'sometimes|numeric|min:0',
            'notes'     => 'nullable|string',
            'tags'      => 'nullable|string',
            
            // Addresses
            'billing_street'  => 'nullable|string',
            'billing_city'    => 'nullable|string',
            'billing_state'   => 'nullable|string',
            'billing_zip'     => 'nullable|string',
            'billing_country' => 'nullable|string',
            'shipping_street' => 'nullable|string',
            'shipping_city'   => 'nullable|string',
            'shipping_state'  => 'nullable|string',
            'shipping_zip'    => 'nullable|string',
            'shipping_country'=> 'nullable|string',

            // Items
            'items'          => 'sometimes|array',
            'items.*.description' => 'required|string',
            'items.*.long_description' => 'nullable|string',
            'items.*.qty'        => 'required|numeric|min:0.01',
            'items.*.unit'       => 'nullable|string',
            'items.*.rate'       => 'required|numeric|min:0',
            'items.*.tax_rate'   => 'nullable|numeric|min:0|max:100',
        ]);

        \DB::transaction(function() use ($invoice, $validated) {
            $invoiceData = \Arr::except($validated, ['items']);
            $invoice->update($invoiceData);

            if (isset($validated['items'])) {
                $invoice->items()->delete();
                foreach ($validated['items'] as $item) {
                    $invoice->items()->create($item);
                }
            }
        });

        return response()->json($invoice->load(['client', 'project:id,name']));
    }

    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        if (!$invoice) return response()->json(['message' => 'Invoice not found'], 404);
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted']);
    }
}
