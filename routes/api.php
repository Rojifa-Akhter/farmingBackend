<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\LogisticController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Farmer\FarmController;
use App\Http\Controllers\Farmer\FarmMonitorController;
use App\Http\Controllers\Farmer\MarketController;
use App\Http\Controllers\Farmer\ProductCategoryController;
use App\Http\Controllers\Farmer\ProductController;
use App\Http\Controllers\Investor\InsuranceController;
use App\Http\Controllers\Investor\InvestmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfitDistributionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//authentication
Route::group(['prefix' => 'auth'], function ($router) {
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
Route::middleware('auth:api', 'super_admin')->group(function () {
    Route::delete('delete-logistic/{id}', [LogisticController::class, 'deleteLogistics']);
    Route::get('analytics', [AdminDashboardController::class, 'Analytics']);

    //product category data routing
    Route::post('add-categories', [ProductCategoryController::class, 'addCategory']);
    Route::put('update-categorie/{id}', [ProductCategoryController::class, 'updateCategory']);
    Route::delete('delete-categories/{id}', [ProductCategoryController::class, 'deleteCategory']);

    Route::get('farmer-list', [UserController::class, 'farmerList']);

});
//farmer
Route::middleware('auth:api', 'farmer')->group(function () {
    Route::post('add-farm', [FarmController::class, 'addFarm']);
    Route::post('update-farm/{id}', [FarmController::class, 'updateFarm']);
    //invest related roure
    Route::put('investment-status/{id}', [InvestmentController::class, 'updateStatus']);

    //farm monitor data routing
    Route::post('add-monitoring', [FarmMonitorController::class, 'addMonitorData']);
    Route::put('update-monitoring/{id}', [FarmMonitorController::class, 'updateMonitorData']);
    Route::delete('delete-monitoring/{id}', [FarmMonitorController::class, 'deleteMonitorData']);
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
Route::middleware('auth:api', 'investor')->group(function () {
    Route::post('add-investment', [InvestmentController::class, 'addInvest']);
    Route::delete('investment-delete/{id}', [InvestmentController::class, 'deleteInvestment']);

    //notification after investment
    Route::get('get-notify', [InvestmentController::class, 'getnotification']);
    Route::get('read-notify/{id}', [InvestmentController::class, 'readNotification']);
    Route::get('read-all-notify', [InvestmentController::class, 'readAllNotification']);

});
//investor and farmer
Route::middleware('auth:api', 'farmer.investor')->group(function () {
    //insurance
    Route::post('add-insurance', [InsuranceController::class, 'addInsurance']);
    Route::put('update-insurance/{id}', [InsuranceController::class, 'updateInsurance']);
    Route::delete('delete-insurance/{id}', [InsuranceController::class, 'deleteInsurance']);

    Route::get('profit-distribution/{farmId}', [ProfitDistributionController::class, 'getProfitDistribution']);
    Route::post('profit-distribution', [ProfitDistributionController::class, 'createProfitDistribution']);
});
//admin and farmer
Route::middleware('auth:api', 'farmer.admin')->group(function () {
    //farm edit and delete

    Route::delete('delete-farm/{id}', [FarmController::class, 'deleteFarm']);
    //logistic
    Route::post('create-logistics', [LogisticController::class, 'createLogistics']);
    Route::put('update-logistic/{id}', [LogisticController::class, 'updateLogisticsStatus']);
});
//common
Route::middleware('auth:api', 'common')->group(function () {
    //farm data
    Route::get('farms', [FarmController::class, 'farmList']);
    Route::get('farm-details/{id}', [FarmController::class, 'farmDetails']);
    //monitor data
    Route::get('get-monitoring/{farm_id}', [FarmMonitorController::class, 'getMonitoring']);
    Route::get('details-monitoring/{id}', [FarmMonitorController::class, 'getMonitoringDetails']);
    //investment
    Route::get('investment-get', [InvestmentController::class, 'getInvestment']);
    Route::get('investment-details/{id}', [InvestmentController::class, 'detailsInvestment']);

    //product category
    Route::get('all-categories', [ProductCategoryController::class, 'getCategories']);
    Route::get('details-categories/{id}', [ProductCategoryController::class, 'detailsCategory']);
    //product
    Route::get('all-products', [ProductController::class, 'getProduct']);
    Route::get('details-product/{id}', [ProductController::class, 'detailsProduct']);

    //market place
    Route::get('get-marketplace-products', [MarketController::class, 'getMarketplaceProducts']);

    //order
    Route::post('payment-intent', [OrderController::class, 'createPaymentIntent']);
    Route::post('create-order', [OrderController::class, 'createOrder']);
    Route::put('update-order-status/{id}', [OrderController::class, 'updateOrder']);
    Route::get('order-list', [OrderController::class, 'orderList']);
    Route::get('order-details/{id}', [OrderController::class, 'orderDetails']);
    Route::delete('delete-order/{id}', [OrderController::class, 'deleteOrder']);

    //insurance
    Route::get('insurance-list', [InsuranceController::class, 'insuranceList']);
    Route::get('insurance-details/{id}', [InsuranceController::class, 'insuranceDetails']);

    //logistics
    Route::get('get-logistics', [LogisticController::class, 'getAllLogistics']);
    Route::get('logistic-details/{id}', [LogisticController::class, 'getLogisticsDetails']);

    Route::get('investor-list', [UserController::class, 'investorList']);

});
// Route::get('insurance-list', [InsuranceController::class, 'insuranceList']);
// Route::get('all-products', [ProductController::class, 'getProduct']);
