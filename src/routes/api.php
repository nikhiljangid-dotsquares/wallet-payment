<?php

use Illuminate\Support\Facades\Route;
use admin\wallets\Controllers\Api\V1\StripeController;
use admin\wallets\Controllers\Api\V1\WalletController;

Route::name('api.')->middleware(['api','auth:sanctum'])->group(function () {  
    Route::get('connect-stripe', [StripeController::class, 'connectStripe']);

    // Wallet API
    Route::prefix('wallet')->group(function () {
        Route::post('deposit/initiate', [WalletController::class, 'depositInitiate']);
        Route::post('deposit/confirm', [WalletController::class, 'depositConfirm']);
        Route::get('balance', [WalletController::class, 'getBalance']);
        Route::post('withdraw-request', [WalletController::class, 'withdrawRequest']);
        Route::post('withdraw-request-cancel', [WalletController::class, 'withdrawRequestCancel']);
        Route::post('send', [WalletController::class, 'sendMoney']);
        Route::get('transactions', [WalletController::class, 'transactionHistory']);
    });
});
