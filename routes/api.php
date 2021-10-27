<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;

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

//public Routes

//Route::resource('students', StudentController::class);
Route::post('/students/register',[StudentController::class,'store']);
Route::get('/students',[StudentController::class,'index']);
Route::post('/auth/login',[UserController::class,'login']);

//protected Routes

Route::group(['middleware'=>['auth:sanctum']], function () {
    Route::get('/students/{id}',[StudentController::class,'show']);
    Route::post('/auth/logout',[UserController::class, 'logout']);
    Route::put('/auth/changePassword',[UserController::class, 'changePassword']);
});

