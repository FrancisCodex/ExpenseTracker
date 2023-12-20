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
        $limit = $request->query('limit') && $request->query('limit') < 100 ? $request->query('limit') : 10;
    
        $sort_column = $request->query('sort_column', 'income_id');
        $sort_order = $request->query('sort_order', 'desc');
    
        $title = $request->query('title');
    
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
    
        $start_amount = $request->query('start_amount');
        $end_amount = $request->query('end_amount');
    
        $category = $request->query('category');
    
        $incomes = Income::query();
    
        $incomes->when($title, function ($query, $title) {
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
    
        $incomes = $incomes->orderBy($sort_column, $sort_order)->with('category')->paginate($limit);
    
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
