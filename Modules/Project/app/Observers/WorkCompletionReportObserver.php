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
        $shortYear = $date->format('y');

        $latest = WorkCompletionReport::withTrashed()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = ($latest && $latest->sequence_number) ? (int) $latest->sequence_number + 1 : 1;

        $report->year = (int) $year;
        $report->sequence_number = $sequence;
        $report->number = sprintf('GDPS/UB/BAPP-%03d/%s', $sequence, $shortYear);

        if (empty($report->project_area_id)) {
            if ($report->project?->project_area_id) {
                $report->project_area_id = $report->project->project_area_id;
            } elseif ($report->sourceable?->project?->project_area_id) {
                $report->project_area_id = $report->sourceable->project->project_area_id;
            }
        }

        if (empty($report->product_cluster_id)) {
            if ($report->project?->product_cluster_id) {
                $report->product_cluster_id = $report->project->product_cluster_id;
            } elseif ($report->sourceable?->project?->product_cluster_id) {
                $report->product_cluster_id = $report->sourceable->project->product_cluster_id;
            }
        }
    }

    /**
     * Handle the WorkCompletionReport "saving" event.
     */
    public function saving(WorkCompletionReport $report): void
    {
        $itemsData = is_array($report->items) && isset($report->items['id']) ? $report->items['id'] : ($report->items ?? []);

        if (! empty($itemsData)) {
            $items = collect($itemsData);
            $report->total_amount = $items->sum('total_price');

            // Auto-calculate Tax if not manually set or to keep it in sync
            $basis = $report->tax_basis ?? 'total';
            $baseAmount = 0;

            if ($basis === 'total') {
                $baseAmount = $report->total_amount;
            } elseif ($basis === 'management_fee') {
                $mfSum = $items->sum('management_fee');

                if ($mfSum <= 0) {
                    // Fallback logic to find management fee from items (matching form logic)
                    $mfSum = $items->filter(function ($item) {
                        $name = strtolower($item['item_name'] ?? $item['work_measurement'] ?? '');

                        return str_contains($name, 'management fee') || str_contains($name, 'fee management');
                    })->sum('total_price');
                }
                $baseAmount = $mfSum;
            } else {
                $baseAmount = $report->tax_base_amount ?? 0;
            }

            $report->tax_base_amount = $baseAmount;

            if ($report->tax_id && $report->tax) {
                $report->tax_amount = $report->tax->calculateTax($baseAmount);
            } else {
                $percentage = (float) ($report->tax_percentage ?? 12);
                $report->tax_amount = floor($baseAmount * ($percentage / 100));
            }
        }

        if (empty($report->snapshot) && $report->sourceable && isset($report->sourceable->snapshot)) {
            $report->snapshot = $report->sourceable->snapshot;
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
                $revision = $report->revisions()->create([
                    'number' => $originalStatus !== null ? $report->getOriginal('number') : $report->number,
                    'sequence_number' => $report->getOriginal('revision_number') ?? 0,
                    'year' => date('Y'),
                    'snapshot' => $report->getRawOriginal(),
                    'reason' => request()->input('reason') ?? 'Manual revision triggered.',
                    'user_id' => auth()->id(),
                ]);

                // Copy Media Snapshots
                foreach (['draft_report', 'signed_report', 'completion_documents'] as $collection) {
                    $report->getMedia($collection)->each(function ($media) use ($revision, $collection) {
                        $media->copy($revision, $collection);
                    });
                }

                // Update main document to reflect revision status
                $date = $report->document_date ? Carbon::parse($report->document_date) : now();
                $shortYear = $date->format('y');
                $newRevisionNumber = ($report->getOriginal('revision_number') ?? 0) + 1;

                $baseNumber = sprintf('GDPS/UB/BAPP-%03d', $report->sequence_number);
                $newNumber = sprintf('%s/REV/%02d/%s', $baseNumber, $newRevisionNumber, $shortYear);

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
