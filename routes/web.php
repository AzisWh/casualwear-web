<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\SepatuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\UserCheckoutController;
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
        Route::post('/cart/add/{sepatu}', [CartController::class, 'addToCart'])->name('cart.add');
        Route::delete('/cart/delete/{cart}', [CartController::class, 'removeCart'])->name('cart.delete');
        Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');

        // co
        Route::get('/checkout', [UserCheckoutController::class, 'index'])->name('user.checkout.index');
        Route::post('/checkout/{sepatu_id}', [UserCheckoutController::class, 'store'])->name('user.checkout.store');
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
    });

   
});
