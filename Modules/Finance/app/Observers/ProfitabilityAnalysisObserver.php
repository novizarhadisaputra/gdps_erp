<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\ProfitabilityAnalysis;
use App\Traits\HasAutoNumber;

class ProfitabilityAnalysisObserver
{
    use HasAutoNumber;

    /**
     * Handle the ProfitabilityAnalysis "creating" event.
     */
    public function creating(ProfitabilityAnalysis $analysis): void
    {
        $this->generateAutoNumber('document_number', 'PA');
    }
}
