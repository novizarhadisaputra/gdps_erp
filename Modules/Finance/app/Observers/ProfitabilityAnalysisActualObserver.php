<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\ProfitabilityAnalysisActual;

class ProfitabilityAnalysisActualObserver
{
    public function creating(ProfitabilityAnalysisActual $actual): void
    {
        if (empty($actual->user_id)) {
            $actual->user_id = auth()->id();
        }

        if (empty($actual->month)) {
            $actual->month = now()->month;
        }

        if (empty($actual->year)) {
            $actual->year = now()->year;
        }
    }
}
