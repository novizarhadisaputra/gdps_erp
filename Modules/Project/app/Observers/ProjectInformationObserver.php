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
            $info->revenue_per_month = $contract->proposal?->amount;
            $info->start_date = $info->start_date ?? now();
            $info->end_date = $info->end_date ?? $contract->expiry_date;
        }

        // Sync PIC from Customer
        if ($project->customer_id) {
            $customer = $project->customer;
            $info->pic_customer_name = $info->pic_customer_name ?? $customer->name;
        }

        $year = date('Y');
        $shortYear = date('y');
        
        $latest = ProjectInformation::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;
        
        $info->year = $year;
        $info->sequence_number = $sequence;
        // PI = Project Information
        $info->document_number = sprintf('GDPS/UB/PI-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the ProjectInformation "created" event.
     */
    public function created(ProjectInformation $info): void
    {
        // Upload to 3rd party Risk Register
        app(\Modules\Project\Services\RiskRegisterService::class)->uploadProjectInfo($info);
    }

    /**
     * Handle the ProjectInformation "updated" event.
     */
    public function updated(ProjectInformation $info): void
    {
        if ($info->wasChanged(['start_date', 'end_date'])) {
            $info->project()->update([
                'start_date' => $info->start_date,
                'end_date' => $info->end_date,
            ]);
        }
    }

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
