<?php

namespace Modules\Project\Observers;

use Modules\Project\Models\Project;

class ProjectObserver
{
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
