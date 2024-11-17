<?php

use App\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserAuth;
use App\Http\Controllers\Auth\AdminAuth;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\JoinRequestController;
use App\Http\Controllers\GroupUserController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\FileUserController;

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
Route::get('/show',[GroupController::class,'show']);

Route::group(['prefix' => 'user','middleware' => ['auth.guard:user']],function ()
{
    Route::post('/check_in', [FileController::class, 'check_in']);
});

Route::group(['prefix' => 'admin','middleware' => ['auth.guard:admin']],function ()
{
    Route::get('/group', [GroupController::class, 'index']);
    Route::get('/users', [UserAuth::class, 'index']);
    Route::get('/files', [FileController::class, 'index']);
    Route::post('/setting', [SettingController::class, 'update']);
});
Route::post('admin/register', [AdminAuth::class, 'register']);
Route::post('admin/login', [AdminAuth::class, 'login']);
Route::get('admin/logout', [AdminAuth::class, 'logout'])->middleware('auth.guard:admin');
