<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\GeneralInformation;

// Duplicate import removed

class GeneralInformationObserver
{
    /**
     * Handle the GeneralInformation "creating" event.
     */
    public function creating(GeneralInformation $info): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = GeneralInformation::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $info->year = $year;
        $info->sequence_number = $sequence;
        $info->document_number = sprintf('GDPS/UB/GI-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the GeneralInformation "created" event.
     */
    public function created(GeneralInformation $info): void
    {
        $response = app(\Modules\Project\Services\RiskRegisterService::class)->uploadGeneralInfo($info);

        if (isset($response['external_rr_id'])) {
            $info->updateQuietly([
                'rr_submission_id' => $response['external_rr_id'],
                'status' => 'submitted',
            ]);
        }

        // Update Lead Status to Approach
        if ($info->lead_id && $info->lead) {
            $info->lead->update([
                'status' => \Modules\CRM\Enums\LeadStatus::Approach,
            ]);
        }
    }

    /**
     * Handle the GeneralInformation "updated" event.
     */
    public function updated(GeneralInformation $info): void
    {
        // Logic for re-submission if needed
    }
}
