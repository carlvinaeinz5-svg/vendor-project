<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::controller(MarketplaceController::class)->group(function () {
    Route::get('/', 'marketplace')->name('marketplace');
    Route::get('/product/{id}', 'product')->name('product.show');
});
Route::view('/about', 'about')->name('about');

Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart');
    Route::post('/cart/add', 'add')->name('cart.add');
    Route::post('/cart/update', 'update')->name('cart.update');
    Route::post('/cart/remove', 'remove')->name('cart.remove');
});
Route::middleware('auth')->controller(OrderController::class)->group(function () {
    Route::get('/checkout', 'checkout')->name('checkout');
    Route::post('/checkout', 'place')->name('checkout.place');
    Route::get('/orders', 'index')->name('orders');
    Route::get('/orders/{order}/confirmation', 'confirmation')->name('orders.confirmation');
});

Route::get('/login', [AuthController::class,'loginForm'])->name('login');
Route::post('/login', [AuthController::class,'login'])->name('login.post');
Route::post('/logout', [AuthController::class,'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/vendor-register', [AuthController::class,'registerForm'])->name('vendor.register');
Route::post('/vendor-register', [AuthController::class,'registerVendor'])->name('vendor.register.post');

Route::middleware('auth')->prefix('/settings')->name('settings.')->controller(SettingsController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::put('/profile', 'updateProfile')->name('profile');
    Route::put('/password', 'updatePassword')->name('password');
    Route::put('/preferences', 'updatePreferences')->name('preferences');
});

Route::middleware('auth')->group(function () {
    Route::get('/vendor/dashboard', [VendorController::class,'dashboard'])->name('vendor.dashboard');
    Route::post('/vendor/products', [VendorController::class,'storeProduct'])->name('vendor.products.store');
    Route::put('/vendor/products/{product}', [VendorController::class,'updateProduct'])->name('vendor.products.update');
    Route::delete('/vendor/products/{product}', [VendorController::class,'destroyProduct'])->name('vendor.products.destroy');
    Route::patch('/vendor/order-items/{item}/status', [VendorController::class,'updateItemStatus'])->name('vendor.order.status');
    Route::patch('/vendor/payments/{payment}/confirm', [VendorController::class,'confirmPayment'])->name('vendor.payment.confirm');
    Route::get('/admin/dashboard', [AdminController::class,'dashboard'])->name('admin.dashboard');
    Route::patch('/admin/vendors/{vendor}/status', [AdminController::class,'decideVendor'])->name('admin.vendor.status');
    Route::get('/admin/categories', [AdminController::class,'categories'])->name('admin.categories');
    Route::post('/admin/categories', [AdminController::class,'storeCategory'])->name('admin.categories.store');
    Route::patch('/admin/categories/{category}', [AdminController::class,'updateCategory'])->name('admin.categories.update');
    Route::delete('/admin/categories/{category}', [AdminController::class,'destroyCategory'])->name('admin.categories.destroy');
});
