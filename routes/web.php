<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
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
    });

   
});
