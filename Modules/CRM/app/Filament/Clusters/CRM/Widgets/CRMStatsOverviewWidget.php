<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CRMStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $service = app(CRMAnalyticsService::class);
        $kpis = $service->getCoreKPIs();

        return [
            Stat::make('Target Revenue (YTD)', 'IDR '.number_format($kpis['target_revenue'] / 1000000, 1).'M')
                ->description(__('Annual Budget Plan'))
                ->descriptionIcon(Heroicon::Flag)
                ->color('primary'),

            Stat::make('Achievement', number_format($kpis['achievement_percent'], 1).'%')
                ->description(__('Actual vs Target'))
                ->descriptionIcon($kpis['achievement_percent'] >= 100 ? Heroicon::CheckCircle : Heroicon::ArrowTrendingUp)
                ->color($kpis['achievement_percent'] >= 100 ? 'success' : 'warning')
                ->chart([7, 10, 5, 2, 20, 30, 45]), // Dummy trend for visual flair

            Stat::make('L1 - Won', $kpis['pipeline_levels']['L1'])
                ->description(__('Signed Contracts'))
                ->descriptionIcon(Heroicon::Trophy)
                ->color('success'),

            Stat::make('L2 - Negotiation', $kpis['pipeline_levels']['L2'])
                ->description(__('Pending Negotiation'))
                ->descriptionIcon(Heroicon::ChatBubbleLeftRight)
                ->color('info'),

            Stat::make('L3 - Proposal', $kpis['pipeline_levels']['L3'])
                ->description(__('Proposals Submitted'))
                ->descriptionIcon(Heroicon::DocumentText)
                ->color('warning'),

            Stat::make('L4 - Approach', $kpis['pipeline_levels']['L4'])
                ->description(__('Initial Prospects'))
                ->descriptionIcon(Heroicon::MagnifyingGlass)
                ->color('gray'),
        ];
    }
}
