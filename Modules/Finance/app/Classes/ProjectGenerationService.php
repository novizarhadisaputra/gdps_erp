<?php

namespace Modules\Finance\Classes;

use Illuminate\Support\Facades\DB;
use Modules\CRM\Models\Contract;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Models\Project;

class ProjectGenerationService
{
    public function generateFromPA(ProfitabilityAnalysis $pa): Project
    {
        return DB::transaction(function () use ($pa) {
            // Get active contract if available
            $contract = $pa->lead?->contracts()->where('status', 'active')->first();

            // 1. Calculate next sequence number for this Customer + Work Scheme
            $nextNumber = $this->getNextSequenceNumber($pa, $contract);

            // 2. Get Project Type from Lead
            $projectTypeId = $pa->lead?->project_type_id;

            // 3. Create or Update the Project
            $projectData = [
                'name' => $pa->proposal?->proposal_number ?? 'Project for '.$pa->customer?->name,
                'customer_id' => $pa->customer_id,
                'work_scheme_id' => $pa->work_scheme_id,
                'product_cluster_id' => $pa->product_cluster_id,
                'tax_id' => $pa->tax_id,
                'project_area_id' => $pa->project_area_id,
                'project_type_id' => $projectTypeId,
                'project_number' => $nextNumber,
                'status' => 'planning',
                'proposal_id' => $pa->proposal_id,
                'profitability_analysis_id' => $pa->id,
                'lead_id' => $pa->lead_id,
                'contract_id' => $contract?->id,
                'oprep_id' => $pa->lead?->oprep_id,
                'ams_id' => $pa->lead?->ams_id,
                'payment_term_id' => $pa->lead?->payment_term_id,
                'billing_option_id' => $pa->lead?->billing_option_id,
                'start_date' => $pa->lead?->start_date,
                'end_date' => $pa->lead?->end_date,
            ];

            // PIVOT SHIFT: Lookup by contract_id if available, otherwise fallback to lead_id or PA
            $lookup = [];
            if ($contract?->id) {
                $lookup = ['contract_id' => $contract->id];
            } elseif ($pa->lead_id) {
                $lookup = ['lead_id' => $pa->lead_id];
            } else {
                $lookup = ['profitability_analysis_id' => $pa->id];
            }

            $project = Project::updateOrCreate(
                $lookup,
                $projectData
            );

            // 4. Populate Project Information from PA
            $details = $pa->analysis_details;
            if (empty($details) && $pa->items()->exists()) {
                $details = $pa->items->map(fn ($item) => [
                    'name' => $item->costable->name ?? 'Unknown',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_cost_price,
                    'total_price' => $item->total_monthly_cost,
                ])->toArray();
            }

            $project->information->update([
                'revenue_per_month' => $pa->revenue_per_month,
                'direct_cost' => $pa->direct_cost,
                'management_fee_per_month' => $pa->management_fee,
                'analysis_details' => $details,
            ]);

            // 5. Update PA status
            $pa->update([
                'status' => 'converted',
                'project_number' => $nextNumber,
            ]);

            // Force code update if parameters changed
            $project->code = Project::generateProjectCode($project);
            $project->save();

            return $project;
        });
    }

    protected function getNextSequenceNumber(ProfitabilityAnalysis $pa, ?Contract $contract = null): int
    {
        // 1. Reuse project number from existing Project tied to this contract/lead
        $existingProject = null;
        if ($contract?->id) {
            $existingProject = Project::where('contract_id', $contract->id)->first();
        } elseif ($pa->lead_id) {
            $existingProject = Project::where('lead_id', $pa->lead_id)->first();
        }

        if ($existingProject && ! empty($existingProject->project_number)) {
            return (int) $existingProject->project_number;
        }

        // 2. Reuse project number from PA if already assigned
        if (! empty($pa->project_number)) {
            return (int) $pa->project_number;
        }

        // 3. Fallback: Calculate next sequence number for this Customer + Work Scheme
        $lastProject = Project::where('customer_id', '=', $pa->customer_id)
            ->where('work_scheme_id', '=', $pa->work_scheme_id)
            ->orderByRaw('CAST(project_number AS INTEGER) DESC')
            ->first();

        return $lastProject ? (int) $lastProject->project_number + 1 : 1;
    }
}
