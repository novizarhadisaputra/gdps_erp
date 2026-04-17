<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\Finance\Models\ProfitabilityAnalysisWeekly;

class CRMStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $cache = app(AnalyticsCacheService::class);

        return $cache->rememberRealtime('crm.stats_overview', function () {
            // Total Active Leads (not Won or Closed Lost)
            $activeLeads = Lead::whereNotIn('status', [
                LeadStatus::Won,
                LeadStatus::ClosedLost,
            ])->count();

            // This Month Conversion Rate
            $thisMonthStart = Carbon::now()->startOfMonth();
            $thisMonthLeads = Lead::where('created_at', '>=', $thisMonthStart)->count();
            $thisMonthWon = Lead::where('status', LeadStatus::Won)
                ->where('created_at', '>=', $thisMonthStart)
                ->count();
            $conversionRate = $thisMonthLeads > 0
                ? round(($thisMonthWon / $thisMonthLeads) * 100, 2)
                : 0;

            // Previous month for comparison
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $lastMonthLeads = Lead::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
            $lastMonthWon = Lead::where('status', LeadStatus::Won)
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->count();
            $lastMonthConversionRate = $lastMonthLeads > 0
                ? round(($lastMonthWon / $lastMonthLeads) * 100, 2)
                : 0;

            $conversionTrend = $conversionRate - $lastMonthConversionRate;

            // Total Pipeline Value
            $pipelineValue = Lead::whereNotIn('status', [
                LeadStatus::Won,
                LeadStatus::ClosedLost,
            ])->sum('estimated_amount');

            // Average Deal Size
            $avgDealSize = Lead::where('status', LeadStatus::Won)
                ->avg('estimated_amount') ?? 0;

            // Leads Created This Month
            $leadsThisMonth = Lead::where('created_at', '>=', $thisMonthStart)->count();
            $leadsLastMonth = Lead::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
            $leadsTrend = $leadsLastMonth > 0
                ? round((($leadsThisMonth - $leadsLastMonth) / $leadsLastMonth) * 100, 1)
                : 0;

            // Projected Revenue from Weekly Updates
            // Fix PostgreSQL MAX(uuid) error: Find latest ID per project using a compatible subquery
            $latestIds = ProfitabilityAnalysisWeekly::query()
                ->select('id')
                ->whereIn('created_at', function ($query) {
                    $query->select(DB::raw('MAX(created_at)'))
                        ->from('profitability_analysis_weeklies')
                        ->groupBy('profitability_analysis_id');
                })
                ->pluck('id');

            $projectedRevenue = ProfitabilityAnalysisWeekly::whereIn('id', $latestIds)->sum('projected_revenue');

            $lastWeekProjected = ProfitabilityAnalysisWeekly::where('created_at', '<', Carbon::now()->startOfWeek())
                ->where('created_at', '>=', Carbon::now()->subWeek()->startOfWeek())
                ->sum('projected_revenue');

            $projTrend = $lastWeekProjected > 0
                ? round((($projectedRevenue - $lastWeekProjected) / $lastWeekProjected) * 100, 1)
                : 0;

            return [
                Stat::make('Active Leads', number_format($activeLeads))
                    ->description('Leads in pipeline')
                    ->descriptionIcon(Heroicon::Funnel)
                    ->color('primary')
                    ->chart($this->getLeadsSparklineData()),

                Stat::make('Conversion Rate', $conversionRate.'%')
                    ->description(
                        $conversionTrend >= 0
                            ? '+'.$conversionTrend.'% from last month'
                            : $conversionTrend.'% from last month'
                    )
                    ->descriptionIcon($conversionTrend >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                    ->color($conversionTrend >= 0 ? 'success' : 'danger'),

                Stat::make('Pipeline Value', 'Rp '.number_format($pipelineValue, 0, ',', '.'))
                    ->description('Total estimated value')
                    ->descriptionIcon(Heroicon::CurrencyDollar)
                    ->color('warning'),

                Stat::make('Projected Revenue (Weekly)', 'Rp '.number_format($projectedRevenue, 0, ',', '.'))
                    ->description($projTrend >= 0 ? '+'.number_format($projTrend, 1).'% from last week' : number_format($projTrend, 1).'% from last week')
                    ->descriptionIcon($projTrend >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                    ->color($projTrend >= 0 ? 'success' : 'warning'),

                Stat::make('Avg Deal Size', 'Rp '.number_format($avgDealSize, 0, ',', '.'))
                    ->description('From won deals')
                    ->descriptionIcon(Heroicon::Calculator)
                    ->color('info'),

                Stat::make('New Leads', number_format($leadsThisMonth))
                    ->description(
                        $leadsTrend >= 0
                            ? '+'.$leadsTrend.'% from last month'
                            : $leadsTrend.'% from last month'
                    )
                    ->descriptionIcon($leadsTrend >= 0 ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                    ->color($leadsTrend >= 0 ? 'success' : 'danger')
                    ->chart($this->getNewLeadsSparklineData()),
            ];
        });
    }

    protected function getLeadsSparklineData(): array
    {
        $days = 7;
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Lead::whereNotIn('status', [LeadStatus::Won, LeadStatus::ClosedLost])
                ->whereDate('created_at', '<=', $date)
                ->count();
            $data[] = $count;
        }

        return $data;
    }

    protected function getNewLeadsSparklineData(): array
    {
        $days = 7;
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Lead::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }
}
