<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Income\CreateIncomeRequest;
use Exception;
use Illuminate\Http\Request;


use App\Models\Income;

class IncomesController extends Controller
{
        public function store(CreateIncomeRequest $request)
    {
        try{
            $income = new Income;
        $income->title = $request->title;
        $income->amount = $request->amount;
        $income->date = $request->date;
        $income->description = $request->description;
        $income->user_id = auth()->id();
        $income->save();
        }catch(Exception $e){
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating income record',
                'error' => $e->getMessage()
            ], 500);
        }
        

        return response()->json([
            'status' => 'success',
            'message' => 'Income record created successfully',
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

        $incomes = Income::query();

        $incomes->when($title, function ($query, $title) {
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

        $incomes = $incomes->where('user_id', auth()->id())
                        ->orderBy($sortColumn, $sortOrder)
                        ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => $incomes,
        ]);
    }

    public function show(Income $income)
    {
        if ($income->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to view this income record',
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => $income,
        ]);
    }

    public function update(CreateIncomeRequest $request, Income $income)
    {
        if ($income->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to update this income record',
            ], 403);
        }

        $income->title = $request->title;
        $income->amount = $request->amount;
        $income->date = $request->date;
        $income->description = $request->description;
        $income->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Income record updated successfully',
        ]);
    }

    public function destroy(Income $income)
{
    if ($income->user_id !== auth()->id()) {
        return response()->json([
            'status' => 'error',
            'message' => 'You are not authorized to delete this income record',
        ], 403);
    }

    $income->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Income record deleted successfully',
    ]);
}
    
}
