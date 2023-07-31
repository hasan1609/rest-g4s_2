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
// GET ALL RESTO
Route::get('/resto', [RestoController::class, 'index']);
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
// GET RESTO TERDEKAT
Route::get('/nearby/resto/{latitude}&{longitude}', [RestoController::class, 'restoTerdekat']);
// DELETE RESTO
Route::post('/resto/delete/{id}', [RestoController::class, 'destroy']);

// DRIVER
// ADD DRIVER
Route::post('/driver', [DriverController::class, 'register']);
// LOGIN
Route::post('/login/driver', [AuthController::class, 'driverLogin']);
// GET ALL DRIVER
Route::get('/driver/motor', [DriverController::class, 'getMotor']);
Route::get('/driver/mobil', [DriverController::class, 'getMobil']);
// GET BY ID DRIVER
Route::get('/driver/{id}', [DriverController::class, 'getByIdDriver']);
// UPDATE DRIVER
Route::post('/driver/update/{id}', [DriverController::class, 'update']);
// DELETE DRIVER
Route::post('/driver/delete/{id}', [DriverController::class, 'destroy']);

// Produk
Route::get('/produk', [ProdukController::class, 'index']);
// ADD produk
Route::post('/produk', [ProdukController::class, 'store']);
// GET produk BY ID produk
Route::get('/produk/{id}', [ProdukController::class, 'show']);
// GET produk BY ID Resto
Route::get('/resto/produk/{id}/{lat}&{long}', [ProdukController::class, 'getByIdResto']);
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