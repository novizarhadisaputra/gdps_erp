<?php

namespace Modules\MasterData\Observers;

use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\Signature;
use Modules\Project\Models\Project;

class SignatureObserver
{
    /**
     * Handle the Signature "created" event.
     */
    public function created(Signature $signature): void
    {
        $signable = $signature->signable;

        // 1. If a Profitability Analysis is being signed
        if ($signable instanceof ProfitabilityAnalysis) {
            // Synchronize the margin approved column
            if (! $signable->is_margin_approved) {
                $signable->syncIsMarginApproved();
            }

            // Check if it's now fully approved
            if ($signable->isFullyApproved()) {
                app(\Modules\Project\Services\ProjectService::class)->attemptProjectCreation($signable);
            }
        }
    }
}
