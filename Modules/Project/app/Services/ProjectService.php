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
        if (!$lead) {
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

        if (!$signedProposal) {
            return null;
        }

        // Find the associated PA
        $analysis = $signedProposal->profitabilityAnalysis;

        // If no PA linked to proposal, maybe it's the one that triggered this (if $source is PA)
        if (!$analysis && $source instanceof ProfitabilityAnalysis) {
            $analysis = $source;
        }

        if (!$analysis || !$analysis->isFullyApproved()) {
            return null;
        }

        // Both ready! Create Project.
        return Project::create([
            'lead_id' => $lead->id,
            'proposal_id' => $signedProposal->id,
            'profitability_analysis_id' => $analysis->id,
            'customer_id' => $lead->customer_id,
            'name' => $lead->name,
            'start_date' => $analysis->start_date ?? now(),
            'end_date' => $analysis->end_date ?? now()->addYear(),
            'status' => ProjectStatus::Planning,
            // Project Code and ProjectInformation will be handled by ProjectObserver
        ]);
    }
}
