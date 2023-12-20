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
        $limit = $request->query('limit') && $request->query('limit') < 100 ? $request->query('limit') : 10;
    
        $sort_column = $request->query('sort_column', 'expense_id');
        $sort_order = $request->query('sort_order', 'desc');
    
        $title = $request->query('title');
    
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
    
        $start_amount = $request->query('start_amount');
        $end_amount = $request->query('end_amount');
    
        $category = $request->query('category');
    
        $expenses = Expense::query();
    
        $expenses->when($title, function ($query, $title) {
            $query->where('title', 'LIKE', '%'.$title.'%');
        })
            ->when($start_date, function ($query, $start_date) {
                $query->whereDate('entry_date', '>=', $start_date);
            })
            ->when($end_date, function ($query, $end_date) {
                $query->whereDate('entry_date', '<=', $end_date);
            })
            ->when($start_amount, function ($query, $start_amount) {
                $query->where('amount', '>=', $start_amount);
            })
            ->when($end_amount, function ($query, $end_amount) {
                $query->where('amount', '<=', $end_amount);
            })
            ->when($category, function ($query, $category) {
                $query->whereHas('category', function ($query) use ($category) {
                    $query->where('name', $category); // replace 'name' with the actual column name in your categories table
                });
            });
    
            $expenses = $expenses->orderBy($sort_column, $sort_order)->with('category')->paginate($limit);
    
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