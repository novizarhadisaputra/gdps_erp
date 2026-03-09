<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\MinutesOfAgreement;

class MinutesOfAgreementObserver
{
    /**
     * Handle the MinutesOfAgreement "creating" event.
     */
    public function creating(MinutesOfAgreement $minutesOfAgreement): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = MinutesOfAgreement::query()
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc') // Simple sequence for now or use specific sequence column if added later
            ->first();

        // For simplicity using a random-like sequence or we can add sequence_number to migration if needed.
        // I'll stick to a simple count for now or just the number.
        $count = MinutesOfAgreement::whereYear('created_at', $year)->count() + 1;

        $minutesOfAgreement->moa_number = sprintf('GDPS/UB/BAK-%03d/%s', $count, $shortYear);
    }
}
