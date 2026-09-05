<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Invoice;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user() && !$request->user()->hasPermission('Payments.view')) {
            abort(403, 'Unauthorized. Missing required permission: Payments.view');
        }

        $query = Payment::with('invoice:id,number,client_id', 'invoice.client:id,company');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('transactionid', 'like', "%{$search}%")
                  ->orWhere('paymentmode', 'like', "%{$search}%")
                  ->orWhereHas('invoice', fn($inv) => $inv->where('number', 'like', "%{$search}%"))
                  ->orWhereHas('invoice.client', fn($c) => $c->where('company', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('paymentmode')) {
            $query->where('paymentmode', $request->input('paymentmode'));
        }

        $perPage = min($request->input('per_page', 25), 100);
        $payments = $query->orderBy('date', 'desc')->paginate($perPage);

        // Summary stats
        $stats = [
            'total'         => Payment::count(),
            'total_amount'  => Payment::sum('amount'),
            'bank_amount'   => Payment::where('paymentmode', 'Bank')->sum('amount'),
            'paypal_amount' => Payment::where('paymentmode', 'PayPal')->sum('amount'),
            'stripe_amount' => Payment::where('paymentmode', 'Stripe')->sum('amount'),
            'cash_amount'   => Payment::where('paymentmode', 'Cash')->sum('amount'),
        ];

        return response()->json([
            'payments' => $payments,
            'stats'    => $stats,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user() && !$request->user()->hasPermission('Payments.create')) {
            abort(403, 'Unauthorized. Missing required permission: Payments.create');
        }

        $validated = $request->validate([
            'invoice_id'    => 'required|exists:invoices,id',
            'amount'        => 'required|numeric|min:0.01',
            'paymentmode'   => 'nullable|string',
            'date'          => 'required|date',
            'transactionid' => 'nullable|string',
            'note'          => 'nullable|string',
        ]);

        $payment = Payment::create($validated);

        // Update invoice status atomically to prevent race conditions
        $invoice = Invoice::find($validated['invoice_id']);
        if ($invoice) {
            \DB::transaction(function () use ($invoice) {
                $paidSum = Payment::where('invoice_id', $invoice->id)->lockForUpdate()->sum('amount');
                if ($paidSum >= $invoice->total) {
                    $invoice->update(['status' => 'paid']);
                } elseif ($paidSum > 0) {
                    $invoice->update(['status' => 'partially_paid']);
                }
            });
        }

        return response()->json($payment->load('invoice.client'), 201);
    }

    public function show($id)
    {
        $payment = Payment::with(['invoice.client'])->find($id);
        if (!$payment) return response()->json(['message' => 'Payment not found'], 404);
        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Payments.edit')) {
            abort(403, 'Unauthorized. Missing required permission: Payments.edit');
        }

        $payment = Payment::find($id);
        if (!$payment) return response()->json(['message' => 'Payment not found'], 404);

        $validated = $request->validate([
            'amount'        => 'sometimes|numeric|min:0.01',
            'paymentmode'   => 'sometimes|string',
            'date'          => 'sometimes|date',
            'transactionid' => 'nullable|string',
            'note'          => 'nullable|string',
        ]);

        $payment->update($validated);

        // Update invoice status after payment update
        $invoice = Invoice::find($payment->invoice_id);
        if ($invoice) {
            $paidSum = Payment::where('invoice_id', $invoice->id)->sum('amount');
            if ($paidSum >= $invoice->total) {
                $invoice->update(['status' => 'paid']);
            } elseif ($paidSum > 0) {
                $invoice->update(['status' => 'partially_paid']);
            } else {
                $invoice->update(['status' => 'unpaid']);
            }
        }

        return response()->json($payment->load('invoice.client'));
    }

    public function destroy(Request $request, $id)
    {
        if ($request->user() && !$request->user()->hasPermission('Payments.delete')) {
            abort(403, 'Unauthorized. Missing required permission: Payments.delete');
        }

        $payment = Payment::find($id);
        if (!$payment) return response()->json(['message' => 'Payment not found'], 404);
        $invoiceId = $payment->invoice_id;
        $payment->delete();

        // Update invoice status after payment deletion
        $invoice = Invoice::find($invoiceId);
        if ($invoice) {
            $paidSum = Payment::where('invoice_id', $invoice->id)->sum('amount');
            if ($paidSum == 0) {
                $invoice->update(['status' => 'unpaid']);
            } else {
                $invoice->update(['status' => 'partially_paid']);
            }
        }

        return response()->json(['message' => 'Payment deleted']);
    }
}
