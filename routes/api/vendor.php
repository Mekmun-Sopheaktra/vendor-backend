<?php

use App\Http\Controllers\api\v1\BasketController;
use App\Http\Controllers\api\v1\CommentController;
use App\Http\Controllers\api\v1\HomeController;
use App\Http\Controllers\api\v1\LikeController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\OrderController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('vendor')->middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('api.vendor.profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('api.vendor.update.profile');
    Route::get('home', [HomeController::class, 'index'])->name('api.vendor.home');

    Route::prefix('search')->group(function () {
        Route::get('filter', [HomeController::class, 'filter'])->name('api.vendor.filter.data');
        Route::get('', [HomeController::class, 'search'])->name('api.vendor.search.data');
    });

    Route::resource('product', ProductController::class)
        ->except(['store', 'update', 'destroy', 'edit'])
        ->names([
            'index' => 'product.vendor.index',
            'show' => 'product.vendor.show',
            'create' => 'product.vendor.create'
        ]);

    Route::get('product/{product}/like', [LikeController::class, 'likeProduct'])->name('api.vendor.product.like');

    Route::get('comment/{product}', [CommentController::class, 'index'])->name('api.vendor.comment');
    Route::post('comment', [CommentController::class, 'store'])->name('api.vendor.comment.store');

    Route::prefix('cart')->group(function () {
        Route::get('', [BasketController::class, 'index'])->name('api.vendor.cart');
        Route::post('add', [BasketController::class, 'add'])->name('api.vendor.cart.add');
        Route::post('delete', [BasketController::class, 'delete'])->name('api.vendor.cart.delete');
        Route::post('buy', [BasketController::class, 'buy'])->name('api.vendor.cart.buy');
    });

    Route::prefix('orders')->group(function () {
        Route::get('', [OrderController::class, 'index']);
    });

    Route::get('address', [ProfileController::class, 'address'])->name('api.vendor.address');
    Route::post('address', [ProfileController::class, 'store_address'])->name('api.vendor.address.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('api.vendor.notifications');
    Route::post('notifications/read', [NotificationController::class, 'read'])->name('api.vendor.notifications.read');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.vendor.notifications.unread');
});
