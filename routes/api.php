<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\McqController;

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

//----------------------------USER Routes----------------------------

//public Routes

Route::post('/auth/login', [UserController::class, 'login'])->name('login');

Route::view('/auth/forgotPassword', 'resetPassword')->name('forgetPassword');

Route::post(
    '/auth/forgotPassword',
    'App\Http\Controllers\UserController@forgotPassword'
)->middleware('guest')->name('password.email');

Route::post(
    '/verifyEmail',
    'App\Http\Controllers\UserController@verifyEmail'
)->middleware('guest');

Route::put(
    '/auth/forgotPassword',
    'App\Http\Controllers\UserController@resetPassword'
)->middleware('guest')->name('password.update');

Route::get('/departments', 'App\Http\Controllers\DepartmentController@index');
Route::get('/schools', 'App\Http\Controllers\SchoolController@index');


//protected Routes

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/auth/logout', [UserController::class, 'logout']);
    Route::put('/auth/changePassword', [UserController::class, 'changePassword']);
});


//********************** INSTRUCTOR ROUTES *********************/

//public Routes

Route::post('/instructors/register', 'App\Http\Controllers\InstructorController@store');

//protected Routes

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/instructors/me', 'App\Http\Controllers\InstructorController@showProfile');
    Route::put('/instructors/me', 'App\Http\Controllers\InstructorController@editProfile');
    Route::apiResource('instructors', 'App\Http\Controllers\InstructorController');
    Route::apiResource('exams', 'App\Http\Controllers\ExamController');
});


//-------------------------STUDENT ROUTES----------------------------


//public Routes

Route::post('/students/register', [StudentController::class, 'store']);

//protected Routes

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/students/me', [StudentController::class, 'showProfile']);
    Route::put('/students/me', [StudentController::class, 'editProfile']);
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);
});


//---------------------------QUESTION ROUTES----------------------------

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/question/create', [McqController::class, 'store']);
});
