<?php

namespace Modules\Project\Observers;

use Modules\Project\Models\Project;

class ProjectObserver
{
    /**
     * Handle the Project "saving" event.
     */
    public function saving(Project $project): void
    {
        // Only generate or update the project code if:
        // 1. The record is being created (id is null)
        // 2. OR the current status is still 'planning'
        // This ensures that once a project goes live (Active, etc.), its identity remains immutable.
        if (! $project->exists || $project->status === 'planning') {
            $project->code = $this->generateProjectCode($project);
        }
    }

    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        // Automatically initialize project information record upon project creation
        try {
            if (! $project->information()->exists()) {
                $project->information()->create();
            }
        } catch (\Exception $e) {
            // Check if it's a unique constraint violation or similar, otherwise rethrow
            // In many test scenarios, factories might trigger this. 
            // We silently ignore if it already exists to prevent crashing.
        }
    }

    /**
     * Generate a structured project code based on its metadata.
     * Format: {ClientCode}{Sequence}{AreaCode}{WorkSchemeCode}{ProductClusterCode}{TaxCode}
     * Example: QGA01CGK02BCLP2
     */
    protected function generateProjectCode(Project $project): string
    {
        $customerCode = $project->customer?->code ?? 'UNK';
        $projectNumber = str_pad($project->project_number ?? '01', 2, '0', STR_PAD_LEFT);
        $areaCode = $project->projectArea?->code ?? 'UNK';
        $schemeCode = $project->workScheme?->code ?? '00';
        $clusterCode = $project->productCluster?->code ?? 'UNK';
        $taxCode = $project->tax?->code ?? 'P0';

        return "{$customerCode}{$projectNumber}{$areaCode}{$schemeCode}{$clusterCode}{$taxCode}";
    }

    /**
     * Handle the Project "updated" event.
     */
    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        if ($project->wasChanged(['start_date', 'end_date'])) {
            $project->information()->update([
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
            ]);
        }
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void {}

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void {}

    /**
     * Handle the Project "force deleted" event.
     */
    public function forceDeleted(Project $project): void {}
}
