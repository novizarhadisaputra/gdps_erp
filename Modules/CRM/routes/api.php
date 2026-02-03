<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\CRMController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('crms', CRMController::class)->names('crm');
});

// Risk Register Webhook (Protected by Shared Secret or optional middleware)
// Assuming we use Shared Secret Header validation inside controller, but we can also use 'api' middleware.
// If purely external machine-to-machine, 'api' group is fine.
Route::prefix('v1/crm')->group(function () {
    Route::post('risk-register/webhook', [\Modules\CRM\Http\Controllers\RiskRegisterWebhookController::class, 'handle']);
});

// External API Access for General Information (Protected by ApiClient Token)
Route::middleware(['auth:sanctum', \Modules\CRM\Http\Middleware\EnsureUserIsApiClient::class])
    ->prefix('v1/crm')
    ->group(function () {
        Route::get('general-informations', [\Modules\CRM\Http\Controllers\GeneralInformationApiController::class, 'index']);
        Route::get('general-informations/{id}', [\Modules\CRM\Http\Controllers\GeneralInformationApiController::class, 'show']);
    });
