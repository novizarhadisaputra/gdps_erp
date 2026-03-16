<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\FinanceController;
use Modules\Finance\Http\Controllers\PublicInvoiceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('finances', FinanceController::class)->names('finance');
});

// Public Signed Routes for Invoice
Route::get('invoices/{invoice}/sign', [PublicInvoiceController::class, 'show'])
    ->name('invoices.public.sign')
    ->middleware('signed');

Route::post('invoices/{invoice}/sign', [PublicInvoiceController::class, 'sign'])
    ->name('invoices.public.submit')
    ->middleware('signed');
