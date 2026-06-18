<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\CreditNote;
use App\Models\PredefinedItem;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function invoices(Request $request)
    {
        $query = Invoice::with('client');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->input('status'));
        }

        if ($request->filled('sale_agent')) {
            $query->where('sale_agent', $request->input('sale_agent'));
        }

        $perPage = $request->input('per_page', 25);
        $invoices = $query->orderBy('date', 'desc')->paginate($perPage);

        $invoices->getCollection()->transform(function ($inv) {
            $totalTax = (float) $inv->tax;
            $amountWithTax = (float) $inv->total;
            $payments = (float) $inv->payments()->sum('amount');
            $amountOpen = $amountWithTax - $payments;

            return [
                'id' => $inv->id,
                'number' => $inv->number,
                'customer' => $inv->client?->company ?? '—',
                'date' => $inv->date?->format('Y-m-d'),
                'duedate' => $inv->duedate?->format('Y-m-d'),
                'amount' => (float) $inv->subtotal,
                'amount_with_tax' => $amountWithTax,
                'total_tax' => $totalTax,
                'tax1' => $totalTax,
                'discount' => (float) $inv->discount_val,
                'adjustment' => (float) $inv->adjustment,
                'applied_credits' => 0,
                'amount_open' => round(max($amountOpen, 0), 2),
                'status' => $inv->status,
            ];
        });

        return response()->json(['invoices' => $invoices]);
    }

    public function items(Request $request)
    {
        $query = PredefinedItem::query();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('name', 'like', "%{$s}%");
        }

        $perPage = $request->input('per_page', 25);
        $items = $query->orderBy('name')->paginate($perPage);

        return response()->json(['items' => $items]);
    }

    public function payments(Request $request)
    {
        $query = Payment::with('invoice.client');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('transactionid', 'like', "%{$s}%")
                  ->orWhere('note', 'like', "%{$s}%")
                  ->orWhereHas('invoice', fn($i) => $i->where('number', 'like', "%{$s}%"));
            });
        }

        $perPage = $request->input('per_page', 25);
        $payments = $query->orderBy('date', 'desc')->paginate($perPage);

        $payments->getCollection()->transform(function ($p) {
            return [
                'id' => $p->id,
                'number' => $p->id,
                'date' => $p->date?->format('Y-m-d'),
                'invoice_number' => $p->invoice?->number ?? '—',
                'customer' => $p->invoice?->client?->company ?? '—',
                'payment_mode' => $p->paymentmode ?? '—',
                'transaction_id' => $p->transactionid ?? '—',
                'note' => $p->note ?? '—',
                'amount' => (float) $p->amount,
            ];
        });

        return response()->json(['payments' => $payments]);
    }

    public function creditNotes(Request $request)
    {
        $query = CreditNote::with('client');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhereHas('client', fn($c) => $c->where('company', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $notes = $query->orderBy('date', 'desc')->paginate($perPage);

        $notes->getCollection()->transform(function ($cn) {
            $refunded = (float) DB::table('credit_notes')->where('id', $cn->id)->sum('amount') * 0;
            return [
                'id' => $cn->id,
                'number' => $cn->number,
                'date' => $cn->date?->format('Y-m-d'),
                'customer' => $cn->client?->company ?? '—',
                'reference' => $cn->reference ?? '—',
                'amount' => (float) $cn->amount,
                'amount_with_tax' => (float) $cn->amount,
                'total_tax' => 0,
                'discount' => 0,
                'adjustment' => 0,
                'remaining_amount' => (float) $cn->amount - $refunded,
                'refunded_amount' => $refunded,
                'status' => $cn->status,
            ];
        });

        return response()->json(['credit_notes' => $notes]);
    }

    public function proposals(Request $request)
    {
        $query = DB::table('estimates');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%{$s}%")
                  ->orWhere('client_id', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $items = $query->orderBy('date', 'desc')->paginate($perPage);

        $items->getCollection()->transform(function ($e) {
            $client = $e->client_id ? Client::find($e->client_id) : null;
            return [
                'number' => 'PRO-' . str_pad($e->id, 6, '0', STR_PAD_LEFT),
                'subject' => $e->note ? substr($e->note, 0, 50) : '—',
                'to' => $client?->company ?? '—',
                'date' => $e->date,
                'open_till' => $e->expiry_date,
                'amount' => (float) $e->subtotal,
                'amount_with_tax' => (float) $e->total,
                'total_tax' => (float) ($e->tax ?? 0),
                'tax1_18' => 0,
                'tax1_20' => 0,
                'tax3_5' => 0,
                'discount' => (float) ($e->discount ?? 0),
                'adjustment' => 0,
                'status' => $e->status,
            ];
        });

        return response()->json(['proposals' => $items]);
    }

    public function estimates(Request $request)
    {
        $query = DB::table('estimates');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('number', 'like', "%{$s}%")
                  ->orWhere('client_id', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->input('status'));
        }

        $perPage = $request->input('per_page', 25);
        $items = $query->orderBy('date', 'desc')->paginate($perPage);

        $items->getCollection()->transform(function ($e) {
            $client = $e->client_id ? Client::find($e->client_id) : null;
            return [
                'number' => $e->number,
                'subject' => $e->note ? substr($e->note, 0, 50) : '—',
                'to' => $client?->company ?? '—',
                'date' => $e->date,
                'open_till' => $e->expiry_date,
                'amount' => (float) $e->subtotal,
                'amount_with_tax' => (float) $e->total,
                'total_tax' => (float) ($e->tax ?? 0),
                'tax1_18' => 0,
                'tax1_20' => 0,
                'tax3_5' => 0,
                'discount' => (float) ($e->discount ?? 0),
                'adjustment' => 0,
                'status' => $e->status,
            ];
        });

        return response()->json(['estimates' => $items]);
    }

    public function customers(Request $request)
    {
        $query = Client::withCount('invoices as total_invoices')
            ->withSum('invoices as total_amount', 'subtotal')
            ->withSum('invoices as total_amount_with_tax', 'total');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('company', 'like', "%{$s}%");
        }

        $perPage = $request->input('per_page', 25);
        $clients = $query->orderBy('company')->paginate($perPage);

        $clients->getCollection()->transform(function ($c) {
            return [
                'id' => $c->id,
                'company' => $c->company,
                'total_invoices' => (int) $c->total_invoices,
                'amount' => round((float) ($c->total_amount ?? 0), 2),
                'amount_with_tax' => round((float) ($c->total_amount_with_tax ?? 0), 2),
            ];
        });

        return response()->json(['customers' => $clients]);
    }

    public function charts(Request $request)
    {
        $year = $request->input('year', now()->year);

        $sales = DB::table('invoices')
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $payments = DB::table('payments')
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $s = $sales->firstWhere('month', $m);
            $p = $payments->firstWhere('month', $m);
            $monthly[] = [
                'month' => $m,
                'invoices' => (int) ($s->count ?? 0),
                'invoice_total' => round((float) ($s->total ?? 0), 2),
                'payments' => (int) ($p->count ?? 0),
                'payment_total' => round((float) ($p->total ?? 0), 2),
            ];
        }

        return response()->json(['monthly' => $monthly, 'year' => $year]);
    }

    public function totalIncome(Request $request)
    {
        $year = $request->input('year', now()->year);

        $invoiced = DB::table('invoices')->whereYear('created_at', $year)->sum('total') ?? 0;
        $paid = DB::table('payments')->whereYear('created_at', $year)->sum('amount') ?? 0;
        $outstanding = (float) $invoiced - (float) $paid;

        return response()->json([
            'year' => $year,
            'invoiced' => round((float) $invoiced, 2),
            'paid' => round((float) $paid, 2),
            'outstanding' => round(max($outstanding, 0), 2),
        ]);
    }

    public function paymentModes(Request $request)
    {
        $modes = DB::table('payments')
            ->selectRaw('COALESCE(NULLIF(paymentmode, ""), "Other") as mode, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('mode')
            ->orderByDesc('total')
            ->get()
            ->map(fn($m) => [
                'mode' => $m->mode,
                'count' => (int) $m->count,
                'total' => round((float) $m->total, 2),
            ]);

        return response()->json(['modes' => $modes]);
    }

    public function customerGroups(Request $request)
    {
        $groups = DB::table('clients')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->selectRaw('clients.company, COUNT(invoices.id) as total_invoices, COALESCE(SUM(invoices.total), 0) as total_value')
            ->groupBy('clients.id', 'clients.company')
            ->orderByDesc('total_value')
            ->get()
            ->map(fn($g) => [
                'company' => $g->company,
                'total_invoices' => (int) $g->total_invoices,
                'total_value' => round((float) $g->total_value, 2),
            ]);

        return response()->json(['groups' => $groups]);
    }
}
