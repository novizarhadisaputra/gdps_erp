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
        $year = date('Y');
        $month = date('n'); // Month without leading zeros
        $shortYear = date('Y');

        // Roman numeral mapping
        $romans = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        $romanMonth = $romans[$month] ?? $month;

        $latest = WorkCompletionReport::withTrashed()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = ($latest && $latest->sequence_number) ? (int) $latest->sequence_number + 1 : 1;

        $report->year = (int) $year;
        $report->sequence_number = $sequence;
        $report->number = sprintf('GDPS/UB/BAPP-%03d/%s/%s', $sequence, $romanMonth, $year);
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
