<?php

use App\Http\Controllers\api\v1\BasketController;
use App\Http\Controllers\api\v1\CategoryController;
use App\Http\Controllers\api\v1\CommentController;
use App\Http\Controllers\api\v1\CompoundController;
use App\Http\Controllers\api\v1\LikeController;
use App\Http\Controllers\api\v1\NotificationController;
use App\Http\Controllers\api\v1\ProfileController;
use App\Http\Controllers\vendor\VendorAuthController;
use App\Http\Controllers\vendor\VendorDashboardController;
use App\Http\Controllers\vendor\VendorDiscountController;
use App\Http\Controllers\vendor\VendorOrderController;
use App\Http\Controllers\vendor\VendorProductController;
use Illuminate\Support\Facades\Route;

Route::post('vendor/login', [VendorAuthController::class, 'Login'])->name('api.vendor.login');

Route::prefix('vendor')->middleware(['auth:sanctum', 'api.vendor'])->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('api.vendor.profile');
    Route::post('profile', [ProfileController::class, 'update'])->name('api.vendor.update.profile');
    Route::get('dashboard', [VendorDashboardController::class, 'index'])->name('api.vendor.home');

    Route::prefix('search')->group(function () {
        Route::get('filter', [VendorDashboardController::class, 'filter'])->name('api.vendor.filter.data');
        Route::get('', [VendorDashboardController::class, 'search'])->name('api.vendor.search.data');
    });
    Route::get('product/options', [VendorProductController::class, 'getOptions'])->name('api.vendor.product.options');

    Route::get('category', [CategoryController::class, 'index'])->name('api.category');

    //products
    Route::get('product', [VendorProductController::class, 'index'])->name('api.vendor.products');
    Route::post('product/create', [VendorProductController::class, 'store'])->name('api.vendor.product.store');
    Route::get('product/{product}', [VendorProductController::class, 'show'])->name('api.vendor.product.show');
    //delete product
    Route::delete('product/{product}', [VendorProductController::class, 'destroy'])->name('api.vendor.product.delete');
    Route::post('product/update/{product}', [VendorProductController::class, 'update'])->name('api.vendor.product.update');

    //get product options

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
        Route::get('history', [VendorOrderController::class, 'history'])->name('api.vendor.order.history');
        //show
        Route::get('{order}', [VendorOrderController::class, 'show'])->name('api.vendor.order.show');
        Route::post('approve/{order}', [VendorOrderController::class, 'approveOrder'])->name('api.vendor.order.approve');
        Route::post('reject/{order}', [VendorOrderController::class, 'rejectOrder'])->name('api.vendor.order.reject');
    });

    //discount routes
    Route::prefix('discount')->group(function () {
        // List discounts
        Route::get('/', [VendorDiscountController::class, 'index'])->name('api.vendor.discount.index');
        // Create a discount
        Route::post('/', [VendorDiscountController::class, 'store'])->name('api.vendor.discount.store');
        //show
        Route::get('/{discount}', [VendorDiscountController::class, 'show'])->name('api.vendor.discount.show');
        // Update a discount
        Route::post('/{discount}', [VendorDiscountController::class, 'update'])->name('api.vendor.discount.update');
        // Delete a discount
        Route::delete('/{discount}', [VendorDiscountController::class, 'destroy'])->name('api.vendor.discount.delete');

    });

    Route::get('address', [ProfileController::class, 'address'])->name('api.vendor.address');
    Route::post('address', [ProfileController::class, 'store_address'])->name('api.vendor.address.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('api.vendor.notifications');
    Route::post('notifications/read', [NotificationController::class, 'read'])->name('api.vendor.notifications.read');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('api.vendor.notifications.unread');
});
