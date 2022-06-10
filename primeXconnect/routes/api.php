<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Api\Controllers\ProductController;
use App\Http\Api\Controllers\ProductsController;
use App\Http\Api\Controllers\StocksController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(ProductController::class)->group(function() {
    Route::get('/product/{id}', 'get');
    Route::patch('/product/{id}', 'update');
    Route::delete('/product/{id}', 'delete');
});

Route::controller(ProductsController::class)->group(function() {
    Route::get('/products', 'list');
    Route::post('/products', 'bulkCreate');
});

Route::controller(StocksController::class)->group(function() {
    Route::post('/stocks', 'bulkCreate');
});