<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Carbon;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Services\SalesOrderService;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityAnalysisRevision;
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Services\ProjectService;

class ProfitabilityAnalysisObserver
{
    /**
     * Handle the ProfitabilityAnalysis "saving" event.
     */
    public function saving(ProfitabilityAnalysis $analysis): void
    {
        // Inject high-level calculation fields into analysis_details so that when the
        // JSON snapshot is passed to Proposals and Invoices, the calculation context is retained.
        $details = $analysis->analysis_details ?? [];
        $details['calculation_context'] = [
            'revenue_per_month' => (float) $analysis->revenue_per_month,
            'direct_cost' => (float) $analysis->direct_cost,
            'gross_profit' => (float) ($analysis->revenue_per_month - $analysis->direct_cost),
            'ebitda' => (float) $analysis->ebitda,
            'ebit' => (float) $analysis->ebit,
            'ebt' => (float) $analysis->ebt,
            'net_profit' => (float) $analysis->net_profit,
            'net_profit_margin' => (float) $analysis->net_profit_margin,
            'margin_percentage' => (float) $analysis->margin_percentage,
            'management_fee_rate' => (float) $analysis->management_fee_rate,
            'tax_rate' => (float) $analysis->tax_rate,
            'duration' => (int) $analysis->duration,
            'interest_rate' => (float) $analysis->interest_rate,
            'total_project_revenue' => (float) ($analysis->revenue_per_month * ($analysis->duration ?: 1)),
            'total_project_cost' => (float) (($analysis->direct_cost + ($analysis->avg_monthly_indirect_cost ?? 0)) * ($analysis->duration ?: 1)),
            'project_name' => $analysis->generalInformation?->project_name ?? $analysis->lead?->project_name,
            'document_number' => $analysis->number,
        ];
        $analysis->analysis_details = $details;
    }

