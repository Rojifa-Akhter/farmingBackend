<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Farmer\FarmController;
use App\Http\Controllers\Farmer\FarmMonitorController;
use App\Http\Controllers\Investor\InvestmentController;
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
//admin
Route::middleware('auth:api','super_admin')->group(function () {
    // Route::post('add-investment', [InvestmentController::class, 'addInvest']);
});
//farmer
Route::middleware('auth:api','farmer')->group(function () {
    Route::get('farms', [FarmController::class, 'farmList']);
    Route::get('farm-details/{id}', [FarmController::class, 'farmDetails']);
    Route::post('add-farm', [FarmController::class, 'addFarm']);
    Route::post('update-farm/{id}', [FarmController::class, 'updateFarm']);
    Route::delete('delete-farm/{id}', [FarmController::class, 'deleteFarm']);

    //invest related roure
    Route::post('investment-status/{id}', [InvestmentController::class, 'updateStatus']);
    Route::get('investment-get', [InvestmentController::class, 'getInvestment']);
    Route::get('investment-details/{id}', [InvestmentController::class, 'detailsInvestment']);
    Route::delete('investment-delete/{id}', [InvestmentController::class, 'deleteInvestment']);

    //
    Route::post('farm-monitorings', [FarmMonitorController::class, 'addMonitorData']);
    Route::get('farm-monitorings/{farm_id}', [FarmMonitorController::class, 'getMonitoring']);
    Route::get('farm-monitoring/{id}', [FarmMonitorController::class, 'getMonitoringDetails']);
    Route::put('farm-monitoring/{id}', [FarmMonitorController::class, 'updateMonitorData']);
    Route::delete('farm-monitoring/{id}', [FarmMonitorController::class, 'deleteMonitorData']);

});
//investor
Route::middleware('auth:api','investor')->group(function () {
    Route::post('add-investment', [InvestmentController::class, 'addInvest']);
});



