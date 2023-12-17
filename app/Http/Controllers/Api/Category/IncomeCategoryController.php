<?php

namespace App\Http\Controllers\Api\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CreateIncomeCategoryRequest;
use App\Http\Requests\Categories\UpdateIncomeCategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;

class IncomeCategoryController extends Controller
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

        $categories = $categories->where('type', 'incomes')
                        ->where('user_id', auth()->id())
                        ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ], 200);
    }

    public function store(CreateIncomeCategoryRequest $request)
    {
        try {
            $category = new Category;
            $category->name = $request->name;
            $category->type = 'incomes';
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

    public function update(UpdateIncomeCategoryRequest $request, Category $category)
    {
        try {
            $category = Category::where('type', 'incomes')->where('id', $category)->first();
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

    public function show($category)
    {

        if (!$category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $category,
        ], 200);
    }

}
