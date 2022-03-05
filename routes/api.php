<?php

use App\Http\Controllers\ImageUploadController;
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

//----------------------------User Routes----------------------------


// Image Upload Route
Route::post('image-upload', [ImageUploadController::class, 'imageUploadPost'])->name('image.upload.post');

// Authentication Routes

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

// Get all departments on sign-up

Route::get('/departments', 'App\Http\Controllers\DepartmentController@index');
// Get all schools on signup
Route::get('/schools', 'App\Http\Controllers\SchoolController@index');


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/auth/logout', [UserController::class, 'logout']);
    Route::put('/auth/changePassword', [UserController::class, 'changePassword']);
    Route::get('/image', 'App\Http\Controllers\UserController@getImage');
});


//********************** INSTRUCTOR ROUTES *********************/

// Signup as Instructor

Route::post('/instructors/register', 'App\Http\Controllers\InstructorController@store');


Route::group(['middleware' => ['auth:sanctum']], function () {
    // Get instructor's profile
    Route::get('/instructors/me', 'App\Http\Controllers\InstructorController@showProfile');
    Route::get('/instructors/myExams', 'App\Http\Controllers\ExamController@indexInstructor');
    // Edit instructor's profile
    Route::put('/instructors/me', 'App\Http\Controllers\InstructorController@editProfile');
    // Instructor routes.
    Route::apiResource('instructors', 'App\Http\Controllers\InstructorController');

    //----------------------------Exam Routes----------------------------
    // Create Exam
    Route::post('/exams/step1', 'App\Http\Controllers\ExamController@storeStepOne');
    Route::post('/exams/step2', 'App\Http\Controllers\ExamController@storeStepTwo');
    Route::post('/exams/step3', 'App\Http\Controllers\ExamController@storeStepThree');
    Route::post('/exams/step4', 'App\Http\Controllers\ExamController@storeStepFour');
    // Get all exams
    Route::get('/exams', 'App\Http\Controllers\ExamController@index');
    // Delete an exam
    Route::delete('/exams/{exam}', 'App\Http\Controllers\ExamController@destroy');
    // Get all exam questions
    Route::get('/exams/{exam}/questions', 'App\Http\Controllers\ExamController@getExamQuestions');
    // Publish an exam
    Route::post('/exams/{exam}/publish', 'App\Http\Controllers\ExamController@publishExam');
    // Get exam configurations
    Route::get('/exams/{exam}/configs', 'App\Http\Controllers\ExamController@getExamConfigurations');
    // Update exam
    Route::put('/exams/{exam}/step1', 'App\Http\Controllers\ExamController@updateStepOne');
    Route::put('/exams/{exam}/step2', 'App\Http\Controllers\ExamController@updateStepTwo');
    Route::put('/exams/{exam}/step3', 'App\Http\Controllers\ExamController@updateStepThree');
    Route::put('/exams/{exam}/step4', 'App\Http\Controllers\ExamController@updateStepFour');
    // ------------------------- Take exam apis ---------------------------------
    // Start an exam
    Route::post('/exams/{exam}/start', 'App\Http\Controllers\ExamController@startExam');
    // Submit an exam
    Route::post('/exams/{exam}/submit', 'App\Http\Controllers\ExamController@submitExam');
    // Store student answer
    Route::post('/answers', 'App\Http\Controllers\AnswerController@store');
    // Get student answers
    Route::get('/exams/{exam}/my-answers', 'App\Http\Controllers\ExamController@getStudentAnswers');
    // Get all student solutions
    Route::get('/exams/{exam}/all-answers', 'App\Http\Controllers\ExamController@getExamAnswers');
    // get detailed student report
    Route::get('/exams/{exam}/all-answers/answer', 'App\Http\Controllers\ExamController@getDetailedExamAnswer');
    // ----------------------------------------------------------------------------------------
    // Get exam details
    Route::get('/exams/{exam}', 'App\Http\Controllers\ExamController@show');
    // ----------------------------------------------------------------------------------------
    // Mark exam automatically
    Route::post('/exams/total-mark/{exam}', 'App\Http\Controllers\MarkMCQController@MarkAllStudentsExam');
    Route::post('/exams/total-mark/{exam}/{student}', 'App\Http\Controllers\MarkMCQController@MarkOneStudentExam');
    // Mark Exam Manual
    Route::post('/exams/manual', 'App\Http\Controllers\MarkMCQController@MarkExamManual');
    // Exam Report
    Route::get('/exams/report/{exam}', 'App\Http\Controllers\MarkMCQController@ExamReportForStudent');
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


//---------------------------ML ROUTES----------------------------

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/faceDetection', 'App\Http\Controllers\faceDetectionController@faceDetection');
    Route::post('/faceVerification', 'App\Http\Controllers\faceVerificationController@faceVerification');
});
