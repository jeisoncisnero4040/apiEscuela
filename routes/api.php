<?php

use App\Http\Controllers\ActivitiescomentsController;
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
use App\Http\Controllers\ActivityFileController;
use App\Http\Controllers\AdviceController;
use App\Http\Controllers\CalificationsController;
use App\Http\Controllers\DetailFileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StudentController;

use App\Http\Middleware\AdminCheck;
use App\Models\ActivityFileModel;

Route::post('/users', [AuthController::class, 'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/retrieve_password',[AuthController::class,'passwordRefresh']);
route::post('/logout',[AuthController::class,'logout']) ;

Route::get('/users',[UserController::class,'get_users']);
//->middleware(AdminCheck::class);
Route::get('/users/{id}', [UserController::class, 'getUser'])->where('id', '\d+');
Route::delete('/users/{id}', [UserController::class, 'deleteUser'])->where('id', '\d+');
Route::patch('/users/{id}', [UserController::class, 'updateUser'])->where('id', '\d+');
Route::post('/users/{id}/change_image',[UserController::class,'changeImageUser'])->where('id', '\d+');

Route::post('/courses' ,[CourseController::class, 'createCourse']);
Route::get('/courses',[CourseController::class, 'getAllCourses']);
Route::get('/courses/{id}', [CourseController::class, 'getCourseById'])->where('id', '\d+');
Route::get('/courses/teacher/{id}', [CourseController::class, 'getCoursesByTeacherId'])->where('id', '\d+');
Route::get('/courses/not_teacher/', [CourseController::class, 'getCoursesWithNullTeacher']);
Route::delete('/courses/{id}', [CourseController::class, 'deleteCourse'])->where('id', '\d+');
Route::patch('/courses/{id}', [CourseController::class, 'updateCourseById'])->where('id', '\d+');
Route::post('/courses/{id}/change_image',[CourseController::class,'changeImageCourse'])->where('id', '\d+');

Route::post('/activities',[ActivityController::class, 'createActivity']);
Route::get('/activities',[ActivityController::class, 'getAllActivities']);
Route::get('/activities/{id}', [ActivityController::class, 'getActivityById'])->where('id', '\d+');
Route::get('/activities/course/{id}', [ActivityController::class, 'getActivitiesByCourseId'])->where('id', '\d+');
Route::delete('/activities/{id}', [ActivityController::class, 'deleteActivityById'])->where('id', '\d+');
Route::patch('/activities/{id}', [ActivityController::class, 'updateActivityById'])->where('id', '\d+');

Route::post('/students',[StudentController::class, 'createStudent']);
Route::get('/students',[StudentController::class, 'getAllActiviesStudents']);
Route::get('/students/{id}',[StudentController::class,'getStudentById'])->where('id','\d+');
Route::get('/students/courses/{id}',[StudentController::class,'getStudentsByCourrseId'])->where('id','\d+');
Route::delete('/students/{id}',[StudentController::class,'deleteStudentById'])->where('id','\d+');

Route::post('/schedule',[ScheduleController::class,'CreateScheduleForActivity']);
Route::get('/schedule/teacher/{id}',[ScheduleController::class,'getScheduleForTeacherId'])->where('id','\d+');
Route::get('/schedule/student/{id}',[ScheduleController::class,'getScheduleByStudentId'])->where('id','\d+');
Route::get('/schedule/{id}',[ScheduleController::class,'deleteScheduleById'])->where('id','\d+');
Route::patch('/schedule/{id}',[ScheduleController::class,'updateScheduleById'])->where('id','\d+');

Route::post('/advices',[AdviceController::class,'createAdvice']);
Route::get('/advices',[AdviceController::class,'getAllAdvices']);
Route::delete('/advices/{id}',[AdviceController::class,'deleteAdviceById'])->where('id','\d+');
Route::post('/advices/{id}/change_image',[AdviceController::class,'changeImageFromAdviceById'])->where('id','\d+');

Route::post('/califications',[CalificationsController::class ,'addCalification']);
Route::get('/califications/{studentId}/activity/{activityId}', [CalificationsController::class, 'getAllCalificationsActivityFromStudent'])
    ->where('studentId', '\d+')
    ->where('activityId', '\d+');
Route::get('/califications/{studentId}/course/{courseId}', [CalificationsController::class, 'getAllCalificationsCourseFromStudent'])
    ->where('studentId', '\d+')
    ->where('courseId', '\d+');
Route::get('/califications/{id}',[CalificationsController::class ,'getCalificationById'])->where('id','\d+');
Route::delete('/califications/{id}',[CalificationsController::class ,'deleteCalificationById'])->where('id','\d+');
Route::post('/califications/{id}',[CalificationsController::class ,'updateCalificationById'])->where('id','\d+');

Route::post('/comments',[ActivitiescomentsController::class, 'createComment']);
Route::get('/comments/activity/{id}',[ActivitiescomentsController::class, 'getAllCommentFromActivityId'])->where('id','\d+');

Route::post('/files_activity',[ActivityFileController::class, 'addFileOrUrlToActivity']);
Route::get('/files_activity/activity/{id}',[ActivityFileController::class, 'getAllFilesByActivityId'])->where('id','\d+');
Route::get('/files_activity/{id}',[ActivityFileController::class, 'getActivityFileById'])->where('id','\d+');
Route::delete('/files_activity/{id}',[ActivityFileController::class, 'deleteFileActivityById'])->where('id','\d+');
Route::post('/files_activity/{id}',[ActivityFileController::class, 'changueFileForFileActivityId'])->where('id','\d+');
Route::get('files_activity/{activityId}/by/{studentId}',[ActivityFileController::class,'getAllFilesForActivityByStudent'])
    ->where('activityId','\d+')
    ->where('studentId','\d+');

Route::post('file_detail/{activityFileId}/by/{studentId}',[DetailFileController::class,'checkFileActivityByStudentId'])
    ->where('activityFileId','\d+')
    ->where('studentId','\d+');