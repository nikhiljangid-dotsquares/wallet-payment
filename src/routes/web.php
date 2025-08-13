<?php

use Illuminate\Support\Facades\Route;
use admin\wallets\Controllers\TransactionManagerController;

Route::name('admin.')->middleware(['web','admin.auth'])->group(function () {  
    Route::resource('transactions', TransactionManagerController::class);
});
