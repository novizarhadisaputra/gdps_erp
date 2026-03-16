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
            'lead_id' => $project->lead_id,
            'status' => ProjectInformationStatus::Planning,
            'start_date' => $project->start_date,
            'end_date' => $project->end_date,
            'payment_term_id' => $project->payment_term_id,
            'project_type_id' => $project->project_type_id,
            'oprep_id' => $project->oprep_id,
            'ams_id' => $project->ams_id,
            'revenue_per_month' => $project->profitabilityAnalysis?->revenue_per_month ?? 0,
            'direct_cost' => $project->profitabilityAnalysis?->direct_cost ?? 0,
            'management_fee_per_month' => $project->profitabilityAnalysis?->management_fee ?? 0,
            'analysis_details' => $project->profitabilityAnalysis?->analysis_details,
        ]);

        if ($project->lead) {
            $project->lead->update([
                'status' => \Modules\CRM\Enums\LeadStatus::Won,
            ]);
        }

        // Auto-create Sales Order
        \Modules\CRM\Models\SalesOrder::create([
            'project_id' => $project->id,
            'proposal_id' => $project->proposal_id,
            'customer_id' => $project->customer_id,
            'order_date' => now(),
            'type' => \Modules\CRM\Enums\SalesOrderType::External,
            'status' => \Modules\CRM\Enums\SalesOrderStatus::Draft,
            'amount' => $project->amount,
            'management_fee_percentage' => $project->profitabilityAnalysis?->management_fee_percentage ?? 0,
            'tax_percentage' => $project->tax?->rate ?? 0,
            'sales_pic_id' => $project->ams_id,
            'project_manager_id' => $project->oprep_id,
            'service_type' => $project->projectType?->name,
            'job_location' => $project->projectArea?->name,
            'payment_terms' => $project->paymentTerm?->name,
        ]);
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
