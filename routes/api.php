<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\MfoController;
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
Route::get('/changePassword', [UserController::class, 'changePassword']);
Route::get('/logout',[UserController::class,'logout']);
Route::get('/getProfile',[UserController::class,'getProfile']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/test',[BankController::class,'test']);
Route::put('/banks/{id}', [BankController::class, 'edit']);
Route::get('/banks', [BankController::class, 'banks']);
Route::post('/banks',[BankController::class,'add']);
Route::get('/bankCount', [BankController::class, 'bankResult']);
Route::get('/banks/{id} ', [BankController::class,'bank']);
Route::put('/banks/{id}', [BankController::class, 'edit']);
Route::get('/mfo', [MfoController::class, 'index']);
Route::post('/mfo',[MfoController::class,'add']);
Route::get('/mfo/{id} ', [MfoController::class,'mfo']);
Route::put('/mfo/{id}', [MfoController::class, 'edit']);
Route::put('/mfoArchive', [MfoController::class, 'archive']);
