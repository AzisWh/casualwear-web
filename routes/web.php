<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\MonitorTraksaksiController;
use App\Http\Controllers\Admin\SepatuController;
use App\Http\Controllers\Admin\VoucherController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\CancelCheckout;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CheckShipping;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\UserCheckoutController;
use App\Http\Controllers\User\UserDataController;
use App\Models\KategoriModel;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// auth
Route::get('/login-page',[AuthController::class, 'indexLogin'])->name('login');
Route::post('/login-action', [AuthController::class, 'fungsiLogin'])->name('login.action');
Route::post('/register-action', [AuthController::class, 'fungsiRegister'])->name('register.action');
Route::get('/register-page',[AuthController::class, 'indexRegister'])->name('register');
Route::post('/logout-action', [AuthController::class, 'logout'])->name('logout.action');

// dashboard
Route::get('/', [DashboardController::class, 'index'])->name('user.home');



Route::middleware('auth')->group(function () {
    Route::middleware('role:0')->group(function () {
        // profile
        Route::put('/profile', [UserDataController::class, 'update'])->name('user.profile.update');
        // cart
        Route::post('/cart/add/{sepatu}', [CartController::class, 'addToCart'])->name('cart.add');
        Route::delete('/cart/delete/{cart}', [CartController::class, 'removeCart'])->name('cart.delete');
        Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');
        Route::get('/cart-page', [CartController::class, 'index'])->name('user.cart.index');
        Route::post('/cart/checkout/{id}', [CartController::class, 'checkoutFromCart'])->name('user.cart.checkout');


        // co
        // Route::get('/checkout', [UserCheckoutController::class, 'index'])->name('user.checkout.index');
        Route::get('/checkout', [UserCheckoutController::class, 'index'])->name('user.checkout.index');
        Route::post('/checkout/{sepatu_id?}', [UserCheckoutController::class, 'store'])->name('user.checkout.store');
        Route::get('/checkout/detail/{id}', [UserCheckoutController::class, 'detail'])->name('user.checkout.detail');
        Route::get('/cities/{province_id}', [UserCheckoutController::class, 'getCities'])->name('user.checkout.cities');
        Route::post('/checkout/calculate/{id}', [UserCheckoutController::class, 'calculateShipping'])->name('user.checkout.calculate');
        Route::post('/checkout/save/{id}', [UserCheckoutController::class, 'saveShipping'])->name('user.checkout.save');
        Route::patch('/checkout/expire/{transactionId}', [UserCheckoutController::class, 'expire'])->name('user.checkout.expire');
        Route::post('/checkout/{id}/update-address', [UserCheckoutController::class, 'updateAddress'])->name('user.checkout.updateAddress');
        Route::post('/checkout/reconfirm/{id}', [UserCheckoutController::class, 'reconfirmCheckout'])->name('user.checkout.reconfirm');
        Route::patch('/checkout/{id}/cancel', [CancelCheckout::class, 'cancelRequest'])->name('user.checkout.cancel');
        // voucher
        Route::post('/check-voucher', [UserCheckoutController::class, 'checkVoucher'])->name('user.check-voucher');
        // shipping
        Route::get('/check-shipping', [CheckShipping::class, 'index'])->name('user.shipping.index');
    });
    //admin
    Route::middleware('role:1')->group(function () {
        Route::get('/admin-dashboard',[AdminDashboardController::class, 'index'])->name('admin.home');
        // kategori
        Route::get('/admin-kategori', [KategoriController::class, 'index'])->name('admin.kategori'); 
        Route::post('/admin-kategori', [KategoriController::class, 'addKategori'])->name('admin.kategori.store');
        Route::patch('/admin-kategori/{kategori}', [KategoriController::class, 'editKategori'])->name('admin.kategori.update');
        Route::delete('/admin-kategori/{kategori}', [KategoriController::class, 'delKategori'])->name('admin.kategori.destroy');
        // sepatu
        Route::get('/admin-sepatu', [SepatuController::class, 'index'])->name('admin.sepatu');
        Route::post('/admin-sepatu', [SepatuController::class, 'addSepatu'])->name('admin.sepatu.store');
        Route::patch('/admin-sepatu/{sepatu}', [SepatuController::class, 'editSepatu'])->name('admin.sepatu.update');
        Route::delete('/admin-sepatu/{sepatu}', [SepatuController::class, 'delSepatu'])->name('admin.sepatu.destroy');
        // voucher
        Route::get('/admin-voucher', [VoucherController::class, 'index'])->name('admin.voucher');
        Route::post('/admin-voucher', [VoucherController::class, 'addVoucher'])->name('admin.voucher.store');
        Route::patch('/admin-voucher/{voucher}', [VoucherController::class, 'editVoucher'])->name('admin.voucher.update');
        Route::delete('/admin-voucher/{voucher}', [VoucherController::class, 'delVoucher'])->name('admin.voucher.destroy');
        // transaksi
        Route::get('/transactions', [MonitorTraksaksiController::class, 'index'])->name('admin.transactions.index');
        Route::delete('/transactions/{id}', [MonitorTraksaksiController::class, 'destroy'])->name('admin.transactions.destroy');
        Route::get('/transactions/{id}/detail', [MonitorTraksaksiController::class, 'show'])->name('admin.transactions.show');
        Route::patch('/transactions/{id}/update-shipping', [MonitorTraksaksiController::class, 'updateShippingStatus'])->name('admin.transactions.update.shipping');
        // cancel
        Route::patch('/transactions/{id}/approve-cancellation', [MonitorTraksaksiController::class, 'approveCancellation'])->name('admin.transactions.approve.cancellation');
        Route::patch('/transactions/{id}/reject-cancellation', [MonitorTraksaksiController::class, 'rejectCancellation'])->name('admin.transactions.reject.cancellation');
    });

   
});
