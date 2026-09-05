<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Client;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Reminder;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\TicketDepartment;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $staffId = $user ? $user->id : null;

        // ── Leads ─────────────────────────────────────────────────
        $customerStatus   = LeadStatus::where('name', 'Customer')->first();
        $customerStatusId = $customerStatus?->id;

        $totalLeads     = Lead::count();
        $convertedLeads = $customerStatusId ? Lead::where('status_id', $customerStatusId)->count() : 0;

        // Leads overview by status
        $statuses     = LeadStatus::orderBy('order_num')->get();
        $leadsOverview = $statuses->map(fn($s) => [
            'name'       => $s->name,
            'color'      => $s->color ?? '#7367F0',
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

        // ── Estimates Overview ────────────────────────────────────
        $estimateOverview = [
            ['label' => 'Draft',    'count' => 0, 'percentage' => 0],
            ['label' => 'Not Sent', 'count' => 0, 'percentage' => 0],
            ['label' => 'Sent',     'count' => 0, 'percentage' => 0],
            ['label' => 'Expired',  'count' => 0, 'percentage' => 0],
            ['label' => 'Declined', 'count' => 0, 'percentage' => 0],
            ['label' => 'Accepted', 'count' => 0, 'percentage' => 0],
        ];

        // ── Proposals Overview ────────────────────────────────────
        $proposalOverview = [
            ['label' => 'Draft',    'count' => 0, 'percentage' => 0],
            ['label' => 'Sent',     'count' => 0, 'percentage' => 0],
            ['label' => 'Open',     'count' => 0, 'percentage' => 0],
            ['label' => 'Revised',  'count' => 0, 'percentage' => 0],
            ['label' => 'Declined', 'count' => 0, 'percentage' => 0],
            ['label' => 'Accepted', 'count' => 0, 'percentage' => 0],
        ];

        // ── Projects / Tasks ──────────────────────────────────────
        $totalProjects      = Project::count();
        $inProgressProjects = Project::where('status', 'In Progress')->count();
        $totalTasks         = Task::count();
        $notFinishedTasks   = Task::where('status', '!=', 'Complete')->count();

        $projectStatuses = ['Not Started', 'In Progress', 'On Hold', 'Cancelled', 'Finished'];
        $projectStatusOverview = collect($projectStatuses)->map(function ($status) use ($totalProjects) {
            $count = Project::where('status', $status)->count();
            return [
                'name'       => $status,
                'count'      => $count,
                'percentage' => $totalProjects > 0 ? round($count / $totalProjects * 100, 2) : 0,
            ];
        });

        // ── Tickets Overview ──────────────────────────────────────
        $totalTickets   = Ticket::count();
        $ticketStatuses = ['Open', 'In Progress', 'Answered', 'On Hold', 'Closed'];
        $ticketStatusOverview = collect($ticketStatuses)->map(function ($status) use ($totalTickets) {
            $count = Ticket::where('status', $status)->count();
            return [
                'name'       => $status,
                'count'      => $count,
                'percentage' => $totalTickets > 0 ? round($count / $totalTickets * 100, 2) : 0,
            ];
        });

        // Tickets by department
        $departments = TicketDepartment::all();
        $departmentTickets = $departments->map(function ($d) {
            return [
                'name'  => $d->name,
                'count' => Ticket::where('department_id', $d->id)->count(),
            ];
        });

        // ── Live Tasks List ───────────────────────────────────────
        $myTasksQuery = Task::with(['assignee', 'client']);
        if ($staffId) {
            $myTasksQuery->where(function($q) use ($staffId) {
                $q->where('assigned_to', $staffId)
                  ->orWhereNull('assigned_to');
            });
        }
        $tasksList = $myTasksQuery->latest()->limit(15)->get()->map(function ($t) {
            return [
                'id'          => $t->id,
                'name'        => $t->name,
                'project'     => $t->related_to_type === 'Project' ? 'Project #' . $t->related_to_id : ($t->client ? $t->client->company : ''),
                'status'      => $t->status ?: 'Not Started',
                'priority'    => $t->priority ?: 'Medium',
                'start'       => $t->start_date ? $t->start_date->format('Y-m-d') : '',
                'due'         => $t->due_date ? $t->due_date->format('Y-m-d') : '',
                'assigned'    => $t->assignee ? $t->assignee->name : 'Unassigned',
                'statusClass' => $t->status === 'In Progress' ? 'badge-blue' : ($t->status === 'Complete' ? 'badge-green' : 'badge-default'),
            ];
        });

        // ── Live Projects List ────────────────────────────────────
        $projectsList = Project::with('client')->latest()->limit(10)->get()->map(function ($p) {
            return [
                'id'          => $p->id,
                'name'        => $p->name,
                'client'      => $p->client ? $p->client->company : 'N/A',
                'billing'     => $p->billing_type ?: 'Fixed Rate',
                'status'      => $p->status ?: 'In Progress',
                'progress'    => $p->progress ?? 0,
                'start_date'  => $p->start_date ? $p->start_date->format('Y-m-d') : '',
                'deadline'    => $p->deadline ? $p->deadline->format('Y-m-d') : '',
            ];
        });

        // ── Live Reminders List ───────────────────────────────────
        $remindersList = Reminder::with(['client', 'remindTo'])
            ->when($staffId, fn($q) => $q->where('remind_to', $staffId)->orWhere('created_by', $staffId))
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($r) {
                return [
                    'id'          => $r->id,
                    'description' => $r->description,
                    'date'        => $r->date ? $r->date->format('Y-m-d H:i') : '',
                    'client'      => $r->client ? $r->client->company : '',
                    'remind_to'   => $r->remindTo ? $r->remindTo->name : '',
                    'is_notified' => $r->is_notified,
                ];
            });

        // ── Live Tickets List ─────────────────────────────────────
        $ticketsList = Ticket::with(['client', 'assignee', 'department'])->latest()->limit(10)->get()->map(function ($t) {
            return [
                'id'          => $t->id,
                'number'      => $t->id,
                'subject'     => $t->subject,
                'client'      => $t->client ? $t->client->company : 'General',
                'department'  => $t->department ? $t->department->name : 'Support',
                'priority'    => $t->priority ?: 'Medium',
                'status'      => $t->status ?: 'Open',
                'statusClass' => $t->status === 'Open' ? 'badge-red' : ($t->status === 'Closed' ? 'badge-green' : 'badge-blue'),
                'last_reply'  => $t->last_reply_at ? $t->last_reply_at->diffForHumans() : $t->created_at->diffForHumans(),
                'excerpt'     => substr(strip_tags($t->message ?? ''), 0, 80),
            ];
        });

        // ── Live Announcements ────────────────────────────────────
        $announcementsList = Announcement::latest()->limit(10)->get()->map(function ($a) {
            return [
                'id'      => $a->id,
                'subject' => $a->subject,
                'message' => $a->message,
                'date'    => $a->created_at ? $a->created_at->format('Y-m-d') : '',
            ];
        });

        // ── Live Activity Logs ────────────────────────────────────
        $activityLogsList = ActivityLog::with('user')->latest()->limit(15)->get()->map(function ($act) {
            return [
                'id'         => $act->id,
                'user'       => $act->user_name ?: ($act->user ? $act->user->name : 'Staff Member'),
                'action'     => $act->description,
                'project'    => '',
                'detail'     => '',
                'time'       => $act->created_at ? $act->created_at->diffForHumans() : 'Just now',
                'dotClass'   => 'feed-dot--blue',
                'colorClass' => 'dot-blue',
            ];
        });

        // ── Expiring Contracts ────────────────────────────────────
        $expiringContracts = Contract::with(['client', 'contractType'])
            ->whereNotNull('end_date')
            ->orderBy('end_date', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id'       => $c->id,
                    'subject'  => $c->subject,
                    'customer' => $c->client ? $c->client->company : 'N/A',
                    'type'     => $c->contractType ? $c->contractType->name : 'General',
                    'value'    => $c->value,
                    'start'    => $c->start_date ? $c->start_date->format('Y-m-d') : '',
                    'end'      => $c->end_date ? $c->end_date->format('Y-m-d') : '',
                    'status'   => $c->status,
                ];
            });

        // ── Staff Tickets Performance Report ──────────────────────
        $staffReport = User::limit(10)->get()->map(function ($u) {
            $assignedCount = Ticket::where('assigned_to', $u->id)->count();
            $openCount     = Ticket::where('assigned_to', $u->id)->where('status', 'Open')->count();
            $closedCount   = Ticket::where('assigned_to', $u->id)->where('status', 'Closed')->count();

            return [
                'id'        => $u->id,
                'name'      => $u->name,
                'assigned'  => $assignedCount,
                'open'      => $openCount,
                'closed'    => $closedCount,
                'replies'   => 0,
                'replyTime' => '-',
            ];
        });

        // ── Goals ─────────────────────────────────────────────────
        $goalsList = Goal::latest()->limit(8)->get()->map(function ($g) {
            $pct = ($g->target_value > 0) ? round(($g->current_value / $g->target_value) * 100, 2) : 0;
            return [
                'id'            => $g->id,
                'title'         => $g->subject,
                'subtitle'      => $g->description ?: 'CRM Performance Target',
                'achieved'      => (string)$g->current_value,
                'progressPct'   => min(100, $pct),
                'progressColor' => $pct >= 100 ? '#28C76F' : ($pct > 40 ? '#7367F0' : '#FF9F43'),
            ];
        });

        // ── To-Do Items ───────────────────────────────────────────
        $toDoItems = Todo::where('staff_id', $staffId)
            ->orderBy('done')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($t) => [
                'id'          => $t->id,
                'text'        => $t->description,
                'date'        => $t->created_at->toDateTimeString(),
                'done'        => $t->done,
                'sort_order'  => $t->sort_order,
            ]);

        return response()->json([
            // Leads
            'total_leads'     => $totalLeads,
            'converted_leads' => $convertedLeads,
            'leads_overview'  => $leadsOverview,

            // Clients
            'total_clients'   => $totalClients,

            // Invoices
            'invoices_awaiting_payment' => $awaitingPayment . ' / ' . $totalInvoices,
            'total_invoices'            => $totalInvoices,
            'invoice_overview'          => $invoiceOverview,
            'outstanding_amount'        => round($outstandingAmount, 2),
            'past_due_amount'           => round($pastDueAmount, 2),
            'paid_amount'               => round($paidAmount, 2),

            // Estimates & Proposals
            'estimate_overview' => $estimateOverview,
            'proposal_overview' => $proposalOverview,

            // Projects / Tasks
            'projects_in_progress'     => $inProgressProjects . ' / ' . $totalProjects,
            'tasks_not_finished'       => $notFinishedTasks . ' / ' . $totalTasks,
            'project_status_overview'  => $projectStatusOverview,
            'ticket_status_overview'   => $ticketStatusOverview,
            'department_tickets'       => $departmentTickets,

            // Live Collections for Tables & Feeds
            'tasks'               => $tasksList,
            'projects'            => $projectsList,
            'reminders'           => $remindersList,
            'tickets'             => $ticketsList,
            'announcements'       => $announcementsList,
            'activity_logs'       => $activityLogsList,
            'expiring_contracts'  => $expiringContracts,
            'staff_report'        => $staffReport,
            'goals'               => $goalsList,

            // To-Do
            'todo_items'          => $toDoItems,
        ]);
    }
}

