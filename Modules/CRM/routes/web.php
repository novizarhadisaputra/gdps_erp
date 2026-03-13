<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\CRMController;
use Modules\CRM\Http\Controllers\PublicProposalController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('crms', CRMController::class)->names('crm');
});

// Public Signed Routes for Proposals
Route::get('proposals/{proposal}/sign', [PublicProposalController::class, 'show'])
    ->name('proposals.public.sign')
    ->middleware('signed');

Route::post('proposals/{proposal}/sign', [PublicProposalController::class, 'sign'])
    ->name('proposals.public.submit')
    ->middleware('signed');
