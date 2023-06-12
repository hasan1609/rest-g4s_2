<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RestoController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProdukController;
use App\Http\Controllers\API\ReviewController;

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

// CUSTOMER
// ADD CUSTOMER
Route::post('/customer', [CustomerController::class, 'register']);


// RESTO 
// REGISTER
Route::post('/resto', [RestoController::class, 'register']);
// LOGIN
Route::post('/login/resto', [AuthController::class, 'restoLogin']);
// GET STATUS
Route::get('/status/resto/{id}', [RestoController::class, 'getStatusResto']);
// UPDATE STATUS
Route::post('/status/resto/{id}', [RestoController::class, 'updateStatusResto']);
// EDIT RESTO BY ID
Route::post('/resto/{id}', [RestoController::class, 'update']);
// GET RESTO BY ID
Route::get('/resto/{id}', [RestoController::class, 'show']);

// DRIVER
// ADD DRIVER
Route::post('/driver', [DriverController::class, 'register']);



// Produk
Route::get('/produk', [ProdukController::class, 'index']);
// ADD produk
Route::post('/produk', [ProdukController::class, 'store']);
// GET produk BY ID produk
Route::get('/produk/{id}', [ProdukController::class, 'show']);
// GET produk BY ID Resto
Route::get('/resto/produk/{id}', [ProdukController::class, 'getProdukById']);
// EDIT produk BY ID
Route::post('/produk/{id}', [ProdukController::class, 'update']);
// DELETE produk BY ID
Route::post('/produk/del/{id}', [ProdukController::class, 'destroy']);
// UPDATE STATUS
Route::post('/status/produk/{id}', [ProdukController::class, 'updateStatusProduk']);
// get kategori produk
Route::get('/kategori/produk/{id}/{kategori}', [ProdukController::class, 'getByKatProduk']);
// get count produk
Route::get('count/kategori/{id}', [ProdukController::class, 'getCount']);


// GET REVIEW BY ID RESTO
Route::get('review/resto/{id}', [ReviewController::class, 'getReviewByIdResto']);