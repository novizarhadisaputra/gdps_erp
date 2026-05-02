<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\CooperationAgreement;

class CooperationAgreementObserver
{
    public function creating(CooperationAgreement $agreement): void
    {
        if (filled($agreement->number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = CooperationAgreement::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $agreement->year = $year;
        $agreement->sequence_number = $sequence;
        $agreement->number = sprintf('GDPS/UB/PKS-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the CooperationAgreement "saved" event.
     */
    public function saved(CooperationAgreement $agreement): void
    {
        // Sync Agreement Number to SalesPlan for tracking
        if ($agreement->lead && $agreement->lead->salesPlan) {
            $agreement->lead->salesPlan->updateQuietly([
                'contract_number' => $agreement->number,
            ]);
        }
    }
}
