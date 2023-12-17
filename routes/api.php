<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Api\IncomesController;
use App\Http\Controllers\Api\ExpensesController;
use App\Http\Controllers\Api\Category\IncomeCategoryController;
use App\Http\Controllers\Api\Category\ExpenseCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::apiResource('incomes', IncomesController::class);
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::apiResource('expenses', ExpensesController::class);
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::apiResource('income-categories', IncomeCategoryController::class);
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::apiResource('expense-categories', ExpenseCategoryController::class);
});


Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login'])->name('login');


// Routes for Controllers
