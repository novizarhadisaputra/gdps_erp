<?php

namespace Modules\CRM\Observers;

use App\Services\AnalyticsCacheService;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Enums\ConfidenceLevel;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Models\ProjectInformation;

class LeadObserver
{
    public function __construct(protected AnalyticsCacheService $cache) {}

    /**
     * Handle the Lead "created" event.
     */
    public function created(Lead $lead): void
    {
        $this->cache->flushCRM();
        $this->handleStatusTransitions($lead);
    }

    /**
     * Handle the Lead "creating" event.
     */
    public function creating(Lead $lead): void
    {
        $lead->status = $lead->status ?? LeadStatus::Lead;
        $lead->confidence_level = $lead->confidence_level ?? ConfidenceLevel::Pessimistic;
        $lead->position = Lead::max('position') + 1;

        if (empty($lead->user_id) && auth()->check()) {
            $lead->user_id = auth()->id();
        }
    }

    /**
     * Handle the Lead "updated" event.
     */
    public function updated(Lead $lead): void
    {
        DB::transaction(function () use ($lead) {
            $this->cache->flushCRM();

            // Bi-directional categorization sync to SalesPlan
            /** @var \Modules\CRM\Models\SalesPlan|null $salesPlan */
            $salesPlan = $lead->salesPlan()->first();

            if ($salesPlan) {
                $salesPlan->updateQuietly([
                    'revenue_segment_id' => $lead->revenue_segment_id,
                    'product_cluster_id' => $lead->product_cluster_id,
                    'project_type_id' => $lead->project_type_id,
                    'industrial_sector_id' => $lead->industrial_sector_id,
                    'project_area_id' => $lead->project_area_id,
                    'estimated_value' => $lead->estimated_amount,
                    'confidence_level' => $lead->confidence_level ?? ConfidenceLevel::Moderate,
                    'job_positions' => $lead->job_positions,
                ]);
            }

            // Sync CostingTemplate and ManpowerTemplate generic data
            if ($lead->wasChanged(['customer_id', 'title', 'project_area_id'])) {
                $newName = $lead->customer?->name ?? $lead->title;

                if ($lead->wasChanged(['customer_id', 'title'])) {
                    $lead->costingTemplates()->update(['name' => $newName]);
                    $lead->manpowerTemplates()->update(['name' => $newName]);
                }

                if ($lead->wasChanged('project_area_id')) {
                    // ManpowerTemplate has project_area_id, CostingTemplate does not.
                    $lead->manpowerTemplates()->update(['project_area_id' => $lead->project_area_id]);
                }
            }

            if ($lead->wasChanged('status')) {
                $this->handleStatusTransitions($lead);
            }
        });
    }

    protected function handleStatusTransitions(Lead $lead): void
    {
        switch ($lead->status) {
            case LeadStatus::Lead:
                break;

            case LeadStatus::Approach:
                // Auto-create SalesPlan if it doesn't exist
                if (! $lead->salesPlan) {
                    $lead->salesPlan()->create([
                        'revenue_segment_id' => $lead->revenue_segment_id,
                        'product_cluster_id' => $lead->product_cluster_id,
                        'project_type_id' => $lead->project_type_id,
                        'industrial_sector_id' => $lead->industrial_sector_id,
                        'project_area_id' => $lead->project_area_id,
                        'estimated_value' => $lead->estimated_amount ?? 0,
                        'confidence_level' => $lead->confidence_level ?? ConfidenceLevel::Moderate,
                        'job_positions' => $lead->job_positions,
                        'start_date' => $lead->start_date ?? now(),
                        'end_date' => $lead->end_date ?? now()->addYear(),
                    ]);
                }

                activity()
                    ->performedOn($lead)
                    ->log('Lead moved to Approach. Sales Plan draft created/synced.');
                break;

            case LeadStatus::Proposal:
                break;

            case LeadStatus::Negotiation:
                break;

            case LeadStatus::Won:
                // No longer auto-creating a generic contract.
                // User should create PO, SPK, or PKS.
                break;

            case LeadStatus::ClosedLost:
                break;
        }
    }

    /**
     * Handle the Lead "deleted" event.
     */
    public function deleted(Lead $lead): void
    {
        $this->cache->flushCRM();

        // Activity logs are preserved (Spatie LogsActivity trait handles this)
        // We only delete related records as requested.
    }

    /**
     * Handle the Lead "deleting" event.
     */
    public function deleting(Lead $lead): void
    {
        DB::transaction(function () use ($lead) {
            // Cascade delete related records
            $lead->salesPlan()?->delete();
            $lead->proposals()->each(fn (Proposal $p) => $p->delete());
            $lead->generalInformations()->each(fn (GeneralInformation $gi) => $gi->delete());
            $lead->profitabilityAnalyses()->each(fn (ProfitabilityAnalysis $pa) => $pa->delete());
            $lead->purchaseOrders()->each(fn ($po) => $po->delete());
            $lead->workOrders()->each(fn ($wo) => $wo->delete());
            $lead->cooperationAgreements()->each(fn ($ca) => $ca->delete());
            $lead->projectInformations()->each(fn (ProjectInformation $pi) => $pi->delete());
        });
    }
}
