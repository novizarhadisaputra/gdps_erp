<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\MinutesOfAgreement;
use Modules\MasterData\Services\SignatureService;

class MinutesOfAgreementObserver
{
    public function creating(MinutesOfAgreement $minutesOfAgreement): void
    {
        if (empty($minutesOfAgreement->moa_number)) {
            $year = date('Y');
            $shortYear = date('y');

            $latest = MinutesOfAgreement::query()
                ->whereYear('created_at', $year)
                ->where('moa_number', 'LIKE', 'GDPS/UB/BAK-%')
                ->orderBy('id', 'desc')
                ->first();

            $sequence = 1;
            if ($latest && preg_match('/BAK-(\d+)\//', $latest->moa_number, $matches)) {
                $sequence = ((int) $matches[1]) + 1;
            }

            $minutesOfAgreement->moa_number = sprintf('GDPS/UB/BAK-%03d/%s', $sequence, $shortYear);
        }
    }

    /**
     * Handle the MinutesOfAgreement "created" event.
     */
    public function created(MinutesOfAgreement $minutesOfAgreement): void
    {
        if ($minutesOfAgreement->lead) {
            $minutesOfAgreement->lead->update([
                'status' => \Modules\CRM\Enums\LeadStatus::Negotiation,
            ]);
        }
    }

    /**
     * Handle the MinutesOfAgreement "updated" event.
     */
    public function updated(MinutesOfAgreement $minutesOfAgreement): void
    {
        if ($minutesOfAgreement->wasChanged('status') && $minutesOfAgreement->status === \Modules\CRM\Enums\MoAStatus::Approved) {
            if ($minutesOfAgreement->lead) {
                // Potential logic for next stage if needed
            }
        }

        if ($minutesOfAgreement->wasChanged('status') && $minutesOfAgreement->status === \Modules\CRM\Enums\MoAStatus::Submitted) {
            app(SignatureService::class)->notifyNextApprovers($minutesOfAgreement);
        }
    }
}
