<?php

namespace Modules\Project\Services;

use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Enums\ProjectStatus;
use Modules\Project\Models\Project;

class ProjectService
{
    /**
     * Attempt to create a project based on a Profitability Analysis and related Proposal.
     */
    public function attemptProjectCreation(ProfitabilityAnalysis|Proposal $source): ?Project
    {
        $lead = $source->lead;
        if (! $lead) {
            return null;
        }

        // 1. Try to find a Winning Proposal (signed)
        $proposal = $lead->proposals()
            ->whereHas('media', function ($query) {
                $query->where('collection_name', 'signed_proposal');
            })
            ->first();

        // Fallback: Use the latest proposal if no signed one exists yet (to allow early project creation)
        if (! $proposal) {
            $proposal = $lead->proposals()->latest()->first();
        }

        if (! $proposal) {
            return null;
        }

        // 2. Find the associated PA
        $analysis = $proposal->profitabilityAnalysis;

        // If no PA linked to proposal, maybe it's the one that triggered this (if $source is PA)
        if (! $analysis && $source instanceof ProfitabilityAnalysis) {
            $analysis = $source;
        }

        if (! $analysis || ! $analysis->isFullyApproved()) {
            return null;
        }

        // 3. Resolve Project Type
        $projectTypeId = $analysis->project_type_id;
        if (! $projectTypeId && $analysis->work_scheme_id) {
            $projectTypeId = $analysis->workScheme?->project_type_id;
        }
        $projectTypeId = $projectTypeId ?? $lead->project_type_id;

        // 4. Create or Update Project (Atomic)
        return Project::updateOrCreate(
            ['lead_id' => $lead->id],
            [
                'proposal_id' => $proposal->id,
                'profitability_analysis_id' => $analysis->id,
                'customer_id' => $lead->customer_id,
                'work_scheme_id' => $analysis->work_scheme_id,
                'project_type_id' => $projectTypeId,
                'product_cluster_id' => $analysis->product_cluster_id ?? $lead->product_cluster_id,
                'project_area_id' => $analysis->project_area_id ?? $lead->project_area_id,
                'tax_id' => $analysis->tax_id ?? $lead->tax_id,
                'payment_term_id' => $analysis->payment_term_id ?? $lead->payment_term_id,
                'oprep_id' => $this->getEmployeeIdFromUser($lead->pic_costing_id),
                'ams_id' => $this->getEmployeeIdFromUser($lead->user_id),
                'name' => $lead->title ?? $lead->name ?? $proposal->number ?? 'New Project',
                'start_date' => $analysis->start_date ?? now(),
                'end_date' => $analysis->end_date ?? now()->addYear(),
                'status' => ProjectStatus::Planning,
            ]
        );
    }

    protected function getEmployeeIdFromUser(?string $userId): ?string
    {
        if (! $userId) {
            return null;
        }

        $user = \App\Models\User::find($userId);
        if (! $user || empty($user->email)) {
            return null;
        }

        return \Modules\MasterData\Models\Employee::where('email', $user->email)->first()?->id;
    }
}
