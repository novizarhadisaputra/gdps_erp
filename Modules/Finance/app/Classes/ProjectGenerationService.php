<?php

namespace Modules\Finance\Classes;

class ProjectGenerationService
{
    public function generateFromPA(\Modules\Finance\Models\ProfitabilityAnalysis $pa): \Modules\Project\Models\Project
    {
        return \DB::transaction(function () use ($pa) {
            // 1. Calculate next sequence number for this Client + Work Scheme
            $nextNumber = $this->getNextSequenceNumber($pa->client_id, $pa->work_scheme_id);

            // 2. Create the Project
            $project = \Modules\Project\Models\Project::create([
                'name' => $pa->proposal?->proposal_number ?? 'Project for '.$pa->client?->name,
                'client_id' => $pa->client_id,
                'work_scheme_id' => $pa->work_scheme_id,
                'product_cluster_id' => $pa->product_cluster_id,
                'tax_id' => $pa->tax_id,
                'project_area_id' => $pa->project_area_id,
                'project_number' => $nextNumber,
                'status' => 'planning',
                'proposal_id' => $pa->proposal_id, // We'll add this to Project model too
                'profitability_analysis_id' => $pa->id, // We'll add this to Project model too
            ]);

            // 3. Populate Project Information from PA
            $project->information->update([
                'revenue_per_month' => $pa->revenue_per_month,
                'direct_cost' => $pa->direct_cost,
                'management_fee_per_month' => $pa->management_fee,
                'profitability_analysis' => $pa->manpower_details, // Or mapping logic
            ]);

            // 4. Update PA status
            $pa->update([
                'status' => 'converted',
                'project_number' => $nextNumber,
            ]);

            return $project;
        });
    }

    protected function getNextSequenceNumber(int $clientId, int $workSchemeId): int
    {
        $lastProject = \Modules\Project\Models\Project::where('client_id', $clientId)
            ->orderByRaw('CAST(project_number AS UNSIGNED) DESC')
            ->first();

        return $lastProject ? (int) $lastProject->project_number + 1 : 1;
    }
}
