<?php

namespace Modules\Finance\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Services\ProjectService;

class ProfitabilityAnalysisObserver
{
    /**
     * Handle the ProfitabilityAnalysis "creating" event.
     */
    public function creating(ProfitabilityAnalysis $analysis): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = ProfitabilityAnalysis::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $analysis->year = $year;
        $analysis->sequence_number = $sequence;
        // PA = Profitability Analysis
        $analysis->document_number = sprintf('GDPS/UB/PA-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the ProfitabilityAnalysis "created" event.
     */
    public function created(ProfitabilityAnalysis $analysis): void
    {
        // When PA is created, Lead moves to Approach stage if not already further
        if ($analysis->lead && $analysis->lead->status->weight() < LeadStatus::Approach->weight()) {
            $analysis->lead->update([
                'status' => LeadStatus::Approach,
            ]);
        }

        // Auto-copy media from General Information if present
        if ($analysis->generalInformation) {
            foreach (['tor', 'rfp', 'rfi'] as $collection) {
                // Only copy if PA doesn't have its own media yet
                if (! $analysis->hasMedia($collection)) {
                    $media = $analysis->generalInformation->getFirstMedia($collection);
                    if ($media) {
                        $media->copy($analysis, $collection);
                    }
                }
            }
        }
    }

    /**
     * Handle the ProfitabilityAnalysis "updated" event.
     */
    public function updated(ProfitabilityAnalysis $analysis): void
    {
        // 1. When Margin is approved, Lead moves to Proposal stage
        if (
            $analysis->isDirty('is_margin_approved') &&
            $analysis->is_margin_approved &&
            $analysis->lead &&
            $analysis->lead->status->weight() < LeadStatus::Proposal->weight()
        ) {
            $analysis->lead->update([
                'status' => LeadStatus::Proposal,
            ]);
        }

        // 2. When PA is reset to Draft (due to revision), track revision info and clear signatures
        if ($analysis->wasChanged('status') && $analysis->status === ProfitabilityAnalysisStatus::Draft) {
            $analysis->updateQuietly([
                'revision_number' => $analysis->revision_number + 1,
                'previous_code' => $analysis->document_number,
                'is_margin_approved' => false,
            ]);

            // Clear signatures from PA
            $analysis->signatures()->delete();

            // Downgrade Lead status to Approach (Revision stage)
            if ($analysis->lead) {
                $analysis->lead->update([
                    'status' => LeadStatus::Approach,
                ]);
            }
        }

        // 3. Sync calculations to Sales Plan
        if ($analysis->lead && $analysis->lead->salesPlan) {
            $analysis->lead->salesPlan->updateQuietly([
                'npm_percentage' => $analysis->net_profit_margin,
                'management_fee_percentage' => $analysis->management_fee_rate,
            ]);
        }

        // 4. Sync calculation results to associated Proposal if exists
        // Refinement: Only sync when PA is Approved and Proposal amount is 0
        if (
            $analysis->wasChanged('status') &&
            $analysis->status === ProfitabilityAnalysisStatus::Approved &&
            $analysis->lead
        ) {
            foreach ($analysis->lead->proposals as $proposal) {
                if ((float) $proposal->amount === 0.0) {
                    $proposal->updateQuietly([
                        'amount' => $analysis->revenue_per_month,
                    ]);
                }
            }
        }

        // 5. When PA is Approved, attempt to create a Project
        if ($analysis->wasChanged('status') && $analysis->status === ProfitabilityAnalysisStatus::Approved) {
            app(ProjectService::class)->attemptProjectCreation($analysis);
        }
    }

    /**
     * Handle the ProfitabilityAnalysis "deleting" event.
     */
    public function deleting(ProfitabilityAnalysis $analysis): void
    {
        // Cascade delete items
        $analysis->items()->delete();
    }
}
