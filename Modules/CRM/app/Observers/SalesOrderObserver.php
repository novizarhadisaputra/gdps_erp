<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Models\SalesOrder;

class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "creating" event.
     */
    public function creating(SalesOrder $salesOrder): void
    {
        if (filled($salesOrder->number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = SalesOrder::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $salesOrder->year = (int) $year;
        $salesOrder->sequence_number = $sequence;
        $salesOrder->number = sprintf('GDPS/UB/SO-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the SalesOrder "saving" event.
     */
    public function saving(SalesOrder $salesOrder): void
    {
        if (empty($salesOrder->snapshot) && $salesOrder->profitabilityAnalysis) {
            $analysis = $salesOrder->profitabilityAnalysis;

            $manpower = $analysis->manpower_requirements ?? [];
            $financials = $analysis->financial_assumptions ?? [];
            $mfRate = (float) ($salesOrder->management_fee_percentage ?? 0);

            $standardizedManpower = collect($manpower)->map(fn ($item) => [
                'type' => 'manpower',
                'name' => $item['job_position_name'] ?? 'Personnel',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_cost' => (float) ($item['unit_cost'] ?? 0),
                'unit_price' => round((float) ($item['unit_cost'] ?? 0) * (1 + ($mfRate / 100))),
                'total_price' => round((float) ($item['unit_cost'] ?? 0) * (1 + ($mfRate / 100)) * (int) ($item['quantity'] ?? 1)),
                'meta' => $item,
            ])->toArray();

            $opItems = $financials['operational_costs'] ?? [];
            $standardizedOperational = collect($opItems)->map(fn ($item) => [
                'type' => 'operational',
                'name' => $item['item_name'] ?? 'Item',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_cost' => (float) ($item['unit_cost'] ?? 0),
                'unit_price' => round((float) ($item['unit_cost'] ?? 0) * (1 + ($mfRate / 100))),
                'total_price' => round((float) ($item['unit_cost'] ?? 0) * (1 + ($mfRate / 100)) * (int) ($item['quantity'] ?? 1)),
                'meta' => $item,
            ])->toArray();

            $salesOrder->snapshot = [
                'meta' => [
                    'pa_number' => $analysis->number,
                    'revision' => $analysis->revision_number,
                    'generated_at' => now()->toDateTimeString(),
                    'management_fee_rate' => $mfRate,
                ],
                'groups' => [
                    'manpower' => $standardizedManpower,
                    'operational' => $standardizedOperational,
                ],
                'summary' => [
                    'total_cost' => collect($standardizedManpower)->sum(fn ($i) => $i['unit_cost'] * $i['quantity']) + collect($standardizedOperational)->sum(fn ($i) => $i['unit_cost'] * $i['quantity']),
                    'total_price' => collect($standardizedManpower)->sum('total_price') + collect($standardizedOperational)->sum('total_price'),
                ],
            ];
        }
    }

    /**
     * Handle the SalesOrder "updated" event.
     */
    public function updated(SalesOrder $salesOrder): void
    {
        // Automation removed: BAPP generation is now handled manually
        // via the "Generate BAPP" button in the Sales Order details page.
    }

    /**
     * Handle the SalesOrder "saved" event.
     */
    public function saved(SalesOrder $salesOrder): void
    {
        // Automation: If Signed SO document is uploaded, flip status to Approved
        if ($salesOrder->hasMedia('signed_so') && in_array($salesOrder->status, [SalesOrderStatus::Draft, SalesOrderStatus::Submitted])) {
            $salesOrder->updateQuietly([
                'status' => SalesOrderStatus::Approved,
            ]);
        }

        // Automation: For Internal SO, if Draft SO (Internal Memo) is uploaded, flip status to Approved immediately
        if ($salesOrder->type === SalesOrderType::Internal && $salesOrder->hasMedia('draft_so') && $salesOrder->status === SalesOrderStatus::Draft) {
            $salesOrder->updateQuietly([
                'status' => SalesOrderStatus::Approved,
            ]);
        }

        // Sync SO Number to SalesPlan for tracking
        if ($salesOrder->proposal && $salesOrder->proposal->lead && $salesOrder->proposal->lead->salesPlan) {
            $salesOrder->proposal->lead->salesPlan->updateQuietly([
                'so_number' => $salesOrder->number,
            ]);
        }
    }
}
