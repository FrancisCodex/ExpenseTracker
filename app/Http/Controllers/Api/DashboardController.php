<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
        public function dashboardData()
    {
        $userId = auth()->id();

        $totalIncome = Income::where('user_id', $userId)->sum('amount');
        $totalExpenses = Expense::where('user_id', $userId)->sum('amount');
        $netIncome = $totalIncome - $totalExpenses;

        $todaysIncome = Income::where('user_id', $userId)
                            ->whereDate('created_at', Carbon::today())
                            ->sum('amount');
        $todaysExpenses = Expense::where('user_id', $userId)
                                ->whereDate('created_at', Carbon::today())
                                ->sum('amount');

        $currentMonthIncomes = Income::where('user_id', $userId)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->get(['created_at as date', 'amount']);

        $currentMonthExpenses = Expense::where('user_id', $userId)
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->get(['created_at as date', 'amount']);

                                    $incomeCategories = Income::with('category')
                                    ->select('category_id', DB::raw('count(*) as incomes_count'))
                                    ->where('user_id', $userId)
                                    ->groupBy('category_id')
                                    ->get()
                                    ->map(function ($income) {
                                        return [
                                            'name' => $income->category->name,
                                            'incomes_count' => $income->incomes_count,
                                        ];
                                    });
          
          $expenseCategories = Expense::with('category')
                                      ->select('category_id', DB::raw('count(*) as expenses_count'))
                                      ->where('user_id', $userId)
                                      ->groupBy('category_id')
                                      ->get()
                                      ->map(function ($expense) {
                                          return [
                                              'name' => $expense->category->name,
                                              'expenses_count' => $expense->expenses_count,
                                          ];
                                      });

        return response()->json([
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_incomes' => $netIncome,
            'todays_income' => $todaysIncome,
            'todays_expenses' => $todaysExpenses,
            'current_month_incomes' => $currentMonthIncomes,
            'current_month_expenses' => $currentMonthExpenses,
            'incomeCatData' => $incomeCategories,
            'expenseCatData' => $expenseCategories,
        ]);
    }
}
