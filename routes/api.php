<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Farmer\FarmController;
use App\Http\Controllers\Farmer\FarmMonitorController;
use App\Http\Controllers\Farmer\MarketController;
use App\Http\Controllers\Farmer\ProductCategoryController;
use App\Http\Controllers\Farmer\ProductController;
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
    Route::post('add-farm', [FarmController::class, 'addFarm']);
    Route::post('update-farm/{id}', [FarmController::class, 'updateFarm']);
    Route::delete('delete-farm/{id}', [FarmController::class, 'deleteFarm']);

    //invest related roure
    Route::post('investment-status/{id}', [InvestmentController::class, 'updateStatus']);
    Route::get('investment-get', [InvestmentController::class, 'getInvestment']);
    Route::get('investment-details/{id}', [InvestmentController::class, 'detailsInvestment']);
    Route::delete('investment-delete/{id}', [InvestmentController::class, 'deleteInvestment']);

    //farm monitor data routing
    Route::post('add-monitoring', [FarmMonitorController::class, 'addMonitorData']);
    Route::put('update-monitoring/{id}', [FarmMonitorController::class, 'updateMonitorData']);
    Route::delete('delete-monitoring/{id}', [FarmMonitorController::class, 'deleteMonitorData']);
    //product category data routing
    Route::post('add-categories', [ProductCategoryController::class, 'addCategory']);
    Route::put('update-categorie/{id}', [ProductCategoryController::class, 'updateCategory']);
    Route::delete('delete-categories/{id}', [ProductCategoryController::class, 'deleteCategory']);
    //product route
    Route::post('add-product', [ProductController::class, 'addProduct']);
    Route::put('update-product/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('delete-product/{id}', [ProductController::class, 'deleteProduct']);
    //marketplace route
    Route::post('marketplace-add', [MarketController::class, 'addProductToMarketplace']);
    Route::put('marketplace-update/{id}', [MarketController::class, 'updateMarketplaceProduct']);
    Route::delete('marketplace-delete/{id}', [MarketController::class, 'deleteMarketplaceProduct']);



});
//investor
Route::middleware('auth:api','investor')->group(function () {
    Route::post('add-investment', [InvestmentController::class, 'addInvest']);
});
//common
Route::middleware('auth:api','common')->group(function () {
        //farm data
        Route::get('farms', [FarmController::class, 'farmList']);
        Route::get('farm-details/{id}', [FarmController::class, 'farmDetails']);
    //monitor data
    Route::get('get-monitoring/{farm_id}', [FarmMonitorController::class, 'getMonitoring']);
    Route::get('details-monitoring/{id}', [FarmMonitorController::class, 'getMonitoringDetails']);

    //product category
    Route::get('all-categories', [ProductCategoryController::class, 'getCategories']);
    Route::get('details-categories/{id}', [ProductCategoryController::class, 'detailsCategory']);
    //product
    Route::get('all-products', [ProductController::class, 'getProduct']);
    Route::get('details-product/{id}', [ProductController::class, 'detailsProduct']);

    //market place
    Route::get('get-marketplace-products', [MarketController::class, 'getMarketplaceProducts']);

});




