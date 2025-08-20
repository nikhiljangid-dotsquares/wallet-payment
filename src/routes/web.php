<?php

use Illuminate\Support\Facades\Route;
use admin\wallets\Controllers\Admin\WalletTransactionController;
use admin\wallets\Controllers\Admin\WalletWithdrawController;

Route::name('admin.')->middleware(['web','admin.auth'])->group(function () {  
    // Withdraw Request Routes
    Route::resource('withdraws', WalletWithdrawController::class);

    // Transaction History Routes
    Route::resource('transactions', WalletTransactionController::class);
});
