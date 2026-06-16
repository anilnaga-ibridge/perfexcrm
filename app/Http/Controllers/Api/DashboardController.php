<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Project;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Leads ─────────────────────────────────────────────────
        $customerStatus   = LeadStatus::where('name', 'Customer')->first();
        $customerStatusId = $customerStatus?->id;

        $totalLeads     = Lead::count();
        $convertedLeads = $customerStatusId ? Lead::where('status_id', $customerStatusId)->count() : 0;

        // Leads overview by status
        $statuses     = LeadStatus::orderBy('order_num')->get();
        $leadsOverview = $statuses->map(fn($s) => [
            'name'       => $s->name,
            'color'      => $s->color,
            'count'      => Lead::where('status_id', $s->id)->count(),
            'percentage' => $totalLeads > 0
                ? round(Lead::where('status_id', $s->id)->count() / $totalLeads * 100, 2)
                : 0,
        ])->values();

        // ── Clients ───────────────────────────────────────────────
        $totalClients = Client::count();

        // ── Invoices ──────────────────────────────────────────────
        $totalInvoices      = Invoice::count();
        $draftCount         = Invoice::where('status', 'draft')->count();
        $unpaidCount        = Invoice::where('status', 'unpaid')->count();
        $partialCount       = Invoice::where('status', 'partially_paid')->count();
        $overdueCount       = Invoice::where('status', 'overdue')->count();
        $paidCount          = Invoice::where('status', 'paid')->count();
        $cancelledCount     = Invoice::where('status', 'cancelled')->count();

        $awaitingPayment    = $unpaidCount + $partialCount + $overdueCount;

        $outstandingAmount  = Invoice::whereIn('status', ['unpaid', 'overdue', 'partially_paid'])->sum('total');
        $pastDueAmount      = Invoice::where('status', 'overdue')->sum('total');
        $paidAmount         = Invoice::where('status', 'paid')->sum('total');

        $invoiceOverview = [];
        $statusMap = [
            'draft'          => ['label' => 'Draft',          'count' => $draftCount],
            'unpaid'         => ['label' => 'Unpaid',         'count' => $unpaidCount],
            'paid'           => ['label' => 'Paid',           'count' => $paidCount],
            'overdue'        => ['label' => 'Overdue',        'count' => $overdueCount],
            'partially_paid' => ['label' => 'Partially Paid', 'count' => $partialCount],
            'cancelled'      => ['label' => 'Cancelled',      'count' => $cancelledCount],
        ];

        foreach ($statusMap as $key => $s) {
            $invoiceOverview[] = [
                'status'     => $key,
                'label'      => $s['label'],
                'count'      => $s['count'],
                'percentage' => $totalInvoices > 0
                    ? round($s['count'] / $totalInvoices * 100, 2)
                    : 0,
            ];
        }

        // ── To-Do Items (mock for now) ─────────────────────────────
        $toDoItems = [
            ['text' => 'Mock Turtle, who looked at the.',   'date' => now()->toDateTimeString(), 'done' => false],
            ['text' => 'Oh, I shouldn\'t want YOURS: I.',   'date' => now()->toDateTimeString(), 'done' => false],
            ['text' => 'Lory, as soon as look at a king.', 'date' => now()->toDateTimeString(), 'done' => false],
            ['text' => 'Alice replied in an undertone.',    'date' => now()->subDays(2)->toDateTimeString(), 'done' => true],
            ['text' => 'Alice. \'You must be,\' said the Caterpillar.', 'date' => now()->subDays(2)->toDateTimeString(), 'done' => true],
        ];

        return response()->json([
            // Leads
            'total_leads'     => $totalLeads,
            'converted_leads' => $convertedLeads,
            'leads_overview'  => $leadsOverview,

            // Clients
            'total_clients' => $totalClients,

            // Invoices
            'invoices_awaiting_payment' => $awaitingPayment . ' / ' . $totalInvoices,
            'total_invoices'            => $totalInvoices,
            'invoice_overview'          => $invoiceOverview,
            'outstanding_amount'        => round($outstandingAmount, 2),
            'past_due_amount'           => round($pastDueAmount, 2),
            'paid_amount'               => round($paidAmount, 2),

            // Projects / Tasks
            'projects_in_progress' => Project::where('status', 'In Progress')->count() . ' / ' . Project::count(),
            'tasks_not_finished'   => Task::where('status', '!=', 'Complete')->count() . ' / ' . Task::count(),

            // To-Do
            'todo_items' => $toDoItems,
        ]);
    }
}
