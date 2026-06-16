<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecurringInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = RecurringInvoice::with('client:id,company', 'project:id,name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('client', fn($q) => $q->where('company', 'like', '%' . $request->search . '%'));
        }

        $perPage = $request->input('per_page', 25);
        $data    = $query->latest()->paginate($perPage);

        $stats = [
            'total'   => RecurringInvoice::count(),
            'active'  => RecurringInvoice::where('status', 'active')->count(),
            'paused'  => RecurringInvoice::where('status', 'paused')->count(),
            'stopped' => RecurringInvoice::where('status', 'stopped')->count(),
        ];

        return response()->json(['recurring_invoices' => $data, 'stats' => $stats]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'              => 'required|exists:clients,id',
            'project_id'             => 'nullable|exists:projects,id',
            'frequency'              => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'cycles'                 => 'nullable|integer|min:0',
            'date_from'              => 'required|date',
            'date_to'                => 'nullable|date|after_or_equal:date_from',
            'subtotal'               => 'nullable|numeric|min:0',
            'tax'                    => 'nullable|numeric|min:0',
            'total'                  => 'nullable|numeric|min:0',
            'discount_type'          => 'nullable|in:no_discount,before_tax,after_tax',
            'discount_percent'       => 'nullable|numeric|min:0',
            'discount_val'           => 'nullable|numeric|min:0',
            'adjustment'             => 'nullable|numeric',
            'currency'               => 'nullable|string|max:10',
            'allowed_payment_modes'  => 'nullable|string',
            'sale_agent'             => 'nullable|string|max:255',
            'qty_display_mode'       => 'nullable|in:qty,hours,qty_hours',
            'notes'                  => 'nullable|string',
            'admin_note'             => 'nullable|string',
            'client_note'            => 'nullable|string',
            'terms_conditions'       => 'nullable|string',
            'tags'                   => 'nullable|string',
            // Address fields
            'billing_street'         => 'nullable|string',
            'billing_city'           => 'nullable|string|max:255',
            'billing_state'          => 'nullable|string|max:255',
            'billing_zip'            => 'nullable|string|max:50',
            'billing_country'        => 'nullable|string|max:255',
            'shipping_street'        => 'nullable|string',
            'shipping_city'          => 'nullable|string|max:255',
            'shipping_state'         => 'nullable|string|max:255',
            'shipping_zip'           => 'nullable|string|max:50',
            'shipping_country'       => 'nullable|string|max:255',
            // Items
            'items'                  => 'nullable|array',
            'items.*.description'    => 'required_with:items|string',
            'items.*.long_description' => 'nullable|string',
            'items.*.qty'            => 'nullable|numeric|min:0',
            'items.*.unit'           => 'nullable|string|max:100',
            'items.*.rate'           => 'nullable|numeric|min:0',
            'items.*.tax_rate'       => 'nullable|numeric|min:0',
        ]);

        $validated['next_invoice_date'] = $this->calcNextDate($validated['date_from'], $validated['frequency']);

        return DB::transaction(function () use ($validated, $request) {
            $items = $validated['items'] ?? [];
            unset($validated['items']);

            $ri = RecurringInvoice::create($validated);

            foreach ($items as $order => $item) {
                $ri->items()->create([
                    'description'      => $item['description'] ?? '',
                    'long_description' => $item['long_description'] ?? null,
                    'qty'              => $item['qty'] ?? 1,
                    'unit'             => $item['unit'] ?? null,
                    'rate'             => $item['rate'] ?? 0,
                    'tax_rate'         => $item['tax_rate'] ?? 0,
                    'order'            => $order,
                ]);
            }

            return response()->json($ri->load(['client', 'project', 'items']), 201);
        });
    }

    public function show($id)
    {
        $ri = RecurringInvoice::with(['client', 'project:id,name', 'items'])->find($id);
        if (!$ri) return response()->json(['message' => 'Not found'], 404);
        return response()->json($ri);
    }

    public function update(Request $request, $id)
    {
        $ri = RecurringInvoice::find($id);
        if (!$ri) return response()->json(['message' => 'Not found'], 404);

        $validated = $request->validate([
            'status'                 => 'sometimes|in:active,paused,stopped',
            'frequency'              => 'sometimes|in:daily,weekly,monthly,quarterly,yearly',
            'cycles'                 => 'nullable|integer|min:0',
            'date_to'                => 'nullable|date',
            'notes'                  => 'nullable|string',
            'admin_note'             => 'nullable|string',
            'client_note'            => 'nullable|string',
            'terms_conditions'       => 'nullable|string',
            'tags'                   => 'nullable|string',
            'subtotal'               => 'nullable|numeric|min:0',
            'tax'                    => 'nullable|numeric|min:0',
            'total'                  => 'nullable|numeric|min:0',
            'discount_type'          => 'nullable|in:no_discount,before_tax,after_tax',
            'discount_percent'       => 'nullable|numeric|min:0',
            'discount_val'           => 'nullable|numeric|min:0',
            'adjustment'             => 'nullable|numeric',
            'currency'               => 'nullable|string|max:10',
            'allowed_payment_modes'  => 'nullable|string',
            'sale_agent'             => 'nullable|string|max:255',
            'qty_display_mode'       => 'nullable|in:qty,hours,qty_hours',
            'items'                  => 'nullable|array',
            'items.*.description'    => 'required_with:items|string',
            'items.*.long_description' => 'nullable|string',
            'items.*.qty'            => 'nullable|numeric|min:0',
            'items.*.unit'           => 'nullable|string|max:100',
            'items.*.rate'           => 'nullable|numeric|min:0',
            'items.*.tax_rate'       => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $ri) {
            $items = $validated['items'] ?? null;
            unset($validated['items']);

            $ri->update($validated);

            if ($items !== null) {
                $ri->items()->delete();
                foreach ($items as $order => $item) {
                    $ri->items()->create([
                        'description'      => $item['description'] ?? '',
                        'long_description' => $item['long_description'] ?? null,
                        'qty'              => $item['qty'] ?? 1,
                        'unit'             => $item['unit'] ?? null,
                        'rate'             => $item['rate'] ?? 0,
                        'tax_rate'         => $item['tax_rate'] ?? 0,
                        'order'            => $order,
                    ]);
                }
            }

            return response()->json($ri->load(['client', 'project', 'items']));
        });
    }

    public function destroy($id)
    {
        $ri = RecurringInvoice::find($id);
        if (!$ri) return response()->json(['message' => 'Not found'], 404);
        $ri->delete();
        return response()->json(['message' => 'Deleted']);
    }

    private function calcNextDate(string $from, string $frequency): string
    {
        $date = Carbon::parse($from);
        return match ($frequency) {
            'daily'     => $date->addDay()->toDateString(),
            'weekly'    => $date->addWeek()->toDateString(),
            'monthly'   => $date->addMonth()->toDateString(),
            'quarterly' => $date->addMonths(3)->toDateString(),
            'yearly'    => $date->addYear()->toDateString(),
            default     => $date->addMonth()->toDateString(),
        };
    }
}
