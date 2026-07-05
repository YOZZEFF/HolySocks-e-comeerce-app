<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\UserController;

Route::middleware('throttle:auth')->group(function () {
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
});


Route::middleware('throttle:webhook')->group(function () {

Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'check.active'])->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'changePassword']);
});

Route::middleware(['auth:sanctum', 'role:customer' , 'check.active'])->group(function () {

    // Start  customer  Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{cartItem}', [CartController::class, 'update']);
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
    // End customer Cart

    // start customer wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
    // end customer wishlist

    //start customer address
    Route::get('/address', [AddressController::class, 'index']);
    Route::post('/address', [AddressController::class, 'store']);
    Route::put('/address/{address}', [AddressController::class, 'update']);
    Route::delete('/address/{address}', [AddressController::class, 'destroy']);
    // end customer address

    // start customer orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    // end customer orders

    // start customer coupons
    Route::post('/coupons/validate', [CouponController::class, 'validate']);
    // end customer coupons

    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment']);
});

Route::middleware([ 'auth:sanctum', 'role:customer' , 'check.active' ,'throttle:sensitive'])->group(function () {

    // reviews
    Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
    // end reviews
});

Route::middleware(['throttle:sensitive'])->group(function () {

    // start newsletter
    Route::post('/newsletter', [NewsletterController::class, 'store']);
    // end newsletter
});


Route::middleware(['throttle:api'])->group(function () {

    // start  customer  Categories Section
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('categories/{category}', [CategoryController::class, 'show']);
    // end  customer  Categories Section

    // start customer Products Section
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    // end customer Products Section

    //  Reviews
    Route::get('products/{product}/reviews', [ReviewController::class, 'index']);

});



// admin zone
Route::middleware(['auth:sanctum', 'role:admin' , 'throttle:admin'])->group(function () {

    //  Categories Section
    Route::post('admin/categories', [CategoryController::class, 'store']);
    Route::match(['put', 'post'], 'admin/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('admin/categories/{category}', [CategoryController::class, 'destroy']);
    // End Categories Section

    //  Products Section
    Route::post('admin/products', [ProductController::class, 'store']);
    Route::match(['put', 'post'], 'admin/products/{product}', [ProductController::class, 'update']);
    Route::delete('admin/products/{product}', [ProductController::class, 'destroy']);
    //  End Products Section

    //  Product Images Section

    Route::post('admin/product/{product}/images', [ProductImageController::class, 'store']);
    Route::put('admin/product/{product}/images/{image}/primary', [ProductImageController::class, 'setPrimary']);
    Route::delete('admin/product/{product}/images/{image}', [ProductImageController::class, 'destroy']);

    //  End Product Images Section

    //  Orders Section
    Route::get('/admin/orders', [OrderController::class, 'Adminindex']);
    Route::get('/admin/orders/{order}', [OrderController::class, 'show']);
    Route::put('/admin/orders/{order}/status', [OrderController::class, 'AdminUpdateStatus']);
    //  End Orders Section

    //  start Coupons Section
    Route::post('/admin/coupons', [CouponController::class, 'store']);
    Route::get('/admin/coupons', [CouponController::class, 'index']);
    Route::delete('/admin/coupons/{coupon}', [CouponController::class, 'destroy']);
    //  start reviews Section
    Route::patch('/admin/reviews/{review}/approve', [ReviewController::class, 'approve']);
    Route::patch('/admin/reviews/{review}/reject', [ReviewController::class, 'reject']);
    Route::get('/admin/reviews', [ReviewController::class, 'adminIndex']);
    //  end reviews Section

    //    start dashboard Section
    Route::get('/admin/stats', [DashboardController::class, 'stats']);

    // start Settings Section
    Route::get('/admin/settings', [App\Http\Controllers\Api\Admin\SettingController::class, 'index']);
    Route::put('/admin/settings', [App\Http\Controllers\Api\Admin\SettingController::class, 'update']);
    // end Settings Section

      // start Users Section
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::get('/admin/users/{id}', [UserController::class, 'show']);
    Route::patch('/admin/users/{id}/block', [UserController::class, 'block']);
    Route::patch('/admin/users/{id}/unblock', [UserController::class, 'unblock']);
    // end Users Section
});
// end admin zone



