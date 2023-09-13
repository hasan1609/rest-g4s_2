<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RestoController;
use App\Http\Controllers\API\DriverController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProdukController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\OrderController;


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

// CART
// GET COUNT CART
Route::get('cart/count/{id}', [CartController::class, 'getCount']);
// ADD TO CART
Route::post('cart', [CartController::class, 'store']);
// get count cart with resto
Route::get('cart/{id}/{lat}&{long}', [CartController::class, 'index']);
// hapus cart by id toko
Route::post('cart/{id}/{user}', [CartController::class, 'destroy']);
// hapus cart by id cart
Route::post('item/cart/{id}', [CartController::class, 'destroyItem']);
// get count cart with resto
Route::get('cart/item/{id}/{user}', [CartController::class, 'show']);

// BOOKING
Route::post('booking', [BookingController::class, 'store']);
//  getby id
Route::get('booking/{id}', [BookingController::class, 'getById']);
// update status terima order toko
Route::post('booking/resto/terima/{id}', [BookingController::class, 'terimaBooking']);

// ORDER
// get by id Resto
Route::get('order/resto/{id}', [OrderController::class, 'getByIdResto']);
// get by id Resto
Route::get('order/customer/{id}', [OrderController::class, 'getByIdCustomer']);
// get produk id produk
Route::get('order/resto/produk/{id}', [OrderController::class, 'getProdukOrder']);

// NOTIFIKASi
//  getby id
Route::get('notifikasi/{id}', [NotificationController::class, 'getById']);
//  update status
Route::post('notifikasi/{id}', [NotificationController::class, 'updateStatus']);