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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::view('/auth/forgotPassword', 'resetPassword')->name('forgetPassword');


Route::post('/auth/forgotPassword', 'App\Http\Controllers\UserController@forgotPassword'
    )->middleware('guest')->name('password.email');

Route::put('/auth/forgotPassword', 'App\Http\Controllers\UserController@resetPassword'
    )->middleware('guest')->name('password.update');


//********************** INSTRUCTOR ROUTES *********************/

Route::post('/instructors/register', 'App\Http\Controllers\InstructorController@store');
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/instructors/me', 'App\Http\Controllers\InstructorController@showProfile');
    Route::put('/instructors/me', 'App\Http\Controllers\InstructorController@editProfile');
    Route::apiResource('instructors', 'App\Http\Controllers\InstructorController');
});