<?php

use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\BasketController;
use App\Http\Controllers\api\v1\CategoryController;
use App\Http\Controllers\api\v1\CommentController;
use App\Http\Controllers\api\v1\CompoundController;
use App\Http\Controllers\api\v1\HomeController;
use App\Http\Controllers\api\v1\LikeController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\OrderController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ProfileController;
use App\Http\Controllers\api\v1\VendorController;
use App\Http\Controllers\Auth\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'Login'])->name('api.login');
    Route::post('register', [AuthController::class, 'Register'])->name('api.register');

    Route::get('email/verify/{id}', [WebAuthController::class, 'verify'])->name('api.verification.verify'); // Make sure to keep this as your route name
    Route::get('email/resend', [WebAuthController::class, 'resend'])->name('verification.resend');
});

//requestVendor create vendor data
Route::post('v1/request/vendor', [VendorController::class, 'requestVendor'])->name('api.vendor.request');
//createVendor create vendor data and send email to vendor for verification
Route::post('v1/vendor/{id}/create', [VendorController::class, 'createVendor'])->name('api.vendor.create');

Route::get('v1/home', [HomeController::class, 'index'])->name('api.home');

//product routes
Route::get('v1/products', [ProductController::class, 'index'])->name('api.product');

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
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
        Route::get('latest', [ProductController::class, 'latestProducts'])->name('api.product.latest'); // Latest products
        Route::get('{id}/related', [ProductController::class, 'relatedProducts'])->name('api.product.related'); // Related products
        Route::get('discounted', [ProductController::class, 'discountedProducts'])->name('api.product.discounted'); // Discounted products
        Route::get('special', [CompoundController::class, 'index'])->name('api.product.compound'); // Compound products
        Route::get('filter', [ProductController::class, 'filterProducts'])->name('api.product.filter'); // Filter products by price, size, and popularity
        Route::get('new-arrivals', [ProductController::class, 'newArrivals'])->name('api.product.new.arrivals'); // New arrivals
        Route::get('wishlist', [ProductController::class, 'wishlist'])->name('api.product.wishlist');
        Route::get('', [ProductController::class, 'index'])->name('api.product');
        Route::get('{product}', [ProductController::class, 'show'])->name('api.product.show');
        Route::post('{product}/like', [LikeController::class, 'likeProduct'])->name('api.product.like');
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
