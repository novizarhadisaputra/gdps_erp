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
        $project->code = $this->generateProjectCode($project);
    }

    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $project->information()->create();
    }

    protected function generateProjectCode(Project $project): string
    {
        $schemeCode = $project->workScheme?->code ?? '00';
        $clusterCode = $project->productCluster?->code ?? 'UNK';
        $taxCode = $project->tax?->code ?? 'P0';
        $clientCode = $project->client?->code ?? 'UNK';
        $areaCode = $project->projectArea?->code ?? 'UNK';
        $projectNumber = str_pad($project->project_number ?? '0001', 4, '0', STR_PAD_LEFT);

        return "{$schemeCode}{$clusterCode}{$taxCode}{$clientCode}{$areaCode}{$projectNumber}";
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void {}

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
