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

        // Check if a Project already exists for this Lead
        $existingProject = Project::where('lead_id', $lead->id)->first();
        if ($existingProject) {
            return $existingProject;
        }

        // Find the Winning Proposal (signed)
        $signedProposal = $lead->proposals()
            ->whereHas('media', function ($query) {
                $query->where('collection_name', 'signed_proposal');
            })
            ->first();

        if (! $signedProposal) {
            return null;
        }

        // Find the associated PA
        $analysis = $signedProposal->profitabilityAnalysis;

        // If no PA linked to proposal, maybe it's the one that triggered this (if $source is PA)
        if (! $analysis && $source instanceof ProfitabilityAnalysis) {
            $analysis = $source;
        }

        if (! $analysis || ! $analysis->isFullyApproved()) {
            return null;
        }

        // Both ready! Create Project.
        $projectTypeId = $analysis->project_type_id;
        if (! $projectTypeId && $analysis->work_scheme_id) {
            $projectTypeId = $analysis->workScheme?->project_type_id;
        }
        $projectTypeId = $projectTypeId ?? $lead->project_type_id;

        return Project::create([
            'lead_id' => $lead->id,
            'proposal_id' => $signedProposal->id,
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
            'name' => $lead->title ?? $lead->name ?? $signedProposal->proposal_number,
            'start_date' => $analysis->start_date ?? now(),
            'end_date' => $analysis->end_date ?? now()->addYear(),
            'status' => ProjectStatus::Planning,
            // Project Code and ProjectInformation will be handled by ProjectObserver
        ]);
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
