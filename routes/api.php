<?php

 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\StudentController;

use App\Http\Middleware\AdminCheck;



Route::post('/users', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/retrieve_password',[AuthController::class,'passwordRefresh']);

Route::get('/users',[UserController::class,'get_users'])
->middleware(AdminCheck::class);
Route::get('/users/{id}', [UserController::class, 'getUser'])->where('id', '\d+');
Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->where('id', '\d+');
Route::patch('/users/{id}', [UserController::class, 'updateUser'])->where('id', '\d+');

Route::post('/courses' ,[CourseController::class, 'createCourse']);
Route::get('/courses',[CourseController::class, 'getAllCourses']);
Route::get('/courses/{id}', [CourseController::class, 'getCourseById'])->where('id', '\d+');
Route::get('/courses/teacher/{id}', [CourseController::class, 'getCoursesByTeacherId'])->where('id', '\d+');
Route::get('/courses/not_teacher/', [CourseController::class, 'getCoursesWithNullTeacher']);
Route::delete('/courses/{id}', [CourseController::class, 'deleteCourse'])->where('id', '\d+');
Route::patch('/courses/{id}', [CourseController::class, 'updateCourseById'])->where('id', '\d+');

Route::post('/activities',[ActivityController::class, 'createActivity']);
Route::get('/activities',[ActivityController::class, 'getAllActivities']);
Route::get('/activities/{id}', [ActivityController::class, 'getActivityById'])->where('id', '\d+');
Route::get('/activities/course/{id}', [ActivityController::class, 'getActivitiesByCourseId'])->where('id', '\d+');
Route::delete('/activities/{id}', [ActivityController::class, 'deleteActivityById'])->where('id', '\d+');
Route::patch('/activities/{id}', [ActivityController::class, 'updateActivityById'])->where('id', '\d+');

Route::post('/students',[StudentController::class, 'createStudent']);
