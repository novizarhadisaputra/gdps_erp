<?php

namespace Modules\Project\Observers;

use Modules\Project\Models\Project;
use Modules\Project\Enums\ProjectInformationStatus;

class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     */
    public function creating(Project $project): void
    {
        if (empty($project->code)) {
            $project->code = Project::generateProjectCode($project);
        }
    }

    /**
     * Handle the Project \"created\" event.
     */
    public function created(Project $project): void
    {
        $project->information()->create([
            'status' => ProjectInformationStatus::Planning,
        ]);
    }

    /**
     * Handle the Project "saved" event.
     */
    public function saved(Project $project): void
    {
        if ($project->lead && $project->lead->salesPlan) {
            $project->lead->salesPlan->update([
                'project_code' => $project->code,
            ]);

            // Sync to monthly as well
            $project->lead->salesPlan->monthlyBreakdowns()->update([
                'project_code' => $project->code,
            ]);
        }
    }
}
