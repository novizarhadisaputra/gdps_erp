<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\CRMController;
use Modules\CRM\Http\Controllers\PublicProposalController;
use Modules\CRM\Http\Controllers\PublicSalesOrderController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('crms', CRMController::class)->names('crm');
});


