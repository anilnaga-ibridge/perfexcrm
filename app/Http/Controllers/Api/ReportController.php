<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Sales summary report
     */
    public function sales(Request $request)
    {
        $year = $request->input('year', now()->year);

        $invoices = DB::table('invoices')
            ->whereYear('created_at', $year)
            ->get();

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $filtered = $invoices->filter(function ($inv) use ($m) {
                return date('n', strtotime($inv->created_at)) == $m;
            });
            $monthly[] = [
                'month'  => $m,
                'total'  => (float)round($filtered->sum('total'), 2),
                'count'  => $filtered->count(),
            ];
        }

        $totals = [
            'total_invoiced' => (float)round($invoices->sum('total'), 2),
            'total_invoices' => $invoices->count(),
        ];

        return response()->json(['monthly' => $monthly, 'totals' => $totals, 'year' => $year]);
    }

    /**
     * Expenses report
     */
    public function expenses(Request $request)
    {
        $year = $request->input('year', now()->year);

        $expenses = DB::table('expenses')
            ->whereYear('date', $year)
            ->get();

        $monthly = [];
        for ($m = 1; $m <= 12; $m++) {
            $filtered = $expenses->filter(function ($exp) use ($m) {
                return date('n', strtotime($exp->date)) == $m;
            });
            $monthly[] = [
                'month' => $m,
                'total' => (float)round($filtered->sum('amount'), 2),
                'count' => $filtered->count(),
            ];
        }

        return response()->json(['monthly' => $monthly, 'year' => $year]);
    }

    /**
     * Finance overview
     */
    public function finance(Request $request)
    {
        $year = $request->input('year', now()->year);

        $income   = DB::table('invoices')->whereYear('created_at', $year)->sum('total') ?? 0;
        $expenses = DB::table('expenses')->whereYear('date', $year)->sum('amount') ?? 0;
        $payments = DB::table('payments')->whereYear('created_at', $year)->sum('amount') ?? 0;

        return response()->json([
            'income'   => (float)$income,
            'expenses' => (float)$expenses,
            'payments' => (float)$payments,
            'profit'   => (float)($income - $expenses),
            'year'     => $year,
        ]);
    }

    /**
     * Leads analytics report
     */
    public function leads(Request $request)
    {
        $year = $request->input('year', now()->year);

        $customerStatus = LeadStatus::where('name', 'Customer')
            ->orWhere('name', 'like', '%Customer%')
            ->orWhere('name', 'like', '%Converted%')
            ->first();
        $customerStatusId = $customerStatus ? $customerStatus->id : null;

        $leads = Lead::whereYear('created_at', $year)
            ->with(['status', 'source', 'assigned'])
            ->get();

        $monthly = [];
        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        for ($m = 1; $m <= 12; $m++) {
            $filtered = $leads->filter(function ($lead) use ($m) {
                return date('n', strtotime($lead->created_at)) == $m;
            });
            $count = $filtered->count();
            $converted = $customerStatusId 
                ? $filtered->where('status_id', $customerStatusId)->count()
                : 0;
            $value = (float) $filtered->sum('lead_value');
            $rate = $count > 0 ? round(($converted / $count) * 100, 1) : 0;

            $monthly[] = [
                'month'           => $m,
                'name'            => $monthNames[$m - 1],
                'count'           => $count,
                'converted'       => $converted,
                'value'           => round($value, 2),
                'conversion_rate' => $rate,
            ];
        }

        $totalLeads = $leads->count();
        $totalConverted = $customerStatusId 
            ? $leads->where('status_id', $customerStatusId)->count()
            : 0;
        $totalValue = (float) $leads->sum('lead_value');
        $overallRate = $totalLeads > 0 ? round(($totalConverted / $totalLeads) * 100, 1) : 0;

        // By Source
        $sources = \App\Models\LeadSource::all();
        $bySource = $sources->map(function ($src) use ($leads, $customerStatusId) {
            $srcLeads = $leads->where('source_id', $src->id);
            $cnt = $srcLeads->count();
            $conv = $customerStatusId ? $srcLeads->where('status_id', $customerStatusId)->count() : 0;
            return [
                'id'              => $src->id,
                'name'            => $src->name,
                'count'           => $cnt,
                'converted'       => $conv,
                'conversion_rate' => $cnt > 0 ? round(($conv / $cnt) * 100, 1) : 0,
                'value'           => round((float) $srcLeads->sum('lead_value'), 2),
            ];
        })->values();

        // By Status
        $statuses = LeadStatus::orderBy('order_num', 'asc')->get();
        $byStatus = $statuses->map(function ($st) use ($leads, $totalLeads) {
            $cnt = $leads->where('status_id', $st->id)->count();
            return [
                'id'         => $st->id,
                'name'       => $st->name,
                'color'      => $st->color ?: '#7367F0',
                'count'      => $cnt,
                'percentage' => $totalLeads > 0 ? round(($cnt / $totalLeads) * 100, 1) : 0,
            ];
        })->values();

        // By Staff / Assignee
        $staffLeads = $leads->groupBy('assigned_id');
        $byStaff = [];
        foreach ($staffLeads as $assignedId => $userLeads) {
            $user = $userLeads->first()->assigned ?? null;
            $cnt = $userLeads->count();
            $conv = $customerStatusId ? $userLeads->where('status_id', $customerStatusId)->count() : 0;
            $byStaff[] = [
                'id'              => $assignedId ?: 0,
                'name'            => $user ? $user->name : 'Unassigned',
                'count'           => $cnt,
                'converted'       => $conv,
                'conversion_rate' => $cnt > 0 ? round(($conv / $cnt) * 100, 1) : 0,
                'value'           => round((float) $userLeads->sum('lead_value'), 2),
            ];
        }
        usort($byStaff, fn($a, $b) => $b['count'] <=> $a['count']);

        return response()->json([
            'year'      => $year,
            'monthly'   => $monthly,
            'totals'    => [
                'total_leads'     => $totalLeads,
                'total_converted' => $totalConverted,
                'conversion_rate' => $overallRate,
                'total_value'     => round($totalValue, 2),
                'sources_count'   => $sources->count(),
            ],
            'by_source' => $bySource,
            'by_status' => $byStatus,
            'by_staff'  => $byStaff,
            'leads'     => $leads->map(function ($l) {
                return [
                    'id'          => $l->id,
                    'name'        => $l->name,
                    'company'     => $l->company ?: '—',
                    'email'       => $l->email ?: '—',
                    'phonenumber' => $l->phonenumber ?: '—',
                    'lead_value'  => (float) $l->lead_value,
                    'status'      => $l->status ? $l->status->name : '—',
                    'status_color'=> $l->status ? $l->status->color : '#7367F0',
                    'source'      => $l->source ? $l->source->name : '—',
                    'assigned'    => $l->assigned ? $l->assigned->name : 'Unassigned',
                    'created_at'  => $l->created_at ? $l->created_at->format('Y-m-d') : '—',
                ];
            }),
        ]);
    }

    /**
     * Team/Activity summary
     */
    public function team(Request $request)
    {
        $users = DB::table('users')
            ->leftJoin('tasks', 'users.id', '=', 'tasks.assigned_to')
            ->selectRaw('users.id, users.name, COUNT(tasks.id) as task_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('task_count')
            ->limit(20)
            ->get();

        return response()->json(['team' => $users]);
    }

    /**
     * Detailed expenses report by category with monthly breakdown
     */
    public function expensesDetailed(Request $request)
    {
        $year = $request->input('year', now()->year);
        $excludeBillable = $request->boolean('exclude_billable', false);

        $categories = ExpenseCategory::orderBy('name')->get();

        $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        $notBillableRows = $this->buildCategoryRows($categories, $year, false);
        $billableRows = $this->buildCategoryRows($categories, $year, true);

        return response()->json([
            'year'           => $year,
            'not_billable'   => $notBillableRows,
            'billable'       => $billableRows,
        ]);
    }

    /**
     * Build category rows for expense reports.
     */
    private function buildCategoryRows($categories, $year, $billable) {
        $rows = [];
        foreach ($categories as $cat) {
            $expenses = Expense::where('category_id', $cat->id)
                ->whereYear('date', $year)
                ->where('billable', $billable)
                ->get();

            $monthly = array_fill(0, 12, 0.0);
            $total = 0.0;
            foreach ($expenses as $e) {
                $m = (int) date('n', strtotime($e->date)) - 1;
                $monthly[$m] += (float) $e->amount;
                $total += (float) $e->amount;
            }
            $monthly = array_map(fn($v) => round($v, 2), $monthly);
            $rows[] = [
                'category' => $cat->name,
                'monthly'  => $monthly,
                'total'    => round($total, 2),
            ];
        }

        // Totals row
        $totals = array_fill(0, 12, 0.0);
        $grandTotal = 0.0;
        foreach ($rows as $r) {
            foreach ($r['monthly'] as $i => $v) {
                $totals[$i] += $v;
            }
            $grandTotal += $r['total'];
        }
        $totals = array_map(fn($v) => round($v, 2), $totals);
        $rows[] = [
            'category' => 'Total',
            'monthly'  => $totals,
            'total'    => round($grandTotal, 2),
            'is_total' => true,
        ];

        return $rows;
    }
}
