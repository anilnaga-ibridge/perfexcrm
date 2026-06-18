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

        function buildCategoryRows($categories, $year, $billable) {
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

        $notBillableRows = buildCategoryRows($categories, $year, false);
        $billableRows = buildCategoryRows($categories, $year, true);

        return response()->json([
            'year'           => $year,
            'not_billable'   => $notBillableRows,
            'billable'       => $billableRows,
        ]);
    }
}
