<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\CreateExpenseRequest;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    public function store(CreateExpenseRequest $request)
    {
        $expense = new Expense;
        $expense->title = $request->title;
        $expense->amount = $request->amount;
        $expense->date = $request->date;
        $expense->description = $request->description;
        $expense->user_id = auth()->id();
        $expense->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Expense record created successfully',
        ], 201);
    }

        public function index(Request $request)
    {
        $limit = $request->query('limit', 10);
        $sortColumn = $request->query('sort_column', 'id');
        $sortOrder = $request->query('sort_order', 'desc');

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
                        ->orderBy($sortColumn, $sortOrder)
                        ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => $expenses,
        ]);
    }

        public function show(Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to view this expense record',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $expense,
        ]);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Expense record updated successfully',
        ]);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Expense record deleted successfully',
        ]);
    }
}