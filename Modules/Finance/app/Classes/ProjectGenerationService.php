<?php

namespace Modules\Finance\Classes;

use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\Project;

class ProjectGenerationService
{
    public function generateFromPA(ProfitabilityAnalysis $pa): Project
    {
        return DB::transaction(function () use ($pa) {
            // 1. Calculate next sequence number for this Customer + Work Scheme
            $nextNumber = $this->getNextSequenceNumber($pa->customer_id, $pa->work_scheme_id);

            // 2. Map Project Type from Work Scheme
            $projectTypeId = $this->mapProjectType($pa->work_scheme_id);

            // 3. Create the Project
            $project = Project::create([
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
            ]);

            // 4. Populate Project Information from PA
            // 4. Populate Project Information from PA
            $details = $pa->analysis_details;
            if (empty($details) && $pa->items()->exists()) {
                $details = $pa->items->map(fn ($item) => [
                    'name' => $item->item->name ?? 'Unknown',
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

            return $project;
        });
    }

    protected function mapProjectType(int $workSchemeId): ?int
    {
        $workScheme = WorkScheme::find($workSchemeId);
        if (! $workScheme) {
            return null;
        }

        $projectTypeCode = match ($workScheme->code) {
            '01' => 'HC',  // TAD/Headcount -> Headcount
            '02' => 'BRG', // Borongan -> Borongan
            default => 'OTH',
        };

        return ProjectType::where('code', $projectTypeCode)->first()?->id;
    }

    protected function getNextSequenceNumber(int $customerId, int $workSchemeId): int
    {
        $lastProject = Project::where('customer_id', '=', $customerId)
            ->orderByRaw('CAST(project_number AS INTEGER) DESC')
            ->first();

        return $lastProject ? (int) $lastProject->project_number + 1 : 1;
    }
}
