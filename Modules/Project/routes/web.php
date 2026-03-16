<?php

use Illuminate\Support\Facades\Route;
use Modules\Project\Http\Controllers\ProjectController;
use Modules\Project\Http\Controllers\PublicWorkCompletionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('projects', ProjectController::class)->names('project');
});

// Public Signed Routes for Work Completion Report (BAPP)
Route::get('work-completion-reports/{report}/sign', [PublicWorkCompletionController::class, 'show'])
    ->name('work_completion_reports.public.sign')
    ->middleware('signed');

Route::post('work-completion-reports/{report}/sign', [PublicWorkCompletionController::class, 'sign'])
    ->name('work_completion_reports.public.submit')
    ->middleware('signed');
