<?php

namespace Modules\CRM\Services;

use Illuminate\Support\Facades\Log;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\SalesOrderAmendment;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Models\Project;

class SalesOrderService
{
    /**
     * Create a Draft Sales Order automatically from an approved PA.
     */
    public function createDraftFromAnalysis(ProfitabilityAnalysis $analysis, ?Project $project = null): SalesOrder|SalesOrderAmendment|null
    {
        // 1. Determine Project
        $project = $project ?? $analysis->project;
        if (! $project) {
            // Last resort: Try to find by lead_id
            $project = Project::where('lead_id', $analysis->lead_id)->first();

            if (! $project) {
                return null;
            }
        }

        // 2. Resolve Proposal ID
        $proposalId = $this->resolveProposalId($analysis);

        // 3. Resolve Amendment Logic
        // We still check if an amendment is needed if the PA revision has increased
        $existing = SalesOrder::where('proposal_id', $proposalId)
            ->where('project_id', $project->id)
            ->first();

        if ($existing) {
            $latestAmendment = $existing->amendments()->orderBy('amendment_number', 'desc')->first();
            $currentRevision = $latestAmendment ? $latestAmendment->after_snapshot['pa_revision_number'] ?? 0 : ($existing->content_config['pa_revision_number'] ?? 0);

            if (($analysis->revision_number ?? 0) > $currentRevision) {
                return $this->createAmendment($existing, $analysis);
            }

            // If it exists and no amendment needed, we'll continue to updateOrCreate which will just refresh the data
        }

        // 4. Prepare Snapshot Data (content_config)
        $manpower = $analysis->manpower_requirements;
        $financials = $analysis->financial_assumptions;

        // Map PA items to SO Table items
        $items = collect($financials['operational_costs'] ?? [])->map(fn ($item) => [
            'description' => $item['item_name'],
            'uom' => 'Unit', // Default or from cat
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_cost'],
            'total_price' => $item['total_monthly_cost'],
        ])->toArray();

        // 5. Generate SO Number
        $year = date('Y');
        $shortYear = date('y');
        $latest = SalesOrder::where('year', $year)->orderBy('sequence_number', 'desc')->first();
        $sequence = $latest ? $latest->sequence_number + 1 : 1;
        $soNumber = sprintf('GDPS/UB/SO-%03d/%s', $sequence, $shortYear);

        // 6. Create or Update the Sales Order (Atomic approach with SoftDeletes support)
        Log::info('Attempting to create/update Sales Order from PA', [
            'pa_id' => $analysis->id,
            'proposal_id' => $proposalId,
            'project_id' => $project->id,
            'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'] ?? 'unknown',
        ]);

        $attributes = [
            'project_id' => $project->id,
            'proposal_id' => $proposalId,
        ];

        $values = [
            'so_number' => $soNumber,
            'order_date' => now(),
            'customer_id' => $analysis->customer_id,
            'type' => SalesOrderType::Internal,
            'status' => SalesOrderStatus::Draft,
            'amount' => $analysis->revenue_per_month,
            'management_fee_percentage' => $analysis->management_fee_rate,
            'tax_percentage' => $analysis->tax_rate ?? 11,
            'sales_pic_id' => $project->ams_id ?? $analysis->lead?->ams_id,
            'project_manager_id' => $project->oprep_id ?? $analysis->lead?->oprep_id,
            'service_type' => $analysis->productCluster?->name,
            'job_location' => $analysis->projectArea?->name,
            'manpower_initial_qty' => collect($manpower)->sum('quantity'),
            'manpower_composition' => $manpower,
            'sequence_number' => $sequence,
            'year' => $year,
            'content_config' => [
                'items' => $items,
                'manpower_details' => $manpower,
                'payment_terms' => $analysis->paymentTerm?->name,
                'probation_period' => '3 Months',
                'replacement_sla' => '3 Working Days',
                'reporting_schedule' => '5th of each month',
                'pa_revision_number' => $analysis->revision_number ?? 0,
            ],
        ];

        $so = SalesOrder::withTrashed()->where($attributes)->first();

        if ($so) {
            if ($so->trashed()) {
                $so->restore();
            }
            $so->update($values);

            return $so;
        }

        return SalesOrder::create(array_merge($attributes, $values));
    }

    /**
     * Create a Sales Order Amendment automatically.
     */
    public function createAmendment(SalesOrder $so, ProfitabilityAnalysis $analysis): SalesOrderAmendment
    {
        $beforeSnapshot = $so->content_config;

        $manpower = $analysis->manpower_requirements;
        $financials = $analysis->financial_assumptions;
        $items = collect($financials['operational_costs'] ?? [])->map(fn ($item) => [
            'description' => $item['item_name'],
            'uom' => 'Unit',
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_cost'],
            'total_price' => $item['total_monthly_cost'],
        ])->toArray();

        $afterSnapshot = [
            'items' => $items,
            'manpower_details' => $manpower,
            'payment_terms' => $analysis->paymentTerm?->name,
            'pa_revision_number' => $analysis->revision_number ?? 0,
        ];

        $latest = $so->amendments()->orderBy('amendment_number', 'desc')->first();
        $nextNumber = ($latest?->amendment_number ?? 0) + 1;

        return SalesOrderAmendment::create([
            'sales_order_id' => $so->id,
            'amendment_number' => $nextNumber,
            'amendment_date' => now(),
            'reason' => "Automatic amendment based on PA Revision #{$analysis->revision_number}",
            'status' => SalesOrderAmendmentStatus::Draft,
            'before_snapshot' => $beforeSnapshot,
            'after_snapshot' => $afterSnapshot,
            'content_config' => $afterSnapshot,
        ]);
    }

    /**
     * Resolve the appropriate Proposal ID for a given Profitability Analysis.
     */
    protected function resolveProposalId(ProfitabilityAnalysis $analysis): string
    {
        // 1. Check direct link
        if ($analysis->proposal_id) {
            return $analysis->proposal_id;
        }

        // 2. Check for Proposal that references this PA
        $proposalFromPA = Proposal::where('profitability_analysis_id', $analysis->id)->first();
        if ($proposalFromPA) {
            return $proposalFromPA->id;
        }

        // 3. Fallback to the latest proposal for the Lead
        if ($analysis->lead) {
            $latestLeadProposal = $analysis->lead->proposals()->latest()->first();
            if ($latestLeadProposal) {
                return $latestLeadProposal->id;
            }
        }

        throw new \Exception("Cannot create Sales Order: No Proposal found for PA {$analysis->document_number}. A Proposal must be created before a Sales Order can be generated.");
    }
}
