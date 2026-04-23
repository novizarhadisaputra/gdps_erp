<?php

namespace Modules\Project\Observers;

use Modules\MasterData\Services\SignatureService;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportObserver
{
    /**
     * Handle the WorkCompletionReport "creating" event.
     */
    public function creating(WorkCompletionReport $report): void
    {
        if (filled($report->report_number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = WorkCompletionReport::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $report->year = (int) $year;
        $report->sequence_number = $sequence;
        $report->report_number = sprintf('GDPS/UB/BAPP-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the WorkCompletionReport "saving" event.
     */
    public function saving(WorkCompletionReport $report): void
    {
        if (is_array($report->items)) {
            $report->total_amount = collect($report->items)->sum('total_price');
        }
    }

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
