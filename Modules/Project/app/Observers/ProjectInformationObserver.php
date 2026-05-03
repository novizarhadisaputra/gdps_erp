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
            $info->management_fee_per_month = $analysis->management_fee ?? $info->management_fee_per_month;

            if ($analysis->tax?->percentage !== null) {
                $info->ppn_percentage = $analysis->tax->percentage;
            }

            // Sync dates if not set
            $info->start_date = $info->start_date ?? $analysis->start_date;
            $info->end_date = $info->end_date ?? $analysis->end_date;

            // Sync structural fields if not set
            $info->payment_term_id = $info->payment_term_id ?? $analysis->payment_term_id;
            $info->project_type_id = $info->project_type_id ?? $analysis->project_type_id;
            $info->billing_option_id = $info->billing_option_id ?? $analysis->billing_option_id;

            // Inherit cost details
            $info->direct_cost = $analysis->direct_cost ?? 0;
            $info->analysis_details = $analysis->financial_assumptions;
            $info->remuneration_details = $analysis->manpower_requirements;
        }

        // Sync from Lead if available
        if ($project->lead_id) {
            $lead = $project->lead;
            $info->lead_id = $lead->id;

            // Sync structural fields if still not set
            $info->billing_option_id = $info->billing_option_id ?? $lead->billing_option_id;
            $info->payment_term_id = $info->payment_term_id ?? $lead->payment_term_id;
            $info->description = $info->description ?? $lead->description;

            // Sync employees (AMS/Oprep) if they were assigned in Lead
            $info->ams_id = $info->ams_id ?? $lead->ams_id;
            $info->oprep_id = $info->oprep_id ?? $lead->oprep_id;
        }

        // Sync from Source Document if available (fallback)
        if ($project->sourceable_id && ! $info->revenue_per_month) {
            $source = $project->sourceable;
            $info->revenue_per_month = $source->amount ?? $source->proposal?->amount;
            $info->start_date = $info->start_date ?? $source->order_date ?? $source->agreement_date ?? now();
            // We don't have a direct end_date in the new models yet, using start_date + 1 year as a default fallback if needed, or just leave it null.
            $info->end_date = $info->end_date ?? ($info->start_date ? $info->start_date->copy()->addYear() : null);
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
