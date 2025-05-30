<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\AuthController;
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


Route::middleware('auth')->group(function () {
    Route::middleware('role:0')->group(function () {

       
    });
    //admin
    Route::middleware('role:1')->group(function () {
        Route::get('/admin-dashboard',[AdminDashboardController::class, 'index'])->name('admin.home');
        // kategori
        Route::get('/admin-kategori', [KategoriController::class, 'index'])->name('admin.kategori'); 
        Route::post('/admin-kategori', [KategoriController::class, 'addKategori'])->name('admin.kategori.store');
        Route::patch('/admin-kategori/{kategori}', [KategoriController::class, 'editKategori'])->name('admin.kategori.update');
        Route::delete('/admin-kategori/{kategori}', [KategoriController::class, 'delKategori'])->name('admin.kategori.destroy');
    });

   
});
