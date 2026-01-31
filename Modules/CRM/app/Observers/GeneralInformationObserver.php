<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\GeneralInformation;
use App\Traits\HasAutoNumber;

class GeneralInformationObserver
{
    use HasAutoNumber;

    /**
     * Handle the GeneralInformation "creating" event.
     */
    public function creating(GeneralInformation $info): void
    {
        $this->generateAutoNumber('document_number', 'GI');
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
    }

    /**
     * Handle the GeneralInformation "updated" event.
     */
    public function updated(GeneralInformation $info): void
    {
        // Logic for re-submission if needed
    }
}
