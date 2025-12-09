<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\OrderController;
use App\Http\Controllers\Api\Admin\PaymentMethodController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Site\AuthController as SiteAuthController;
use App\Http\Controllers\Api\Site\OrderController as SiteOrderController;
use App\Http\Controllers\Api\Site\ProductController as SiteProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin'], function () {
    Route::post('login', [AuthController::class, 'login'])->middleware('guest:admin');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:admin');

    Route::group(['middleware' => 'auth:admin'], function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('products',ProductController::class);
        Route::apiResource('payment-methods',PaymentMethodController::class);

        Route::get('orders',[OrderController::class,'index']);
        Route::post('orders/create',[OrderController::class,'store']);
        Route::put('orders/{order}',[OrderController::class,'update']);
        Route::delete('orders/{order}',[OrderController::class,'destroy']);

        Route::post('orders/{order}/add-item',[OrderController::class,'addItem']);
        Route::post('orders/{order}/update-item/{itemId}',[OrderController::class,'updateItem']);
        Route::delete('orders/{order}/remove-item/{itemId}',[OrderController::class,'removeItem']);
    });
});


Route::group(['prefix' => 'site'], function () {
    Route::post('login', [SiteAuthController::class, 'login'])->middleware('guest:customer');
    Route::post('register', [SiteAuthController::class, 'register'])->middleware('guest:customer');
    Route::post('logout', [SiteAuthController::class, 'logout'])->middleware('auth:customer');

    Route::get('products', [SiteProductController::class, 'index']);

    Route::group(['middleware' => 'auth:customer'], function () {
        // edit profile
        Route::get('profile', [SiteAuthController::class, 'profile']);
        Route::put('profile', [SiteAuthController::class, 'updateProfile']);
        // orders (previously placed and new)
        // create order
        Route::get('orders', [SiteOrderController::class, 'index']);
        Route::post('orders', [SiteOrderController::class, 'store']);
        // payments
        Route::get('payments', [SiteOrderController::class, 'payments']);
    });
});
