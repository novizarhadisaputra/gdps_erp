<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\ProfitabilityAnalysis;

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
        // When PA is created, Lead moves to Negotiation stage
        if ($analysis->lead) {
            $analysis->lead->update([
                'status' => \Modules\CRM\Enums\LeadStatus::Approach,
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
     * Handle the ProfitabilityAnalysis "deleting" event.
     */
    public function deleting(ProfitabilityAnalysis $analysis): void
    {
        // Cascade delete items
        $analysis->items()->delete();
    }
}
