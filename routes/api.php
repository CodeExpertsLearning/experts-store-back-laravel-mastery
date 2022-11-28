<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


/**
 * GET /products
 * GET /products/:id
 * POST /products
 * PUT/PATCH /products/:id
 * DELETE /products/:id
 */
Route::apiResource('products.categories', \App\Http\Controllers\API\ProductCategoryController::class)
    ->only('index');

Route::apiResource('products.photos', \App\Http\Controllers\API\ProductPhotosController::class)
    ->only('index', 'store', 'destroy')
    ->middleware('auth:sanctum');

Route::apiResource('products', \App\Http\Controllers\API\ProductController::class)
                ->middleware('auth:sanctum');

Route::post('/login', [\App\Http\Controllers\API\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\API\AuthController::class, 'logout'])->middleware('auth:sanctum');

//Route::controller(\App\Http\Controllers\API\ProductController::class)
//    ->prefix('products')
//    ->group(function() {
//        Route::get('/', 'index');
//        Route::get('/{product}', 'show');
//        Route::post('/', 'store');
//        Route::match(['put', 'patch'], '/{product}', 'update');
//        Route::delete('/{product}', 'destroy');
//});


