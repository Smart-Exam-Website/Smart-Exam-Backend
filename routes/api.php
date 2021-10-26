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

Route::post('/login', 'App\Http\Controllers\Auth\LoginController@login');

//********************** INSTRUCTOR ROUTES *********************/

// Route::group(['middleware' => ['auth:sanctum']], function () {
//     Route::apiResource('instructors', 'App\Http\Controllers\InstructorController');
//     Route::post('/instructors/register', 'App\Http\Controllers\InstructorController@store');
//     Route::get('/instructors/me', 'App\Http\Controllers\InstructorController@showProfile');
//     Route::put('/instructors/me', 'App\Http\Controllers\InstructorController@editProfile');
// });
Route::apiResource('instructors', 'App\Http\Controllers\InstructorController')->middleware('App\Http\Middleware\Cors');