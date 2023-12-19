<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Income\CreateIncomeRequest;
use App\Http\Resources\Incomes\IncomeResource;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


use App\Models\Income;

class IncomesController extends Controller
{
    public function store(CreateIncomeRequest $request)
    {   
        Log::info('Request data:', $request->all());

        $income = new Income;
        $income->title = $request->title;
        $income->amount = $request->amount;
        $income->entry_date = $request->entry_date;
        $income->description = $request->description;
        $income->category_id = $request->category_id;
        $income->user_id = auth()->id();
        $income->save();
        $income->load('category');
        return new IncomeResource($income);
    }

        public function index(Request $request)
    {
        $limit = $request->query('limit', 10);

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
            return $query->whereDate('entry_date', '>=', $startDate);
        })
        ->when($endDate, function ($query, $endDate) {
            return $query->whereDate('entry_date', '<=', $endDate);
        })
        ->when($startAmount, function ($query, $startAmount) {
            return $query->where('amount', '>=', $startAmount);
        })
        ->when($endAmount, function ($query, $endAmount) {
            return $query->where('amount', '<=', $endAmount);
        });

        $incomes = $incomes->where('user_id', auth()->id())
                    ->with('category')
                    ->paginate($limit);

        return IncomeResource::collection($incomes);
    }

    public function show(Income $income)
    {
        $income->load('category');
        if (!$income) {
            return response()->json(["No Incomes Data"], 404);
        }

        if ($income->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to view this income record',
            ], 403);
        }

        return new IncomeResource($income);
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

    public function totalIncomes()
    {
        $totalIncomes = Income::where('user_id', auth()->id())->sum('amount');

        if ($totalIncomes == 0) {
            return response()->json([
                'message' => 'You have no Income'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $totalIncomes,
        ], 200);
    }
    
}
