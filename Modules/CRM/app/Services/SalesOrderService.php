<?php

namespace Modules\CRM\Services;

use Exception;
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
            $latestAmendment = $existing->amendments()->orderBy('number', 'desc')->first();
            $currentRevision = $latestAmendment ? $latestAmendment->after_snapshot['pa_revision_number'] ?? 0 : ($existing->content_config['pa_revision_number'] ?? 0);

            if (($analysis->revision_number ?? 0) > $currentRevision) {
                return $this->createAmendment($existing, $analysis);
            }

            // If it exists and no amendment needed, we'll continue to updateOrCreate which will just refresh the data
        }

        // 4. Prepare Snapshot Data (content_config)
        $extracted = $this->extractDataFromAnalysis($analysis);
        $items = $extracted['items'];
        $manpowerDetails = $extracted['manpower_details'];
        $totalSoAmount = $extracted['total_amount'];

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
            'number' => $soNumber,
            'order_date' => now(),
            'customer_id' => $analysis->customer_id,
            'type' => SalesOrderType::Internal,
            'status' => SalesOrderStatus::Draft,
            'amount' => $totalSoAmount,
            'management_fee_percentage' => $analysis->management_fee_rate,
            'tax_percentage' => $analysis->tax?->rate ?? 11,
            'sales_pic_id' => $project->ams_id ?? $analysis->lead?->ams_id,
            'project_manager_id' => $project->oprep_id ?? $analysis->lead?->oprep_id,
            'service_type' => $analysis->productCluster?->name,
            'job_location' => $analysis->projectArea?->name,
            'manpower_initial_qty' => collect($manpowerDetails)->sum('quantity'),
            'manpower_composition' => $manpowerDetails,
            'sequence_number' => $sequence,
            'year' => $year,
            'content_config' => [
                'items' => $items,
                'manpower_details' => $manpowerDetails,
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

            $so->forceFill($values);
            $so->save();

            return $so;
        }

        $so = new SalesOrder;
        $so->forceFill(array_merge($attributes, $values));
        $so->save();

        return $so;
    }

    /**
     * Create a Sales Order Amendment automatically.
     */
    public function createAmendment(SalesOrder $so, ProfitabilityAnalysis $analysis): SalesOrderAmendment
    {
        $beforeSnapshot = $so->content_config;

        $extracted = $this->extractDataFromAnalysis($analysis);
        $afterSnapshot = [
            'items' => $extracted['items'],
            'manpower_details' => $extracted['manpower_details'],
            'payment_terms' => $analysis->paymentTerm?->name,
            'pa_revision_number' => $analysis->revision_number ?? 0,
        ];

        $latest = $so->amendments()->orderBy('number', 'desc')->first();
        $nextNumber = ($latest?->number ?? 0) + 1;

        return SalesOrderAmendment::create([
            'sales_order_id' => $so->id,
            'number' => $nextNumber,
            'amendment_date' => now(),
            'reason' => "Automatic amendment based on PA Revision #{$analysis->revision_number}",
            'status' => SalesOrderAmendmentStatus::Draft,
            'before_snapshot' => $beforeSnapshot,
            'after_snapshot' => $afterSnapshot,
            'content_config' => $afterSnapshot,
        ]);
    }

    /**
     * Extract structured data from PA, handling both manual and standard costing modes.
     */
    protected function extractDataFromAnalysis(ProfitabilityAnalysis $analysis): array
    {
        $mfRate = (float) ($analysis->management_fee_rate ?? 0);
        $calculateRevenue = function ($cost) use ($mfRate) {
            // Apply standard markup formula: Revenue = Cost * (1 + Rate%)
            return (float) round($cost * (1 + ($mfRate / 100)), 0);
        };

        // Leverage model accessors which now handle both manual and template-based costing
        $manpowerRaw = $analysis->manpower_requirements ?? [];
        $financialsRaw = $analysis->financial_assumptions['operational_costs'] ?? [];

        $manpower = collect($manpowerRaw)->map(function ($mp) use ($calculateRevenue) {
            $qty = (float) ($mp['quantity'] ?? 1);
            $unitCost = (float) ($mp['unit_cost'] ?? 0);
            $unitPrice = $calculateRevenue($unitCost);

            return [
                'job_position_id' => $mp['job_position_id'] ?? null,
                'job_position_name' => $mp['job_position_name'] ?? 'Personnel',
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $qty,
                'uom' => $mp['uom'] ?? 'Person',
            ];
        })->toArray();

        $items = collect($financialsRaw)->map(function ($item) use ($calculateRevenue) {
            $qty = (float) ($item['quantity'] ?? 1);
            $unitCost = (float) ($item['unit_cost'] ?? 0);
            $unitPrice = $calculateRevenue($unitCost);

            return [
                'description' => $item['item_name'] ?? 'Operational Item',
                'uom' => $item['uom'] ?? 'Unit',
                'quantity' => $qty,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $qty,
            ];
        })->toArray();

        return [
            'items' => $items,
            'manpower_details' => $manpower,
            'total_amount' => (float) (collect($items)->sum('total_price') + collect($manpower)->sum('total_price')),
        ];
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

        throw new Exception("Cannot create Sales Order: No Proposal found for PA {$analysis->number}. A Proposal must be created before a Sales Order can be generated.");
    }
}
