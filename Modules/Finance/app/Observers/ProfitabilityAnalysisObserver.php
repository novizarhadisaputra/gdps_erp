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
}
