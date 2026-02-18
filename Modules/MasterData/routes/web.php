<?php

use Illuminate\Support\Facades\Route;
use Modules\MasterData\Http\Controllers\MasterDataController;
use Modules\MasterData\Http\Controllers\SignatureVerificationController;

Route::get('verify-signature/{token}', [SignatureVerificationController::class, 'verify'])->name('signature.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('masterdatas', MasterDataController::class)->names('masterdata');
});
