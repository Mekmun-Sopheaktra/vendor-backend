<?php

use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\BasketController;
use App\Http\Controllers\api\v1\CategoryController;
use App\Http\Controllers\api\v1\CommentController;
use App\Http\Controllers\api\v1\HomeController;
use App\Http\Controllers\api\v1\LikeController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\OrderController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ProfileController;
use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::post('login', [AuthController::class, 'Login'])->name('api.login');
    Route::post('register', [AuthController::class, 'Register'])->name('api.register');

    Route::get('email/verify/{id}', [WebAuthController::class, 'verify'])->name('api.verification.verify'); // Make sure to keep this as your route name
    Route::get('email/resend', [WebAuthController::class, 'resend'])->name('verification.resend');
});
Route::get('user/home', [HomeController::class, 'index'])->name('api.home');
Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('permission', [AuthController::class, 'Permission']);
    Route::get('profile', [ProfileController::class, 'index'])->name('api.profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('api.update.profile');

    Route::prefix('search')->group(function () {
        Route::get('filter', [HomeController::class, 'filter'])->name('api.filter.data');
        Route::get('', [HomeController::class, 'search'])->name('api.search.data');
    });

    //category routes
    Route::prefix('category')->group(function () {
        Route::get('', [CategoryController::class, 'index'])->name('api.category');
        Route::get('{category}', [CategoryController::class, 'show'])->name('api.category.show');
        Route::get('{categoryId}/highlight-products', [CategoryController::class, 'highlightProducts']); // Highlighted products based on tag
        Route::get('{categoryId}/products', [CategoryController::class, 'listProductsInCategory']); // List products in a category
    });


    //product routes
    Route::prefix('product')->group(function () {
        Route::get('wishlist', [ProductController::class, 'wishlist'])->name('api.product.wishlist');
        Route::resource('', ProductController::class)->except(['store', 'update', 'delete', 'edit']);
        Route::get('{product}/like', [LikeController::class, 'likeProduct'])->name('api.product.like');

        Route::get('latest', [ProductController::class, 'latestProducts']); // Latest products
        Route::get('{id}/related', [ProductController::class, 'relatedProducts']); // Related products
        Route::get('discounted', [ProductController::class, 'discountedProducts']); // Discounted products
        Route::get('filter', [ProductController::class, 'filterProducts']); // Filter products by price, size, and popularity
        Route::get('new-arrivals', [ProductController::class, 'newArrivals']); // New arrivals
    });

    Route::get('comment/{product}', [CommentController::class, 'index'])->name('api.comment');
    Route::post('comment', [CommentController::class, 'store'])->name('api.comment.store');

    Route::prefix('cart')->group(function () {
        Route::get('', [BasketController::class, 'index'])->name('api.cart');
        Route::post('add', [BasketController::class, 'add'])->name('api.cart.add');
        Route::post('delete', [BasketController::class, 'delete'])->name('api.cart.delete');
        Route::post('buy', [BasketController::class, 'buy'])->name('api.cart.buy');
    });

    Route::prefix('orders')->group(function () {
        Route::get('', [OrderController::class, 'index']);
    });

    Route::get('address', [ProfileController::class, 'address'])->name('api.address');
    Route::post('address', [ProfileController::class, 'store_address'])->name('api.address.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('api.notifications');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.notifications.unread');
});
