<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\Categories\CreateExpenseCategoryRequest;
use App\Http\Requests\Categories\UpdateExpenseCategoryRequest;

class ExpenseCategoryController extends Controller
{
    //
    public function index(Request $request)
    {
        $limit = $request->query('limit', 10);

        $name = $request->query('name');

        $categories = Category::query();

        $categories->when($name, function ($query, $name) {
            return $query->where('name', 'LIKE', '%' . $name . '%');
        });

        $categories = $categories->where('type', 'expense')
                            ->where('user_id', auth()->id()) // Add this line
                            ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ], 200);
    }

    public function store(CreateExpenseCategoryRequest $request)
    {
        try {
            $category = new Category;
            $category->name = $request->name;
            $category->type = 'expense';
            $category->user_id = auth()->id();
            $category->save();

            return response()->json([
                'status' => 'success',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateExpenseCategoryRequest $request, Category $category)
    {
        try {
            $category = Category::where('category_type', 'expense')->where('id', $category)->first();
            $category->name = $request->validated()['name'];
            $category->type = $request->validated()['type'];
            $category->save();
        }
        catch (\Exception $e) {
            // Exception handling code
            return response()->json([
                'status' => 'error',
                'message' => 'failed to update expense category',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'expense category updated succesfully',
        ], 200);
    }

    public function destroy($category_id)
    {
        $category = Category::where('category_id', $category_id)->first();

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Category deleted successfully',
        ], 200);
    }

    public function show(Category $expense_category)
{

    if (!$expense_category) {
        return response()->json([
            'status' => 'error',
            'message' => 'Category not found',
        ], 404);
    }
    error_log('Category type: ' . $expense_category->type);
    
    if ($expense_category->type !== 'expense') {
        return response()->json([
            'status' => 'error',
            'message' => 'Category type is not expenses',
        ], 400);
    }

    return response()->json([
        'status' => 'success',
        'data' => $expense_category,
    ], 200);
}

}
