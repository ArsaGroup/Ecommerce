<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;

Route::prefix('api')->group(function () {
    Route::middleware('auth:sanctum')->get('/redirect', [HomeController::class, 'redirect']);
    Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Authentication Routes
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);

    // Public Product Routes (No Authentication Needed)
    Route::get('/products', [HomeController::class, 'product']);
    Route::get('/product_details/{id}', [HomeController::class, 'product_details']);
    Route::get('/product_search', [HomeController::class, 'product_search']);
    Route::get('/search_product', [HomeController::class, 'search_product']);

    // Public Category Routes (No Authentication Needed)
    Route::get('/view_category', [AdminController::class, 'view_category']);
    Route::get('/view_product', [AdminController::class, 'view_product']);

    // User Routes (Protected by auth:sanctum middleware)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [UserController::class, 'user']);
        Route::get('/dashboard', [HomeController::class, 'dashboard']);
        Route::get('/show_order', [HomeController::class, 'show_order']);
        Route::get('/cancel_order/{id}', [HomeController::class, 'cancel_order']);
        Route::post('/add_cart/{id}', [HomeController::class, 'add_cart']);
        Route::get('/show_cart', [HomeController::class, 'show_cart']);
        Route::get('/remove_cart/{id}', [HomeController::class, 'remove_cart']);
        Route::get('/cash_order', [HomeController::class, 'cash_order']);
        Route::get('/stripe/{totalprice}', [HomeController::class, 'stripe']);
        Route::post('/stripe/{totalprice}', [HomeController::class, 'stripePost'])->name('stripe.post');
        Route::post('/add_comment', [HomeController::class, 'add_comment']);
        Route::post('/add_reply', [HomeController::class, 'add_reply']);
    });

    // Admin Routes (Protected by auth:sanctum middleware)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/add_category', [AdminController::class, 'add_category']);
        Route::get('/delete_category/{id}', [AdminController::class, 'delete_category']);
        Route::post('/add_product', [AdminController::class, 'add_product']);
        Route::get('/show_product', [AdminController::class, 'show_product']);
        Route::get('/delete_product/{id}', [AdminController::class, 'delete_product']);
        Route::get('/update_product/{id}', [AdminController::class, 'update_product']);
        Route::post('/update_product_confirm/{id}', [AdminController::class, 'update_product_confirm']);
        Route::get('/order', [AdminController::class, 'order']);
        Route::get('/delivered/{id}', [AdminController::class, 'delivered']);
        Route::get('/print_pdf/{id}', [AdminController::class, 'print_pdf']);
        Route::get('/send_email/{id}', [AdminController::class, 'send_email']);
        Route::post('/send_user_email/{id}', [AdminController::class, 'send_user_email']);
        Route::get('/search', [AdminController::class, 'searchdata']);
    });
});
