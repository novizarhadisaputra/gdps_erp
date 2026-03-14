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

        // Sync from Profitability Analysis if available
        if ($project->profitability_analysis_id) {
            $analysis = $project->profitabilityAnalysis;
            $info->revenue_per_month = $analysis->revenue_per_month ?? $info->revenue_per_month;
            $info->management_fee_per_month = $analysis->management_fee_amount ?? $info->management_fee_per_month;
            
            if ($analysis->tax?->percentage !== null) {
                $info->ppn_percentage = $analysis->tax->percentage;
            }
            
            // Sync dates if not set
            $info->start_date = $info->start_date ?? $analysis->start_date;
            $info->end_date = $info->end_date ?? $analysis->end_date;

            // Inherit cost details
            $info->direct_cost = $analysis->total_monthly_cost ?? 0;
            $info->analysis_details = $analysis->financial_assumptions;
            $info->remuneration_details = $analysis->manpower_requirements;
        }

        // Sync from Lead if available
        if ($project->lead_id) {
            $lead = $project->lead;
            $info->lead_id = $lead->id;
            
            // Sync employees (AMS/Oprep) if they were assigned in Lead
            $info->ams_id = $info->ams_id ?? $lead->ams_id;
            $info->oprep_id = $info->oprep_id ?? $lead->oprep_id;
        }

        // Sync from Contract if available (fallback)
        if ($project->contract_id && !$info->revenue_per_month) {
            $contract = $project->contract;
            $info->revenue_per_month = $contract->proposal?->amount;
            $info->start_date = $info->start_date ?? now();
            $info->end_date = $info->end_date ?? $contract->expiry_date;
        }

        // Sync PIC from Customer
        if ($project->customer_id) {
            $customer = $project->customer;
            // $info->pic_customer_name = $info->pic_customer_name ?? $customer->name;
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
