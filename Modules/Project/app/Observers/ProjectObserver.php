<?php

namespace Modules\Project\Observers;

use App\Services\AnalyticsCacheService;
use Modules\CRM\Enums\LeadStatus;
use Modules\Finance\Services\AccrualGenerationService;
use Modules\MasterData\Models\Employee;
use Modules\Project\Enums\ProjectInformationStatus;
use Modules\Project\Enums\ProjectMemberRole;
use Modules\Project\Models\Project;

class ProjectObserver
{
    public function __construct(protected AnalyticsCacheService $cache) {}

    /**
     * Handle the Project "creating" event.
     */
    public function creating(Project $project): void
    {
        // 1. Generate final project code
        if (empty($project->number)) {
            $project->number = Project::generateProjectNumber($project);
        }
    }

    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->cache->flushProject();

        // Create initial project information with AMS and Oprep from project
        $project->information()->updateOrCreate(
            ['project_id' => $project->id],
            [
                'status' => ProjectInformationStatus::Planning,
                'ams_id' => $project->ams_id,
                'oprep_id' => $project->oprep_id,
            ]
        );

        // Add AMS as default project member
        if ($project->ams_id) {
            $project->members()->updateOrCreate(
                [
                    'memberable_id' => $project->ams_id,
                    'memberable_type' => Employee::class,
                    'role' => ProjectMemberRole::AMS,
                ],
                ['joined_at' => now()]
            );
        }

        // Add Oprep as default project member
        if ($project->oprep_id) {
            $project->members()->updateOrCreate(
                [
                    'memberable_id' => $project->oprep_id,
                    'memberable_type' => Employee::class,
                    'role' => ProjectMemberRole::Oprep,
                ],
                ['joined_at' => now()]
            );
        }

        if ($project->lead) {
            $project->lead->update([
                'status' => LeadStatus::Won,
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
            $project->number = Project::generateProjectNumber($project);
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
                'project_code' => $project->number,
            ]);

            // Sync to monthly as well
            $project->lead->salesPlan->monthlyBreakdowns()->update([
                'project_code' => $project->number,
            ]);
        }

        // Generate 12-month revenue accruals if linked to a Profitability Analysis
        // We trigger this if it's a new project or if the PA was recently updated
        if ($project->profitability_analysis_id && ($project->wasRecentlyCreated || $project->wasChanged('profitability_analysis_id'))) {
            $analysis = $project->profitabilityAnalysis;
            if ($analysis) {
                app(AccrualGenerationService::class)->generateFromPA($analysis, $project);
            }
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
