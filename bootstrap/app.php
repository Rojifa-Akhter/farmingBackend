<?php

use App\Http\Middleware\AdminFarmerMiddleware;
use App\Http\Middleware\AdminInvestorFarmerMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CommonMiddleware;
use App\Http\Middleware\FarmerInvestorMiddleware;
use App\Http\Middleware\FarmerMiddleware;
use App\Http\Middleware\InvestorMiddleware;
use App\Http\Middleware\UserMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'super_admin' => AdminMiddleware::class,
            'investor' => InvestorMiddleware::class,
            'farmer' => FarmerMiddleware::class,
            'user' => UserMiddleware::class,
            'common' => CommonMiddleware::class,
            'farmer.investor' => FarmerInvestorMiddleware::class,
            'farmer.admin' => AdminFarmerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
