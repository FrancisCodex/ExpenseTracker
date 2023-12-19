<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\CreateExpenseRequest;
use App\Http\Resources\Expenses\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExpensesController extends Controller
{
    public function store(CreateExpenseRequest $request)
    {
        try {
            $expense = new Expense();
            $expense->title = $request->title;
            $expense->entry_date = $request->entry_date;
            $expense->amount = $request->amount;
            $expense->description = $request->description;
            $expense->category_id = $request->category_id;
            $expense->user_id = auth()->id();
            $expense->save();
            Log::info('store method called');
            return response()->json([
                'status' => 'success',
                'message' => 'expense record created successfully',
                'data' => $expense,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'failed to create expense item',
                'error' => $e->getMessage(),
            ], 500);
        } 
    }

        public function index(Request $request)
    {
        $limit = $request->query('limit', 10);

        $title = $request->query('title');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $startAmount = $request->query('start_amount');
        $endAmount = $request->query('end_amount');

        $expenses = Expense::query();

        $expenses->when($title, function ($query, $title) {
            return $query->where('title', 'LIKE', '%' . $title . '%');
        })
        ->when($startDate, function ($query, $startDate) {
            return $query->whereDate('date', '>=', $startDate);
        })
        ->when($endDate, function ($query, $endDate) {
            return $query->whereDate('date', '<=', $endDate);
        })
        ->when($startAmount, function ($query, $startAmount) {
            return $query->where('amount', '>=', $startAmount);
        })
        ->when($endAmount, function ($query, $endAmount) {
            return $query->where('amount', '<=', $endAmount);
        });

        $expenses = $expenses->where('user_id', auth()->id())
                        ->with('category')
                        ->paginate($limit);

        return ExpenseResource::collection($expenses);
    }

        public function show(Expense $expense)
        {   
            $expense->load('category');

            if (!$expense) {
                return response()->json([], 404);
            }
        
            if ($expense->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to view this expense record',
                ], 403);
            }
        
            return new ExpenseResource($expense);
        }

        public function update(CreateExpenseRequest $request, Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to update this expense record',
            ], 403);
        }

        $expense->title = $request->title;
        $expense->amount = $request->amount;
        $expense->date = $request->date;
        $expense->description = $request->description;
        $expense->save();

        return new ExpenseResource($expense);
    }

        public function destroy(Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this expense record',
            ], 403);
        }

        $expense->delete();

        return new ExpenseResource($expense);
    }

        public function totalExpenses()
    {
        $totalExpenses = Expense::where('user_id', auth()->id())->sum('amount');

        if ($totalExpenses == 0) {
            return response()->json([
                'message' => 'You have no expenses'
            ], 404);
        }

        return response()->json([
            'total_expenses' => $totalExpenses
        ], 200);
    }
}