<?php

namespace Modules\Project\Observers;

use App\Services\AnalyticsCacheService;
use Modules\Project\Enums\ProjectInformationStatus;
use Modules\Project\Models\Project;

class ProjectObserver
{
    public function __construct(protected AnalyticsCacheService $cache) {}

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
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->cache->flushProject();

        $project->information()->create([
            'status' => ProjectInformationStatus::Planning,
        ]);

        if ($project->lead) {
            $project->lead->update([
                'status' => \Modules\CRM\Enums\LeadStatus::Won,
            ]);
        }
    }

    /**
     * Handle the Project "updating" event.
     */
    public function updating(Project $project): void
    {
        // Regenerate code if any segment parameters changed
        $segments = [
            'customer_id',
            'project_area_id',
            'product_cluster_id',
            'tax_id',
            'project_number',
            'work_scheme_id',
            'project_type_id',
        ];

        if ($project->isDirty($segments)) {
            $project->code = Project::generateProjectCode($project);
        }
    }

    /**
     * Handle the Project "saved" event.
     */
    public function saved(Project $project): void
    {
        $this->cache->flushProject();

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

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        $this->cache->flushProject();
    }
}
