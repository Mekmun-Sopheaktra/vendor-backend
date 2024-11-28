<?php

use App\Http\Controllers\admin\AuthController as AdminAuthController;
use App\Http\Controllers\api\v1\BasketController;
use App\Http\Controllers\api\v1\CommentController;
use App\Http\Controllers\api\v1\CompoundController;
use App\Http\Controllers\api\v1\HomeController;
use App\Http\Controllers\api\v1\LikeController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\OrderController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('admin/login', [AdminAuthController::class, 'Login'])->middleware('api.admin')->name('api.admin.login');
Route::post('admin/register', [AdminAuthController::class, 'Register'])->name('api.admin.register');

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('api.admin.profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('api.admin.update.profile');
    Route::get('home', [HomeController::class, 'index'])->name('api.admin.home');

    Route::prefix('search')->group(function () {
        Route::get('filter', [HomeController::class, 'filter'])->name('api.admin.filter.data');
        Route::get('', [HomeController::class, 'search'])->name('api.admin.search.data');
    });

    Route::get('product/wishlist', [ProductController::class, 'wishlist'])->name('api.admin.product.wishlist');
    Route::resource('product', ProductController::class)
        ->except(['store', 'update', 'destroy', 'edit'])
        ->names([
            'index' => 'product.admin.index',
            'show' => 'product.admin.show',
            'create' => 'product.admin.create'
        ]);
    Route::get('product/{product}/like', [LikeController::class, 'likeProduct'])->name('api.admin.product.like');

    //compound
    Route::get('compound', [CompoundController::class, 'index'])->name('api.admin.compound');
    Route::post('compound', [CompoundController::class, 'store'])->name('api.admin.compound.store');


    Route::get('comment/{product}', [CommentController::class, 'index'])->name('api.admin.comment');
    Route::post('comment', [CommentController::class, 'store'])->name('api.admin.comment.store');

    Route::prefix('cart')->group(function () {
        Route::get('', [BasketController::class, 'index'])->name('api.admin.cart');
        Route::post('add', [BasketController::class, 'add'])->name('api.admin.cart.add');
        Route::post('delete', [BasketController::class, 'delete'])->name('api.admin.cart.delete');
        Route::post('buy', [BasketController::class, 'buy'])->name('api.admin.cart.buy');
    });

    Route::prefix('orders')->group(function () {
        Route::get('', [OrderController::class, 'index']);
    });

    Route::get('address', [ProfileController::class, 'address'])->name('api.admin.address');
    Route::post('address', [ProfileController::class, 'store_address'])->name('api.admin.address.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('api.admin.notifications');
    Route::post('notifications/read', [NotificationController::class, 'read'])->name('api.admin.notifications.read');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.admin.notifications.unread');
});
