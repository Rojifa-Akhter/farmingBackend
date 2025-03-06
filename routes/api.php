<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Farmer\FarmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//authentication
Route::group(['prefix'=>'auth'],function($router)
{
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('verify', [AuthController::class, 'verify']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('social-login', [AuthController::class, 'socialLogin']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);
    Route::middleware('auth:api')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('profile-update', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});
//farmer
Route::middleware('auth:api','farmer')->group(function () {
    Route::get('farms', [FarmController::class, 'farmList']);
    Route::get('farm-details/{id}', [FarmController::class, 'farmDetails']);
    Route::post('add-farm', [FarmController::class, 'addFarm']);
    Route::post('update-farm/{id}', [FarmController::class, 'updateFarm']);
    Route::delete('delete-farm/{id}', [FarmController::class, 'deleteFarm']);
});
