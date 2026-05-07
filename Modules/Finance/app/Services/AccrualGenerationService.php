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
            $monthlies = $analysis->monthlies()->orderBy('year')->orderBy('month')->get();

            foreach ($monthlies as $monthly) {
                // Convert month name (e.g. "January") to integer (1)
                try {
                    $monthInt = Carbon::parse($monthly->month)->month;
                } catch (\Exception $e) {
                    // Fallback if month is already an integer or invalid
                    $monthInt = is_numeric($monthly->month) ? (int) $monthly->month : date('n');
                }

                // 1. Create AccrueRevenue record (Draft)
                $accrual = AccrueRevenue::create([
                    'project_id' => $project->id,
                    'customer_id' => $project->customer_id,
                    'project_area_id' => $project->project_area_id,
                    'month' => $monthInt,
                    'year' => $monthly->year,
                    'total_amount_estimated' => $monthly->target_revenue,
                    'total_amount_actual' => 0,
                    'status' => AccrueRevenueStatus::Draft,
                    'description' => "Automated accrual for {$monthly->month} {$monthly->year} based on Sales Plan",
                    'work_period' => Carbon::createFromDate($monthly->year, $monthInt, 1)->startOfMonth(),
                    'accrual_period' => Carbon::createFromDate($monthly->year, $monthInt, 1)->endOfMonth(),
                ]);

                // 2. Resolve account mapping for snapshotting if needed
                // (JournalService will resolve these again at runtime, but we populate COA IDs if possible)
                $revenueMapping = $this->mappingService->resolveAccountMapping(
                    'revenue',
                    $project->projectArea,
                    $project->customer,
                    null, // Default
                    $project->revenue_segment_id
                );

                // 3. Create initial item
                $accrual->items()->create([
                    'amount_estimated' => $monthly->target_revenue,
                    'amount_actual' => 0,
                    'revenue_chart_of_account_id' => $revenueMapping?->chart_of_account_id,
                ]);
            }
        });
    }
}
