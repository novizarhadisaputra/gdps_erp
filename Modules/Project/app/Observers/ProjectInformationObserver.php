<?php

namespace Modules\Project\Observers;

use Modules\Project\Models\ProjectInformation;

class ProjectInformationObserver
{
    /**
     * Handle the ProjectInformation "creating" event.
     */
    public function creating(ProjectInformation $info): void
    {
        $project = $info->project;
        if (! $project) {
            return;
        }

        // Sync from Contract if available
        if ($project->contract_id) {
            $contract = $project->contract;
            $info->revenue_per_month = $contract->proposal?->amount; // Mapping amount to revenue as proxy
            $info->start_date = $info->start_date ?? now();
            $info->end_date = $info->end_date ?? $contract->expiry_date;
        }

        // Sync PIC from Client
        if ($project->client_id) {
            $client = $project->client;
            $info->pic_client_name = $info->pic_client_name ?? $client->name;
            // Assuming we might add phone/email to Client later,
            // for now we use client name as a placeholder for PIC
        }
    }

    /**
     * Handle the ProjectInformation "created" event.
     */
    public function updated(ProjectInformation $projectinformation): void {}

    /**
     * Handle the ProjectInformation "deleted" event.
     */
    public function deleted(ProjectInformation $projectinformation): void {}

    /**
     * Handle the ProjectInformation "restored" event.
     */
    public function restored(ProjectInformation $projectinformation): void {}

    /**
     * Handle the ProjectInformation "force deleted" event.
     */
    public function forceDeleted(ProjectInformation $projectinformation): void {}
}
