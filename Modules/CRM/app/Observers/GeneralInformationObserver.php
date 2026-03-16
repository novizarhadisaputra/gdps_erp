<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Enums\LeadStatus;
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
        $info->syncContactsToCustomer();

        // Update Lead Status to Approach
        if ($info->lead_id && $info->lead) {
            $info->lead->update([
                'status' => LeadStatus::Approach,
            ]);
        }
    }

    /**
     * Handle the GeneralInformation "updated" event.
     */
    public function updated(GeneralInformation $info): void
    {
        $info->syncContactsToCustomer();

        // Trigger ProjectReview creation when submitted
        if ($info->wasChanged('status') && $info->status === GeneralInformationStatus::Submitted && $info->lead_id) {
            $info->lead->projectReviews()->firstOrCreate([
                'general_information_id' => $info->id,
            ], [
                'status' => 'draft',
            ]);
        }

        // Sync CostingTemplate and ManpowerTemplate description
        if ($info->wasChanged(['scope_of_work', 'project_area_id', 'status']) && $info->status === GeneralInformationStatus::Approved) {
            $lead = $info->lead;
            if ($lead) {
                if ($info->wasChanged(['scope_of_work', 'status'])) {
                    $lead->costingTemplates()->update(['description' => $info->scope_of_work]);
                    $lead->manpowerTemplates()->update(['description' => $info->scope_of_work]);
                }

                if ($info->wasChanged(['project_area_id', 'status'])) {
                    // CostingTemplate does not have project_area_id
                    $lead->manpowerTemplates()->update(['project_area_id' => $info->project_area_id]);
                }
            }
        }
    }

    /**
     * Handle the GeneralInformation "deleting" event.
     */
    public function deleting(GeneralInformation $info): void
    {
        // Cascade delete PICs
        $info->pics()->delete();
    }
}
