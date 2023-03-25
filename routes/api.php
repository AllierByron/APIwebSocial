<?php

use App\Http\Controllers\PublicationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/registro/{id}',[UserController::class, 'create']);
Route::get('/mostrarAllUsers',[UserController::class, 'index']);
Route::post('/showUserPubs/{id}',[UserController::class, 'show']);
Route::post('/updateUser/{id}/{user_id}',[UserController::class, 'update']);
Route::post('/eraseUser/{id}/{user_id}',[UserController::class, 'destroy']);//no borra fisicamente, solo logicamente


Route::get('/getPubs', [PublicationController::class, 'index']);
Route::post('/createPub/{user_id}', [PublicationController::class, 'create']);
Route::get('/showPubs/{id}/{forum_id}/{user_id}', [PublicationController::class, 'show']);


Route::post('/crComment/{id}/{pub_id}/{user_id}', [CommentController::class, 'create']);
Route::post('/upComment/{id}/{com_id}', [CommentController::class, 'update']);

Route::get('/getAllForums', [ForumController::class, 'index']);