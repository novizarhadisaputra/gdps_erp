<?php

namespace Modules\Finance\Observers;

use App\Services\AnalyticsCacheService;
use Modules\Finance\Models\ProfitabilityAnalysisWeekly;
use Illuminate\Support\Facades\DB;

class ProfitabilityAnalysisWeeklyObserver
{
    public function saved(ProfitabilityAnalysisWeekly $weekly): void
    {
        $this->syncToMonthly($weekly);
        $this->invalidateCache();
    }

    public function deleted(ProfitabilityAnalysisWeekly $weekly): void
    {
        $this->syncToMonthly($weekly);
        $this->invalidateCache();
    }

    /**
     * Sync weekly data to monthly parent.
     */
    protected function syncToMonthly(ProfitabilityAnalysisWeekly $weekly): void
    {
        $monthly = $weekly->monthly;

        if (! $monthly) {
            return;
        }

        // 1. Calculate Actual Revenue (Sum of Achieved Weekly)
        $totalAchieved = DB::table('profitability_analysis_weeklies')
            ->where('profitability_analysis_monthly_id', $monthly->id)
            ->sum('achieved_revenue');

        // 2. Calculate Forecast Revenue (Latest Projection)
        $latestProjection = DB::table('profitability_analysis_weeklies')
            ->where('profitability_analysis_monthly_id', $monthly->id)
            ->orderByDesc('created_at')
            ->value('projected_revenue');

        $monthly->update([
            'actual_revenue' => $totalAchieved,
            'forecast_revenue' => $latestProjection,
        ]);
    }

    /**
     * Invalidate relevant dashboard caches.
     */
    protected function invalidateCache(): void
    {
        $cache = app(AnalyticsCacheService::class);
        
        $cache->forget('crm.stats_overview');
        $cache->forget('crm.sales_performance_cumulative');
        $cache->forget('crm.lead_pipeline_levels');
        $cache->forget('crm.team_performance');
    }
}
