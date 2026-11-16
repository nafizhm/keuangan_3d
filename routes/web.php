<?php

use App\Http\Controllers\FinanceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::middleware('guest')->group(function () {
    Route::get('/login', [FinanceController::class, 'login'])->name('login');
    Route::post('/login', [FinanceController::class, 'authenticate'])->middleware('throttle:5,1');
});
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [FinanceController::class, 'dashboard'])->name('dashboard');
    Route::get('/ubah-password', [FinanceController::class, 'password'])->name('password.edit');
    Route::put('/ubah-password', [FinanceController::class, 'updatePassword'])->middleware('throttle:5,1')->name('password.update');
    Route::get('/transaksi', [FinanceController::class, 'transactions'])->name('transactions');
    Route::post('/transaksi', [FinanceController::class, 'store'])->name('transactions.store');
    Route::put('/transaksi/{transaction}', [FinanceController::class, 'update'])->name('transactions.update');
    Route::delete('/transaksi/{transaction}', [FinanceController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/transaksi/{transaction}/lampiran', [FinanceController::class, 'attachment'])->name('attachment');
    Route::post('/logout', [FinanceController::class, 'logout'])->name('logout');
});
