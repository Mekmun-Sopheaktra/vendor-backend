<?php

use App\Http\Controllers\api\v1\BasketController;
use App\Http\Controllers\api\v1\CommentController;
use App\Http\Controllers\api\v1\CompoundController;
use App\Http\Controllers\api\v1\HomeController;
use App\Http\Controllers\api\v1\LikeController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\ProfileController;
use App\Http\Controllers\vendor\VendorAuthController;
use App\Http\Controllers\vendor\VendorOrderController;
use App\Http\Controllers\vendor\VendorProductController;
use Illuminate\Support\Facades\Route;

Route::post('vendor/login', [VendorAuthController::class, 'Login'])->name('api.vendor.login');

Route::prefix('vendor')->middleware(['auth:sanctum', 'api.vendor'])->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('api.vendor.profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('api.vendor.update.profile');
    Route::get('home', [HomeController::class, 'index'])->name('api.vendor.home');

    Route::prefix('search')->group(function () {
        Route::get('filter', [HomeController::class, 'filter'])->name('api.vendor.filter.data');
        Route::get('', [HomeController::class, 'search'])->name('api.vendor.search.data');
    });

    //products
    Route::post('product/create', [VendorProductController::class, 'store'])->name('api.vendor.product.store');
    Route::get('products', [VendorProductController::class, 'index'])->name('api.vendor.products');
    Route::get('product/{product}/like', [LikeController::class, 'likeProduct'])->name('api.vendor.product.like');

    Route::prefix('compound')->name('api.vendor.compound.')->group(function () {
        // List compounds
        Route::get('/', [CompoundController::class, 'index'])->name('index');

        // Create a compound
        Route::post('/', [CompoundController::class, 'store'])->name('store');

        // Show a specific compound
        Route::get('/{compound}', [CompoundController::class, 'show'])->name('show');

        // Update a specific compound
        Route::post('/{compound}', [CompoundController::class, 'update'])->name('update');

        // Delete a specific compound
        Route::delete('/{compound}', [CompoundController::class, 'destroy'])->name('delete');

        // Add a product to a compound
        Route::post('/{compound}/add', [CompoundController::class, 'addProduct'])->name('add.product');

        // Remove a product from a compound
        Route::post('/{compound}/remove', [CompoundController::class, 'removeProduct'])->name('remove.product');

        // Update a product in a compound
        Route::post('/{compound}/update', [CompoundController::class, 'updateProduct'])->name('update.product');

        // Show products in a compound
        Route::get('/{compound}/products', [CompoundController::class, 'showProducts'])->name('show.products');
    });

    Route::get('comment/{product}', [CommentController::class, 'index'])->name('api.vendor.comment');
    Route::post('comment', [CommentController::class, 'store'])->name('api.vendor.comment.store');

    Route::prefix('cart')->group(function () {
        Route::get('', [BasketController::class, 'index'])->name('api.vendor.cart');
        Route::post('add', [BasketController::class, 'add'])->name('api.vendor.cart.add');
        Route::post('delete', [BasketController::class, 'delete'])->name('api.vendor.cart.delete');
        Route::post('buy', [BasketController::class, 'buy'])->name('api.vendor.cart.buy');
    });

    Route::prefix('orders')->group(function () {
        Route::get('', [VendorOrderController::class, 'index']);
    });

    Route::get('address', [ProfileController::class, 'address'])->name('api.vendor.address');
    Route::post('address', [ProfileController::class, 'store_address'])->name('api.vendor.address.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('api.vendor.notifications');
    Route::post('notifications/read', [NotificationController::class, 'read'])->name('api.vendor.notifications.read');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.vendor.notifications.unread');
});
