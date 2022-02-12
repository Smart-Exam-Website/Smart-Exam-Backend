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

    //exams apis
    Route::post('/answers', 'App\Http\Controllers\AnswerController@store');
    Route::get('/exams', 'App\Http\Controllers\ExamController@index');
    Route::post('/exams/{exam}/start', 'App\Http\Controllers\ExamController@startExam');
    Route::delete('/exams/{exam}', 'App\Http\Controllers\ExamController@destroy');
    Route::get('/exams/{exam}/questions', 'App\Http\Controllers\ExamController@getExamQuestions');
    Route::get('/exams/{exam}/answers', 'App\Http\Controllers\ExamController@getStudentAnswers');
    Route::get('/exams/{exam}/configs', 'App\Http\Controllers\ExamController@getExamConfigurations');
    Route::get('/exams/{exam}', 'App\Http\Controllers\ExamController@show');
    Route::post('/exams/step1', 'App\Http\Controllers\ExamController@storeStepOne');
    Route::post('/exams/step2', 'App\Http\Controllers\ExamController@storeStepTwo');
    Route::post('/exams/step3', 'App\Http\Controllers\ExamController@storeStepThree');
    Route::post('/exams/step4', 'App\Http\Controllers\ExamController@storeStepFour');
    Route::get('/exams/totalMark/{id}', 'App\Http\Controllers\ExamController@getExamAllStudentMarks');
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
    Route::get('/questions', 'App\Http\Controllers\McqController@index');
    Route::post('/questions/create', 'App\Http\Controllers\McqController@store');
    Route::get('/questions/{question}', 'App\Http\Controllers\McqController@show');
    Route::post('/questions', [McqController::class, 'store']);
    Route::put('/questions/{id}', [McqController::class, 'update']);
    Route::delete('/questions/{id}', [McqController::class, 'destroy']);
});
