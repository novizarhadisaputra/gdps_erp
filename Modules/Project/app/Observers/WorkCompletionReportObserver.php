<?php

namespace Modules\Project\Observers;

use Illuminate\Support\Carbon;
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
        $date = $report->document_date ? Carbon::parse($report->document_date) : now();
        $year = $date->format('Y');
        $month = (int) $date->format('n'); // Month without leading zeros
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
        if ($report->wasChanged('status')) {
            if ($report->status === WorkCompletionStatus::Submitted) {
                app(SignatureService::class)->notifyNextApprovers($report);
            }

            // Revision Logic: Capture snapshot if status changed back to Draft from a non-Draft status
            $originalStatus = $report->getOriginal('status');
            if ($report->status === WorkCompletionStatus::Draft && $originalStatus !== WorkCompletionStatus::Draft) {
                $report->revisions()->create([
                    'number' => $originalStatus !== null ? $report->getOriginal('number') : $report->number,
                    'sequence_number' => $report->getOriginal('revision_number') ?? 0,
                    'snapshot' => $report->getRawOriginal(),
                    'reason' => request()->input('reason') ?? 'Manual revision triggered.',
                    'user_id' => auth()->id(),
                ]);

                // Update main document to reflect revision status
                $newRevisionNumber = $report->revision_number + 1;
                
                $date = $report->document_date ? \Illuminate\Support\Carbon::parse($report->document_date) : now();
                $year = $date->format('Y');
                $month = (int) $date->format('n');
                $romans = [
                    1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                    7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
                ];
                $romanMonth = $romans[$month] ?? $month;
                
                $baseNumber = sprintf('GDPS/UB/BAPP-%03d', $report->sequence_number);
                $newNumber = sprintf('%s/REV/%02d/%s/%s', $baseNumber, $newRevisionNumber, $romanMonth, $year);

                $report->updateQuietly([
                    'revision_number' => $newRevisionNumber,
                    'previous_code' => $report->number,
                    'number' => $newNumber,
                ]);
            }
        }
    }

    /**
     * Handle the WorkCompletionReport "saved" event.
     */
    public function saved(WorkCompletionReport $report): void
    {
        // Sync BA Number to SalesPlan for tracking
        if ($report->project && $report->project->lead && $report->project->lead->salesPlan) {
            $report->project->lead->salesPlan->updateQuietly([
                'ba_number' => $report->number,
            ]);
        }
    }
}
