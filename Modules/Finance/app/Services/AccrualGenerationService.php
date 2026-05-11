<?php

namespace Modules\Finance\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Models\Project;

class AccrualGenerationService
{
    public function __construct(
        protected AccrualMappingService $mappingService
    ) {}

    /**
     * Generate 12-month accrual records from an approved Profitability Analysis.
     */
    public function generateFromPA(ProfitabilityAnalysis $analysis, Project $project): void
    {
        DB::transaction(function () use ($analysis, $project) {
            // 0. Clear existing Draft accruals for this project to avoid duplicates if PA is revised/re-approved
            AccrueRevenue::where('project_id', $project->id)
                ->where('status', AccrueRevenueStatus::Draft)
                ->forceDelete();

            // 1. Get monthlies and sort them chronologically
            // Since 'month' is stored as a string name, we sort in PHP or use a smarter query
            $monthlies = $analysis->monthlies->sort(function ($a, $b) {
                if ($a->year !== $b->year) {
                    return $a->year <=> $b->year;
                }

                try {
                    $mA = Carbon::parse($a->month)->month;
                    $mB = Carbon::parse($b->month)->month;

                    return $mA <=> $mB;
                } catch (\Exception $e) {
                    return 0;
                }
            });

            foreach ($monthlies as $monthly) {
                // Convert month name to integer
                try {
                    $monthInt = Carbon::parse($monthly->month)->month;
                } catch (\Exception $e) {
                    $monthInt = is_numeric($monthly->month) ? (int) $monthly->month : date('n');
                }

                // 2. Create AccrueRevenue record (Draft)
                $accrual = AccrueRevenue::create([
                    'project_id' => $project->id,
                    'customer_id' => $project->customer_id,
                    'project_area_id' => $project->project_area_id,
                    'month' => $monthInt,
                    'year' => $monthly->year,
                    'total_amount_estimated' => $monthly->target_revenue,
                    'total_amount_expense_estimated' => $analysis->direct_cost + $analysis->getTotalIndirectCost(),
                    'total_amount_actual' => 0,
                    'status' => AccrueRevenueStatus::Draft,
                    'description' => "Automated accrual for {$monthly->month} {$monthly->year} based on Sales Plan",
                    'work_period' => Carbon::createFromDate($monthly->year, $monthInt, 1)->startOfMonth(),
                    'accrual_period' => Carbon::createFromDate($monthly->year, $monthInt, 1)->endOfMonth(),
                ]);

                // 3. Prepare items based on PA breakdown
                $this->createAccrualItems($accrual, $analysis, $project, $monthly);
            }
        });
    }

    /**
     * Create granular accrual items based on PA breakdown.
     */
    protected function createAccrualItems(AccrueRevenue $accrual, ProfitabilityAnalysis $analysis, Project $project, $monthly): void
    {
        // Define mapping of category codes to RevenueType codes
        $mappings = [
            'manpower' => [
                'revenue' => (float) $analysis->revenue_per_month,
                'expense' => (float) $analysis->getTotalDirectCostByCategory('manpower'),
            ],
            'material' => [
                'revenue' => 0,
                'expense' => (float) $analysis->getTotalDirectCostByCategory('material'),
            ],
            'other_direct' => [
                'revenue' => 0,
                'expense' => $analysis->direct_cost - $analysis->getTotalDirectCostByCategory('manpower') - $analysis->getTotalDirectCostByCategory('material'),
            ],
            'indirect' => [
                'revenue' => 0,
                'expense' => $analysis->getTotalIndirectCost(),
            ],
            'overtime' => [
                'revenue' => 0,
                'expense' => 0,
            ],
        ];

        // Fetch RevenueType IDs
        $revenueTypes = DB::table(config('database.default') === 'sqlite' ? 'revenue_types' : 'master_data.revenue_types')
            ->whereIn('code', ['manpower', 'overtime', 'material', 'other_direct', 'indirect'])
            ->get()
            ->keyBy('code');

        // 1. Create Revenue Items
        foreach ($mappings as $key => $map) {
            if (isset($map['revenue']) && $map['revenue'] > 0) {
                $accrual->items()->create([
                    'type' => 'revenue',
                    'revenue_type_id' => $revenueTypes->get($key)?->id,
                    'amount_estimated' => $map['revenue'],
                    'amount_expense_estimated' => 0,
                    'description' => 'Planned Revenue for '.str_replace('_', ' ', $key),
                ]);
            }
        }

        // 2. Create Expense Items
        foreach ($mappings as $key => $map) {
            if (isset($map['expense']) && $map['expense'] > 0) {
                $accrual->items()->create([
                    'type' => 'expense',
                    'revenue_type_id' => $revenueTypes->get($key)?->id,
                    'amount_estimated' => 0,
                    'amount_expense_estimated' => 0, // Set to 0 per user preference for draft
                    'description' => 'Planned Expense for '.str_replace('_', ' ', $key),
                ]);
            }
        }

        // 3. Ensure Overtime exists in both (even if 0) if not already created
        if (! $accrual->items()->where('type', 'revenue')->where('revenue_type_id', $revenueTypes->get('overtime')?->id)->exists()) {
            $accrual->items()->create([
                'type' => 'revenue',
                'revenue_type_id' => $revenueTypes->get('overtime')?->id,
                'amount_estimated' => 0,
                'amount_expense_estimated' => 0,
                'description' => 'Overtime Revenue Accrual (Manual Entry)',
            ]);
        }
        if (! $accrual->items()->where('type', 'expense')->where('revenue_type_id', $revenueTypes->get('overtime')?->id)->exists()) {
            $accrual->items()->create([
                'type' => 'expense',
                'revenue_type_id' => $revenueTypes->get('overtime')?->id,
                'amount_estimated' => 0,
                'amount_expense_estimated' => 0,
                'description' => 'Overtime Expense Accrual (Manual Entry)',
            ]);
        }
    }
}
