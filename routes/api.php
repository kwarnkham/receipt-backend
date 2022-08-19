<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PictureController;
use App\Http\Controllers\ReceiptController;
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
    Route::get('receipt/{receipt}', 'show');
});

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
});

Route::middleware('auth:sanctum')->controller(AuthController::class)->group(function () {
    Route::get('user', 'user');
});

Route::middleware(['auth:sanctum'])->controller(PictureController::class)->group(function () {
    Route::post('picture', 'store');
});
