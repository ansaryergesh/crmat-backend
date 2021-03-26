<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user(); 
});
Route::put('/editOwn', [UserController::class, 'editOwn']);
Route::put('/changePassword', [UserController::class, 'changePassword']);
Route::get('/logout',[UserController::class,'logout']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/test',[BankController::class,'test']);
Route::get('/banks', [BankController::class, 'banks']);
Route::post('/banks',[BankController::class,'add']);
Route::get('/bankCount', [BankController::class, 'bankResult']);
Route::get('/banks/{id} ', [BankController::class,'bank']);