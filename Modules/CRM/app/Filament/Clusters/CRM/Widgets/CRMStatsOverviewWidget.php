<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;

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

            return [
                Stat::make('Active Leads', number_format($activeLeads))
                    ->description('Leads in pipeline')
                    ->descriptionIcon('heroicon-m-funnel')
                    ->color('primary')
                    ->chart($this->getLeadsSparklineData()),

                Stat::make('Conversion Rate', $conversionRate.'%')
                    ->description(
                        $conversionTrend >= 0
                            ? '+'.$conversionTrend.'% from last month'
                            : $conversionTrend.'% from last month'
                    )
                    ->descriptionIcon($conversionTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                    ->color($conversionTrend >= 0 ? 'success' : 'danger'),

                Stat::make('Pipeline Value', 'Rp '.number_format($pipelineValue, 0, ',', '.'))
                    ->description('Total estimated value')
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('warning'),

                Stat::make('Avg Deal Size', 'Rp '.number_format($avgDealSize, 0, ',', '.'))
                    ->description('From won deals')
                    ->descriptionIcon('heroicon-m-calculator')
                    ->color('info'),

                Stat::make('New Leads', number_format($leadsThisMonth))
                    ->description(
                        $leadsTrend >= 0
                            ? '+'.$leadsTrend.'% from last month'
                            : $leadsTrend.'% from last month'
                    )
                    ->descriptionIcon($leadsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
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
