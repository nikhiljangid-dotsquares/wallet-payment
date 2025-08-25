<?php

use Illuminate\Support\Facades\Route;
use admin\wallets\Controllers\Admin\WalletTransactionController;
use admin\wallets\Controllers\Admin\WalletWithdrawController;
use admin\wallets\Controllers\Admin\WalletWebStripeController;

Route::name('admin.')->middleware(['web','admin.auth'])->group(function () {  
    // Withdraw Request Routes
    Route::resource('withdraws', WalletWithdrawController::class)->only([
        'index', 'show'
    ]);
    Route::post('withdraws/{id}/status', [WalletWithdrawController::class, 'changeWithdrawStatus'])
        ->name('withdraws.changeStatus');

    // Transaction History Routes
    Route::resource('transactions', WalletTransactionController::class);
});

Route::get('/connect-account-redirect/{userId}', [WalletWebStripeController::class, 'connectAccountRedirect'])->name('stripe.connect.account.redirect');
