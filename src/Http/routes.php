<?php

use DrH\Tanda\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('/tanda')->namespace(Controller::class)->name('tanda.')->group(function() {
    Route::prefix('/billing')->group(function() {
        Route::get('balance', [Controller::class, 'accountBalance'])->name('account.balance');
        Route::post('transaction-status', [Controller::class, 'transactionStatus'])->name('transaction.status');

        Route::post('airtime/create', [Controller::class, 'airtimePurchase'])->name('airtime.purchase');
        Route::post('bill/create', [Controller::class, 'billPayment'])->name('bill.payment');
    });

    Route::prefix('/callbacks')->name('callback.')->group(function() {
        Route::post('/register', [Controller::class, 'registerCallbackURL'])->name('register');
        Route::post('/notification', [Controller::class, 'instantPaymentNotification'])->name('notification');
    });
});
