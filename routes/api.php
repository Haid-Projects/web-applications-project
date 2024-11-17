<?php

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
use App\Http\Controllers\SettingController;

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

Route::post('user/register', [UserAuth::class, 'register']);
Route::post('user/login', [UserAuth::class, 'login']);

Route::group(['prefix' => 'user','middleware' => ['auth.guard:user']],function ()
{
    Route::get('/logout', [UserAuth::class, 'logout']);

    // Group CRUD
    Route::get('/index', [GroupController::class, 'index']);
    Route::post('/create_group', [GroupController::class, 'store']);
    Route::post('/update_group/{id}', [GroupController::class, 'update']);
    Route::delete('/delete_group/{id}', [GroupController::class, 'destroy']);

    Route::get('/show_my_own_group',[GroupController::class,'show_my_own_group']);
    Route::get('/show_my_join_group',[GroupController::class,'show_my_join_group']);
    Route::get('/getInfo',[GroupController::class,'getInfo']);
    Route::get('/show_group_users',[GroupUserController::class,'show_group_users']);

    // Group Membership
    Route::post('/add_to_group', [JoinRequestController::class, 'store']);
    Route::post('/accept_join_request', [JoinRequestController::class, 'accept_join_request']);
    Route::post('/reject_join_request', [JoinRequestController::class, 'reject_join_request']);
    Route::get('/show_join_request', [JoinRequestController::class, 'show']);
    Route::delete('/delete_group_member', [GroupUserController::class, 'delete_group_member']);
    Route::get('/show_friend', [FriendController::class, 'show_friend']);

    // Manage Files
    Route::get('fileContent', [FileController::class, 'content']);
    Route::post('/create_file', [FileController::class, 'store']);
    Route::post('/update_file/{id}', [FileController::class, 'update']);
    Route::get('/show_own_file',[FileController::class,'show_own_file']);
    Route::get('/show_files',[FileController::class,'show_files']);
    Route::delete('/delete_file/{id}', [FileController::class, 'destroy']);
    Route::post('/add_file', [FileController::class, 'add_to_group']);
    Route::post('/check_in_single', [FileController::class, 'check_in_single']);
    Route::post('/check_in', [FileController::class, 'check_in']);
    Route::post('/check_out', [FileController::class, 'check_out']);
    Route::post('/upload_with_file', [FileController::class, 'upload_with_file']);
    Route::post('/upload_with_txt', [FileController::class, 'upload_with_txt']);
    ///////////////////////////////////////////////////////////////////////
    Route::get('/show_file_report', [FileUserController::class, 'show']);
    /////////////////////////////////////////////////

});