    /**
     * Handle the ProfitabilityAnalysis "creating" event.
     */
    public function creating(ProfitabilityAnalysis $analysis): void
    {
        $year = date('Y');
        $shortYear = date('y');

        $latest = ProfitabilityAnalysis::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $analysis->year = $year;
        $analysis->sequence_number = $sequence;
        // PA = Profitability Analysis
        $analysis->number = sprintf('GDPS/UB/PA-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the ProfitabilityAnalysis "created" event.
     */
    public function created(ProfitabilityAnalysis $analysis): void
    {
        // When PA is created, Lead moves to Approach stage if not already further
        if ($analysis->lead && $analysis->lead->status->weight() < LeadStatus::Approach->weight()) {
            $analysis->lead->update([
                'status' => LeadStatus::Approach,
            ]);
        }

        // Trigger ProjectReview update
        if ($analysis->lead && $analysis->lead->latestProjectReview) {
            $analysis->lead->latestProjectReview->touch();
        }

        // Auto-copy media from General Information if present
        if ($analysis->generalInformation) {
            foreach (['tor', 'rfp', 'rfq'] as $collection) {
                // Only copy if PA doesn't have its own media yet
                if (! $analysis->hasMedia($collection)) {
                    $media = $analysis->generalInformation->getFirstMedia($collection);
                    if ($media) {
                        $media->copy($analysis, $collection);
                    }
                }
            }
        }

        // Auto-copy media from selected templates if IDs are present in analysis_details
        $this->syncTemplateMedia($analysis);

        // Auto-generate monthly records from Sales Plan
        $this->generateMonthlyRecords($analysis);
    }

    /**
     * Automatically generate monthly performance records based on Sales Plan distribution.
     */
    protected function generateMonthlyRecords(ProfitabilityAnalysis $analysis): void
    {
        $analysis->loadMissing('lead.salesPlan.monthlyBreakdowns');
        $salesPlan = $analysis->lead?->salesPlan;

        if (! $salesPlan || $salesPlan->monthlyBreakdowns->isEmpty()) {
            return;
        }

        foreach ($salesPlan->monthlyBreakdowns as $breakdown) {
            // Convert month integer (1-12) to month name (e.g., "January")
            $monthName = Carbon::create()->month($breakdown->month)->format('F');

            $analysis->monthlies()->create([
                'year' => $breakdown->year,
                'month' => $monthName,
                'target_revenue' => $breakdown->budget_amount,
                'forecast_revenue' => $breakdown->budget_amount,
                'status' => ProfitabilityAnalysisMonthlyStatus::Draft,
            ]);
        }
    }

    /**
     * Handle the ProfitabilityAnalysis "updated" event.
     */
    public function updated(ProfitabilityAnalysis $analysis): void
    {
        // 1. When Margin is approved, Lead moves to Proposal stage
        if (
            $analysis->isDirty('is_margin_approved') &&
            $analysis->is_margin_approved &&
            $analysis->lead &&
            $analysis->lead->status->weight() < LeadStatus::Proposal->weight()
        ) {
            $analysis->lead->update([
                'status' => LeadStatus::Proposal,
            ]);
        }

        if ($analysis->wasChanged('status') && $analysis->status === ProfitabilityAnalysisStatus::Submitted) {
            app(SignatureService::class)->notifyNextApprovers($analysis);
        }

        // 2. When PA is reset to Draft (due to revision), track revision info and clear signatures
        if ($analysis->wasChanged('status') && $analysis->status === ProfitabilityAnalysisStatus::Draft) {
            // Create Snapshot Revision
            $revision = ProfitabilityAnalysisRevision::create([
                'profitability_analysis_id' => $analysis->id,
                'number' => $analysis->getOriginal('number'),
                'snapshot' => $analysis->getRawOriginal(),
                'reason' => $analysis->revision_reason ?? request()->input('reason'),
                'user_id' => auth()->id(),
                'year' => date('Y'),
                'sequence_number' => $analysis->getOriginal('revision_number') ?? 0,
            ]);

            // Copy Media Snapshots
            foreach (['tor', 'rfp', 'rfq', 'cogs_source', 'manpower_costing_backup', 'operational_costing_backup'] as $collection) {
                $analysis->getMedia($collection)->each(function ($media) use ($revision, $collection) {
                    $media->copy($revision, $collection);
                });
            }

            $analysis->updateQuietly([
                'revision_number' => $analysis->revision_number + 1,
                'previous_code' => $analysis->number,
                'is_margin_approved' => false,
            ]);

            // Clear signatures from PA
            $analysis->signatures()->delete();

            // Downgrade Lead status to Approach (Revision stage)
            if ($analysis->lead) {
                $analysis->lead->update([
                    'status' => LeadStatus::Approach,
                ]);
            }
        }

        // 3. Sync calculations to Sales Plan
        if ($analysis->lead && $analysis->lead->salesPlan) {
            $analysis->lead->salesPlan->updateQuietly([
                'npm_percentage' => $analysis->net_profit_margin,
                'management_fee_percentage' => $analysis->management_fee_rate,
            ]);
        }

        // 4. Bi-directional sync for key fields from PA to Lead
        $this->syncLeadAndSalesPlan($analysis);

        // 5. Sync calculation results to associated Proposal if exists
        // Refinement: Only sync when PA is Approved and Proposal amount is 0
        if (
            $analysis->wasChanged('status') &&
            $analysis->status === ProfitabilityAnalysisStatus::Approved &&
            $analysis->lead
        ) {
            foreach ($analysis->lead->proposals as $proposal) {
                if ((float) $proposal->amount === 0.0) {
                    $proposal->updateQuietly([
                        'amount' => $analysis->revenue_per_month,
                    ]);
                }
            }
        }

        // 5. When PA is Approved, attempt to create a Project and a Draft Sales Order
        if ($analysis->wasChanged('status') && $analysis->status === ProfitabilityAnalysisStatus::Approved) {
            $project = app(ProjectService::class)->attemptProjectCreation($analysis);

            // Ensure the relationship is refreshed and we pass the project to the SO service
            $analysis->loadMissing('project');
            app(SalesOrderService::class)->createDraftFromAnalysis($analysis, $project);
        }

        // Sync template media if IDs in analysis_details changed
        if ($analysis->wasChanged('analysis_details')) {
            $this->syncTemplateMedia($analysis);
        }
    }

    /**
     * Handle the ProfitabilityAnalysis "deleting" event.
     */
    public function deleting(ProfitabilityAnalysis $analysis): void
    {
        // No items relationship to cascade delete anymore
    }

    /**
     * Sync key fields from PA back to Lead and Sales Plan.
     */
    protected function syncLeadAndSalesPlan(ProfitabilityAnalysis $analysis): void
    {
        $lead = $analysis->lead;
        if (! $lead) {
            return;
        }

        $fields = [
            'product_cluster_id',
            'project_area_id',
            'project_type_id',
            'work_scheme_id',
            'tax_id',
            'start_date',
            'end_date',
        ];

        // 1. Sync to Lead
        $leadData = [];
        foreach ($fields as $field) {
            if ($analysis->wasChanged($field)) {
                $leadData[$field] = $analysis->{$field};
            }
        }

        if ($analysis->wasChanged('revenue_per_month')) {
            $leadData['estimated_amount'] = $analysis->revenue_per_month;
        }

        if (! empty($leadData)) {
            $lead->updateQuietly($leadData);
        }

        // 2. Sync to Sales Plan
        $salesPlan = $lead->salesPlan;
        if ($salesPlan) {
            $salesPlanData = [];
            foreach ($fields as $field) {
                if ($analysis->wasChanged($field)) {
                    $salesPlanData[$field] = $analysis->{$field};
                }
            }

            if ($analysis->wasChanged('revenue_per_month')) {
                $salesPlanData['estimated_value'] = $analysis->revenue_per_month;
            }

            if (! empty($salesPlanData)) {
                $salesPlan->updateQuietly($salesPlanData);
            }
        }
    }

    /**
     * Synchronize media from Manpower and Costing templates to the Analysis record.
     */
    protected function syncTemplateMedia(ProfitabilityAnalysis $analysis): void
    {
        $details = $analysis->analysis_details;
        if (! $details) {
            return;
        }

        // 1. Manpower Template Media
        $manpowerId = $details['manpower_template_id'] ?? null;
        if ($manpowerId) {
            $template = ManpowerTemplate::find($manpowerId);
            if ($template) {
                // Copy the first media (usually the main costing file)
                $media = $template->getFirstMedia('source_file');
                if ($media) {
                    // Only copy if it's different or doesn't exist to avoid duplicates
                    $existing = $analysis->getFirstMedia('manpower_costing_backup');
                    if (! $existing || $existing->file_name !== $media->file_name) {
                        $analysis->clearMediaCollection('manpower_costing_backup');
                        $media->copy($analysis, 'manpower_costing_backup');
                    }
                }
            }
        }

        // 2. Costing Template Media
        $costingId = $details['costing_template_id'] ?? null;
        if ($costingId) {
            $template = CostingTemplate::find($costingId);
            if ($template) {
                $media = $template->getFirstMedia('source_file');
                if ($media) {
                    $existing = $analysis->getFirstMedia('operational_costing_backup');
                    if (! $existing || $existing->file_name !== $media->file_name) {
                        $analysis->clearMediaCollection('operational_costing_backup');
                        $media->copy($analysis, 'operational_costing_backup');
                    }
                }
            }
        }
    }
}
