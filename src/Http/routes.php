<?php

use DrH\Tanda\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('/tanda')->namespace(Controller::class)->name('tanda.')->group(function () {
    Route::prefix('/billing')->group(function () {
        Route::get('balance', [Controller::class, 'accountBalance'])->name('account.balance');
        Route::post('transaction-status', [Controller::class, 'requestStatus'])->name('transaction.status');

        Route::post('airtime', [Controller::class, 'airtimePurchase'])->name('airtime.purchase');
        Route::post('bill', [Controller::class, 'billPayment'])->name('bill.payment');
    });

    Route::prefix('/callbacks')->name('callback.')->group(function () {
        Route::post('/notification', [Controller::class, 'instantPaymentNotification'])->name('notification');
    });
});
