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
use App\Http\Middleware\AdminCheck;



Route::middleware('auth:sanctum')->get('/usuario', function (Request $request) {
    return $request->user();
});


Route::post('/users', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/login',[AuthController::class,'login']);


Route::get('/users',[UserController::class,'get_users'])
->middleware(AdminCheck::class);
Route::get('users/{id}', [UserController::class, 'getUser'])->where('id', '\d+');
Route::patch('users/{id}', [UserController::class, 'updateUser'])->where('id', '\d+');

Route::post('/courses' ,[CourseController::class, 'createCourse']);



