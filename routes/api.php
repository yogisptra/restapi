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
Route::post('login', [App\Http\Controllers\Api\UserController::class, 'login'])->name('login');
Route::post('register', [App\Http\Controllers\Api\UserController::class, 'register'])->name('register');
// Route::post('register', 'API\UserController@register');

Route::group(['middleware' => 'auth:api'], function(){
	Route::get('user/user', [App\Http\Controllers\Api\UserController::class, 'index'])->name('index');
	Route::get('user/show/{id}', [App\Http\Controllers\Api\UserController::class, 'show'])->name('show');
    Route::get('user/detail', [App\Http\Controllers\Api\UserController::class, 'details'])->name('details');
    Route::post('user/store', [App\Http\Controllers\Api\UserController::class, 'store'])->name('store');
    Route::put('user/update/{id}', [App\Http\Controllers\Api\UserController::class, 'update'])->name('update');
    Route::delete('user/delete/{id}', [App\Http\Controllers\Api\UserController::class, 'delete'])->name('delete');
    Route::post('logout', [App\Http\Controllers\Api\UserController::class, 'logout'])->name('logout');
 }); 

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});
