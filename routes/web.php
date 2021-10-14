<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\TravelPackageController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DetailController;
use App\Http\Controllers\MidtransController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/detail/{slug}', [DetailController::class, 'index'])->name('detail');

// Checkout
Route::get('/checkout/{id}', [CheckoutController::class, 'index'])->middleware('auth', 'verified')->name('checkout');
Route::post('/checkout/{id}', [CheckoutController::class, 'process'])->middleware('auth', 'verified')->name('checkout_process');
Route::post('/checkout/create/{detail_id}', [CheckoutController::class, 'create'])->middleware('auth', 'verified')->name('checkout_create');
Route::get('/checkout/remove/{detail_id}', [CheckoutController::class, 'remove'])->middleware('auth', 'verified')->name('checkout_remove');
Route::get('/checkout/success/{id}', [CheckoutController::class, 'success'])->middleware('auth', 'verified')->name('checkout_success');

// Route::prefix('admin')->namespace('Admin')->middleware('auth', 'admin')
Route::prefix('admin')->middleware('auth', 'admin')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('travel-package', TravelPackageController::class);
        Route::resource('gallery', GalleryController::class);
        Route::resource('transaction', TransactionController::class);
    });

Auth::routes(
    ['verify' => true]
);

// midtrans
Route::post('/midtrans/callback', [MidtransController::class, 'nitificationHandler']);
Route::get('/midtrans/finish', [MidtransController::class, 'finishRedirect']);
Route::get('/midtrans/unfinish', [MidtransController::class, 'unfinishRedirect']);
Route::get('/midtrans/error', [MidtransController::class, 'errorRedirect']);
