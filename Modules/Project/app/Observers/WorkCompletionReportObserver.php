<?php

namespace Modules\Project\Observers;

use Modules\Project\Enums\WorkCompletionStatus;
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportObserver
{
    /**
     * Handle the WorkCompletionReport "updated" event.
     */
    public function updated(WorkCompletionReport $report): void
    {
        if ($report->wasChanged('status') && $report->status === WorkCompletionStatus::Submitted) {
            app(SignatureService::class)->notifyNextApprovers($report);
        }
    }
}
