<?php

namespace App\Services;

use Carbon\Carbon;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\SalesPlanMonthly;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\RevenueSegment;

class CRMAnalyticsService
{
    /**
     * Get core KPIs for the top cards (Real-time)
     */
    public function getCoreKPIs(): array
    {
        $year = now()->year;

        $stats = SalesPlanMonthly::where('year', $year)
            ->selectRaw('SUM(budget_amount) as target, SUM(actual_amount) as actual')
            ->first();

        $target = (float) ($stats->target ?? 0);
        $actual = (float) ($stats->actual ?? 0);
        $achievement = $target > 0 ? ($actual / $target) * 100 : 0;

        // Pipeline by levels (L1 Won, L2 Nego, L3 Proposal, L4 Approach)
        $levels = [
            'L1' => Lead::where('status', LeadStatus::Won)->whereYear('created_at', $year)->count(),
            'L2' => Lead::where('status', LeadStatus::Negotiation)->count(),
            'L3' => Lead::where('status', LeadStatus::Proposal)->count(),
            'L4' => Lead::whereIn('status', [LeadStatus::Lead, LeadStatus::Approach])->count(),
        ];

        return [
            'target_revenue' => $target,
            'actual_revenue' => $actual,
            'achievement_percent' => $achievement,
            'pipeline_levels' => $levels,
        ];
    }

    /**
     * Get revenue by segment (Real-time) - Updated to use budget_amount for "Target"
     */
    public function getRevenueBySegment(): array
    {
        $year = now()->year;
        $segments = RevenueSegment::all();
        $labels = [];
        $data = [];

        foreach ($segments as $segment) {
            $value = SalesPlanMonthly::where('year', $year)
                ->whereHas('salesPlan', fn ($q) => $q->where('revenue_segment_id', $segment->id))
                ->sum('budget_amount'); // Menggunakan budget_amount karena judulnya "Target"

            $labels[] = $segment->name;
            $data[] = round((float) $value / 1000000, 2);
        }

        return compact('labels', 'data');
    }

    /**
     * Get revenue by product cluster (Real-time)
     */
    public function getRevenueByProductCluster(): array
    {
        $year = now()->year;
        $clusters = ProductCluster::all();
        $labels = [];
        $data = [];

        foreach ($clusters as $cluster) {
            $value = SalesPlanMonthly::where('year', $year)
                ->whereHas('salesPlan', fn ($q) => $q->where('product_cluster_id', $cluster->id))
                ->sum('actual_amount');

            $labels[] = $cluster->name;
            $data[] = round((float) $value / 1000000, 2);
        }

        return compact('labels', 'data');
    }

    /**
     * Get monthly performance trend (Real-time)
     */
    public function getMonthlyPerformanceTrend(): array
    {
        $year = now()->year;
        $months = [];
        $targetData = [];
        $forecastData = [];
        $actualData = [];

        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($year, $m, 1);
            $months[] = $date->format('M');

            $stats = SalesPlanMonthly::where('year', $year)
                ->where('month', $m)
                ->selectRaw('SUM(budget_amount) as target, SUM(forecast_amount) as forecast, SUM(actual_amount) as actual')
                ->first();

            $targetData[] = round((float) ($stats->target ?? 0) / 1000000, 2);
            $forecastData[] = round((float) ($stats->forecast ?? 0) / 1000000, 2);
            $actualData[] = round((float) ($stats->actual ?? 0) / 1000000, 2);
        }

        return [
            'months' => $months,
            'targetData' => $targetData,
            'forecastData' => $forecastData,
            'actualData' => $actualData,
        ];
    }

    /**
     * Legacy method: Get basic CRM statistics (Real-time)
     */
    public function getStats(): array
    {
        $activeLeads = Lead::whereNotIn('status', [
            LeadStatus::Won,
            LeadStatus::ClosedLost,
        ])->count();

        $thisMonthStart = Carbon::now()->startOfMonth();
        $thisMonthLeads = Lead::where('created_at', '>=', $thisMonthStart)->count();
        $thisMonthWon = Lead::where('status', LeadStatus::Won)
            ->where('created_at', '>=', $thisMonthStart)
            ->count();

        $conversionRate = $thisMonthLeads > 0
            ? round(($thisMonthWon / $thisMonthLeads) * 100, 2)
            : 0;

        $pipelineValue = Lead::whereNotIn('status', [
            LeadStatus::Won,
            LeadStatus::ClosedLost,
        ])->sum('estimated_amount');

        return [
            'active_leads' => $activeLeads,
            'conversion_rate' => $conversionRate,
            'pipeline_value' => $pipelineValue,
            'leads_this_month' => $thisMonthLeads,
        ];
    }

    /**
     * Legacy method: Get financial performance metrics (Real-time)
     */
    public function getFinancialPerformance(): array
    {
        $year = now()->year;

        $metrics = ProfitabilityAnalysisMonthly::where('year', $year)
            ->selectRaw('SUM(target_revenue) as target, SUM(actual_revenue) as actual, SUM(gross_profit) as gp, SUM(ebit) as ebit')
            ->first();

        return [
            'target' => (float) ($metrics->target ?? 0),
            'actual' => (float) ($metrics->actual ?? 0),
            'gross_profit' => (float) ($metrics->gp ?? 0),
            'ebit' => (float) ($metrics->ebit ?? 0),
            'realization_rate' => $metrics->target > 0 ? round(($metrics->actual / $metrics->target) * 100, 1) : 0,
        ];
    }
}
