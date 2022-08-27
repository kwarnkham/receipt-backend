<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
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


Route::middleware('auth:sanctum')->controller(ReceiptController::class)->group(function () {
    Route::post('receipt', 'store');
    Route::get('receipt', 'index');
    Route::get('customer/known', 'getKnownCustomers');
    Route::get('receipt/{receipt}', 'show');
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function () {
    Route::get('token', 'token');
    Route::post('password', 'changePassword');
});

Route::middleware(['auth:sanctum'])->controller(PictureController::class)->group(function () {
    Route::post('picture', 'store');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(UserController::class)->group(function () {
    Route::get('user', 'index');
    Route::post('user', 'store');
    Route::post('user/{user}/password', 'resetPassword');
    Route::get('user/{user}', 'show');
    Route::put('user/{user}', 'update');
});

Route::controller(PaymentController::class)->group(function () {
    Route::get('payment', 'index');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(PaymentController::class)->group(function () {
    Route::post('payment', 'store');
    Route::post('user/payment', 'userPayment');
    Route::put('user/{user}/payment/{payment}/number/{number}', 'updateUserPayment');
    Route::delete('user/{user}/payment/{payment}/number/{number}', 'deleteUserPayment');
});

Route::middleware(['auth:sanctum'])->controller(ItemController::class)->group(function () {
    Route::get('item/known', 'getKnownItems');
});

Route::middleware(['auth:sanctum', 'admin'])->controller(SubscriptionController::class)->group(function () {
    Route::post('subscription', 'store');
    Route::post('subscription/{subscription}/add', 'increaseSubscription');
});
