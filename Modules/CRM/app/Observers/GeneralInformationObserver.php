<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\GeneralInformation;

class GeneralInformationObserver
{
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
    }

    /**
     * Handle the GeneralInformation "updated" event.
     */
    public function updated(GeneralInformation $info): void
    {
        // Logic for re-submission if needed
    }
}
