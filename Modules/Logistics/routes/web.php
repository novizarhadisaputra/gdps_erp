<?php

use Illuminate\Support\Facades\Route;
use Modules\Logistics\Http\Controllers\LogisticsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('logistic-module', LogisticsController::class)->names('logistics-module');
});
