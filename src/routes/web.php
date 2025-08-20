<?php

use Illuminate\Support\Facades\Route;
use admin\wallets\Controllers\Backend\TransactionManagerController;
use admin\wallets\Controllers\Backend\WithdrawManagerController;

Route::name('admin.')->middleware(['web','admin.auth'])->group(function () {  
    // Withdraw Request Routes
    Route::resource('withdraws', WithdrawManagerController::class);

    // Transaction History Routes
    Route::resource('transactions', TransactionManagerController::class);
});
