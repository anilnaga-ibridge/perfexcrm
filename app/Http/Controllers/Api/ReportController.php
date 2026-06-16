<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
